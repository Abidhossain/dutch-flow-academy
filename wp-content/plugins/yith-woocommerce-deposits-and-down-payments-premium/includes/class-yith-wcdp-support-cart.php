<?php
/**
 * Support Cart for Deposit
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Support_Cart' ) ) {
	/**
	 * Support cart for deposit
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Support_Cart extends WC_Cart {

		/**
		 * Array of notices
		 *
		 * @var array
		 */
		protected $notices;

		/**
		 * Array that register relation between original cart items and support cart items
		 *
		 * @var array
		 */
		protected $items_relation = array();

		/**
		 * Constructor for the cart class. Loads options and hooks in the init method.
		 */
		public function __construct() {
			$this->session          = new YITH_WCDP_Support_Cart_Session();
			$this->fees_api         = new WC_Cart_Fees();
			$this->tax_display_cart = get_option( 'woocommerce_tax_display_cart' );

			add_action( 'woocommerce_add_to_cart', array( $this, 'calculate_totals' ), 20, 0 );
			add_action( 'woocommerce_applied_coupon', array( $this, 'calculate_totals' ), 20, 0 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'calculate_totals' ), 20, 0 );
			add_action( 'woocommerce_cart_item_restored', array( $this, 'calculate_totals' ), 20, 0 );
			add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ), 1 );
			add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_coupons' ), 1 );
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_customer_coupons' ), 1 );
		}

		/**
		 * Retrieves an item of the original cart that is the equivalent of the item of the support cart specified by $item_key
		 *
		 * @param string $item_key Support cart item key.
		 * @return string!bool Original cart item key; false on failure.
		 */
		public function get_original_item( $item_key ) {
			if ( ! $this->items_relation ) {
				return false;
			}

			$support_to_original = array_flip( $this->items_relation );

			if ( ! isset( $support_to_original[ $item_key ] ) ) {
				return false;
			}

			return $support_to_original[ $item_key ];
		}

		/**
		 * Retrieves an item of the support cart that is the equivalent of the item of the original cart specified by $item_key
		 *
		 * @param string $item_key Support cart item key.
		 * @return string!bool Original cart item key; false on failure.
		 */
		public function get_support_item( $item_key ) {
			if ( ! $this->items_relation ) {
				return false;
			}

			if ( ! isset( $this->items_relation[ $item_key ] ) ) {
				return false;
			}

			return $this->items_relation[ $item_key ];
		}

		/**
		 * Populate support cart with some contents
		 *
		 * @param array $items   Array of cart items.
		 * @param array $coupons Optional. Array of coupons to apply to current instance.
		 *
		 * @return bool
		 */
		public function populate( $items, $coupons = array() ) {
			foreach ( $items as $original_item_key => $cart_item ) {
				$product_id   = $cart_item['product_id'];
				$variation_id = $cart_item['variation_id'];
				$quantity     = $cart_item['quantity'];
				$item_meta    = $cart_item;

				if ( isset( $cart_item['deposit_shipping_method'] ) ) {
					$item_meta['deposit_shipping_method'] = $cart_item['deposit_shipping_method'];
				}

				try {
					$new_item_key = $this->add_to_cart( $product_id, $quantity, $variation_id, array(), $item_meta );
				} catch ( Exception $e ) {
					return false;
				}

				// register relation between support and original cart items.
				$this->items_relation[ $original_item_key ] = $new_item_key;
			}

			if ( $coupons ) {
				$this->applied_coupons = $coupons;
			}

			$this->calculate_shipping();
			$this->calculate_totals();

			do_action( 'yith_wcdp_after_populate_support_cart', $this );

			return true;
		}

		/**
		 * Empty cart (do not affect current user session)
		 *
		 * @param bool $clear_persistent_cart Not used.
		 *
		 * @return void
		 */
		public function empty_cart( $clear_persistent_cart = true ) {
			$this->cart_contents              = array();
			$this->removed_cart_contents      = array();
			$this->shipping_methods           = array();
			$this->coupon_discount_totals     = array();
			$this->coupon_discount_tax_totals = array();
			$this->applied_coupons            = array();
			$this->totals                     = $this->default_totals;

			$this->fees_api->remove_all_fees();
		}

		/**
		 * Add a product to the cart.
		 *
		 * @param int   $product_id     contains the id of the product to add to the cart.
		 * @param int   $quantity       contains the quantity of the item to add.
		 * @param int   $variation_id   ID of the variation being added to the cart.
		 * @param array $variation      attribute values.
		 * @param array $cart_item_data extra cart item data we want to pass into the item.
		 *
		 * @return string|bool $cart_item_key
		 * @throws Exception Plugins can throw an exception to prevent adding to cart.
		 */
		public function add_to_cart( $product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {
			$this->stash_current_notices();

			add_filter( 'yith_wcdp_process_cart_item_product_change', '__return_false' );

			/**
			 * DO_ACTION: yith_wcdp_before_add_to_support_cart
			 *
			 * Action triggered before adding the product to the cart.
			 *
			 * @param int   $product_id     Contains the id of the product to add to the cart.
			 * @param int   $quantity       Contains the quantity of the item to add.
			 * @param int   $variation_id   ID of the variation being added to the cart.
			 * @param array $variation      Attribute values.
			 * @param array $cart_item_data Extra cart item data we want to pass into the item.
			 */
			do_action( 'yith_wcdp_before_add_to_support_cart', $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			$cart_item_key = parent::add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			/**
			 * DO_ACTION: yith_wcdp_after_add_to_support_cart
			 *
			 * Action triggered after adding the product to the cart.
			 *
			 * @param int   $product_id     Contains the id of the product to add to the cart.
			 * @param int   $quantity       Contains the quantity of the item to add.
			 * @param int   $variation_id   ID of the variation being added to the cart.
			 * @param array $variation      Attribute values.
			 * @param array $cart_item_data Extra cart item data we want to pass into the item.
			 * @param array $cart_item      Cart item data of the product.
			 */
			do_action( 'yith_wcdp_after_add_to_support_cart', $product_id, $quantity, $variation_id, $variation, $cart_item_data, $this->get_cart_item( $cart_item_key ), $cart_item_key );

			$this->restore_notices();

			return $cart_item_key;
		}

		/**
		 * Removes stock handling for items in support cart
		 * (stock level is checked on main cart, this cart just need for additional calculations)
		 *
		 * @return bool
		 */
		public function check_cart_item_stock() {
			return true;
		}

		/**
		 * Store current notices for future usage
		 *
		 * @return void
		 * @since 1.3.11
		 */
		public function stash_current_notices() {
			$this->notices = wc_get_notices();
		}

		/**
		 * Restore previous status of the notices
		 *
		 * @return void
		 * @since 1.3.11
		 */
		public function restore_notices() {
			wc_clear_notices();

			if ( ! empty( $this->notices ) ) {
				foreach ( $this->notices as $notice_type => $notices ) {
					foreach ( $notices as $notice ) {
						wc_add_notice( $notice['notice'], $notice_type );
					}
				}
			}
		}
	}
}
