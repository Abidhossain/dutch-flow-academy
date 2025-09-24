<?php
/**
 * Shortcode class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Shortcode' ) ) {
	/**
	 * WooCommerce Deposits Shortcode
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Shortcode {

		/**
		 * Performs all required add_shortcode
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function init() {
			$shortcodes = array(
				'yith_wcdp_deposit_value' => __CLASS__ . '::yith_wcdp_deposit_value', // print deposit price.
			);

			foreach ( $shortcodes as $shortcode => $function ) {
				add_shortcode( $shortcode, $function );
			}
		}

		/**
		 * ShortCode Deposit price
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string
		 * @since 1.0.0
		 */
		public static function yith_wcdp_deposit_value( $atts ) {

			$message    = '';
			$product_id = 0;
			$atts       = shortcode_atts(
				array(
					'product_id' => 0,
				),
				$atts
			);
			extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract

			$product_id = (int) $product_id;

			if ( ! $product_id ) {
				global $product;

				$current_product = $product;
				$product_id      = $product->get_id();
			} else {
				$current_product = wc_get_product( $product_id );
			}

			if ( $current_product instanceof WC_Product ) {
				$parent_id = $current_product->get_parent_id();
				$parent_id = $parent_id ? $parent_id : $product_id;

				/**
				 * APPLY_FILTERS: yith_wcdp_deposit_value
				 *
				 * Filters the deposit value.
				 *
				 * @param mixed               Minimum value.
				 * @param WC_Product $product The product.
				 *
				 * @return mixed
				 */
				$deposit_value = apply_filters( 'yith_wcdp_deposit_value', min( YITH_WCDP_Deposits::get_deposit( $product_id, false, false, 'view' ), $current_product->get_price() ), $product_id, $parent_id, array() );

				if ( $deposit_value ) {
					$message = wc_price( $deposit_value );
				}
			}

			return $message;
		}
	}
}
