<?php
/**
 * YITH_WCCustomerOrderExport_Support class
 *
 * @since      1.11.4
 * @author     YITH
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

if ( ! class_exists( 'YITH_WCCustomerOrderExport_Support' ) ) {
	/**
	 * Handle support to WC Customer Order Export
	 */
	class YITH_WCCustomerOrderExport_Support {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Construct
		 */
		private function __construct() {
			add_action( 'load-edit.php', array( $this, 'customer_order_csv_export' ), 5 );
			add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'export_order_headers' ) );
			add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'export_order_row_one_row_per_item' ), 10, 2 );
		}

		/**
		 * Add Vendor order to $_POST array
		 *
		 * @since  1.9.8
		 * @return void
		 */
		public function customer_order_csv_export() {
			global $typenow;

			if ( 'shop_order' === $typenow ) {
				// Get the action.
				$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
				$action        = $wp_list_table->current_action();

				// Return if not processing our actions.
				if ( ! in_array( $action, array( 'download_to_csv', 'mark_exported_to_csv', 'mark_not_exported_to_csv' ), true ) ) {
					return;
				}

				// Security check.
				check_admin_referer( 'bulk-posts' );
				$_request_post = array();

				// Make sure order IDs are submitted.
				if ( isset( $_REQUEST['post'] ) ) {
					$order_ids     = array_map( 'absint', $_REQUEST['post'] );
					$_request_post = $order_ids;
				}

				// Return if there are no orders to export.
				if ( empty( $order_ids ) ) {
					return;
				}

				foreach ( $order_ids as $order_id ) {
					$suborder_ids = YITH_Vendors_Orders::get_suborders( $order_id );
					if ( $suborder_ids ) {
						$_request_post = array_merge( $_request_post, $suborder_ids );
					}

					$_REQUEST['post'] = $_request_post;
				}
			}
		}

		/**
		 * Add post_author_id and post_parent_id to order list
		 *
		 * @since  1.9.8
		 * @param array    $order_data The order data array.
		 * @param WC_Order $order      The order object.
		 * @return array The order data array
		 */
		public function export_order_row_one_row_per_item( $order_data, $order ) {
			$order_id                      = $order->get_id();
			$post_author                   = get_post_field( 'post_author', $order_id );
			$order_data[0]['order_author'] = $post_author;
			$order_data[0]['parent_order'] = $order->get_parent_id();

			return $order_data;
		}

		/**
		 * Add post_author and post_parent CSV Header
		 *
		 * @since  1.9.8
		 * @param array $headers The CSV Headers data array.
		 * @return array
		 */
		public function export_order_headers( $headers ) {
			$headers['order_author'] = 'order_author';
			$headers['parent_order'] = 'parent_order';

			return $headers;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.9.8
 * @return YITH_WCCustomerOrderExport_Support
 */
if ( ! function_exists( 'YITH_WCCustomerOrderExport_Support' ) ) {
	function YITH_WCCustomerOrderExport_Support() { // phpcs:ignore
		return YITH_WCCustomerOrderExport_Support::instance();
	}
}

YITH_WCCustomerOrderExport_Support();
