<?php
/**
 * Compatibility class with WC Aelia Currency Switcher
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_WC_Aelia_Currency_Switcher_Compatibility' ) ) {
	/**
	 * Deposit - Aelia compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_WC_Aelia_Currency_Switcher_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_WC_Aelia_Currency_Switcher_Compatibility
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
			add_action( 'yith_wcdp_aelia_switch_currency', array( $this, 'changed_currency' ), 10, 1 );

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

			$session_data['deposit_value']   = apply_filters( 'yith_wcdp_aelia_deposit_value', $this->maybe_convert_value( $session_data['deposit_value'] ), $session_data['product_id'], $session_data['variation_id'], $session_data, $this->current_currency, $this->previous_currency );
			$session_data['deposit_balance'] = apply_filters( 'yith_wcdp_aelia_deposit_balance', $this->maybe_convert_value( $session_data['deposit_balance'] ), $session_data['product_id'], $session_data['variation_id'], $session_data, $this->current_currency, $this->previous_currency );

			if (
				apply_filters( 'yith_wcdp_aelia_process_cart_item_product_change', true, $session_data ) &&
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

			$converted_value = apply_filters( 'wc_aelia_cs_convert', $value, $from_currency, $to_currency );

			return apply_filters( 'yith_wcdp_aelia_convert_value', $converted_value, $value, $from_currency, $to_currency );
		}

		/**
		 * Returns default store currency
		 *
		 * @return string Store currency.
		 */
		public function get_store_currency() {
			return WC_Aelia_CurrencySwitcher::instance()->base_currency();
		}

		/**
		 * Register current currency in session.
		 * If detects currency change, triggers yith_wcdp_aelia_switch_currency action
		 */
		protected function register_current_currency() {
			$session = WC()->session;

			if ( ! $session ) {
				return;
			}

			$currency       = WC_Aelia_CurrencySwitcher::instance()->get_selected_currency();
			$saved_currency = $session->get( 'client_currency' );

			if ( $currency && $saved_currency && $saved_currency !== $currency ) {
				do_action( 'yith_wcdp_aelia_switch_currency', $currency );
			}

			$session->set( 'client_currency', $currency );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_WC_Aelia_Currency_Switcher_Compatibility
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
