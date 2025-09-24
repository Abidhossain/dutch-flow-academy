<?php
/**
 * Suborder class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Suborders' ) ) {
	/**
	 * WooCommerce Deposits / Down Payments
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Suborders {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_Suborders
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// status handling.
			add_action( 'trashed_post', array( $this, 'trash_suborders' ) );
			add_action( 'untrashed_post', array( $this, 'untrash_suborders' ) );
			add_action( 'woocommerce_order_status_changed', array( $this, 'synch_suborders_with_parent_status' ), 10, 3 );
			add_action( 'yith_wcdp_balance_expired', array( $this, 'handle_suborder_expiration' ) );

			// avoid payment gateway to reduce stock order for suborders.
			add_filter( 'woocommerce_can_reduce_order_stock', array( $this, 'skip_reduce_stock_on_suborders' ), 10, 2 );
			add_filter( 'woocommerce_prevent_adjust_line_item_product_stock', array( $this, 'skip_reduce_stock_on_suborder_items' ), 10, 2 );

			// avoid WooCommerce to block suborder processing because of products out of stock (stock was already processed during deposit checkout).
			add_filter( 'woocommerce_order_item_product', array( $this, 'set_suborder_items_as_in_stock' ), 10, 2 );
		}

		/* === STATUS HANDLING METHODS === */

		/**
		 * Trash suborders on parent order trashing
		 *
		 * @param int $post_id Trashed post id.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function trash_suborders( $post_id ) {
			$order = wc_get_order( $post_id );

			if ( ! $order ) {
				return;
			}

			$suborders = $this->get_suborders( $post_id );

			if ( ! $suborders ) {
				return;
			}

			foreach ( $suborders as $suborder ) {
				if ( ! $suborder instanceof WC_Order && is_int( $suborder ) ) {
					$suborder = wc_get_order( $suborder );
				}
				( method_exists( $suborder, 'delete' ) ) ? $suborder->delete() : wp_trash_post( $suborder->get_id() );
			}
		}

		/**
		 * Restore suborders on parent order restoring
		 *
		 * @param int $post_id Restore post id.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function untrash_suborders( $post_id ) {
			$order = wc_get_order( $post_id );

			if ( ! $order ) {
				return;
			}

			$suborders = $this->get_suborders( $post_id );

			if ( ! $suborders ) {
				return;
			}

			foreach ( $suborders as $suborder ) {
				wp_untrash_post( $suborder );
			}
		}

		/**
		 * Set suborders status according to parent status when there is a failure and they're not completed yet
		 *
		 * @param int    $order_id   Parent order id.
		 * @param string $old_status Old order status.
		 * @param string $new_status New order status.
		 *
		 * @return void
		 * @since 1.2.4
		 */
		public function synch_suborders_with_parent_status( $order_id, $old_status, $new_status ) {
			$suborders = $this->get_suborders( $order_id );

			if ( ! $suborders ) {
				return;
			}

			foreach ( $suborders as $suborder_id ) {
				$suborder = wc_get_order( $suborder_id );

				if ( in_array( $new_status, array( 'cancelled', 'failed' ), true ) && $suborder->has_status( array( 'pending', 'on-hold' ) ) ) {
					$destination_status = $new_status;
				} elseif ( in_array( $old_status, array( 'cancelled', 'failed' ), true ) && $suborder->has_status( array( 'cancelled', 'failed' ) ) ) {
					foreach ( $suborder->get_items() as $item ) {
						$destination_status = $this->get_suborder_status( $suborder_id );
					}
				}

				if ( ! isset( $destination_status ) ) {
					continue;
				}

				$suborder->set_status( $destination_status, __( 'Suborder status changed to reflect parent order status change:', 'yith-woocommerce-deposits-and-down-payments' ) );
				$suborder->save();
			}
		}

		/**
		 * Handle suborder expiration
		 *
		 * @param WC_Order $order    Order expired.
		 */
		public function handle_suborder_expiration( $order ) {
			$expiration_fallback = get_option( 'yith_wcdp_deposit_expiration_fallback', array() );

			// use last attempt fallback.
			if ( empty( $expiration_fallback['attempts'] ) ) {
				$fallback = isset( $expiration_fallback['initial_fallback'] ) ? $expiration_fallback['initial_fallback'] : 'none';
			} else {
				$last_attempt = array_pop( $expiration_fallback['attempts'] );
				$fallback     = isset( $last_attempt['fallback'] ) ? $last_attempt['fallback'] : 'none';
			}

			// if no fallback is provided, just end here.
			if ( 'none' === $fallback ) {
				return;
			} elseif ( 'refund' === $fallback ) {
				$this->refund_expired_suborder( $order );
			}

			do_action( 'yith_wcdp_suborder_expired', $order );
		}

		/**
		 * Refunds suborder after expiration
		 *
		 * @param WC_Order $order    Order expired.
		 */
		public function refund_expired_suborder( $order ) {
			// retrieve parent order, and items to refund.
			$parent_order_id = $order->get_parent_id();
			$parent_order    = wc_get_order( $parent_order_id );

			// stop if we can't retrieve parent order, or if it doesn't have expected status.
			if ( ! $parent_order || ! $parent_order->has_status( array( 'completed', 'processing', 'partially-paid' ) ) ) {
				return;
			}

			$items = $parent_order->get_items();

			if ( empty( $items ) ) {
				return;
			}

			$to_refund     = array();
			$refund_amount = 0;

			foreach ( $items as $item_id => $item ) {
				$item_balance_id = (int) $item->get_meta( '_full_payment_id' );
				$item_refunded   = (bool) $item->get_meta( '_deposit_refunded_after_expiration' );

				if ( $item_balance_id !== $order || $item_refunded ) {
					continue;
				}

				$to_refund[ $item_id ] = array(
					'item'         => $item,
					'name'         => $item->get_name(),
					'qty'          => $item->get_quantity(),
					'refund_total' => $parent_order->get_item_total( $item, true ),
					'type'         => 'line_item',
				);

				$refund_amount += $parent_order->get_item_total( $item, true, false );
			}

			if ( empty( $to_refund ) ) {
				return;
			}

			$payment_method   = $parent_order->get_payment_method();
			$payment_gateways = WC_Payment_Gateways::instance()->payment_gateways();
			$payment_gateway  = isset( $payment_gateways[ $payment_method ] ) ? $payment_gateways[ $payment_method ] : false;
			$refund           = false;

			// process automatic refund through gateway, when possible.
			if ( $payment_gateway && $payment_gateway instanceof WC_Payment_Gateway && $payment_gateway->supports( 'refunds' ) ) {
				$refund_reason = __( 'Item refunded automatically for deposit expiration', 'yith-woocommerce-deposits-and-down-payments' );

				// Create the refund object.
				try {
					$refund = wc_create_refund(
						array(
							'amount'        => $refund_amount,
							'reason'        => $refund_reason,
							'order_id'      => $parent_order_id,
							'line_items'    => $to_refund,
							'restock_items' => true,
						)
					);

					$result = $payment_gateway->process_refund( $parent_order_id, $refund_amount, $refund_reason );

					do_action( 'yith_wcdp_refund_processed', $refund, $result );
				} catch ( Exception $e ) { // phpcs:ignore
					// do nothing. System will fallback to manual refund meta.
				}

				// if correctly refunded, mark deposit items.
				if ( $refund ) {
					foreach ( wp_list_pluck( $to_refund, 'item' ) as $item ) {
						$item->update_meta_data( '_deposit_refunded_after_expiration', $refund->get_id() );
						$item->save();
					}

					// format order note.
					$item_names      = wp_list_pluck( $to_refund, 'name' );
					$expiration_date = strtotime( $order->get_meta( '_suborder_expiration' ) );
					$creation_date   = $order->get_date_created()->getTimestamp();
					$expiration_span = floor( ( $expiration_date - $creation_date ) / DAY_IN_SECONDS );

					// translators: 1. Product id. 2. Number of days before deposit expiration.
					$message = _n( 'Item %1$s has been automatically refunded because the %2$d days allowed to complete payment have passed', 'Items %1$s have been automatically refunded because the %2$d days allowed to complete payment have passed', count( $to_refund ), 'yith-woocommerce-deposits-and-down-payments' );
					$message = sprintf( $message, implode( ', ', $item_names ), $expiration_span );

					$parent_order->add_order_note( apply_filters( 'yith_wcdp_expired_order_notice', $message ), true );
				}
			}

			// when automatic refund is not possible, or fails for whatever reason, mark items for manual refund.
			if ( ! $refund ) {
				foreach ( wp_list_pluck( $to_refund, 'item' ) as $item ) {
					$item->update_meta_data( '_deposit_needs_manual_refund', true );
					$item->save();
				}
			}
		}

		/* === SUBORDERS STOCK HANDLING METHODS === */

		/**
		 * Let WooCommerce skip stock decreasing for suborders
		 *
		 * @param bool      $can   Whether to perform or not stock decreasing.
		 * @param \WC_Order $order Current order.
		 *
		 * @return bool Filtered \$skip value
		 */
		public function skip_reduce_stock_on_suborders( $can, $order ) {
			if ( $this->is_suborder( $order->get_id() ) ) {
				return false;
			}

			return $can;
		}

		/**
		 * Let WooCommerce skip stock decreasing for suborders
		 *
		 * @param bool           $skip  Whether to perform or not stock decreasing.
		 * @param \WC_Order_Item $item Current order.
		 *
		 * @return bool Filtered \$skip value
		 */
		public function skip_reduce_stock_on_suborder_items( $skip, $item ) {
			if ( $this->is_suborder( $item->get_order_id() ) ) {
				return true;
			}

			return $skip;
		}

		/**
		 * Set products as in stock if they're retrieved for a balance payment
		 *
		 * @param \WC_Product            $product Currently retrieved product.
		 * @param \WC_Order_Item_Product $item    Current order item.
		 *
		 * @return \WC_Product filtered product
		 */
		public function set_suborder_items_as_in_stock( $product, $item ) {
			if ( isset( $product ) && $product instanceof WC_Product ) {
				$order_id = method_exists( $item, 'get_order_id' ) ? $item->get_order_id() : false;

				if ( ( $order_id && $this->is_suborder( $order_id ) ) || isset( $item['full_payment_id'] ) ) {
					$product->set_stock_status( 'instock' );
				}
			}

			return $product;
		}

		/* === HELPER METHODS === */

		/**
		 * Returns correct status of the suborder
		 *
		 * @param int $suborder_id Id of the suborder.
		 *
		 * @return string Suborder status.
		 */
		public function get_suborder_status( $suborder_id ) {
			$status   = 'pending';
			$suborder = wc_get_order( $suborder_id );

			if ( $suborder ) {
				foreach ( $suborder->get_items() as $item ) {
					$product            = $item->get_product();
					$balance_preference = YITH_WCDP_Options::get( 'create_balance', $product->get_id() );

					if ( 'pending' === $balance_preference ) {
						// if at least one item requires "pending" suborder, use that as order status.
						$status = 'pending';
						break;
					} elseif ( 'on-hold' === $balance_preference ) {
						// use on-hold status ony when all items requests it.
						$status = 'on-hold';
					}
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_suborder_status
			 *
			 * Filters default suborder status.
			 *
			 * @param string                  Default value: 'pending'.
			 * @param int    $new_suborder_id The suborder ID.
			 * @param int    $order_id        The main order ID.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_suborder_status', $status, $suborder_id, $suborder->get_parent_id() );
		}

		/**
		 * Check if order identified by $order_id has suborders, and eventually returns them
		 *
		 * @param int $order_id Id of the order to check.
		 *
		 * @return int[] Array of suborder ids
		 * @since 1.0.0
		 */
		public function get_suborders( $order_id ) {
			global $wpdb;

			$suborder_ids = array();
			$parent_ids   = (array) absint( $order_id );

			while ( ! empty( $parent_ids ) ) {
				$parents_list = implode( ', ', $parent_ids );

				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					$prepared_query = $wpdb->prepare(
						"SELECT o.id
						FROM {$wpdb->prefix}wc_orders AS o
						LEFT JOIN {$wpdb->prefix}wc_order_operational_data AS od ON o.id = od.order_id
						WHERE parent_order_id IN ({$parents_list}) " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						'AND o.type = %s
						AND od.created_via = %s',
						'shop_order',
						'yith_wcdp_balance_order'
					);
				} else {
					$prepared_query = $wpdb->prepare(
						"SELECT ID
						FROM {$wpdb->posts} AS p
						LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
						WHERE post_parent IN ({$parents_list}) " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						'AND post_type=%s
						AND meta_key=%s
						AND meta_value=%s',
						'shop_order',
						'_created_via',
						'yith_wcdp_balance_order'
					);
				}

				$parent_ids = $wpdb->get_col( $prepared_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

				$suborder_ids = array_merge( $suborder_ids, $parent_ids );
			}

			$suborder_ids = array_map( 'intval', $suborder_ids );

			/**
			 * APPLY_FILTERS: yith_wcdp_suboder
			 *
			 * Filters order suborders.
			 *
			 * @param array $suborder_ids Array of suborder IDs.
			 * @param int   $order_id     Id of the order to check.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcdp_suboder', $suborder_ids, $order_id );
		}

		/**
		 * Check if order identified by $order_id has uncompleted suborders, and eventually returns them
		 *
		 * @param int $order_id Id of the order to check.
		 *
		 * @return mixed Array of uncompleted suborders, if any
		 * @since 1.4.6
		 */
		public function get_uncompleted_suborders( $order_id ) {
			$suborder_ids    = $this->get_suborders( $order_id );
			$uncompleted_ids = array();

			foreach ( $suborder_ids as $suborder_id ) {
				$order = wc_get_order( $suborder_id );

				if ( $order->needs_payment() ) {
					$uncompleted_ids[] = $suborder_id;
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_uncompleted_suborder
			 *
			 * Filters array of uncompleted suborders.
			 *
			 * @param array $uncompleted_suborder_ids Array of uncompleted suborder IDs.
			 * @param int   $order_id                 Id of the order to check.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcdp_uncompleted_suborder', $uncompleted_ids, $order_id );
		}

		/**
		 * Returns post parent of a Full payment order
		 * If order is not a full payment order, it will return false
		 *
		 * @param int $order_id Order id.
		 *
		 * @return int|bool If order is full payment, and has post parent, returns parent ID; false otherwise
		 */
		public function get_parent_order( $order_id ) {
			$order            = wc_get_order( $order_id );
			$has_full_payment = $order->get_meta( '_has_full_payment' );

			if ( ! $has_full_payment ) {
				return false;
			}

			return $order->get_parent_id();
		}

		/**
		 * Check if order identified by $order is a suborder (has post_parent)
		 *
		 * @param int|WC_Order $order Id of the order to check, or order object.
		 *
		 * @return bool Whether order is a suborder or no
		 * @since 1.0.0
		 */
		public function is_suborder( $order ) {
			if ( ! $order instanceof WC_Order ) {
				$order_id = $order;
				$order    = wc_get_order( $order_id );
			}

			if ( ! $order || ! $order instanceof WC_Order ) {
				return false;
			}

			$post_parent = $order->get_parent_id();
			$created_via = $order->get_created_via();

			/**
			 * APPLY_FILTERS: yith_wcdp_is_suborder
			 *
			 * Filters if order is a suborder.
			 *
			 * @param bool     $bool  Default value.
			 * @param WC_Order $order Order object.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcdp_is_suborder', $post_parent && 'yith_wcdp_balance_order' === $created_via, $order );
		}

		/**
		 * Check if order identified by $order contains deposits
		 *
		 * @param int|WC_Order $order Id of the order to check, or order object.
		 *
		 * @return bool Whether order contains deposit or not.
		 * @since 1.3.9
		 */
		public function has_deposits( $order ) {
			if ( ! $order instanceof WC_Order ) {
				$order_id = $order;
				$order    = wc_get_order( $order_id );
			}

			if ( ! $order ) {
				return false;
			}

			$items = $order->get_items( 'line_item' );

			if ( empty( $items ) ) {
				return false;
			}

			$has_deposits = false;

			foreach ( $items as $item ) {
				if ( $item->get_meta( '_deposit', true ) ) {
					$has_deposits = true;
					break;
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_order_has_deposits
			 *
			 * Filters if order has deposits.
			 *
			 * @param bool     $bool  Default value.
			 * @param WC_Order $order Order object.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcdp_order_has_deposits', $has_deposits, $order );
		}

		/**
		 * Get parent orders for current user
		 *
		 * @return WC_Order[] Array of found orders
		 * @since 1.0.0
		 */
		public function get_parent_orders() {
			$customer_orders = wc_get_orders(
				/**
				 * APPLY_FILTERS: yith_wcdp_add_parent_orders
				 *
				 * Filters array of options when retrieving parent orders of current user.
				 *
				 * @param array Default array of options.
				 *
				 * @return array
				 */
				apply_filters(
					'yith_wcdp_add_parent_orders',
					array(
						'posts_per_page' => - 1,
						'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							array(
								'key'   => '_customer_user',
								'value' => get_current_user_id(),
							),
							array(
								'key' => '_has_deposit',
							),
						),
						'post_type'      => wc_get_order_types( 'view-orders' ),
						'post_status'    => array_keys( wc_get_order_statuses() ),
						'post_parent'    => 0,
					)
				)
			);

			return $customer_orders;
		}

		/**
		 * Get child orders for current user
		 *
		 * @return \WP_Post[] Array of found orders
		 * @since 1.0.0
		 */
		public function get_child_orders() {
			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$customer_orders = wc_get_orders(
					/**
					 * APPLY_FILTERS: yith_wcdp_add_child_orders
					 *
					 * Filters array of options when retrieving child orders of current user.
					 *
					 * @param array Default array of options.
					 *
					 * @return array
					 */
					apply_filters(
						'yith_wcdp_add_child_orders',
						array(
							'limit'       => -1,
							'return'      => 'ids',
							'customer_id' => get_current_user_id(),
							'type'        => wc_get_order_types( 'view-orders' ),
							'status'      => array_keys( wc_get_order_statuses() ),
							'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key' => '_has_full_payment',
								),
							),
						)
					)
				);
			} else {
				$customer_orders = get_posts(
					apply_filters(
						'yith_wcdp_add_child_orders',
						array(
							'posts_per_page' => - 1,
							'fields'         => 'ids',
							'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key'   => '_customer_user',
									'value' => get_current_user_id(),
								),
								array(
									'key' => '_has_full_payment',
								),
							),
							'post_type'      => wc_get_order_types( 'view-orders' ),
							'post_status'    => array_keys( wc_get_order_statuses() ),
						)
					)
				);
			}

			return $customer_orders;
		}

		/**
		 * Return an array of ids of orders that contain deposit
		 *
		 * @return array Array of order ids
		 */
		public function get_all_deposits_ids() {
			global $wpdb;

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$prepared_query = $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = %s AND meta_value = %s", '_has_deposit', '1' );
			} else {
				$prepared_query = $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", '_has_deposit', '1' );
			}

			return $wpdb->get_col( $prepared_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Return an array of ids of orders that where created as balance orders
		 *
		 * @return array Array of order ids
		 */
		public function get_all_balances_ids() {
			global $wpdb;

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$prepared_query = $wpdb->prepare( "SELECT order_id from {$wpdb->prefix}wc_order_operational_data WHERE created_via = %s", 'yith_wcdp_balance_order' );
			} else {
				$prepared_query = $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", '_created_via', 'yith_wcdp_balance_order' );
			}

			return $wpdb->get_col( $prepared_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Count orders with an expired deposit, that requires manual refund
		 *
		 * @return int Number of orders with deposit to manually refund
		 * @since 1.0.0
		 */
		public function count_deposit_to_refund() {
			global $wpdb;

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$prepared_query = $wpdb->prepare(
					"SELECT COUNT( DISTINCT( id ) )
					FROM {$wpdb->prefix}wc_orders AS o
					LEFT JOIN {$wpdb->prefix}woocommerce_order_items as i ON o.id = i.order_id
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
					WHERE o.type = %s
					AND o.status IN (%s, %s)
					AND im.meta_key = %s
					AND im.meta_value = %d",
					array(
						'shop_order',
						'wc-completed',
						'wc-processing',
						'_deposit_needs_manual_refund',
						1,
					)
				);
			} else {
				$prepared_query = $wpdb->prepare(
					"SELECT COUNT( DISTINCT( ID ) )
                    FROM {$wpdb->posts} AS p
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_items as i ON p.ID = i.order_id
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
                    WHERE p.post_type = %s
                    AND p.post_status IN (%s, %s)
                	AND im.meta_key = %s
                    AND im.meta_value = %d",
					array(
						'shop_order',
						'wc-completed',
						'wc-processing',
						'_deposit_needs_manual_refund',
						1,
					),
				);
			}

			$count = $wpdb->get_var( $prepared_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			return $count;
		}

		/**
		 * Get expiration date for a suborder, if any
		 * Depending on the context may return a formatted date, or a WC_DateTime object
		 * If suborder won't expire, or there is a problem with expiration date, false will be returned instead
		 *
		 * @param int    $suborder_id Suborder id.
		 * @param string $context     Context of the operation.
		 *
		 * @return bool|string|WC_DateTime Expiration date, or false on failure.
		 */
		public function get_suborder_expiration( $suborder_id, $context = 'view' ) {
			$suborder = wc_get_order( $suborder_id );

			if ( ! $suborder || ! $this->is_suborder( $suborder ) ) {
				return false;
			}

			$will_expire = yith_plugin_fw_is_true( $suborder->get_meta( '_will_suborder_expire' ) );

			if ( ! $will_expire ) {
				return false;
			}

			$expiration_date = $suborder->get_meta( '_suborder_expiration' );
			$expiration_ts   = strtotime( $expiration_date );

			if ( ! $expiration_ts ) {
				return false;
			}

			if ( 'view' === $context ) {
				return gmdate( wc_date_format(), $expiration_ts );
			}

			try {
				$expiration_dt = new WC_DateTime( "@{$expiration_ts}", new DateTimeZone( 'UTC' ) );
			} catch ( Exception $e ) {
				return false;
			}

			return $expiration_dt;
		}

		/**
		 * Checks if we should extend expiration date of a suborder, because admin enabled another attempt
		 * If we can extend expiration date, return new expiration
		 *
		 * @param int $suborder_id Id of the suborder.
		 *
		 * @return bool|string New Expiration date; false if attempts are over.
		 */
		public function should_extend_expiration( $suborder_id ) {
			$order = wc_get_order( $suborder_id );

			if ( ! $order ) {
				return false;
			}

			$expiration_iterations = (int) $order->get_meta( '_suborder_expiration_iterations' );
			$expiration_fallback   = get_option( 'yith_wcdp_deposit_expiration_fallback' );

			if ( ! $expiration_iterations ) {
				$fallback  = $expiration_fallback['initial_fallback'];
				$extension = isset( $expiration_fallback['attempts'][1] ) ? $expiration_fallback['attempts'][1]['days_after'] : false;
			} elseif ( isset( $expiration_fallback['attempts'][ $expiration_iterations ] ) ) {
				$next_iteration = $expiration_iterations + 1;
				$fallback       = $expiration_fallback['attempts'][ $expiration_iterations ]['fallback'];
				$extension      = isset( $expiration_fallback['attempts'][ $next_iteration ] ) ? $expiration_fallback['attempts'][ $next_iteration ]['days_after'] : false;
			} else {
				$fallback  = 'none';
				$extension = false;
			}

			if ( 'retry' !== $fallback || ! $extension ) {
				return false;
			}

			$original_expiration    = $order->get_meta( '_suborder_expiration' );
			$original_expiration_ts = $original_expiration ? strtotime( $original_expiration ) : time();
			$new_expiration         = gmdate( 'Y-m-d', $original_expiration_ts + (int) $extension * DAY_IN_SECONDS );

			return $new_expiration;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_Suborders
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCDP_suborders class
 *
 * @return \YITH_WCDP_Suborders
 * @since 1.0.0
 */
function YITH_WCDP_Suborders() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, Universal.Files.SeparateFunctionsFromOO
	return YITH_WCDP_Suborders::get_instance();
}

/**
 * Legacy function, left just for backward compatibility
 *
 * @return \YITH_WCDP_Suborders
 */
function YITH_WCDP_Suborders_Premium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	_deprecated_function( 'YITH_WCDP_Suborders_Premium', '2.0.0', 'YITH_WCDP_Suborders' );

	return YITH_WCDP_Suborders();
}

// create legacy class alias.
class_alias( 'YITH_WCDP_Suborders', 'YITH_WCDP_Suborders_Premium' );
