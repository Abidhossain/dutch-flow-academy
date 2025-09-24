<?php
/**
 * YITH_Vendors_Gateway_Legacy
 * Define methods and properties for class that manages admin payments
 *
 * @class      YITH_Vendors_Gateway_Legacy
 * @since      Version 4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway_Legacy' ) ) {

	abstract class YITH_Vendors_Gateway_Legacy {

		/**
		 * The default gateway id.
		 *
		 * @var string
		 */
		protected static $default_gateway_id = 'manual-payments';

		/**
		 * Array of instances of the class, one for each available gateway
		 *
		 * @var array Array of instances of the class.
		 */
		public static $instances = array();

		/**
		 * Add PayPal Payouts options array from this plugin.
		 *
		 * @param array $options Admin panel options.
		 * @return array
		 * @deprecated
		 */
		public static function add_section_options( $options ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $options;
		}

		/**
		 * Add Stripe Connect Section
		 *
		 * @param array $sections Admin panel sections.
		 * @return array
		 * @deprecated
		 */
		public static function add_section( $sections ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $sections;
		}

		/**
		 * Add payouts gateway options
		 *
		 * @return array
		 * @deprecated
		 */
		public static function get_options_array() {
			_deprecated_function( __METHOD__, '4.0.0' );
			return array();
		}

		/**
		 * Retrieve the paypal options array from this plugin.
		 *
		 * @return array paypal option array
		 */
		public static function get_paypal_options_array() {
			_deprecated_function( __METHOD__, '4.0.0' );
			return array();
		}

		/**
		 * Handle the single commission from commission list
		 *
		 * @since  1.0.0
		 * @deprecated
		 */
		public function handle_single_commission_pay() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$commission_id = ! empty( $_GET['commission_id'] ) ? absint( $_GET['commission_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$redirect_args = array();

			$this->handle_commissions_payment( array( $commission_id ), $redirect_args );

			wp_safe_redirect( add_query_arg( $redirect_args, wp_get_referer() ) );
			exit();
		}

		/**
		 * Handle the massive commission from commission list
		 *
		 * @since  1.0.0
		 * @param YITH_Vendor $vendor         Vendor object.
		 * @param array       $commission_ids An array of commissions to pay.
		 * @param string      $action         Current action.
		 */
		public function handle_massive_commissions_pay( $vendor, $commission_ids, $action ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			$redirect_args = array();
			$this->handle_commissions_payment( $commission_ids, $redirect_args );

			wp_safe_redirect( add_query_arg( $redirect_args, wp_get_referer() ) );
			exit();
		}

		/**
		 * Handle commissions payment action
		 *
		 * @since  4.0.0
		 * @param array $commission_ids An array of commission ids to process.
		 * @param array $redirect_args  Redirect url arguments.
		 * @return boolean True on success, false othrwise.
		 */
		public function handle_commissions_payment( $commission_ids, &$redirect_args ) {
			return false;
		}

		/**
		 * Process commissions payment for given commissions array
		 *
		 * @since  4.0.0
		 * @param integer|string|array $commission_ids An array of commission ids or a single commission to process.
		 * @return boolean True on success, false otherwise.
		 */
		public function process_commissions_payment( $commission_ids ) {
			return false;
		}

		/**
		 * Pay single commission
		 *
		 * @since  1.0.0
		 * @param integer $commission_id The commission id.
		 * @return array
		 */
		public function pay_commission( $commission_id ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			$res = $this->process_commissions_payment( array( $commission_id ) );

			return array(
				'status'   => $res,
				'messages' => '',
			);
		}

		/**
		 * Pay massive commission
		 *
		 * @param array|string $commission_ids     An array of commission ids.
		 * @param string       $action             The action.
		 * @param string       $transaction_status (Optional) Transaction status. Processing by default.
		 * @return array
		 */
		public function pay_massive_commissions( $commission_ids, $action, $transaction_status = 'processing' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			$res = $this->process_commissions_payment( $commission_ids );

			return array(
				'status'   => $res,
				'messages' => '',
			);
		}

		/**
		 * Show Pay Button for MassPay service
		 *
		 * @since  1.0.0
		 * @param YITH_Commission $commission The commission to pay.
		 * @return void
		 */
		public function add_button( $commission ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Extend the data from get_pay_data() method
		 *
		 * @since  1.0.0
		 * @param mixed $pay_data Array argument to pay.
		 * @return array
		 * @deprecated
		 */
		protected function get_pay_data_extra_args( $pay_data ) {
			return $pay_data;
		}
	}
}
