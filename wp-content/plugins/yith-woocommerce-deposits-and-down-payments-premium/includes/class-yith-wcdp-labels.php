<?php
/**
 * Offers a set of methods to retrieve labels to use on frontend
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Labels' ) ) {
	/**
	 * Collection of methods that returns labels to use on frontend
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Labels {

		/**
		 * Returns label for deposit option/item
		 *
		 * @return string
		 */
		public static function get_deposit_label() {
			$default = __( 'Deposit', 'yith-woocommerce-deposits-and-down-payments' );
			$option  = get_option( 'yith_wcdp_deposit_labels_deposit', $default );

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_label
			 *
			 * Filters the deposit label text.
			 *
			 * @param string Default value: 'Deposit:'.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_deposit_label', $option );
		}

		/**
		 * Returns label for "full price" meta
		 *
		 * @return string
		 */
		public static function get_full_price_label() {
			$default = __( 'Full price', 'yith-woocommerce-deposits-and-down-payments' );
			$option  = get_option( 'yith_wcdp_deposit_labels_full_price_label', $default );

			/**
			 * APPLY_FILTERS: yith_wcdp_full_price_filter
			 *
			 * Filters "Full price" label text.
			 *
			 * @param string Default value: 'Full price'.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_full_price_filter', $option );
		}

		/**
		 * Returns label for "balance" meta
		 *
		 * @return string
		 */
		public static function get_balance_label() {
			$default = __( 'Balance', 'yith-woocommerce-deposits-and-down-payments' );
			$option  = get_option( 'yith_wcdp_deposit_labels_balance_label', $default );

			/**
			 * APPLY_FILTERS: yith_wcdp_balance_filter
			 *
			 * Filters 'Balance' label text
			 *
			 * @param string Default value: 'Balance'.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_balance_filter', $option );
		}

		/**
		 * Returns label for "balance" meta
		 *
		 * @return string
		 */
		public static function get_full_payment_label() {
			$default = __( 'Full payment', 'yith-woocommerce-deposits-and-down-payments' );

			/**
			 * APPLY_FILTERS: yith_wcdp_full_payment_label
			 *
			 * Filters the 'Full payment' label text.
			 *
			 * @param string Default value: 'Full payment'.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_full_payment_label', $default );
		}

		/**
		 * Returns label for "Pay deposit" button
		 *
		 * @return string
		 */
		public static function get_pay_deposit_label() {
			$default = __( 'Pay deposit', 'yith-woocommerce-deposits-and-down-payments' );
			$option  = get_option( 'yith_wcdp_deposit_labels_pay_deposit', $default );

			return apply_filters( 'yith_wcdp_pay_deposit_label', $option );
		}

		/**
		 * Returns label for "Pay full amount" button
		 *
		 * @return string
		 */
		public static function get_pay_full_amount_label() {
			$default = __( 'Pay full amount', 'yith-woocommerce-deposits-and-down-payments' );
			$option  = get_option( 'yith_wcdp_deposit_labels_pay_full_amount', $default );

			return apply_filters( 'yith_wcdp_pay_full_amount_label', $option );
		}

		/**
		 * Returns label for "Partially paid" order status
		 *
		 * @return string
		 */
		public static function get_partially_paid_status_label() {
			$default = __( 'Partially paid', 'yith-woocommerce-deposits-and-down-payments' );
			$option  = get_option( 'yith_wcdp_deposit_labels_partially_paid_status', $default );

			/**
			 * APPLY_FILTERS: yith_wcdp_partially_paid_status
			 *
			 * Filters partially-paid order status.
			 *
			 * @param string             Default value: 'partially-paid'.
			 * @param WC_Order $suborder Suborder.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_partially_paid_status_label', $option );
		}
	}
}
