<?php
/**
 * YITH Vendor Legacy Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendor_Legacy' ) ) {
	/**
	 * The legacy main class for the Vendor
	 */
	abstract class YITH_Vendor_Legacy extends YITH_Vendors_Vendor_Data_Store {

		/**
		 * Stores term data of vendor.
		 *
		 * @var string
		 */
		protected static $user_meta_key = '';

		/**
		 * Stores term data of vendor.
		 *
		 * @var string
		 */
		protected static $user_meta_owner = '';

		/**
		 * Default store header image
		 *
		 * @deprecated
		 * @var string
		 */
		public static $default_store_header = '';

		/**
		 * The taxonomy of the vendor.
		 *
		 * @var string
		 * @deprecated
		 */
		public static $taxonomy = YITH_Vendors_Taxonomy::TAXONOMY_NAME;

		/**
		 * Main Instance
		 *
		 * @since  1.0
		 * @access protected
		 * @var null|YITH_Vendor[]
		 */
		protected static $instance = null;

		/**
		 * __get function.
		 *
		 * @param string $key The property key to retrieve.
		 * @return mixed
		 */
		public function __get( $key ) {

			yith_wcmv_doing_it_wrong( $key, 'Vendor properties should not be accessed directly.', '4.0.0' );

			$defaults = array(
				'payment_type' => 'instant',
				'threshold'    => 50,
			);

			if ( 'id' === $key ) {
				$value = $this->get_id();
			} elseif ( isset( $this->term->$key ) ) {
				$value = $this->term->$key;

			} elseif ( ! empty( $this->meta_data[ $key ] ) ) {
				$value = $this->meta_data[ $key ];

			} elseif ( array_key_exists( $key, $defaults ) ) {
				$value = $defaults[ $key ];

			} else {
				$value = '';
			}

			// Special cases.
			switch ( $key ) {
				case 'admins':
					$value = $this->get_admins();
					break;

				case 'owner':
					$value = $this->get_owner();
					break;

				case 'taxonomy':
					$value = YITH_Vendors_Taxonomy::TAXONOMY_NAME;
					break;

				case 'socials':
					$value = ! empty( $value ) ? $value : array();
					break;

				case 'registration_date':
					if ( empty( $value ) ) {
						$owner_id = $this->get_owner();
						if ( ! empty( $owner_id ) ) {
							$owner = get_user_by( 'id', $owner_id );
							$value = $owner->user_registered;
						}
					}
					break;

				case 'enable_selling':
					$value = $this->get_enable_selling();
					break;

				case 'pending':
					$value = $this->get_pending();
					break;

				case 'header_image':
					$value = $this->get_header_image_id();
					break;
			}

			return apply_filters( 'yith_wcmv_get_vendor_value', $value, $this, $key );
		}

		/**
		 * __set function.
		 *
		 * @param string $key   The property key to set.
		 * @param mixed  $value The new property value.
		 * @return void
		 */
		public function __set( $key, $value ) {

			yith_wcmv_doing_it_wrong( $key, 'Vendor properties should not be set directly.', '4.0.0' );

			// Handle boolean.
			if ( is_bool( $value ) ) {
				$value = $value ? 'yes' : 'no';
			}

			// Handle removed meta.
			switch ( $key ) {
				case 'enable_selling':
					$key   = 'status';
					$value = 'yes' === $value ? 'enabled' : 'disabled';
					break;

				case 'pending':
					$key   = 'status';
					$value = 'yes' === $value ? 'pending' : 'enabled';
					break;
			}

			$this->changes[ $key ] = $value;
			// We need to be backward compatible and save set data on shutdown.
			if ( ! has_action( 'shutdown', array( $this, 'save_data' ) ) ) {
				add_action( 'shutdown', array( $this, 'save_data' ) );
			}
		}

		/**
		 * __isset function.
		 *
		 * @param mixed $key The property keys to check.
		 * @return bool
		 */
		public function __isset( $key ) {

			$socials = YITH_Vendors()->get_social_fields();
			$isset   = false;

			if ( isset( $this->term->$key ) || isset( $this->data[ $key ] ) || isset( $this->meta_data[ $key ] ) ) {
				$isset = true;
			} elseif ( ! empty( $socials ) && ! empty( $socials['social_fields'] ) && isset( $socials['social_fields'][ $key ] ) ) { // Check if the fields is a socials.
				$isset = true;
			}

			return $isset;
		}

		/**
		 * Retrieve a vendor
		 *
		 * @param mixed  $vendor The vendor object.
		 * @param string $obj    What object is if is numeric (vendor|user|post).
		 * @return bool|YITH_Vendor
		 * @deprecated
		 */
		public static function retrieve( $vendor = false, $obj = 'vendor' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Factory::read' );

			self::$user_meta_key   = yith_wcmv_get_user_meta_key();
			self::$user_meta_owner = yith_wcmv_get_user_meta_owner();

			return YITH_Vendors_Factory::read( $vendor, $obj );
		}

		/**
		 * Populate information of vendor
		 *
		 * @since 1.0
		 * @deprecated
		 */
		protected function populate() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Factory::read' );
			$this->read();
		}

		/**
		 * Get cached vendor instance by ID
		 *
		 * @param integer $vendor_id   (Optional) The vendor ID. Default is 0.
		 * @param null    $vendor_term (Optional) The vendor term. Default is null.
		 * @return mixed
		 * @deprecated
		 */
		protected static function instance( $vendor_id = 0, $vendor_term = null ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Factory::read' );
			return null;
		}

		/**
		 * Get the vendor's settings
		 *
		 * @param string      $key     The setting key to retrieve.
		 * @param bool|string $default The setting default value.
		 * @return mixed
		 * @deprecated
		 */
		public function get_setting( $key, $default = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			$settings = get_option( 'yit_vendor_' . $this->id . '_options' );
			return isset( $settings[ $key ] ) ? wc_clean( $settings[ $key ] ) : $default;
		}

		/**
		 * Get enabled selling cap for vendor
		 *
		 * @since    1.11.2
		 * @return   bool
		 * @deprecated
		 */
		public function get_pending() {
			$vendor_id = yith_wcmv_get_wpml_vendor_id( $this->id );
			$return    = get_term_meta( $vendor_id, 'pending', true );

			return $return;
		}

		/**
		 * Check if the user passed in parameter is admin
		 *
		 * @since 1.0
		 * @param bool $user_id The user to check.
		 * @return bool
		 */
		public function is_super_user( $user_id = false ) {

			_deprecated_function( __METHOD__, '4.0.0', 'current_user_can' );

			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			// If the user is shop manager or administrator, return true.
			return user_can( $user_id, 'manage_woocommerce' );
		}

		/**
		 * Return the arguments to make a query for the posts of this vendor
		 *
		 * @param array $extra More arguments to append.
		 * @return array
		 * @deprecated
		 */
		public function get_query_products_args( $extra = array() ) {
			return wp_parse_args(
				$extra,
				array(
					'post_type' => 'product',
					'tax_query' => array( // phpcs:ignore
						array(
							'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
							'field'    => 'id',
							'terms'    => $this->id,
						),
					),
				)
			);
		}

		/**
		 * Get all unpaid commissions, if the sum amount is out threshold
		 *
		 * @param array $extra_args An array of extra arguments to use in query.
		 * @return array|null
		 * @deprecated
		 */
		public function get_unpaid_commissions( $extra_args = array() ) {
			$args = array(
				'vendor_id' => $this->get_id(),
				'order_id'  => '', // Useful when is set the order as completed from orders list, because it set "order_id" in the query string.
				'status'    => 'unpaid',
			);

			$args = wp_parse_args( $extra_args, $args );

			return yith_wcmv_get_commissions( $args );
		}

		/**
		 * Get all unpaid commissions, if the sum amount is out threshold
		 *
		 * @return array|null
		 * @deprecated
		 */
		public function get_unpaid_commissions_if_out_threshold() {
			if ( $this->get_unpaid_commissions_amount() < $this->threshold ) {
				return array();
			}

			$args = array(
				'vendor_id' => $this->id,
				'order_id'  => '', // Useful when is set the order as completed from orders list, because it set "order_id" in the query string.
				'status'    => 'unpaid',
			);

			return yith_wcmv_get_commissions( $args );
		}

		/**
		 * If payment minimum threshold is reached, get all commissions that haven't been paid yet.
		 *
		 * @return float
		 * @deprecated
		 */
		public function get_unpaid_commissions_amount() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors()->commissions->get_unpaid_commissions_amount( $vendor_id )' );
			return YITH_Vendors()->commissions->get_unpaid_commissions_amount( $this->get_id() );
		}

		/**
		 * Pay commissions unpaid, in base of payment type chosen
		 *
		 * @param string $type All or only after threshold.
		 * @return array
		 * @deprecated
		 */
		public function commissions_to_pay( $type = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( 'threshold' === $type ) {
				$commissions = $this->get_unpaid_commissions_if_out_threshold(); // could be empty.
			} else {
				$commissions = $this->get_unpaid_commissions();
			}

			return $commissions;
		}

		/**
		 * Get the email vendor order table
		 *
		 * @param WC_Order $order               Order object.
		 * @param boolean  $show_download_links (Optional) True to show item download link, false otherwise. Default false.
		 * @param boolean  $show_sku            (Optional) True to show item sku, false otherwise. Default false.
		 * @param boolean  $show_purchase_note  (Optional) True to show purchase note, false otherwise. Default false.
		 * @param boolean  $show_image          (Optional) True to show item image, false otherwise. Default false.
		 * @param array    $image_size          (Optional) The item image size. Default array(32,32).
		 * @param boolean  $plain_text          (Optional) True if is a plain email, false otherwise. Default false.
		 * @return void
		 * @deprecated
		 */
		public function email_order_items_table( $order, $show_download_links = false, $show_sku = false, $show_purchase_note = false, $show_image = false, $image_size = array( 32, 32 ), $plain_text = false ) {
			_deprecated_function( __METHOD__, '4.0.0', 'Use action yith_wcmv_email_order_items_table' );
			do_action( 'yith_wcmv_email_order_items_table', $this, $order, $show_download_links, $show_sku, $show_purchase_note, $show_image, $image_size, $plain_text );
		}

		/**
		 * Check if current vendor is on vacation.
		 *
		 * @return boolean
		 * @deprecated
		 */
		public function is_on_vacation() {
			_deprecated_function( __METHOD__, '4.0.0', 'Use method YITH_Vendor_Vacation()->vendor_is_on_vacation( $vendor )' );
			return function_exists( 'YITH_Vendor_Vacation' ) ? YITH_Vendor_Vacation()->vendor_is_on_vacation( $this ) : false;
		}

		/**
		 * Check if current vendor can handle featured products.
		 *
		 * @return mixed
		 * @deprecated
		 */
		public function featured_products_management() {
			_deprecated_function( __METHOD__, '4.0.0', 'Use method can_handle_featured_products' );
			return $this->get_meta( 'featured_products' );
		}

		/**
		 * Get the translation for Shipping Policy and Refund Policy for vendor
		 *
		 * @param string      $value  Current term meta value.
		 * @param YITH_Vendor $vendor Current vendor object.
		 * @param string      $key    Field to get.
		 * @return string The translated string
		 * @deprecated
		 */
		public function get_translation( $value, $vendor, $key ) {
			$keys = apply_filters(
				'yith_wcmv_get_translation_keys',
				array(
					'shipping_policy',
					'shipping_refund_policy',
				)
			);

			if ( in_array( $key, $keys, true ) ) {
				$type             = apply_filters( 'wpml_element_type', YITH_Vendors_Taxonomy::TAXONOMY_NAME );
				$trid             = apply_filters( 'wpml_element_trid', null, $vendor->id, $type );
				$vendors          = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );
				$current_language = apply_filters( 'wpml_current_language', '' );

				if ( ! empty( $vendors[ $current_language ] ) ) {
					$wpml_vendor_args = $vendors[ $current_language ];
					$wpml_vendor      = get_term( $wpml_vendor_args->element_id, YITH_Vendors_Taxonomy::TAXONOMY_NAME );

					if ( $wpml_vendor instanceof WP_Term ) {
						$value = get_term_meta( $wpml_vendor->term_id, $key );
					}
				}
			}

			return $value;
		}

		/**
		 * Save changed data function.
		 *
		 * @since  1.0.0
		 * @return void
		 * @deprecated
		 */
		public function save_data() {
			$this->save();
		}

		/**
		 * Get the registration date
		 *
		 * @param string $registration_date The registration date to format.
		 * @param string $context           (Optional) The context of the date (timestamp|display|edit).
		 * @param string $format            (Optional) The date format.
		 * @return string The registration date.
		 * @deprecated
		 */
		public static function get_date( $registration_date, $context = '', $format = '' ) {

			_deprecated_function( __METHOD__, '4.0.0' );

			if ( 'timestamp' === $context ) {
				return mysql2date( 'U', $registration_date );
			} elseif ( 'display' === $context ) {
				if ( empty( $format ) ) {
					$format = get_option( 'date_format' );
				}
				return mysql2date( $format, $registration_date );
			} else {
				return $registration_date;
			}
		}

		/**
		 * Get posts of this vendor
		 *
		 * @since  4.0.0
		 * @param string       $post_type the post type to get.
		 * @param array|string $fields    The fields to retrieve.
		 * @param string       $group_by  If group by the query.
		 * @return array
		 * @deprecated
		 */
		protected function get_posts( $post_type, $fields = '*', $group_by = '' ) {
			_deprecated_function( __METHOD__, '4.12.0' );

			global $wpdb;

			if ( empty( $post_type ) ) {
				return array();
			}

			if ( 'all' === $fields ) {
				$fields = '*';
			}

			if ( is_array( $fields ) ) {
				$fields = implode( ',', $fields );
			}

			if ( 'shop_order' === $post_type ) {
				$join  = "INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID";
				$where = $wpdb->prepare( 'WHERE p.post_type = %s AND p.post_parent <> 0 AND pm.meta_key = %s AND pm.meta_value = %d', 'shop_order', 'vendor_id', $this->id );
			} else {
				$join  = "INNER JOIN {$wpdb->term_relationships} AS tr ON tr.object_id = p.ID INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
				$where = $wpdb->prepare( 'WHERE tt.taxonomy = %s AND tt.term_id = %d AND p.post_type = %s', YITH_Vendors_Taxonomy::TAXONOMY_NAME, $this->id, $post_type );
			}

			// Set group by if any.
			$group_by = $group_by ? "GROUP BY {$group_by}" : '';
			$result   = $wpdb->get_results( "SELECT {$fields} FROM {$wpdb->posts} AS p $join $where $group_by" ); // phpcs:ignore

			return ! empty( $result ) ? $result : array();
		}

		/**
		 * Count posts of this vendor
		 *
		 * @since  4.0.0
		 * @param string $post_type The post type to count.
		 * @return array
		 * @deprecated
		 */
		public function count_posts( $post_type ) {
			_deprecated_function( __METHOD__, '4.12.0' );
			return $this->get_posts( $post_type, 'post_status, COUNT( DISTINCT ID ) AS count', 'p.post_status' );
		}

		/**
		 * Get enable selling
		 *
		 * @since    4.0.0
		 * @return   string Store header image url.
		 * @deprecated
		 */
		public function get_enable_selling() {
			_deprecated_function( __METHOD__, '5.0.0', 'Use method get_status' );
			return ( 'enabled' === $this->get_meta( 'status' ) ) ? 'yes' : 'no';
		}

		/**
		 * Get enable selling
		 *
		 * @since    4.0.0
		 * @param string $value The name to set.
		 * @return   void
		 * @deprecated
		 */
		public function set_enable_selling( $value ) {
			_deprecated_function( __METHOD__, '5.0.0', 'Use method set_status' );
			$status = ( true === $value || 'no' !== $value ) ? 'enabled' : 'disabled';
			$this->set_meta_data( 'status', $status );
		}

		/**
		 * Is selling enabled for vendor?
		 *
		 * @since  4.0.0
		 * @return boolean
		 * @deprecated
		 */
		public function is_selling_enabled() {
			_deprecated_function( __METHOD__, '5.0.0', 'Use method has_status( \'enabled\' )' );
			return 'enabled' === $this->get_meta( 'status' );
		}

		/**
		 * Is vendor in pending?
		 *
		 * @since  4.0.0
		 * @return boolean
		 * @deprecated
		 */
		public function is_in_pending() {
			_deprecated_function( __METHOD__, '5.0.0', 'Use method has_status( \'pending\' )' );
			return 'pending' === $this->get_meta( 'status' );
		}
	}
}
