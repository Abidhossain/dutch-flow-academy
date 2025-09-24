<?php
/**
 * YITH Vendors List Table Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Vendors_List_Table' ) ) {

	class YITH_Vendors_Vendors_List_Table extends WP_List_Table {

		/**
		 * Constructor.
		 *
		 * @since  4.0.0
		 * @param array $args An associative array of arguments.
		 * @see    WP_List_Table::__construct() for more information on default arguments.
		 */
		public function __construct( $args = array() ) {
			parent::__construct(
				array(
					'plural'   => 'vendors',
					'singular' => 'vendor',
					'ajax'     => false,
					'screen'   => 'yith-plugins_page_yith-wcmv-vendors-list',
				)
			);

			add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
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
			$hidden = array_merge( $hidden, array( 'id' ) );
			return $hidden;
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
		 * Prepares the list of items for displaying.
		 *
		 * @since  4.0.0
		 * @return void
		 * @uses   WP_List_Table::set_pagination_args()
		 */
		public function prepare_items() {
			// phpcs:disable WordPress.Security.NonceVerification

			// Let's build the tax query args.
			$page = $this->get_pagenum();
			$args = array(
				'taxonomy'   => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
				'page'       => $page,
				'offset'     => ( $page - 1 ) * 20,
				'number'     => 20,
				'hide_empty' => 0,
				'search'     => '',
				'fields'     => 'ids',
			);

			$search = ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			if ( $search ) {
				$args['search'] = $search;
			}

			// Listen for status filter.
			$status = ! empty( $_GET['vendor_status'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['vendor_status'] ) ) ) : '';
			if ( array_key_exists( $status, yith_wcmv_get_vendor_statuses() ) ) {
				$args['meta_query'] = array( //phpcs:ignore
					array(
						'key'   => 'status',
						'value' => $status,
					),
				);
			}

			// Handle search by ID.
			if ( is_numeric( $args['search'] ) ) {
				$term        = get_term( absint( $args['search'] ) );
				$this->items = $term instanceof WP_Term ? array( $term->term_id ) : array();
				$this->set_pagination_args(
					array(
						'total_items' => count( $this->items ),
						'per_page'    => 20,
					)
				);

			} else {
				if ( ! empty( $_GET['orderby'] ) ) {
					$orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
					if ( in_array( $orderby, array( 'term_id', 'name', 'slug' ), true ) ) {
						$args['orderby'] = $orderby;
					} else {
						$args['meta_key'] = $orderby; // phpcs:ignore
						$args['orderby']  = 'meta_value';
					}
				}

				if ( ! empty( $_GET['order'] ) ) {
					$args['order'] = sanitize_text_field( wp_unslash( $_GET['order'] ) );
				}

				$this->items = apply_filters( 'yith_wcmv_get_terms_vendors_list', get_terms( $args ), $args );
				$this->set_pagination_args(
					array(
						'total_items' => wp_count_terms(
							array(
								'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
								'search'   => $search,
							)
						),
						'per_page'    => 20,
					)
				);
			}

			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Message to be displayed when there are no items
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function no_items() {
			echo '';
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @since  4.0.0
		 * @return array Array of available actions.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete_vendor' => __( 'Delete', 'yith-woocommerce-product-vendors' ),
			);

			return apply_filters( 'yith_wcmv_vendors_list_table_bulk_actions', $actions );
		}

		/**
		 * Display the search box.
		 *
		 * @since  3.1.0
		 * @access public
		 * @param string $text     The search button text.
		 * @param string $input_id The search input id.
		 */
		public function add_search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );
		}

		/**
		 * Gets a list of columns.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_columns() {
			return apply_filters(
				'yith_wcmv_vendors_list_table_columns',
				array(
					'cb'              => '<input type="checkbox" />',
					'name'            => _x( 'Vendor', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'owner'           => _x( 'Owner', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'registered'      => _x( 'Registered on', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'policies'        => _x( 'Policies & VAT', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'commission_rate' => _x( 'Commission', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'items'           => _x( 'Products', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'status'          => _x( 'Status', '[Admin] Vendors lists: column title', 'yith-woocommerce-product-vendors' ),
					'actions'         => '',
				)
			);
		}

		/**
		 * Gets a list of sortable columns.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_sortable_columns() {
			return apply_filters(
				'yith_wcmv_vendors_list_table_sortable_columns',
				array(
					'commission_rate' => 'commission',
					'name'            => 'name',
					'registered'      => 'registration_date',
				)
			);
		}

		/**
		 * Gets the name of the primary column.
		 *
		 * @since 4.3.0
		 * @return string The name of the primary column.
		 */
		protected function get_primary_column_name() {
			return 'name';
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since 4.0.0
		 * @param object|array $vendor_id The current vendor ID.
		 */
		public function single_row( $vendor_id ) {
			$vendor = yith_wcmv_get_vendor( $vendor_id );
			$class  = $vendor->has_status( 'pending' ) ? 'pending' : '';

			echo '<tr class="' . esc_attr( $class ) . '">';
			$this->single_row_columns( $vendor );
			echo '</tr>';
		}

		/**
		 * Prints column cb
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string
		 */
		public function column_cb( $vendor ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['plural'], // Let's simply repurpose the table's plural label.
				$vendor->get_id() // The value of the checkbox should be the record's id.
			);
		}

		/**
		 * Output column name content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_name( $vendor ) {
			$html = sprintf( '<a href="javascript:void(0)" class="edit-vendor" data-vendor_id="%1$d">%2$s</a><br><small>ID: #%1$d</small>', absint( $vendor->get_id() ), esc_html( $vendor->get_name() ) );

			return apply_filters( 'yith_wcmv_vendors_list_table_name_column', $html, $vendor );
		}

		/**
		 * Output column owner content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_owner( $vendor ) {
			$owner = $vendor->get_owner( 'all' );

			if ( $owner instanceof WP_User ) {
				// Define row actions.
				$actions = apply_filters(
					'yith_wcmv_vendors_list_table_owner_row_actions',
					array(
						'edit' => sprintf(
							'<a href="%s" target="_blank">%s</a>',
							esc_url( add_query_arg( array( 'user_id' => $owner->ID ), admin_url( 'user-edit.php' ) ) ),
							esc_html_x( 'Edit', '[Admin]Vendor owner action label', 'yith-woocommerce-product-vendors' )
						),
					)
				);

				if ( class_exists( 'user_switching' ) ) {
					global $user_switching;
					if ( ! empty( $user_switching ) && $user_switching instanceof user_switching ) {
						$actions = $user_switching->filter_user_row_actions( $actions, $owner );
					}
				}

				$html = $owner->display_name . sprintf( '<br><a href="mailto:%1$1s">%1$1s</a>', $owner->user_email );
				if ( ! empty( $actions ) ) {
					$html .= '<div class="row-actions">' . implode( ' | ', $actions ) . '</div>';
				}
			} else {
				$html = '-';
			}

			return apply_filters( 'yith_wcmv_vendors_list_table_owner_column', $html, $vendor );
		}

		/**
		 * Output column registered date content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_registered( $vendor ) {
			return apply_filters( 'yith_wcmv_vendors_list_table_registered_column', yith_wcmv_get_formatted_date_html( $vendor->get_registration_date() ), $vendor );
		}

		/**
		 * Output column commission rate content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_commission_rate( $vendor ) {
			return apply_filters( 'yith_wcmv_vendors_list_table_rate_column', ( $vendor->get_commission() * 100 ) . '%', $vendor );
		}

		/**
		 * Output column products count content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_items( $vendor ) {
			return apply_filters( 'yith_wcmv_vendors_list_table_items_column', $vendor->get_product_count(), $vendor );
		}

		/**
		 * Output column status content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_status( $vendor ) {
			$statuses = yith_wcmv_get_vendor_statuses();
			$html     = sprintf( '<span class="status-badge %s">%s</span>', $vendor->get_status(), $statuses[ $vendor->get_status() ] ?? array_shift( $statuses ) );

			if ( $vendor->has_status( 'pending' ) ) {

				$html .= yith_plugin_fw_get_component(
					array(
						'type'   => 'action-button',
						'action' => 'approve',
						'class'  => 'approve-vendor',
						'title'  => _x( 'Approve', 'Vendor status action', 'yith-woocommerce-product-vendors' ),
						'url'    => '#',
						'icon'   => 'check-alt',
						'data'   => array( 'vendor_id' => $vendor->get_id() ),
					),
					false
				);

				$html .= yith_plugin_fw_get_component(
					array(
						'type'   => 'action-button',
						'action' => 'reject',
						'class'  => 'reject-vendor',
						'title'  => _x( 'Reject', 'Vendor status action', 'yith-woocommerce-product-vendors' ),
						'url'    => '#',
						'icon'   => 'close-alt',
						'data'   => array( 'vendor_id' => $vendor->get_id() ),
					),
					false
				);
			}

			return apply_filters( 'yith_wcmv_vendors_list_table_status_column', $html, $vendor );
		}

		/**
		 * Output column polices content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_policies( $vendor ) {

			$polices = array(
				array(
					'label' => __( 'Policy', 'yith-woocommerce-product-vendors' ),
					'value' => $vendor->has_privacy_policy_accepted(),
				),
				array(
					'label' => __( 'Terms & Conditions', 'yith-woocommerce-product-vendors' ),
					'value' => $vendor->has_terms_and_conditions_accepted(),
				),
				array(
					'label' => __( 'VAT', 'yith-woocommerce-product-vendors' ),
					'value' => ! empty( $vendor->get_meta( 'vat' ) ),
				),
			);

			$html = '<ul class="vendor-polices-container">';
			foreach ( $polices as $policy ) {
				$html .= '<li><strong>' . esc_html( $policy['label'] ) . ':</strong>';
				$html .= '<i class="yith-icon ' . ( $policy['value'] ? 'yith-icon-check-circle' : 'yith-icon-warning-triangle' ) . '"></i></li>';
			}
			$html .= '</ul>';

			return apply_filters( 'yith_wcmv_vendors_list_table_policies_column', $html, $vendor );
		}

		/**
		 * Output column actions content
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return string Column content
		 */
		public function column_actions( $vendor ) {

			$html = yith_plugin_fw_get_component(
				array(
					'type'   => 'action-button',
					'class'  => 'edit-vendor',
					'action' => 'edit',
					'title'  => __( 'Edit Vendor', 'yith-woocommerce-product-vendors' ),
					'data'   => array(
						'vendor_id' => $vendor->get_id(),
					),
					'icon'   => 'pencil',
					'url'    => '#',
				),
				false
			);

			if ( $vendor->has_status( 'enabled' ) ) {
				$html .= yith_plugin_fw_get_component(
					array(
						'type'  => 'action-button',
						'class' => 'view-vendor',
						'title' => __( 'View Vendor', 'yith-woocommerce-product-vendors' ),
						'icon'  => 'eye',
						'url'   => $vendor->get_url(),
					),
					false
				);
			}

			$html .= yith_plugin_fw_get_component(
				array(
					'type'         => 'action-button',
					'action'       => 'delete',
					'title'        => __( 'Delete Vendor', 'yith-woocommerce-product-vendors' ),
					'class'        => 'delete-vendor',
					'icon'         => 'trash',
					'url'          => add_query_arg(
						array(
							'action'   => 'delete_vendor',
							'_wpnonce' => wp_create_nonce( 'bulk-vendors' ),
							'vendors'  => $vendor->get_id(),
						),
						YITH_Vendors_Admin_Vendors::get_current_vendors_list_table_url()
					),
					'confirm_data' => array(
						'title'               => _x( 'Delete vendor', '[Admin]Vendor delete modal title', 'yith-woocommerce-product-vendors' ),
						// translators: %s is the vendor name.
						'message'             => sprintf( _x( 'Are you sure you want to delete vendor %s?', '[Admin]Vendor delete modal message. %s placeholder for vendor name', 'yith-woocommerce-product-vendors' ), '<b>' . $vendor->get_name() . '</b>' ),
						'confirm-button'      => _x( 'Delete', '[Admin]Vendor delete modal button label', 'yith-woocommerce-product-vendors' ),
						'confirm-button-type' => 'delete',
					),
				),
				false
			);

			return apply_filters( 'yith_wcmv_vendors_list_table_actions_column', $html, $vendor );
		}

		/**
		 * Return default column content
		 * Let third party use this custom hook to output custom column content.
		 *
		 * @since  5.0.0
		 * @param YITH_Vendor $vendor      Vendor object.
		 * @param string      $column_name Name of the column.
		 * @return void
		 */
		protected function column_default( $vendor, $column_name ) {
			do_action( "yith_wcmv_vendors_list_table_col_{$column_name}", $vendor, $column_name );
		}

		/**
		 * Table views
		 *
		 * @since  5.0.0
		 */
		protected function get_views() {

			if ( ! $this->has_items() ) {
				return array();
			}

			$views        = array_merge( array( 'all' => __( 'All', 'yith-woocommerce-product-vendors' ) ), yith_wcmv_get_vendor_statuses() );
			$current_view = $this->get_current_view();

			foreach ( $views as $id => $view ) {
				// Let's filter views query args.
				$args  = apply_filters( 'yith_wcmv_vendors_list_table_views_args', 'all' === $id ? array() : array( 'status' => $id ) );
				$count = YITH_Vendors_Factory::count( $args );

				// Build the navigation item.
				$href         = esc_url( add_query_arg( 'vendor_status', $id ) );
				$views[ $id ] = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $id === $current_view ? 'current' : '', $view, $count );
			}

			return $views;
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  5.0.0
		 * @return string
		 */
		public function get_current_view() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			return isset( $_GET['vendor_status'] ) ? sanitize_text_field( wp_unslash( $_GET['vendor_status'] ) ) : 'all';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Determines whether the table has items to display or not
		 *
		 * @since 3.1.0
		 *
		 * @return bool
		 */
		public function has_items() {
			static $has_items;

			if ( is_null( $has_items ) ) {
				$has_items = wp_count_terms( array( 'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME ) );
			}

			return ! empty( $has_items ) && ! is_wp_error( $has_items );
		}
	}
}
