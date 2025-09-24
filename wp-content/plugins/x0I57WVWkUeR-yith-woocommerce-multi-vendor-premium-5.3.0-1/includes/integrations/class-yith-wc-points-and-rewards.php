<?php
/**
 * YITH_WooCommerce_Points_And_Rewards_Support class
 *
 * @since      1.11.4
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

if ( ! class_exists( 'YITH_WooCommerce_Points_And_Rewards_Support' ) ) {
	/**
	 * Handle support to YITH WooCommerce Points and Rewards
	 *
	 * @class      YITH_WooCommerce_Points_And_Rewards_Support
	 * @since      1.7
	 * @package YITH\MultiVendor
	 */
	class YITH_WooCommerce_Points_And_Rewards_Support {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Construct
		 */
		private function __construct() {
			die();
			add_action( 'woocommerce_order_status_pending_to_completed', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_failed_to_completed', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'prevent_double_points' ), 5, 2 );
			add_action( 'woocommerce_payment_complete', array( $this, 'prevent_double_points' ), 5, 1 );
		}

		/**
		 * Prevent double points from vendor suborder
		 * If a vendor suborder change their status no points are assign to customer
		 *
		 * @param integer  $order_id The order id.
		 * @param WC_Order $order    (Optional) The order object.
		 * @return void
		 */
		public function prevent_double_points( $order_id, $order = null ) {
			global $wc_points_rewards;

			if ( ! is_null( $order ) ) {
				$order = wc_get_order( $order_id );
			}

			// Skip guest user.
			if ( ! $order || ! $order->get_user_id() ) {
				return;
			}

			$parent_order_id = $order->get_parent_id();
			if ( $parent_order_id ) {
				remove_action( current_action(), array( $wc_points_rewards->order, 'add_points_earned' ) );
			}
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.7
 * @return YITH_WooCommerce_Points_And_Rewards_Support
 */
if ( ! function_exists( 'YITH_WooCommerce_Points_And_Rewards_Support' ) ) {
	function YITH_WooCommerce_Points_And_Rewards_Support() { // phpcs:ignore
		return YITH_WooCommerce_Points_And_Rewards_Support::instance();
	}
}

YITH_WooCommerce_Points_And_Rewards_Support();
