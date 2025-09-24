<?php
/**
 * YITH Vendors Admin Orders Class. Handle orders admin side.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Commissions' ) ) {
	/**
	 * Vendor admin commissions handler class
	 */
	class YITH_Vendors_Admin_Commissions {

		/**
		 * The list table class instance.
		 *
		 * @since 4.0.0
		 * @var object|null
		 */
		protected $list_table_class = null;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'preload_list_table_class' ) );
			// Admin templates.
			add_action( 'yith_wcmv_commissions_admin_list_table', array( $this, 'commissions_list_table' ) );
			add_action( 'yith_wcmv_commissions_admin_gateways', 'YITH_Vendors_Gateways::show_gateways_list' );
			// Customize commission table for limited access vendor.
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_commissions_table_customize' ) );
			// Handle commission actions.
			add_action( 'admin_init', array( $this, 'handle_table_actions' ), 20 );
		}

		/**
		 * Preload list table class. This is useful for screen reader options.
		 *
		 * @since 4.0.0
		 */
		public function preload_list_table_class() {
			if ( empty( $this->list_table_class ) && ( ! isset( $_GET['sub_tab'] ) || 'commissions-list' === sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// First load table class.
				$class = 'YITH_Vendors_Commissions_List_Table';
				// Check for premium version.
				if ( class_exists( $class . '_Premium' ) ) {
					$class .= '_Premium';
				}
				$class                  = apply_filters( 'yith_wcmv_commissions_list_table_class', $class );
				$this->list_table_class = new $class();
				// Overwrite current screen.
				set_current_screen( 'yith-plugins_page_yith-wcmv-commissions-list' );
				add_filter( 'yith_plugin_fw_wc_panel_screen_ids_for_assets', array( $this, 'register_list_table_screen_id_for_assets' ) );
			}
		}

		/**
		 * Register list table screen ID for assets
		 *
		 * @since  4.0.0
		 * @param array $screen_ids An array of screen ids registered.
		 * @return array
		 */
		public function register_list_table_screen_id_for_assets( $screen_ids ) {
			$screen_ids[] = 'yith-plugins_page_yith-wcmv-commissions-list';
			return $screen_ids;
		}


		/**
		 * Print commissions list table
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function commissions_list_table() {

			if ( empty( $this->list_table_class ) ) {
				return;
			}

			$this->list_table_class->prepare_items();
			$args = apply_filters(
				'yith_vendors_commissions_template',
				array(
					'commissions_table' => $this->list_table_class,
					'page_title'        => sprintf( '%s %s', YITH_Vendors_Taxonomy::get_singular_label( 'ucfirst' ), _x( 'Commissions', '[Part of] Vendor Commissions', 'yith-woocommerce-product-vendors' ) ),
				)
			);

			yith_wcmv_include_admin_template( 'commissions-list-table', $args );
			// Include also commission details template.
			yith_wcmv_include_admin_template( 'commission-details' );
		}

		/**
		 * Get commission details data array
		 *
		 * @since  4.0.0
		 * @param YITH_Vendors_Commission $commission THe commission object.
		 * @return array
		 */
		public static function get_commission_details( $commission ) {

			$user = $commission->get_user();
			// Let's start build the data array.
			$data = array(
				'id'                 => $commission->get_id(),
				'type'               => $commission->get_type(),
				'rate'               => $commission->get_rate( 'display' ),
				'amount'             => $commission->get_amount( 'display' ),
				'refunded'           => $commission->get_amount_refunded( 'display' ),
				'to_pay'             => $commission->get_amount_to_pay( 'display' ),
				'user'               => $user ? yith_wcmv_get_formatted_user_html( $user ) : $commission->get_user_id(),
				'order'              => $commission->get_formatted_order_uri(),
				'date'               => yith_wcmv_get_formatted_date_html( $commission->get_date( 'edit' ) ),
				'last_edit'          => yith_wcmv_get_formatted_date_html( $commission->get_last_edit() ),
				'status_html'        => self::get_commission_details_status_html( $commission ),
				'vendor'             => '',
				'vendor_owner'       => '',
				'vendor_owner_email' => '',
				'vendor_paypal'      => '',
				'vendor_bank'        => '',
				'item_image'         => '',
				'item_name'          => '',
				'item_price'         => '',
				'notes_html'         => self::get_commission_details_notes_html( $commission ),
			);

			$vendor = $commission->get_vendor();
			if ( $vendor && $vendor->is_valid() ) {
				$data['vendor']        = $vendor->get_name();
				$data['vendor_paypal'] = $vendor->get_meta( 'paypal_email' );
				$data['vendor_bank']   = $vendor->get_meta( 'bank_account' );

				$owner = $vendor->get_owner( 'all' );
				if ( $owner ) {
					$data['vendor_owner']       = $owner->display_name;
					$data['vendor_owner_email'] = $owner->user_email;
				}
			}

			// Process item.
			$item = $commission->get_item();
			if ( $item ) {
				if ( 'shipping' === $commission->get_type() ) {
					$data['item_image'] = wc_placeholder_img( 'shop_thumbnail' );
					$data['item_name']  = sprintf( '%s - %s', esc_html_x( 'Shipping fee', '[Admin Commission Detail]Detail label', 'yith-woocommerce-product-vendors' ), $item->get_name() );
					$data['item_price'] = wc_price( $item->get_total(), array( 'currency' => $commission->get_currency() ) );
				} else {
					$product           = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
					$product_uri       = $product ? apply_filters( 'yith_wcmv_commissions_list_table_product_url', admin_url( 'post.php?post=' . absint( $product->get_id() ) . '&action=edit' ), $product->get_id() ) : '';
					$product_name      = $product ? $product->get_name() : $item->get_name();
					$data['item_name'] = esc_html( $product_name );

					if ( $product_uri ) {
						$data['item_name'] = '<a target="_blank" href="' . esc_url( $product_uri ) . '"><strong>' . $data['item_name'] . '</strong></a>';
					}
					// Append quantity.
					$data['item_name'] .= ' x ' . $item->get_quantity();
					$data['item_image'] = $product ? $product->get_image( 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' );
					$data['item_price'] = wc_price( $item->get_total(), array( 'currency' => $commission->get_currency() ) );
				}
			}

			return apply_filters( 'yith_wcmv_get_commission_details', $data, $commission );
		}

		/**
		 * Get commission details status html
		 *
		 * @since  4.0.0
		 * @param YITH_Vendors_Commission $commission The commission object.
		 * @return string
		 */
		public static function get_commission_details_status_html( $commission ) {
			// Statuses array. Build an array of possible status change.
			$status      = $commission->get_status();
			$status_html = $commission->get_status( 'display' );
			if ( user_can( get_current_user_id(), 'manage_woocommerce' ) ) {
				$statuses = array();
				foreach ( yith_wcmv_get_commission_statuses( true ) as $key => $label ) {
					// Check if status change is permitted but also include the current status.
					if ( ! YITH_Vendors()->commissions->is_status_changing_permitted( $key, $status ) && $key !== $status ) {
						continue;
					}

					$statuses[ $key ] = $label;
				}

				if ( ! empty( $statuses ) ) {
					$status_html = '<select id="commission-status" name="commission-status">';
					foreach ( $statuses as $key => $value ) {
						$status_html .= '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $status, false ) . '>' . esc_html( $value ) . '</option>';
					}
					$status_html .= '</select>';
				}
			}

			return $status_html;
		}

		/**
		 * Get commission details notes html
		 *
		 * @since  4.0.0
		 * @param YITH_Vendors_Commission $commission The commission object.
		 * @return string
		 */
		public static function get_commission_details_notes_html( $commission ) {
			$notes_html = '';
			$notes      = $commission->get_notes();

			if ( ! empty( $notes ) ) {
				foreach ( $notes as $note ) {
					// translators: %1$s is a date string, %2$s is a time string.
					$formatted_date = sprintf( esc_html__( 'added on %1$s at %2$s', 'yith-woocommerce-product-vendors' ), date_i18n( wc_date_format(), strtotime( $note->note_date ) ), date_i18n( wc_time_format(), strtotime( $note->note_date ) ) );
					$notes_html    .= '<li rel="' . $note->ID . '" class="note">';
					$notes_html    .= '<div>' . wp_kses_post( $note->description ) . '</div>';
					$notes_html    .= '<abbr class="exact-date" title="' . $note->note_date . '">' . $formatted_date . '</abbr>';
					$notes_html    .= '</li>';
				}
			}

			return $notes_html;
		}

		/**
		 * Get commission list table base url
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public static function get_commissions_list_table_url() {
			$args = array(
				'page'    => YITH_Vendors_Admin::PANEL_PAGE,
				'tab'     => 'commissions',
				'sub_tab' => 'commissions-list',
			);

			return apply_filters( 'yith_wcmv_get_commissions_list_table_url', add_query_arg( $args, admin_url( 'admin.php' ) ), $args );
		}

		/**
		 * Get commission list table current url including filters.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public static function get_current_commissions_list_table_url() {
			// phpcs:disable WordPress.Security.NonceVerification
			// Optional parameters.
			$args   = array();
			$params = array( 'commission_status', 'paged', 'order', 'orderby', 's', 'm', 'product_id', 'vendor_id' );
			foreach ( $params as $key ) {
				if ( ! empty( $_GET[ $key ] ) ) {
					$args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
				}
			}

			return apply_filters( 'yith_wcmv_get_current_commissions_list_table_url', add_query_arg( $args, self::get_commissions_list_table_url() ), $args );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Get commission action url
		 *
		 * @since  4.0.0
		 * @param integer $commission_id The commission ID to save.
		 * @param string  $action        The commission action to set.
		 * @param array   $args          (Optional) An array of additional url args.
		 * @return string
		 */
		public static function get_commission_action_url( $commission_id, $action, $args = array() ) {

			$args = array_merge(
				array(
					'action'      => $action,
					'_wpnonce'    => wp_create_nonce( 'bulk-commissions' ),
					'commissions' => $commission_id,
				),
				$args
			);

			return apply_filters( 'yith_wcmv_get_commission_action_url', add_query_arg( $args, self::get_current_commissions_list_table_url() ), $args );
		}

		/**
		 * Get current table bulk action
		 *
		 * @since  4.0.0
		 * @return string
		 */
		protected function get_current_action() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_GET['action'] ) && '-1' !== $_GET['action'] ) {
				$action = sanitize_text_field( wp_unslash( $_GET['action'] ) );
			} elseif ( ! empty( $_GET['action2'] ) && '-1' !== $_GET['action2'] ) {
				$action = sanitize_text_field( wp_unslash( $_GET['action2'] ) );
			} elseif ( ! empty( $_GET['export_commissions_csv'] ) ) {
				$action = 'export_commissions_csv';
			} else {
				$action = '';
			}

			// Validate action based on current user. Some actions are available only for shop manager.
			if ( in_array( $action, array( 'change_commissions_status', 'delete_commissions' ), true ) && ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You do not have the permission to perform this action!', 'yith-woocommerce-product-vendors' ) );
			}

			return $action;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle commissions table bulk action
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_table_actions() {

			// phpcs:disable WordPress.Security.NonceVerification
			$action = $this->get_current_action();
			if ( empty( $action ) || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-commissions' ) ) {
				return;
			}

			$commissions   = ! empty( $_GET['commissions'] ) ? array_map( 'absint', (array) $_GET['commissions'] ) : array();
			$redirect      = self::get_current_commissions_list_table_url();
			$redirect_args = array();

			$method_args = array(
				$commissions,
				&$redirect_args,
			);

			// Handle special bulk action.
			if ( false !== strpos( $action, 'commissions_status_change_to_' ) ) {
				$method_args[] = str_replace( 'commissions_status_change_to_', '', $action );
				$action        = 'change_commissions_status';
			}

			$method = 'handle_' . $action;
			if ( method_exists( $this, $method ) ) {
				$this->$method( ...$method_args );
			} else {

				// Check for deprecated action.
				if ( has_action( "admin_action_pay_commissions_{$action}" ) ) {
					$vendor = yith_wcmv_get_vendor( 'current', 'user' );
					do_action( "admin_action_pay_commissions_{$action}", $vendor, $commissions, $action );
				}

				do_action_ref_array( "yith_wcmv_commissions_table_action_{$action}", $method_args );
			}

			wp_safe_redirect( add_query_arg( $redirect_args, $redirect ) );
			exit;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle change commission status table action
		 *
		 * @since  4.0.0
		 * @param array  $commissions   An array of commissions to export.
		 * @param array  $redirect_args Redirect url arguments.
		 * @param string $status        The new status to set for commissions.
		 */
		protected function handle_change_commissions_status( $commissions, &$redirect_args, $status = '' ) {

			if ( empty( $commissions ) ) {
				YITH_Vendors_Admin_Notices::add( __( 'Please, select at least one commission.', 'yith-woocommerce-product-vendors' ), 'error' );
				return;
			}

			$send_bulk_email = apply_filters( 'yith_wcmv_send_commissions_email_on_bulk_action', true );
			// Disable default email.
			$send_bulk_email && add_filter( 'yith_wcmv_send_commission_paid_email', '__return_false' );

			if ( empty( $status ) ) {
				$status = ! empty( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			}

			$errors = array();
			if ( ! empty( $status ) ) {
				$commissions_by_vendor = array();
				foreach ( $commissions as $commission_id ) {
					$commission = yith_wcmv_get_commission( absint( $commission_id ) );
					if ( ! $commission || $status === $commission->get_status() ) {
						continue;
					}

					if ( $commission->update_status( $status ) ) {
						$vendor_id                             = $commission->get_vendor_id();
						$commissions_by_vendor[ $vendor_id ][] = $commission;
					} else {
						$errors[] = $commission->get_id();
					}
				}

				if ( $send_bulk_email ) {
					WC()->mailer();
					foreach ( $commissions_by_vendor as $vendor_id => $comm ) {
						do_action( 'yith_vendors_commissions_bulk_action', $comm, $vendor_id, $status );
					}
				}
			}

			if ( ! empty( $errors ) ) {
				$errors = implode( ', #', $errors );
				// translators: %s could be a single commission id or a list of ids comma separated.
				YITH_Vendors_Admin_Notices::add( sprintf( _n( 'Error changing status for commission <b>#%s</b>.', 'Error changing status for commissions <b>#%s</b>.', count( $commissions ), 'yith-woocommerce-product-vendors' ), $errors ), 'error' );
			} else {
				YITH_Vendors_Admin_Notices::add( _n( 'Commission status changed.', 'Commissions status changed.', count( $commissions ), 'yith-woocommerce-product-vendors' ) );
			}
		}

		/**
		 * Handle delete commissions table action
		 *
		 * @since  4.0.0
		 * @param array $commissions   An array of commissions to export.
		 * @param array $redirect_args Redirect url arguments.
		 */
		protected function handle_delete_commissions( $commissions, &$redirect_args ) {

			if ( empty( $commissions ) ) {
				YITH_Vendors_Admin_Notices::add( __( 'Please, select at least one commission.', 'yith-woocommerce-product-vendors' ), 'error' );
				return;
			}

			$errors = array();
			foreach ( $commissions as $commission_id ) {
				$res = YITH_Vendors_Commissions_Factory::delete( $commission_id );
				if ( is_wp_error( $res ) ) {
					$errors[] = $commission_id;
				}
			}

			if ( ! empty( $errors ) ) {
				$errors = implode( ', #', $errors );
				// translators: %s could be a single commission id or a list of ids comma separated.
				YITH_Vendors_Admin_Notices::add( sprintf( _n( 'Error deleting commission <b>#%s</b>.', 'Error deleting commissions <b>#%s</b>.', count( $commissions ), 'yith-woocommerce-product-vendors' ), $errors ), 'error' );
			} else {
				YITH_Vendors_Admin_Notices::add( _n( 'Commission deleted.', 'Commissions deleted.', count( $commissions ), 'yith-woocommerce-product-vendors' ) );
			}
		}

		/**
		 * Maybe handle commissions CSV export. Both dedicated button and bulk actions.
		 *
		 * @since  4.0.0
		 * @param array $commissions   An array of commissions to export.
		 * @param array $redirect_args Redirect url arguments.
		 * @return void
		 */
		protected function handle_export_commissions_csv( $commissions, &$redirect_args ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( empty( $commissions ) ) {
				// If commissions array is empty, maybe the dedicated button was clicked, handle it!
				$args = apply_filters(
					'yith_wcmv_commissions_export_csv_query_args',
					array(
						'number'     => 0,
						'status'     => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
						'product_id' => isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0,
						'vendor_id'  => isset( $_GET['vendor_id'] ) ? absint( $_GET['vendor_id'] ) : 0,
						'm'          => isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : '',
						's'          => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
					)
				);

				$commissions = yith_wcmv_get_commissions( $args );
			}

			if ( empty( $commissions ) ) {
				YITH_Vendors_Admin_Notices::add( __( 'Commissions export error.', 'yith-woocommerce-product-vendors' ), 'error' );
			} else {
				$this->export_csv_commissions( $commissions );
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Export given commissions to CVS
		 *
		 * @since  1.0.0
		 * @param array $commissions An array of commissions to export.
		 * @return void
		 */
		private function export_csv_commissions( $commissions ) {

			$csv_array = array();

			$fields = apply_filters(
				'yith_wcmv_commissions_export_csv_columns',
				array(
					'ID',
					'vendor_id',
					'vendor',
					'order_id',
					'type',
					'product_id',
					'product',
					'currency',
					'rate',
					'amount',
					'amount_refunded',
					'to_pay',
					'status',
					'last_edit',
				)
			);

			// First add CSV fields header.
			$csv_array[] = $fields;

			foreach ( $commissions as $commission_id ) {
				$commission = yith_wcmv_get_commission( $commission_id );
				if ( ! $commission ) {
					continue;
				}

				$t = array();
				foreach ( $fields as $key ) {
					$v = '';
					switch ( $key ) {
						case 'ID':
							$v = $commission->get_id();
							break;

						case 'vendor_id':
							$v = $commission->get_vendor_id();
							break;

						case 'vendor':
							$vendor = $commission->get_vendor();
							$v      = ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() : '-';
							break;

						case 'order_id':
							$v = $commission->get_order_id();
							break;

						case 'type':
							$v = $commission->get_type();
							break;

						case 'product_id':
							$product = $commission->get_product();
							$v       = $product instanceof WC_Product ? $product->get_id() : 0;
							break;

						case 'product':
							$item = $commission->get_item();
							$v    = $item instanceof WC_Order_Item ? $item->get_name() : '-';
							break;

						case 'rate':
							$v = $commission->get_rate( 'display' );
							break;

						case 'currency':
							$v = $commission->get_currency();
							break;

						case 'amount':
							$v = wc_format_decimal( $commission->get_amount( 'edit' ) );
							break;

						case 'amount_refunded':
							$v = wc_format_decimal( $commission->get_amount_refunded( 'edit' ) );
							break;

						case 'to_pay':
							$v = wc_format_decimal( $commission->get_amount_to_pay( 'edit' ) );
							break;

						case 'status':
							$v = $commission->get_status();
							break;

						case 'last_edit':
							$v = $commission->get_date();
							break;
					}

					$t[] = apply_filters( 'yith_wcmv_commissions_export_csv_column_value', $v, $key, $commission );
				}
				$csv_array[] = $t;
			}

			// Download CSV.
			$this->array_to_csv_download( $csv_array );
		}

		/**
		 * Transform an array to CSV file
		 *
		 * @since  4.0.0
		 * @param array $data The data to write on CSV file.
		 * @return void
		 */
		private function array_to_csv_download( $data ) {

			$filename  = apply_filters( 'yith_wcmv_commissions_export_csv_filename', 'commissions-export.csv' );
			$delimiter = apply_filters( 'yith_wcmv_commissions_export_csv_delimiter', ',' );

			header( 'X-Robots-Tag: noindex, nofollow', true );
			header( 'Content-Type: application/csv' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
			$f = fopen( 'php://output', 'w' );

			foreach ( $data as $line ) {
				fputcsv( $f, $line, $delimiter );
			}
			exit;
		}

		/**
		 * Customize commissions table columns and features if a simple vendor is logged in
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function vendor_commissions_table_customize() {
			add_filter( 'yith_wcmv_commissions_list_table_column', array( $this, 'vendor_commissions_table_columns' ), 10, 1 );
			add_filter( 'yith_wcmv_commissions_list_table_args', array( $this, 'vendor_filter_table_query' ), 10, 1 );
			add_filter( 'yith_wcmv_commissions_list_table_views_args', array( $this, 'vendor_filter_table_query' ), 10, 1 );
			add_filter( 'yith_wcmv_commissions_export_csv_query_args', array( $this, 'vendor_filter_table_query' ), 10, 1 );
			// Remove customer detail from commissions table.
			add_action( 'yith_wcmv_commissions_list_table_order_id_column', array( $this, 'maybe_remove_customer_details' ), 1, 2 );
			// Remove single commission actions.
			add_filter( 'yith_wcmv_commissions_list_table_add_single_actions', '__return_false' );
			// Remove bulk actions.
			add_filter( 'yith_wcmv_commissions_list_table_bulk_actions', array( $this, 'vendor_filter_bulk_actions' ) );
			// Remove vendor filter.
			add_filter( 'yith_wcmv_commissions_list_table_show_vendor_filter', '__return_false' );
		}

		/**
		 * Maybe remove customer data from commissions table if visibility options is disabled.
		 *
		 * @since  4.0.0
		 * @auhtor YITH
		 * @param string                  $html Current html value.
		 * @param YITH_Vendors_Commission $commission Current commission.
		 * @return string
		 */
		public function maybe_remove_customer_details( $html, $commission ) {
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_show_customer', 'no' ) ) {
				return $html;
			}

			return $commission->get_formatted_order_uri();
		}

		/**
		 * Filter commissions table bulk actions
		 *
		 * @since  4.0.0
		 * @param array $actions An array of bulk actions.
		 * @return array
		 */
		public function vendor_filter_bulk_actions( $actions ) {
			return array_intersect_key( $actions, array( 'export_commissions_csv' => '' ) );
		}

		/**
		 * Remove commissions table columns for limited access vendor
		 *
		 * @since  4.0.0
		 * @param array $columns The table list columns.
		 * @return array
		 */
		public function vendor_commissions_table_columns( $columns ) {
			$to_remove = array( 'vendor', 'payment_methods' );
			foreach ( $to_remove as $remove ) {
				unset( $columns[ $remove ] );
			}

			return $columns;
		}

		/**
		 * Filter table content based on current logged in vendor
		 *
		 * @since  4.0.0
		 * @param array $args The query arguments.
		 * @return array
		 */
		public function vendor_filter_table_query( $args ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor ) {
				$args['vendor_id'] = $vendor->get_id();
			}

			return $args;
		}
	}
}
