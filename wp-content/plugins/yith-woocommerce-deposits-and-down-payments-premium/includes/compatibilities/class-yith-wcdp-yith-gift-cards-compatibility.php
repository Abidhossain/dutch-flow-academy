<?php
/**
 * Compatibility class with Gift Cards
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Gift_Cards_Compatibility' ) ) {
	/**
	 * Deposit - Gift card compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_Gift_Cards_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Gift_Cards_Compatibility
		 * @since 1.0.5
		 */
		protected static $instance;

		/**
		 * Constructor method
		 */
		public function __construct() {
			add_filter( 'yith_ywgc_apply_gift_card_discount_before_cart_total', array( $this, 'remove_cart_restore_after_gift_card_processing' ) );
		}

		/**
		 * Prevent plugin from restoring cart after suborder processing, when gift card is applied to the order
		 *
		 * @eturn void
		 * @since 1.0.5
		 */
		public function remove_cart_restore_after_gift_card_processing() {
			if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
				return;
			}

			add_action( 'yith_wcdp_reset_cart_after_suborder_processing', '__return_false' );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Gift_Cards_Compatibility
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
