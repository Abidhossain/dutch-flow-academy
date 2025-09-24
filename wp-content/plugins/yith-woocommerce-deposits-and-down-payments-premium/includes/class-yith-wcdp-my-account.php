<?php
/**
 * My Account handling class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_My_Account' ) ) {
	/**
	 * Alters My Account, to add deposit info
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_My_Account {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			// change Orders view.
			add_filter( 'woocommerce_my_account_my_orders_query', array( self::class, 'filter_my_account_my_orders_query' ) );
			add_filter( 'woocommerce_get_formatted_order_total', array( self::class, 'filter_my_account_my_orders_total' ), 10, 2 );
			add_filter( 'woocommerce_my_account_my_orders_actions', array( self::class, 'filter_my_account_my_orders_actions' ), 10, 2 );

			// change View Order view.
			add_action( 'woocommerce_order_item_meta_end', array( self::class, 'print_deposit_order_item' ), 10, 3 );
			add_action( 'woocommerce_order_details_after_order_table', array( self::class, 'print_full_amount_payments_orders' ), 10, 1 );
			add_action( 'woocommerce_order_details_after_order_table', array( self::class, 'print_on_location_notice' ), 10, 1 );

			// custom order status.
			add_filter( 'woocommerce_order_get_status', array( self::class, 'filter_order_status' ), 10, 2 );
			add_filter( 'wc_order_statuses', array( self::class, 'filter_order_status_labels' ) );

			// expiration notice.
			add_action( 'yith_wcdp_before_my_deposits_table', array( self::class, 'print_expired_suborders_notice' ), 10, 1 );

			// handle downloads for partially-paid orders.
			add_filter( 'woocommerce_order_is_download_permitted', array( self::class, 'is_download_permitted_on_partially_paid' ), 10, 2 );
			add_filter( 'woocommerce_get_item_downloads', array( self::class, 'downloads_for_deposit_item' ), 10, 2 );
		}

		/* === CHANGES TO ORDERS ENDPOINT === */

		/**
		 * Filter "Recent orders" my-account section query
		 *
		 * @param array $query_vars Array of query var.
		 *
		 * @return array Filtered query var
		 * @since  1.0.0
		 */
		public static function filter_my_account_my_orders_query( $query_vars ) {
			$child_orders = YITH_WCDP_Suborders()->get_child_orders();

			$query_vars['exclude'] = $child_orders;

			return $query_vars;
		}

		/**
		 * Filter order total price html, to show deposit info
		 *
		 * @param string   $total_html Original HTML for order total.
		 * @param WC_Order $order      Current order.
		 *
		 * @return string Filtered total HTML
		 * @since 1.0.0
		 */
		public static function filter_my_account_my_orders_total( $total_html, $order ) {
			$suborders = false;

			if ( $order->get_meta( '_has_deposit' ) ) {
				$total_html = wc_price( $order->get_total() );

				$total     = $order->get_total();
				$suborders = $order->get_meta( '_full_payment_orders' );
				if ( ! empty( $suborders ) ) {
					foreach ( $suborders as $suborder_id ) {
						$suborder = wc_get_order( $suborder_id );

						if ( ! $suborder ) {
							continue;
						}

						$total += $suborder->get_total();
					}
				}

				$total_html .= sprintf( ' (%s <strong>%s</strong>)', __( 'of', 'yith-woocommerce-deposits-and-down-payments' ), wc_price( $total ) );
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_show_total_html
			 *
			 * Filters order total price HTML.
			 *
			 * @param string   $total_html Default value.
			 * @param WC_Order $order      The order.
			 * @param array    $suborders  Array of suborders.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_show_total_html', $total_html, $order, $suborders );
		}

		/**
		 * Filter order total price html, to show deposit info
		 *
		 * @param array    $actions Availabl order actions.
		 * @param WC_Order $order   Current order.
		 *
		 * @return array Filtered array of available order actions.
		 * @since 1.0.0
		 */
		public static function filter_my_account_my_orders_actions( $actions, $order ) {
			if ( $order->get_meta( '_has_deposit' ) ) {
				$actions['view_full_amount_payments'] = array(
					'url'  => $order->get_view_order_url() . '#yith_wcdp_deposits_details',
					// translators: 1. Balance label.
					'name' => sprintf( __( 'View %s', 'yith-woocommerce-deposits-and-down-payments' ), YITH_WCDP_Labels::get_balance_label() ),
				);
			}

			return $actions;
		}

		/**
		 * Filter order status label for partially paid orders
		 *
		 * @param string   $status Current status.
		 * @param WC_Order $order  Current order.
		 *
		 * @return string Filtered status
		 * @since 1.0.0
		 */
		public static function filter_order_status( $status, $order ) {
			if ( is_account_page() && $order->get_meta( '_has_deposit' ) && in_array( $status, array( 'completed', 'processing' ), true ) ) {
				$suborders = YITH_WCDP_Suborders()->get_suborders( $order->get_id() );

				if ( $suborders ) {
					foreach ( $suborders as $suborder_id ) {
						$suborder = wc_get_order( $suborder_id );
						if ( ! $suborder->has_status( array( 'completed', 'processing' ) ) ) {
							$status = apply_filters( 'yith_wcdp_partially_paid_status', 'partially-paid', $suborder );
						}
					}
				}
			}

			return $status;
		}

		/**
		 * Filter order status labels to print "Partially paid" status
		 *
		 * @param array $labels Current available labels.
		 *
		 * @return array Filtered labels
		 * @since 1.0.0
		 */
		public static function filter_order_status_labels( $labels ) {
			$labels['wc-partially-paid'] = YITH_WCDP_Labels::get_partially_paid_status_label();

			return $labels;
		}

		/**
		 * Print item data on cart / checkout views, to inform user about deposit & balance he's going to pay
		 *
		 * @param int           $item_id Order item id.
		 * @param WC_Order_Item $item    Order item object.
		 * @param WC_order      $order   Order object.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function print_deposit_order_item( $item_id, $item, $order ) {
			if ( isset( $item['deposit'] ) && $item['deposit'] ) {
				$full_amount = $item['deposit_value'] + $item['deposit_balance'];
				$product     = is_object( $item ) ? $item->get_product() : $order->get_product_from_item( $item );

				/**
				 * APPLY_FILTERS: yith_wcdp_full_amount_order_item_html
				 *
				 * Filters the price HTML.
				 *
				 * @param string        $html        Default HTML.
				 * @param double        $full_amount Deposit + balance amount.
				 * @param WC_Order_Item $item        Order item object.
				 * @param WC_order      $order       Order object.
				 *
				 * @return string
				 */
				$full_amount_html = apply_filters(
					'yith_wcdp_full_amount_order_item_html',
					wc_price(
						yith_wcdp_get_price_to_display(
							$product,
							array(
								'qty'   => intval( $item['qty'] ),
								'price' => $full_amount,
								'order' => $order,
							)
						)
					),
					$full_amount,
					$item,
					$order
				);

				$template = '';

				$template .= '<p style=" margin: 0;padding: 0;"><small style="display: block !important;"><b>' . wp_kses_post( YITH_WCDP_Labels::get_full_price_label() ) . ':</b> ' . $full_amount_html . '</small></p>';
				$template .= '<p style=" margin: 0;padding: 0;"><small style="display: block !important;"><b>' . wp_kses_post( YITH_WCDP_Labels::get_balance_label() ) . ':</b> ' . wc_price(
					yith_wcdp_get_price_to_display(
						$product,
						array(
							'qty'   => intval( $item['qty'] ),
							'price' => $item['deposit_balance'],
							'order' => $order,
						)
					)
				) . '</small></p>';

				/**
				 * APPLY_FILTERS: yith_wcdp_print_deposit_order_item_template
				 *
				 * Filters deposit order item template (HTML).
				 *
				 * @param string        $template Default HTML.
				 * @param WC_Order_Item $item     Order item object.
				 * @param WC_order      $order    Order object.
				 *
				 * @return string
				 */
				echo wp_kses_post( apply_filters( 'yith_wcdp_print_deposit_order_item_template', $template, $item, $order ) );
			}
		}

		/* === CHANGES TO VIEW-ORDER ENDPOINT === */

		/**
		 * Prints full amount payments for an order (order-detail view)
		 *
		 * @param WC_Order $order Current order.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function print_full_amount_payments_orders( $order ) {
			if ( $order->get_meta( '_has_deposit' ) ) {
				$deposits  = array();
				$suborders = YITH_WCDP_Suborders()->get_suborders( $order->get_id() );

				foreach ( $suborders as $suborder_id ) {
					$suborder = wc_get_order( $suborder_id );

					$product_list = array();
					$items        = $suborder->get_items( 'line_item' );

					if ( ! empty( $items ) ) {
						foreach ( $items as $item ) {
							/**
							 * Every single product item of the order
							 *
							 * @var $item \WC_Order_Item_Product
							 */
							$product        = $item->get_product();
							$product_list[] = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $item->get_name() );
						}
					}

					$actions = array();

					if ( $suborder->needs_payment() ) {
						$actions['pay'] = array(
							'url'  => $suborder->get_checkout_payment_url(),
							'name' => __( 'Pay order', 'yith-woocommerce-deposits-and-down-payments' ),
						);
					}

					if ( $suborder->has_status( apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $suborder ) ) ) {
						$actions['cancel'] = array(
							'url'  => $suborder->get_cancel_order_url( wc_get_page_permalink( 'myaccount' ) ),
							'name' => __( 'Cancel order', 'yith-woocommerce-deposits-and-down-payments' ),
						);
					}

					$actions['view'] = array(
						'url'  => $suborder->get_view_order_url(),
						'name' => __( 'View order', 'yith-woocommerce-deposits-and-down-payments' ),
					);

					$actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $suborder );

					$deposits[] = array(
						'balance'           => $suborder,
						'balance_id'        => $suborder_id,
						'suborder_id'       => $suborder_id,
						'suborder_view_url' => $suborder->get_view_order_url(),
						'product_list'      => $product_list,
						'order_status'      => $suborder->get_status(),
						'order_items'       => $suborder->get_items(),
						'order_paid'        => $suborder->has_status( array( 'processing', 'completed' ) ) ? $suborder->get_total() : 0,
						'order_total'       => $suborder->get_total(),
						'order_subtotal'    => $suborder->get_subtotal(),
						'order_discount'    => $suborder->get_total_discount(),
						'order_shipping'    => $suborder->get_shipping_total(),
						'order_taxes'       => array_sum( wp_list_pluck( $suborder->get_tax_totals(), 'amount' ) ),
						'order_to_pay'      => $suborder->has_status( array( 'processing', 'completed' ) ) ? 0 : $suborder->get_total(),
						'expiration_date'   => YITH_WCDP_Suborders()->get_suborder_expiration( $suborder_id ),
						'actions'           => $actions,
					);
				}

				$args = array(
					'order'    => $order,
					'order_id' => $order->get_id(),
					'deposits' => $deposits,
					'balances' => $deposits,
				);

				yith_wcdp_get_template( 'my-deposits.php', $args );
			}
		}

		/**
		 * Print notice to warning user some deposits have expired
		 *
		 * @param int $order_id Current order id.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function print_expired_suborders_notice( $order_id ) {
			$suborders         = YITH_WCDP_Suborders()->get_suborders( $order_id );
			$expired_suborders = array();

			if ( ! empty( $suborders ) ) {
				foreach ( $suborders as $suborder_id ) {
					$suborder    = wc_get_order( $suborder_id );
					$has_expired = $suborder->get_meta( '_has_deposit_expired' );

					if ( $has_expired ) {
						$expired_suborders[] = $suborder_id;
					}
				}
			}

			if ( ! empty( $expired_suborders ) ) {
				$orders_link = '';
				$first       = true;

				foreach ( $expired_suborders as $suborder_id ) {
					if ( ! $first ) {
						$orders_link .= ', ';
					}

					// retrieve order url and append to orders_link string.
					$view_order_url = apply_filters( 'woocommerce_get_view_order_url', wc_get_endpoint_url( 'view-order', $suborder_id, wc_get_page_permalink( 'myaccount' ) ) );
					$orders_link   .= sprintf( '<a href="%s">#%d</a>', esc_url( $view_order_url ), esc_html( $suborder_id ) );

					$first = false;
				}

				// translators: 1. Link to balance order(s).
				$message = sprintf( _n( 'This order contains an expired deposit; full amount order %s was consequently switched to canceled and it cannot be completed anymore.', 'This order contains an expired deposit; full amount orders %s were consequently switched to canceled and they cannot be completed anymore.', count( $expired_suborders ), 'yith-woocommerce-deposits-and-down-payments' ), $orders_link );
				$message = sprintf( '<div class="woocommerce-error">%s</div>', $message );

				// output alread escaped.
				echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Print notice to let use know this full payment order should be paid on location
		 *
		 * @param WC_Order $order Current order.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function print_on_location_notice( $order ) {
			if ( $order->get_meta( '_has_full_payment' ) && $order->get_meta( '_full_payment_needs_manual_payment' ) && $order->has_status( 'on-hold' ) ) {
				$notice   = get_option( 'yith_wcdp_deposit_labels_pay_in_loco', '' );
				$template = '';

				if ( ! empty( $notice ) ) {
					$template .= '<div id="yith_wcdp_on_location_notice" class="yith-wcdp-on-location-notice">';
					$template .= '<h2>' . esc_html__( 'Payment options', 'yith-woocommerce-deposits-and-down-payments' ) . '</h2>';
					$template .= '<p>' . wp_kses_post( $notice ) . '</p>';
					$template .= '</div>';

					// template is already escaped.
					echo $template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		/* ==== DOWNLOADS HANDLING === */

		/**
		 * Let customers download files on deposit when deposit can be downloadable
		 *
		 * @param bool               $is_download_permitted Whether order is downloadable or not.
		 * @param \WC_Abstract_Order $order                 Current order.
		 *
		 * @return bool Whether order is downloadable or not
		 */
		public static function is_download_permitted_on_partially_paid( $is_download_permitted, $order ) {
			if ( $order->get_meta( '_has_deposit' ) ) {
				/**
				 * APPLY_FILTERS: yith_wcdp_not_downloadable_on_deposit
				 *
				 * Filters if order is not downloadable on deposit.
				 *
				 * @param bool Default value: True.
				 *
				 * @return bool
				 */
				return ! apply_filters( 'yith_wcdp_not_downloadable_on_deposit', true ) && 'partially-paid' === $order->get_status();
			}

			return $is_download_permitted;
		}

		/**
		 * Return downloads for deposit item
		 *
		 * @param array                 $downloads Array of available downloads.
		 * @param WC_Order_Item_Product $item      Current item.
		 *
		 * @return array Array of filtered downloads.
		 */
		public static function downloads_for_deposit_item( $downloads, $item ) {
			if ( empty( $downloads ) ) {
				// nothing to do.
				return $downloads;
			}

			if ( ! empty( $item['deposit'] ) ) {
				// deposit item, check suborder.
				if ( apply_filters( 'yith_wcdp_not_downloadable_on_deposit', true ) ) {
					return array();
				} else {
					return $downloads;
				}
			}

			return $downloads;
		}
	}
}
