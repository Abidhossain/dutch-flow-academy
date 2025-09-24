<?php
/**
 * YITH Vendors Admin Coupons Helper Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
 */

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Coupons' ) ) {
	/**
	 * YITH Vendors Admin Coupons Helper class.
	 */
	class YITH_Vendors_Admin_Coupons {

		/**
		 * Construct
		 *
		 * @since  5.0.0
		 */
		public function __construct() {
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_limited_hooks' ), 10, 1 );
		}

		/**
		 * Add hooks to limited vendor dashboard
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function vendor_limited_hooks() {
			if ( 'yes' !== get_option( 'yith_wpv_vendors_option_coupon_management', 'no' ) ) {
				return;
			}

			// Add post types.
			add_filter( 'yith_wcmv_vendor_allowed_vendor_post_type', array( $this, 'add_vendor_post_types' ), 10, 1 );
			// Remove coupon discount type.
			add_filter( 'woocommerce_coupon_discount_types', array( $this, 'coupon_discount_types' ) );
			// Handle post filter custom.
			add_action( 'yith_wcmv_vendor_filter_content_shop_coupon', array( $this, 'filter_coupon_list' ), 10, 2 );
			add_action( 'yith_wcmv_vendor_filter_count_post_shop_coupon', array( $this, 'count_shop_coupon' ), 10, 2 );
			add_action( 'yith_wcmv_restrict_edit_shop_coupon_vendor', array( $this, 'restrict_coupon_edit' ), 10, 2 );

			add_action( 'woocommerce_before_data_object_save', array( $this, 'add_vendor_meta_id' ), 10, 1 );
			// Skip associate taxonomy to orders.
			add_filter( 'yith_wcmv_add_vendor_taxonomy_to_shop_coupon', '__return_false' );
		}

		/**
		 * Add shop_coupon post type to post type available for vendor
		 *
		 * @since  5.0.0
		 * @param array $post_types The default post types array value.
		 * @return array
		 */
		public function add_vendor_post_types( $post_types ) {
			$post_types[] = 'shop_coupon';

			return $post_types;
		}

		/**
		 * Manage vendor taxonomy bulk actions
		 *
		 * @since  5.0.0
		 * @param array $coupon_types The coupon types.
		 * @return array The new coupon types list
		 */
		public function coupon_discount_types( $coupon_types ) {
			$to_unset = apply_filters( 'yith_wcmv_vendor_coupon_types_to_disable', array( 'fixed_cart' ) );

			return array_diff_key( $coupon_types, array_flip( $to_unset ) );
		}

		/**
		 * Filter content based on current vendor
		 *
		 * @since  5.0.0
		 * @param WP_Query    $query  The Wp_Query instance.
		 * @param YITH_Vendor $vendor Current vendor.
		 * @return void
		 */
		public function filter_coupon_list( $query, $vendor ) {
			$query->set( 'author__in', $vendor->get_admins() );
		}

		/**
		 * Filter the coupon count for vendor
		 *
		 * @since    5.0.0
		 * @param boolean|array $counts Current counts.
		 * @param YITH_Vendor   $vendor Current vendor.
		 * @return boolean|array
		 */
		public function count_shop_coupon( $counts, $vendor ) {

			global $wpdb;

			$admins = $vendor->get_admins();
			if ( empty( $admins ) ) {
				return array();
			}

			$admins_count = count( $admins );
			// Prepare the admin placeholders.
			$admin_placeholders = implode( ', ', array_fill( 0, $admins_count, '%s' ) );

			$query   = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = 'shop_coupon' AND post_author IN ( $admin_placeholders ) GROUP BY post_status";
			$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $admins ), ARRAY_A );
			$counts  = array_fill_keys( get_post_stati(), 0 );

			foreach ( $results as $row ) {
				$counts[ $row['post_status'] ] = $row['num_posts'];
			}

			return $counts;
		}

		/**
		 * Restrict coupon edit by vendor
		 *
		 * @since  5.0.0
		 * @param WP_Post     $post   The current post.
		 * @param YITH_Vendor $vendor The vendor instance.
		 */
		public function restrict_coupon_edit( $post, $vendor ) {
			$post_author = $post ? absint( $post->post_author ) : 0;

			if ( ! $post_author || ! in_array( $post_author, $vendor->get_admins(), true ) ) {
				// translators: %1$s and %2$s are placeholder for <a/> html tag opening and closing.
				wp_die( sprintf( __( 'You do not have permission to edit this coupon. %1$sClick here to view and edit your coupons%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_coupon' ) . '">', '</a>' ) );
			}
		}

		/**
		 * Prevent vendor to create percent coupon for cart
		 *
		 * @since 5.0.0
		 * @param WC_Coupon $coupon Coupon object.
		 * @return void
		 */
		public static function add_vendor_meta_id( $coupon ) {

			if ( ! $coupon instanceof WC_Coupon ) {
				return;
			}

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				$coupon->add_meta_data( 'vendor_id', $vendor->get_id(), true );
			}
		}
	}
}
