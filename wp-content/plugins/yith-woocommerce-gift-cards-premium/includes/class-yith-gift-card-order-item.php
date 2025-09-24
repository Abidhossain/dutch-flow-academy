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
 * Get YITH Gift Card Order Item Key.
 *
 * @param  string $key
 * @param  string $item
 *
 * @return string
 */
function ywgc_order_item_woocommerce_get_items_key( $key, $item ) {
	if ( is_a( $item, 'YITH_Gift_Card_Order_Item' ) ) {
		return 'yith_gift_card_lines';
	}
	return $key;
}
add_filter( 'woocommerce_get_items_key', 'ywgc_order_item_woocommerce_get_items_key', 10, 2 );

/**
 * YITH Gift Card Order Type to Group.
 *
 * @param array $groups
 *
 * @return array
 */
function ywgc_woocommerce_order_type_to_group( $type_to_group ) {
	$type_to_group['yith_gift_card'] = 'yith_gift_card_lines';
	return $type_to_group;
}
add_filter( 'woocommerce_order_type_to_group', 'ywgc_woocommerce_order_type_to_group', 10, 2 );

/**
 * Get YITH Gift Card Order Item Class Name.
 *
 * @param  string $classname
 * @param  string $item_type
 * @param  int $id
 *
 * @return string
 */
function ywgc_woocommerce_get_order_item_classname( $classname, $item_type, $id ) {
	if ( $item_type === 'yith_gift_card' ) {
		$classname = 'YITH_Gift_Card_Order_Item';
	}
	return $classname;
}
add_filter( 'woocommerce_get_order_item_classname', 'ywgc_woocommerce_get_order_item_classname', 10, 3 );

if ( ! class_exists( 'YITH_Gift_Card_Order_Item' ) ) {

	/**
	 * YITH_Gift_Card_Order_Item class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_Gift_Card_Order_Item extends WC_Order_Item {

		/**
		 * Order Data array.
		 *
		 * @since 3.0.0
		 * @var array
		 */
		protected $extra_data = array(
			'code'         => '',
			'amount'     => 0,
		);

		/*
		|--------------------------------------------------------------------------
		| Setters
		|--------------------------------------------------------------------------
		*/

		/**
		 * Set order item name.
		 *
		 * @param string $value Gift Card code.
		 */
		public function set_name( $value ) {
			return $this->set_code( $value );
		}

		/**
		 * Set code.
		 *
		 * @param string $value Gift Card code.
		 */
		public function set_code( $value ) {
			$this->set_prop( 'code', $value );
		}

		/**
		 * Set discount amount.
		 *
		 * @param string $value Discount.
		 */
		public function set_amount( $value ) {
			$this->set_prop( 'amount', wc_format_decimal( $value ) );
		}

		/*
		|--------------------------------------------------------------------------
		| Getters
		|--------------------------------------------------------------------------
		*/

		/**
		 * Get order item type.
		 *
		 * @return string
		 */
		public function get_type() {
			return 'yith_gift_card';
		}

		/**
		 * Get order item name.
		 *
		 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
		 * @return string
		 */
		public function get_name( $context = 'view' ) {
			return $this->get_code( $context );
		}

		/**
		 * Get Gift Card code.
		 *
		 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
		 * @return string
		 */
		public function get_code( $context = 'view' ) {
			return $this->get_prop( 'code', $context );
		}

		/**
		 * Get discount amount.
		 *
		 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
		 * @return string
		 */
		public function get_amount( $context = 'view' ) {
			return $this->get_prop( 'amount', $context );
		}

	}
}
