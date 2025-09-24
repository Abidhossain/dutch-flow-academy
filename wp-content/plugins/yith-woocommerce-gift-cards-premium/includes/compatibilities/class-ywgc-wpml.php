<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * YWGC_YITH_WCMCS_Module class
 *
 * @package YITH\GiftCards\Classes\Compatibilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_WPML' ) ) {
	/**
	 * Class to manage the integration with WPML
	 *
	 * @class   YWGC_WPML
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YWGC_WPML {

		/**
		 * Single instance of the class
		 *
		 * @var YWGC_WPML
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
			/**
			 * Convert gift card amounts shown on product page according to current WPML currency
			 */
			add_filter( 'yith_ywgc_gift_card_amounts', array( $this, 'get_wpml_multi_currency' ) );

			/**
			 * Retrieve the array data key for the subtotal in the current currency
			 */
			add_filter( 'yith_ywgc_line_subtotal', array( $this, 'line_subtotal' ) );

			/**
			 * Retrieve the array data key for the subtotal tax in the  current currency
			 */
			add_filter( 'yith_ywgc_line_subtotal_tax', array( $this, 'line_subtotal_tax' ) );

			/**
			 * Set the amount from customer currency to base currency
			 */
			add_filter( 'yith_ywgc_gift_card_amount_before_deduct', array( $this, 'convert_to_base_currency' ) );

			/**
			 * Show the amount of the gift card using the user currency
			 */
			add_filter( 'yith_ywgc_gift_card_template_amount', array( $this, 'get_amount_in_gift_card_currency' ), 10, 3 );

			add_filter( 'yith_ywgc_gift_card_coupon_amount', array( $this, 'convert_to_user_currency' ) );
			add_filter( 'yith_ywgc_gift_card_amount_thank_you_page', array( $this, 'convert_to_user_currency' ) );

			add_filter( 'yith_wcgc_table_get_balance', array( $this, 'display_base_currency_symbol_in_table' ), 10, 2 );

			add_filter( 'yith_ywgc_get_gift_card_price', array( $this, 'convert_unrounded_amount_to_user_currency' ), 10, 2 );

			add_filter( 'ywgc_gift_card_amount_order_total_item', array( $this, 'get_amount_in_order_currency' ), 10, 2 );

			add_filter( 'yith_ywgc_cart_totals_gift_card_amount', array( $this, 'get_amount_in_customer_currency_cart_totals' ), 10, 2 );

			add_action( 'woocommerce_before_calculate_totals', array( $this, 'yith_ywgc_convert_price_in_cart' ), 1000 );
		}

		/**
		 * Convert gift card amounts shown on product page according to current WPML currency
		 *
		 * @param array $amounts amounts to be shown.
		 *
		 * @return array
		 */
		public function get_wpml_multi_currency( $amounts ) {
			if ( $amounts ) {
				$multi_currency_amounts = array();

				foreach ( $amounts as $amount ) {
					$multi_currency_amounts[] = apply_filters( 'wcml_raw_price_amount', $amount );
				}

				return $multi_currency_amounts;
			}

			return $amounts;
		}

		/**
		 * Get the gift card amounts in the correct currency
		 *
		 * @param float                  $price amount to be displayed.
		 * @param YWGC_Gift_Card_Premium $gift_card the gift card product.
		 * @param float                  $amount amount to be converted.
		 *
		 * @return float
		 * @since  1.0.0
		 */
		public function get_amount_in_gift_card_currency( $price, $gift_card, $amount ) {
			if ( $gift_card->currency ) {
				$unrounded_amount = get_post_meta( $gift_card->ID, '_ywgc_default_currency_amount', true );

				if ( ! empty( $unrounded_amount ) ) {
					$amount = $unrounded_amount;
				}

				$price = wc_price(
					apply_filters( 'wcml_raw_price_amount', $amount, $gift_card->currency ),
					apply_filters(
						'yith_wcgc_param_array_format_price',
						array(
							'currency' => $gift_card->currency,
						)
					)
				);
			}

			return $price;
		}

		/**
		 * Convert an amount to the user currency
		 *
		 * @param float $price amount to be converted.
		 *
		 * @return float
		 */
		public function convert_to_user_currency( $price ) {
			global $woocommerce_wpml;

			if ( $woocommerce_wpml->multi_currency ) {
				$currency = $woocommerce_wpml->multi_currency->get_client_currency();

				if ( get_option( 'woocommerce_currency' ) !== $currency ) {
					$price = apply_filters( 'wcml_raw_price_amount', $price );
				}
			}

			return $price;
		}

		/**
		 * Convert the default gift card amount, without round, to the user currency
		 *
		 * @param float                  $price amount to be converted.
		 * @param YWGC_Gift_Card_Premium $gift_card the gift card product.
		 *
		 * @return float
		 */
		public function convert_unrounded_amount_to_user_currency( $price, $gift_card ) {
			global $woocommerce_wpml;

			if ( $woocommerce_wpml->multi_currency ) {
				$currency = $woocommerce_wpml->multi_currency->get_client_currency();

				if ( get_option( 'woocommerce_currency' ) !== $currency ) {
					$unrounded_amount = get_post_meta( $gift_card->ID, '_ywgc_default_currency_amount', true );

					if ( ! empty( $unrounded_amount ) ) {
						$price = $unrounded_amount;
					}

					$price = apply_filters( 'wcml_raw_price_amount', $price );
				}
			}

			return $price;
		}

		/**
		 * Convert gift card amount to the order currency
		 *
		 * @param float                  $amount amount to be converted.
		 * @param YWGC_Gift_Card_Premium $gift_card the gift card product.
		 *
		 * @return float
		 */
		public function get_amount_in_order_currency( $amount, $gift_card ) {
			if ( $gift_card->currency ) {
				$amount = apply_filters( 'wcml_raw_price_amount', $amount, $gift_card->currency );
			}

			return $amount;
		}

		/**
		 * Display the base currency simbol in the gift card dashboard
		 *
		 * @param float                  $price amount to be converted.
		 * @param YWGC_Gift_Card_Premium $gift_card the gift card product.
		 *
		 * @return float
		 */
		public function display_base_currency_symbol_in_table( $price, $gift_card ) {
			$price = wc_price( $gift_card->get_balance(), array( 'currency' => get_option( 'woocommerce_currency' ) ) );

			return $price;
		}

		/**
		 * Convert the default currency amount to the user in the cart totals
		 *
		 * @param float  $amount amount to be converted.
		 * @param string $code the code of the gift card applied.
		 *
		 * @return float
		 */
		public function get_amount_in_customer_currency_cart_totals( $amount, $code ) {
			$gift_card = YITH_YWGC()->get_gift_card_by_code( $code );

			$amount = apply_filters( 'wcml_raw_price_amount', - $gift_card->get_balance() );

			return $amount;
		}


		/**
		 * Convert an amount to the base currency
		 *
		 * @param float $price amount to be converted.
		 *
		 * @return float
		 */
		public function convert_to_base_currency( $price ) {
			global $woocommerce_wpml;

			if ( $woocommerce_wpml->multi_currency ) {
				$price = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $price );
			}

			return $price;
		}

		/**
		 * Retrieve the array data key for the subtotal in the current currency
		 *
		 * @param float $amount Amount to be converted.
		 */
		public function line_subtotal( $amount ) {
			return $this->convert_to_base_currency( $amount );
		}

		/**
		 * Retrieve the array data key for the subtotal in the current currency
		 *
		 * @param float $amount Amount to be converted.
		 */
		public function line_subtotal_tax( $amount ) {
			return $this->convert_to_base_currency( $amount );
		}

		/**
		 * Updates cart item price for gift cards
		 *
		 * @param WC_Cart $cart Cart object.
		 */
		public function yith_ywgc_convert_price_in_cart( $cart ) {
			foreach ( $cart->get_cart_contents() as $cart_item ) {
				if ( ! isset( $cart_item['ywgc_product_id'] ) ) {
					continue;
				}
				
				$product = wc_get_product( $cart_item['ywgc_product_id'] );

				if ( is_object( $product ) && $product instanceof WC_Product_Gift_Card ) {
					$default_currency_amount = ! empty( $cart_item['ywgc_default_currency_amount'] ) ? $cart_item['ywgc_default_currency_amount'] : $cart_item['ywgc_amount'];

					// Include the addons price to the gift card amount TODO: implement this in separated class for the addons integration
					if ( defined( 'YITH_WAPO_PREMIUM' ) && isset( $cart_item['yith_wapo_total_options_price'] ) ){
						$default_currency_amount += $cart_item['yith_wapo_total_options_price'];
					}

					$selected_currency       = get_woocommerce_currency();

					if ( wcml_get_woocommerce_currency_option() === $selected_currency ) {
						$amount = $default_currency_amount;
					} else {
						$amount = apply_filters( 'wcml_raw_price_amount', $default_currency_amount );
					}

					$cart_item['ywgc_amount'] = $amount;

					$cart_item['data']->set_price( $amount );
				}
			}
		}
	}
}

YWGC_WPML::get_instance();
