<?php
/**
 * YITH Vendors Commissions List Table Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'YITH_Vendors_Commissions_List_Table' ) ) {
	/**
	 * YITH_Vendors_Commissions_List_Table class.
	 */
	class YITH_Vendors_Commissions_List_Table extends WP_List_Table {

		/**
		 * Construct
		 */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'commission',
					'plural'   => 'commissions',
					'ajax'     => false,
					'screen'   => 'yith-plugins_page_yith-wcmv-commissions-list',
				)
			);

			add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
			// Months dropdown.
			add_filter( 'pre_months_dropdown_query', array( $this, 'get_months_dropdown' ), 10 );
			add_action( 'wc_order_statuses', array( $this, 'custom_order_status' ) );
		}

		/**
		 * Returns columns available in table
		 *
		 * @since 1.0.0
		 * @return array Array of columns of the table
		 */
		public function get_columns() {

			return apply_filters(
				'yith_wcmv_commissions_list_table_column',
				array(
					'cb'            => '<input type="checkbox" />',
					'commission_id' => __( 'ID', 'yith-woocommerce-product-vendors' ),
					'date'          => __( 'Date', 'yith-woocommerce-product-vendors' ),
					'date_edit'     => __( 'Last update', 'yith-woocommerce-product-vendors' ),
					'order_id'      => __( 'Order', 'yith-woocommerce-product-vendors' ),
					'line_item'     => __( 'Product', 'yith-woocommerce-product-vendors' ),
					'rate'          => __( 'Rate', 'yith-woocommerce-product-vendors' ),
					'amount'        => __( 'Total', 'yith-woocommerce-product-vendors' ),
					'refunded'      => __( 'Refunded', 'yith-woocommerce-product-vendors' ),
					'to_pay'        => __( 'Commission', 'yith-woocommerce-product-vendors' ),
					// translators: %s stand for the vendor taxonomy singular label.
					'vendor'        => sprintf( _x( '%s info', '[Admin] %s stand for the vendor taxonomy singular label', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ) ),
					'status'        => __( 'Status', 'yith-woocommerce-product-vendors' ),
					'actions'       => '',
				)
			);
		}

		/**
		 * Gets a list of CSS classes for the WP_List_Table table tag.
		 *
		 * @since 3.1.0
		 * @return string[] Array of CSS classes for the table tag.
		 */
		protected function get_table_classes() {
			$classes = parent::get_table_classes();

			return array_merge( $classes, array( 'yith-plugin-fw__classic-table' ) );
		}

		/**
		 * Adjust which columns are displayed by default.
		 *
		 * @since  4.0.0
		 * @param array  $hidden Current hidden columns.
		 * @param object $screen Current screen.
		 * @return array
		 */
		public function default_hidden_columns( $hidden, $screen ) {
			$hidden = array_merge(
				$hidden,
				array(
					'amount',
					'refunded',
					'date_edit',
				)
			);

			return $hidden;
		}

		/**
		 * Prepare items for table
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function prepare_items() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$items = array();

			// Sets pagination arguments.
			$per_page     = apply_filters( 'yith_wcmv_commissions_list_table_per_page', 20 );
			$current_page = absint( $this->get_pagenum() );
			$order        = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Commissions args.
			$args = array(
				'status'  => $this->get_current_view(),
				'paged'   => $current_page,
				'number'  => $per_page,
				'orderby' => $orderby ? $orderby : 'ID',
				'order'   => in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? $order : 'DESC',
			);

			foreach ( array( 'm', 's', 'vendor_id', 'product_id' ) as $key ) {
				if ( ! empty( $_GET[ $key ] ) ) {
					$args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
				}
			}

			$args = apply_filters( 'yith_wcmv_commissions_list_table_args', $args );

			$commission_ids = YITH_Vendors_Commissions_Factory::query( $args );
			$total_items    = YITH_Vendors_Commissions_Factory::count( $args );

			// Sets columns headers.
			$columns               = $this->get_columns();
			$hidden                = get_hidden_columns( $this->screen->id );
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			foreach ( $commission_ids as $commission_id ) {
				$items[] = yith_wcmv_get_commission( absint( $commission_id ) );
			}

			// Retrieve data for table. Use array filter to remove empty commission (this should not happen anyway).
			$this->items = array_filter( $items );

			// Sets pagination args.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Display the search box.
		 *
		 * @since  4.0.0
		 * @access public
		 * @param string $text     The search button text.
		 * @param string $input_id The search input id.
		 * @return mixed
		 */
		public function add_search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @since 4.0.0
		 * @return array The array of columns that can be sorted by the user.
		 */
		public function get_sortable_columns() {
			return array(
				'commission_id' => array( 'ID', false ),
				'order_id'      => array( 'order_id', false ),
				'amount'        => array( 'amount', false ),
				'date_edit'     => array( 'last_edit', false ),
				'vendor'        => array( 'vendor_id', false ),
			);
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @since 4.0.0
		 * @return array Array of available actions.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'export_commissions_csv' => __( 'Export commissions to CSV file', 'yith-woocommerce-product-vendors' ),
				'delete_commissions'     => __( 'Delete commissions', 'yith-woocommerce-product-vendors' ),
			);
			foreach ( yith_wcmv_get_commission_statuses() as $action => $label ) {
				$actions[ 'commissions_status_change_to_' . $action ] = __( 'Change to', 'yith-woocommerce-product-vendors' ) . ' ' . strtolower( $label );
			}

			return apply_filters( 'yith_wcmv_commissions_list_table_bulk_actions', $actions );
		}

		/**
		 * Prints column cb
		 *
		 * @since 1.0.0
		 * @param YITH_Vendors_Commission $commission Item to use to print CB record.
		 * @return string
		 */
		public function column_cb( $commission ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['plural'], // Let's simply repurpose the table's plural label.
				$commission->get_id() // The value of the checkbox should be the record's id.
			);
		}

		/**
		 * Output commission ID column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_commission_id( $commission ) {
			$html = sprintf( '<a href="javascript:void(0)" class="commission-details" data-commission_id="%1$s">#%1$s</a>', $commission->get_id() );

			return apply_filters( 'yith_wcmv_commissions_list_table_commission_id_column', $html, $commission );
		}

		/**
		 * Output order ID column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_order_id( $commission ) {

			$order     = $commission->get_order();
			$order_uri = $commission->get_formatted_order_uri();
			$user      = yith_wcmv_get_formatted_order_user_html( $order );
			// translators: %1$s is the order number, %2$s is the customer.
			$html = sprintf( _x( '%1$s by %2$s', '[Admin]Order number by user', 'yith-woocommerce-product-vendors' ), $order_uri, $user );

			$billing_email = $order ? $order->get_billing_email() : '';
			if ( $billing_email ) {
				$html .= sprintf( '<small class="meta email"><a href="mailto:%1$s">%1$s</a></small>', $billing_email );
			}

			$order_status = ! empty( $order ) ? wc_get_order_status_name( $order->get_status() ) : '';
			$html        .= $order_status ? '<small style="display:block;">(' . esc_html__( 'Order status:', 'yith-woocommerce-product-vendors' ) . ' ' . $order_status . ')</small>' : '';

			return apply_filters( 'yith_wcmv_commissions_list_table_order_id_column', $html, $commission );
		}

		/**
		 * Output line item column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_line_item( $commission ) {

			$html = '<small class="meta">-</small>';

			if ( 'shipping' === $commission->get_type() ) {
				$shipping_fee = _x( 'Shipping fee', '[Admin]: Commission type', 'yith-woocommerce-product-vendors' );
				$html         = "<strong>{$shipping_fee}</strong>";
			} else {
				$item = $commission->get_item();

				if ( $item ) {
					/** @var WC_Product $product */
					$product     = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
					$product_id  = is_callable( array( $item, 'get_product_id' ) ) ? $item->get_product_id() : false;
					$product_url = $product_id ? apply_filters( 'yith_wcmv_commissions_list_table_product_url', admin_url( 'post.php?post=' . $product_id . '&action=edit' ), $product, $commission ) : '';

					$html  = $product instanceof WC_Product ? $product->get_image( 'thumbnail' ) : '';
					$html .= ! empty( $product_url ) ? "<a target='_blank' href='{$product_url}'><strong>{$item->get_name()}</strong></a>" : "<strong>{$item->get_name()}</strong>";
				}
			}

			return apply_filters( 'yith_wcmv_commissions_list_table_line_item_column', $html, $commission );
		}

		/**
		 * Output rate column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_rate( $commission ) {
			return apply_filters( 'yith_wcmv_commissions_list_table_rate_column', $commission->get_rate( 'display' ), $commission );
		}

		/**
		 * Output vendor column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_vendor( $commission ) {

			$vendor = $commission->get_vendor();

			if ( ! empty( $vendor ) ) {
				if ( ! $vendor->is_valid() ) {
					$html = '<em>' . esc_html__( 'Vendor deleted.', 'yith-woocommerce-product-vendors' ) . '</em>';
				} else {

					$html           = yith_wcmv_get_formatted_user_html( $commission->get_user() );
					$html && $html .= '<br>';

					$vendor_url = apply_filters( 'yith_wcmv_commissions_list_table_vendor_url', $vendor->get_url( 'admin' ), $vendor, $commission );
					$html      .= __( 'Store', 'yith-woocommerce-product-vendors' ) . ': ';
					$html      .= ! empty( $vendor_url ) ? ' <a href="' . esc_url( $vendor_url ) . '" target="_blank">' . esc_html( $vendor->get_name() ) . '</a>' : esc_html( $vendor->get_name() );
				}
			}

			return apply_filters( 'yith_wcmv_commissions_list_table_vendor_column', $html, $commission );
		}

		/**
		 * Output amount column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_amount( $commission ) {
			return apply_filters( 'yith_wcmv_commissions_list_table_amount_column', $commission->get_amount( 'display' ), $commission );
		}

		/**
		 * Output amount to pay column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_to_pay( $commission ) {
			return apply_filters( 'yith_wcmv_commissions_list_table_to_pay_column', $commission->get_amount_to_pay( 'display' ), $commission );
		}

		/**
		 * Output amount refunded column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_refunded( $commission ) {
			return apply_filters( 'yith_wcmv_commissions_list_table_refunded_column', $commission->get_amount_refunded( 'display' ), $commission );
		}

		/**
		 * Output status column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_status( $commission ) {
			$html = sprintf( '<span class="status-badge %s">%s</span>', strtolower( $commission->get_status() ), $commission->get_status( 'display' ) );

			return apply_filters( 'yith_wcmv_commissions_list_table_amount_column', $html, $commission );
		}

		/**
		 * Output actions column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_actions( $commission ) {

			$html = yith_plugin_fw_get_component(
				array(
					'type'  => 'action-button',
					'class' => 'commission-details',
					'title' => __( 'View commission', 'yith-woocommerce-product-vendors' ),
					'data'  => array(
						'commission_id' => $commission->get_id(),
					),
					'icon'  => 'eye',
					'url'   => '#',
				),
				false
			);

			if ( apply_filters( 'yith_wcmv_commissions_list_table_add_single_actions', true ) ) {
				$commission_status  = $commission->get_status( 'edit' );
				$available_statuses = yith_wcmv_get_commission_statuses( true );
				$actions            = array();

				foreach ( $available_statuses as $status => $label ) {
					if ( ! YITH_Vendors()->commissions->is_status_changing_permitted( $status, $commission_status ) ) {
						continue;
					}

					$actions[] = array(
						// translators: %s stand for the commission status name.
						'name'         => sprintf( _x( 'Change to %s', '[Admin]Commission table action label', 'yith-woocommerce-product-vendors' ), strtolower( $label ) ),
						'url'          => YITH_Vendors_Admin_Commissions::get_commission_action_url( $commission->get_id(), 'change_commissions_status', array( 'status' => $status ) ),
						'confirm_data' => array(
							'title'   => _x( 'Confirm status change', '[Admin]Commission modal action title', 'yith-woocommerce-product-vendors' ),
							'message' => sprintf(
							// translators: %1$s stand for the commission ID, %2$s stand for the current commission status, %3$s stand for the new commission status.
								_x( 'Are you sure you want to change commission #%1$s status from %2$s to %3$s?', '[Admin]Commission modal action message', 'yith-woocommerce-product-vendors' ),
								$commission->get_id(),
								$commission->get_status( 'display' ),
								$label
							),
						),
					);
				}

				$actions[] = array(
					'name'         => 'Delete',
					'url'          => YITH_Vendors_Admin_Commissions::get_commission_action_url( $commission->get_id(), 'delete_commissions' ),
					'confirm_data' => array(
						'title'               => _x( 'Confirm delete', '[Admin]Commission modal action title', 'yith-woocommerce-product-vendors' ),
						'message'             => sprintf(
						// translators: %s stand for the commission ID.
							_x( 'Are you sure you want to delete commission #%s?', '[Admin]Commission modal action message', 'yith-woocommerce-product-vendors' ),
							$commission->get_id()
						),
						'confirm-button'      => _x( 'Delete', '[Admin]Commission modal button label', 'yith-woocommerce-product-vendors' ),
						'confirm-button-type' => 'delete',
					),
				);

				// Let's filter actions.
				$actions = apply_filters( 'yith_wcmv_single_commission_row_actions', $actions, $commission );

				if ( ! empty( $actions ) ) {
					$html .= yith_plugin_fw_get_component(
						array(
							'type'   => 'action-button',
							'action' => 'show-more',
							'icon'   => 'more',
							'menu'   => $actions,
						),
						false
					);
				}
			}

			return apply_filters( 'yith_wcmv_commissions_list_table_actions_column', $html, $commission );
		}

		/**
		 * Output date column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_date( $commission ) {
			return apply_filters( 'yith_wcmv_commissions_list_table_date_column', yith_wcmv_get_formatted_date_html( $commission->get_date() ), $commission );
		}

		/**
		 * Output date edit column content
		 *
		 * @since 5.0.0
		 * @param YITH_Commission $commission The commission object.
		 * @return string
		 */
		public function column_date_edit( $commission ) {
			$date = $commission->get_last_edit();
			if ( ( empty( $last_edit ) || strpos( $last_edit, '0000-00-00' ) !== false ) ) {
				$date = $commission->get_date();
			}

			return apply_filters( 'yith_wcmv_commissions_list_table_date_edit_column', yith_wcmv_get_formatted_date_html( $date ), $commission );
		}

		/**
		 * Print the columns default.
		 * Let third party use this custom hook to output custom column content.
		 *
		 * @since  4.0.0
		 * @param YITH_Vendors_Commission $commission  The commission object.
		 * @param string                  $column_name Current column name.
		 * @return void
		 */
		protected function column_default( $commission, $column_name ) {
			$vendor = $commission->get_vendor();
			do_action( "yith_wcmv_commissions_list_table_col_{$column_name}", $commission, $vendor, $column_name );
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  1.0.0
		 * @return string The view name
		 */
		public function get_current_view() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['commission_status'] ) ) {
				return get_option( 'yith_commissions_default_table_view', 'all' );
			}

			$status = sanitize_text_field( wp_unslash( $_GET['commission_status'] ) );

			return ( 'unpaid' === $status ) ? array( 'unpaid', 'processing' ) : $status;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Whether the table has items to display or not
		 *
		 * @since 3.1.0
		 *
		 * @return bool
		 */
		public function has_items() {
			// Private items empty for custom views.
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() ) {
				$args = array( 'vendor_id' => $vendor->get_id() );
			} else {
				$args = array();
			}

			return ! empty( $this->items ) || YITH_Vendors_Commissions_Factory::count( $args );
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  4.0.0
		 * @access protected
		 */
		protected function get_views() {

			if ( ! $this->has_items() ) {
				return array();
			}

			$views        = array_merge( array( 'all' => __( 'All', 'yith-woocommerce-product-vendors' ) ), yith_wcmv_get_commission_statuses() );
			$current_view = (array) $this->get_current_view();

			// Merge Unpaid with Processing.
			$views['unpaid'] .= '/' . $views['processing'];
			unset( $views['processing'] );

			foreach ( $views as $id => $view ) {
				$args = array( 'status' => 'unpaid' === $id ? array( $id, 'processing' ) : $id );
				// Let's filter views query args.
				$args  = apply_filters( 'yith_wcmv_commissions_list_table_views_args', $args );
				$count = YITH_Vendors_Commissions_Factory::count( $args );

				// Build the navigation item.
				$href         = esc_url( add_query_arg( 'commission_status', $id ) );
				$class        = in_array( $id, $current_view, true ) ? 'current' : '';
				$views[ $id ] = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $count );
			}

			return $views;
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  3.1.0
		 * @param string $which Nav location.
		 */
		protected function extra_tablenav( $which ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! $this->has_items() ) {
				return;
			}

			if ( 'top' === $which ) {
				if ( ! empty( $_REQUEST['status'] ) ) {
					echo '<input type="hidden" name="status" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) ) . '" />';
				}

				$this->months_dropdown( 'commissions' );
				$this->product_dropdown();
				$this->vendor_dropdown();

				submit_button( _x( 'Filter', '[Admin]Commissions list button label', 'yith-woocommerce-product-vendors' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );

				if ( isset( $_REQUEST['s'] ) ) {
					$reset_button = apply_filters( 'yith_wcmv_commissions_list_table_reset_filter_url', YITH_Vendors_Admin_Commissions::get_commissions_list_table_url() );
					echo '<a href="' . esc_url( $reset_button ) . '" class="button-primary reset-button">' . esc_html_x( 'Reset', '[Admin]Commissions list button label', 'yith-woocommerce-product-vendors' ) . '</a>';
				}

				submit_button(
					_x( 'Export CSV', '[Admin]Commissions list button label', 'yith-woocommerce-product-vendors' ),
					'button filter-button',
					'export_commissions_csv',
					false,
					array(
						'id'     => 'export-commissions-csv',
						'target' => '_blank',
					)
				);
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Add the product dropdown
		 *
		 * @since  1.0
		 * @return void
		 */
		public function product_dropdown() {

			if ( ! apply_filters( 'yith_wcmv_commissions_list_table_show_product_filter', true ) ) {
				return;
			}

			$product_id      = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$product         = ! empty( $product_id ) ? wc_get_product( $product_id ) : false;
			$product_display = ! empty( $product ) ? $product->get_name() . '(#' . $product_id . ')' : '';

			$select2_args = array(
				'class'            => 'wc-product-search',
				'id'               => 'product_id',
				'name'             => 'product_id',
				'data-placeholder' => __( 'Search for a product&hellip;', 'yith-woocommerce-product-vendors' ),
				'data-action'      => 'woocommerce_json_search_products',
				'data-allow_clear' => true,
				'data-selected'    => array( $product_id => $product_display ),
				'data-multiple'    => false,
				'value'            => $product_id,
				'style'            => 'width: 180px;',
			);

			?>
			<div id="product_data_search" class="panel data_search_wrapper">
				<div class="options_group">
					<?php yit_add_select2_fields( $select2_args ); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Add the vendor dropdown
		 *
		 * @since  1.0
		 * @return void
		 */
		public function vendor_dropdown() {

			if ( ! apply_filters( 'yith_wcmv_commissions_list_table_show_vendor_filter', true ) ) {
				return;
			}

			$vendor_id      = ! empty( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$vendor         = yith_wcmv_get_vendor( $vendor_id, 'vendor' );
			$vendor_display = ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() . '(#' . $vendor->get_id() . ')' : '';
			$select2_args   = array(
				'class'            => 'wc-product-search',
				'id'               => 'vendor_id',
				'name'             => 'vendor_id',
				'data-placeholder' => __( 'Search for a vendor&hellip;', 'yith-woocommerce-product-vendors' ),
				'data-action'      => 'yith_json_search_vendors',
				'data-allow_clear' => true,
				'data-selected'    => array( $vendor_id => $vendor_display ),
				'data-multiple'    => false,
				'value'            => $vendor_id,
				'style'            => 'width: 180px;',
			);

			?>
			<div id="vendor_data_search" class="panel data_search_wrapper">
				<div class="options_group">
					<?php yit_add_select2_fields( $select2_args ); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Displays a dropdown for filtering items in the list table by month.
		 *
		 * @since 3.1.0
		 * @param string $post_type The post type.
		 * @global WP_Locale $wp_locale WordPress date and time locale object.
		 * @global wpdb      $wpdb      WordPress database abstraction object.
		 */
		protected function months_dropdown( $post_type ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			global $wpdb, $wp_locale;

			/**
			 * Filters whether to remove the 'Months' drop-down from the post list table.
			 *
			 * @since 4.2.0
			 * @param bool   $disable   Whether to disable the drop-down. Default false.
			 * @param string $post_type The post type.
			 */
			if ( apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
				return;
			}

			/**
			 * Filters to short-circuit performing the months dropdown query.
			 *
			 * @since 5.7.0
			 * @param object[]|false $months    'Months' drop-down results. Default false.
			 * @param string         $post_type The post type.
			 */
			$months = apply_filters( 'pre_months_dropdown_query', false, $post_type );

			if ( ! is_array( $months ) ) {
				$current_view = $this->get_current_view();
				$where        = 'WHERE 1=1 ';

				if ( 'all' !== $current_view ) {
					if ( is_array( $current_view ) ) {
						$where .= sprintf( 'AND status IN ( \'%s\' )', implode( "','", $current_view ) );
					} else {
						$where .= $wpdb->prepare( 'AND status = %s', $current_view );
					}
				}

				$months     = array();
				$start_date = $wpdb->get_var( "SELECT created_date FROM $wpdb->commissions $where ORDER BY created_date ASC LIMIT 1" );

				if ( $start_date ) {
					$y       = (int) date( 'Y' );
					$start_y = (int) date( 'Y', strtotime( $start_date ) );
					$start_m = (int) date( 'm', strtotime( $start_date ) );

					while ( $y >= $start_y ) {
						$m = ( (int) date( 'Y' ) === $y ) ? (int) date( 'm' ) : 12;
						while ( $m && ( $y !== $start_y || $m >= $start_m ) ) {
							$month                 = str_pad( (string) $m, 2, '0', STR_PAD_LEFT );
							$months[ $y . $month ] = sprintf( '%1$s %2$d', $wp_locale->get_month( $m ), $y );
							--$m;
						}
						--$y;
					}
				}
			}

			if ( empty( $months ) || 1 === count( $months ) ) {
				return;
			}

			$m = isset( $_GET['m'] ) ? absint( $_GET['m'] ) : 0;
			?>
			<label for="filter-by-date" class="screen-reader-text"></label>
			<select name="m" id="filter-by-date">
				<option<?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates', 'yith-woocommerce-product-vendors' ); ?></option>
				<?php
				foreach ( $months as $key => $value ) {
					printf(
						"<option %s value='%s'>%s</option>\n",
						selected( $m, $key, false ),
						esc_attr( $key ),
						esc_html( $value )
					);
				}
				?>
			</select>
			<?php
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Add WooCommerce Order Custom Status
		 *
		 * @since 1.0.0
		 * @param array $status Array of order statuses.
		 * @return array Array of columns of the table
		 */
		public function custom_order_status( $status ) {
			$status['trash'] = _x( 'Trashed', 'Order status', 'yith-woocommerce-product-vendors' );

			return $status;
		}
	}
}
