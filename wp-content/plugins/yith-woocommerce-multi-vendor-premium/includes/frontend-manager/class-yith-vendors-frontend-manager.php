<?php
/**
 * YITH_Vendors_Frontend_Manager class.
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


if ( ! function_exists( 'YITH_Frontend_Manager_For_Vendor' ) ) {
	/**
	 * Redefine YITH_Frontend_Manager_For_Vendor to return overridden instance
	 *
	 * @since  4.0.0
	 * @return YITH_Vendors_Frontend_Manager
	 */
	function YITH_Frontend_Manager_For_Vendor() { // phpcs:ignore
		return YITH_Vendors_Frontend_Manager::instance();
	}
}

if ( ! class_exists( 'YITH_Frontend_Manager_For_Vendor', false ) ) {
	require_once YITH_WCFM_CLASS_PATH . 'module/multi-vendor/module.yith-multi-vendor.php';
}

if ( ! class_exists( 'YITH_Vendors_Frontend_Manager' ) ) {
	/**
	 * This class is used to handle compatibility with YITH Frontend Manager for WooCommerce
	 */
	class YITH_Vendors_Frontend_Manager extends YITH_Frontend_Manager_For_Vendor {

		/**
		 * Main instance
		 *
		 * @since  4.0.0
		 * @var null|YITH_Vendors_Frontend_Manager
		 */
		private static $instance = null;

		/**
		 * Default section classes to load
		 *
		 * @since 4.0.0
		 * @var string|boolean
		 */
		protected $vendor_section_classes = array();

		/**
		 * Clone.
		 * Disable class cloning and throw an error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single
		 * object. Therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'yith-woocommerce-product-vendors' ), '1.0.0' );
		}

		/**
		 * Wakeup.
		 * Disable unserializing of the class.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'yith-woocommerce-product-vendors' ), '1.0.0' );
		}

		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @since  4.0.0
		 * @return YITH_Vendors_Frontend_Manager Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Class construct
		 */
		protected function __construct() {
			// Filter admin request check.
			add_filter( 'yith_wcmv_is_admin_request', '__return_true', 99 );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'template_redirect', array( $this, 'unset_wrong_hooks' ) );
			// Disable default YITH Frontend Manager handlers.
			add_filter( 'yith_wcfm_module_files', array( $this, 'unset_default_frontend_manager_module' ), 10, 1 );
			add_filter( 'yith_wcfm_section_files', array( $this, 'overwrite_default_vendor_section' ), 10, 1 );
			add_action( 'yith_wcfm_after_load_common_classes', array( $this, 'load_required_section_file' ), 99 );

			add_filter( 'yith_wcmv_admin_panel_tabs', array( $this, 'remove_panel_tabs' ), 99, 1 );
			add_filter( 'yith_wcfm_allowed_product_status', array( $this, 'allowed_product_status' ) );
			add_filter( 'yith_wcfm_query_vars_for_product_query', array( $this, 'get_product_query_by_vendor' ), 10, 2 );
		}

		/**
		 * Remove panel tabs for vendor in frontend manager.
		 * The action is added here and not in YITH_Vendors_Frontend_Manager_Section_Vendor class to fix priority hook issue.
		 *
		 * @since  4.0.0
		 * @param array $tabs An array of tabs.
		 * @return array
		 */
		public function remove_panel_tabs( $tabs ) {
			if ( is_admin() || empty( $_GET['page'] ) || YITH_Vendors_Admin::PANEL_PAGE !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return $tabs;
			}
			unset( $tabs['commissions'] );
			return $tabs;
		}

		/**
		 * Unset default Multi Vendor module from YITH Frontend Manager for WooCommerce.
		 *
		 * @since  4.0.0
		 * @param array $modules The modules to load.
		 * @return array
		 */
		public function unset_default_frontend_manager_module( $modules ) {
			unset( $modules['YITH_Vendors'] );
			return $modules;
		}

		/**
		 * Unset default Multi Vendor section from YITH Frontend Manager for WooCommerce.
		 *
		 * @since  4.0.0
		 * @param array $sections The sections to load.
		 * @return array
		 */
		public function overwrite_default_vendor_section( $sections ) {

			// Commissions section.
			if ( isset( $sections['YITH_Frontend_Manager_Section_Commissions'] ) ) {
				$this->vendor_section_classes[] = $sections['YITH_Frontend_Manager_Section_Commissions'];
				// Unset the original section.
				unset( $sections['YITH_Frontend_Manager_Section_Commissions'] );
				// Overwrite with the custom one.
				$sections['YITH_Vendors_Frontend_Manager_Section_Commissions'] = YITH_WPV_PATH . 'includes/frontend-manager/class-yith-vendors-frontend-manager-section-commissions.php';
			}

			// Vendor section.
			if ( isset( $sections['YITH_Frontend_Manager_Section_Vendor'] ) ) {
				$this->vendor_section_classes[] = $sections['YITH_Frontend_Manager_Section_Vendor'];
				// Unset the original section.
				unset( $sections['YITH_Frontend_Manager_Section_Vendor'] );
				// Overwrite with the custom one.
				$sections['YITH_Vendors_Frontend_Manager_Section_Vendor'] = YITH_WPV_PATH . 'includes/frontend-manager/class-yith-vendors-frontend-manager-section-vendor.php';
			}
			
			// Order section.
			if ( isset( $sections['YITH_Frontend_Manager_Section_Orders'] ) ) {
				$this->vendor_section_classes[] = $sections['YITH_Frontend_Manager_Section_Orders'];
				// Unset the original section.
				unset( $sections['YITH_Frontend_Manager_Section_Orders'] );
				// Overwrite with the custom one.
				$sections['YITH_Vendors_Frontend_Manager_Section_Orders'] = YITH_WPV_PATH . 'includes/frontend-manager/class-yith-vendors-frontend-manager-section-orders.php';
			}

			return $sections;
		}

		/**
		 * Load required section file.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function load_required_section_file() {
			if ( ! empty( $this->vendor_section_classes ) ) {
				foreach ( $this->vendor_section_classes as $class ) {
					if ( file_exists( $class ) ) {
						// Require original section.
						require_once $class;
					}
				}
			}
		}

		/**
		 * Init class hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function init() {

			if ( empty( YITH_Vendors()->admin ) ) {
				return;
			}

			$this->vendor                 = yith_wcmv_get_vendor( 'current', 'user' );
			$this->current_user_is_vendor = $this->vendor && $this->vendor->is_valid() && $this->vendor->has_limited_access();

			if ( $this->current_user_is_vendor ) {
				// Vendors admin limitation.
				if ( get_current_user_id() !== $this->vendor->get_owner() ) {
					// Allow commissions section only for vendor store owner.
					add_filter( 'yith_wcfm_print_commissions_section', '__return_false' );
					add_filter( 'yith_wcfm_remove_commissions_menu_item', '__return_true' );
				}

				// Allow vendors to manage WooCommerce on front.
				add_filter( 'yith_wcfm_access_capability', array( $this, 'allow_vendor_to_manage_store_on_front' ) );
				// Vendor can't manage taxonomy and we need to remove the add new tags and add new category button.
				add_filter( 'yith_wcfm_show_add_new_product_taxonomy_term', '__return_false' );
				// Manage Section.
				add_filter( 'yith_wcfm_get_section_enabled_id_from_object', array( $this, 'get_section_enabled_id_from_object' ) );
				// Products.
				add_action( 'init', array( $this, 'products_management' ), 20 );
				add_filter( 'yith_wcfm_premium_products_subsections', array( $this, 'prevent_vendor_edit_product_taxonomies' ), 10, 4 );
				// Coupons.
				add_action( 'init', array( $this, 'coupons_management' ), 20 );
				// Orders.
				add_action( 'init', array( $this, 'orders_management' ), 20 );
				add_filter( 'yith_wcfm_get_subsections_in_print_navigation', array( $this, 'orders_subsections' ), 10, 2 );
				// WooCommerce Reports.
				add_filter( 'yith_wcfm_reports_subsections', array( $this, 'add_vendor_commissions_reports_subsections' ), 10, 2 );
				add_filter( 'yith_wcfm_orders_reports_type', array( $this, 'orders_reports_type' ) );
				add_filter( 'yith_wcfm_print_dashboard_section_args', array( $this, 'net_sales_this_month_hack' ) );
				// Dashboard.
				add_filter( 'yith_wcfm_outofstock_count_transient', '__return_false' );
				add_filter( 'yith_wcfm_low_stock_count_transient', '__return_false' );
				add_filter( 'yith_wcfm_save_stock_transient', '__return_false' );
				add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'get_order_report_query_for_dashboard' ), 20 );
				add_action( 'yith_wcfm_dashboard_info', array( $this, 'show_unpaid_commissions' ) );

				// My Account URL.
				add_filter( 'yith_wcmv_my_vendor_dashboard_uri', 'yith_wcfm_get_main_page_url' );
				// Get vendor avatar.
				add_filter( 'yith_wcfm_user_avatar', array( $this, 'get_vendor_avatar' ), 10, 2 );

				// Live Chat.
				add_action( 'init', array( $this, 'live_chat_management' ), 20 );
				// SMS Notifications.
				add_action( 'init', array( $this, 'sms_management' ), 20 );
				// Stripe Connect.
				add_filter( 'yith_wcsc_connect_account_template_args', array( $this, 'stripe_connect_account_template_args' ) );
				add_filter( 'yith_wcsc_account_page_script_data', array( $this, 'stripe_connect_account_template_args' ) );
				// Panel policy revision message.
				add_action( 'yith_wcmf_before_print_section', array( $this, 'print_check_revision_message' ), 5 );
				/* === Vendor Panel: Search Customer ===  */
				add_action( 'wc_ajax_json_search_customers', 'YITH_Vendors_Admin::json_search_admins', 5 );

				// Report Stock
				add_filter( 'woocommerce_report_low_in_stock_query_from', array( $this, 'filter_report_stock_query_from' ) );
				add_filter( 'woocommerce_report_out_of_stock_query_from', array( $this, 'filter_report_stock_query_from' ) );
				add_filter( 'woocommerce_report_most_stocked_query_from', array( $this, 'filter_report_stock_query_from' ) );

				add_filter( 'yith_wcfm_low_in_stock_items', array( $this, 'filter_report_stock_query_from' ) );

			} else { // WebSite Admin.

				add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'admin_settings_sanitize_option' ), 10, 3 );
				add_action( 'woocommerce_admin_field_yith-wcfm-double-checkbox', 'yith_wcfm_double_checkbox', 10, 1 );
				add_filter( 'yith_wcfm_section_option_type', 'YITH_Frontend_Manager_For_Vendor::section_option_type', 10, 2 );
				add_filter( 'yith_wcfm_section_option_title', 'YITH_Frontend_Manager_For_Vendor::section_option_title', 10, 2 );

				// Reports.
				add_filter( 'yith_wcfm_reports_subsections', array( $this, 'add_vendor_reports_admin_subsections' ), 15, 2 );
				// Orders.
				add_action( 'yith_wcfm_order_cols_suborder', array( YITH_Vendors()->admin->get_orders_handler(), 'render_shop_order_columns' ), 10, 2 );
				// Products.
				add_action( 'yith_wcfm_product_save', array( YITH_Vendors()->admin->get_products_handler(), 'commission_tab_save' ), 10, 2 );
				add_filter( 'yith_wcmv_single_product_commission_value_object', array( $this, 'single_product_commission_value_object' ) );
				add_filter( 'post_column_taxonomy_links', array( $this, 'post_column_taxonomy_links' ), 10, 3 );
			}

			add_filter( 'yith_wcfm_print_shortcode_template_args', array( $this, 'orders_template_args' ) );
			// Regenerate transient after activation process.
			add_action( 'yith_wcmv_plugin_activation_process_completed', 'YITH_Frontend_Manager::regenerate_transient_rewrite_rule_transient' );
			// Product edit page.
			add_action( 'yith_wcfm_show_product_metaboxes', 'YITH_Vendors_Taxonomy::single_taxonomy_meta_box', 20 );

			if ( ! is_admin() && ! wp_doing_ajax() ) {

				// Prevent default vendor dashboard actions.
				remove_action( 'pre_get_posts', array( YITH_Vendors()->admin->get_vendor_dashboard_handler(), 'filter_content' ), 20 );
				remove_filter( 'wp_count_posts', array( YITH_Vendors()->admin->get_vendor_dashboard_handler(), 'filter_count_posts' ), 10 );

				// WC Report.
				add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'filter_dashboard_values' ), 10, 1 );

				add_filter( 'yith_wcmv_edit_order_uri', array( $this, 'edit_order_uri' ), 10, 2 );
				add_filter( 'wp_count_posts', 'YITH_Vendors_Admin::vendor_count_posts', 10, 3 );

				// Commission table URI Management.
				add_filter( 'yith_wcmv_commissions_list_table_product_url', array( $this, 'customize_product_url' ), 10, 3 );
				add_filter( 'yith_wcmv_commissions_list_table_order_url', 'yith_wcfm_order_url', 10, 3 );
				add_filter( 'yith_wcmv_commission_get_view_url', array( $this, 'customize_commission_url' ), 10, 3 );
			}
		}

		/**
		 * Unset wrong hooks to prevent issue on direct call
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function unset_wrong_hooks() {
			remove_filter( 'yith_wcmv_commissions_list_table_product_url', 'yith_wcfm_product_url', 10 );
		}

		/**
		 * Allow vendor to manager store on front
		 * if the current vendor is enabled
		 * (not pending account)
		 *
		 * @static
		 * @since  4.0
		 * @param string $cap Current capability requested for panel.
		 * @return string Vendor role.
		 */
		public function allow_vendor_to_manage_store_on_front( $cap ) {
			if ( $this->vendor->has_status( 'enabled' ) ) {
				$cap = YITH_Vendors_Capabilities::ROLE_ADMIN_CAP;
			}

			return $cap;
		}

		/**
		 * Get the vendor avatar image
		 *
		 * @since  1.0.11
		 * @param string         $user_avatar Current user avatar.
		 * @param string|integer $avatar_size The avatar size.
		 * @return string Avatar image.
		 */
		public function get_vendor_avatar( $user_avatar, $avatar_size ) {
			if ( $this->vendor && $this->vendor->is_valid() ) {
				$user_avatar = wp_get_attachment_image( $this->vendor->get_avatar_id(), array( $avatar_size, $avatar_size ), false, array( 'class' => 'avatar' ) );
			}
			return $user_avatar;
		}

		/**
		 * Customize commission table product url
		 *
		 * @since  4.0.0
		 * @param string          $url        Current product url.
		 * @param WC_Product      $product    Product object.
		 * @param YITH_Commission $commission Commission object.
		 * @return string
		 */
		public function customize_product_url( $url, $product, $commission ) {
			return yith_wcfm_product_url( $url, array( 'product_id' => $product->get_id() ), $commission );
		}

		/**
		 * Customize single commission url
		 *
		 * @since  4.0.0
		 * @param string          $url        Current product url.
		 * @param string          $context    Url context.
		 * @param YITH_Commission $commission Commission object.
		 * @return string
		 */
		public function customize_commission_url( $url, $context, $commission ) {
			$url = add_query_arg( array( 'id' => $commission->get_id() ), yith_wcfm_get_section_url( 'commissions' ) );
			return $url;
		}

		/**
		 * Check if vendor can manage refunds or not.
		 *
		 * @since  1.0
		 * @return void
		 */
		public function allow_vendor_to_manage_refunds() {
			$is_ajax          = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$is_order_details = is_admin() && 'shop_order' === get_current_screen()->id;

			if ( $this->current_user_is_vendor && $is_order_details && ! $is_ajax ) {
				$refund_management = 'yes' === get_option( 'yith_wpv_vendors_option_order_refund_synchronization', 'no' );
				if ( ! $refund_management ) {
					$js = "jQuery( 'button.refund-items' ).remove();";
					wp_add_inline_script( 'yith-frontend-manager-order-js', $js );
				}
			}
		}

		/**
		 * Add reports management action
		 *
		 * @since  4.0.0
		 * @param array  $subsections An array of subsections.
		 * @param object $obj         Subsections object.
		 * @return array
		 */
		public function add_vendor_commissions_reports_subsections( $subsections, $obj ) {
			$new_subsection = array(
				'commissions-report' => array(
					'slug' => $obj->get_option( 'slug', $obj->id . '_commissions-report', 'commissions-report' ),
					'name' => __( 'Commissions', 'yith-woocommerce-product-vendors' ),
				),
			);

			if ( $this->current_user_is_vendor && ! is_admin() ) {
				unset( $subsections['customers-report'] );
			}

			return array_merge( $subsections, $new_subsection );
		}

		/**
		 * Add vendor reports for admin
		 *
		 * @since  4.0.0
		 * @param array  $subsections An array of subsections.
		 * @param object $obj         Subsections object.
		 * @return array
		 */
		public function add_vendor_reports_admin_subsections( $subsections, $obj ) {
			$new_subsection = array(
				'vendors' => array(
					'slug' => $obj->get_option( 'vendors', $obj->id . '_vendors', 'vendors' ),
					'name' => YITH_Vendors_Taxonomy::get_taxonomy_labels( 'menu_name' ),
				),
			);

			if ( ! $this->current_user_is_vendor && ! is_admin() ) {
				unset( $subsections['commissions'] );
			}

			return array_merge( $subsections, $new_subsection );
		}

		/**
		 * Change the net sales amount for vendors
		 *
		 * @since  4.0.0
		 * @param array $args An array of report arguments.
		 * @return array
		 */
		public function net_sales_this_month_hack( $args ) {
			if ( $this->current_user_is_vendor ) {
				$report_class = YITH_WPV_PATH . 'includes/reports/class.yith-report-sales-by-date.php';

				if ( ! class_exists( 'YITH_Report_Sales_By_Date' ) && file_exists( $report_class ) ) {
					require_once $report_class;
				}

				if ( class_exists( 'YITH_Report_Sales_By_Date' ) ) {
					$reports = new YITH_Report_Sales_By_Date();
					$reports->calculate_current_range( 'month' );
					$args['report_data']->net_sales = $reports->get_report_data()->orders_net_amount;
					$args                           = $this->dashboard_section_args( $args );
				}
			}

			return $args;
		}

		/**
		 * Filter the account connection template args
		 *
		 * @since  4.0.0
		 * @param array $args An array of template arguments.
		 * @return array
		 */
		public function stripe_connect_account_template_args( $args ) {
			if ( $this->current_user_is_vendor && ! empty( YITH_Frontend_Manager()->gui ) && YITH_Frontend_Manager()->gui->is_main_page() ) {
				$oauth_link                = add_query_arg( array( 'redirect_uri' => yith_wcfm_get_stripe_redirect_uri_for_vendors( true ) ), $args['oauth_link'] );
				$args['oauth_link']        = $oauth_link;
				$args['count_commissions'] = 0;
			}

			return $args;
		}

		/**
		 * Filter dashboard value
		 *
		 * @since  4.0.0
		 * @param array $args An array of report data arguments.
		 * @return array
		 */
		public function filter_dashboard_values( $args ) {
			$current_section = YITH_Frontend_Manager()->gui->get_current_section_obj()->get_id();
			if ( in_array( $current_section, array( 'dashboard', 'reports' ) ) ) {
				if ( $this->vendor && $this->vendor->is_valid() && $this->vendor->has_limited_access() ) {
					$args['where'] = array(
						array(
							'key'      => 'posts.ID',
							'operator' => 'in',
							'value'    => $this->vendor->get_orders(),
						),
					);
				} elseif ( current_user_can( 'manage_woocommerce' ) ) {

					$args['where'] = array(
						array(
							'key'      => 'posts.post_parent',
							'operator' => '=',
							'value'    => 0,
						),
					);
				}
			}

			return $args;
		}

		/**
		 * Add unpaid commissions box
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function show_unpaid_commissions() {
			if ( $this->vendor->is_valid() ) {
				ob_start();
				?>
				<li id="yith-wcfm-dashboard-unpaid-commissions">
					<span class="dashicons dashicons-chart-bar"></span>
					<?php esc_html_e( 'Unpaid commissions', 'yith-woocommerce-product-vendors' ); ?>
					<strong><?php echo wc_price( YITH_Vendors()->commissions->get_unpaid_commissions_amount( $this->vendor->get_id() ) ); // phpcs:ignore ?></strong>
				</li>
				<?php
				echo ob_get_clean(); // phpcs:ignore
			}
		}

		/**
		 * Register style and script
		 *
		 * @since  4.0.0
		 */
		public function register_scripts() {
		}

		/**
		 * Remove Import products on Add product page for vendor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function remove_import_products() {
			wp_add_inline_style( 'yith-wcfm-products', '.yith-wcfm-section-products .woocommerce-BlankState a.woocommerce-BlankState-cta.button:not(.button-primary){display:none !important;}' );
			wp_add_inline_script( 'yith-frontend-manager-product-js', "jQuery( '.woocommerce-BlankState' ).find( 'a.woocommerce-BlankState-cta.button' ).not( '.button-primary'  ).remove();" );
		}

		/**
		 * Only show current vendor's coupon
		 *
		 * @since  4.0.0
		 * @param array $args Current request args.
		 * @return array Modified request.
		 */
		public function filter_coupons_list( $args ) {
			if ( $this->vendor->is_valid() ) {
				$args['author__in'] = $this->vendor->get_admins();
			}

			return $args;
		}

		/**
		 * Get select for product status
		 *
		 * @since  4.0.0
		 * @param array $product_status An array of product status.
		 * @return array Product allowed status.
		 */
		public function allowed_product_status( $product_status ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$is_edit_product = ! empty( $_GET['product_id'] );
			$is_add_product  = empty( $_GET['product_id'] );
			$back_to_review  = 'yes' === get_option( 'yith_wpv_vendors_option_pending_post_status', 'no' );

			if ( ( $is_edit_product && $back_to_review ) || ( $is_add_product && 'yes' !== $this->vendor->get_meta( 'skip_review' ) ) ) {
				$not_allowed = array( 'publish' );
				foreach ( $not_allowed as $remove ) {
					if ( isset( $product_status[ $remove ] ) ) {
						unset( $product_status[ $remove ] );
					}
				}
			}

			return $product_status;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Add vendor tax query arg
		 *
		 * @since  4.0.0
		 * @param array $query_args Array of query arguments.
		 * @return array query args
		 */
		public function filter_product_list( $query_args ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$vendor = $this->vendor;
			if ( ( empty( $vendor ) || ! $this->vendor->is_valid() ) && ! empty( $_GET[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] ) ) {
				$vendor = yith_wcmv_get_vendor( sanitize_textarea_field( wp_unslash( $_GET[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] ) ), 'vendor' );
			}

			if ( $vendor && $vendor->is_valid() ) {
				$vendor_query_args = $vendor->get_query_products_args();
				if ( ! empty( $vendor_query_args['tax_query'] ) ) {
					$query_args['tax_query'] = ! empty( $query_args['tax_query'] ) ? array_merge( $query_args['tax_query'], $vendor_query_args['tax_query'] ) : $vendor_query_args['tax_query']; // phpcs:ignore
				}
			}

			return $query_args;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Check for featured management
		 * Allowed or Disabled for vendor
		 *
		 * @since  4.0.0
		 * @param array $columns The products' table columns.
		 * @return array
		 */
		public function render_product_columns( $columns ) {
			if ( $this->current_user_is_vendor && ! $this->vendor->can_handle_featured_products() ) {
				unset( $columns['featured'] );
			}

			return $columns;
		}

		/**
		 * Check if the current product are assign to vendor or not
		 * If not an error message shown
		 *
		 * @since  1.0
		 * @return bool $check = true if vendor can edit this product, not otherwise
		 */
		public function check_for_vendor_product( $check, $subsection, $section, $atts ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_GET['product_id'] ) && $this->current_user_is_vendor ) {
				$product_id       = absint( $_GET['product_id'] );
				$vendor_id        = $this->vendor->get_id();
				$type             = apply_filters( 'wpml_element_type', YITH_Vendors_Taxonomy::TAXONOMY_NAME );
				$trid             = apply_filters( 'wpml_element_trid', null, $vendor_id, $type );
				$vendors          = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );
				$current_language = apply_filters( 'wpml_current_language', '' );

				if ( ! empty( $vendors[ $current_language ] ) ) {
					$wpml_vendor_args = $vendors[ $current_language ];
					$vendor_id        = $wpml_vendor_args->element_id;
				}

				$check = has_term( $vendor_id, YITH_Vendors_Taxonomy::TAXONOMY_NAME, $product_id );
			} else {
				$section = $atts['section_obj'];
				if ( array_key_exists( 'product', $section->get_current_subsection() ) ) {
					if ( ! $this->vendor->can_add_products() ) {
						add_filter( 'yith_wcfm_restricted_products_section_args', array( $this, 'product_amount_limit_message' ) );
					}
				}
			}

			return $check;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Include specific vendor on product query.
		 *
		 * @since  4.14.0
		 * @param array $query - Args for WP_Query.
		 * @param array $query_vars - Query vars from WC_Product_Query.
		 * @return array modified $query
		 */
		public function get_product_query_by_vendor( $query, $query_args ) {

			if ( $this->current_user_is_vendor ) {
				$query['tax_query'][] = array(
					'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
					'field'    => 'term_id',
					'operator' => 'IN',
					'terms'    => $this->vendor->get_id(),
				);
			}

			return $query;
		}

		/**
		 * Filter vendor product on frontend manager stock report
		 *
		 * @since  4.15.0
		 * @param string $query_from The query FROM.
		 * @return string
		 */
		public function filter_report_stock_query_from( $query_from ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$product_ids = implode( ',', $vendor->get_products() );
				$product_ids = ! empty( $product_ids ) ? $product_ids : -1;
				$query_from .= "AND posts.ID IN ({$product_ids})";
			}

			return $query_from;
		}
	}
}
