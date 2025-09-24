<?php
/**
 * Compatibility class with WPML
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_WCML_Compatibility' ) ) {
	/**
	 * Deposit - WPML compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_WCML_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_WCML_Compatibility
		 * @since 2.0.0
		 */
		protected static $instance;

		/**
		 * Register previous session currency
		 *
		 * @var string
		 */
		protected $current_currency = '';

		/**
		 * Register current session currency
		 *
		 * @var string
		 */

		protected $previous_currency = '';

		/**
		 * Constructor method
		 */
		public function __construct() {
			add_action( 'wcml_switch_currency', array( $this, 'changed_currency' ), 10, 1 );
			add_action( 'yith_wcdp_wcml_switch_currency', array( $this, 'changed_currency' ), 10, 1 );
			add_filter( 'yith_wcbk_process_multi_currency_price_for_product', array( $this, 'skip_price_change_on_booking_deposits' ), 10, 2 );

			if ( ! is_admin() ) {
				$this->register_current_currency();
			}
		}

		/**
		 * When system detects a currency change, make sure to recalculate all deposit/balance values for items in cart, while retrieving them from session
		 *
		 * @param string $currency New currency code.
		 */
		public function changed_currency( $currency ) {
			$session = WC()->session;

			if ( ! $session ) {
				return;
			}

			$this->previous_currency = $session->get( 'client_currency' );
			$this->current_currency  = $currency;

			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'convert_session_values' ), 120, 2 );
		}

		/**
		 * Convert Deposit/Balance values when system detects a currency change
		 *
		 * @param array $session_data Data retrieved from session.
		 * @param array $values       Raw values read from session.
		 *
		 * @return array Filtered session data.
		 */
		public function convert_session_values( $session_data, $values ) {
			if ( empty( $session_data['deposit'] ) ) {
				return $session_data;
			}

			$session_data['deposit_value']   = apply_filters( 'yith_wcdp_wcml_deposit_value', $this->maybe_convert_value( $session_data['deposit_value'] ), $session_data['product_id'], $session_data['variation_id'], $session_data, $this->current_currency, $this->previous_currency );
			$session_data['deposit_balance'] = apply_filters( 'yith_wcdp_wcml_deposit_balance', $this->maybe_convert_value( $session_data['deposit_balance'] ), $session_data['product_id'], $session_data['variation_id'], $session_data, $this->current_currency, $this->previous_currency );

			if (
				apply_filters( 'yith_wcdp_wcml_process_cart_item_product_change', true, $session_data ) &&
				isset( $values['deposit_value'] )
			) {
				$product = $session_data['data'];

				$product->set_price( $session_data['deposit_value'] );
			}

			return $session_data;
		}

		/**
		 * Tries to covert an amount from one currency to another, using WCML tools
		 *
		 * @param float  $value         Amount to convert.
		 * @param string $from_currency Origin currency code.
		 * @param string $to_currency   Destination currency code.
		 *
		 * @return float Converted amount
		 */
		public function maybe_convert_value( $value, $from_currency = false, $to_currency = false ) {
			if ( ! $from_currency ) {
				$from_currency = $this->previous_currency;
			}

			if ( ! $from_currency ) {
				$from_currency = $this->get_store_currency();
			}

			if ( ! $to_currency ) {
				$to_currency = $this->current_currency;
			}

			if ( ! $from_currency || ! $to_currency || $from_currency === $to_currency ) {
				return $value;
			}

			global $woocommerce_wpml;

			$original_value  = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $value, $from_currency );
			$converted_value = $woocommerce_wpml->multi_currency->prices->convert_price_amount( $original_value, $to_currency );

			return apply_filters( 'yith_wcdp_wcml_convert_value', $converted_value, $original_value, $from_currency, $to_currency );
		}

		/**
		 * Returns default store currency
		 *
		 * @return string Store currency.
		 */
		public function get_store_currency() {
			return function_exists( 'wcml_get_woocommerce_currency_option' ) ? wcml_get_woocommerce_currency_option() : get_woocommerce_currency();
		}

		/**
		 * Prevents this class from converting product price on booking products only
		 * This is required as booking applies its own conversion to product price, and in the end we'd have a double conversion
		 *
		 * @param bool       $proceed Whether to proceed or not.
		 * @param WC_Product $product Product object.
		 *
		 * @return bool Whether to proceed with price change.
		 */
		public function skip_price_change_on_booking_deposits( $proceed, $product ) {
			if ( $product && ( $product->get_meta( 'yith_wcdp_deposit' ) || $product->get_meta( 'yith_wcdp_balance' ) ) ) {
				return false;
			}

			return $proceed;
		}

		/**
		 * Register current currency in session.
		 * If detects currency change, triggers yith_wcdp_wcml_switch_currency action
		 */
		protected function register_current_currency() {
			$session = WC()->session;

			if ( ! $session ) {
				return;
			}

			$default_currency = $this->get_store_currency();
			$currency         = apply_filters( 'wcml_price_currency', $default_currency );
			$saved_currency   = $session->get( 'client_currency' );

			if ( $currency && $saved_currency !== $currency ) {
				do_action( 'yith_wcdp_wcml_switch_currency', $currency );
			}

			$session->set( 'client_currency', $currency );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_WCML_Compatibility
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
