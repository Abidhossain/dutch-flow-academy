<?php
/**
 * Compatibility class with Bundle Products
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Deposits / Down Payments
 * @version 1.3.1
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Products_Bundle_Compatibility' ) ) {
	/**
	 * Deposit - Product Bundles compatibility
	 *
	 * @since 1.3.1
	 */
	class YITH_WCDP_YITH_Products_Bundle_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Products_Bundle_Compatibility
		 * @since 1.1.3
		 */
		protected static $instance;

		/**
		 * Register current bundle product id
		 *
		 * @var int Product ID
		 */
		protected $bundle;

		/**
		 * Constructor.
		 *
		 * @since 1.3.1
		 */
		public function __construct() {
			add_filter( 'yith_wcpb_ajax_get_bundle_total_price_response', array( $this, 'add_deposit_value' ), 10, 2 );
			add_action( 'yith_wcdp_yith_bundle_add_to_cart', array( $this, 'add_single_add_deposit_to_cart' ), 10, 1 );

			add_action( 'yith_wcpb_before_bundle_woocommerce_add_to_cart', array( $this, 'set_deposit_filters_for_bundle' ), 10, 2 );
			add_action( 'yith_wcpb_after_bundle_woocommerce_add_to_cart', array( $this, 'remove_deposit_filters_for_bundle' ), 10 );

			// cart/checkout handling.
			add_filter( 'yith_wcpb_woocommerce_cart_item_price', array( $this, 'filter_bundle_subtotal' ), 10, 3 );
			add_filter( 'yith_wcpb_bundle_pip_bundled_items_subtotal', array( $this, 'filter_bundle_subtotal' ), 10, 3 );
			add_action( 'yith_wcdp_before_add_to_support_cart', array( $this, 'remove_bundle_handling' ) );
			add_action( 'yith_wcdp_before_suborder_add_to_cart', array( $this, 'remove_bundle_handling' ) );
			add_action( 'yith_wcdp_after_add_to_support_cart', array( $this, 'restore_bundle_handling' ) );
			add_action( 'yith_wcdp_after_add_to_support_cart', array( $this, 'fix_bundled_by_value' ), 10, 7 );
			add_action( 'yith_wcdp_after_populate_support_cart', array( $this, 'sync_bundled_items' ) );

			// handle components in cart.
			add_filter( 'yith_wcdp_deposit_value', array( $this, 'filter_components_totals' ), 10, 4 );
			add_filter( 'yith_wcdp_deposit_balance', array( $this, 'filter_components_totals' ), 10, 4 );
			add_action( 'yith_wcpb_after_bundle_woocommerce_add_to_cart', array( $this, 'filter_bundle_balance' ), 10 );
			add_filter( 'yith_wcdp_full_amount_item', array( $this, 'remove_item_for_components' ), 10, 2 );
			add_filter( 'yith_wcdp_balance_item', array( $this, 'remove_item_for_components' ), 10, 2 );
			add_filter( 'yith_wcdp_checkout_balance_key', array( $this, 'filter_components_balance' ), 10, 3 );

			// handle components in order.
			add_filter( 'yith_wcdp_print_deposit_order_item_template', array( $this, 'filter_order_item_for_components' ), 10, 3 );
			add_filter( 'yith_wcdp_email_deposit_table_skip_suborder', array( $this, 'filter_out_components_from_email' ), 10, 3 );

			// handle components shipping.
			add_filter( 'yith_wcdp_shipping_package_key', array( $this, 'filter_components_package' ), 10, 3 );
		}

		/**
		 * Add deposit value to fragments used by Bundle plugin
		 *
		 * @param mixed      $response Fragments used by bundle.
		 * @param WC_Product $product  Current product.
		 *
		 * @return mixed Filtered array of fragments
		 */
		public function add_deposit_value( $response, $product ) {
			if ( ! empty( $product->per_items_pricing ) ) {
				$response['deposit_html'] = wc_price( YITH_WCDP_Deposits::get_deposit( $product->get_id(), false, $response['price'], 'view' ) );
			}

			return $response;
		}

		/**
		 * Add "Deposit Form" to Bundle product page
		 *
		 * @param WC_Product $product Current product.
		 *
		 * @return void
		 */
		public function add_single_add_deposit_to_cart( $product ) {
			YITH_WCDP_Frontend()->print_single_add_deposit_to_cart_template( $product->get_id() );
		}

		/* === USE BUNDLE PREFERENCES FOR BUNDLED PRODUCT === */

		/**
		 * Set filters for deposits, in order to use bundle instead of bundled items
		 *
		 * @param string $cart_item_key Cart item key.
		 * @param int    $bundle_id     Bundle id.
		 */
		public function set_deposit_filters_for_bundle( $cart_item_key, $bundle_id ) {
			$this->bundle = $bundle_id;

			// filter all deposit options, to refer them to main bundle product.
			add_filter( 'yith_wcdp_is_deposit_enabled_on_product', array( $this, 'is_deposit_enabled_on_bundle' ), 10, 2 );
			add_filter( 'yith_wcdp_is_deposit_mandatory', array( $this, 'is_deposit_mandatory_for_bundle' ), 10, 2 );
			add_filter( 'yith_wcdp_is_deposit_expired_for_product', array( $this, 'is_deposit_expired_for_bundle' ), 10, 2 );
			add_filter( 'yith_wcdp_deposit_type', array( $this, 'get_deposit_type_for_bundle' ), 10, 2 );
			add_filter( 'yith_wcdp_deposit_amount', array( $this, 'get_deposit_amount_for_bundle' ), 10, 2 );
			add_filter( 'yith_wcdp_deposit_rate', array( $this, 'get_deposit_rate_for_bundle' ), 10, 2 );
		}

		/**
		 * Filter is_deposit_enabled method to use bundle
		 *
		 * @param bool $enabled    Whether deposit is enabled on bundle.
		 * @param int  $product_id Product id.
		 *
		 * @return bool Filtered value
		 */
		public function is_deposit_enabled_on_bundle( $enabled, $product_id ) {
			if ( $product_id !== $this->bundle ) {
				return YITH_WCDP_Deposits::is_enabled( $this->bundle );
			}

			return $enabled;
		}

		/**
		 * Filter is_deposit_mandatory method to use bundle
		 *
		 * @param bool $mandatory  Whether deposit is mandatory on bundle.
		 * @param int  $product_id Product id.
		 *
		 * @return bool Filtered value
		 */
		public function is_deposit_mandatory_for_bundle( $mandatory, $product_id ) {
			if ( $product_id !== $this->bundle ) {
				return YITH_WCDP_Deposits::is_mandatory( $this->bundle );
			}

			return $mandatory;
		}

		/**
		 * Filter is_deposit_expired method to use bundle
		 *
		 * @param bool $expired    Whether deposit is expired on bundle.
		 * @param int  $product_id Product id.
		 *
		 * @return bool Filtered value
		 */
		public function is_deposit_expired_for_bundle( $expired, $product_id ) {
			if ( $product_id !== $this->bundle ) {
				return YITH_WCDP_Deposits::has_expired( $this->bundle );
			}

			return $expired;
		}

		/**
		 * Filter get_deposit_type method to use bundle
		 *
		 * @param string $type       Deposit type for bundle.
		 * @param int    $product_id Product id.
		 *
		 * @return bool Filtered value
		 */
		public function get_deposit_type_for_bundle( $type, $product_id ) {
			if ( $product_id !== $this->bundle ) {
				return YITH_WCDP_Deposits::get_type( $this->bundle );
			}

			return $type;
		}

		/**
		 * Filter get_deposit_amount method to use bundle
		 *
		 * @param float $amount     Deposit amount for bundle.
		 * @param int   $product_id Product id.
		 *
		 * @return bool Filtered value
		 */
		public function get_deposit_amount_for_bundle( $amount, $product_id ) {
			if ( $product_id !== $this->bundle ) {
				return YITH_WCDP_Deposits::get_amount( $this->bundle );
			}

			return $amount;
		}

		/**
		 * Filter get_deposit_rate method to use bundle
		 *
		 * @param string $rate       Deposit rate for bundle.
		 * @param int    $product_id Product id.
		 *
		 * @return bool Filtered value
		 */
		public function get_deposit_rate_for_bundle( $rate, $product_id ) {
			if ( $product_id !== $this->bundle ) {
				return YITH_WCDP_Deposits::get_rate( $this->bundle );
			}

			return $rate;
		}

		/**
		 * Remove all filters previously set for bundle product
		 *
		 * @return void
		 */
		public function remove_deposit_filters_for_bundle() {
			remove_filter( 'yith_wcdp_is_deposit_enabled_on_product', array( $this, 'is_deposit_enabled_on_bundle' ) );
			remove_filter( 'yith_wcdp_is_deposit_mandatory', array( $this, 'is_deposit_mandatory_for_bundle' ) );
			remove_filter( 'yith_wcdp_is_deposit_expired_for_product', array( $this, 'is_deposit_expired_for_bundle' ) );
			remove_filter( 'yith_wcdp_deposit_type', array( $this, 'get_deposit_type_for_bundle' ) );
			remove_filter( 'yith_wcdp_deposit_amount', array( $this, 'get_deposit_amount_for_bundle' ) );
			remove_filter( 'yith_wcdp_deposit_rate', array( $this, 'get_deposit_rate_for_bundle' ) );
		}

		/* === CART/CHECKOUT HANDLING FOR DEPOSIT BUNDLES === */

		/**
		 * Filter bundle subtotal
		 *
		 * @param float $price Price.
		 * @param mixed $arg1  Cart item / Bundle price.
		 * @param mixed $arg2  Bundle price / Cart item.
		 *
		 * @return float Filtered bundle price
		 */
		public function filter_bundle_subtotal( $price, $arg1, $arg2 ) {
			if ( doing_filter( 'yith_wcpb_woocommerce_cart_item_price' ) ) {
				$cart_item    = $arg2;
				$bundle_price = $arg1;
			} else {
				$cart_item    = $arg1;
				$bundle_price = $arg2;
			}

			if ( empty( $cart_item['deposit'] ) ) {
				return $price;
			}

			$product = $cart_item['data'];

			if ( YITH_WCDP_Deposits::is_enabled( $product->get_id() ) ) {
				$deposit = YITH_WCDP_Deposits::get_deposit( $product->get_id(), false, $bundle_price );
				$price   = wc_price( yith_wcdp_get_price_to_display( $product, array( 'price' => $deposit ) ) );
			}

			return $price;
		}

		/**
		 * Filter bundle balance, to account for fixed deposit, whenever needed
		 *
		 * @param string $cart_item_key Cart item key of the bundle item.
		 */
		public function filter_bundle_balance( $cart_item_key ) {
			$cart     = WC()->cart;
			$contents = $cart ? $cart->get_cart_contents() : array();
			$item     = isset( $contents[ $cart_item_key ] ) ? $contents[ $cart_item_key ] : false;

			if ( ! $item || ! isset( $item['deposit'] ) ) {
				return;
			}

			$product = $item['data'];

			if ( ! $product || ! $product->per_items_pricing || 'amount' !== $item['deposit_type'] ) {
				return;
			}

			$bundle_price = yith_wcpb_frontend()->calculate_bundled_items_price_by_cart( $item );
			$new_balance  = max( 0, $bundle_price - $item['deposit_value'] );

			$cart->cart_contents[ $cart_item_key ]['deposit_balance'] = $new_balance;
		}

		/**
		 * Remove bundle handling during support cart processing
		 *
		 * @return void
		 */
		public function remove_bundle_handling() {
			remove_action( 'woocommerce_add_to_cart', array( YITH_WCPB_Frontend(), 'woocommerce_add_to_cart' ) );
		}

		/**
		 * Restore bundle handling during support cart processing
		 *
		 * @return void
		 */
		public function restore_bundle_handling() {
			add_action( 'woocommerce_add_to_cart', array( YITH_WCPB_Frontend(), 'woocommerce_add_to_cart' ), 10, 6 );
		}

		/**
		 * Correct cart item references for the support cart (bundled_by meta)
		 *
		 * @param int    $product_id     Product id.
		 * @param int    $quantity       Item quantity.
		 * @param int    $variation_id   Variation id.
		 * @param int    $variation      Variation data.
		 * @param array  $cart_item_data Cart item data.
		 * @param array  $cart_item      Cart item.
		 * @param string $cart_item_key Cart item key.
		 */
		public function fix_bundled_by_value( $product_id, $quantity, $variation_id, $variation, $cart_item_data, $cart_item, $cart_item_key ) {
			$support_cart = YITH_WCDP()->get_support_cart( false );

			if ( ! $support_cart || ! isset( $cart_item['bundled_by'] ) ) {
				return;
			}

			$new_item = $support_cart->get_support_item( $cart_item['bundled_by'] );

			if ( ! $new_item ) {
				return;
			}

			$cart_item['bundled_by'] = $new_item;

			$support_cart->cart_contents[ $cart_item_key ] = $cart_item;

			YITH_WCPB_Frontend()->woocommerce_add_cart_item( $cart_item, $cart_item_key );
		}

		/**
		 * After all items are added to support cart, correct bundled_items meta for bundle products (correct cart item references)
		 */
		public function sync_bundled_items() {
			$cart = YITH_WCDP()->get_support_cart( false );

			foreach ( $cart->cart_contents as $key => $cart_item ) {
				if ( isset( $cart_item['cartstamp'] ) && isset( $cart_item['bundled_items'] ) ) {
					$cart->cart_contents[ $key ]['bundled_items'] = array();
				}
			}

			foreach ( $cart->cart_contents as $key => $cart_item ) {
				if ( isset( $cart_item['bundled_by'] ) && isset( $cart->cart_contents[ $cart_item['bundled_by'] ]['bundled_items'] ) ) {
					$cart->cart_contents[ $cart_item['bundled_by'] ]['bundled_items'][] = $key;
					$cart->cart_contents[ $cart_item['bundled_by'] ]['bundled_items']   = array_unique( $cart->cart_contents[ $cart_item['bundled_by'] ]['bundled_items'] );
				}
			}
		}

		/**
		 * Filters components totals (deposit/balance cost), and set them to 0 if ! isPerItemPricing (they don't contribute to overall Composite Product price)
		 *
		 * @param float $total        Original component total (deposit/balance).
		 * @param int   $product_id   Product id.
		 * @param int   $variation_id Variation id.
		 * @param array $cart_item    Cart item being processed.
		 */
		public function filter_components_totals( $total, $product_id, $variation_id, $cart_item ) {
			if ( empty( $cart_item['deposit'] ) ) {
				return $total;
			}

			if ( isset( $cart_item['cartstamp'] ) ) {
				$bundle_product = $cart_item['data'];

				if ( $bundle_product && $bundle_product->per_items_pricing && 'amount' !== $cart_item['deposit_type'] ) {
					return 0;
				}
			}

			if ( isset( $cart_item['bundled_by'] ) ) {
				$cart          = WC()->cart;
				$cart_contents = $cart ? $cart->get_cart_contents() : array();
				$parent_object = isset( $cart_contents[ $cart_item['bundled_by'] ] ) ? $cart_contents[ $cart_item['bundled_by'] ]['data'] : null;

				if ( $parent_object && ( ! $parent_object->per_items_pricing || 'amount' === $cart_item['deposit_type'] ) ) {
					return 0;
				}
			}

			return $total;
		}

		/**
		 * Remove deposit/balance notes for the components from Cart/Checkout, when ! isPerItemPricing (they don't contribute to overall Composite Product price)
		 *
		 * @param array $item      Array that will be used to show meta in cart item (could be either deposit or balance meta).
		 * @param array $cart_item Cart item.
		 */
		public function remove_item_for_components( $item, $cart_item ) {
			if ( isset( $cart_item['cartstamp'] ) ) {
				$bundle_product = $cart_item['data'];

				if ( $bundle_product && $bundle_product->per_items_pricing && 'amount' !== $cart_item['deposit_type'] ) {
					return false;
				}
			}

			if ( isset( $cart_item['bundled_by'] ) ) {
				$cart          = WC()->cart;
				$cart_contents = $cart ? $cart->get_cart_contents() : array();
				$parent_object = isset( $cart_contents[ $cart_item['bundled_by'] ] ) ? $cart_contents[ $cart_item['bundled_by'] ]['data'] : null;

				if ( $parent_object && ( ! $parent_object->per_items_pricing || 'amount' === $cart_item['deposit_type'] ) ) {
					return false;
				}
			}

			return $item;
		}

		/**
		 * Filter shipping package where components should be placed
		 *
		 * @param string $package_key   Original package key for current item.
		 * @param string $cart_item_key Current item key.
		 * @param array  $cart_item     Current item.
		 *
		 * @return string Filtered package key for current item.
		 */
		public function filter_components_package( $package_key, $cart_item_key, $cart_item ) {
			if ( 'common' === $package_key ) {
				return $package_key;
			}

			if ( isset( $cart_item['bundled_by'] ) ) {
				return $cart_item['bundled_by'];
			}

			return $package_key;
		}

		/**
		 * Filters the balance order where a specific item should be placed (places components in the same balance as Composite)
		 *
		 * @param string                $key     Balance key.
		 * @param int                   $item_id Id of the item being processed.
		 * @param WC_Order_Item_Product $item    Item being processed.
		 *
		 * @return string Filtered balance key.
		 */
		public function filter_components_balance( $key, $item_id, $item ) {
			if ( 'common' === $key ) {
				return $key;
			}

			$bundled_by = $item->get_meta( '_bundled_by' );

			if ( ! $bundled_by ) {
				return $key;
			}

			$order          = $item->get_order();
			$order_items    = $order->get_items();
			$parent_item_id = false;

			foreach ( $order_items as $order_item_id => $order_item ) {
				$bundle_cart_key = $order_item->get_meta( '_yith_bundle_cart_key' );

				if ( ! $bundle_cart_key ) {
					continue;
				}

				if ( $bundle_cart_key !== $bundled_by ) {
					continue;
				}

				$parent_item_id = $order_item_id;
				break;
			}

			if ( ! $parent_item_id ) {
				return $key;
			}

			return $parent_item_id;
		}

		/* === HANDLE COMPONENTS IN ORDER === */

		/**
		 * Remove deposit/balance notes for the components from Order details page, when ! isPerItemPricing (they don't contribute to overall Composite Product price)
		 *
		 * @param string                $template Template to show for current item.
		 * @param WC_Order_Item_Product $item     Current order item.
		 * @param WC_order              $order    Order object.
		 *
		 * @return string Filtered template.
		 */
		public function filter_order_item_for_components( $template, $item, $order ) {
			$bundled_by   = $item->get_meta( '_bundled_by' );
			$bundle_key   = $item->get_meta( '_yith_bundle_cart_key' );
			$deposit_type = $item->get_meta( '_deposit_type' );

			if ( $bundled_by ) {
				$order_items    = $order->get_items();
				$parent_item_id = false;

				foreach ( $order_items as $order_item_id => $order_item ) {
					$bundle_cart_key = $order_item->get_meta( '_yith_bundle_cart_key' );

					if ( ! $bundle_cart_key ) {
						continue;
					}

					if ( $bundle_cart_key !== $bundled_by ) {
						continue;
					}

					$parent_item_id = $order_item_id;
					break;
				}

				if ( $parent_item_id ) {
					$parent_item = $order->get_item( $parent_item_id );
					$product     = $parent_item->get_product();

					if ( ! $product->per_items_pricing || 'amount' === $deposit_type ) {
						return '';
					}
				}
			}

			if ( $bundle_key ) {
				$product = $item->get_product();

				if ( $product->per_items_pricing && 'amount' !== $deposit_type ) {
					return '';
				}
			}

			return $template;
		}

		/**
		 * Removes components from deposit email table
		 *
		 * @param bool                  $skip     Whether to skip item.
		 * @param WC_Order              $suborder Suborder object.
		 * @param WC_Order_Item_Product $item     Item object.
		 *
		 * @return bool Whether to skip item or not.
		 */
		public function filter_out_components_from_email( $skip, $suborder, $item ) {
			if ( $item->get_meta( '_bundled_by' ) ) {
				return true;
			}

			return $skip;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Products_Bundle_Compatibility
		 * @since 1.3.1
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
