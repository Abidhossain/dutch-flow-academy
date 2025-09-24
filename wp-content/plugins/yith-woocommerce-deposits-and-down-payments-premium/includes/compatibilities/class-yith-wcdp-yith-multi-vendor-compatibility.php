<?php
/**
 * Compatibility class with Multi Vendor
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Multi_Vendor_Compatibility' ) ) {
	/**
	 * Deposit - Multi vendor compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_Multi_Vendor_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Multi_Vendor_Compatibility
		 * @since 1.0.5
		 */
		protected static $instance;

		/**
		 * Constructor method
		 */
		public function __construct() {
			// admin order view handling.
			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				add_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'filter_orders_table_query_clauses' ), 10, 2 );
			} else {
				add_filter( 'request', array( $this, 'filter_order_list' ), 15, 1 );
			}

			// filter suborders.
			add_filter( 'yith_wcdp_suboder', array( $this, 'get_suborder' ), 10, 2 );

			// skip balance orders from MV processing.
			if ( version_compare( YITH_WPV_VERSION, '4.0.0', '>=' ) ) {
				add_filter( 'yith_wcmv_get_suborders_ids', array( $this, 'remove_deposit_suborder_from_multi_vendor' ), 10, 2 );
			} else {
				add_filter( 'yith_wcmv_get_suborder_ids', array( $this, 'remove_deposit_suborder_from_multi_vendor' ), 10, 2 );
			}
		}

		/* === ORDER VIEW METHODS === */

		/**
		 * Only show parent orders
		 *
		 * @param array $query Query arguments.
		 *
		 * @return array Modified request
		 * @since  1.0.0
		 */
		public function filter_order_list( $query ) {
			global $typenow, $wpdb;

			$vendor = yith_get_vendor( 'current', 'user' );

			if ( ! $vendor->is_valid() || ! $vendor->has_limited_access() ) {
				return $query;
			}

			// retrieve balance orders.
			$balance_ids = YITH_WCDP_Suborders()->get_all_balances_ids();

			if ( 'shop_order' === $typenow ) {
				$query['post_parent__not_in'] = $balance_ids;
			}

			return $query;
		}

		/**
		 * Only show parent orders
		 *
		 * @param array  $clauses Query clauses.
		 * @param object $query   Query object.
		 *
		 * @return array Modified query clauses
		 */
		public function filter_orders_table_query_clauses( $clauses, $query ) {
			$vendor = yith_get_vendor( 'current', 'user' );

			if ( ! $vendor->is_valid() || ! $vendor->has_limited_access() ) {
				return $clauses;
			}

			$orders_table = $query->get_table_name( 'orders' );

			// retrieve balance orders.
			$balance_ids = YITH_WCDP_Suborders()->get_all_balances_ids();

			if ( ! empty( $balances_ids ) ) {
				$clauses['where'] .= " AND {$orders_table}.parent_order_id NOT IN (" . implode( ',', $balance_ids ) . ')';
			}

			return $clauses;
		}

		/**
		 * Check if order identified by $order_id has suborders, and eventually returns them
		 *
		 * @param array $suborders Array of suborders.
		 * @param int   $order_id  Id of the order to check.
		 *
		 * @return mixed Array of suborders, if any
		 * @since 1.0.0
		 */
		public function get_suborder( $suborders, $order_id ) {
			$vendor = yith_get_vendor( 'current', 'user' );

			if ( ! $vendor->is_valid() || ! $vendor->has_limited_access() ) {
				return $suborders;
			}

			$parent = wp_get_post_parent_id( $order_id );
			if ( ! $parent ) {
				return $suborders;
			}

			remove_filter( 'yith_wcdp_suboder', array( $this, 'get_suborder' ) );
			remove_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'filter_orders_table_query_clauses' ), 10 );
			$parent_suborders = YITH_WCDP_Suborders()->get_suborders( $parent );

			if ( empty( $parent_suborders ) ) {
				return $suborders;
			}

			foreach ( $parent_suborders as $id ) {
				$suborders = array_merge( $suborders, YITH_Vendors_Orders::get_suborders( $id ) );
			}

			add_filter( 'yith_wcdp_suboder', array( $this, 'get_suborder' ), 10, 2 );
			add_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'filter_orders_table_query_clauses' ), 10, 2 );

			return $suborders;
		}

		/**
		 * Remove deposit suborders from Multi Vendor suborders list
		 *
		 * @param mixed $suborder_ids    Multi Vendor suborders.
		 * @param int   $parent_order_id Parent order id.
		 *
		 * @return mixed Array diff between Multi Vendor suborders and deposit suborders
		 * @since 1.0.4
		 */
		public function remove_deposit_suborder_from_multi_vendor( $suborder_ids, $parent_order_id ) {
			if ( $parent_order_id && $suborder_ids ) {
				$deposit_suborder_ids = YITH_WCDP_Suborders()->get_suborders( $parent_order_id );

				if ( $deposit_suborder_ids ) {
					$suborder_ids = array_diff( $suborder_ids, $deposit_suborder_ids );
				}
			}

			return $suborder_ids;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Multi_Vendor_Compatibility
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
