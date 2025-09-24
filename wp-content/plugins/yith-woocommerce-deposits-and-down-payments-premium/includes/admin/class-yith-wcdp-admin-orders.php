<?php
/**
 * Admin orders class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Admin_Orders' ) ) {
	/**
	 * Admin orders handling
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Admin_Orders {

		/**
		 * Constructor method
		 *
		 * @since 2.0.0
		 */
		public function __construct() {
			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				// admin order view handling.
				add_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'filter_orders_table_query_clauses' ), 10, 2 );
				add_filter( 'woocommerce_shop_order_list_table_order_count', array( $this, 'filter_order_table_count' ), 10, 2 );
				add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'shop_order_columns' ), 15 );
				add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_shop_order_columns' ), 10, 2 );

				add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_bulk_action' ) );
				add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'handle_bulk_action_deposit_expiring' ), 10, 3 );

				// add order views.
				add_filter( 'views_woocommerce_page_wc-orders', array( $this, 'add_to_refund_deposit_view_orders_page' ) );
			} else {
				// admin order view handling.
				add_filter( 'request', array( $this, 'filter_order_list' ), 10, 1 );
				add_filter( 'wp_count_posts', array( $this, 'filter_order_counts' ), 10, 3 );
				add_filter( 'manage_shop_order_posts_columns', array( $this, 'shop_order_columns' ), 15 );
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_shop_order_columns' ), 10, 2 );

				add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_action' ) );
				add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_bulk_action_deposit_expiring' ), 10, 3 );

				// add order views.
				add_filter( 'views_edit-shop_order', array( $this, 'add_to_refund_deposit_view' ) );
				add_action( 'pre_get_posts', array( $this, 'filter_order_for_view' ) );
			}

			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30, 2 );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_order_item_meta' ) );
			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'print_full_payment_order_itemmeta' ), 10, 2 );

			add_filter( 'woocommerce_order_item_get_name', array( $this, 'filter_order_items' ), 10, 2 );
			add_filter( 'woocommerce_order_get_items', array( $this, 'filter_order_items' ), 10, 2 );

			// add custom admin actions.
			add_action( 'admin_notices', array( $this, 'print_resend_notification_email_notice' ), 15 );
			add_action( 'admin_action_yith_wcdp_send_notification_email', array( $this, 'resend_notification_email' ) );
			add_action( 'admin_action_yith_wcdp_refund_item', array( $this, 'create_refund_for_item' ) );
			add_action( 'admin_action_yith_wcdp_delete_refund_notice', array( $this, 'delete_refund_notice' ) );

			// add resend new deposit email action.
			add_action( 'woocommerce_order_action_new_deposit', array( $this, 'resend_new_deposit_email' ), 10, 1 );

			// filter WooCommerce reports.
			add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'filter_sales_report' ) );

			// print admin order notices.
			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'print_item_to_refund_notice' ), 10, 2 );
		}

		/* === ORDERS PAGE === */

		/**
		 * Only show parent orders
		 *
		 * @param array $query Orders query.
		 *
		 * @return array Modified request
		 *
		 * @since  1.0.0
		 */
		public function filter_order_list( $query ) {
			global $typenow;

			if ( 'shop_order' === $typenow ) {
				$query['post__not_in'] = YITH_WCDP_Suborders()->get_all_balances_ids();

				/**
				 * APPLY_FILTERS: yith_wcdp_{$typenow}_request
				 *
				 * Filters the order list query based on the current post type in the WordPress admin.
				 *
				 * @param array $query Orders query.
				 *
				 * @return array
				 */
				$query = apply_filters( "yith_wcdp_{$typenow}_request", $query );
			}

			return $query;
		}

		/**
		 * Only show parent orders
		 *
		 * @param array  $clauses Query clauses.
		 * @param object $query   Query object.
		 *
		 * @return array Modified query clauses
		 */
		public function filter_orders_table_query_clauses( $clauses, $query ) {
			global $wpdb;

			$orders_table = $query->get_table_name( 'orders' );

			$balances_ids = ! empty( YITH_WCDP_Suborders()->get_all_balances_ids() ) ? YITH_WCDP_Suborders()->get_all_balances_ids() : array();

			if ( ! empty( $balances_ids ) ) {
				$clauses['where'] .= " AND {$orders_table}.id NOT IN (" . implode( ',', $balances_ids ) . ')';
			}

			// Filter orders for custom plugin views.
			if ( ! empty( $_GET['deposit_to_refund'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$clauses['join']  .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as i ON {$orders_table}.id = i.order_id LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id";
				$clauses['where'] .= $wpdb->prepare( ' AND im.meta_key = %s AND im.meta_value = %d', array( '_deposit_needs_manual_refund', 1 ) );
			}

			return $clauses;
		}

		/**
		 * Filter views count for admin views, to count only parent orders
		 *
		 * @param array  $counts Array of post stati count.
		 * @param string $type   Current post type.
		 * @param string $perm   The permission to determine if the posts are 'readable' by the current user.
		 *
		 * @return array filtered array of counts
		 *
		 * @since 1.1.1
		 */
		public function filter_order_counts( $counts, $type, $perm ) {
			global $wpdb;

			if ( 'shop_order' === $type ) {
				$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND ID NOT IN ( SELECT post_ID FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s )";

				if ( 'readable' === $perm && is_user_logged_in() ) {
					$post_type_object = get_post_type_object( $type );
					if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
						$query .= $wpdb->prepare(
							" AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
							get_current_user_id()
						);
					}
				}
				$query .= ' GROUP BY post_status';

				$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type, '_created_via', 'yith_wcdp_balance_order' ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

				if ( ! empty( $results ) ) {
					$results = array_combine( wp_list_pluck( $results, 'post_status' ), array_map( 'intval', wp_list_pluck( $results, 'num_posts' ) ) );
				}

				foreach ( array_keys( wc_get_order_statuses() ) as $order_status ) {
					$counts->{$order_status} = isset( $results[ $order_status ] ) ? $results[ $order_status ] : 0;
				}
			}

			return $counts;
		}

		/**
		 * Filter views count for admin views, to count only parent orders
		 *
		 * @param int   $count  Orders count Order count.
		 * @param array $status Order status Order status.
		 *
		 * @return int
		 */
		public function filter_order_table_count( $count, $status ) {
			global $wpdb;

			$query = "SELECT status, COUNT( * ) AS num_orders FROM {$wpdb->prefix}wc_orders WHERE type = %s AND id NOT IN ( SELECT order_id FROM {$wpdb->prefix}wc_order_operational_data WHERE created_via = %s ) GROUP BY status";

			$results = $wpdb->get_results( $wpdb->prepare( $query, 'shop_order', 'yith_wcdp_balance_order' ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL

			if ( ! empty( $results ) ) {
				$results = array_combine( array_column( $results, 'status' ), array_map( 'absint', array_column( $results, 'num_orders' ) ) );
			}

			$status = (array) $status;

			$count = array_sum( array_intersect_key( $results, array_flip( $status ) ) );

			return $count;
		}

		/**
		 * Add and reorder order table column
		 *
		 * @param array $order_columns Order table's columns.
		 *
		 * @return array Filtered list of oreder table's columns.
		 *
		 * @since 1.0.0
		 */
		public function shop_order_columns( $order_columns ) {
			$suborder      = array( 'balance' => _x( 'Balances', 'Admin: column heading in "Orders" table', 'yith-woocommerce-deposits-and-down-payments' ) );
			$ref_pos       = array_search( 'order_status', array_keys( $order_columns ), true );
			$order_columns = array_slice( $order_columns, 0, $ref_pos + 1, true ) + $suborder + array_slice( $order_columns, $ref_pos + 1, count( $order_columns ) - 1, true );

			return $order_columns;
		}

		/**
		 * Output custom columns for coupons
		 *
		 * @param string       $column Column to render.
		 * @param WC_Order|int $item Order object or order ID.
		 */
		public function render_shop_order_columns( $column, $item ) {
			$order = $item instanceof WC_Order ? $item : wc_get_order( $item );

			$order_id = $order->get_id();

			$suborder_ids = YITH_WCDP_Suborders()->get_suborders( $order_id );

			switch ( $column ) {
				case 'balance':
					if ( $suborder_ids ) {
						echo '<ul class="suborders-details">';

						foreach ( $suborder_ids as $suborder_id ) {
							$suborder = wc_get_order( $suborder_id );

							printf(
								'<li class="suborder-details">
                                    <mark class="order-status tips status-%1$s " data-tip="%3$s"><span>%2$s</span></mark>
                                    <a href="#" class="order-preview" data-order-id="%3$d" title="%4$s">%4$s</a>
                                </li>',
								esc_attr( sanitize_title( $suborder->get_status() ) ),
								esc_attr( wc_get_order_status_name( $suborder->get_status() ) ),
								esc_attr( $suborder_id ),
								esc_attr__( 'Preview', 'yith-woocommerce-deposits-and-down-payments' )
							);
						}

						echo '</ul>';
					} else {
						echo '<span class="na">&ndash;</span>';
					}

					break;
				case 'order_status':
					$column = '';

					if ( $suborder_ids ) {
						$count_uncompleted = 0;
						foreach ( $suborder_ids as $suborder_id ) {

							$suborder = wc_get_order( $suborder_id );

							if ( ! $suborder->has_status( array( 'completed', 'processing', 'cancelled', 'refunded' ) ) ) {
								++$count_uncompleted;
							}
						}

						if ( $count_uncompleted ) {
							$column .= '<span class="pending-count">' . esc_html( $count_uncompleted ) . '</span>';
						}
					}

					echo wp_kses_post( $column );

					break;
			}
		}

		/* === META BOXES === */

		/**
		 * Add suborder metaboxes for Deposit order
		 *
		 * @param string       $post_type Post type.
		 * @param WC_Order|int $post Order object or order ID.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_meta_boxes( $post_type, $post ) {
			if ( in_array( $post_type, array( wc_get_page_screen_id( 'shop-order' ), 'shop_order' ), true ) ) {
				$order = $post instanceof WC_Order ? $post : wc_get_order( $post );

				$has_suborder = YITH_WCDP_Suborders()->get_suborders( $order->get_id() );
				$is_suborder  = YITH_WCDP_Suborders()->is_suborder( $order->get_id() );

				if ( $has_suborder ) {
					$metabox_suborder_description = _x( 'Balance orders', 'Admin: Single order page. Suborder details box', 'yith-woocommerce-deposits-and-down-payments' ) . ' <span class="tips" data-tip="' . esc_attr__( 'Note: from this box you can monitor the status of suborders concerning full payments.', 'yith-woocommerce-deposits-and-down-payments' ) . '">[?]</span>';
					add_meta_box(
						'yith-wcdp-woocommerce-suborders',
						$metabox_suborder_description,
						array(
							$this,
							'render_metabox_output',
						),
						$post_type,
						'side',
						'core',
						array( 'metabox' => 'suborders' )
					);
				} elseif ( $is_suborder ) {
					$metabox_parent_order_description = _x( 'Deposit order', 'Admin: Single order page. Info box with parent order details', 'yith-woocommerce-deposits-and-down-payments' );
					add_meta_box(
						'yith-wcdp-woocommerce-parent-order',
						$metabox_parent_order_description,
						array(
							$this,
							'render_metabox_output',
						),
						$post_type,
						'side',
						'high',
						array( 'metabox' => 'parent-order' )
					);
				}
			}
		}

		/**
		 * Output the suborder metaboxes
		 *
		 * @param WP_Post $post  The post object.
		 * @param array   $param Callback args.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function render_metabox_output( $post, $param ) {
			$order = $post instanceof WC_Order ? $post : wc_get_order( $post );

			$order_id = $order->get_id();

			$deposit_expire    = get_option( 'yith_wcdp_deposit_expiration_enable', 'no' );
			$notification_days = yith_wcdp_duration_to_days( get_option( 'yith_wcdp_notify_customer_deposit_expiring_days_limit', 15 ) );

			$send_available = false;

			if ( $order && ! ! $order->get_meta( '_has_deposit' ) ) {
				// retrieve current order suborders.
				$suborders = YITH_WCDP_Suborders()->get_suborders( $order_id );

				// check if order have suborders.
				if ( ! $suborders ) {
					return;
				}

				// enable "re-send notify email" only if at least one suborder is not expired, and not completed or cancelled.
				foreach ( $suborders as $suborder_id ) {
					$suborder = wc_get_order( $suborder_id );

					if ( ! $order->get_meta( '_has_expired' ) && ! $suborder->has_status( array( 'completed', 'processing', 'cancelled' ) ) ) {
						$send_available = true;
					}
				}
			}

			switch ( $param['args']['metabox'] ) {
				case 'suborders':
					$suborder_ids = YITH_WCDP_Suborders()->get_suborders( $order_id );
					echo '<ul class="suborders-details">';

					foreach ( $suborder_ids as $suborder_id ) {
						$suborder = wc_get_order( absint( $suborder_id ) );
						$items    = $suborder->get_items( 'line_item' );

						if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
							$suborder_uri = add_query_arg(
								array(
									'page'   => 'wc-orders',
									'action' => 'edit',
									'id'     => absint( $suborder_id ),
								),
								admin_url( 'admin.php' )
							);
						} else {
							$suborder_uri = get_edit_post_link( $suborder_id );
						}

						$items_to_string = array();

						if ( ! empty( $items ) ) {
							foreach ( $items as $item ) {
								$items_to_string[] = $item['name'];
							}
						}

						$items_to_string = implode( ' | ', $items_to_string );
						$order_status    = $suborder->get_status();

						$formatted_status = wc_get_order_status_name( $order_status );
						$status_words     = explode( ' ', $formatted_status );
						$status_initials  = array_map(
							function ( $item ) {
								return strtoupper( substr( $item, 0, 1 ) );
							},
							$status_words
						);
						$shortened_status = implode( '', $status_initials );

						echo '<li class="suborder-details">';
						printf(
							'<mark class="status-%1$s order-status tips" data-tip="%2$s"><span>%3$s</span></mark><p><strong><a href="%4$s">#%5$s</a></strong> <small>(%6$s)</small></p>',
							esc_attr( sanitize_title( $order_status ) ),
							esc_attr( $formatted_status ),
							esc_html( $shortened_status ),
							esc_url( $suborder_uri ),
							esc_html( $suborder_id ),
							esc_html( $items_to_string )
						);
						echo '</li>';
					}

					echo '</ul>';

					/**
					 * APPLY_FILTERS: yith_wcdp_change_deposit_expiration_enable
					 *
					 * Filters if should force to render the button to send the expiration notification email.
					 *
					 * @param bool Default value: false.
					 *
					 * @return bool
					 */
					if ( apply_filters( 'yith_wcdp_change_deposit_expiration_enable', false ) || 'yes' === $deposit_expire && $notification_days && $send_available ) {
						$resend_url = esc_url(
							add_query_arg(
								array(
									'action'   => 'yith_wcdp_send_notification_email',
									'order_id' => $order_id,
								),
								wp_nonce_url( admin_url( 'admin.php' ), 'resend_notification_email', 'resend_notification_email_nonce' )
							)
						);
						printf( '<a class="button" href="%s">%s</a>', esc_url( $resend_url ), esc_html__( 'Send notification email', 'yith-woocommerce-deposits-and-down-payments' ) );
					}
					break;

				case 'parent-order':
					$parent_order_id  = $order->get_parent_id();
					$parent_order_uri = esc_url( 'post.php?post=' . absint( $parent_order_id ) . '&action=edit' );
					printf( '<a href="%s">&#8592; %s</a>', esc_url( $parent_order_uri ), esc_html_x( 'Back to main order', 'Admin: single order page. Link to parent order', 'yith-woocommerce-deposits-and-down-payments' ) );
					break;
			}
		}

		/* === ORDER DETAIL PAGE === */

		/**
		 * Filter order items to add label for deposit orders
		 *
		 * @param mixed         $arg1 Mixed value (order item).
		 * @param WC_Order_item $arg2 Order items object.
		 *
		 * @return mixed Filtered array of order items
		 * @since 1.0.0
		 */
		public function filter_order_items( $arg1, $arg2 ) {
			global $pagenow;

			// apply this filter only when in single post page.
			if ( 'post.php' !== $pagenow && ( array_key_exists( 'page', $_GET ) && 0 !== strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'wc-orders' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return $arg1;
			}

			$deposit_prefix = YITH_WCDP_Labels::get_deposit_label() . ': ';
			$balance_prefix = YITH_WCDP_Labels::get_balance_label() . ': ';

			if ( is_string( $arg1 ) ) {
				if ( wc_get_order_item_meta( $arg2->get_id(), '_deposit' ) && strpos( $arg1, $deposit_prefix ) === false ) {
					$arg1 = $deposit_prefix . $arg1;
				} elseif ( wc_get_order_item_meta( $arg2->get_id(), '_full_payment' ) && strpos( $arg1, $balance_prefix ) === false ) {
					$arg1 = $balance_prefix . $arg1;
				}
			}

			return $arg1;
		}

		/**
		 * Hide plugin item meta, when not in debug mode
		 *
		 * @param array $hidden_items Array of meta to hide on admin side.
		 *
		 * @return mixed Filtered array of meta to hide
		 * @since 1.0.0
		 */
		public function hide_order_item_meta( $hidden_items ) {
			if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
				$hidden_items = array_merge(
					$hidden_items,
					array(
						'_deposit',
						'_deposit_type',
						'_deposit_amount',
						'_deposit_rate',
						'_deposit_value',
						'_deposit_balance',
						'_deposit_shipping_method',
						'_deposit_id',
						'_deposit_refunded_after_expiration',
						'_deposit_needs_manual_refund',
						'_full_payment',
						'_full_payment_id',
					)
				);
			}

			return $hidden_items;
		}

		/**
		 * Print Full Payment link to before order item meta section of order edit admin page
		 *
		 * @param int   $item_id Current order item id.
		 * @param array $item    Current item data.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_full_payment_order_itemmeta( $item_id, $item ) {
			if ( isset( $item['deposit'] ) && $item['deposit'] ) {
				$suborder = wc_get_order( $item['full_payment_id'] );

				if ( ! $suborder ) {
					return;
				}

				$suborder_id = $suborder->get_id();

				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					$suborder_link = add_query_arg(
						array(
							'page'   => 'wc-orders',
							'action' => 'edit',
							'id'     => $suborder_id,
						),
						admin_url( 'admin.php' )
					);
				} else {
					$suborder_link = get_edit_post_link( $suborder_id );
				}

				?>
				<div class="yith-wcdp-full-payment">
					<small>
						<a href="<?php echo esc_url( $suborder_link ); ?>">
							<?php printf( '%s: #%d', esc_html__( 'Full payment order', 'yith-woocommerce-deposits-and-down-payments' ), esc_html( $suborder_id ) ); ?>
						</a>
					</small>
				</div>
				<?php
			}
		}

		/* === DEPOSIT TO REFUND VIEW === */

		/**
		 * Filter orders for custom plugin views
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function filter_order_for_view() {
			if ( ! empty( $_GET['deposit_to_refund'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_filter( 'posts_join', array( $this, 'filter_order_join_for_view' ) );
				add_filter( 'posts_where', array( $this, 'filter_order_where_for_view' ) );
			}
		}

		/**
		 * Add joins to order view query
		 *
		 * @param string $join Original join query section.
		 *
		 * @return string filtered join query section
		 * @since 1.0.0
		 */
		public function filter_order_join_for_view( $join ) {
			global $wpdb;

			$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as i ON {$wpdb->posts}.ID = i.order_id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id";

			return $join;
		}

		/**
		 * Add conditions to order view query
		 *
		 * @param string $where Original where query section.
		 *
		 * @return string filtered where query section
		 * @since 1.0.0
		 */
		public function filter_order_where_for_view( $where ) {
			global $wpdb;

			$where .= $wpdb->prepare( ' AND im.meta_key = %s AND im.meta_value = %d', array( '_deposit_needs_manual_refund', 1 ) );

			return $where;
		}

		/**
		 * Add a view o default order status view, to filter orders that needs manual refund
		 *
		 * @param array $views Current order views.
		 *
		 * @return mixed Filtered array of views
		 *
		 * @since 1.0.0
		 */
		public function add_to_refund_deposit_view( $views ) {
			$order_to_refund_count = YITH_WCDP_Suborders()->count_deposit_to_refund();

			if ( $order_to_refund_count ) {
				$filter_url   = esc_url(
					add_query_arg(
						array(
							'post_type'         => 'shop_order',
							'deposit_to_refund' => true,
						),
						admin_url( 'edit.php' )
					)
				);
				$filter_class = isset( $_GET['deposit_to_refund'] ) ? 'current' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$views['deposit_to_refund'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', $filter_url, $filter_class, __( 'Deposit to refund', 'yith-woocommerce-deposits-and-down-payments' ), $order_to_refund_count );
			}

			return $views;
		}

		/**
		 * Add a view o default order status view, to filter orders that needs manual refund
		 *
		 * @param array $views Current order views.
		 *
		 * @return mixed Filtered array of views
		 *
		 * @since 1.0.0
		 */
		public function add_to_refund_deposit_view_orders_page( $views ) {
			$order_to_refund_count = YITH_WCDP_Suborders()->count_deposit_to_refund();

			if ( $order_to_refund_count ) {
				$filter_url   = esc_url(
					add_query_arg(
						array(
							'page'              => 'wc-orders',
							'deposit_to_refund' => true,
						),
						admin_url( 'admin.php' )
					)
				);
				$filter_class = isset( $_GET['deposit_to_refund'] ) ? 'current' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$views['deposit_to_refund'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', $filter_url, $filter_class, __( 'Deposit to refund', 'yith-woocommerce-deposits-and-down-payments' ), $order_to_refund_count );
			}

			return $views;
		}

		/* === BULK ACTIONS === */
		/**
		 * Add bulk action.
		 *
		 * @param array $bulk_actions Array with the bulk actions.
		 *
		 * @return array
		 */
		public function add_bulk_action( $bulk_actions ) {
			$bulk_actions['remind_deposit_expiring'] = __( 'Remind about deposit expiring', 'yith-woocommerce-deposits-and-down-payments' );

			return $bulk_actions;
		}

		/**
		 * Process the new bulk actions for changing order status
		 *
		 * @param string $sendback URL to redirect to.
		 * @param string $doaction Action name.
		 * @param array  $items    List of ids.
		 *
		 * @return string
		 */
		public function handle_bulk_action_deposit_expiring( $sendback, $doaction, $items ) {
			// Bail out if this is not a status-changing action.
			if ( 'remind_deposit_expiring' !== $doaction ) {
				return $sendback;
			}

			$changed = 0;

			foreach ( $items as $item ) {
				do_action( 'yith_wcdp_deposits_expiring', $item, false, true );
				++$changed;
			}

			$sendback = add_query_arg(
				array(
					'changed' => $changed,
					'ids'     => join( ',', $items ),
				),
				$sendback
			);

			return $sendback;
		}

		/* === ORDER ACTIONS === */

		/**
		 * Re-send notification email and to edit order page
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function resend_notification_email() {
			if ( isset( $_GET['order_id'] ) && isset( $_GET['resend_notification_email_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['resend_notification_email_nonce'] ) ), 'resend_notification_email' ) ) {
				$order_id = intval( $_GET['order_id'] );
				do_action( 'yith_wcdp_deposits_expiring', $order_id, false, true );

				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					$return_url = add_query_arg(
						array(
							'page'                    => 'wc-orders',
							'action'                  => 'edit',
							'id'                      => $order_id,
							'notification_email_sent' => true,
						),
						admin_url( 'admin.php' )
					);
				} else {
					$return_url = add_query_arg( 'notification_email_sent', true, str_replace( '&amp;', '&', get_edit_post_link( $order_id ) ) );
				}

				wp_safe_redirect( esc_url_raw( $return_url ) );
				die();
			}
		}

		/**
		 * Re-send new deposit email for customer and to edit order page
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		public function resend_new_deposit_email( $order ) {

			$order_id     = $order->get_id();
			$notify_admin = get_option( 'yith_wcdp_notify_customer_deposit_created' );

			// is_enabled always enable to send it manually.
			if ( 'yes' !== $notify_admin ) {
				add_filter( 'yith_wcdp_customer_deposit_created_email_enabled', '__return_true' );
			}

			/**
			 * DO_ACTION: yith_wcdp_deposits_created
			 *
			 * Action triggered after setting if should notify the admin via email about new deposits created.
			 *
			 * @param int $order_id Order ID.
			 */
			do_action( 'yith_wcdp_deposits_created', $order_id );
		}

		/**
		 * Print "Notification Email Sent" notice
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_resend_notification_email_notice() {
			global $post;

			$post      = isset( $_GET['id'] ) ? get_post( absint( $_GET['id'] ) ) : $post; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.WP.GlobalVariablesOverride.Prohibited
			$post_type = get_post_type( $post );

			if ( in_array( $post_type, array( wc_get_page_screen_id( 'shop-order' ), 'shop_order' ), true ) && ! empty( $_GET['notification_email_sent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				echo '<div class="updated notice notice-success is-dismissible below-h2">';
				echo '<p>' . esc_html__( 'Notification email sent', 'yith-woocommerce-deposits-and-down-payments' ) . '</p>';
				echo '</div>';
			}
		}

		/**
		 * Print item refund notice
		 *
		 * @param int           $item_id Current item id.
		 * @param WC_Order_Item $item    Current item.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_item_to_refund_notice( $item_id, $item ) {
			$suborder_id = $item->get_meta( 'full_payment_id' );

			if ( $suborder_id ) {
				return;
			}

			$order_id        = $item->get_order_id();
			$schedule        = get_option( 'yith_wcdp_deposit_expiration_enable', 'no' );
			$expiration_type = get_option( 'yith_wcdp_deposits_expiration_type', 'num_of_days' );

			if ( 'num_of_days' === $expiration_type ) {
				$expiration_days = get_option( 'yith_wcdp_deposits_expiration_duration', 30 );
			} else {
				$suborder = wc_get_order( $suborder_id );

				if ( ! $suborder ) {
					return;
				}

				$suborder_expires    = $suborder->get_meta( '_will_suborder_expire', true );
				$suborder_expiration = $suborder->get_meta( '_suborder_expiration', true );

				if ( 'yes' === $suborder_expires && $suborder_expiration ) {
					$expiration_date = $suborder_expiration;
				}
			}

			$message = 'num_of_days' === $expiration_type ?
				// translators: 1. Number of days before expiration.
				sprintf( __( 'This item should be manually refunded by admin since the %d days available to complete payment have passed and the deposit has expired', 'yith-woocommerce-deposits-and-down-payments' ), $expiration_days ) :
				// translators: 1. Expiration date.
				sprintf( __( 'This item should be manually refunded by admin since the deposit has expired on %s', 'yith-woocommerce-deposits-and-down-payments' ), ! empty( $expiration_date ) ? $expiration_date : __( 'N/A', 'yith-woocommerce-deposits-and-down-payments' ) );

			if ( isset( $item['deposit_needs_manual_refund'] ) && $item['deposit_needs_manual_refund'] && 'yes' === $schedule ) {
				$create_refund_for_item_url = esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action'   => 'yith_wcdp_refund_item',
								'order_id' => $order_id,
								'item_id'  => $item_id,
							),
							admin_url( 'admin.php' )
						),
						'yith_wcdp_refund_item'
					)
				);
				$hide_notice_for_item_url   = esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action'   => 'yith_wcdp_delete_refund_notice',
								'order_id' => $order_id,
								'item_id'  => $item_id,
							),
							admin_url( 'admin.php' )
						),
						'yith_wcdp_delete_refund_notice'
					)
				);

				?>
				<div class="yith-wcdp-to-refund-notice error-notice">
					<p>
						<small>
							<?php echo esc_html( $message ); ?>
							<a href="<?php echo esc_url( $create_refund_for_item_url ); ?>"><?php esc_html_e( 'Create refund', 'yith-woocommerce-deposits-and-down-payments' ); ?></a>
							|
							<a href="<?php echo esc_url( $hide_notice_for_item_url ); ?>"><?php esc_html_e( 'Hide this notice', 'yith-woocommerce-deposits-and-down-payments' ); ?></a>
						</small>
					</p>
				</div>
				<?php
			} elseif ( isset( $item['deposit_refunded_after_expiration'] ) && $item['deposit_refunded_after_expiration'] ) {
				$refund_id = $item['deposit_refunded_after_expiration'];
				?>
				<div class="yith-wcdp-to-refund-notice info-notice">
					<p>
						<small>
							<?php
							// translators: 1. Refund id.
							echo esc_html( sprintf( __( 'This item has been refunded due to deposit expiration (refund #%d)', 'yith-woocommerce-deposits-and-down-payments' ), $refund_id ) );
							?>
						</small>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Create manual refund for item, after admin action
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function create_refund_for_item() {
			$is_hpos_enabled = yith_plugin_fw_is_wc_custom_orders_table_usage_enabled();

			if ( $is_hpos_enabled ) {
				$redirect_url = add_query_arg( 'page', 'wc-orders', admin_url( 'admin.php' ) );
			} else {
				$redirect_url = add_query_arg( 'post_type', 'shop_order', admin_url( 'edit.php' ) );
			}

			if ( ! isset( $_GET['order_id'] ) || ! isset( $_GET['item_id'] ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'yith_wcdp_refund_item' ) ) {
				wp_safe_redirect( esc_url_raw( $redirect_url ) );
				die();
			}

			$order_id = intval( $_GET['order_id'] );
			$item_id  = intval( $_GET['item_id'] );

			$order = wc_get_order( $order_id );

			$refund_amount = 0;
			$to_refund     = array();

			if ( $order ) {
				$order_items = $order->get_items();

				if ( isset( $order_items[ $item_id ] ) ) {
					$item = $order_items[ $item_id ];

					$to_refund[ $item_id ] = array(
						'qty'          => $item['qty'],
						'refund_total' => $order->get_item_total( $item, true ),
						'type'         => 'line_item',
					);
					$refund_amount        += $order->get_item_total( $item, true, false );
				}

				if ( WC()->payment_gateways() ) {
					$payment_gateways = WC()->payment_gateways->payment_gateways();
				}

				$order_payment_gateway = $order->get_payment_method();

				if ( isset( $payment_gateways, $payment_gateways[ $order_payment_gateway ] ) ) {
					$refund_reason = __( 'Item refunded manually for deposit expiration', 'yith-woocommerce-deposits-and-down-payments' );

					// Create the refund object.
					try {
						$refund = wc_create_refund(
							array(
								'amount'     => $refund_amount,
								'reason'     => $refund_reason,
								'order_id'   => $order_id,
								'line_items' => $to_refund,
							)
						);

						if ( $refund ) {
							wc_update_order_item_meta( $item_id, '_deposit_refunded_after_expiration', $refund->get_id() );
							wc_delete_order_item_meta( $item_id, '_deposit_needs_manual_refund' );
						}
					} catch ( Exception $e ) {
						wp_safe_redirect( esc_url_raw( $redirect_url ) );
						die();
					}
				}
			}

			if ( $is_hpos_enabled ) {
				$redirect_url = add_query_arg(
					array(
						'page'   => 'wc-orders',
						'action' => 'edit',
						'id'     => $order_id,
					),
					admin_url( 'admin.php' )
				);
			} else {
				$redirect_url = str_replace( '&amp;', '&', get_edit_post_link( $order_id ) );
			}

			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * Delete notice to refund order after deposit expiration
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function delete_refund_notice() {
			$is_hpos_enabled = yith_plugin_fw_is_wc_custom_orders_table_usage_enabled();

			if ( $is_hpos_enabled ) {
				$redirect_url = add_query_arg( 'page', 'wc-orders', admin_url( 'admin.php' ) );
			} else {
				$redirect_url = add_query_arg( 'post_type', 'shop_order', admin_url( 'edit.php' ) );
			}

			if ( ! isset( $_GET['order_id'] ) || ! isset( $_GET['item_id'] ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'yith_wcdp_delete_refund_notice' ) ) {
				wp_safe_redirect( esc_url_raw( $redirect_url ) );
				die();
			}

			$order_id = intval( $_GET['order_id'] );
			$item_id  = intval( $_GET['item_id'] );

			try {
				wc_delete_order_item_meta( $item_id, '_deposit_needs_manual_refund' );
			} catch ( Exception $e ) {
				wp_safe_redirect( esc_url_raw( $redirect_url ) );
				die();
			}

			if ( $is_hpos_enabled ) {
				$redirect_url = add_query_arg(
					array(
						'page'   => 'wc-orders',
						'action' => 'edit',
						'id'     => $order_id,
					),
					admin_url( 'admin.php' )
				);
			} else {
				$redirect_url = str_replace( '&amp;', '&', get_edit_post_link( $order_id ) );
			}

			wp_safe_redirect( $redirect_url );
			die();
		}

		/* === WOOCOMMERCE REPORT === */

		/**
		 * Filters report data, to remove balance from items sold count
		 *
		 * @param array $args Report args.
		 *
		 * @return array Filtered array of arguments
		 */
		public function filter_sales_report( $args ) {
			if ( isset( $args['data'] ) && isset( $args['data']['_qty'] ) && 'order_item_meta' === $args['data']['_qty']['type'] ) {

				$args['where'][] = array(
					'key'      => 'order_items.order_id',
					'value'    => YITH_WCDP_Suborders()->get_all_balances_ids(),
					'operator' => 'not in',
				);
			}

			return $args;
		}
	}
}
