<?php
/**
 * YITH Vendors Admin Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin' ) ) {
	/**
	 * Vendor admin class
	 */
	class YITH_Vendors_Admin extends YITH_Vendors_Admin_Legacy {

		/**
		 * Admin panel page
		 *
		 * @const string
		 */
		const PANEL_PAGE = 'yith_wpv_panel';

		/**
		 * YITH_Vendors_Admin_Vendor_Dashboard instance
		 *
		 * @var YITH_Vendors_Admin_Vendor_Dashboard | null
		 */
		protected $vendor_dashboard = null;

		/**
		 * YITH_Vendors_Admin_Order instance
		 *
		 * @var YITH_Vendors_Admin_Orders | null
		 */
		protected $orders = null;

		/**
		 * Products helper class instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Admin_Products|null
		 */
		protected $products = null;

		/**
		 * Coupons helper class instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Admin_Coupons|null
		 */
		protected $coupons = null;

		/**
		 * Current tab handler class instance
		 *
		 * @var mixed
		 */
		protected $tab_handler = null;

		/**
		 * AJAX handler class instance
		 *
		 * @var mixed
		 */
		protected $ajax_handler = null;

		/**
		 * Construct
		 */
		public function __construct() {
			$this->init();
			$this->init_vendor_dashboard();
			$this->register_hooks();
			$this->register_gutenberg_block();
		}

		/**
		 * Init class
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init() {

			YITH_Vendors_Admin_Assets::init();
			YITH_Vendors_Admin_Notices::init();

			new YITH_Vendors_Privacy();

			$this->ajax_handler = new YITH_Vendors_Admin_Ajax();
			$this->orders       = new YITH_Vendors_Admin_Orders();
			$this->products     = new YITH_Vendors_Admin_Products();
			$this->coupons      = new YITH_Vendors_Admin_Coupons();
			// Load class based on current active tab.
			$tab = $this->get_plugin_current_tab();
			if ( $tab ) {
				$class = 'YITH_Vendors_Admin_' . ucfirst( $tab );

				if ( class_exists( $class ) ) {
					$this->tab_handler = new $class();
				}
			}
		}

		/**
		 * Init vendor dashboard
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init_vendor_dashboard() {
			$this->vendor_dashboard = new YITH_Vendors_Admin_Vendor_Dashboard();
		}

		/**
		 * Register class hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_hooks() {
			// Plugin Information.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WPV_PATH . '/' . basename( YITH_WPV_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			// Add admin body class.
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
			// Panel settings.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'admin_menu', array( $this, 'maybe_register_deprecated_panels' ), 15 );
			add_action( 'admin_init', array( $this, 'handle_deprecated_panel_redirect' ), 1 );
			// Redirect edit term link to the new plugin panel.
			add_action( 'admin_init', array( $this, 'maybe_redirect_to_vendor_section' ) );
			// Support to YITH Themes FW 2.0.
			add_filter( 'yit_layouts_taxonomies_list', array( $this, 'add_taxonomy_to_layouts' ) );
			// Custom manage users columns.
			add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );
			// WooCommerce Status Dashboard Widget.
			add_filter( 'woocommerce_dashboard_status_widget_top_seller_query', array( $this, 'dashboard_status_widget_top_seller_query' ) );
			// Add filter products by vendor.
			add_filter( 'woocommerce_products_admin_list_table_filters', array( $this, 'products_admin_list_table_filters' ) );
			// Add custom class to plugin list table section.
			add_filter( 'yith_admin_tab_params', array( $this, 'add_list_tables_wrap_class' ) );
			// Manager custom plugin fields.
			add_filter( 'yith_plugin_fw_inline_fields_allowed_types', array( $this, 'customize_inline_fields_allowed' ), 10, 1 );
			add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'custom_panel_fields' ), 10, 2 );
			// Handle inline percentage symbol for number fields.
			add_action( 'yith_plugin_fw_get_field_number_after', array( $this, 'add_number_field_inline_description' ), 10, 1 );
			// Remove quick edit taxonomy from product.
			add_filter( 'quick_edit_show_taxonomy', array( $this, 'remove_quick_edit_vendor_taxonomy' ), 10, 3 );
			// Regenerate Permalink after panel update.
			add_action( 'yit_panel_wc_before_update', array( $this, 'check_rewrite_rules' ) );
			add_action( 'yit_panel_wc_before_reset', array( $this, 'check_rewrite_rules' ) );
			// Check for vendor's owner.
			add_action( 'admin_notices', array( $this, 'check_vendors_owner' ) );
			add_action( 'yith_wcmv_empty_vendor_object_cache', array( $this, 'delete_check_vendors_owner_transient' ) );
			// JSON Search Vendors using direct request using WooCommerce AJAX system.
			add_action( 'wp_ajax_yith_json_search_vendors', array( $this, 'json_search_vendors' ) );
		}

		/**
		 * Register plugin gutenberg block
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_gutenberg_block() {
			$product_cat           = get_terms( array( 'taxonomy' => 'product_cat' ) );
			$product_cat_gutenberg = array( 'none' => esc_html_x( 'All categories', 'Short Label', 'yith-woocommerce-product-vendors' ) );
			if ( ! is_wp_error( $product_cat ) ) {
				foreach ( $product_cat as $term ) {
					$product_cat_gutenberg[ $term->slug ] = $term->name;
				}
			}

			$blocks = array(
				'yith-wcmv-list'            => array(
					'style'          => 'yith-wc-product-vendors',
					'title'          => _x( 'Vendors List', '[gutenberg]: block name', 'yith-woocommerce-product-vendors' ),
					'description'    => _x( 'Show a list of vendors.', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
					'shortcode_name' => 'yith_wcmv_list',
					'keywords'       => array(
						_x( 'Multi Vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Vendor list', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
					),
					'attributes'     => array(
						'per_page'                => array(
							'type'    => 'number',
							'label'   => _x( 'Number of vendors to show per page', '[gutenberg]: attributes description', 'yith-woocommerce-product-vendors' ),
							'default' => -1,
							'min'     => -1,
							'max'     => 50,
						),
						'include'                 => array(
							'type'    => 'text',
							'label'   => _x( 'Add the vendors\' IDs, comma-separated, to be included. I.E.: 16, 34, 154, 78', '[gutenberg]: attributes description', 'yith-woocommerce-product-vendors' ),
							'default' => '',
						),
						'hide_no_products_vendor' => array(
							'type'    => 'toggle',
							'label'   => _x( 'Vendors without products', '[gutenberg]: attribute description', 'yith-woocommerce-product-vendors' ),
							'default' => false,
							'helps'   => array(
								'checked'   => _x( 'Hide vendors', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
								'unchecked' => _x( 'Show all', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
							),
						),
						'show_description'        => array(
							'type'    => 'toggle',
							'label'   => _x( 'Description', '[gutenberg]: attribute description', 'yith-woocommerce-product-vendors' ),
							'default' => false,
							'helps'   => array(
								'checked'   => _x( 'Show vendor description', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
								'unchecked' => _x( 'Hide vendor description', '[gutenberg]: Help text', 'yith-woocommerce-product-vendors' ),
							),
						),
						'description_lenght'      => array(
							'type'    => 'number',
							'default' => 40,
							'min'     => 5,
							'max'     => apply_filters( 'yith_wcmv_vendor_list_description_max_lenght', 400 ),
						),
						'vendor_image'            => array(
							'type'    => 'select',
							'label'   => _x( 'Which image do you want to use?', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'store'    => _x( 'Store image', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'gravatar' => _x( 'Vendor logo', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
							'default' => 'store',
						),
						'orderby'                 => array(
							'type'    => 'select',
							'label'   => _x( 'Order by: defines the parameter for vendors organization within the list', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'id'          => _x( 'ID', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'name'        => _x( 'Name', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'slug'        => _x( 'Slug', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'description' => _x( 'Description', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
							'default' => 'name',
						),
						'order'                   => array(
							'type'    => 'select',
							'label'   => _x( 'Ascending or descending order?', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'ASC' => _x( 'Ascending', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'DSC' => _x( 'Descending', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
							'default' => 'ASC',
						),
					),
				),
				'yith-wcmv-become-a-vendor' => array(
					'style'          => 'yith-wc-product-vendors',
					'title'          => _x( 'Become a vendor', '[gutenberg]: block name', 'yith-woocommerce-product-vendors' ),
					'description'    => _x( 'Add a form for users to register as vendors.', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
					'shortcode_name' => 'yith_wcmv_become_a_vendor',
					'keywords'       => array(
						_x( 'Become a vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Registration form', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Multi Vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
					),
				),
				'yith-wcmv-vendor-name'     => array(
					'style'          => 'yith-wc-product-vendors',
					'title'          => _x( 'Vendor name', '[gutenberg]: block name', 'yith-woocommerce-product-vendors' ),
					'description'    => _x( 'This shows the name of one vendor. It can also link to the vendor\'s page.', '[gutenberg]: block description', 'yith-woocommerce-product-vendors' ),
					'shortcode_name' => 'yith_wcmv_vendor_name',
					'keywords'       => array(
						_x( 'Vendor name', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
						_x( 'Multi Vendor', '[gutenberg]: keywords', 'yith-woocommerce-product-vendors' ),
					),

					'attributes'     => array(
						'show_by'  => array(
							'type'    => 'select',
							'default' => 'vendor',
							'label'   => _x( 'Get vendor by', '[gutenberg]: Get vendor by name, by products, by user', 'yith-woocommerce-product-vendors' ),
							'options' => array(
								'product' => _x( 'Product ID', '[gutenberg]: block option value', 'yith-woocommerce-product-vendors' ),
								'user'    => _x( 'Owner ID', '[gutenberg]: block option value', 'yith-woocommerce-product-vendors' ),
								'vendor'  => _x( 'Vendor ID', '[gutenberg]: block option value', 'yith-woocommerce-product-vendors' ),
							),
						),
						'value'    => array(
							'type'    => 'text',
							'default' => 0,
							'label'   => _x( 'ID', '[gutenberg]: Option title', 'yith-woocommerce-product-vendors' ),
						),
						'type'     => array(
							'type'    => 'select',
							'label'   => _x( 'Make the vendor name clickable', '[gutenberg]: Option description', 'yith-woocommerce-product-vendors' ),
							'default' => true,
							'options' => array(
								'no'   => _x( 'No', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
								'link' => _x( 'Yes', '[gutenberg]: inspector description', 'yith-woocommerce-product-vendors' ),
							),
						),
						'category' => array(
							'type'    => 'select',
							'label'   => _x( 'Filter products by category', '[gutenberg]: Option description', 'yith-woocommerce-product-vendors' ),
							'default' => '',
							'options' => $product_cat_gutenberg,
						),
					),
				),
			);

			yith_plugin_fw_gutenberg_add_blocks( $blocks );
		}

		/**
		 * Get plugin admin tabs
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_plugin_tabs() {

			$tabs = array();

			if ( current_user_can( 'view_woocommerce_reports' ) ) { // phpcs:ignore
				$tabs['dashboard'] = _x( 'Dashboard', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' );
			}

			$tabs = array_merge(
				$tabs,
				array(
					'commissions'    => _x( 'Commissions', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ) . $this->get_pending_count_html( 'commissions' ),
					'vendors'        => YITH_Vendors_Taxonomy::get_plural_label( 'ucfirst' ) . $this->get_pending_count_html( 'vendors' ),
					'frontend-pages' => _x( 'Store & Product Pages', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ),
					'other'          => array(
						'title'       => _x( 'Other', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'Advanced options for managing your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
				)
			);

			return apply_filters( 'yith_wcmv_admin_panel_tabs', $tabs );
		}

		/**
		 * Get pending count object html. Useful for admin views.
		 *
		 * @since  4.0.0
		 * @param string $type The object type to count.
		 * @return string
		 */
		protected function get_pending_count_html( $type ) {
			$html  = '';
			$count = 0;

			switch ( $type ) {
				case 'commissions':
					$count = count(
						yith_wcmv_get_commissions(
							array(
								'status' => 'pending',
								'fields' => 'ids',
							)
						)
					);
					break;

				case 'vendors':
					$count = count(
						yith_wcmv_get_vendors(
							array(
								'status' => 'pending',
								'fields' => 'ids',
								'number' => -1,
							)
						)
					);
					break;
			}

			if ( ! empty( $count ) ) {
				$html = '<span class="pending-count">' . $count . '</span>';
			}

			return $html;
		}

		/**
		 * Check current request and maybe redirect to vendors section if is edit term request.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function maybe_redirect_to_vendor_section() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			global $pagenow;

			if ( 'term.php' !== $pagenow || ! isset( $_GET['taxonomy'] ) || YITH_Vendors_Taxonomy::TAXONOMY_NAME !== sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) ) {
				return;
			}

			$args = array( 'tab' => 'vendors' );
			if ( isset( $_GET['tag_ID'] ) ) {
				$args['s'] = absint( $_GET['tag_ID'] );
			}

			wp_safe_redirect( yith_wcmv_get_admin_panel_url( $args ) );
			exit;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Get current active tab
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_plugin_current_tab() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! yith_wcmv_is_plugin_panel() ) {
				return '';
			}

			// Get tab from query string if set.
			if ( ! empty( $_GET['tab'] ) ) {
				return sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			}

			$tabs_key = array_keys( $this->get_plugin_tabs() );
			return array_shift( $tabs_key );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Action Links. Add the action links to plugin admin page.
		 *
		 * @since    1.0
		 * @param array $links Links plugin array.
		 * @return array
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, self::PANEL_PAGE, false );
			return $links;
		}

		/**
		 * Define plugin row metas.
		 *
		 * @since    1.0
		 * @param array    $new_row_meta_args An array of plugin row meta.
		 * @param string[] $plugin_meta       An array of the plugin's metadata,
		 *                                    including the version, author,
		 *                                    author URI, and plugin URI.
		 * @param string   $plugin_file       Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data       An array of plugin data.
		 * @param string   $status            Status of the plugin. Defaults are 'All', 'Active',
		 *                                    'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
		 *                                    'Drop-ins', 'Search', 'Paused'.
		 * @param string   $init_file         Plugin init.
		 * @return array
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WPV_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = 'yith-woocommerce-multi-vendor';
			}

			if ( defined( 'YITH_WPV_FREE_INIT' ) && YITH_WPV_FREE_INIT === $plugin_file ) {
				$new_row_meta_args['support'] = array(
					'url' => 'https://wordpress.org/support/plugin/yith-woocommerce-product-vendors',
				);
			}

			return $new_row_meta_args;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @since    1.0
		 * @use      /Yit_Plugin_Panel class
		 * @return   void
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$args = apply_filters(
				'yith_wcmv_plugin_panel_args',
				array(
					'create_menu_page' => true,
					'parent_slug'      => '',
					'page_title'       => 'YITH WooCommerce Multi Vendor',
					'menu_title'       => 'Multi Vendor',
					'capability'       => $this->get_panel_capability(),
					'parent'           => '',
					'parent_page'      => 'yit_plugin_panel',
					'page'             => self::PANEL_PAGE,
					'admin-tabs'       => $this->get_plugin_tabs(),
					'options-path'     => YITH_WPV_PATH . 'plugin-options',
					'class'            => yith_set_wrapper_class(),
					'ui_version'       => 2,
					'plugin_slug'      => YITH_WPV_SLUG,
					'is_premium'       => defined( 'YITH_WPV_PREMIUM' ),
					'plugin_icon'      => YITH_WPV_ASSETS_URL . '/images/plugins/multi-vendor.svg',
					'your_store_tools' => $this->get_store_tools_tab_options(),
					'help_tab'         => $this->get_help_tab_options(),
					'welcome_modals'   => $this->get_welcome_modals_options(),
				)
			);

			// Add icon to tabs if missing.
			foreach ( $args['admin-tabs'] as $tab_key => &$tab ) {
				$options = is_array( $tab ) ? $tab : array( 'title' => $tab );
				if ( empty( $options['icon'] ) ) {
					$options['icon'] = yith_wcmv_get_panel_item_icon( $tab_key );
				}

				$tab = $options;
			}

			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WPV_PATH . 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Get admin panel capability
		 *
		 * @since  4.0.0
		 * @return string
		 */
		protected function get_panel_capability() {
			return apply_filters( 'yith_wcmv_plugin_panel_capability', 'manage_options' );
		}

		/**
		 * Get panel help tab options
		 *
		 * @since 5.0.0
		 * @return array
		 */
		protected function get_help_tab_options() {
			return array(
				'main_video' => array(
					'desc' => _x( 'Check this video to learn how to <b>create a registration page for vendors</b>', '[HELP TAB] Video title', 'yith-woocommerce-product-vendors' ),
					'url'  => array(
						'en' => 'https://www.youtube.com/embed/YjVcpV3fyAA?si=tlw-HCG3ivNZBu0x',
						'it' => 'https://www.youtube.com/embed/Bhqyhx9tm0s?si=CEH0GF5TaO2zAqdY',
						'es' => 'https://www.youtube.com/embed/C4Zrj9Q0B7g?si=IQ3YwJnZdNHKJh3u',
					),
				),
				'playlists'  => array(
					'en' => 'https://www.youtube.com/playlist?list=PLDriKG-6905ml-hTvRAK9XLsGQMJ9FdSC',
					'it' => 'https://www.youtube.com/playlist?list=PL9Ka3j92PYJOgDy7iEYaaa_eQZmToRjNd',
					'es' => 'https://www.youtube.com/playlist?list=PL9c19edGMs08SImdWPM_Y6gPT06WaYucs',
				),
				'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003474378-YITH-WOOCOMMERCE-MULTI-VENDOR',
				'doc_url'    => 'https://docs.yithemes.com/yith-woocommerce-multi-vendor/',
			);
		}

		/**
		 * Get panel store tools tab options
		 *
		 * @since 5.0.0
		 * @return array
		 */
		protected function get_store_tools_tab_options() {
			if ( file_exists( YITH_WPV_PATH . 'plugin-options/store-tools.php' ) ) {
				$store_tools = include YITH_WPV_PATH . 'plugin-options/store-tools.php';
			}

			return array(
				'items' => $store_tools ?? array(),
			);
		}

		/**
		 * Get welcome modals options
		 *
		 * @since 5.0.0
		 * @return array
		 */
		protected function get_welcome_modals_options() {
			return array(
				'on_close' => function () {
					update_option( 'yith_wcmv_welcome_modal', YITH_WPV_VERSION );
				},
				'modals'   => array(
					'welcome' => array(
						'type'        => 'welcome',
						'description' => __( 'The All-In-One solution to turn your shop into a powerful marketplace', 'yith-woocommerce-product-vendors' ),
						'show'        => empty( get_option( 'yith_wcmv_welcome_modal', null ) ),
						'items'       => array(
							'documentation' => array(
								'url' => 'https://docs.yithemes.com/yith-woocommerce-multi-vendor/',
							),
							'how-to-video'  => array(
								'url' => array(
									'en' => 'https://www.youtube.com/watch?v=ige46HhMDBA',
									'it' => 'https://www.youtube.com/watch?v=r-29V2heT_4',
									'es' => 'https://www.youtube.com/watch?v=_ImWDNAJ6-k',
								),
							),
							'feature'       => array(
								'title'       => __( 'Create a custom registration form for <mark>your vendors</mark>', 'yith-woocommerce-product-vendors' ),
								'description' => __( 'and embark on this new adventure!', 'yith-woocommerce-product-vendors' ),
								'url'         => yith_wcmv_get_admin_panel_url(
									array(
										'page'    => self::PANEL_PAGE,
										'tab'     => 'vendors',
										'sub_tab' => 'vendors-registration',
									),
								),
							),
						),
					),
				),
			);
		}

		/**
		 * Add an extra body classes for vendors dashboard
		 *
		 * @since  1.5.1
		 * @param string $admin_body_classes Admin body classes.
		 * @return string
		 */
		public function admin_body_class( $admin_body_classes ) {
			global $post, $current_screen;

			$order_screen_id   = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
			$vendor            = yith_wcmv_get_vendor( 'current', 'user' );
			$is_order_details  = is_admin() && ! empty( $current_screen ) && $order_screen_id === $current_screen->id;
			$order   		   = $post instanceof WP_Post ? wc_get_order( $post->ID ) : ( isset( $_GET['id'] ) ? wc_get_order( absint( $_GET['id'] ) ) : false ); // phpcs:ignore
			$refund_management = 'yes' === get_option( 'yith_wpv_vendors_option_order_refund_synchronization', 'no' );
			$quote_management  = 'yes' === get_option( 'yith_wpv_vendors_enable_request_quote', 'no' );

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$admin_body_classes .= ' vendor_limited_access';
				if ( $is_order_details && $refund_management ) {
					$admin_body_classes .= ' vendor_refund_management';
				}

				if ( function_exists( 'YITH_Vendors_Request_Quote' ) && $quote_management && $order && YITH_Vendors_Request_Quote()->has_valid_quote_status( $order ) ) {
					$admin_body_classes .= ' vendor_quote_management';
				}
			} elseif ( current_user_can( 'manage_woocommerce' ) ) {
				$admin_body_classes .= ' vendor_super_user';

				if ( $order && $order->get_parent_id() && $is_order_details ) {
					$admin_body_classes .= ' vendor_suborder_detail';
				}
			}

			if ( yith_wcmv_is_plugin_panel() ) {
				$admin_body_classes .= ' section-' . self::PANEL_PAGE;
			}

			return $admin_body_classes;
		}

		/**
		 * Handles the output of the roles column on the `users.php` screen.
		 *
		 * @since  1.0.0
		 * @param string  $output  The column output.
		 * @param string  $column  Current column.
		 * @param integer $user_id Current user ID.
		 * @return string
		 */
		public function manage_users_custom_column( $output, $column, $user_id ) {
			if ( 'roles' === $column ) {
				global $wp_roles;

				$user       = new WP_User( $user_id );
				$user_roles = array();
				$output     = esc_html__( 'None', 'yith-woocommerce-product-vendors' );

				if ( is_array( $user->roles ) ) {
					foreach ( $user->roles as $role ) {
						$user_roles[] = translate_user_role( $wp_roles->role_names[ $role ] );
					}
					$output = join( ', ', $user_roles );
				}
			}

			return $output;
		}

		/**
		 * Adds custom columns to the `users.php` screen.
		 *
		 * @since  1.0.0
		 * @param array $columns The table columns array.
		 * @return array
		 */
		public function manage_users_columns( $columns ) {
			// Unset the core WP `role` column.
			if ( isset( $columns['role'] ) ) {
				unset( $columns['role'] );
			}

			// Add our new roles column.
			$columns['roles'] = esc_html__( 'Roles', 'yith-woocommerce-product-vendors' );

			return $columns;
		}

		/**
		 * Add vendor taxonomy to YITH Theme fw 2.0 in layouts section
		 *
		 * @since  1.8.1
		 * @param array $taxonomies Array of taxonomies.
		 * @return mixed Taxonomies array
		 */
		public function add_taxonomy_to_layouts( $taxonomies ) {
			$taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] = get_taxonomy( YITH_Vendors_Taxonomy::TAXONOMY_NAME );

			return $taxonomies;
		}

		/**
		 * Get panel object
		 *
		 * @return YIT_Plugin_Panel_Woocommerce|null
		 */
		public function get_panel() {
			return $this->panel;
		}

		/**
		 * Get orders class object
		 *
		 * @since  4.0.0
		 * @return YITH_Vendors_Admin_Orders|null
		 */
		public function get_orders_handler() {
			return $this->orders;
		}

		/**
		 * Get products class object
		 *
		 * @since  4.0.0
		 * @return object|null
		 */
		public function get_products_handler() {
			return $this->products;
		}

		/**
		 * Get orders class object
		 *
		 * @since  4.0.0
		 * @return YITH_Vendors_Admin_Vendor_Dashboard|null
		 */
		public function get_vendor_dashboard_handler() {
			return $this->vendor_dashboard;
		}

		/**
		 * Get AJAX handler class object
		 *
		 * @since  4.0.0
		 * @return YITH_Vendors_Admin_Ajax|null
		 */
		public function get_ajax_handler() {
			return $this->ajax_handler;
		}

		/**
		 * Get current settings tab class object if any
		 *
		 * @since  4.0.0
		 * @return object|null
		 */
		public function get_current_tab_handler() {
			return $this->tab_handler;
		}

		/**
		 * Filter TopSeller query for WooCommerce Dashboard Widget
		 *
		 * @since  1.9.16
		 * @param array $query Widget query array.
		 * @return array
		 */
		public function dashboard_status_widget_top_seller_query( $query ) {
			$query['where'] .= 'AND posts.post_parent = 0';

			return $query;
		}

		/**
		 * Add a filter by vendor in products list in admin area only if current user can manager WooCommerce.
		 *
		 * @param array $filters Array of enabled filters.
		 * @return array
		 */
		public function products_admin_list_table_filters( $filters ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$tax_name             = YITH_Vendors_Taxonomy::TAXONOMY_NAME;
				$filters[ $tax_name ] = array( $this, 'render_products_category_filter' );
			}

			return $filters;
		}

		/**
		 * Filter by Vendor render on products page in admin
		 *
		 * @return void
		 */
		public function render_products_category_filter() {
			global $wp_query;

			$taxonomy_label = YITH_Vendors_Taxonomy::get_taxonomy_labels();
			$taxonomy_name  = YITH_Vendors_Taxonomy::TAXONOMY_NAME;

			$args = array(
				'pad_counts'         => 1,
				'show_count'         => 1,
				'hierarchical'       => 1,
				'hide_empty'         => 1,
				'show_uncategorized' => 1,
				'orderby'            => 'name',
				'selected'           => isset( $wp_query->query_vars[ $taxonomy_name ] ) ? $wp_query->query_vars[ $taxonomy_name ] : '',
				// translators: %s stand for the vendor taxonomy singular name.
				'show_option_none'   => sprintf( __( 'No %s' ), strtolower( $taxonomy_label['singular_name'] ) ),
				'option_none_value'  => '',
				'value_field'        => 'slug',
				'taxonomy'           => $taxonomy_name,
				'name'               => $taxonomy_name,
				'class'              => 'dropdown_product_vendor',
			);

			if ( 'order' === $args['orderby'] ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'order'; // phpcs:ignore
			}

			wp_dropdown_categories( $args );
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return 'https://yithemes.com/themes/plugins/yith-woocommerce-multi-vendor/';
		}

		/**
		 * Add list table section wrap class to get plugin FW style
		 *
		 * @since  4.0.0
		 * @param array $args The page arguments.
		 * @return array
		 */
		public function add_list_tables_wrap_class( $args ) {
			if ( self::PANEL_PAGE !== $args['page'] ) {
				return $args;
			}

			if ( in_array( $args['current_tab'], array( 'vendors', 'commissions' ), true ) && ( empty( $args['current_sub_tab'] ) || in_array( $args['current_sub_tab'], array( 'vendors-list', 'commissions-list' ), true ) ) ) {
				$args['wrap_class'] .= ' yith-plugin-ui--classic-wp-list-style yith-plugin-fw-wp-page-wrapper';
			}

			return $args;
		}

		/**
		 * Filter custom plugin panel location
		 *
		 * @since  4.0.0
		 * @param string $field_template THe field default template location.
		 * @param array  $field          The field to load.
		 * @return string
		 */
		public function custom_panel_fields( $field_template, $field ) {
			$allowed_types = apply_filters( 'yith_wcmv_allowed_custom_panel_fields', array( 'ajax-vendors', 'vendor-registration-table', 'options-table', 'price', 'upload-attachment' ) );
			if ( isset( $field['type'] ) && in_array( $field['type'], $allowed_types, true ) ) {
				$field_template = YITH_WPV_PATH . 'includes/admin/views/fields/' . $field['type'] . '.php';
			}
			return $field_template;
		}

		/**
		 * Customize inline fields allowed types
		 *
		 * @since  4.0.0
		 * @param array $allowed An array of allowed types.
		 * @return array
		 */
		public function customize_inline_fields_allowed( $allowed ) {
			if ( yith_wcmv_is_plugin_panel() ) {
				$allowed[] = 'price';
			}
			return $allowed;
		}

		/**
		 * Add percentage symbol for special number fields.
		 *
		 * @since  4.0.0
		 * @param array $field The fields' data.
		 * @return void
		 */
		public function add_number_field_inline_description( $field ) {
			if ( ! empty( $field['inline_description'] ) ) {
				echo '<span class="inline-description">' . esc_html( $field['inline_description'] ) . '</span>';
			}
		}

		/**
		 * Remove quick edit vendor taxonomy for vendor
		 *
		 * @since  4.0.0
		 * @param boolean $show_in_quick_edit Whether to show the current taxonomy in Quick Edit.
		 * @param string  $taxonomy_name      Taxonomy name.
		 * @param string  $post_type          Post type of current Quick Edit post.
		 * @return boolean
		 */
		public function remove_quick_edit_vendor_taxonomy( $show_in_quick_edit, $taxonomy_name, $post_type ) {
			if ( YITH_Vendors_Taxonomy::TAXONOMY_NAME === $taxonomy_name && 'product' === $post_type ) {
				return false;
			}
			return $show_in_quick_edit;
		}

		/**
		 * Check if needs to refresh rewrite rules for frontpage
		 *
		 * @since    4.0.0
		 * @return void
		 */
		public function check_rewrite_rules() {
			if ( ! empty( $_POST['yith_wpv_vendor_taxonomy_rewrite'] ) && get_option( 'yith_wpv_vendor_taxonomy_rewrite', '' ) !== sanitize_text_field( wp_unslash( $_POST['yith_wpv_vendor_taxonomy_rewrite'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				update_option( 'yith_wcmv_flush_rewrite_rules', true );
			}
		}

		/**
		 * Check for vendor without owner
		 *
		 * @since    1.6
		 * @return  void
		 */
		public function check_vendors_owner() {
			if ( ! yith_wcmv_is_plugin_panel() || ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			$vendors = get_transient( 'yith_wcmv_check_vendors_owner_cache' );
			if ( false === $vendors ) {
				$vendors = YITH_Vendors_Factory::query( array( 'owner' => '' ) );
				set_transient( 'yith_wcmv_check_vendors_owner_cache', $vendors );
			}

			if ( empty( $vendors ) ) {
				return;
			}

			?>
			<div class="notice notice-warning">
				<p>
					<?php
					// translators: %d is the number of vendors with no owner.
					echo wp_kses_post( sprintf( __( '<strong>Warning</strong>: no owner(s) set on %d vendor shop(s). Please, set an owner for each vendor shop in order to enable the shop(s).', 'yith-woocommerce-product-vendors' ), count( $vendors ) ) );
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Delete transient on vendor object cache reset
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function delete_check_vendors_owner_transient() {
			delete_transient( 'yith_wcmv_check_vendors_owner_cache' );
		}

		/**
		 * Check and get the revision message for vendors
		 *
		 * @param boolean|YITH_Vendor $vendor  Current vendor object.
		 * @param boolean             $terms   If terms must be accepted.
		 * @param boolean             $privacy If privacy policy must be accepted.
		 * @return string
		 */
		public function get_revision_message( $vendor = false, $terms = false, $privacy = false ) {

			if ( ! $vendor ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( ! $vendor || ! $vendor->is_valid() ) {
					return '';
				}

				$terms   = ! yith_wcmv_is_terms_and_conditions_required() || $vendor->has_terms_and_conditions_accepted();
				$privacy = ! yith_wcmv_is_privacy_policy_required() || $vendor->has_privacy_policy_accepted();

			}

			if ( $terms && $privacy ) {
				return '';
			}

			$action       = get_option( 'yith_wpv_manage_terms_and_privacy_revision_actions', 'no_action' );
			$endpoint_url = wc_get_account_endpoint_url( 'terms-of-service' );
			// Get pages title.
			$terms_page_title   = $terms ? get_the_title( get_option( 'yith_wpv_terms_and_conditions_page_id', 0 ) ) : '';
			$privacy_page_title = $privacy ? get_the_title( get_option( 'yith_wpv_privacy_page', 0 ) ) : '';

			switch ( $action ) {
				case 'disable_now':
					if ( $terms && $privacy ) {
						// translators: %1$s stand for terms and conditions page title, %2$s stand for the privacy policy page title.
						$message = sprintf( __( 'The %1$s and %2$s have been modified and your profile has been disabled for sale. To reactivate it, please accept our terms of service and privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title, $terms_page_title );
					} elseif ( $terms ) {
						// translators: %s stand for terms and conditions page title.
						$message = sprintf( __( 'The %s have been modified and your profile has been disabled for sale. To reactivate it, please accept our terms of service again from this page', 'yith-woocommerce-product-vendors' ), $terms_page_title );
					} elseif ( $privacy ) {
						// translators: %s stand for privacy policy page title.
						$message = sprintf( __( 'The %s has been modified and your profile has been disabled for sale. To reactivate it, please accept our privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title );
					}
					break;

				case 'disable_after':
					// Get last modified.
					$last_terms_modified   = $terms ? strtotime( YITH_Vendors()->get_last_modified_data_terms_and_conditions() ) : 0;
					$last_privacy_modified = $privacy ? strtotime( YITH_Vendors()->get_last_modified_data_privacy_policy() ) : 0;
					$days                  = 'disable_after' === $action ? get_option( 'yith_wpv_manage_terms_and_privacy_revision_disable_after', 3 ) : 0;

					$max_last_modified  = max( $last_terms_modified, $last_privacy_modified );
					$today              = current_time( 'Y-m-d' );
					$max_last_modified += ( $days * DAY_IN_SECONDS );

					$datetime1     = new DateTime( $today );
					$datetime2     = new DateTime( date( 'Y-m-d', $max_last_modified ) ); // phpcs:ignore
					$disable_after = $datetime1->diff( $datetime2 )->d;

					if ( $terms && $privacy ) {
						// translators: %1$s stand for terms and conditions page title, %2$s stand for the privacy policy page title, %3$s stand for the days' interval.
						$message = sprintf( __( 'The %1$s and %2$s have been modified and your profile will be disabled in %3$s days. To reactivate it, please accept our terms of service and privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title, $terms_page_title, $disable_after );
					} elseif ( $terms ) {
						// translators: %1$s stand for terms and conditions page title, %2$s stand for the days' interval.
						$message = sprintf( __( 'The %1$s have been modified and your profile will be disabled in %2$s days. To reactivate it, please accept our terms of service again from this page', 'yith-woocommerce-product-vendors' ), $terms_page_title, $disable_after );
					} elseif ( $privacy ) {
						// translators: %1$s stand for privacy policy page title, %2$s stand for the days' interval.
						$message = sprintf( __( 'The %1$s has been modified and your profile will be disabled in %2$s days. To reactivate it, please accept our privacy policy again from this page', 'yith-woocommerce-product-vendors' ), $privacy_page_title, $disable_after );
					}
					break;

				case 'no_action':
					break;
			}

			return ! empty( $message ) ? sprintf( '%s <a href="%s">%s</a>', $message, $endpoint_url, $endpoint_url ) : '';
		}

		/**
		 * Print the error message on admin area for vendors
		 *
		 * @since  4.0.0
		 */
		public function print_check_revision_message() {

			// Get the current vendor.
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( ! $vendor || ! $vendor->is_valid() ) {
				return;
			}

			$terms_must_be_accepted   = yith_wcmv_is_terms_and_conditions_required() && ! $vendor->has_terms_and_conditions_accepted();
			$privacy_must_be_accepted = yith_wcmv_is_privacy_policy_required() && ! $vendor->has_privacy_policy_accepted();
			if ( ! $terms_must_be_accepted && ! $privacy_must_be_accepted ) {
				return;
			}

			$message = $this->get_revision_message( $vendor, $terms_must_be_accepted, $privacy_must_be_accepted );
			if ( ! empty( $message ) ) {
				?>
				<div class="notice notice-error">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * JSON search for vendors using the WooCommerce AJAX system.
		 * Backward compatibility with old system.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function json_search_vendors() {
			check_ajax_referer( 'search-products', 'security' );

			$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $term ) ) {
				die();
			}

			$vendors = YITH_Vendors_Factory::search( $term );
			wp_send_json( apply_filters( 'yith_wcmv_json_search_found_vendors', $vendors ) );
		}
	}
}
