<?php
/**
 * Checkout class
 * Hooks functions to alter default WooCommerce Checkout process
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Checkout' ) ) {
	/**
	 * Checkout class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Checkout {

		/**
		 * Temp storage where to store real cart during plugin elaboration that requires a custom cart
		 *
		 * @var \WC_Cart
		 * @since 1.0.0
		 */
		protected static $cart;

		/**
		 * Temp storage where to store real applied coupon during plugin elaboration that requires a custom cart
		 *
		 * @var mixed
		 * @since 1.0.0
		 */
		protected static $coupons;

		/**
		 * Stores shipping packages before removing customizations.
		 *
		 * @var array
		 */
		protected static $packages;

		/**
		 * Stores chosen shipping methods, before removing balance packages
		 *
		 * @var array
		 */
		protected static $methods;

		/**
		 * Init class and hooks methods
		 */
		public static function init() {
			// filter order status after checkout.
			add_filter( 'woocommerce_payment_complete_order_status', array( self::class, 'deposit_payment_complete_status' ), 10, 3 );

			// process checkout.
			add_filter( 'woocommerce_checkout_create_order_line_item', array( self::class, 'update_order_item_data' ), 30, 3 );
			add_action( 'woocommerce_checkout_process', array( self::class, 'remove_balance_packages' ) );
			add_filter( 'rest_dispatch_request', array( self::class, 'remove_balance_packages_for_rest' ), 10, 3 );

			// suborders creation.
			add_action( 'woocommerce_checkout_order_processed', array( self::class, 'create_balance_suborder' ), 10, 2 );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( self::class, 'create_balance_suborder' ), 10, 2 );
			add_action( 'woocommerce_checkout_create_order_line_item', array( self::class, 'update_suborder_item_data' ), 40, 3 );
		}

		/* === CHECKOUT PROCESSING === */

		/**
		 * Set order to completed after payment if it only contains deposits, and if deposits are virtual
		 *
		 * @param string   $complete_status Order status after payment.
		 * @param int      $order_id        Current order it.
		 * @param WC_Order $order           Current order.
		 *
		 * @return string Filtered status
		 * @since 1.2.1
		 */
		public static function deposit_payment_complete_status( $complete_status, $order_id, $order ) {
			$deposit_only = true;

			if ( $order ) {
				$items = $order->get_items();

				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						if ( ! isset( $item['deposit'] ) ) {
							$deposit_only = false;
							break;
						}
					}
				}
			}

			if ( $deposit_only && apply_filters( 'yith_wcdp_virtual_on_deposit', true, $order ) ) {
				/**
				 * APPLY_FILTERS: yith_wcdp_virtual_deposit_order_status
				 *
				 * Filters the status that the virtual deposit will get.
				 *
				 * @param string Default value: 'completed'.
				 *
				 * @return string
				 */
				return apply_filters( 'yith_wcdp_virtual_deposit_order_status', 'completed' );
			}

			return $complete_status;
		}

		/**
		 * Store deposit cart item data as order item meta, on process checkout
		 *
		 * @param WC_Order_Item $item          Order item.
		 * @param string        $cart_item_key Key of the origin cart item.
		 * @param array         $values        Array of meta values for the cart item.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function update_order_item_data( $item, $cart_item_key, $values ) {
			if ( empty( $values['deposit'] ) ) {
				return;
			}

			$item->add_meta_data( '_deposit', true );
			$item->add_meta_data( '_deposit_type', $values['deposit_type'] );
			$item->add_meta_data( '_deposit_amount', $values['deposit_amount'] );
			$item->add_meta_data( '_deposit_rate', $values['deposit_rate'] );
			$item->add_meta_data( '_deposit_value', $values['deposit_value'] );
			$item->add_meta_data( '_deposit_balance', $values['deposit_balance'] );
			$item->add_meta_data( '_deposit_balance_shipping', self::get_balance_shipping_method( $cart_item_key ) );

			if ( isset( $values['ywraq_discount'] ) ) {
				$item->add_meta_data( '_ywraq_discount', $values['ywraq_discount'] );
			}
		}

		/**
		 * Remove balance packages from cart before proceeding with checkout of deposit order
		 */
		public static function remove_balance_packages() {
			self::$packages = WC()->cart->get_shipping_packages();
			self::$methods  = WC()->session->get( 'chosen_shipping_methods' );

			remove_filter( 'woocommerce_cart_needs_shipping_address', array( self::class, 'needs_shipping' ) );
			remove_filter( 'woocommerce_cart_needs_shipping', array( 'YITH_WCDP_Shipping', 'needs_shipping' ) );
			remove_filter( 'woocommerce_cart_shipping_packages', array( 'YITH_WCDP_Shipping', 'shipping_packages' ) );
			remove_action( 'woocommerce_after_calculate_totals', array( 'YITH_WCDP_Shipping', 'subtract_balance_shipping_costs' ), 10, 1 );
		}

		/**
		 * Remove balance packages from cart when in wc/store checkout endpoint
		 *
		 * @param mixed           $response Response to return, or null to process matching handler.
		 * @param WP_REST_Request $request  Request object.
		 * @param string          $route    Route matched for the request.
		 */
		public static function remove_balance_packages_for_rest( $response, $request, $route ) {
			// TODO: improve this check to be sure we're in the correct REST endpoint.
			if ( strpos( $route, 'wc/store' ) !== false && strpos( $route, 'checkout' ) !== false ) {
				// TODO: can we avoid to manually load the cart and leave this to \Automattic\WooCommerce\StoreApi\Routes\V1\Checkout::get_response?
				wc_load_cart();
				self::remove_balance_packages();
			}

			return $response;
		}

		/* === SUPPORT CART METHODS === */

		/**
		 * Create a support cart, used to temporarily replace actual cart and make shipping/tax calculation, suborders checkout
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function create_support_cart() {
			// save current cart.
			self::$cart    = WC()->session->get( 'cart' );
			self::$coupons = WC()->session->get( 'applied_coupons' );

			WC()->cart->empty_cart( true );
			WC()->cart->remove_coupons();
		}

		/**
		 * Restore original cart, saved in \YITH_WCDP_Suborders::_cart property
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function restore_original_cart() {
			// delete current cart.
			WC()->cart->empty_cart( true );
			WC()->cart->remove_coupons();

			// reload cart.
			/**
			 * APPLY_FILTERS: yith_wcdp_reset_cart_after_suborder_processing
			 *
			 * Filters if should reset cart after suborder processing.
			 *
			 * @param bool Default value: true.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcdp_reset_cart_after_suborder_processing', true ) ) {
				/**
				 * Depending on where \YITH_WCDP_Suborders::create_support_cart() was called, \YITH_WCDP_Suborders::_cart property may be
				 * an instance of WC_Cart class, or an array of cart contents (results of a previous WC_Cart::get_cart_for_session() )
				 *
				 * Instanceof prevents Fatal Error: method called on a non-object on single product pages, while
				 * WC_Cart::get_cart_for_session() avoid cart remaining empty after restore on process checkout
				 *
				 * @since 1.0.5
				 */
				WC()->session->set( 'cart', self::$cart instanceof WC_Cart ? self::$cart->get_cart_for_session() : self::$cart );
				WC()->session->set( 'applied_coupons', self::$coupons );

				WC()->cart->get_cart_from_session();

				/**
				 * Since we're sure cart has changed, let's force calculate_totals()
				 * Under some circumstances, not calculating totals at this point could effect WC()->cart->needs_payment() later,
				 * causing checkout process to redirect directly to Thank You page, instead of processing payment
				 *
				 * This was possibly caused by change in check performed at the end of get_cart_from_session() with WC 3.2
				 * Now conditions to recalculate totals after getting it from session are different then before
				 *
				 * @since 1.1.1
				 */
				WC()->cart->calculate_totals();
			}
		}

		/* === CREATE SUBORDERS === */

		/**
		 * Create suborders during process checkout, to let user finalize all his/her deposit in a separate balance order
		 *
		 * @param int|\WC_order $order       Processing order id/object.
		 * @param array         $posted_data Array of data posted with checkout.
		 *
		 * @return bool Status of the operation
		 * @since 1.0.0
		 */
		public static function create_balance_suborder( $order, $posted_data = null ) {

			if ( ! defined( 'YITH_WCDP_PROCESS_SUBORDERS' ) ) {
				define( 'YITH_WCDP_PROCESS_SUBORDERS', true );
			}

			// retrieve order.
			$parent_order = $order instanceof \WC_Order ? $order : wc_get_order( (int) $order );
			$order_id     = $parent_order->get_id();
			$suborders    = array();

			/**
			 * DO_ACTION: yith_wcdp_before_suborders_create
			 *
			 * Action triggered before creating deposit suborder.
			 *
			 * @param int   $order_id    The product ID.
			 * @param array $posted_data Array of data posted with checkout.
			 */
			do_action( 'yith_wcdp_before_suborders_create', $order_id, $posted_data );

			// if no order found, exit.
			if ( ! $parent_order ) {
				return false;
			}

			// populate posted_data array.
			$posted_data = $posted_data ? $posted_data : array_merge( $parent_order->get_base_data(), $parent_order->get_meta_data() );

			// remove data that explicitely reference parent order.
			unset( $posted_data['id'], $posted_data['number'] );

			// if order already process, exit.
			$suborders_meta = $parent_order->get_meta( '_full_payment_orders' );

			if ( $suborders_meta ) {
				return false;
			}

			if ( ! YITH_WCDP_Suborders()->has_deposits( $parent_order ) ) {
				return false;
			}

			// set has_deposit meta.
			$parent_order->update_meta_data( '_has_deposit', true );

			// retrieve order items.
			$items = $parent_order->get_items();

			// if no item is found, something is wrong: exit with fail status.
			if ( empty( $items ) ) {
				return false;
			}

			// filter out items that don't require balance.
			foreach ( $items as $item_id => $item ) {
				$product = $item->get_product();

				if ( ! $product || ! $product instanceof WC_Product ) {
					continue;
				}

				$balance_preference = YITH_WCDP_Options::get( 'create_balance', $product->get_id() );

				if ( ! $item->get_meta( '_deposit' ) || 'none' === $balance_preference ) {
					unset( $items[ $item_id ] );
				}
			}

			// if no item is left after removal, end operation successfully.
			if ( empty( $items ) ) {
				$parent_order->save();
				return true;
			}

			$balances = array();

			foreach ( $items as $item_id => $item ) {
				$balance_key = self::get_balance_key( $item_id, $item );

				if ( ! isset( $balances[ $balance_key ] ) ) {
					$balances[ $balance_key ] = array(
						'items'           => array(),
						'shipping_method' => 'common' === $balance_key ? self::get_balance_shipping_method( 'common' ) : $item->get_meta( '_deposit_balance_shipping' ),
					);
				}

				$balances[ $balance_key ]['items'][ $item_id ] = $item;
			}

			foreach ( $balances as $balance ) {
				// set correct shipping method.
				WC()->checkout()->shipping_methods = array_filter( (array) $balance['shipping_method'] );

				// create suborder.
				$new_suborder_id = self::build_suborder( $order_id, $balance['items'], $posted_data );

				// register suborder just created.
				if ( $new_suborder_id ) {
					$suborders[] = $new_suborder_id;
				}
			}

			$parent_order->update_meta_data( '_full_payment_orders', $suborders );
			$parent_order->save();

			/**
			 * DO_ACTION: yith_wcdp_after_suborders_create
			 *
			 * Action triggered after creating deposit suborder.
			 *
			 * @param array $suborders   Order suborders.
			 * @param int   $order_id    The product ID.
			 * @param array $posted_data Array of data posted with checkout.
			 */
			do_action( 'yith_wcdp_after_suborders_create', $suborders, $order_id, $posted_data );

			return true;
		}

		/**
		 * Change item price, when adding it to temp cart, to let user pay only order balance
		 *
		 * @param array $cart_item_data Array of items added to temp cart.
		 *
		 * @return mixed Filtered cart item data
		 * @since 1.0.0
		 */
		public static function set_item_full_amount_price( $cart_item_data ) {
			if ( empty( $cart_item_data['balance'] ) ) {
				return $cart_item_data;
			}

			$product = isset( $cart_item_data['data'] ) ? $cart_item_data['data'] : false;

			if ( ! $product instanceof WC_Product ) {
				return $cart_item_data;
			}

			$product->set_price( $cart_item_data['deposit_balance'] );
			$product->update_meta_data( 'yith_wcdp_balance', true );

			return $cart_item_data;
		}

		/**
		 * Store balance cart item data as suborder item meta, on process checkout
		 *
		 * @param WC_Order_Item $item          Order item.
		 * @param string        $cart_item_key Key of the origin cart item.
		 * @param array         $values        Array of meta values for the cart item.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function update_suborder_item_data( $item, $cart_item_key, $values ) {
			if ( empty( $values['balance'] ) ) {
				return;
			}

			$item->add_meta_data( '_balance', true );
			$item->add_meta_data( '_deposit_item_id', $values['deposit_item_id'] );
		}

		/**
		 * Retrieves key identifying balance order for curren item
		 *
		 * @param int                   $item_id Current item id.
		 * @param WC_Order_Item_Product $item    Current item.
		 *
		 * @return string Balance key.
		 */
		protected static function get_balance_key( $item_id, $item ) {
			$balance_type = get_option( 'yith_wcdp_balance_type', 'multiple' );

			if ( 'multiple' === $balance_type ) {
				$key = $item_id;
			} else {
				$key = 'common';
			}

			return apply_filters( 'yith_wcdp_checkout_balance_key', $key, $item_id, $item );
		}

		/**
		 * Retrieves preferred shipping method for a specific suborder
		 *
		 * @param string $key Something to identify suborder: it may be "common", for single suborder, or any cart item key, for multiple suborders.
		 * @return string|bool Preferred shipping method for the suborder, if could find one; false otherwise.
		 */
		protected static function get_balance_shipping_method( $key ) {
			if ( empty( self::$packages ) ) {
				return false;
			}

			$matching_packages = wp_list_filter( self::$packages, array( 'balance_package' => $key ) );

			if ( empty( $matching_packages ) ) {
				return false;
			}

			$matching_key    = key( $matching_packages );
			$matching_method = isset( self::$methods, self::$methods[ $matching_key ] ) ? self::$methods[ $matching_key ] : false;

			return $matching_method;
		}

		/**
		 * Returns correct status of the suborder
		 *
		 * @param int $suborder_id Id of the suborder.
		 *
		 * @return string Suborder status.
		 */
		protected static function get_suborder_status( $suborder_id ) {
			$status = YITH_WCDP_Suborders()->get_suborder_status( $suborder_id );

			if ( 'on-hold' === $status ) {
				$suborder = wc_get_order( $suborder_id );

				if ( $suborder ) {
					$suborder->update_meta_data( '_full_payment_needs_manual_payment', true );
					$suborder->save();
				}
			}

			return $status;
		}

		/**
		 * Returns date of expiration for the suborder, if expiration is enabled; false otherwise
		 *
		 * @param int $suborder_id Suborder id.
		 * @return string|bool Expiration date (in mysql format: Y-m-d); false if expiration is disabled.
		 */
		protected static function get_suborder_expiration_date( $suborder_id ) {
			$suborder = wc_get_order( $suborder_id );

			if ( ! $suborder ) {
				return false;
			}

			$will_expire = false;
			$expiration  = false;

			foreach ( $suborder->get_items() as $item ) {
				$product            = $item->get_product();
				$product_id         = $product->get_id();
				$expiration_enabled = YITH_WCDP_Options::get( 'enable_expiration', $product_id );

				if ( ! yith_plugin_fw_is_true( $expiration_enabled ) ) {
					continue;
				}

				$will_expire     = true;
				$item_expiration = YITH_WCDP_Deposits::get_expiration_date( $product_id );

				if ( $item_expiration && ( ! $expiration || $item_expiration < $expiration ) ) {
					$expiration = $item_expiration;
				}
			}

			$expiration = $expiration ? max( gmdate( 'Y-m-d' ), $expiration ) : false;

			/**
			 * APPLY_FILTERS: yith_wcdp_will_suborder_expire
			 *
			 * Filters if suborder will expire.
			 *
			 * @param bool     $will_expire Default value.
			 * @param WC_Order $suborder    The suborder.
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'yith_wcdp_will_suborder_expire', $will_expire, $suborder ) ) {
				return false;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_suborder_expiration_date
			 *
			 * Filters suborder expiration date.
			 *
			 * @param string|bool $expiration_date Default value.
			 * @param WC_Order    $suborder        The suborder.
			 *
			 * @return string|bool
			 */
			return apply_filters( 'yith_wcdp_suborder_expiration_date', $expiration, $suborder );
		}

		/**
		 * Retrieves the date system should notify user about pending balance
		 *
		 * @param int $suborder_id Suborder id.
		 * @return string Notification date, in mysql format (Y-m-d); if expiration isn't enabled, or no notification is required, returns false.
		 */
		protected static function get_suborder_notification_date( $suborder_id ) {
			$suborder   = wc_get_order( $suborder_id );
			$expiration = self::get_suborder_expiration_date( $suborder_id );

			if ( ! $suborder || ! $expiration ) {
				return false;
			}

			$should_notify = false;
			$limit         = false;

			foreach ( $suborder->get_items() as $item ) {
				$product              = $item->get_product();
				$product_id           = $product->get_id();
				$notification_enabled = YITH_WCDP_Options::get( 'notify_expiration', $product_id );
				$item_limit           = YITH_WCDP_Options::get( 'expiration_notification_limit', $product_id );

				if ( ! yith_plugin_fw_is_true( $notification_enabled ) ) {
					continue;
				}

				$should_notify = true;
				$limit         = max( $limit, $item_limit );
			}

			if ( ! apply_filters( 'yith_wcdp_will_expiration_be_notified', $should_notify && $limit, $suborder_id ) ) {
				return false;
			}

			$notification_ts   = strtotime( $expiration ) - yith_wcdp_duration_to_days( $limit ) * DAY_IN_SECONDS;
			$notification_ts   = max( time(), $notification_ts );
			$notification_date = gmdate( 'Y-m-d', $notification_ts );

			return apply_filters( 'yith_wcdp_expiration_notification_date', $notification_date, $suborder_id );
		}

		/**
		 * Create a single suborder with all the items included within second parameter
		 *
		 * @param int              $order_id    Parent order id.
		 * @param \WC_Order_Item[] $items       Array of order items to be processed for the suborder.
		 * @param mixed            $posted_data Array of data submitted by the user.
		 *
		 * @return int|bool Suborder id; false on failure
		 * @since 1.2.1
		 */
		protected static function build_suborder( $order_id, $items, $posted_data ) {
			// retrieve order.
			$parent_order = wc_get_order( $order_id );

			// create support cart
			// we use an default WC_cart instead of YITH_WCDP_Support_Cart because WC()->checkout will create orders only
			// from default session cart.
			self::create_support_cart();

			/**
			 * APPLY_FILTERS: yith_wcdp_virtual_on_deposit
			 *
			 * Filters if virtual on deposit.
			 *
			 * @param array         Default value.
			 * @param null|WC_Order Order if exists.
			 *
			 * @return string
			 */
			$virtual_deposit = apply_filters( 'yith_wcdp_virtual_on_deposit', true, null );

			// if we didn't use virtual deposit, balance shouldn't include shipping.
			( ! $virtual_deposit ) && add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );

			// cycle over order items.
			foreach ( $items as $item_id => $item ) {
				$deposit         = $item->get_meta( '_deposit' );
				$deposit_balance = $item->get_meta( '_deposit_balance' );
				$product         = $item->get_product();

				// if not a deposit, continue.
				if ( ! $deposit ) {
					continue;
				}

				if ( ! $product || ! $product instanceof WC_Product ) {
					continue;
				}

				// skip processing for other reason.
				/**
				 * APPLY_FILTERS: yith_wcdp_skip_suborder_creation
				 *
				 * Filters if should skip suborder creation.
				 *
				 * @param bool                  $skip         Default value: false.
				 * @param int                   $item_id      The item ID.
				 * @param WC_Order_Item_Product $item         Item to be processed for the suborder.
				 * @param int                   $order_id     The order ID.
				 * @param WC_Order              $parent_order The parent order object.
				 * @param WC_Product            $product      The item product.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcdp_skip_suborder_creation', false, $item_id, $item, $order_id, $parent_order, $product ) ) {
					continue;
				}

				// make sure we have no problem with stock handling.
				remove_action( 'woocommerce_checkout_order_created', 'wc_reserve_stock_for_order' );

				$is_variation         = $product->is_type( 'variation' );
				$product_id           = $is_variation ? $product->get_parent_id() : $product->get_id();
				$variation_id         = $is_variation ? $product->get_id() : '';
				$variation_attributes = $is_variation ? $product->get_variation_attributes() : array();

				/**
				 * APPLY_FILTERS: yith_wcdp_suborder_add_cart_item_data
				 *
				 * Filters suborder cart item data.
				 *
				 * @param array                          Default array of data.
				 * @param WC_Order_Item_Product $item    Item to be processed for the suborder.
				 * @param WC_Product            $product The item product.
				 *
				 * @return array
				 */
				$cart_item_data = apply_filters(
					'yith_wcdp_suborder_add_cart_item_data',
					array(
						'balance'         => true,
						'deposit_balance' => $deposit_balance,
						'deposit_item_id' => $item_id,
					),
					$item,
					$product
				);

				do_action( 'yith_wcdp_before_suborder_add_to_cart', $product_id, $item['qty'], $variation_id, $variation_attributes, $cart_item_data );

				try {
					// if deposit, add elem to support cart (filters change price of the product to be added to the cart).
					add_filter( 'woocommerce_add_cart_item', array( self::class, 'set_item_full_amount_price' ) );

					remove_all_actions( 'woocommerce_before_calculate_totals' );
					$cart_item_key = WC()->cart->add_to_cart(
						$product_id,
						$item['qty'],
						$variation_id,
						$variation_attributes,
						$cart_item_data
					);

					remove_filter( 'woocommerce_add_cart_item', array( self::class, 'set_item_full_amount_price' ) );
				} catch ( Exception $e ) {
					// translators: 1. Item id 2. Product title.
					$parent_order->add_order_note( sprintf( __( 'There was an error while processing suborder for item #%$1d (%$2s).', 'yith-woocommerce-deposits-and-down-payments' ), $item_id, $product->get_title() ) );
					continue;
				}

				do_action( 'yith_wcdp_after_suborder_add_to_cart', $product_id, $item['qty'], $variation_id, $variation_attributes, $cart_item_data, WC()->cart->get_cart_item( $cart_item_key ), $cart_item_key );
			}

			// if no item was added to cart, proceed no further.
			if ( WC()->cart->is_empty() ) {
				self::restore_original_cart();

				return false;
			}

			// apply coupons (when required and possible) to suborder.
			/**
			 * APPLY_FILTERS: yith_wcdp_propagate_coupons
			 *
			 * Filters if should propagate coupons.
			 *
			 * @param bool Default value: false.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcdp_propagate_coupons', false ) && ! empty( self::$coupons ) ) {
				foreach ( self::$coupons as $coupon ) {
					/**
					 * APPLY_FILTERS: yith_wcdp_propagate_coupon
					 *
					 * Filters if should propagate the coupon.
					 *
					 * @param bool        Default value: true.
					 * @param mixed $code The coupon.
					 *
					 * @return bool
					 */
					if ( apply_filters( 'yith_wcdp_propagate_coupon', true, $coupon ) ) {
						WC()->cart->add_discount( $coupon );
					}
				}
				wc_clear_notices();
			}

			try {
				// create suborder.
				$new_suborder_id = WC()->checkout()->create_order( $posted_data );

				if ( ! $new_suborder_id || is_wp_error( $new_suborder_id ) ) {
					return false;
				}
			} catch ( Exception $e ) {
				$parent_order->add_order_note( __( 'There was an error while processing suborder.', 'yith-woocommerce-deposits-and-down-payments' ) );

				return false;
			}

			add_action( 'woocommerce_checkout_order_created', 'wc_reserve_stock_for_order' );

			// set new suborder post parent.
			$new_suborder = wc_get_order( $new_suborder_id );

			try {
				$new_suborder->set_parent_id( $order_id );
				$new_suborder->set_status( self::get_suborder_status( $new_suborder_id ) );
				$new_suborder->set_created_via( 'yith_wcdp_balance_order' );

				// avoid counting sale twice.
				$new_suborder->update_meta_data( '_recorded_sales', 'yes' );

				// mark order as Full payment.
				$new_suborder->update_meta_data( '_has_full_payment', true );

				// disable stock management for brand new order.
				$new_suborder->update_meta_data( '_order_stock_reduced', true );

				// add plugin version.
				$new_suborder->update_meta_data( '_yith_wcdp_version', YITH_WCDP::YITH_WCDP_VERSION );

				// maybe set expiration.
				$expiration = self::get_suborder_expiration_date( $new_suborder_id );

				if ( $expiration ) {
					$new_suborder->update_meta_data( '_will_suborder_expire', 'yes' );
					$new_suborder->update_meta_data( '_suborder_expiration', $expiration );

					// if expiring, set up notification date (if enabled).
					$notification_date = self::get_suborder_notification_date( $new_suborder_id );

					if ( $notification_date ) {
						$new_suborder->update_meta_data( '_suborder_expiration_notification_date', $notification_date );
					}
				}
			} catch ( Exception $e ) {
				$new_suborder->add_order_note( __( 'Failed to update balance order meta.', 'yith-woocommerce-deposits' ) );
			}

			// set suborder customer note (remove email notification for this action only during this call).
			add_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );
			$new_suborder->add_order_note(
				sprintf(
					'%s <a href="%s">#%d</a>',
					__( 'This order has been created to allow payment of the balance', 'yith-woocommerce-deposits-and-down-payments' ),
					$parent_order->get_view_order_url(),
					$order_id
				),
				/**
				 * APPLY_FILTERS: yith_wcdp_suborder_note_is_customer_note
				 *
				 * Filters the suborder notes are customer notes.
				 *
				 * @param bool Default value: true.
				 *
				 * @return bool
				 */
				apply_filters( 'yith_wcdp_suborder_note_is_customer_note', true )
			);
			remove_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );

			// set order item meta with deposit-related full payment order.
			try {
				foreach ( $items as $item_id => $item ) {
					$item->update_meta_data( '_full_payment_id', $new_suborder_id );
					$item->save();
				}
			} catch ( Exception $e ) {
				$new_suborder->add_order_note( __( 'There was an error while updating item meta.', 'yith-woocommerce-deposits-and-down-payments' ) );

				return false;
			}

			// save the new order.
			$new_suborder->save();

			( ! $virtual_deposit ) && remove_filter( 'woocommerce_cart_needs_shipping', '__return_false' );

			// Let plugins add meta.
			/**
			 * DO_ACTION: yith_wcdp_update_suborder_meta
			 *
			 * Action triggered to let plugins add meta.
			 *
			 * @param int $new_suborder_id New suborder ID.
			 */
			do_action( 'yith_wcdp_update_suborder_meta', $new_suborder_id );

			// empty support cart, for next suborder.
			WC()->cart->empty_cart();

			// restore original cart.
			self::restore_original_cart();

			return $new_suborder_id;
		}
	}
}
