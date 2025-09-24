<?php
/**
 * AJAX Handler class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Ajax_Handler' ) ) {
	/**
	 * AJAX Handling
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Ajax_Handler {

		/**
		 * Init handlers
		 */
		public static function init() {
			add_action( 'wp_ajax_get_add_deposit_to_cart_template', array( self::class, 'get_add_deposit_to_cart_template' ) );
			add_action( 'wp_ajax_nopriv_get_add_deposit_to_cart_template', array( self::class, 'get_add_deposit_to_cart_template' ) );
		}

		/* === HANDLERS === */

		/**
		 * Return Add deposit to Cart template via Ajax call
		 *
		 * @return void
		 */
		public static function get_add_deposit_to_cart_template() {
			// nonce get_add_deposit_to_cart_template.
			if ( ! check_ajax_referer( 'get_add_deposit_to_cart_template' ) ) {
				die;
			}

			// retrieves data from request.
			$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : false;

			if ( ! $variation_id ) {
				die;
			}

			/**
			 * Product variation
			 *
			 * @var $product \WC_Product_Variation
			 */
			$product    = wc_get_product( $variation_id );
			$product_id = $product->get_parent_id();

			$variation_specific = ! apply_filters( 'yith_wcdp_disable_deposit_variation_option', false, $product_id );
			$form_template      = YITH_WCDP_Frontend::get_instance()->single_add_deposit_to_cart( $variation_specific ? $variation_id : $product_id );

			// template is already escaped.
			echo $form_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			die;
		}
	}
}
