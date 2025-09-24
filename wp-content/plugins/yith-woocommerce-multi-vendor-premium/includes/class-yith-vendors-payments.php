<?php
/**
 * YITH_Vendors_Payments class
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Payments' ) ) {
	/**
	 * Manage Multi Vendor Payments tables
	 */
	class YITH_Vendors_Payments {

		/**
		 * Class construct
		 */
		public function __construct() {}

		/**
		 * Commissions API - set table name
		 *
		 * @since  1.6.0
		 * @return void
		 * @deprecated
		 */
		public function add_transaction_table_wpdb() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Install::define_tables' );
			YITH_Vendors_Install::define_tables();
		}

		/**
		 * Get all commissions id by payment
		 *
		 * @since  1.6.0
		 * @param integer $payment_id The payment ID.
		 * @param string  $status     The payments status.
		 * @return array
		 */
		public function get_commissions_by_payment_id( $payment_id, $status = 'processing' ) {
			global $wpdb;

			$commission_ids = array();
			if ( $payment_id ) {
				$query = $wpdb->prepare(
					"SELECT relationship.commission_id AS commission_id FROM {$wpdb->payments_relationship} AS relationship 
    JOIN {$wpdb->payments} AS payments ON relationship.payment_id = payments.ID AND payments.status = %s AND relationship.payment_id = %d",
					$status,
					$payment_id
				);

				$commission_ids = $wpdb->get_col( $query );  // phpcs:ignore
			}

			return $commission_ids;
		}

		/**
		 * Validate given payment status
		 *
		 * @since  4.0.0
		 * @param string $status The status to validate.
		 * @return boolean
		 */
		protected function is_status_valid( $status ) {
			// Validate status.
			$valid_statuses = array(
				'processing',
				'paid',
				'unpaid',
				'refunded',
				'failed',
			);

			return in_array( $status, $valid_statuses, true );
		}

		/**
		 * Update payment status
		 *
		 * @since  1.6.0
		 * @param integer $payment_id The payment ID.
		 * @param string  $status     The payment status.
		 * @return boolean
		 */
		public function update_payment_status( $payment_id, $status ) {
			global $wpdb;

			if ( ! empty( $payment_id ) && $this->is_status_valid( $status ) ) {
				return $wpdb->update( $wpdb->payments, array( 'status' => $status ), array( 'ID' => $payment_id ) ); // phpcs:ignore
			}

			return false;
		}

		/**
		 * Register vendor payments relationship into database
		 *
		 * @since  1.6.0
		 * @param integer $payment_id    The payment ID.
		 * @param integer $commission_id The commission ID.
		 */
		public function add_vendor_payment_relationship( $payment_id, $commission_id ) {
			global $wpdb;

			if ( ! empty( $payment_id ) && ! empty( $commission_id ) ) {
				$wpdb->insert( // phpcs:ignore
					$wpdb->payments_relationship,
					array(
						'payment_id'    => $payment_id,
						'commission_id' => $commission_id,
					)
				);
			}
		}

		/**
		 * Register Vendor payments into database
		 * $payment is an array with these keys array( 'vendor_id', 'user_id','amount', 'status', 'payment_date', 'payment_date_gmt' )
		 * The status can be : paid|failed|processing
		 *
		 * @since   1.6.0
		 * @param array $payment Array of payments data.
		 * @return integer|boolean
		 */
		public function add_vendor_payments_log( $payment ) {

			global $wpdb;

			$status = ( ! empty( $payment['status'] ) && $this->is_status_valid( $payment['status'] ) ) ? $payment['status'] : '';
			if ( ! empty( $payment['vendor_id'] ) && ! empty( $payment['user_id'] ) && ! empty( $payment['amount'] ) && ! empty( $payment['currency'] ) && $status ) {
				if ( $wpdb->insert( $wpdb->payments, $payment ) ) { // phpcs:ignore
					return $wpdb->insert_id;
				}
			}

			return false;
		}

		/**
		 * Add note to payments
		 *
		 * @since  1.6.0
		 * @param integer $payment_id The payment ID.
		 * @param string  $note       The note to add.
		 * @return void
		 */
		public function add_note( $payment_id, $note ) {
			global $wpdb;

			if ( ! empty( $payment_id ) && ! empty( $note ) ) {
				$wpdb->update( $wpdb->payments, array( 'note' => serialize( $note ) ), array( 'ID' => $payment_id ) ); // phpcs:ignore
			}
		}

		/**
		 * Register Vendor payments and relationship into database
		 * $payment is an array with these keys array( 'vendor_id', 'user_id','amount', 'status', 'payment_date', 'payment_date_gmt' )
		 * The status can be : paid|failed|processing
		 *
		 * @param array $args Array of payments data.
		 * @return integer|boolean
		 */
		public function add_payment( $args ) {
			$payment_id = 0;
			if ( ! empty( $args['payment'] ) ) {
				$payment_id = $this->add_vendor_payments_log( $args['payment'] );
			}

			if ( ! empty( $args['commission_ids'] ) && ! empty( $payment_id ) ) {
				$commission_ids = $args['commission_ids'];
				foreach ( $commission_ids as $commission_id ) {
					$this->add_vendor_payment_relationship( $payment_id, $commission_id );
				}
			}

			return $payment_id;
		}

		/**
		 * Get payment details by commissions id.
		 *
		 * @since   1.6.0
		 * @param integer|array $commission_id A single commission ID or an array of commissions id.
		 * @param string|array  $select        Which fields select from query. Can be an array or a single string.
		 * @param array         $where         An array of query conditions.
		 * @return mixed
		 */
		public function get_payments_details_by_commission_ids( $commission_id, $select = array(), $where = array() ) {

			global $wpdb;
			$select_query = '*';

			if ( ! empty( $select ) ) {

				if ( is_array( $select ) ) {
					array_walk(
						$select,
						function ( &$value, $key ) {
							$value = 'payments.' . $value;
						}
					);
					$select_query = implode( ', ', $select );
				} else {
					$select_query = $select;
				}
			}

			if ( is_array( $commission_id ) ) {
				$commission_id = implode( "', '", $commission_id );
			}
			$query = "SELECT DISTINCT $select_query, relationship.commission_id
					  FROM {$wpdb->payments_relationship} AS relationship JOIN
					  	   {$wpdb->payments} AS payments ON relationship.payment_id = payments.ID 
					  WHERE relationship.commission_id IN( %s )";

			$query = $wpdb->prepare( $query, $commission_id );

			$where_clause = '';
			foreach ( $where as $key => $value ) {
				$where_clause .= $wpdb->prepare( " AND payments.$key = %s", $value );
			}

			return $wpdb->get_results( $query . $where_clause, ARRAY_A ); // phpcs:ignore
		}
	}
}
