<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * YWGC_WC_Multi_Currency_Module class
 *
 * @package YITH\GiftCards\Classes\Compatibilities
 */

use WOOMC\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_WC_Multi_Currency_Module' ) ) {
	/**
	 * Class to manage the integration with WooCommerce Currency Switcher
	 *
	 * @class   YWGC_WC_Multi_Currency_Module
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YWGC_WC_Multi_Currency_Module {

		/**
		 * Single instance of the class
		 *
		 * @var YWGC_WC_Multi_Currency_Module
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			// Don't convert the gift card amount in the add to cart.
			add_filter( 'woocommerce_multicurrency_pre_product_get_price', array( $this, 'block_cart_conversion' ), 10, 3 );

			add_filter( 'yith_ywgc_gift_card_coupon_amount', array( $this, 'apply_gift_card_in_customer_currency' ) );

			add_filter( 'ywgc_get_gift_card_balance_amount_for_coupon', array( $this, 'apply_gift_card_in_customer_currency' ) );

			add_filter( 'ywgc_amount_to_deduct_in_gift_card_from_coupon', array( $this, 'apply_gift_card_in_customer_currency' ) );

			add_filter( 'yith_ywgc_gift_card_amount_before_deduct', array( $this, 'yith_ywgc_gift_card_amount_before_deduct' ) );

			add_filter( 'yith_wcgc_template_formatted_price', array( $this, 'yith_wcgc_template_formatted_price' ), 10, 2 );

			add_filter( 'yith_wcgc_table_get_balance', array( $this, 'yith_wcgc_table_get_balance' ), 10, 2 );

			add_filter( 'yith_ywgc_get_gift_card_price', array( $this, 'yith_ywgc_get_gift_card_price_call_back' ) );
		}

		/**
		 * Block cart conversion
		 *
		 * @param float      $pre_value Previous value.
		 * @param float      $value     Current value.
		 * @param WC_Product $product   Product object.
		 */
		public function block_cart_conversion( $pre_value, $value, $product ) {
			if ( is_object( $product ) && $product->is_type( 'gift-card' ) ) {
				$pre_value = $value;
			}

			return $pre_value;
		}

		/**
		 * Convert the gift card to apply the amount in the customer currency
		 *
		 * @param float $gift_card_balance The gift card balance.
		 */
		public function apply_gift_card_in_customer_currency( $gift_card_balance ) {
			$currency_detector = new \WOOMC\Currency\Detector();

			$conversion_api = new \WOOMC\API();

			$to   = $currency_detector->currency();
			$from = $currency_detector->getDefaultCurrency();

			$gift_card_balance = $conversion_api->convert( $gift_card_balance, $to, $from );

			return $gift_card_balance;
		}

		/**
		 * Convert gift card amount before deducting
		 *
		 * @param float $amount Amount to convert.
		 *
		 * @return float
		 */
		public function yith_ywgc_gift_card_amount_before_deduct( $amount ) {
			$currency_detector = new \WOOMC\Currency\Detector();

			$conversion_api = new \WOOMC\API();

			$from = $currency_detector->currency();
			$to   = $currency_detector->getDefaultCurrency();

			$amount = $conversion_api->convert( $amount, $to, $from );

			return $amount;
		}

		/**
		 * Convert the gift card balance
		 *
		 * @param string $formatted_price Formatted price.
		 * @param object $gift_card       Gift card object.
		 *
		 * @return string
		 */
		public function yith_wcgc_template_formatted_price( $formatted_price, $gift_card ) {
			$currency_detector = new \WOOMC\Currency\Detector();
			$conversion_api    = new \WOOMC\API();
			$order             = wc_get_order( $gift_card->order_id );

			if ( ! $order || ! $order instanceof WC_Order ) {
				return $formatted_price;
			}

			$from = $currency_detector->getDefaultCurrency();
			$to   = $order->get_meta( '_order_currency' );

			$formatted_price = $conversion_api->convert( $gift_card->total_amount, $to, $from );

			return wc_price( $formatted_price, array( 'currency' => $to ) );
		}

		/**
		 * Convert the gift card balance
		 *
		 * @param string $formatted_price Formatted price.
		 * @param object $gift_card       Gift card object.
		 *
		 * @return string
		 */
		public function yith_wcgc_table_get_balance( $formatted_price, $gift_card ) {
			$currency_detector = new \WOOMC\Currency\Detector();

			return wc_price( $gift_card->get_balance(), array( 'currency' => $currency_detector->getDefaultCurrency() ) );
		}

		/**
		 * Get the gift card amount converted
		 *
		 * @param float $amount Amount to convert.
		 *
		 * @return float
		 */
		public function yith_ywgc_get_gift_card_price_call_back( $amount ) {
			$currency_detector = new \WOOMC\Currency\Detector();

			$conversion_api = new \WOOMC\API();

			$to   = $currency_detector->currency();
			$from = $currency_detector->getDefaultCurrency();

			$amount = $conversion_api->convert( $amount, $to, $from );

			return $amount;
		}
	}
}

YWGC_WC_Multi_Currency_Module::get_instance();
