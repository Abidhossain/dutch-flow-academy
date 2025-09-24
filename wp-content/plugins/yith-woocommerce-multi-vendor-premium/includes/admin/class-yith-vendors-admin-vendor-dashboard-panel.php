<?php
/**
 * YITH Vendors Admin Vendor Dashboard Panel Abstract.
 * This class is useful to create dashboard panel and manage vendor meta.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Vendor_Dashboard_Panel' ) ) {
	/**
	 * YITH_Vendors_Admin_Vendor_Dashboard_Panel class.
	 */
	class YITH_Vendors_Admin_Vendor_Dashboard_Panel {

		/**
		 * Dashboard form action
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const FORM_ACTION = 'yith_wcmv_update_vendor_dashboard';

		/**
		 * Current vendor
		 *
		 * @var YITH_Vendor|null
		 */
		protected $vendor = false;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 */
		public function __construct( $vendor ) {
			$this->vendor = $vendor;
			// Double check for vendor limited access.
			if ( $this->vendor && $this->vendor->is_valid() && $this->vendor->has_limited_access() ) {
				$this->init();
			}
		}

		/**
		 * Init class hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init() {
			// Customize plugin admin dashboard for vendor.
			add_filter( 'yith_wcmv_plugin_panel_args', array( $this, 'filter_plugin_panel_args' ), 50 );
			add_filter( 'yith_wcmv_plugin_panel_capability', array( $this, 'filter_plugin_panel_capability' ) );
			add_filter( 'yith_plugin_fw_wc_panel_screen_ids_for_assets', array( $this, 'add_dashboard_screen_id' ), 10, 1 );
			// Custom panel settings tab.
			add_action( 'yith_wcmv_vendor_dashboard_settings_tab', array( $this, 'dashboard_settings_tab' ) );
			add_action( 'yith_wcmv_vendor_dashboard_payments_tab', array( $this, 'dashboard_payments_tab' ) );
			// Handle submit vendor dashboard.
			add_action( 'admin_init', array( $this, 'handle_update_vendor' ), 10 );
			// Include vendor dashboard tabs in is_plugin_panel check.
			add_filter( 'yith_wcmv_is_admin_plugin_panel_tabs', array( $this, 'get_vendor_dashboard_tabs_keys' ), 0 );

			// Customize plugin FW panel.
			if ( yith_wcmv_is_plugin_panel() ) {
				// Filter field value.
				add_filter( 'yith_plugin_fw_wc_panel_field_data', array( $this, 'filter_panel_data' ), 10, 1 );
				add_filter( 'yith_plugin_fw_wc_panel_pre_field_value', array( $this, 'set_panel_value' ), 10, 2 );
				// Handle actions.
				add_action( 'yit_panel_wc_before_update', array( $this, 'save_vendor_meta' ), 1 );
				add_action( 'yit_panel_wc_before_reset', array( $this, 'reset_vendor_meta' ), 1 );
			}
		}

		/**
		 * Add dashboard screen id to allowed screen ids for plugin fw admin assets
		 *
		 * @since  4.0.0
		 * @param array $screen_ids An array of allowed screen id.
		 * @return array
		 */
		public function add_dashboard_screen_id( $screen_ids ) {
			$screen_ids[] = 'toplevel_page_' . YITH_Vendors_Admin::PANEL_PAGE;
			return array_unique( $screen_ids );
		}

		/**
		 * Get vendor dashboard tabs.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_vendor_dashboard_tabs() {
			$tabs = array(
				'dashboard'   => __( 'Dashboard', 'yith-woocommerce-product-vendors' ),
				'commissions' => __( 'Commissions', 'yith-woocommerce-product-vendors' ),
				'vendors'     => __( 'Store settings', 'yith-woocommerce-product-vendors' ),
				'payments'    => __( 'Payment info', 'yith-woocommerce-product-vendors' ),
			);

			// Check for report permissions.
			if ( ! current_user_can( 'view_woocommerce_reports' ) ) {
				unset( $tabs['dashboard'] );
			}

			return apply_filters( 'yith_wcmv_admin_vendor_dashboard_tabs', $tabs, $this->vendor );
		}

		/**
		 * Get vendor dashboard tabs keys.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_vendor_dashboard_tabs_keys() {
			return array_keys( $this->get_vendor_dashboard_tabs() );
		}

		/**
		 * Get an array of vendor dashboard tab options.
		 *
		 * @since  4.0.0
		 * @param string $tab    The tab to get options for.
		 * @param string $subtab (Optional) The current subtab. Default is empty.
		 * @return array
		 */
		protected function get_dashboard_tab_options( $tab, $subtab = '' ) {

			if ( empty( $tab ) ) {
				return array();
			}

			$options = array();
			$fields  = YITH_Vendors_Factory::get_fields();

			// First check if it is a custom tab.
			switch ( $tab ) {
				case 'settings':
				case 'vendors':
					$options = isset( $fields['store'] ) ? $fields['store'] : array();
					// Add additional fields if any.
					if ( ! empty( $fields['additional'] ) ) {
						$options = array_merge( $options, $fields['additional'] );
					}

					if ( 'no' === get_option( 'yith_wpv_vendors_option_edit_store_slug', 'yes' ) ) {
						unset( $options['slug'] );
					}

					break;

				case 'payments':
					$options = isset( $fields['payment'] ) ? $fields['payment'] : array();
					break;
			}

			// If options is empty search for options file.
			if ( empty( $options ) ) {
				$path = YITH_WPV_PATH . 'plugin-options/vendor-dashboard';
				if ( $subtab ) {
					$path        .= "/{$tab}/{$subtab}-options.php";
					$options_key = $subtab;
				} else {
					$path        .= "/{$tab}-options.php";
					$options_key = $tab;
				}

				if ( file_exists( $path ) ) {
					$options = include $path;
					$options = ! empty( $options[ $options_key ] ) ? $options[ $options_key ] : array();
				}
			}

			// Prepare options for posted data.
			$prepared_options = array();
			foreach ( $options as $key => $option ) {
				if ( ! empty( $option['id'] ) ) {
					$key = $option['id'];
				}

				if ( isset( $option['yith-type'] ) ) {
					$option['type'] = $option['yith-type'];
				}
				$prepared_options[ $key ] = $option;
			}

			return apply_filters( "yith_wcmv_get_dashboard_{$tab}_options", $prepared_options, $tab, $subtab );
		}

		/**
		 * Filter plugin panel args to customize it for simple vendor
		 *
		 * @since  4.0.0
		 * @param array $args The current panel arguments.
		 * @return array
		 */
		public function filter_plugin_panel_args( $args ) {

			if ( apply_filters( 'yith_wcmv_hide_vendor_settings', false ) ) {
				return $args;
			}

			$args = array_merge(
				$args,
				array(
					'create_menu_page' => false,
					'page_title'       => _x( 'Your shop', '[Admin]Vendor dashboard menu menu title', 'yith-woocommerce-product-vendors' ),
					'menu_title'       => _x( 'Your shop', '[Admin]Vendor dashboard menu menu title', 'yith-woocommerce-product-vendors' ),
					'capability'       => YITH_Vendors_Capabilities::ROLE_ADMIN_CAP,
					'parent'           => 'vendor_' . $this->vendor->get_id(),
					'parent_page'      => '',
					'admin-tabs'       => $this->get_vendor_dashboard_tabs(),
					'options-path'     => YITH_WPV_PATH . 'plugin-options/vendor-dashboard',
					'icon_url'         => 'dashicons-store',
					'position'         => 30,
				)
			);

			// Unset help tab for vendor.
			unset( $args['help_tab'], $args['your_store_tools'] );

			return $args;
		}

		/**
		 * Filter plugin panel capability
		 *
		 * @since  4.0.0
		 * @param string $capability The default panel capability.
		 * @return string
		 */
		public function filter_plugin_panel_capability( $capability ) {
			return YITH_Vendors_Capabilities::ROLE_ADMIN_CAP;
		}

		/**
		 * Prepare vendor fields
		 *
		 * @since  4.0.0
		 * @param array $options The section options array.
		 * @return array
		 */
		protected function prepare_dashboard_vendor_options( $options ) {
			foreach ( $options as $key => &$option ) {
				$type = isset( $option['type'] ) ? $option['type'] : 'text';
				if ( 'title' === $type || 'html' === $type || 'separator' === $type ) {
					continue;
				}

				$option          = $this->filter_panel_data( $option );
				$option['value'] = $this->set_panel_value( '', $option, $key );
			}

			return $options;
		}

		/**
		 * Set default panel options value to get Vendor data
		 *
		 * @since  4.0.0
		 * @param mixed  $value  Current field value.
		 * @param array  $option The field option.
		 * @param string $id     (Optional) The field id. Default is empty string.
		 * @return mixed
		 */
		public function set_panel_value( $value, $option, $id = '' ) {
			$id = ! empty( $id ) ? $id : ( isset( $option['id'] ) ? $option['id'] : '' );
			if ( $id ) {
				$values = YITH_Vendors_Factory::get_data( $this->vendor->get_id(), false, array( $id ) );
				$value  = isset( $values[ $id ] ) ? $values[ $id ] : null;

				if ( empty( $value ) && ! $this->vendor->meta_exists( $id ) ) {
					$value = null;
				}
			}

			$value = ! is_null( $value ) ? $value : ( isset( $option['default'] ) ? $option['default'] : '' );
			return apply_filters( 'yith_wcmv_vendor_dashboard_panel_value', $value, $option, $id );
		}

		/**
		 * Filter panel options data to handle textarea editor permission
		 *
		 * @since  4.0.0
		 * @param array $data The panel option data.
		 * @return mixed
		 */
		public function filter_panel_data( $data ) {
			$enable_editor       = 'yes' === get_option( 'yith_wpv_vendors_option_editor_management', 'no' );
			$enable_editor_media = 'yes' === get_option( 'yith_wpv_vendors_option_editor_media', 'no' );

			if ( isset( $data['type'] ) && 'textarea-editor' === $data['type'] ) {
				if ( ! $enable_editor ) {
					$data['type'] = 'textarea';
				} else {
					$data['media_buttons'] = $enable_editor_media;
				}
			}
			return $data;
		}

		/**
		 * Handle custom settings tab for vendor dashboard
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function dashboard_settings_tab() {
			yith_wcmv_include_admin_template(
				'vendor-dashboard/settings-tab.php',
				array(
					'fields'    => $this->prepare_dashboard_vendor_options( $this->get_dashboard_tab_options( 'settings' ) ),
					'section'   => 'settings',
					'vendor'    => $this->vendor,
					'vendor_id' => $this->vendor->get_id(),
				)
			);
		}

		/**
		 * Handle custom settings tab for vendor dashboard
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function dashboard_payments_tab() {
			yith_wcmv_include_admin_template(
				'vendor-dashboard/payments-tab.php',
				array(
					'fields'    => $this->prepare_dashboard_vendor_options( $this->get_dashboard_tab_options( 'payments' ) ),
					'section'   => 'payments',
					'vendor'    => $this->vendor,
					'vendor_id' => $this->vendor->get_id(),
				)
			);
		}

		/**
		 * Handle update vendor from dashboard
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_update_vendor() {

			if ( ! isset( $_POST['action'] ) || ! isset( $_POST['_wpnonce'] ) || self::FORM_ACTION !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), self::FORM_ACTION ) ) {
				return;
			}

			$vendor_id = isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0;
			// Double check vendor ID is the current one.
			if ( $vendor_id !== $this->vendor->get_id() ) {
				return;
			}

			$section = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : '';
			$fields  = $this->get_dashboard_tab_options( $section );
			$posted  = yith_wcmv_get_posted_data( $fields, 'vendor' );
			if ( empty( $posted ) ) {
				return;
			}

			// Update vendor!
			$result = YITH_Vendors_Factory::update( $vendor_id, $posted );

			if ( is_wp_error( $result ) ) {
				// translators: %s stand for the error message detail.
				$message = _x( 'An error occurred updating your settings.', '[Notice]Update vendor process error', 'yith-woocommerce-product-vendors' );
				YITH_Vendors_Admin_Notices::add( $message, 'error' );
			} else {
				$message = _x( 'Settings updated correctly!', '[Notice]Update vendor process success', 'yith-woocommerce-product-vendors' );
				YITH_Vendors_Admin_Notices::add( $message );
			}

			wp_safe_redirect( yith_wcmv_get_admin_panel_url( array( 'tab' => ( 'settings' === $section ) ? 'vendors' : 'payments' ) ) );
			exit;
		}

		/**
		 * Save vendor meta
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function save_vendor_meta() {

			$tab     = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$subtab  = ! empty( $_GET['sub_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$options = $this->get_dashboard_tab_options( $tab, $subtab );

			// Prepare message.
			$message      = _x( 'There was an error saving store options.', '[Admin]Generic vendor dashboard error message.', 'yith-woocommerce-product-vendors' );
			$message_type = 'error';

			if ( ! empty( $options ) ) {
				// Prepare options for posted data.
				$prepared_options = array();
				foreach ( $options as $option ) {
					if ( empty( $option['id'] ) ) {
						continue;
					}

					if ( isset( $option['yith-type'] ) ) {
						$option['type'] = $option['yith-type'];
					}
					$prepared_options[ $option['id'] ] = $option;
				}

				$posted = yith_wcmv_get_posted_data( $prepared_options );
				$res    = YITH_Vendors_Factory::update( $this->vendor->get_id(), $posted );
				if ( ! is_wp_error( $res ) ) {
					$message      = _x( 'Store options saved correctly.', '[Admin]Generic vendor dashboard success message.', 'yith-woocommerce-product-vendors' );
					$message_type = 'success';
				}
			}

			YITH_Vendors_Admin_Notices::add( $message, $message_type );

			wp_safe_redirect(
				yith_wcmv_get_admin_panel_url(
					array_filter(
						array(
							'tab'     => $tab,
							'sub_tab' => $subtab,
						)
					)
				)
			);
			exit;
		}

		/**
		 * Reset vendor meta
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function reset_vendor_meta() {

			$tab     = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$subtab  = ! empty( $_GET['sub_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$options = $this->get_dashboard_tab_options( $tab, $subtab );

			// Prepare message.
			$message      = _x( 'There was an error resetting store options.', '[Admin]Generic vendor dashboard error message.', 'yith-woocommerce-product-vendors' );
			$message_type = 'error';

			if ( ! empty( $options ) ) {
				// Prepare options with default values.
				$default_options = array();
				foreach ( $options as $option ) {
					if ( empty( $option['id'] ) ) {
						continue;
					}
					$default_options[ $option['id'] ] = ! empty( $option['default'] ) ? $option['default'] : null;
				}

				$res = YITH_Vendors_Factory::update( $this->vendor->get_id(), $default_options );
				if ( ! is_wp_error( $res ) ) {
					$message      = _x( 'Store options reset correctly.', '[Admin]Generic vendor dashboard success message.', 'yith-woocommerce-product-vendors' );
					$message_type = 'success';
				}
			}

			YITH_Vendors_Admin_Notices::add( $message, $message_type );

			wp_safe_redirect(
				yith_wcmv_get_admin_panel_url(
					array_filter(
						array(
							'tab'     => $tab,
							'sub_tab' => $subtab,
						)
					)
				)
			);
			exit;
		}
	}
}
