<?php
/**
 * Compatibility class with Advanced Product Options
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Advanced_Product_Options_Compatibility' ) ) {
	/**
	 * Deposit - Advanced product options compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_Advanced_Product_Options_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Advanced_Product_Options_Compatibility
		 * @since 1.0.5
		 */
		protected static $instance;

		/**
		 * Constructor method
		 */
		public function __construct() {
			add_filter( 'yith_wcdp_deposit_value', array( $this, 'fix_sold_individually_addons' ), 10, 4 );
			add_filter( 'yith_wcdp_suborder_add_cart_item_data', array( $this, 'add_sold_individually_meta_on_balance_orders' ), 10, 2 );
		}
		/**
		 * Add individual addons on balance orders
		 *
		 * @param array                 $item_data Item data.
		 * @param WC_Order_Item_Product $item      Order item.
		 *
		 * @return array Filtered item data.
		 */
		public function add_sold_individually_meta_on_balance_orders( $item_data, $item ) {
			if ( $item->get_meta( '_yith_wapo_individual_addons' ) ) {
				$individual_addons_meta = array(
					'yith_wapo_individual_addons' => $item->get_meta( '_yith_wapo_individual_addons' ),
					'yith_wapo_product_id'        => $item->get_meta( '_yith_wapo_product_id_individual_addons' ),
					'yith_wapo_variation_id'      => $item->get_meta( '_yith_wapo_variation_id_individual_addons' ),
					'yith_wapo_addons_parent_key' => '',
				);

				$item_data = array_merge( $individual_addons_meta, $item_data );
			}

			return $item_data;
		}

		/**
		 * Change deposit value for sold individually add-ons when deposit is a fixed amount
		 *
		 * @param float $deposit_value Deposit value.
		 * @param int   $product_id    Product ID.
		 * @param int   $variation_id  Variation ID.
		 * @param array $cart_item     Cart item.
		 *
		 * @return float Filtered deposit value
		 */
		public function fix_sold_individually_addons( $deposit_value, $product_id, $variation_id, $cart_item ) {
			if ( ( isset( $cart_item['yith_wapo_sold_individually'] ) && $cart_item['yith_wapo_sold_individually'] ) ||
				isset( $cart_item['yith_wapo_individual_addons'] ) && $cart_item['yith_wapo_individual_addons'] ) {

				$product_id   = isset( $cart_item['yith_wapo_product_id'] ) ? $cart_item['yith_wapo_product_id'] : $product_id;
				$variation_id = isset( $cart_item['yith_wapo_variation_id'] ) ? $cart_item['yith_wapo_variation_id'] : $variation_id;

				if ( 'amount' === YITH_WCDP_Deposits::get_type( $variation_id ? $variation_id : $product_id ) ) {
					$deposit_value = 0;
				}
			}

			return $deposit_value;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Advanced_Product_Options_Compatibility
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
