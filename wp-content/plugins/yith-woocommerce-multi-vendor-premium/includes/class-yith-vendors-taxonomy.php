<?php
/**
 * YITH Vendors Taxonomy Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Taxonomy' ) ) {
	/**
	 * Define plugin taxonomy
	 */
	class YITH_Vendors_Taxonomy {

		/**
		 * The taxonomy name
		 *
		 * @const string
		 */
		const TAXONOMY_NAME = 'yith_shop_vendor';

		/**
		 * Vendor taxonomy singular label
		 *
		 * @since 3.2.14
		 * @var string
		 */
		public static $singular_label = '';

		/**
		 * Vendor taxonomy plural label
		 *
		 * @since 3.2.14
		 * @var string
		 */
		public static $plural_label = '';

		/**
		 * Init class
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function init() {
			// Set plural and Singular for vendor/vendors labels.
			self::set_singular_label();
			self::set_plural_label();

			// Register the taxonomy.
			add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 5 );
			add_action( 'init', array( __CLASS__, 'register_taxonomy_for_post_types' ), 15 );
		}

		/**
		 * Register taxonomy for vendors
		 *
		 * @since  1.0
		 * @return void
		 */
		public static function register_taxonomy() {

			$slug = get_option( 'yith_wpv_vendor_taxonomy_rewrite', 'vendor' );
			if ( empty( $slug ) ) {
				$slug = 'vendor';
			}

			$args = apply_filters(
				'yith_wcmv_vendor_taxonomy_args',
				array(
					'public'            => true,
					'hierarchical'      => false,
					'show_admin_column' => true,
					'show_in_menu'      => false,
					'show_in_nav_menus' => true,
					'labels'            => self::get_taxonomy_labels(),
					'rewrite'           => array( 'slug' => $slug ),
					'meta_box_cb'       => 'YITH_Vendors_Taxonomy::single_taxonomy_meta_box',
				)
			);

			register_taxonomy( self::TAXONOMY_NAME, 'product', $args );
		}

		/**
		 * Register taxonomy for additional post types
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function register_taxonomy_for_post_types() {
			$taxonomies_object_type = apply_filters( 'yith_wcmv_register_taxonomy_object_type', array() );
			foreach ( $taxonomies_object_type as $taxonomy_object_type ) {
				register_taxonomy_for_object_type( self::TAXONOMY_NAME, $taxonomy_object_type );
			}
		}

		/**
		 * Get the vendors taxonomy label
		 *
		 * @since  1.0.0
		 * @param string $key The string to return. Default empty. If is empty return all taxonomy labels.
		 * @return array|string
		 */
		public static function get_taxonomy_labels( $key = '' ) {
			$vendor_singular_label = self::get_singular_label();
			$vendor_plural_label   = self::get_plural_label();

			$label = apply_filters(
				'yith_product_vendors_taxonomy_label',
				array(
					'name'                       => $vendor_singular_label,
					'singular_name'              => $vendor_singular_label,
					'menu_name'                  => $vendor_plural_label,
					'search_items'               => sprintf( '%s %s', _x( 'Search', '[Part of] Search Vendors', 'yith-woocommerce-product-vendors' ), $vendor_plural_label ),
					'all_items'                  => sprintf( '%s %s', _x( 'All', '[Part of] All Vendors', 'yith-woocommerce-product-vendors' ), $vendor_plural_label ),
					'parent_item'                => sprintf( '%s %s', _x( 'Parent', '[Part of] Parent Vendor', 'yith-woocommerce-product-vendors' ), $vendor_singular_label ),
					'parent_item_colon'          => sprintf( '%s %s:', _x( 'Parent', '[Part of] Parent Vendor', 'yith-woocommerce-product-vendors' ), $vendor_singular_label ),
					'view_item'                  => sprintf( '%s %s', _x( 'View', '[Part of] View Vendor', 'yith-woocommerce-product-vendors' ), $vendor_singular_label ),
					'edit_item'                  => sprintf( '%s %s', _x( 'Edit', '[Part of] Edit Vendor', 'yith-woocommerce-product-vendors' ), $vendor_singular_label ),
					'update_item'                => sprintf( '%s %s', _x( 'Update', '[Part of] Update Vendor', 'yith-woocommerce-product-vendors' ), $vendor_singular_label ),
					'add_new_item'               => sprintf( '%s %s', _x( 'Add New', '[Part of] Add New Vendor', 'yith-woocommerce-product-vendors' ), $vendor_singular_label ),
					'new_item_name'              => sprintf( "%s %s's %s", _x( 'New', "[Part of] New Vendor's Name", 'yith-woocommerce-product-vendors' ), $vendor_singular_label, _x( 'New', "[Part of] New Vendor's Name", 'yith-woocommerce-product-vendors' ) ),
					'popular_items'              => null, // don't remove!
					'separate_items_with_commas' => sprintf( '%s %s %s', _x( 'Separate', '[Part of] Separate vendors with commas', 'yith-woocommerce-product-vendors' ), self::get_plural_label( 'strtolower' ), _x( 'with commas', '[Part of] Separate vendors with commas', 'yith-woocommerce-product-vendors' ) ),
					'add_or_remove_items'        => sprintf( '%s %s', _x( 'Add or remove', '[Part of] Add or remove vendors', 'yith-woocommerce-product-vendors' ), self::get_plural_label( 'strtolower' ) ),
					'choose_from_most_used'      => sprintf( '%s %s', _x( 'Choose from most used', '[Part of] Choose from most used vendors', 'yith-woocommerce-product-vendors' ), $vendor_plural_label ),
					'not_found'                  => sprintf( '%s %s', $vendor_plural_label, _x( 'not found', '[Part of] Vendors not found', 'yith-woocommerce-product-vendors' ) ),
					'back_to_items'              => sprintf( '%s %s %s', '&larr;', _x( 'Back to', '[Part of] Back to Vendors', 'yith-woocommerce-product-vendors' ), $vendor_plural_label ),
				)
			);

			return ! empty( $key ) ? $label[ $key ] : $label;
		}

		/**
		 * Get the vendor singular label
		 *
		 * @param string $callback A callback to use before return result.
		 * @return string
		 */
		public static function get_singular_label( $callback = '' ) {
			$singular = self::$singular_label;
			if ( ! empty( $callback ) && function_exists( $callback ) ) {
				$singular = $callback( $singular );
			}
			$singular = call_user_func( '__', $singular, 'yith-woocommerce-product-vendors' );

			return $singular;
		}

		/**
		 * Set the vendor singular label
		 *
		 * @param string $singular_label The vendor singular label.
		 * @return void
		 */
		public static function set_singular_label( $singular_label = '' ) {
			if ( empty( $singular_label ) ) {
				$labels         = get_option( 'yith_wpv_vendor_label_text', array() );
				$singular_label = isset( $labels['singular'] ) ? $labels['singular'] : _x( 'Vendor', 'default singular vendor label', 'yith-woocommerce-product-vendors' );
			}
			self::$singular_label = $singular_label;
		}

		/**
		 * Get the vendor  plural  label
		 *
		 * @param string $callback A callback to use before return result.
		 * @return string
		 */
		public static function get_plural_label( $callback = '' ) {
			$plural = self::$plural_label;
			if ( ! empty( $callback ) && function_exists( $callback ) ) {
				$plural = $callback( $plural );
			}
			$plural = call_user_func( '__', $plural, 'yith-woocommerce-product-vendors' );

			return $plural;
		}

		/**
		 * Set the vendor plural label
		 *
		 * @param string $plural_label The vendor plural label.
		 * @return void
		 */
		public static function set_plural_label( $plural_label = '' ) {
			if ( empty( $plural_label ) ) {
				$labels       = get_option( 'yith_wpv_vendor_label_text', array() );
				$plural_label = isset( $labels['plural'] ) ? $labels['plural'] : _x( 'Vendors', 'default plural vendor label', 'yith-woocommerce-product-vendors' );
			}
			self::$plural_label = $plural_label;
		}

		/**
		 * Print the Single Taxonomy meta-box
		 *
		 * @since  4.0.0
		 * @param WP_Post $product The current product instance.
		 * @return void
		 */
		public static function single_taxonomy_meta_box( $product = false ) {

			$taxonomy_label = self::get_taxonomy_labels();
			$vendor         = yith_wcmv_get_vendor( $product, 'product' );

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				echo $vendor->get_name();
			} else {

				$is_super_user = current_user_can( 'manage_woocommerce' );
				$vendor_slug   = ( $vendor && $vendor->is_valid() ) ? $vendor->get_slug() : '';

				// Let's filter vendor slug.
				$vendor_slug = apply_filters( 'yith_wcmv_single_taxonomy_meta_box_vendor_slug', $vendor_slug, $vendor );

				$args = array(
					'id'                => 'tax-input-yith_shop_vendor',
					'name'              => 'tax_input[' . self::TAXONOMY_NAME . ']',
					'taxonomy'          => self::TAXONOMY_NAME,
					'show_option_none'  => ! $is_super_user ? '' : sprintf( __( 'No %s' ), strtolower( $taxonomy_label['singular_name'] ) ),
					'hide_empty'        => ! $is_super_user,
					'selected'          => $vendor_slug,
					'value_field'       => 'slug',
					'option_none_value' => '', // Avoid to save -1 as new vendor when you create a new product.
				);

				wp_dropdown_categories( $args );
			}
		}
	}
}
