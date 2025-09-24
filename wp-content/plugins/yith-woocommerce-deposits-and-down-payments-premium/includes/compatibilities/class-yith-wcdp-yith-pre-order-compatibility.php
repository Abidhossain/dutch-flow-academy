<?php
/**
 * Compatibility class with Pre Order
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Deposits / Down Payments
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Pre_Order_Compatibility' ) ) {
	/**
	 * Deposit - Pre order compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_Pre_Order_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Pre_Order_Compatibility
		 * @since 1.0.5
		 */
		protected static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.0.5
		 */
		public function __construct() {
			add_filter( 'yith_wcpo_return_original_price', array( $this, 'return_deposit_price' ), 10, 2 );
		}

		/**
		 * Tells Pre Order to return original product price, if deposit is applied
		 *
		 * @param bool       $return_original_price Whether to return original product price.
		 * @param WC_Product $product               Product object.
		 *
		 * @return bool
		 * @since 1.0.5
		 */
		public function return_deposit_price( $return_original_price, $product ) {
			if ( $product->get_meta( 'yith_wcdp_deposit' ) || $product->get_meta( 'yith_wcdp_balance' ) ) {
				$return_original_price = true;
			}

			return $return_original_price;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Pre_Order_Compatibility
		 * @since 1.0.5
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
