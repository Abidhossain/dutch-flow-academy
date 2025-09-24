<?php
/**
 * YITH_Vendors_Frontend_Manager_Section_Vendor class.
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

use Automattic\WooCommerce\Internal\Admin\WCAdminAssets;

if ( ! class_exists( 'YITH_Vendors_Frontend_Manager_Section_Vendor' ) ) {
	/**
	 * Handle panel vendor in frontend manager.
	 */
	class YITH_Vendors_Frontend_Manager_Section_Vendor extends YITH_Frontend_Manager_Section_Vendor {

		/**
		 * Current Vendor Object
		 *
		 * @var null|YITH_Vendor
		 */
		public $vendor = null;

		/**
		 * Vendor panel object
		 *
		 * @since 4.0.0
		 * @var null|YIT_Plugin_Panel_Woocommerce
		 */
		protected $vendor_panel = null;

		/**
		 * Constructor method
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			parent::__construct();
			$this->set_allowed_query_string( array( 'page' => YITH_Vendors_Admin::PANEL_PAGE ) );

			remove_action( 'wp_enqueue_scripts', array( YITH_Vendors()->admin, 'enqueue_scripts' ), 5 );

			add_action( 'wp_loaded', array( $this, 'maybe_register_panel' ) );
			add_filter( 'yith_wc_plugin_panel_current_tab', array( $this, 'admin_tab_params' ) );
			add_filter( 'yith_wcmv_admin_vendor_dashboard_tabs', array( $this, 'remove_panel_tabs' ), 99, 1 );
		}

		/**
		 * Maybe register vendor section panel
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function maybe_register_panel() {

			if ( ! $this->is_vendor_panel() ) {
				return;
			}

			if ( ! empty( YITH_Frontend_Manager()->gui ) ) {
				add_filter( 'yit_panel_hide_sidebar', '__return_true' );
			}

			if ( ! function_exists( 'woocommerce_admin_fields' ) ) {
				include_once WC()->plugin_path() . '/includes/admin/wc-admin-functions.php';
			}

			// Register panel filters.
			add_filter( 'yith_wcmv_get_admin_panel_url', array( $this, 'filter_admin_panel_url' ), 99, 2 );
			add_filter( 'yith_wcmv_plugin_panel_args', array( $this, 'filter_admin_panel_args' ), 99, 1 );

			// Register panel.
			YITH_Vendors()->admin->register_panel();
			$this->vendor_panel = YITH_Vendors()->admin->get_panel();

			// Handle update option.
			$this->vendor_panel->woocommerce_update_options();
			YITH_Vendors()
				->admin
				->get_vendor_dashboard_handler()
				->get_panel_handler()
				->handle_update_vendor();
		}

		/**
		 * Print shortcode function
		 *
		 * @since  4.0.0
		 * @param array  $atts    (Optional) An array of shortcode attributes. Default empty array.
		 * @param string $content (Optional) The shortcode content. Default empty string.
		 * @param string $tag     (Optional) The shortcode tag. Default empty string.
		 * @return void
		 */
		public function print_shortcode( $atts = array(), $content = '', $tag = '' ) {
			if ( ! is_user_logged_in() || ! $this->is_enabled() || is_null( $this->vendor_panel ) ) {
				return;
			}

			$GLOBALS['hook_suffix'] = 'vendor'; // phpcs:ignore

			// Print notices if any.
			YITH_Vendors_Admin_Notices::print();

			// Print panel. Wrap in div with ID #wpwrap to let yith.ui works properly.
			echo '<div id="wpwrap">';

			do_action( 'yith_wcmv_before_fm_vendor_panel' );

			$this->vendor_panel->yit_panel();

			do_action( 'yith_wcmv_after_fm_vendor_panel' );

			echo '</div>';
		}

		/**
		 * Get correct vendor panel endpoint uri
		 *
		 * @since  4.0.0
		 * @param string $endpoint_uri Current section endpoint uri.
		 * @param string $slug         The section slug.
		 * @param string $subsection   Slug of subsection to check; if no subsection is passed, main section is checked.
		 * @param string $id           The section ID.
		 * @return string
		 */
		public function vendor_settings_uri( $endpoint_uri, $slug, $subsection, $id ) {
			if ( $id === $this->get_id() ) {
				$endpoint_uri = add_query_arg( array( 'page' => YITH_Vendors_Admin::PANEL_PAGE ), $endpoint_uri );
			}
			return $endpoint_uri;
		}

		/**
		 * Filter panel url.
		 *
		 * @since  4.0.0
		 * @param string $url         Current panel url.
		 * @param string $page        The page.
		 * @param string $tab         The tab.
		 * @param string $sub_tab     The sub-tab.
		 * @param string $parent_page The parent page.
		 *
		 * @return string
		 */
		public function change_panel_url( $url, $page, $tab, $sub_tab, $parent_page ) {
			if ( YITH_Vendors_Admin::PANEL_PAGE === $page && ! empty( YITH_Frontend_Manager()->gui ) && YITH_Frontend_Manager()->gui->is_main_page() ) {
				$old_url = add_query_arg( array( 'page' => $page ), admin_url( 'admin.php' ) );
				$url     = str_replace( $old_url, $this->get_url(), $url );
			}

			return $url;
		}

		/**
		 * Filter admin panel url.
		 *
		 * @since  4.0.0
		 * @param string $url  Current panel url.
		 * @param array  $args (Optional) Panel url arguments.
		 *
		 * @return string
		 */
		public function filter_admin_panel_url( $url, $args = array() ) {
			return ! empty( $args ) ? add_query_arg( $args, $this->get_url() ) : $this->get_url();
		}

		/**
		 * Filter admin panel args
		 *
		 * @since 5.0.0
		 * @param array $args Panel args.
		 * @retur array
		 */
		public function filter_admin_panel_args( $args ) {
			unset( $args['ui_version'] );
			return $args;
		}

		/**
		 * Check if current section is vendor panel.
		 *
		 * @since  4.0.0
		 * @return boolean True if is the panel, false otherwise.
		 */
		protected function is_vendor_panel() {
			if ( empty( YITH_Frontend_Manager()->gui ) || empty( $_GET['page'] ) || YITH_Vendors_Admin::PANEL_PAGE !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return false;
			}
			return true;
		}

		/**
		 * Filter panel admin tab params
		 *
		 * @since  4.0.0
		 * @param string $tab Current tab value.
		 * @return string
		 */
		public function admin_tab_params( $tab ) {
			if ( ! $this->is_vendor_panel() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return $tab;
			}
			return ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $tab; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Remove panel tabs for vendor in frontend manager.
		 *
		 * @since  4.0.0
		 * @param array $tabs An array of tabs.
		 * @return array
		 */
		public function remove_panel_tabs( $tabs ) {
			if ( ! $this->is_vendor_panel() ) {
				return $tabs;
			}
			unset( $tabs['commissions'] );

			if ( ! class_exists( 'Automattic\WooCommerce\Internal\Admin\WCAdminAssets' ) ) {
				unset( $tabs['dashboard'] );
			}
			return $tabs;
		}

		/**
		 * Enqueue vendor section style and scripts.
		 * The global enqueue is done by parent class.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function enqueue_section_scripts() {
			if ( empty( $this->vendor_panel ) ) {
				return;
			}

			// Register plugin FW script and style.
			YIT_Assets::instance()->register_styles_and_scripts();
			wp_enqueue_style( 'yit-plugin-style' );
			wp_enqueue_script( 'yit-plugin-panel' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'yith-plugin-fw-fields' );

			// Register WC Admin scripts for dashboard report.
			if ( class_exists( 'Automattic\WooCommerce\Internal\Admin\WCAdminAssets' ) && yith_wcmv_is_plugin_panel( 'dashboard' ) ) {
				Automattic\WooCommerce\Internal\Admin\WCAdminAssets::get_instance()->register_scripts();
			}

			// Add AJAX request data.
			add_action( 'wp_print_scripts', array( YITH_Vendors()->admin->get_ajax_handler(), 'add_script_data' ), 5 );

			// Add dashboard script to handle fields.
			YITH_Vendors()
				->admin
				->get_vendor_dashboard_handler()
				->add_style_and_scripts();

			// If shipping tab, add related scripts.
			if ( yith_wcmv_is_plugin_panel( 'shipping' ) && ! empty( YITH_Vendors_Shipping()->admin ) ) {
				YITH_Vendors_Shipping()->admin->enqueue_scripts();
			}

			// Add custom css for frontend manager.
			YITH_Vendors_Admin_Assets::add_css( 'admin', 'frontend-manager.css' );
			YITH_Vendors_Admin_Assets::register_assets();
			YITH_Vendors_Admin_Assets::enqueue_assets();

			do_action( 'yith_wcmv_vendor_section_scripts_enqueued' );
		}

		/**
		 * Check if a shop manager can access to WordPress backend
		 *
		 * @since  1.0.0
		 * @param boolean $prevent_access True to prevent access, false otherwise.
		 * @return bool
		 */
		public function prevent_admin_access( $prevent_access ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$action = ! empty( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

			$is_save_vendor_panel   = 'yith_admin_save_fields' === $action;
			$is_media_library       = 'query-attachments' === $action;
			$is_upload_image        = 'upload-attachment' === $action;
			$is_add_attribute       = 'woocommerce_add_attribute' === $action;
			$is_change_order_status = 'woocommerce_mark_order_status' === $action;

			if ( ! empty( $_REQUEST['post'] ) ) {
				$_post      = $_REQUEST['post']; // phpcs:ignore
				$_post_type = get_post_type( $_post );
			}

			$is_delete_option = 'trash' === $action && 'shop_order' === $_post_type;
			if ( ! wp_doing_ajax() && ! $is_delete_option && ! $is_upload_image && ! $is_save_vendor_panel && ! $is_media_library && ! $is_add_attribute && ! $is_change_order_status && current_user_can( YITH_Vendors_Capabilities::ROLE_NAME ) && 'yes' === get_option( 'yith_wcfm_prevent_backend_access_for_vendor', 'no' ) ) {
				$prevent_access = true;
			}

			return $prevent_access;
			// phpcs:enable WordPress.Security.NonceVerification
		}
	}
}
