<?php
/**
 * YITH Vendors Admin Vendors Tab Helper Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Vendors' ) ) {
	/**
	 * Class YITH_Vendors_Admin_Vendors
	 */
	class YITH_Vendors_Admin_Vendors {

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
			// Panel admin custom tabs.
			add_action( 'yith_wcmv_vendors_admin_list_table', array( $this, 'list_table_tab' ), 10 );
			// Init handle submit vendor modal.
			add_action( 'init', array( $this, 'handle_create_vendor_submit' ), 20 );
			add_action( 'init', array( $this, 'handle_edit_vendor_submit' ), 20 );
			// Handle commission actions.
			add_action( 'admin_init', array( $this, 'handle_table_actions' ), 20 );
		}

		/**
		 * Preload list table class. This is useful for screen reader options.
		 *
		 * @since 4.0.0
		 */
		public function preload_list_table_class() {
			if ( empty( $this->list_table_class ) && ( ! isset( $_GET['sub_tab'] ) || 'vendors-list' === sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// First load class.
				$class                  = 'YITH_Vendors_Vendors_List_Table';
				$class                  = apply_filters( 'yith_wcmv_vendors_list_table_class', $class );
				$this->list_table_class = new $class();
				// Overwrite current screen.
				set_current_screen( 'yith-plugins_page_yith-wcmv-vendors-list' );
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
			$screen_ids[] = 'yith-plugins_page_yith-wcmv-vendors-list';
			return $screen_ids;
		}

		/**
		 * Print admin vendors list table
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function list_table_tab() {

			$pagenum = $this->list_table_class->get_pagenum();

			$this->list_table_class->prepare_items();
			$total_pages = $this->list_table_class->get_pagination_arg( 'total_pages' );

			if ( $pagenum > $total_pages && $total_pages > 0 ) {
				wp_safe_redirect( add_query_arg( 'paged', $total_pages ) );
				exit;
			}

			$args = apply_filters(
				'yith_vendors_list_table_template',
				array(
					'vendors_table' => $this->list_table_class,
				)
			);

			yith_wcmv_include_admin_template( 'vendors-list-table', $args );
			// Include also vendor crete/edit modal template.
			yith_wcmv_include_admin_template( 'vendor-modal' );
			// Include pending reject vendor modal.
			yith_wcmv_include_admin_template( 'vendor-reject-modal' );
		}

		/**
		 * Handle create vendor modal submit
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_create_vendor_submit() {
			if ( ! isset( $_POST['_vendor_modal_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_vendor_modal_nonce'] ) ), 'yith_wcmv_create_vendor' ) ) {
				return;
			}

			$fields = YITH_Vendors_Factory::get_fields( true );
			// Get posted data.
			$posted    = yith_wcmv_get_posted_data( $fields, 'vendor' );
			$vendor_id = YITH_Vendors_Factory::create( $posted );

			if ( is_wp_error( $vendor_id ) ) {
				// translators: %s stand for the error message detail.
				$message = sprintf( _x( 'An error occurred creating vendor. %s', '[Notice]Create vendor process error', 'yith-woocommerce-product-vendors' ), $vendor_id->get_error_message() );
				YITH_Vendors_Admin_Notices::add( $message, 'error' );
			} else {
				$vendor = yith_wcmv_get_vendor( $vendor_id );
				// translators: %s stand for the vendor name.
				$message = sprintf( _x( 'Vendor <b>%s</b> created!', '[Notice]Create vendor process success', 'yith-woocommerce-product-vendors' ), $vendor->get_name() );
				YITH_Vendors_Admin_Notices::add( $message );
			}

			wp_safe_redirect(
				yith_wcmv_get_admin_panel_url(
					array(
						'tab'     => 'vendors',
						'sub_tab' => 'vendors-list',
					)
				)
			);
			exit;
		}

		/**
		 * Handle edit vendor modal submit
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_edit_vendor_submit() {
			if ( ! isset( $_POST['_vendor_modal_nonce'] ) || empty( $_POST['vendor_id'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_vendor_modal_nonce'] ) ), 'yith_wcmv_edit_vendor' ) ) {
				return;
			}

			$vendor_id = isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0;
			$fields    = YITH_Vendors_Factory::get_fields( true );
			// Get posted data.
			$posted = yith_wcmv_get_posted_data( $fields, 'vendor' );
			$result = YITH_Vendors_Factory::update( $vendor_id, $posted );

			if ( is_wp_error( $result ) ) {
				// translators: %s stand for the error message detail.
				$message = sprintf( _x( 'An error occurred updating vendor. %s', '[Notice]Update vendor process error', 'yith-woocommerce-product-vendors' ), $result->get_error_message() );
				YITH_Vendors_Admin_Notices::add( $message, 'error' );
			} else {
				$vendor = yith_wcmv_get_vendor( $vendor_id );
				// translators: %s stand for the vendor name.
				$message = sprintf( _x( 'Vendor <b>%s</b> updated correctly!', '[Notice]Update vendor process success', 'yith-woocommerce-product-vendors' ), $vendor->get_name() );
				YITH_Vendors_Admin_Notices::add( $message );
			}

			wp_safe_redirect(
				yith_wcmv_get_admin_panel_url(
					array(
						'tab'     => 'vendors',
						'sub_tab' => 'vendors-list',
					)
				)
			);
			exit;
		}

		/**
		 * Handle vendors table bulk action
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function handle_table_actions() {
			// phpcs:disable WordPress.Security.NonceVerification
			$action = '';
			if ( ! empty( $_GET['action'] ) && '-1' !== $_GET['action'] ) {
				$action = sanitize_text_field( wp_unslash( $_GET['action'] ) );
			} elseif ( ! empty( $_GET['action2'] ) && '-1' !== $_GET['action2'] ) {
				$action = sanitize_text_field( wp_unslash( $_GET['action2'] ) );
			}

			if ( empty( $action ) || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-vendors' ) ) {
				return;
			}

			$vendors = ! empty( $_GET['vendors'] ) ? array_map( 'absint', (array) $_GET['vendors'] ) : array();
			if ( empty( $vendors ) ) {
				YITH_Vendors_Admin_Notices::add( __( 'Please select at least one vendor.', 'yith-woocommerce-product-vendors' ), 'error' );
				return;
			}

			$redirect      = self::get_current_vendors_list_table_url();
			$redirect_args = array();

			$method_args = array(
				$vendors,
				&$redirect_args,
			);

			$method = 'handle_' . $action;
			if ( method_exists( $this, $method ) ) {
				$this->$method( ...$method_args );
			} else {
				do_action_ref_array( "yith_wcmv_vendors_table_action_{$action}", $method_args );
			}

			wp_safe_redirect( add_query_arg( $redirect_args, $redirect ) );
			exit;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle delete vendor table action
		 *
		 * @since  5.0.0
		 * @param array $vendors       An array of vendors to export.
		 * @param array $redirect_args Redirect url arguments.
		 * @return void
		 */
		protected function handle_delete_vendor( $vendors, &$redirect_args ) {

			$errors = array();
			foreach ( $vendors as $vendor_id ) {
				$res = YITH_Vendors_Factory::delete( $vendor_id );
				if ( is_wp_error( $res ) ) {
					$errors[] = $vendor_id;
				}
			}

			if ( ! empty( $errors ) ) {
				// translators: %s could be a single commission id or a list of ids comma separated.
				YITH_Vendors_Admin_Notices::add( sprintf( _n( 'Error deleting vendor <b>#%s</b>.', 'Error deleting vendors <b>#%s</b>.', count( $errors ), 'yith-woocommerce-product-vendors' ), implode( ', #', $errors ) ), 'error' );
			} else {
				YITH_Vendors_Admin_Notices::add( _n( 'Vendor deleted.', 'Vendors deleted.', count( $vendors ), 'yith-woocommerce-product-vendors' ) );
			}
		}

		/**
		 * Get vendors list table current url including filters.
		 *
		 * @since  5.0.0
		 * @return string
		 */
		public static function get_current_vendors_list_table_url() {
			// phpcs:disable WordPress.Security.NonceVerification
			$url = yith_wcmv_get_admin_panel_url(
				array(
					'tab'     => 'vendors',
					'sub_tab' => 'vendors-list',
				)
			);
			// Optional parameters.
			$args   = array();
			$params = array( 'vendor_status', 'paged', 'order', 'orderby', 's' );
			foreach ( $params as $key ) {
				if ( ! empty( $_GET[ $key ] ) ) {
					$args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
				}
			}

			return apply_filters( 'yith_wcmv_get_current_vendors_list_table_url', add_query_arg( $args, $url ), $args );
			// phpcs:enable WordPress.Security.NonceVerification
		}
	}
}
