<?php
/**
 * Compatibility class with Composite Products
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.1.3
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Composite_Products_Compatibility' ) ) {
	/**
	 * Deposit - Composite compatibility
	 *
	 * @since 1.1.3
	 */
	class YITH_WCDP_YITH_Composite_Products_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Composite_Products_Compatibility
		 * @since 1.1.3
		 */
		protected static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.1.3
		 */
		public function __construct() {
			add_action( 'yith_wcdp_yith-composite_add_to_cart', array( $this, 'add_deposit_on_composite' ), 10, 1 );
			add_filter( 'yith_wcp_composite_children_subtotal', array( $this, 'update_composite_children_subtotal' ), 10, 5 );

			// handle components in cart.
			add_filter( 'yith_wcdp_deposit_value', array( $this, 'filter_components_totals' ), 10, 4 );
			add_filter( 'yith_wcdp_deposit_balance', array( $this, 'filter_components_totals' ), 10, 4 );
			add_filter( 'yith_wcdp_full_amount_item', array( $this, 'remove_item_for_components' ), 10, 2 );
			add_filter( 'yith_wcdp_balance_item', array( $this, 'remove_item_for_components' ), 10, 2 );
			add_filter( 'yith_wcdp_checkout_balance_key', array( $this, 'filter_components_balance' ), 10, 3 );

			// handle components in order.
			add_filter( 'yith_wcdp_print_deposit_order_item_template', array( $this, 'filter_order_item_for_components' ), 10, 2 );

			// handle components shipping.
			add_filter( 'yith_wcdp_shipping_package_key', array( $this, 'filter_components_package' ), 10, 3 );

			// change checkout process.
			add_filter( 'woocommerce_add_cart_item', array( $this, 'update_cart_item' ), 25, 3 );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'update_cart_item_data' ), 25, 3 );

			// fix for support cart.
			add_action( 'yith_wcdp_before_add_to_support_cart', array( $this, 'adding_to_support_cart' ) );
			add_action( 'yith_wcdp_after_add_to_support_cart', array( $this, 'added_to_support_cart' ) );

			add_filter( 'woocommerce_product_needs_shipping', array( $this, 'check_composite_shipping' ), 99, 2 );
		}

		/**
		 * Add deposit options for Composite Products
		 *
		 * @param WC_Product $product Current product.
		 *
		 * @return void
		 *
		 * @since 1.1.3
		 */
		public function add_deposit_on_composite( $product ) {
			YITH_WCDP_Frontend()->print_single_add_deposit_to_cart_template( $product->get_id() );
		}

		/* === COMPONENTS IN CART/CHECKOUT HANDLING === */

		/**
		 * Filters components totals (deposit/balance cost), and set them to 0 if ! isPerItemPricing (they don't contribute to overall Composite Product price)
		 *
		 * @param float $total        Original component total (deposit/balance).
		 * @param int   $product_id   Product id.
		 * @param int   $variation_id Variation id.
		 * @param array $cart_item    Cart item being processed.
		 */
		public function filter_components_totals( $total, $product_id, $variation_id, $cart_item ) {
			if ( isset( $cart_item['yith_wcp_child_component_data'] ) ) {
				$parent_object = $cart_item['yith_wcp_child_component_data']['yith_wcp_component_parent_object'];

				if ( 'yith_wcdp_deposit_value' === current_action() && 'amount' === $cart_item['deposit_type'] ) {
					return 0;
				}

				if ( ! $parent_object->isPerItemPricing() ) {
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
			if ( isset( $cart_item['yith_wcp_child_component_data'] ) ) {
				$parent_object = $cart_item['yith_wcp_child_component_data']['yith_wcp_component_parent_object'];

				if ( ! $parent_object->isPerItemPricing() || 'amount' !== $cart_item['deposit_type'] ) {
					return false;
				}
			}

			return $item;
		}

		/**
		 * Filters components total as it was calculated by YITH WooCommerce Composite Products, and changes it to its deposit value
		 * Deposit value is calculated using deposit options from Composite product
		 *
		 * @param float                     $subtotal        Current components subtotal.
		 * @param WC_Product_Yith_Composite $product         Composite product.
		 * @param array                     $component_data  Component for current cart item.
		 * @param string                    $cart_item_key   Current cart item key.
		 * @param int                       $global_quantity Quantity of the Composite product in cart.
		 *
		 * @return float Filtered components subtotal
		 *
		 * @since 1.1.3
		 */
		public function update_composite_children_subtotal( $subtotal, $product, $component_data, $cart_item_key, $global_quantity ) {
			$cart_contents = WC()->cart->cart_contents;

			if ( isset( $cart_contents[ $cart_item_key ] ) && isset( $cart_contents[ $cart_item_key ]['deposit'] ) && $cart_contents[ $cart_item_key ]['deposit'] ) {
				$cart         = WC()->cart->get_cart();
				$new_subtotal = 0;

				if ( 'amount' === $cart_contents[ $cart_item_key ]['deposit_type'] ) {
					return $new_subtotal;
				}

				$composite_stored_data = $product->getComponentsData();

				foreach ( $composite_stored_data as $key => $component_item ) {
					if ( ! isset( $component_data['selection_data'][ $key ] ) || $component_data['selection_data'][ $key ] <= 0 ) {
						continue;
					}

					// Variation selected.
					if ( isset( $component_data['selection_variation_data'][ $key ] ) && $component_data['selection_variation_data'][ $key ] > 0 ) {
						$child_product = wc_get_product( $component_data['selection_variation_data'][ $key ] );
					} else {
						$child_product = wc_get_product( $component_data['selection_data'][ $key ] );
					}

					// Read child items of composite products.
					foreach ( $cart as $cart_item_key_ass => $value ) {
						if ( ! isset( $value['yith_wcp_child_component_data'] ) ) {
							continue;
						}

						$cart_child_meta_data = $value['yith_wcp_child_component_data'];

						if ( isset( $cart_child_meta_data['yith_wcp_cart_parent_key'] ) && $cart_child_meta_data['yith_wcp_cart_parent_key'] === $cart_item_key && $cart_child_meta_data['yith_wcp_component_key'] === $key ) {
							$child_quantity     = $component_data['selection_quantity'][ $key ];
							$wcp_component_item = $cart_child_meta_data['yith_wcp_component_parent_object']->getComponentItemByKey( $cart_child_meta_data['yith_wcp_component_key'] );
							$sold_individually  = isset( $wcp_component_item['sold_individually'] ) && $wcp_component_item['sold_individually'] ? $wcp_component_item['sold_individually'] : false;

							if ( $sold_individually ) {
								$single_total = yit_get_display_price( $child_product ) * $child_quantity;
							} else {
								$single_total = yit_get_display_price( $child_product ) * $global_quantity;
							}

							$new_subtotal += YITH_WCDP_Deposits::get_deposit( $child_product->get_id(), false, $single_total );
							break;
						}
					}
				}

				$subtotal_deposit = min( $new_subtotal, $subtotal );

				$subtotal = $subtotal_deposit;
			}

			return $subtotal;
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

			$component_data = $item->get_meta( '_yith_wcp_child_component_data' );

			if ( ! $component_data ) {
				return $key;
			}

			$order           = $item->get_order();
			$order_items     = $order->get_items();
			$parent_cart_key = isset( $component_data['yith_wcp_cart_parent_key'] ) ? $component_data['yith_wcp_cart_parent_key'] : false;
			$parent_item_id  = false;

			if ( ! $parent_cart_key ) {
				return $key;
			}

			foreach ( $order_items as $order_item_id => $order_item ) {
				$parent_component_data = $order_item->get_meta( '_yith_wcp_component_data' );

				if ( ! $parent_component_data ) {
					continue;
				}

				if ( ! isset( $parent_component_data['cart_item_key'] ) || $parent_component_data['cart_item_key'] !== $parent_cart_key ) {
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
		 *
		 * @return string Filtered template.
		 */
		public function filter_order_item_for_components( $template, $item ) {
			$component_data = $item->get_meta( '_yith_wcp_child_component_data' );

			if ( $component_data ) {
				$parent_object = $component_data['yith_wcp_component_parent_object'];

				if ( ! $parent_object->isPerItemPricing() || 'amount' !== $item['deposit_type'] ) {
					return '';
				}
			}

			return $template;
		}

		/* === HANDLE COMPONENTS SHIPPING === */

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

			if ( isset( $cart_item['yith_wcp_child_component_data'] ) ) {
				return $cart_item['yith_wcp_child_component_data']['yith_wcp_cart_parent_key'];
			}

			return $package_key;
		}

		/* === CHECKOUT PROCESS METHODS === */

		/**
		 * Update cart item when deposit is selected
		 *
		 * @param array $cart_item Current cart item.
		 *
		 * @return mixed Filtered cart item
		 * @since 1.0.0
		 */
		public function update_cart_item( $cart_item ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$composite_parent = isset( $cart_item['yith_wcp_child_component_data'] ) ? $cart_item['yith_wcp_child_component_data'] : false;

			if ( ! $composite_parent ) {
				return $cart_item;
			}

			/**
			 * Product objects for current cart item
			 *
			 * @var $product          \WC_Product
			 * @var $original_product \WC_Product
			 */
			$product          = $cart_item['yith_wcp_child_component_data']['yith_wcp_component_parent_object'];
			$original_product = $cart_item['data'];

			$product_id = $product->get_id();

			if ( YITH_WCDP_Deposits::is_enabled( $product_id ) && ! $product->get_meta( 'yith_wcdp_deposit' ) && ! apply_filters( 'yith_wcdp_skip_cart_item_processing', false, $cart_item ) ) {
				$deposit_forced = YITH_WCDP_Deposits::is_mandatory( $product_id );

				$deposit_value   = apply_filters( 'yith_wcdp_deposit_value', YITH_WCDP_Deposits::get_deposit( $original_product->get_id(), false, $original_product->get_price() ), $product_id, false, $cart_item );
				$deposit_balance = apply_filters( 'yith_wcdp_deposit_balance', max( $original_product->get_price() - $deposit_value, 0 ), $product_id, false, $cart_item );

				if (
					apply_filters( 'yith_wcdp_process_cart_item_product_change', true, $cart_item ) &&
					isset( $_REQUEST['add-to-cart'] ) &&
					( ( $deposit_forced && ! defined( 'YITH_WCDP_PROCESS_SUBORDERS' ) ) || ( isset( $_REQUEST['payment_type'] ) && 'deposit' === $_REQUEST['payment_type'] ) )
				) {
					$product->set_price( $deposit_value );
					$product->update_meta_data( 'yith_wcdp_deposit', true );

					if ( apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
						$product->set_virtual( true );
					}

					$cart_item['deposit_value']   = $deposit_value;
					$cart_item['deposit_balance'] = $deposit_balance;
				}
			}

			return $cart_item;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Add cart item data when deposit is selected, to store info to save with order
		 *
		 * @param array $cart_item_data Currently saved cart item data.
		 * @param int   $product_id     Product id.
		 * @param int   $variation_id   Variation id.
		 *
		 * @return mixed Filtered cart item data
		 * @since 1.0.0
		 */
		public function update_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$composite_parent = isset( $cart_item_data['yith_wcp_child_component_data'] ) ? $cart_item_data['yith_wcp_child_component_data'] : false;

			if ( ! $composite_parent ) {
				return $cart_item_data;
			}

			$product          = $cart_item_data['yith_wcp_child_component_data']['yith_wcp_component_parent_object'];
			$original_product = wc_get_product( ! empty( $variation_id ) ? $variation_id : $product_id );

			$product_id = $product->get_id();

			if ( YITH_WCDP_Deposits::is_enabled( $product_id ) && ! apply_filters( 'yith_wcdp_skip_cart_item_data_processing', false, $cart_item_data, $product ) ) {
				$deposit_forced = YITH_WCDP_Deposits::is_mandatory( $product_id );

				$deposit_type   = YITH_WCDP_Deposits::get_type( $product_id );
				$deposit_amount = YITH_WCDP_Deposits::get_amount( $product_id );
				$deposit_rate   = YITH_WCDP_Deposits::get_rate( $product_id );

				$deposit_value   = YITH_WCDP_Deposits::get_deposit( $product_id, false, $original_product->get_price() );
				$deposit_balance = max( $product->get_price() - $deposit_value, 0 );

				$process_deposit = ( $deposit_forced && ! defined( 'YITH_WCDP_PROCESS_SUBORDERS' ) ) || ( isset( $_REQUEST['payment_type'] ) && 'deposit' === $_REQUEST['payment_type'] );

				if ( apply_filters( 'yith_wcdp_process_deposit', $process_deposit, $cart_item_data ) ) {
					$cart_item_data['deposit']         = true;
					$cart_item_data['deposit_type']    = $deposit_type;
					$cart_item_data['deposit_amount']  = $deposit_amount;
					$cart_item_data['deposit_rate']    = $deposit_rate;
					$cart_item_data['deposit_value']   = $deposit_value;
					$cart_item_data['deposit_balance'] = $deposit_balance;
				}
			}

			return $cart_item_data;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Removes Composite child handling when adding a product to support cart
		 *
		 * @eturn void
		 */
		public function adding_to_support_cart() {
			add_filter( 'yith_wcp_composite_add_child_items', '__return_false' );
		}

		/**
		 * Enables again Composite child handling after adding a product to support cart
		 *
		 * @eturn void
		 */
		public function added_to_support_cart() {
			remove_filter( 'yith_wcp_composite_add_child_items', '__return_false' );
		}


		/**
		 * Removes shipping from composit product if it is also a virtual deposit
		 *
		 * @param bool       $needs_shipping Whether composite product needs shipping.
		 * @param WC_Product $product        Current product.
		 *
		 * @return bool Filtered value for needs shipping
		 */
		public function check_composite_shipping( $needs_shipping, $product ) {
			if ( $product->is_type( 'yith-composite' ) && apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
				$needs_shipping = false;
			}

			return $needs_shipping;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Composite_Products_Compatibility
		 * @since 1.1.3
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
