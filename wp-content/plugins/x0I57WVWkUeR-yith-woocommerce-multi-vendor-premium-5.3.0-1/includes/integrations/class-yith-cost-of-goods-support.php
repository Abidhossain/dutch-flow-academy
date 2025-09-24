<?php
/**
 * YITH WooCommerce Cost of Goods module
 *
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

if ( ! class_exists( 'YITH_Cost_Of_Goods_Support' ) ) {
	/**
	 * YITH_WooCommerce_Cost_Of_Goods_Support Class
	 */
	class YITH_Cost_Of_Goods_Support {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * YITH_COG instance
		 *
		 * @var YITH_COG|null
		 */
		public $yith_cog = null;

		/**
		 * YITH_COG_Admin instance
		 *
		 * @var YITH_COG_Admin|null
		 */
		public $yith_cog_admin = null;

		/**
		 * YITH_COG_Admin_Premium instance
		 *
		 * @var YITH_COG_Admin_Premium|null
		 */
		public $yith_cog_admin_premium = null;

		/**
		 * YITH_COG_Orders instance
		 *
		 * @var YITH_COG_Orders|null
		 */
		public $yith_cog_orders = null;

		/**
		 * Show cog info to vendor
		 *
		 * @var boolean
		 */
		public $show_yith_cog_info = true;

		/**
		 * Show cog info to vendor
		 *
		 * @var boolean
		 */
		public $exclude_yith_cog_in_commission = false;

		/**
		 * Order item meta name
		 *
		 * @var string
		 */
		public $yith_cog_oder_item_meta_name = '_commission_included_yith_cost_of_goods';

		/**
		 * Construct
		 */
		private function __construct() {
			$this->init_variables();
			$this->init_hooks();
		}

		/**
		 * Init class variables
		 *
		 * @since  4.0.0
		 */
		protected function init_variables() {
			// Object initialization.
			$this->yith_cog        = YITH_COG::instance();
			$this->yith_cog_admin  = $this->yith_cog->admin;
			$this->yith_cog_orders = YITH_COG_Orders::get_instance();

			// Privacy option.
			$this->show_yith_cog_info = 'yes' === get_option( 'yith_wpv_show_cog_info', 'yes' );
			// Exclude cog in commission calculation.
			$this->exclude_yith_cog_in_commission = 'no' === get_option( 'yith_wpv_include_cog', 'yes' );
		}

		/**
		 * Init class hooks
		 *
		 * @since  4.0.0
		 */
		protected function init_hooks() {

			// Add cost of goods in vendor suborder - Checkout.
			add_action( 'yith_wcmv_checkout_order_processed', array( $this->yith_cog_orders, 'set_order_cost_meta' ), 10, 1 );
			// Add options for WC COG under commissions table.
			add_filter( 'yith_wcmv_admin_commissions_settings', array( $this, 'add_yith_cog_options' ), 20, 1 );

			if ( $this->show_yith_cog_info ) {
				// Add Cost Of Goods column header to commission details page.
				add_action( 'yith_wcmv_admin_order_item_headers', array( $this->yith_cog_orders, 'yith_cog_order_item_header' ), 10, 1 );
				// Add cost of goods value in line item row in commission details page.
				add_action( 'yith_wcmv_admin_order_item_values', array( $this->yith_cog_orders, 'yith_cog_order_item_value' ), 10, 3 );
			}

			if ( $this->exclude_yith_cog_in_commission ) {
				add_filter( 'yith_wcmv_get_line_total_amount_for_commission', array( $this, 'exclude_cog_from_commission' ), 10, 4 );
			}

			// Commission note message.
			add_filter( 'yith_wcmv_new_commission_note', array( $this, 'new_commission_note' ) );
			// Add cog order item meta.
			add_action( 'yith_wcmv_add_extra_commission_order_item_meta', array( $this, 'add_cog_order_item_meta' ), 10, 1 );
			// Exclude cog order item meta to parent/child order sync.
			add_filter( 'yith_wcmv_order_item_meta_no_sync', array( $this, 'order_item_meta_no_sync' ) );
			// Add cog message in order details page.
			add_filter( 'yith_wcmv_order_details_page_commission_message', array( $this, 'order_details_page_commission_message' ), 10, 2 );

			// Vendor limited access hooks.
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_limited_access_hooks' ) );
		}

		/**
		 * Vendor limit access hooks
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function vendor_limited_access_hooks() {
			if ( $this->yith_cog_admin instanceof YITH_COG_Admin ) {
				// Remove cost field to simple products under the 'General' tab.
				remove_action( 'woocommerce_product_options_pricing', array( $this->yith_cog_admin, 'add_cost_field_to_simple_product' ) );
				// Remove cost field to variable products under the 'General' tab.
				remove_action( 'woocommerce_product_options_sku', array( $this->yith_cog_admin, 'add_cost_field_to_variable_product' ) );
				// Remove in quick edit.
				remove_action( 'woocommerce_product_quick_edit_end', array( $this->yith_cog_admin, 'render_quick_edit_cost_field' ) );
				// Remove in bulk edit.
				remove_action( 'woocommerce_product_bulk_edit_end', array( $this->yith_cog_admin, 'bulk_actions_edit_product' ) );
			}
		}

		/**
		 * Add cog options to commissions tab
		 *
		 * @since  1.11.4
		 * @param array $options Current commissions option array.
		 * @return array
		 */
		public function add_yith_cog_options( $options ) {
			$new_options                         = array();
			$new_options['commissions-settings'] = array(
				array(
					'id'   => 'yith_cog_options_start',
					'type' => 'sectionstart',
				),

				array(
					'title' => 'YITH WooCommerce Cost of Goods',
					'id'    => 'yith_cog_options_title',
					'type'  => 'title',
					'desc'  => '',
				),

				array(
					'title'     => __( 'YITH Cost of Goods handling', 'yith-woocommerce-product-vendors' ),
					'type'      => 'yith-field',
					'yith-type' => 'onoff',
					'default'   => 'yes',
					'desc'      => __( 'Decide whether the vendors\' commissions have to be calculated including the cost of goods value or not.', 'yith-woocommerce-product-vendors' ),
					'id'        => 'yith_wpv_include_cog',
				),

				array(
					'title'     => __( 'Show YITH Cost of Goods information', 'yith-woocommerce-product-vendors' ),
					'type'      => 'yith-field',
					'yith-type' => 'onoff',
					'default'   => 'yes',
					'desc'      => __( 'Show the cost of goods information on the commission details page.', 'yith-woocommerce-product-vendors' ),
					'id'        => 'yith_wpv_show_cog_info',
				),

				array(
					'id'   => 'yith_cog_options_end',
					'type' => 'sectionend',
				),
			);

			$to_return['commissions-settings'] = array_merge( $options['commissions-settings'], $new_options['commissions-settings'] );
			return $to_return;
		}

		/**
		 * Exclude yith cog cost to commissions
		 *
		 * @since  1.11.4
		 * @param float         $line_total Processed line total.
		 * @param WC_Order      $order      Order instance processed.
		 * @param WC_Order_Item $item       Order item instance.
		 * @param integer       $item_id    Order item ID.
		 * @return float
		 */
		public function exclude_cog_from_commission( $line_total, $order, $item, $item_id ) {

			$original_line_total = $line_total;
			$item_total_cost     = wc_get_order_item_meta( $item_id, '_yith_cog_item_cost', true );
			$quantity            = $item->get_quantity();

			if ( ! empty( $item_total_cost ) ) {
				$line_total = $line_total - $item_total_cost * $quantity;
			}

			return apply_filters( 'yith_wcmv_exclude_cog_from_commission', $line_total, $item_total_cost, $original_line_total );
		}

		/**
		 * Add yith cog info to commissions note
		 *
		 * @since  1.11.4
		 * @param string $msg Current message.
		 * @return mixed
		 */
		public function new_commission_note( $msg ) {
			$yith_cog = $this->exclude_yith_cog_in_commission ? _x( 'excluded', 'means: Vendor commission have been calculated: tax excluded', 'yith-woocommerce-product-vendors' ) : _x( 'included', 'means: Vendor commission have been calculated: tax included', 'yith-woocommerce-product-vendors' );
			$msg      = sprintf(
				'%s:<br>* %s <em>%s</em>',
				$msg,
				_x( 'cost of goods', 'part of: cost of goods included or cost of goods excluded', 'yith-woocommerce-product-vendors' ),
				$yith_cog
			);
			return $msg;
		}

		/**
		 * Add cog order item meta
		 *
		 * @since  1.11.4
		 * @param integer $item_id Order item ID.
		 * @return void
		 */
		public function add_cog_order_item_meta( $item_id ) {
			wc_add_order_item_meta( $item_id, $this->yith_cog_oder_item_meta_name, $this->exclude_yith_cog_in_commission ? 'no' : 'yes' );
		}

		/**
		 * Exclude cog order item meta from sync.
		 *
		 * @since  1.11.4
		 * @param array $no_sync An array of meta key to exclude from sync.
		 * @return mixed
		 */
		public function order_item_meta_no_sync( $no_sync ) {
			$no_sync[] = $this->yith_cog_oder_item_meta_name;
			return $no_sync;
		}

		/**
		 * Order details commission message
		 *
		 * @since  1.11.4
		 * @param string  $msg     Current message.
		 * @param integer $item_id The order item ID.
		 * @return string
		 */
		public function order_details_page_commission_message( $msg, $item_id ) {
			$commission_included_yith_cog = wc_get_order_item_meta( $item_id, $this->yith_cog_oder_item_meta_name, true );
			if ( $commission_included_yith_cog ) {
				$yith_cog = 'yes' === $commission_included_yith_cog ? _x( 'included', 'means: Vendor commission have been calculated: tax included', 'yith-woocommerce-product-vendors' ) : _x( 'excluded', 'means: Vendor commission have been calculated: tax excluded', 'yith-woocommerce-product-vendors' );
				$msg      = sprintf( '%s <small><em>- %s <strong>%s</strong></em></small>', $msg, _x( 'cost of goods', 'part of: cost of goods included or cost of goods excluded', 'yith-woocommerce-product-vendors' ), $yith_cog );
			}
			return $msg;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.11.4
 * @return YITH_WooCommerce_Cost_Of_Goods_Support
 */
if ( ! function_exists( 'YITH_Cost_Of_Goods_Support' ) ) {
	function YITH_Cost_Of_Goods_Support() { // phpcs:ignore
		return YITH_Cost_Of_Goods_Support::instance();
	}
}

YITH_Cost_Of_Goods_Support();
