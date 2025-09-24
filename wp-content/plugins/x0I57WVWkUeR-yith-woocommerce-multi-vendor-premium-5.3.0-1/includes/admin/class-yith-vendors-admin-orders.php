<?php
/**
 * YITH Vendors Admin Orders Class. Handle orders admin side.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Orders' ) ) {
	/**
	 * Class YITH_Vendors_Admin_Orders
	 */
	class YITH_Vendors_Admin_Orders {

		/**
		 * Order screen id.
		 *
		 * @var string
		 */
		protected $screen_id = '';

		/**
		 * Current vendor id.
		 *
		 * @var int
		 */
		protected $vendor_id;

		/**
		 * Caches order counts by status.
		 *
		 * @var array
		 */
		protected $status_count_cache = null;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 */
		public function __construct() {

			// Init hooks to let WC initialize correctly.
			add_action( 'admin_init', array( $this, 'setup' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'orders_section_style' ), 1 );
			// Exclude suborder from processing count.
			add_action( 'init', array( $this, 'count_orders' ) );
			// Order Item Meta.
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_itemmeta' ) );
			add_action( 'woocommerce_after_order_itemmeta', array( $this, 'commission_info_in_order_line_item' ), 10, 3 );
			// Order Details Customization.
			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'add_sold_by_to_order' ), 10, 3 );
			// Vendor hooks.
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_limited_access_hooks' ), 10 );
			// Skip associate taxonomy to orders.
			add_filter( 'yith_wcmv_add_vendor_taxonomy_to_shop_order', '__return_false' );
			add_filter( 'yith_wcmv_add_vendor_taxonomy_to_shop_order_refund', '__return_false' );

            //  Filter orders.
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'filter_orders' ), 10, 2 );
		}

		/**
		 * Setup class hooks and filters.
		 *
		 * @return void
		 */
		public function setup() {
			// Set the correct screen ID for order.
			$this->screen_id = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';

			// Customize shop order table.
			add_filter( 'woocommerce_shop_order_list_table_columns', array( $this, 'shop_order_columns' ), 20 );
			add_filter( 'manage_shop_order_posts_columns', array( $this, 'shop_order_columns' ), 20 );
			add_action( 'manage_' . $this->screen_id . '_custom_column', array( $this, 'render_shop_order_columns' ), 10, 2 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_shop_order_columns' ), 10 );

			add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'hpos_filter_vendor_orders_list' ) );
			add_filter( 'woocommerce_shop_order_list_table_order_count', array( $this, 'filter_table_order_count' ), 10, 2 );

			if ( current_user_can( 'manage_woocommerce' ) ) {
				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					add_action( 'add_meta_boxes', array( $this, 'hpos_order_meta_boxes' ), 30, 2 );
				} else {
					// Remove suborders from order list table.
					add_action( 'pre_get_posts', array( $this, 'hide_vendor_suborders' ), 10, 1 );
					add_filter( 'wp_count_posts', array( $this, 'exclude_suborders_count' ), 10, 3 );
					add_action( 'add_meta_boxes', array( $this, 'order_meta_boxes' ), 30 );
				}
			}
		}

		/**
		 * Return true we are in a shor_order section
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		public function is_order_section() {
			global $current_screen, $post;

			return ( ! empty( $current_screen ) && $current_screen->id === $this->screen_id ) || ( isset( $_GET['post_type'] ) && 'shop_order' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) || ( $post && 'shop_order' === $post->post_type ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Customize WooCommerce order section style.
		 *
		 * @since    4.0.0
		 * @return void
		 */
		public function orders_section_style() {
			if ( ! $this->is_order_section() ) {
				return;
			}

			YITH_Vendors_Admin_Assets::add_css( 'admin-orders', 'admin-orders.css' );
		}

		/**
		 * Prevent suborder display as main order.
		 *
		 * @param array $order_query_args Order query args.
		 *
		 * @return mixed
		 */
		public function hpos_filter_vendor_orders_list( $order_query_args ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() ) {
				$order_query_args['meta_query'] = array(
					array(
						'key'   => 'vendor_id',
                        'compare' => 'IN',
						'value' => apply_filters( 'yith_wcmv_hpos_vendor_orders_list_vendor_id', array( $vendor->get_id() ), $vendor->get_id() ),
					),
				);
			} else {
				$order_query_args['parent_order_id'] = '0';
			}

			return $order_query_args;
		}

		/**
		 * Filter orders count in HPOS list table
		 *
		 * @since 4.12.0
		 * @param int      $count  Number of orders for the given status.
		 * @param string[] $status List of order statuses in the count.
		 * @return int
		 */
		public function filter_table_order_count( $count, $status ) {
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			global $wpdb;

			// Compute all counts and cache if necessary.
			if ( is_null( $this->status_count_cache ) ) {
				$vendor       = yith_wcmv_get_vendor( 'current', 'user' );
				$orders_table = OrdersTableDataStore::get_orders_table_name();

				if ( $vendor->is_valid() && ! current_user_can( 'manage_woocoommerce' ) ) {
					$orders_meta_table = OrdersTableDataStore::get_meta_table_name();
					$query             = $wpdb->prepare(
						"SELECT o.status, COUNT(*) AS cnt FROM {$orders_table} AS o INNER JOIN {$orders_meta_table} AS om ON o.id = om.order_id
                                 WHERE o.type = 'shop_order' AND om.meta_key = %s AND om.meta_value = %s GROUP BY o.status",
						'vendor_id',
						$vendor->get_id()
					);
				} else {
					$query = "SELECT o.status, COUNT(*) AS cnt FROM {$orders_table} AS o WHERE o.type = 'shop_order' AND o.parent_order_id = 0 GROUP BY o.status";
				}

				$res                      = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore
				$this->status_count_cache =
					$res
						? array_combine( array_column( $res, 'status' ), array_map( 'absint', array_column( $res, 'cnt' ) ) )
						: array();
			}

			$status = (array) $status;
			return array_sum( array_intersect_key( $this->status_count_cache, array_flip( $status ) ) );
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		/**
		 * Hide vendor sub-orders from WC Orders list
		 *
		 * @since  4.0.0
		 * @param WP_Query $query Current query.
		 * @return mixed|void
		 */
		public function hide_vendor_suborders( $query ) {
			if ( 'shop_order' === $query->get( 'post_type' ) && empty( $_REQUEST['s'] ) //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				&& ( empty( $query->get( 'post_parent' ) ) && empty( $query->get( 'post_parent__in' ) ) ) ) {
				$query->set( 'post_parent', 0 );
			}
		}

		/**
		 * Exclude from WP count posts vendor suborders
		 *
		 * @since  4.0.0
		 * @param object $counts An object containing the current post_type's post
		 *                       counts by status.
		 * @param string $type   Post type.
		 * @param string $perm   The permission to determine if the posts are 'readable'
		 *                       by the current user.
		 * @return object
		 */
		public function exclude_suborders_count( $counts, $type, $perm ) {
			global $wpdb;

			if ( 'shop_order' !== $type ) {
				return $counts;
			}

			// Create a cache key.
			$cache_key = _count_posts_cache_key( 'admin_' . $type, $perm );
			$counts    = wp_cache_get( $cache_key, 'counts' );

			if ( false !== $counts ) {
				return $counts;
			}

			$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = 0";

			if ( 'readable' === $perm ) {
				$post_type_object = get_post_type_object( $type );
				if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
					$query .= $wpdb->prepare(
						" AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
						get_current_user_id()
					);
				}
			}

			$query .= ' GROUP BY post_status';

			$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A ); // phpcs:ignore
			$counts  = array_fill_keys( get_post_stati(), 0 );

			foreach ( $results as $row ) {
				$counts[ $row['post_status'] ] = $row['num_posts'];
			}

			$counts = (object) $counts;
			wp_cache_set( $cache_key, $counts, 'counts' );

			return $counts;
		}

		/**
		 * Add and reorder order table column
		 *
		 * @param array $order_columns The order table columns.
		 * @return array
		 */
		public function shop_order_columns( $order_columns ) {

			$post_status = isset( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : false;  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( current_user_can( 'manage_woocommerce' ) ) {
				if ( ! $post_status || 'trash' !== $post_status ) {
					$suborder      = array( 'suborder' => _x( 'Suborders', '[Admin] Order table column', 'yith-woocommerce-product-vendors' ) );
					$ref_pos       = array_search( 'order_number', array_keys( $order_columns ), true );
					$order_columns = array_slice( $order_columns, 0, $ref_pos + 1, true ) + $suborder + array_slice( $order_columns, $ref_pos + 1, count( $order_columns ) - 1, true );
				} else {
					$vendor        = array( 'vendor' => _x( 'Vendor', '[Admin] Order table column', 'yith-woocommerce-product-vendors' ) );
					$ref_pos       = array_search( 'order_number', array_keys( $order_columns ), true );
					$order_columns = array_slice( $order_columns, 0, $ref_pos + 1, true ) + $vendor + array_slice( $order_columns, $ref_pos + 1, count( $order_columns ) - 1, true );
				}
			} else {
				if ( ! $post_status || 'trash' !== $post_status ) {
					$suborder      = array( 'parent_order' => _x( 'Parent Order', '[Admin] Order table column', 'yith-woocommerce-product-vendors' ) );
					$ref_pos       = array_search( 'order_number', array_keys( $order_columns ), true );
					$order_columns = array_slice( $order_columns, 0, $ref_pos + 1, true ) + $suborder + array_slice( $order_columns, $ref_pos + 1, count( $order_columns ) - 1, true );
				}

				if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_hide_shipping_billing', 'no' ) ) {
					unset( $order_columns['billing_address'] );
					unset( $order_columns['shipping_address'] );
				}
			}

			return $order_columns;
		}

		/**
		 * Output custom columns for coupons
		 *
		 * @param string         $column The column to be rendered.
		 * @param WC_Order|false $order  The order object or false.
		 * @return void
		 */
		public function render_shop_order_columns( $column, $order = false ) {
			global $post, $the_order;

			if ( ! empty( $order ) ) {
				$current_order = $order;
			} elseif ( empty( $the_order ) || ( $the_order instanceof WC_Order && $the_order->get_id() !== $post->ID ) ) {
				$current_order = wc_get_order( $post->ID );
			} else {
				$current_order = $the_order;
			}

			switch ( $column ) {
				case 'parent_order':
					$parent_order_id = $current_order->get_parent_id();

					if ( $parent_order_id ) {
						$parent_order = wc_get_order( $parent_order_id );
						printf( '<strong>#%s</strong>', esc_html( $parent_order->get_order_number() ) );

						do_action( 'yith_wcmv_after_parent_order_details', $parent_order );

					} else {
						echo '<span class="na">&ndash;</span>';
					}
					break;
				case 'suborder':
					$suborder_ids = YITH_Vendors_Orders::get_suborders( $current_order->get_id() );

					if ( ! empty( $suborder_ids ) ) {
						echo '<ul>';
						foreach ( $suborder_ids as $suborder_id ) {
							$suborder = wc_get_order( $suborder_id );
							if ( ! $suborder ) {
								continue;
							}
							// Vendor info.
							$vendor_id   = yith_wcmv_get_vendor_id_for_order( $suborder );
							$vendor      = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;
							$vendor_name = ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() : '';
							// Order info.
							$order_uri         = apply_filters( 'yith_wcmv_edit_order_uri', $suborder->get_edit_order_url(), absint( $suborder_id ) );
							$order_status_name = wc_get_order_status_name( $suborder->get_status() );
							?>
							<li>
								<mark class="<?php echo esc_attr( $suborder->get_status() ); ?> tips" data-tip="<?php echo esc_attr( $order_status_name ); ?>"><?php echo esc_attr( $order_status_name ); ?></mark>
								<strong><a href="<?php echo esc_url( $order_uri ); ?>"><?php echo esc_html( $suborder->get_order_number() ); ?></a></strong>
								<?php if ( $vendor_name ) : ?>
									<small class="yith-wcmv-suborder-owner">(
										<?php
										// translators: %s stand for the vendor name.
										echo esc_html( sprintf( _x( 'in %s', 'Order table details', 'yith-woocommerce-product-vendors' ), $vendor_name ) );
										?>
										)</small>
								<?php endif; ?>
							</li>
							<?php
							do_action( 'yith_wcmv_after_suborder_details', $suborder );
						}
						echo '</ul>';
					} else {
						echo '<span class="na">&ndash;</span>';
					}

					break;

				case 'vendor':
					$order_author_id = $current_order->get_meta( 'vendor_id' );
					$vendor          = yith_wcmv_get_vendor( $order_author_id, 'user' );
					if ( $vendor->is_valid() ) {
						printf( '<a href="%s">%s</a>', $vendor->get_url( 'admin' ), $vendor->get_name() ); // phpcs:ignore
					} else {
						echo '<span class="na">&ndash;</span>';
					}
					break;
			}
		}

		/**
		 * Add suborder meta-boxes for Vendors order
		 *
		 * @param WC_Order|WP_Post $order The order instance.
		 * @return void
		 */
		public function order_meta_boxes( $order ) {
			global $current_screen, $post;

			$order = $order instanceof WC_Order ? $order : wc_get_order( $post->ID );

			if ( empty( $order ) || is_null( $current_screen ) || $this->screen_id !== $current_screen->id ) {
				return;
			}

			if ( ! empty( YITH_Vendors_Orders::get_suborders( $order->get_id() ) ) ) {
				add_meta_box(
					'woocommerce-suborders',
					_x( 'Suborders', 'Admin: Single order page. Suborder details box', 'yith-woocommerce-product-vendors' ) . ' <span class="tips" data-tip="' . esc_attr__( 'Note: from this box, you can monitor the status of suborders associated to individual vendors.', 'yith-woocommerce-product-vendors' ) . '">[?]</span>',
					array( $this, 'output_meta_boxes' ),
					$this->screen_id,
					'side',
					'core',
					array( 'metabox' => 'suborders' )
				);
			} elseif ( $order->get_parent_id() ) {
				add_meta_box(
					'woocommerce-parent-order',
					_x( 'Parent order', 'Admin: Single order page. Parent order details box', 'yith-woocommerce-product-vendors' ),
					array( $this, 'output_meta_boxes' ),
					$this->screen_id,
					'side',
					'high',
					array( 'metabox' => 'parent-order' )
				);
			}
		}

		/**
		 * Add suborder meta-boxes for Vendors order when HPOS is enabled
		 *
		 * @param string   $screen Current screen ID.
		 * @param WC_Order $order  The order instance.
		 * @return void
		 */
		public function hpos_order_meta_boxes( $screen, $order ) {
			$this->order_meta_boxes( $order );
		}

		/**
		 * Output the suborder metaboxes
		 *
		 * @param WP_Post $post  The post object.
		 * @param array   $param Callback args.
		 * @return void
		 */
		public function output_meta_boxes( $post, $param ) {
			$post_id = $post instanceof WC_Order ? $post->get_id() : $post->ID;
			switch ( $param['args']['metabox'] ) {
				case 'suborders':
					$suborder_ids = YITH_Vendors_Orders::get_suborders( absint( $post_id ) );

					echo '<ul class="suborders-list single-orders">';
					foreach ( $suborder_ids as $suborder_id ) {
						$suborder = wc_get_order( absint( $suborder_id ) );
						if ( ! $suborder ) {
							continue;
						}

						$vendor_id    = yith_wcmv_get_vendor_id_for_order( $suborder );
						$vendor       = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;
						$suborder_uri = $suborder->get_edit_order_url();
						echo '<li class="suborder-info">';
						printf(
							'<mark class="%s tips" data-tip="%s">%s</mark> <strong><a href="%s">#%s</a></strong> <small class="single-order yith-wcmv-suborder-owner">%s %s</small><br/>',
							sanitize_title( $suborder->get_status() ),
							wc_get_order_status_name( $suborder->get_status() ),
							wc_get_order_status_name( $suborder->get_status() ),
							$suborder_uri,
							$suborder->get_order_number(),
							( $vendor && $vendor->is_valid() ) ? esc_html_x( 'in', 'Order table details', 'yith-woocommerce-product-vendors' ) : '-',
							( $vendor && $vendor->is_valid() ) ? $vendor->get_name() : esc_html__( 'Vendor deleted.', 'yith-woocommerce-product-vendors' )
						);
						echo '<li>';
						do_action( 'yith_wcmv_after_suborder_vendor_info', $suborder, $vendor );
					}
					echo '</ul>';
					break;

				case 'parent-order':
					$post_parent      = $post instanceof WC_Order ? $post->get_parent_id() : $post->post_parent;
					$parent_order     = wc_get_order( absint( $post_parent ) );
					$parent_order_uri = $parent_order ? $parent_order->get_edit_order_url() : '#';
					printf( '<a href="%s">&#8592; %s</a>', $parent_order_uri, _x( 'Return to main order', 'Admin: single order page. Link to parent order', 'yith-woocommerce-product-vendors' ) );
					break;

				case 'vendor':
					// @since 2.0.2
					esc_html_e( 'Pass this ID over to the website administrator for any communication related to this order', 'yith-woocommerce-product-vendors' );
					break;
			}
		}

		/**
		 * Filters meta to hide, to add to the list item order meta added by author class
		 *
		 * @since    1.0
		 * @param array $to_hidden Array of order_item_meta meta_key to hide.
		 * @return array
		 */
		public function hidden_order_itemmeta( $to_hidden ) {
			if ( apply_filters( 'yith_wcmv_hide_commissions_order_item_meta', true ) && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
				$to_hidden = array_merge(
					$to_hidden,
					array(
						'_commission_id',
						'_child__commission_id',
						'_parent_line_item_id',
						'_commission_included_tax',
						'_commission_included_coupon',
					)
				);
			}

			return $to_hidden;
		}

		/**
		 * Add the commission information to order line item
		 *
		 * @since  1.9.12
		 * @param integer         $item_id The item ID.
		 * @param WC_Order_Item   $item    The order item object.
		 * @param WC_Product|null $product The line item associated product. Null otherwise.
		 */
		public function commission_info_in_order_line_item( $item_id, $item, $product ) {

			global $theorder;

			$commission_id = $item->get_meta( '_commission_id', true );
			if ( empty( $commission_id ) ) { // Get the child commission id if any. Leave for backward compatibility.
				$commission_id = $item->get_meta( '_child__commission_id', true );
			}

			if ( $theorder && ! empty( $commission_id ) && apply_filters( 'yith_wcmv_show_commission_info_in_order_line_item', true ) ) {

				$commission = yith_wcmv_get_commission( $commission_id );
				if ( ! $commission ) {
					return;
				}

				$commission_included_tax    = $item->get_meta( '_commission_included_tax' );
				$commission_included_coupon = 'yes' === $item->get_meta( '_commission_included_coupon' );

				$tax_string = array(
					'website' => _x( 'credited to the website admin', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
					'split'   => _x( 'split by percentage between admin and vendor', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
					'vendor'  => _x( 'credited to the vendor', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				);

				$on_product_price_text  = _x( 'on product price', 'part of: Commission: 19,00$ (50% on product price)', 'yith-woocommerce-product-vendors' );
				$on_shipping_price_text = _x( 'on shipping price', 'part of: Commission: 19,00$ (50% on product price)', 'yith-woocommerce-product-vendors' );
				$tax_label              = isset( $tax_string[ $commission_included_tax ] ) ? $tax_string[ $commission_included_tax ] : '';
				$coupon                 = $commission_included_coupon ? _x( 'included', 'means: Vendor commission have been calculated: coupon included', 'yith-woocommerce-product-vendors' ) : _x( 'excluded', 'means: Vendor commission have been calculated: coupon excluded', 'yith-woocommerce-product-vendors' );
				// Set refund amount message.
				$refunded_amount_message = ! empty( (float) $commission->get_amount_refunded( 'edit' ) ) ? '%s: <strong class="commission-amount-refunded">%s</strong><br/>' : '';
				$refunded_amount_message = sprintf( $refunded_amount_message, _x( 'Refunded amount', 'Single order label', 'yith-woocommerce-product-vendors' ), $commission->get_amount_refunded( 'display', array( 'currency' => $theorder->get_currency() ) ) );

				// Link to commission details.
				$msg = sprintf(
					"<a href='%s' class='%s' target='_blank'>%s #%d</a> <small>(%s: <strong>%s</strong>)</small>",
					$commission->get_view_url(),
					'commission-id-label',
					__( 'Commission', 'yith-woocommerce-product-vendors' ),
					$commission->get_id(),
					__( 'status', 'yith-woocommerce-product-vendors' ),
					strtolower( $commission->get_status( 'display' ) )
				);

				// Set the message.
				$msg .= sprintf(
					'<br/>%s: <strong>%s</strong> (%s %s)<br/>%s%s: <strong>%s</strong>',
					__( 'Commission amount', 'yith-woocommerce-product-vendors' ),
					$commission->get_amount( 'display', array( 'currency' => $theorder->get_currency() ) ),
					$commission->get_rate( 'display' ),
					'shipping' === $commission->get_type() ? $on_shipping_price_text : $on_product_price_text,
					$refunded_amount_message,
					_x( 'Amount to pay', 'Single order label', 'yith-woocommerce-product-vendors' ),
					$commission->get_amount_to_pay( 'display', array( 'currency' => $theorder->get_currency() ) )
				);

				if ( 'product' === $commission->get_type() ) {
					$msg .= sprintf(
						'<br/><small><em>%s: %s <strong>%s</strong> - %s <strong>%s</strong></em></small>',
						_x( 'Vendor commission has been calculated', 'part of: Vendor commission have been calculated: tax included', 'yith-woocommerce-product-vendors' ),
						_x( 'with tax', 'part of: tax included or tax excluded', 'yith-woocommerce-product-vendors' ),
						$tax_label,
						_x( 'coupon', 'part of: coupon included or coupon excluded', 'yith-woocommerce-product-vendors' ),
						$coupon
					);
				}

				$msg = apply_filters( 'yith_wcmv_order_details_page_commission_message', $msg, $item_id, $commission );
				echo wp_kses_post( sprintf( '<span class="yith-order-item-commission-details">%s</span>', $msg ) );
			}
		}

		/**
		 * Hack WoCommerce processing order count
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function count_orders() {
			$order_statuses = array_keys( wc_get_order_statuses() );
			foreach ( $order_statuses as $status ) {
				$count       = 0;
				$cache_key   = WC_Cache_Helper::get_cache_prefix( 'orders' ) . $status;
				$cache_group = 'yith_wcmv';

				$cached = wp_cache_get( $cache_group . '_' . $cache_key, $cache_group );
				if ( false !== $cached ) {
					return;
				}

				foreach ( wc_get_order_types( 'order-count' ) as $type ) {
					$orders = wc_get_orders(
						array(
							'type'   => $type,
							'limit'  => -1,
							'return' => 'ids',
							'status' => $status,
							'parent' => 0,
						)
					);

					$count += count( $orders ); // phpcs:ignore
				}

				wp_cache_set( $cache_key, $count, 'counts' );
				wp_cache_set( $cache_group . '_' . $cache_key, true, $cache_group );
			}
		}

		/**
		 * Hack WoCommerce processing order count
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor (Optional) Vendor object. Use current if not passed.
		 * @return integer
		 */
		public function count_vendor_processing_order( $vendor = false ) {
			global $yith_wcmv_cache;

			$count = 0;
			if ( empty( $vendor ) ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			}

			if ( ! $vendor || ! $vendor->is_valid() ) {
				return $count;
			}

			$count = $yith_wcmv_cache->get_vendor_cache( $vendor->get_id(), 'processing_order_count' );
			if ( false === $count ) {
				$orders = wc_get_orders(
					array(
						'return'     => 'ids',
						'status'     => array( 'wc-processing' ),
						'type'       => 'shop_order',
						'meta_query' => array(
							array(
								'key'   => 'vendor_id',
								'value' => $vendor->get_id(),
							),
						),
					)
				);

				$count = count( $orders );
				$yith_wcmv_cache->set_vendor_cache( $vendor->get_id(), 'processing_order_count', $count );
			}

			return $count;
		}

		/**
		 * Add sold by information to product in order details
		 * The follow args are documented in woocommerce\templates\emails\email-order-items.php:37
		 *
		 * @since    1.6
		 * @param integer    $item_id The line item ID.
		 * @param array      $item    Item data.
		 * @param WC_Product $product The product related to order item.
		 * @return  void
		 */
		public function add_sold_by_to_order( $item_id, $item, $product ) {
			$current           = yith_wcmv_get_vendor( 'current', 'user' );
			$vendor_by_product = isset( $item['product_id'] ) ? yith_wcmv_get_vendor( $item['product_id'], 'product' ) : false;

			if ( empty( $vendor_by_product ) || ! $vendor_by_product->is_valid() ) {
				return;
			}
			// Exclude add label for same vendor.
			if ( $current && $current->is_valid() && $current->get_id() === $vendor_by_product->get_id() ) {
				return;
			}

			$vendor_uri = $vendor_by_product->get_url( 'admin' );
			echo wp_kses_post( ' (<small>' . apply_filters( 'yith_wcmv_sold_by_string_admin', _x( 'Sold by', 'Order details: Product sold by', 'yith-woocommerce-product-vendors' ) ) . ': <a href="' . $vendor_uri . '" target="_blank">' . $vendor_by_product->get_name() . '</a></small>)' );

		}

		/**********************************************
		 * VENDOR DASHBOARD HOOKS|FILTERS
		 *********************************************/

		/**
		 * Register hooks and filter for vendor with limited access
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The current vendor object.
		 * @return void
		 */
		public function vendor_limited_access_hooks( $vendor ) {
			if ( 'yes' !== get_option( 'yith_wpv_vendors_option_order_management', 'no' ) ) {
				return;
			}

			$this->vendor_id = $vendor->get_id();

			// Add post types.
			add_filter( 'yith_wcmv_vendor_allowed_vendor_post_type', array( $this, 'add_vendor_post_types' ), 10, 1 );// Restrict add orders.
			add_action( 'current_screen', array( $this, 'restrict_add_vendor_orders' ), 0, 1 );
			add_action( 'admin_menu', array( $this, 'add_orders_menu' ), 15 );
			// Order actions.
			add_filter( 'woocommerce_order_actions', array( $this, 'maybe_remove_order_actions' ), 99, 1 );
			// Filter the preview order data for vendors.
			add_filter( 'woocommerce_admin_order_preview_get_order_details', array( $this, 'order_preview_get_order_details' ), 99, 2 );
			add_filter( 'manage_shop_order_posts_columns', array( $this, 'customize_order_columns' ), 15 );
			add_filter( 'woocommerce_shop_order_list_table_columns', array( $this, 'customize_order_columns' ), 15 );

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				add_action( 'current_screen', array( $this, 'restrict_edit_orders' ), 0, 1 );
				add_action( 'add_meta_boxes', array( $this, 'vendor_hpos_order_meta_boxes' ), 30, 2 );
				add_filter( 'is_protected_meta', array( $this, 'set_protected_meta' ), 10, 3 );
			} else {
				// Handle post filter.
				add_action( 'yith_wcmv_restrict_edit_shop_order_vendor', array( $this, 'restrict_edit_orders' ) );
				add_action( 'yith_wcmv_vendor_filter_content_shop_order', array( $this, 'filter_vendor_orders_list' ), 10, 2 );
				add_action( 'yith_wcmv_vendor_filter_count_post_shop_order', array( $this, 'filter_vendor_count_orders' ), 10, 2 );
				add_action( 'add_meta_boxes', array( $this, 'vendor_order_meta_boxes' ), 30 );
				// Hide protected meta from custom meta table. This is for backward compatibility since meta visibility is not protected.
				add_filter( 'auth_post_meta_vendor_id_for_shop_order', '__return_false' );
				add_filter( 'auth_post_meta_has_sub_order_for_shop_order', '__return_false' );
			}
		}

		/**
		 * Add shop_order post type to post type available for vendor
		 *
		 * @since  4.0.0
		 * @param array $post_types The default post types array value.
		 * @return array
		 */
		public function add_vendor_post_types( $post_types ) {
			$post_types[] = 'shop_order';

			return $post_types;
		}

		/**
		 * Filter content based on current vendor
		 *
		 * @since  4.0.0
		 * @param WP_Query    $query  The Wp_Query instance.
		 * @param YITH_Vendor $vendor Current vendor.
		 * @return void
		 */
		public function filter_vendor_orders_list( $query, $vendor ) {
			$conditions = $query->get( 'meta_query' );
			if ( ! is_array( $conditions ) ) {
				$conditions = array();
			}

			$keys = ! empty( $conditions ) ? wp_list_pluck( $conditions, 'key' ) : array();
			if ( ! in_array( 'vendor_id', $keys, true ) ) {
				$conditions[] = array(
					'key'   => 'vendor_id',
					'value' => $vendor->get_id(),
				);
			}

			$query->set( 'meta_query', $conditions );
		}

		/**
		 * Filter the coupon count for vendor
		 *
		 * @since    4.0.0
		 * @param boolean|array $counts Current counts.
		 * @param YITH_Vendor   $vendor Current vendor.
		 * @return boolean|array
		 */
		public function filter_vendor_count_orders( $counts, $vendor ) {

			global $wpdb;

			$result = $wpdb->get_results( // phpcs:ignore
				$wpdb->prepare(
					"SELECT post_status, COUNT( DISTINCT ID ) AS count 
						FROM {$wpdb->posts} AS p 
						INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID
						WHERE pm.meta_key = %s AND pm.meta_value = %s AND p.post_type = %s GROUP BY p.post_status",
					'vendor_id',
					$vendor->get_id(),
					'shop_order'
				)
			);

			$posts  = ! empty( $result ) ? $result : array();
			$counts = array_fill_keys( get_post_stati(), 0 );

			foreach ( $posts as $post ) {
				$counts[ $post->post_status ] = $post->count;
			}

			return $counts;
		}

		/**
		 * Restrict add order for vendors
		 *
		 * @since  4.0.0
		 * @param WP_Screen $screen Current screen instance.
		 * @return void
		 */
		public function restrict_add_vendor_orders( $screen ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( $this->is_order_section() && ( 'add' === $screen->action || ( isset( $_GET['action'] ) && 'new' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) ) {
				// translators: %1$s and %2$s are open and closing tag for a html anchor.
				wp_die( sprintf( __( 'You are not allowed to create orders manually. %1$sClick here to return to your admin area%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_order' ) . '">', '</a>' ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Restrict edit order to only vendor own orders
		 *
		 * @since  4.0.0
		 * @param WP_Screen $screen Current screen instance.
		 * @return void
		 */
		public function restrict_edit_orders( $screen = null ) {
			if ( empty( $screen ) ) {
				$screen = get_current_screen();
			}
			// phpcs:disable WordPress.Security.NonceVerification
			if ( $this->is_order_section() && ( 'edit' === $screen->action || ( isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) ) {
				$order_id        = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
				$order           = $order_id ? wc_get_order( $order_id ) : false;
                $vendor_ids       = apply_filters( 'yith_wcmv_hpos_vendor_orders_list_vendor_id', array( $this->vendor_id ), $this->vendor_id  );
				$is_vendor_order = $order ? in_array( absint( yith_wcmv_get_vendor_id_for_order( $order ) ), $vendor_ids ) : false;
				if ( ! $is_vendor_order ) {
					// translators: %1$s and %2$s stand for the link html open and close tag.
					wp_die( sprintf( __( 'You do not have permission to edit this order. %1$sClick here to view and edit your orders%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_order' ) . '">', '</a>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Add suborder meta-boxes for vendor order
		 *
		 * @param WC_Order|WP_Post $order The order instance.
		 * @return void
		 */
		public function vendor_order_meta_boxes( $order ) {
			global $current_screen, $post;

			// Remove default order meta-box according to settings.
			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_edit_custom_fields', 'yes' ) ) {
				remove_meta_box( 'order_custom', $this->screen_id, 'normal' );
				remove_meta_box( 'postcustom', $this->screen_id, 'normal' );
			}

			$order = $order instanceof WC_Order ? $order : wc_get_order( $post->ID );

			if ( empty( $order ) || is_null( $current_screen ) || $this->screen_id !== $current_screen->id ) {
				return;
			}

			// @since 2.0.2
			$order_id = $order->get_parent_id();
			$order    = $order_id ? wc_get_order( $order_id ) : false;

			if ( $order instanceof WC_Order ) {
				add_meta_box(
					'woocommerce-parent-order',
					sprintf( '%s: <em>#%s</em>', _x( 'Parent order ID', 'Admin: Single order page. Parent order details box', 'yith-woocommerce-product-vendors' ), $order->get_order_number() ),
					array( $this, 'output_meta_boxes' ),
					$this->screen_id,
					'side',
					'high',
					array( 'metabox' => 'vendor' )
				);
			}
		}

		/**
		 * Add suborder meta-boxes for vendor orders using HPOS system
		 *
		 * @param string   $screen Current screen ID.
		 * @param WC_Order $order  The order instance.
		 * @return void
		 */
		public function vendor_hpos_order_meta_boxes( $screen, $order ) {
			$this->vendor_order_meta_boxes( $order );
		}

		/**
		 * Add the order menu item if missing
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_orders_menu() {
			$menu_slug  = 'edit.php?post_type=shop_order';
			$page_title = esc_html__( 'Orders', 'yith-woocommerce-product-vendors' );
			$menu_title = esc_html__( 'Orders', 'yith-woocommerce-product-vendors' );

			// Add order count to menu title.
			if ( apply_filters( 'yith_wcmv_woocommerce_include_processing_order_count_in_menu', true ) ) {
				$order_count = $this->count_vendor_processing_order();
				$menu_title  .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $order_count ) . '"><span class="processing-count">' . number_format_i18n( $order_count ) . '</span></span>';
			}

			add_menu_page( $page_title, $menu_title, 'edit_shop_orders', $menu_slug, '', 'dashicons-cart', 56 );
		}

		/**
		 * Maybe remove order actions for vendor
		 *
		 * @since  4.0.0
		 * @param array $actions An array of order actions.
		 * @return $actions
		 */
		public function maybe_remove_order_actions( $actions ) {
			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_resend_email', 'no' ) ) {
				$actions = array();
			}

			return $actions;
		}

		/**
		 * Filtered the order preview data
		 *
		 * @since  3.4.1
		 * @param array    $data  The order preview data.
		 * @param WC_Order $order Current order object.
		 * @return array Filtered preview data
		 */
		public function order_preview_get_order_details( $data, $order ) {

			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_customer', 'no' ) ) {
				$data['data']['billing']['phone'] = '';
				$data['data']['billing']['email'] = '';
			}

			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_payment', 'no' ) ) {
				$data['payment_via'] = '';
			}

			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_billing_shipping', 'no' ) ) {
				$data['formatted_shipping_address'] = '';
				$data['formatted_billing_address']  = '';
			}

			return $data;
		}

		/**
		 * Customize WC_Order list table columns
		 *
		 * @since  4.0.0
		 * @param array $columns The order table columns.
		 * @return array
		 */
		public function customize_order_columns( $columns ) {
			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_show_billing_shipping', 'no' ) ) {
				unset( $columns['billing_address'], $columns['shipping_address'] );
			}

			return $columns;
		}

		/**
		 * Set vendor_id as protected meta.
		 *
		 * @since 3.2.0
		 *
		 * @param bool   $protected Whether the key is considered protected.
		 * @param string $meta_key  Metadata key.
		 * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
		 *                          or any other object type with an associated meta table.
		 * @return bool
		 */
		public function set_protected_meta( $protected, $meta_key, $meta_type ) {
			return $protected || 'vendor_id' === $meta_key;
		}

		/**
         * Filter orders by vendor ID.
         *
		 * @param array $query WC Get Orders main query.
		 * @param array $query_vars WC Get Orders query vars.
		 *
		 * @return array
		 */
		public function filter_orders( $query, $query_vars ){

			if ( ! empty( $query_vars['vendor_id'] ) ) {
				$query['meta_query'][] = array(
					'key'   => 'vendor_id',
					'value' => $query_vars['vendor_id'],
				);
			}

            if ( ! empty( $query_vars['_parent_refund_id'] ) ) {
                $query['meta_query'][] = array(
                    'key'   => '_parent_refund_id',
                    'value' => $query_vars['_parent_refund_id'],
                );
            }

			return $query;
		}
	}
}
