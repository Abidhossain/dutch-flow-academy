<?php
/**
 * YITH_Report_Customer_List Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'WC_Report_Customer_List' ) ) {
	require_once WC()->plugin_path() . '/includes/admin/reports/class-wc-report-customer-list.php';
}

if ( ! class_exists( 'YITH_Report_Customer_List' ) ) {
	/**
	 * YITH_Report_Customer_List
	 */
	class YITH_Report_Customer_List extends WC_Report_Customer_List {

		/**
		 * Column_default function.
		 *
		 * @param WP_User $user        The user object related to the current column.
		 * @param string  $column_name The column name.
		 * @return string
		 */
		public function column_default( $user, $column_name ) {
			if ( 'orders' === $column_name ) {
				$count = get_user_meta( $user->ID, '_order_count', true );
				if ( ! $count ) {

					$orders = wc_get_orders(
						array(
							'return'      => 'ids',
							'type'        => wc_get_order_types( 'order-count' ),
							'customer_id' => $user->ID,
							'parent'      => 0,
						)
					);

					$count = is_countable( $orders ) ? count( $orders ) : 0;
					update_user_meta( $user->ID, '_order_count', absint( $count ) );
				}

				$result = absint( $count );
			} else {
				$result = parent::column_default( $user, $column_name );
			}

			return $result;
		}
	}
}

