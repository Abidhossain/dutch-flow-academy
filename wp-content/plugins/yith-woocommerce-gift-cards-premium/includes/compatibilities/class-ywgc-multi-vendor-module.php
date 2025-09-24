<?php
/**
 * Multi Vendor compatibility class
 *
 * @package YITH\GiftCards\Classes\Compatibilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_Multi_Vendor_Module' ) ) {
	/**
	 * Multi Vendor compatibility class
	 *
	 * @class   YWGC_Multi_Vendor_Module
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YWGC_Multi_Vendor_Module {

		/**
		 * Single instance of the class
		 *
		 * @var YWGC_Multi_Vendor_Module
		 *
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			/**
			 * Prevent the multiple gift card generation when an order is set as completed
			 */
			add_filter( 'yith_ywgc_create_gift_card_for_order_item', array( $this, 'manage_vendor_gift_cards_generation' ), 10, 4 );

			/**
			 * Retrieve the list of gift cards of an item in the suborder, to be shown on main order
			 */
			add_filter( 'yith_ywgc_get_order_item_gift_cards', array( $this, 'retrieve_suborder_gift_cards' ), 10, 2 );

			/**
			 * Manage a link between the order items from main order and sub order
			 */
			add_filter( 'yith_get_order_item_gift_cards', array( $this, 'get_parent_order_item' ) );

			add_filter( 'yith_ywgc_enter_pre_printed_gift_card_code', array( $this, 'can_enter_pre_printed_code' ), 10, 3 );

			/**
			 * Add plugin compatibility with YITH WooCommerce Multi Vendor
			 */
			add_filter( 'ywgc_can_create_gift_card', array( $this, 'user_can_create_gift_cards' ) );

			if ( 'yes' === get_option( 'yith_ywgc_wpv_vendor_restriction_option', 'no' ) ) {
				add_filter( 'yith_ywgc_check_gift_card_return', array( $this, 'yith_ywgc_check_vendor_for_gift_card_use' ), 10, 2 );
			}
		}

		/**
		 * Deny all vendors from creating gift cards
		 *
		 * @param bool $enable_user current enable status.
		 *
		 * @return bool
		 */
		public function user_can_create_gift_cards( $enable_user ) {
			// if YITH Multivendor is active, check if the user can.
			if ( defined( 'YITH_WPV_PREMIUM' ) ) {
				return current_user_can( 'manage_woocommerce' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown
			}

			return $enable_user;
		}

		/**
		 * Manage a link between the order items from main order and sub order
		 *
		 * @param int $order_item_id Order item id.
		 *
		 * @return int
		 */
		public function get_parent_order_item( $order_item_id ) {
			$parent_id = wc_get_order_item_meta( $order_item_id, '_parent_line_item_id', true );

			if ( $parent_id ) {
				return $parent_id;
			}

			return $order_item_id;
		}

		/**
		 * Prevent the main order from generating gift cards code automatically if the product owner is a (different) vendor
		 *
		 * @param bool     $can_do          Whether the user can perform the action.
		 * @param WC_Order $order           Order object.
		 * @param int      $order_item_id   Order item id.
		 * @param array    $order_item_data Order item data.
		 *
		 * @return bool
		 */
		public function manage_vendor_gift_cards_generation( $can_do, $order, $order_item_id, $order_item_data ) {
			$product_id = $order_item_data['product_id'];

			// Always generate the gift card if its from the Gift this product.
			if ( intval( get_option( YWGC_PRODUCT_PLACEHOLDER ) ) === $product_id ) {
				return true;
			}

			return $this->is_product_owner( $order, $product_id );
		}

		/**
		 * Check if the seller of the order is also the owner of the product
		 *
		 * @param WC_Order $order      Order object.
		 * @param int      $product_id Product id.
		 *
		 * @return bool
		 */
		public function is_product_owner( $order, $product_id ) {
			$order_seller_id = get_post_field( 'post_author', $order->get_id() );

			// check if the product owner is the same of the order.
			$product_vendor = yith_wcmv_get_vendor( $product_id, 'product' );
			$order_vendor   = yith_wcmv_get_vendor( $order_seller_id, 'user' );

			if ( 0 === $product_vendor->get_id() ) {
				return true;
			}

			if ( ! $order->get_parent_id() ) {
				return true;
			} else {
				if ( ! $order_vendor->get_id() ) {
					return $product_vendor->get_id() == $order->get_meta( 'vendor_id' );
				} else {
					return $product_vendor->get_id() == $order_vendor->get_id();
				}
			}

		}

		/**
		 * Check if the current user can enter the code for pre-printed gift card
		 *
		 * @param bool       $can_do   Whether the user can perform the action.
		 * @param WC_Order   $order    Order object.
		 * @param WC_Product $_product Product object.
		 *
		 * @return bool
		 */
		public function can_enter_pre_printed_code( $can_do, $order, $_product ) {
			return $this->is_product_owner( $order, $_product->get_id() );
		}

		/**
		 * Add condition when a gift card is applied
		 *
		 * @param bool   $bool Condition to apply gift card for vendor restrictions.
		 * @param object $gift Gift card object.
		 *
		 * @return bool
		 */
		public function yith_ywgc_check_vendor_for_gift_card_use( $bool, $gift ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.boolFound
			$gift_card_vendor    = yith_get_vendor( $gift->product_id, 'product' );
			$gift_card_vendor_id = $gift_card_vendor->id;

			$items = WC()->cart->get_cart();

			foreach ( $items as $cart_item_key => $values ) {
				$product = $values['data'];

				$cart_product_vendor    = yith_get_vendor( $product->get_id(), 'product' );
				$cart_product_vendor_id = $cart_product_vendor->id;

				if ( $cart_product_vendor_id !== $gift_card_vendor_id ) {
					$link = '<a href="' . $gift_card_vendor->get_url() . '">' . $gift_card_vendor->name . '</a>';

					// translators: %s is the link to the vendor page to which the restriction is applied when using a gift card.
					$message = sprintf( esc_html__( 'This gift card can be used if the cart contains only products of the %s.', 'yith-woocommerce-gift-cards' ), $link );

					wc_add_notice( $message, 'error' );

					$bool = false;
				}
			}

			return $bool;
		}
	}
}

YWGC_Multi_Vendor_Module::get_instance();
