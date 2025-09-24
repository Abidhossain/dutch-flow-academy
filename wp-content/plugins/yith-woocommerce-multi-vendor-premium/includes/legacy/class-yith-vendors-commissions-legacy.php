<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Commissions_Legacy' ) ) {
	/**
	 * Class YITH_Commissions legacy.
	 */
	abstract class YITH_Vendors_Commissions_Legacy {

		/**
		 * Whether or not to show order item meta added by plugin in order page
		 *
		 * @since 1.0.0
		 * @var boolean Whether or not to show order item meta
		 */
		public $show_order_item_meta = true;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 * @deprecated
		 */
		public $version = YITH_WPV_VERSION;

		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @since  1.0
		 * @return YITH_Vendors_Commissions
		 * @deprecated
		 */
		public static function instance() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors()->commissions' );

			return YITH_Vendors()->commissions;
		}

		/**
		 * Magic get method
		 *
		 * @since  4.0.0
		 * @param string $key The property key to retrieve.
		 * @return mixed
		 */
		public function __get( $key ) {
			switch ( $key ) {
				case '_status_capabilities':
					return array(
						'pending'    => array( 'unpaid', 'paid', 'cancelled' ),
						'unpaid'     => array( 'pending', 'paid', 'cancelled', 'processing' ),
						'paid'       => array( 'pending', 'unpaid', 'refunded' ),
						'cancelled'  => array(),
						'refunded'   => array(),
						'processing' => array( 'paid', 'unpaid' ),
					);

				case '_db_version':
					return YITH_WPV_DB_VERSION;

				case '_commissions_notes_table_name':
					return YITH_Vendors_Install::COMMISSIONS_NOTES_TABLE;

				case '_commissions_table_name':
					return YITH_Vendors_Install::COMMISSIONS_TABLE;

				case '_screen':
					return 'yith_vendor_commissions';

				case '_messages':
					return array();

				case '_instance':
					return null;
			}
		}

		/**
		 * Commissions API - set table name
		 *
		 * @since  4.0.0
		 * @return void
		 * @deprecated
		 */
		public function add_commissions_table_wpdb() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Install::define_tables' );
			YITH_Vendors_Install::define_tables();
		}

		/**
		 * Admin init
		 *
		 * @since 1.0.0
		 * @deprecated
		 */
		protected function _admin_init() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}


		/**
		 * Add the Commissions menu item in dashboard menu
		 *
		 * @since  1.0
		 * @return void
		 * @fire   yith_wc_product_vendors_commissions_menu_items hooks
		 * @see    wp-admin\includes\plugin.php -> add_menu_page()
		 * @deprecated
		 */
		public function add_menu_item() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Show the Commissions page
		 *
		 * @since  1.0
		 * @return void
		 * @fire   yith_vendors_commissions_template hooks
		 * @deprecated
		 */
		public function commissions_details_page() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Change the page title of commission detail page
		 *
		 * @since 1.0
		 * @param $title
		 * @param $admin_title
		 * @return string
		 * @deprecated
		 */
		public function change_commission_view_page_title( $admin_title, $title ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $admin_title;
		}

		/**
		 * @since  1.0
		 * @param $screen_ids array The WC Screen ids
		 * @return array The screen ids
		 * @use    woocommerce_screen_ids hooks
		 * @deprecated
		 */
		public function add_screen_ids( $screen_ids ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $screen_ids;
		}

		/**
		 * Add Screen option
		 *
		 * @return void
		 * @deprecated
		 */
		public function add_screen_option() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Save custom screen options
		 *
		 * @param $set      Filter value
		 * @param $option   Option id
		 * @param $value    The option value
		 * @return mixed
		 * @deprecated
		 */
		public function set_screen_option( $set, $option, $value ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $set;
		}

		/**
		 * Print admin notice
		 *
		 * @since  1.0
		 * @fire   yith_commissions_admin_notice hooks
		 * @deprecated
		 */
		public function admin_notice() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Update the commission status by Commissions page
		 *
		 * @since  1.0
		 * @return void
		 * @deprecated
		 */
		public function table_update_status() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Change commission label value
		 *
		 * @param $attribute_label  string The Label Value
		 * @param $meta_key         string The Meta Key value
		 * @param $product          WC_Product The Product object
		 * @return string           The label value
		 */
		public function commissions_attribute_label( $attribute_label, $meta_key, $product = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $attribute_label;
		}

		/**
		 * Return the screen id for commissions page
		 *
		 * @since 1.0
		 * @deprecated
		 */
		public function get_screen() {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $this->_screen;
		}

		/**
		 * Add commission id from parent to child order
		 *
		 * @since    WooCommerce 2.7
		 * @internal moved from YITH_Orders
		 * @deprecated
		 */
		public function register_commission_to_parent_order( $commission_id, $child_item_id, $key, $suborder ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors()->commissions->register_commission_to_order_item( $child_item_id, $commission_id );
		}

		/**
		 * Return the commissions table name
		 *
		 * @since  2.0.3
		 * @return string table name
		 * @deprecated
		 */
		public function get_commissions_table_name() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_::COMMISSIONS_TABLE' );
			return $this->_commissions_table_name;
		}

		/**
		 * Get Commissions
		 *
		 * @since  1.0.0
		 * @param array $q Query parameters.
		 * @return mixed
		 * @deprecated
		 */
		public function get_commissions( $q = array() ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Commissions_Factory::query' );
			return YITH_Vendors_Commissions_Factory::query( $q );
		}

		/**
		 * Return the count of posts in base of query
		 *
		 * @since 1.0
		 * @param array $q Query parameters.
		 * @return integer
		 */
		public function count_commissions( $q = array() ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Commissions_Factory::count' );
			return YITH_Vendors_Commissions_Factory::count( $q );
		}

		/**
		 * Multiple Delete Bulk commission
		 *
		 * @since  1.8.4
		 * @param array  $order_ids The order ids to apply the bulk action.
		 * @param string $action    Bulk action type.
		 * @return void
		 * @deprecated
		 */
		public function bulk_action( $order_ids, $action = 'delete' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			switch ( $action ) {
				case 'delete':
					foreach ( $order_ids as $order_id ) {
						$commission_ids = yith_wcmv_get_commissions( array( 'order_id' => $order_id ) );
						foreach ( $commission_ids as $commission_id ) {
							YITH_Vendors_Commissions_Factory::delete( $commission_id );
						}
					}
					break;
			}
		}

		/**
		 * Calculate commission for an order, vendor and item
		 *
		 * @param YITH_Vendor    $vendor  Item vendor.
		 * @param WC_Order       $order   The order related to the commission.
		 * @param WC_Order_Item  $item    The item order related to the commission.
		 * @param integer|string $item_id The item order ID related to the commission.
		 * @param WC_Product     $product (Optional) The product related to the commission.
		 * @return mixed
		 * @deprecated
		 */
		protected function calculate_commission( $vendor, $order, $item, $item_id, $product = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( empty( $product ) ) { // Backward compatibility.
				$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;

				if ( $product && $product->is_type( 'variation' ) ) {
					$product = wc_get_product( $product->get_parent_id() );
				}
			}

			// If product is not valid then go no further.
			if ( empty( $product ) ) {
				return 0;
			}

			// Get percentage for commission.
			$rate = (float) $vendor->get_commission_rate( $product->get_id() );
			$rate = apply_filters( 'yith_wcmv_product_commission', $rate, $vendor, $order, $item, $item_id );

			return $this->calculate_commission_amount( $vendor, $order, $item, $rate );
		}

		/**
		 * Return the list of commission status
		 *
		 * @since  1.0.0
		 * @param boolean $singular (Optional) True to get singular label, false to get plural. Default is false.
		 * @return array
		 * @deprecated
		 */
		public function get_status( $singular = false ) {
			_deprecated_function( __METHOD__, '5.0.0', 'Use function yith_wcmv_get_commission_statuses' );
			return yith_wcmv_get_commission_statuses( $singular );
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.0
 * @return YITH_Vendors_Commissions
 * @deprecated
 */
if ( ! function_exists( 'YITH_Commissions' ) ) {
	function YITH_Commissions() { // phpcs:ignore
		return YITH_Vendors()->commissions;
	}
}
