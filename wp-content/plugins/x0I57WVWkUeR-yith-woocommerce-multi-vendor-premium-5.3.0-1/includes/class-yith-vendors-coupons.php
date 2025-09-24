<?php
/**
 * Handle coupon module.
 *
 * @since      4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Coupons' ) ) {
	/**
	 * Shop Coupon handler class
	 */
	class YITH_Vendors_Coupons {

		/**
		 * YITH_Vendor_Coupons constructor.
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			// Add vendor capabilities.
			add_filter( 'yith_wcmv_vendor_additional_capabilities', array( $this, 'add_coupon_capabilities' ) );
			add_filter( 'woocommerce_coupon_get_product_ids', array( $this, 'filter_coupon_product_ids' ), 10, 2 );
		}

		/**
		 * Get coupon capabilities
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_capabilities() {
			return apply_filters(
				'yith_wcmv_get_coupon_capabilities',
				array(
					'edit_shop_coupons'             => true,
					'read_shop_coupons'             => true,
					'delete_shop_coupons'           => true,
					'publish_shop_coupons'          => true,
					'edit_published_shop_coupons'   => true,
					'delete_published_shop_coupons' => true,
					'edit_others_shop_coupons'      => true,
					'delete_others_shop_coupons'    => true,
				)
			);
		}

		/**
		 * Customize coupon module capabilities
		 *
		 * @since  4.0.0
		 * @param array $capabilities Current module capabilities.
		 * @return array
		 */
		public function add_coupon_capabilities( $capabilities ) {
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_coupon_management', 'no' ) ) {
				$capabilities['coupons'] = $this->get_capabilities();
			}

			return $capabilities;
		}

		/**
		 * If is a vendor coupon and there are no products ids set, set all vendor products to restrict coupon usage.
		 *
		 * @since  4.0.0
		 * @param array     $value  An array of coupon products ids.
		 * @param WC_Coupon $coupon Coupon instance.
		 * @return array
		 */
		public function filter_coupon_product_ids( $value, $coupon ) {

			// Exclude admin side (list table).
			$is_list_table = false;
			if ( is_admin() && function_exists( 'get_current_screen' ) ) {
				$screen        = get_current_screen();
				$is_list_table = $screen instanceof WP_Screen && 'edit' === $screen->base && 'shop_coupon' === $screen->post_type;
			}

			if ( empty( $value ) && ! $is_list_table ) {
				$vendor_id = $coupon->get_meta( 'vendor_id', true );
				$vendor    = $vendor_id ? yith_wcmv_get_vendor( absint( $vendor_id ), 'vendor' ) : false;
				if ( $vendor && $vendor->is_valid() ) {
					return $vendor->get_products( array( 'exclude' => $coupon->get_excluded_product_ids() ) );
				}
			}

			return $value;
		}
	}
}
