<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class to manage the gift card order item
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * YITH Gift Card Order Item WooCommerce Data Stores.
 *
 * @param  array $stores
 *
 * @return array
 */
function ywgc_order_item_woocommerce_data_stores( $stores ) {

	if ( !isset( $stores[ 'order-item-yith_gift_card' ] ) ) {
		$stores[ 'order-item-yith_gift_card' ] = 'YITH_Gift_Card_Data_Store';
	}

	return $stores;
}
add_filter( 'woocommerce_data_stores', 'ywgc_order_item_woocommerce_data_stores' );


if ( ! class_exists( 'YITH_Gift_Card_Data_Store' ) ) {

	/**
	 * YITH_Gift_Card_Data_Store class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_Gift_Card_Data_Store extends Abstract_WC_Order_Item_Type_Data_Store implements WC_Object_Data_Store_Interface, WC_Order_Item_Type_Data_Store_Interface {

		protected $internal_meta_keys = array( 'code', 'amount' );

		/**
		 * Read Item Data Store.
		 *
		 * @param  object $item
		 */
		public function read( &$item ) {
			parent::read( $item );
			$id = $item->get_id();
			$item->set_props( array(
				'code'   => get_metadata( 'order_item', $id, 'code', true ),
				'amount'        => get_metadata( 'order_item', $id, 'amount', true ),
			) );
			$item->set_object_read( true );
		}

		/**
		 * Save Item Data Store.
		 *
		 * @param  object $item
		 */
		public function save_item_data( &$item ) {
			$id          = $item->get_id();
			$save_values = array(
				'code'   => addslashes( $item->get_code( 'edit' ) ),
				'amount' => $item->get_amount( 'edit' ),
			);
			foreach ( $save_values as $key => $value ) {
				update_metadata( 'order_item', $id, $key, $value );
			}
		}

	}
}
