<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_WPML' ) ) {
	/**
	 * Handle compatibility with WPML
	 */
	class YITH_Vendors_WPML {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * The default language code
		 *
		 * @since  4.0.0
		 * @access protected
		 * @var string
		 */
		protected $default_language = '';

		/**
		 * Vendors translations
		 *
		 * @since  4.0.0
		 * @access protected
		 * @var string
		 */
		protected $translations = array();

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function __construct() {
			if ( apply_filters( 'wpml_setting', false, 'setup_complete' ) ) {
				$this->init();
			}
		}

		/**
		 * Class initialization.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init() {

			$this->default_language = wpml_get_default_language();

			// Se vendor taxonomy as translatable.
			add_action( 'init', array( $this, 'set_taxonomy_translatable' ) );

			add_filter( 'yith_wcmv_get_vendor_meta_data', array( $this, 'filter_vendor_meta_data' ), 10, 3 );
			add_filter( 'yith_wcmv_vendor_dashboard_vendor_in_post', array( $this, 'filter_vendor_in_post' ), 10, 3 );
			add_filter( 'yith_wcmv_vendors_factory_read_vendor_id', array( $this, 'filter_vendor_id_factory' ), 10, 3 );
			// Extend taxonomy meta-box.
			add_filter( 'yith_wcmv_single_taxonomy_meta_box_vendor_slug', array( $this, 'filter_taxonomy_meta_box_vendor_slug' ), 10, 2 );
			// Filter default args for vendor shortcode.
			add_filter( 'yith_wcmv_shortcode_vendor_products_default_args', array( $this, 'filter_vendor_products_default_args' ), 10, 1 );

			add_filter( 'yith_wcmv_commission_vendor_id', array( $this, 'retrieve_vendor_id_for_all_languages' ), 10, 2 );

            add_filter( 'yith_wcmv_hpos_vendor_orders_list_vendor_id', array( $this, 'filter_vendor_id_for_order_list' ), 10, 2 );
		}

		/**
		 * Set taxonomy translatable
		 *
		 * @since  4.2.1
		 * @return void
		 */
		public function set_taxonomy_translatable() {
			$settings_helper = wpml_load_settings_helper();
			$settings_helper->set_taxonomy_translatable( YITH_Vendors_Taxonomy::TAXONOMY_NAME );
		}

		/**
		 * Get an array of translatable keys
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_translatable_keys() {
			return apply_filters(
				'yith_wcmv_get_translation_keys',
				array(
					'shipping_policy',
					'shipping_refund_policy',
				)
			);
		}

		/**
		 * Check if given vendor is a translation
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID to check.
		 * @return boolean
		 */
		public function is_vendor_a_translation( $vendor_id ) {
			$original_vendor = $this->get_vendor_translated( $vendor_id, $this->default_language );
			return $original_vendor && $original_vendor->get_id() !== $vendor_id;
		}

		/**
		 * Check if current vendor is a translation
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		public function is_current_vendor_a_translation() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				return $this->is_vendor_a_translation( $vendor->get_id() );
			}

			return false;
		}

		/**
		 * Get original vendor
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID.
		 * @param string  $language  (Optional) The language to get translation for. The default is the current one.
		 * @return YITH_Vendor|false The vendor instance if found, false otherwise.
		 */
		protected function get_vendor_translated( $vendor_id, $language = '' ) {
			global $yith_wcmv_cache;

			if ( empty( $language ) ) {
				$language = wpml_get_current_language();
			}

			$cache_key            = "translation_{$language}";
			$translated_vendor_id = $yith_wcmv_cache->get_vendor_cache( $vendor_id, $cache_key );
			if ( false === $translated_vendor_id ) {
				$translated_vendor_id = yit_wpml_object_id( $vendor_id, YITH_Vendors_Taxonomy::TAXONOMY_NAME, false, $language );
				// Store on cache.
				$yith_wcmv_cache->set_vendor_cache( $vendor_id, $cache_key, $translated_vendor_id );
			}

			$translated_vendor = ! empty( $translated_vendor_id ) ? yith_wcmv_get_vendor( $translated_vendor_id ) : false;

			return ( $translated_vendor && $translated_vendor->is_valid() ) ? $translated_vendor : false;
		}

		/**
		 * Filter vendor meta data
		 *
		 * @since  4.0.0
		 * @param mixed       $value  The meta value.
		 * @param string      $key    The meta key.
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return mixed
		 */
		public function filter_vendor_meta_data( $value, $key, $vendor ) {
			$translatable_keys = $this->get_translatable_keys();
			$current_vendor_id = $vendor->get_id();
			if ( ! in_array( $key, $translatable_keys, true ) && $this->is_vendor_a_translation( $current_vendor_id ) ) {
				$original_vendor = $this->get_vendor_translated( $current_vendor_id, $this->default_language );
				// Double check for original vendor.
				if ( $original_vendor ) {
					$value = $original_vendor->get_meta( $key );
				}
			}

			return $value;
		}

		/**
		 * Filter vendor associated with the given post ID.
		 * Always get the vendor associated with the original post.
		 *
		 * @since  4.0.0
		 * @param mixed   $vendor    Current vendor associated with given post id.
		 * @param integer $post_id   The post ID.
		 * @param string  $post_type (Optional) The post type. Default is post.
		 * @return mixed
		 */
		public function filter_vendor_in_post( $vendor, $post_id, $post_type = 'post' ) {
			$original_post_id = yit_wpml_object_id( $post_id, $post_type, true, $this->default_language );
			if ( $original_post_id !== $post_id ) {
				$vendor = yith_wcmv_get_vendor( $original_post_id, $post_type ); // If false, the product hasn't any vendor set.
			}

			return $vendor;
		}

		/**
		 * Filter vendor id associated with a post.
		 * Always get the original vendor for translated products or post.
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id   Current vendor ID.
		 * @param mixed   $object      The vendor object.
		 * @param string  $object_type What object is if is numeric (vendor|user|post).
		 * @return mixed
		 */
		public function filter_vendor_id_factory( $vendor_id, $object, $object_type ) {
			if ( 'post' === $object_type || 'product' === $object_type ) {
				$vendor = $this->get_vendor_translated( $vendor_id, $this->default_language );
				if ( $vendor ) {
					$vendor_id = $vendor->get_id();
				}
			}
			return $vendor_id;
		}

		/**
		 * Filter taxonomy meta-box vendor slug value.
		 *
		 * @since  4.0.0
		 * @param string      $slug   Current vendor slug value.
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string
		 */
		public function filter_taxonomy_meta_box_vendor_slug( $slug, $vendor ) {

			global $pagenow;

			if ( $vendor && $vendor->is_valid() ) {
				$translated_vendor = $this->get_vendor_translated( $vendor->get_id() );
				if ( $translated_vendor ) {
					$slug = $translated_vendor->get_slug();
				}
			} elseif ( current_user_can( 'manage_woocommerce' ) && 'post-new.php' === $pagenow && ! empty( $_GET['trid'] ) ) {
				// Get original product from trid.
				$original_product_id = SitePress::get_original_element_id_by_trid( $_GET['trid'] );
				$original_vendor     = yith_wcmv_get_vendor( $original_product_id, 'product' );
				if ( $original_vendor && $original_vendor->is_valid() ) {
					$slug = $this->filter_taxonomy_meta_box_vendor_slug( '', $original_vendor );
				}
			}

			return $slug;
		}

		/**
		 * Filter default args for [yith_wcmv_vendor_products] shortcode.
		 *
		 * @since  4.0.0
		 * @param array $args Current default shortcode arguments.
		 * @return array
		 */
		public function filter_vendor_products_default_args( $args ) {
			if ( ! empty( $args['vendor_id'] ) ) {
				$original_vendor = $this->get_vendor_translated( $args['vendor_id'], $this->default_language );
				if ( $original_vendor ) {
					$args['vendor_id'] = $original_vendor->get_id();
				}
			}
			return $args;
		}
		/**
		 * Filter default args for [yith_wcmv_vendor_products] shortcode.
		 *
		 * @since  4.16.0
		 * @param array $args Current args with default vendor id.
		 * @param int   $vendor_id The original vendor id.
		 * @return array
		 */
		public function retrieve_vendor_id_for_all_languages( $args, $vendor_id ) {
			$languages = apply_filters( 'wpml_active_languages', null, array() );
			foreach ( $languages as $language ) {
				$vendor_translated = apply_filters( 'wpml_object_id', $vendor_id, 'yith_shop_vendor', false, $language['language_code'] );
				$args[]            = $vendor_translated;
			}
			return $args;
		}


        /**
         * Filter vendor ID to retrieve the correct list of orders.
         *
         * @param $ids
         * @param $vendor_id
         * @return array
         */
        public function filter_vendor_id_for_order_list( $ids, $vendor_id ){
            $translated_ids = $this->retrieve_vendor_id_for_all_languages( array(), $vendor_id );
            $translated_ids = array_merge( $translated_ids, $ids );
            return $translated_ids;
        }

	}
}

if ( ! function_exists( 'YITH_Vendors_WPML' ) ) {
	/**
	 * Get single instance if the class
	 *
	 * @since  4.0.0
	 * @return YITH_Vendors_WPML
	 */
	function YITH_Vendors_WPML() { // phpcs:ignore
		return YITH_Vendors_WPML::instance();
	}
}
