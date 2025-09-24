<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * Class to manage the integration with Aelia Currency Switcher
 *
 * @package YITH\GiftCards\Classes\Compatibilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_AeliaCS_Module' ) ) {
	/**
	 * YWGC_AeliaCS_Module class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YWGC_AeliaCS_Module {

		/**
		 * Single instance of the class
		 *
		 * @var YWGC_AeliaCS_Module
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Shop's base currency. Used for caching.
		 *
		 * @var string
		 * @since 1.0.6
		 */
		protected static $base_currency;

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
			/**
			 * Aelia  Multi-currency support
			 */
			add_filter( 'wc_aelia_currencyswitcher_product_convert_callback', array( $this, 'wc_aelia_currencyswitcher_product_convert_callback' ), 10, 2 );

			/**
			 * Show the amount of the gift card in the PDF and email using the user currency
			 */
			add_filter( 'yith_ywgc_gift_card_template_amount', array( $this, 'get_amount_in_gift_card_currency_for_email_and_pdf' ), 10, 2 );

			/**
			 * Set the amount from gift card in my account
			 */
			add_filter( 'yith_ywgc_get_gift_card_price', array( $this, 'convert_base_currency_amount_to_user_currency' ), 10, 2 );

			add_filter( 'yith_ywgc_gift_card_coupon_amount', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );
			add_filter( 'ywgc_get_gift_card_balance_amount_for_coupon', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );
			add_filter( 'ywgc_amount_to_deduct_in_gift_card_from_coupon', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );
			add_filter( 'yith_ywgc_new_balance_before_update_balance', array( $this, 'yith_convert_new_balance' ), 10, 3 );

			add_action( 'woocommerce_before_calculate_totals', array( $this, 'yith_ywgc_convert_price_in_cart' ), 1000 );
		}

		/**
		 * Convert the gift card amount to be displayed in the email and pdf
		 *
		 * @param float                  $amount    Gift card amount.
		 * @param YWGC_Gift_Card_Premium $gift_card Gift card object.
		 *
		 * @return float
		 * @since  1.0.0
		 */
		public function get_amount_in_gift_card_currency_for_email_and_pdf( $amount, $gift_card ) {
			return wc_price( $gift_card->get_balance(), array( 'currency' => $gift_card->currency ) );
		}

		/**
		 * Callback to support currency conversion of Gift Card products.
		 *
		 * @param callable   $callback The original callback passed by the Currency
		 *                             Switcher.
		 * @param WC_Product $product  The product to convers.
		 *
		 * @return callable The callback that will perform the conversion.
		 * @since  1.0.6
		 */
		public function wc_aelia_currencyswitcher_product_convert_callback( $callback, $product ) {
			if ( $product instanceof WC_Product_Gift_Card ) {
				$callback = array( $this, 'convert_gift_card_prices' );
			}

			return $callback;
		}

		/**
		 * Converts the prices of a gift card product to the specified currency.
		 *
		 * @param WC_Product_Gift_Card $product  A variable product.
		 * @param string               $currency A currency code.
		 *
		 * @return WC_Product_Gift_Card The product with converted prices.
		 * @since  1.0.6
		 */
		public function convert_gift_card_prices( $product, $currency ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
			$min_price = $product->get_meta( 'min_price' );
			$max_price = $product->get_meta( 'max_price' );

			$product->update_meta_data( 'min_price', $this->get_amount_in_currency( $min_price ) );
			$product->update_meta_data( 'max_price', $this->get_amount_in_currency( $max_price ) );

			$amounts = $product->get_product_amounts();

			if ( $amounts ) {
				foreach ( $amounts as $idx => $amount ) {
					$amounts[ $idx ] = $this->get_amount_in_currency( $amounts[ $idx ] );
				}

				$product->set_amounts( $amounts );
			}

			$product_price = $product->get_meta( 'price' );

			if ( ! empty( $product_price ) ) {
				$product->update_meta_data( 'price', $this->get_amount_in_currency( $product_price ) );
			}

			$product->save_meta_data();

			return $product;
		}

		/**
		 * Convert the amount from base currency to current currency
		 *
		 * @param float                  $amount    Gift card amount.
		 * @param YWGC_Gift_Card_Premium $gift_card Gift card object.
		 *
		 * @return float
		 * @since  1.0.0
		 */
		public function convert_base_currency_amount_to_user_currency( $amount, $gift_card ) {
			if ( ! empty( $gift_card->currency ) ) {
				return self::get_amount_in_currency( $amount, null, $gift_card->currency );
			}

			return self::get_amount_in_currency( $amount );
		}

		/**
		 * Convenience method. Returns WooCommerce base currency.
		 *
		 * @return string
		 * @since 1.0.6
		 */
		public static function base_currency() {
			if ( empty( self::$base_currency ) ) {
				self::$base_currency = get_option( 'woocommerce_currency' );
			}

			return self::$base_currency;
		}

		/**
		 * Basic integration with WooCommerce Currency Switcher, developed by Aelia
		 * (https://aelia.co). This method can be used by any 3rd party plugin to
		 * return prices converted to the active currency.
		 *
		 * @param double $amount        The source price.
		 * @param string $to_currency   The target currency. If empty, the active currency
		 *                              will be taken.
		 * @param string $from_currency The source currency. If empty, WooCommerce base
		 *                              currency will be taken.
		 *
		 * @return double The price converted from source to destination currency.
		 * @link   https://aelia.co
		 * @since  1.0.6
		 */
		public static function get_amount_in_currency( $amount, $to_currency = null, $from_currency = null ) {
			if ( empty( $from_currency ) ) {
				$from_currency = self::base_currency();
			}

			if ( empty( $to_currency ) ) {
				$to_currency = get_woocommerce_currency();
			}

			return apply_filters( 'wc_aelia_cs_convert', $amount, $from_currency, $to_currency );
		}

		/**
		 * Convert the gift card amount in the gift card currency to the customer selected currency
		 *
		 * @param float  $gift_card_balance The gift card balance.
		 * @param object $gift_card         The gift card object.
		 */
		public function yith_convert_from_gift_card_currency_to_selected_currency( $gift_card_balance, $gift_card ) {
			$currency_id = get_woocommerce_currency();

			if ( $currency_id === $gift_card->currency ) {
				return $gift_card_balance;
			} else {
				$gift_card_balance = self::get_amount_in_currency( $gift_card_balance, $currency_id, $gift_card->currency );
			}

			return $gift_card_balance;
		}

		/**
		 * Filter the new balance of the gift card in the correct currency
		 *
		 * @param float  $new_balance The new gift card balance.
		 * @param object $gift_card   The gift card object.
		 * @param float  $amount      The gift card amount.
		 */
		public function yith_convert_new_balance( $new_balance, $gift_card, $amount ) {
			$currency_id = get_woocommerce_currency();

			if ( $currency_id === $gift_card->currency ) {
				return $new_balance;
			} else {
				$amount = self::get_amount_in_currency( $amount, $gift_card->currency, $currency_id );

				$new_balance = $gift_card->get_balance() - $amount;
			}

			return $new_balance;
		}

		/**
		 * Updates cart item price for gift cards
		 *
		 * @param WC_Cart $cart Cart object.
		 */
		public function yith_ywgc_convert_price_in_cart( $cart ) {
			foreach ( $cart->get_cart_contents() as $cart_item ) {

				if ( ! isset( $cart_item['ywgc_product_id'] ) ){
					continue;
				}
				
				$product = wc_get_product( $cart_item['ywgc_product_id'] );

				if ( is_object( $product ) && $product instanceof WC_Product_Gift_Card ) {
					$amount             = $cart_item['ywgc_amount'];
					$purchased_currency = $cart_item['ywgc_currency'];
					$selected_currency  = get_woocommerce_currency();
					$amount_converted   = self::get_amount_in_currency( $amount, $selected_currency, $purchased_currency );

					$cart_item['ywgc_amount'] = $amount_converted;

					$cart_item['data']->set_price( $amount_converted );
				}
			}
		}
	}
}

YWGC_AeliaCS_Module::get_instance();
