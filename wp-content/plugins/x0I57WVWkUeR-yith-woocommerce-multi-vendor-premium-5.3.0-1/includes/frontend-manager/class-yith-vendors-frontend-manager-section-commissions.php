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

if ( ! class_exists( 'YITH_Vendors_Frontend_Manager_Section_Commissions' ) ) {
	/**
	 * Handle panel commissions in frontend manager.
	 */
	class YITH_Vendors_Frontend_Manager_Section_Commissions extends YITH_Frontend_Manager_Section_Commissions {

		/**
		 * Constructor method
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			parent::__construct();

			add_filter( 'yith_wcmv_is_admin_plugin_panel', array( $this, 'set_commissions_tab_as_panel' ), 10, 2 );
			add_filter( 'yith_wcmv_get_commissions_list_table_url', array( $this, 'commissions_list_table_url' ), 10, 1 );

			add_action( 'wp_loaded', array( $this, 'section_handler' ) );
		}

		/**
		 * Define class alias
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function class_alias() {
			class_alias( 'YITH_Vendors_Commissions_List_Table', 'YITH_WCMV_Commissions_List_Table' );
		}

		/**
		 * Required files for this section
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function deps() {
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}

			do_action( 'yith_wcfm_commissions_section_deps', $this );

			require_once YITH_WCFM_LIB_PATH . 'class.yith-frontend-manager-commissions-list-table.php';
		}

		/**
		 * Section handler
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function section_handler() {
			// Get commissions tab handler.
			$commissions_handler = new YITH_Vendors_Admin_Commissions();
			remove_action( 'current_screen', array( $commissions_handler, 'preload_list_table_class' ) );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$commissions_handler->vendor_commissions_table_customize();
			}

			// Handle filter by ID.
			add_filter( 'yith_wcmv_commissions_list_table_args', array( $this, 'filter_commissions_list' ), 10, 1 );

			// Handle action if any.
			$commissions_handler->handle_table_actions();
		}

		/**
		 * Filter commissions list table query arguments.
		 *
		 * @since  4.0.0
		 * @param array $args Array of query arguments.
		 * @return array
		 */
		public function filter_commissions_list( $args ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['id'] ) ) {
				$args['include'] = absint( $_GET['id'] );
			}

			return $args;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Print section
		 *
		 * @since  1.0.0
		 * @param string $subsection (Optional) The subsection to print. Default empty string.
		 * @param string $section    (Optional) The section to print. Default empty string.
		 * @param array  $atts       (Optional) The section attributes array. Default empty array.
		 * @return void
		 */
		public function print_section( $subsection = '', $section = '', $atts = array() ) {
			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( $this->is_enabled() ) {

				set_current_screen( 'commissions' );
				$GLOBALS['hook_suffix'] = 'commissions'; // phpcs:ignore

				$page  = ! empty( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : $this->get_url(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$table = new YITH_WCFM_Commissions_List_Table(
					array(
						'screen'      => 'commissions',
						'section_obj' => $this,
					)
				);
				// Prepare items and display table.
				$table->prepare_items();

				?>
				<div id="yith-wcfm-commisssions">
					<?php YITH_Vendors_Admin_Notices::print(); ?>
					<div id="wpwrap">
						<h1><?php echo esc_html_x( 'Commissions List', '[Admin]: Options section title', 'yith-woocommerce-product-vendors' ); ?></h1>
						<form id="commissions-filter" method="get">
							<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
							<?php $table->display(); ?>
						</form>
					</div>
				</div>
				<?php
				// Include also commission details template.
				yith_wcmv_include_admin_template( 'commission-details' );
			} else {
				do_action( 'yith_wcfm_print_section_unauthorized', $this->id );
			}
		}

		/**
		 * Set frontend manager commissions section as plugin admin tab
		 *
		 * @since  4.0.0
		 * @param boolean $is_panel True if is panel, false otherwise.
		 * @param string  $tab      The tab to check.
		 * @return boolean
		 */
		public function set_commissions_tab_as_panel( $is_panel, $tab ) {
			if ( ( empty( $tab ) || 'commissions' === $tab ) && ! $is_panel ) {
				return ! empty( YITH_Frontend_Manager()->gui ) && 'commissions' === YITH_Frontend_Manager()->gui->get_current_section_obj()->id;
			}

			return $is_panel;
		}

		/**
		 * Filter commissions list table url
		 *
		 * @since  4.0.0
		 * @param string $url Commissions list table url.
		 * @return string
		 */
		public function commissions_list_table_url( $url ) {
			return YITH_Frontend_Manager()->gui ? $this->get_url() : $url;
		}

		/**
		 * Register style and script
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function enqueue_section_scripts() {
			$section = YITH_Frontend_Manager()->gui->get_current_section_obj();
			if ( 'commissions' !== $section->id ) {
				return;
			}

			// Register plugin FW script and style.
			YIT_Assets::instance()->register_styles_and_scripts();
			wp_enqueue_style( 'yit-plugin-style' );
			wp_enqueue_script( 'yit-plugin-panel' );

			// Add AJAX request data.
			add_action( 'wp_print_scripts', array( YITH_Vendors()->admin->get_ajax_handler(), 'add_script_data' ), 5 );

			// Add custom css for frontend manager.
			YITH_Vendors_Admin_Assets::add_css( 'admin', 'frontend-manager.css' );

			YITH_Vendors_Admin_Assets::register_assets();
			YITH_Vendors_Admin_Assets::enqueue_assets();
		}
	}
}

