<?php
/**
 * Legacy class for YITH Vendors Admin.
 * This class includes all deprecated methods and arguments that are going to be removed on future release.
 *
 * @since      4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

// phpcs:disable WordPress.Security.NonceVerification

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


if ( ! class_exists( 'YITH_Vendors_Admin_Legacy' ) ) {
	/**
	 * Legacy class for YITH Vendors Admin
	 */
	abstract class YITH_Vendors_Admin_Legacy {

		/**
		 * YIT_Plugin_Panel_Woocommerce instance
		 *
		 * @var YIT_Plugin_Panel_Woocommerce|null
		 */
		protected $panel = null;

		/**
		 * Vendor panel page for vendors
		 *
		 * @var string
		 * @deprecated
		 */
		public $vendor_panel_page = 'yith_vendor_settings';

		/**
		 * Get deprecated panels
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_deprecated_panels() {
			$panels = array(
				'commissions' => array(
					'title'     => __( 'Commissions', 'yith-woocommerce-product-vendors' ),
					'icon'      => 'dashicons-tickets',
					'page-icon' => YITH_WPV_ASSETS_URL . '/icons/commission.svg',
					'position'  => '58.1',
				),
			);

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$panels['dashboard'] = array(
					'title'     => __( 'Sales Report', 'yith-woocommerce-product-vendors' ),
					'icon'      => 'dashicons-chart-bar',
					'page-icon' => YITH_WPV_ASSETS_URL . '/icons/report.svg',
					'position'  => '56.5',
				);
			} else {
				$panels['vendors'] = array(
					'title'     => __( 'Vendors', 'yith-woocommerce-product-vendors' ),
					'icon'      => 'dashicons-admin-multisite',
					'page-icon' => YITH_WPV_ASSETS_URL . '/icons/store-alt.svg',
					'position'  => '56.5',
				);
			}

			return $panels;
		}

		/**
		 * Maybe register deprecated panel for first 4.0 installation
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function maybe_register_deprecated_panels() {
			$option = get_option( 'yith_wcmv_legacy_admin_panels', null );
			if ( is_null( $option ) ) {
				return;
			}

			$user   = get_current_user_id();
			$panels = $this->get_deprecated_panels();
			foreach ( $panels as $key => $panel ) {

				if ( isset( $option[ $key ] ) && in_array( $user, $option[ $key ] ) ) { //phpcs:ignore
					continue;
				}

				add_menu_page(
					$panel['title'],
					$panel['title'],
					$this->get_panel_capability(),
					'yith_wpv_deprecated_panel_' . $key,
					array( $this, 'output_deprecated_panel' ),
					$panel['icon'],
					$panel['position']
				);
			}
		}

		/**
		 * Output the deprecated panel
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function output_deprecated_panel() {
			$panel_id = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$panel_id = str_replace( 'yith_wpv_deprecated_panel_', '', $panel_id );
			$panels   = $this->get_deprecated_panels();
			if ( empty( $panel_id ) || ! isset( $panels[ $panel_id ] ) ) {
				return;
			}

			// Is vendor?
			$vendor              = yith_wcmv_get_vendor( 'current', 'user' );
			$is_vendor_dashboard = $vendor && $vendor->is_valid() && $vendor->has_limited_access();

			$panel = $panels[ $panel_id ];
			wp_enqueue_style( 'yith-plugin-ui' ); // Make sure style is enqueued.

			echo '<div id="yith_wpv_deprecated_panel_wrapper" class="yith-plugin-ui">';
			echo '<style>#yith_wpv_deprecated_panel_wrapper{background:#fff;margin:20px;margin-left:0;padding: 50px;}.yith-plugin-fw__list-table-blank-state{max-width:800px;margin:0 auto;}</style>';
			yith_plugin_fw_get_component(
				array(
					'type'     => 'list-table-blank-state',
					'icon_url' => $panel['page-icon'],
					'message'  => sprintf(
					// translators: 1. plugin version; 2. plugin name; 3. menu name with link.
						esc_html__( 'Since version %1$s of %2$s we moved all settings to a new panel that you can find in %3$s, so you can access to all plugin settings from there.', 'yith-woocommerce-product-vendors' ),
						'<strong>4.0.0</strong>',
						'<strong>YITH WooCommerce Multi Vendor</strong>',
						'<strong>' . ( $is_vendor_dashboard ? _x( 'Your shop', '[Admin]Vendor dashboard menu menu title', 'yith-woocommerce-product-vendors' ) : 'YITH > Multi Vendor' ) . '</strong>'
					),
					'cta'      => array(
						'title' => __( 'Go to the new panel', 'yith-woocommerce-product-vendors' ),
						'url'   => add_query_arg(
							array(
								'action' => 'yith_wcmv_deprecated_panel_redirect',
								'_nonce' => wp_create_nonce( 'yith_wcmv_deprecated_panel_redirect' ),
								'panel'  => $panel_id,
							),
							admin_url()
						),
					),
				)
			);
			echo '</div>';
		}

		/**
		 * Handle deprecated panel redirect
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_deprecated_panel_redirect() {
			if ( ! isset( $_GET['action'], $_GET['_nonce'], $_GET['panel'] ) || 'yith_wcmv_deprecated_panel_redirect' !== sanitize_text_field( wp_unslash( $_GET['action'] ) )
				|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_nonce'] ) ), 'yith_wcmv_deprecated_panel_redirect' ) ) {
				return;
			}

			$panel  = sanitize_text_field( wp_unslash( $_GET['panel'] ) );
			$panels = $this->get_deprecated_panels();
			if ( ! isset( $panels[ $panel ] ) ) {
				$panel = array_key_first( $panels );
			}

			$option             = get_option( 'yith_wcmv_legacy_admin_panels', array() );
			$option[ $panel ][] = get_current_user_id();
			update_option( 'yith_wcmv_legacy_admin_panels', $option );

			wp_safe_redirect( yith_wcmv_get_admin_panel_url( array( 'tab' => $panel ) ) );
			exit;
		}

		/**
		 * Get admin panel capability
		 *
		 * @since  4.0.0
		 * @return string
		 */
		protected function get_panel_capability() {
			return 'manage_options';
		}

		/**
		 * Allow/Disable WooCommerce add new post type creation
		 *
		 * @since    1.2.0
		 * @return void
		 */
		public function add_new_link_check() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Remove add new post types button
		 *
		 * @since    1.6.0
		 * @return void
		 */
		public function remove_add_new_button() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Add items to dashboard menu
		 *
		 * @since  1.0.0
		 * @return void
		 * @deprecated
		 */
		public function menu_items() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Enqueue Style and Scripts
		 *
		 * @since  1.0
		 * @return void
		 * @deprecated
		 */
		public function enqueue_scripts() {
			YITH_Vendors_Admin_Assets::register_assets();
			YITH_Vendors_Admin_Assets::enqueue_assets();
		}

		/**
		 * Check for order capabilities
		 * Add or remove vendor capabilities for coupon and review management
		 *
		 * @since  1.3
		 * @return void
		 * @deprecated
		 */
		public function manage_caps() {
			_deprecated_function( __METHOD__, '4.0.0' );
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
		}

		/**
		 * Add items to dashboard menu
		 *
		 * @since  1.0.0
		 * @return void
		 * @deprecated
		 */
		public function vendor_settings() {
			_deprecated_function( __METHOD__, '4.0.0' );
			$this->register_panel();
		}

		/**
		 * Remove Posts From WP Menu Dashboard
		 *
		 * @since  1.3
		 * @return void
		 */
		public function remove_posts_page() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Add Report Menu
		 *
		 * @since  3.7.5
		 * @return void
		 */
		public function report_menu() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Get vendors admin array for select2
		 *
		 * @since  1.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return array
		 * @deprecated
		 */
		public function format_vendor_admins_for_select2( $vendor = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_format_vendor_admins_for_select2' );

			return yith_wcmv_format_vendor_admins_for_select2( $vendor );
		}

		/**
		 * Return the protected attribute capability for vendor menu
		 *
		 * @since    1.8.4
		 * @return string The vendor menu capability
		 * @deprecated
		 */
		public function get_special_cap() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::ROLE_ADMIN_CAP' );

			return YITH_Vendors_Capabilities::ROLE_ADMIN_CAP;
		}

		/**
		 * Restore the Orders menu if missing
		 *
		 * @since  3.7.3
		 * @return void
		 * @deprecated
		 */
		public function add_shop_orders_menu_item() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Suppress the WooCommerce connect store notice.
		 * WooCommerce add a notice if you are using one of their plugins.
		 * Hide this information to the vendor.
		 *
		 * @since  3.7
		 * @param boolean $suppress The current value. True by default.
		 * @return bool True to suppress it, false otherwise
		 * @deprecated
		 */
		public function suppress_connect_notice( $suppress ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $suppress;
		}

		/**
		 * Check if current page is the vendor taxonomy page (in admin)
		 *
		 * @since  1.7
		 * @return bool
		 * @deprecated
		 */
		public function is_vendor_tax_page() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_is_plugin_panel' );

			return yith_wcmv_is_plugin_panel( 'vendors' );
		}

		/**
		 * Check if current page is the vendor dashboard page (in admin)
		 *
		 * @since  1.8.4
		 * @return bool
		 * @deprecated
		 */
		public function is_vendor_dashboard() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_is_plugin_panel' );

			return yith_wcmv_is_plugin_panel();
		}

		/**
		 * Suppress the WordPress update notice
		 *
		 * @since  3.7
		 * @return void
		 * @deprecated
		 */
		public function remove_update_nag() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Print the Single Taxonomy Metabox
		 *
		 * @since  1.0.0
		 * @param string $taxonomy     Taxonomy Name.
		 * @param string $taxonomy_box Taxonomy Box.
		 * @return void
		 * @deprecated
		 */
		public static function single_taxonomy_meta_box( $taxonomy = '', $taxonomy_box = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Taxonomy::single_taxonomy_meta_box' );
			YITH_Vendors_Taxonomy::single_taxonomy_meta_box();
		}

		/**
		 * Add a new attribute via ajax function.
		 *
		 * @since  2.3.2
		 * @return void
		 * @use    admin_notices hooks
		 * @deprecated
		 */
		public static function add_new_attribute() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * If a user is a vendor admin remove the woocommerce prevent admin access
		 *
		 * @since  1.0.0
		 * @param boolean $prevent_access True if admin access is forbidden, false otherwise.
		 * @return boolean
		 * @deprecated
		 */
		public function prevent_admin_access( $prevent_access ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $prevent_access;
		}

		/**
		 * Hakc WooCommerce order count
		 *
		 * @since  1.9.16
		 * @return void
		 * @deprecated
		 */
		public function count_processing_order() {
			_deprecated_function( __METHOD__, '4.0.0' );
			$this->orders->count_orders();
		}

		/**
		 * Quick Edit output render
		 *
		 * @param string $column_name The column name.
		 * @param string $post_type   The post type.
		 */
		public function quick_edit_render( $column_name, $post_type ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( ! empty( $this->products ) ) {
				$this->products->quick_edit_render();
			}
		}

		/**
		 * Add input hidden with vendor id
		 *
		 * @param string         $col_name The column name.
		 * @param string|integer $post_id  The post ID.
		 */
		public function manage_product_vendor_tax_column( $col_name, $post_id ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Add commission tab in single product "Product Data" section
		 *
		 * @deprecated
		 */
		public function single_product_commission_tab( $product_data_tabs ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( ! empty( $this->products ) ) {
				return $this->products->commission_tab();
			}

			return $product_data_tabs;
		}

		/**
		 * Add commission tab in single product "Product Data" section
		 *
		 * @deprecated
		 */
		public function single_product_commission_content() {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( ! empty( $this->products ) ) {
				$this->products->commission_tab_content();
			}
		}

		/**
		 * Save product commission rate meta
		 *
		 * @since  1.0
		 * @param integer $post_id The post id.
		 * @param WP_Post $post    The post object.
		 * @return void
		 * @deprecated
		 */
		public function save_product_commission_meta( $post_id, $post ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( ! empty( $this->products ) ) {
				$this->products->commission_tab_save( $post_id, $post );
			}
		}

		/**
		 * Add options tab
		 *
		 * @since 1.0.0
		 * @param string $current_tab Current tab key.
		 * @return void
		 * @deprecated
		 */
		public function show_sections( $current_tab ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Check for featured management
		 * Allowed or Disabled for vendor
		 *
		 * @since  1.3
		 * @param array $columns The product column name.
		 * @return array
		 * @deprecated
		 */
		public function render_product_columns( $columns ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $columns;
		}

		/**
		 * Set product to pending status
		 * If the vendor haven't the skip admin cap, the product will be set to
		 * pending review after any changed
		 *
		 * @since       1.9.13
		 * @param integer     $post_id        The post ID.
		 * @param WP_Post     $post           The post object.
		 * @param YITH_Vendor $current_vendor The current vendor object.
		 * @return      void
		 * @deprecated
		 */
		public function set_product_to_pending_review_after_edit( $post_id, $post, $current_vendor ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Set product to pending status
		 * If the vendor haven't the skip admin cap, the product will be set to
		 * pending review after save attributes or save variations action
		 *
		 * @since  2.0.8
		 * @return void
		 * @deprecated
		 */
		public function set_product_to_pending_review_after_ajax_save() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Add a bubble notification icon for pending products
		 *
		 * @since    1.5.1
		 * @deprecated
		 */
		public function products_to_approve() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Add vendor widget dashboard
		 *
		 * @since  1.3
		 * @return void
		 */
		public function add_dashboard_widgets() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Vendor Recent Comments Widgets
		 *
		 * @since  1.3
		 * @return bool
		 */
		public function vendor_recent_comments_widget() {
			_deprecated_function( __METHOD__, '4.0.0' );

			return false;
		}

		/**
		 * Vendor Recent Reviews Widgets
		 *
		 * @since  1.3
		 * @return void
		 */
		public function vendor_recent_reviews_widget() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * When searching using the WP_User_Query, search names (user meta) too
		 *
		 * @param object $query The current query object.
		 * @return void
		 * @see WP_User_Query Class wp-includes/user.php
		 * @deprecated
		 */
		public function json_search_customer_name( $query ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Filter admin-ajax url
		 *
		 * @since  1.8.4
		 * @param string $url The admin url.
		 * @return string   Filter admin url
		 * @deprecated
		 */
		public function admin_url( $url ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $url;
		}

		/**
		 * Check for duplicate vendor name
		 *
		 * @since    1.0
		 * @param string $term     The term name.
		 * @param string $taxonomy The taxonomy name.
		 * @return mixed term object | WP_Error
		 * @deprecated
		 */
		public function check_duplicate_term_name( $term, $taxonomy ) {

			_deprecated_function( __METHOD__, '4.0.0' );

			if ( apply_filters( 'yith_wcmv_skip_check_duplicate_term_name', false ) || ! empty( $_REQUEST['icl_translation_of'] ) ) {
				return $term;
			}

			if ( YITH_Vendors_Taxonomy::TAXONOMY_NAME !== $taxonomy ) {
				return $term;
			}

			if ( 'edit_terms' === current_action() && isset( $_POST['name'] ) ) {
				$duplicate = get_term_by( 'name', sanitize_text_field( wp_unslash( $_POST['name'] ) ), $taxonomy );

				/**
				 * If the vendor name exist -> check if is the edited item or not
				 */
				if ( $duplicate && $duplicate->term_id === $term ) {
					$duplicate = false;
				}

				$message   = __( 'A vendor with this name already exists.', 'yith-woocommerce-product-vendors' );
				$title     = __( 'This vendor name already exists.', 'yith-woocommerce-product-vendors' );
				$back_link = admin_url( 'edit-tag.php' );

				$back_link = esc_url(
					add_query_arg(
						$back_link,
						array(
							'action'    => 'edit',
							'taxonomy'  => sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ),
							'tag_ID'    => absint( $_POST['tag_ID'] ),
							'post_type' => 'product',
						)
					)
				);

				$args = array( 'back_link' => $back_link );

				return ! $duplicate ? $term : wp_die( $message, $title, $args );

			} else {
				$to_return = $term;

				// If the user try to save a translation don't need to check for duplicated term name.
				$duplicate = get_term_by( 'name', $term, $taxonomy );

				if ( $duplicate ) {
					$to_return = new WP_Error( 'term_exists', __( 'A vendor with this name already exists.', 'yith-woocommerce-product-vendors' ), $duplicate );
				}

				if ( ! empty( $_POST['yith_vendor_data']['owner'] ) ) {
					$vendor = yith_wcmv_get_vendor( $_POST['yith_vendor_data']['owner'], 'user' );
					if ( $vendor->is_valid() ) {
						$to_return = new WP_Error( 'owner_exists', __( "You can't associate more vendor shops with the same shop owner.", 'yith-woocommerce-product-vendors' ), $duplicate );
					}
				}

				return $to_return;
			}
		}

		/**
		 * Add TinyMCE text editor
		 *
		 * @since  1.0
		 * @param string $value              The text area value.
		 * @param array  $args               Text editor params.
		 * @param false  $add_remove_scripts Remove script flag.
		 * @return void
		 * @deprecated
		 */
		public function add_wp_editor( $value = '', $args = array(), $add_remove_scripts = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Remove the add existing content from tiny mce editor
		 *
		 * @return void
		 */
		public static function remove_add_existing_content() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}


		/**
		 * Add upload fields
		 *
		 * @since  1.0
		 * @return void
		 */
		public function add_upload_field( $wrapper = 'div', $image_id = '', $type = 'header_image', $label = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Add upload fields script
		 *
		 * @since  1.0
		 * @return void
		 */
		public function add_upload_field_script( $args ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Allowed WooCommerce Post Types
		 *
		 * @since    1.2.0
		 * @param YITH_Vendor $vendor    The vendor instance.
		 * @param string      $post_type The post type.
		 * @return  bool
		 * @deprecated
		 */
		public function vendor_can_add_products( $vendor, $post_type ) {
			_deprecated_function( __METHOD__, '4.0.0', 'vendor->can_add_products' );
			if ( 'product' === $post_type ) {
				return $vendor->can_add_products();
			} else {
				return true;
			}
		}

		/**
		 * Amount limit error message
		 *
		 * @since    1.2.0
		 * @return  string
		 * @deprecated
		 */
		public function get_product_amount_limit_message( $vendor ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			$products_limit = apply_filters( 'yith_wcmv_vendors_products_limit', get_option( 'yith_wpv_vendors_product_limit', 25 ), $vendor );

			return sprintf( __( 'You are not allowed to create more than %1$s products. %2$sClick here to return to your admin area%3$s.', 'yith-woocommerce-product-vendors' ), $products_limit, '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' );
		}

		/**
		 * Allowed comments for vendor
		 *
		 * @since  2.4.0
		 * @return void
		 * @deprecated
		 */
		public function allowed_comments() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() && $vendor->has_limited_access() && ! empty( $this->vendor_dashboard ) && method_exists( $this->vendor_dashboard, 'allowed_comments' ) ) {
				$this->vendor_dashboard->allowed_comments();
			}
		}

		/**
		 * Filter product reviews
		 *
		 * @since  1.0.0
		 * @fire   product_vendors_details_fields_save action
		 * @param object $query The WP_Comment_Query object.
		 * @deprecated
		 */
		public function filter_reviews_list( $query ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() && $vendor->has_limited_access() && ! empty( $this->vendor_dashboard ) && method_exists( $this->vendor_dashboard, 'filter_reviews_list' ) ) {
				$this->vendor_dashboard->filter_reviews_list( $query );
			}
		}

		/**
		 * Filter product reviews count
		 *
		 * @since  1.3
		 * @param array   $stats   The comment stats.
		 * @param integer $post_id The post ID.
		 * @return bool|mixed|object
		 * @deprecated
		 */
		public function count_comments( $stats, $post_id ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() && $vendor->has_limited_access() && ! empty( $this->vendor_dashboard ) && method_exists( $this->vendor_dashboard, 'count_comments' ) ) {
				return $this->vendor_dashboard->count_comments( $stats, $post_id );
			}

			return $stats;
		}

		/**
		 * Disable to mange other vendor options
		 *
		 * @since  1.6
		 * @return void
		 * @deprecated
		 */
		public function disabled_manage_other_comments() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
				return $this->vendor_dashboard->disabled_manage_other_comments();
			}
		}

		/**
		 * Set the vendor id for current coupon
		 *
		 * @since  1.3
		 * @return void
		 */
		public function add_vendor_to_coupon( $post_id ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
				$coupon = new WC_Coupon( $post_id );
				if ( $coupon instanceof WC_Coupon ) {
					$coupon->add_meta_data( 'vendor_id', $vendor->get_id(), true );
					$coupon->save_meta_data();
				}
			}
		}

		/**
		 * Change selling capability -> Table row actions
		 *
		 * @since    1.0.0
		 * @return void
		 * @use      admin_action_switch-selling-capability action
		 * @deprecated
		 */
		public function switch_selling_capability( $vendor_id = 0, $direct_call = false, $switch_to = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( $direct_call || ! empty( $_GET['action'] ) && 'switch-selling-capability' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				$vendor_id = empty( $vendor_id ) ? absint( $_GET['vendor_id'] ) : $vendor_id;
				$vendor    = yith_wcmv_get_vendor( $vendor_id );

				if ( $vendor && $vendor->is_valid() ) {
					if ( $switch_to ) {
						$vendor->set_enable_selling( $switch_to );
					} else {
						$vendor->set_enable_selling( ! $vendor->is_selling_enabled() );
					}

					$vendor->save();
				}

				if ( ! $direct_call ) {
					wp_redirect( esc_url_raw( remove_query_arg( array( 'action', 'vendor_id', '_wpnonce' ) ) ) );
					exit;
				}
			}
		}

		/**
		 * Change Pending Status -> Table row actions
		 *
		 * @since    1.2
		 * @return void
		 * @deprecated
		 */
		public function switch_pending_status( $vendor_id = 0, $direct_call = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			$check = $direct_call ? $direct_call : ! empty( $_GET['action'] ) && 'switch-pending-status' === sanitize_text_field( wp_unslash( $_GET['action'] ) );
			if ( $check ) {
				$vendor_id = $direct_call ? $vendor_id : absint( $_GET['vendor_id'] );
				$vendor    = yith_wcmv_get_vendor( $vendor_id );
				if ( $vendor->is_in_pending() ) {
					$vendor->set_enable_selling( 'yes' );

					$vendor->save();

					/* Send Email notification to New vendor */
					do_action( 'yith_vendors_account_approved', $vendor->get_owner() );
				}

				if ( ! $direct_call ) {
					$redirect = remove_query_arg( array( 'action', 'vendor_id', '_wpnonce' ) );
					$paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
					wp_redirect( esc_url_raw( add_query_arg( array( 'paged' => $paged ), $redirect ) ) );
					exit;
				}
			}
		}

		/**
		 * Premium panel options
		 *
		 * @since  1.0
		 * @param array  $options The original options array.
		 * @param string $tab     The tab.
		 * @return array The new options array
		 */
		public function add_panel_premium_options( $options, $tab ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $options;
		}

		/**
		 * Add the custom type option "button"
		 *
		 * @since  1.0
		 * @param mixed $value The field value.
		 * @return void
		 */
		public function admin_field_button( $value ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				</th>
				<td class="forminp">
					<input type="button" name="force_review" id="<?php echo esc_attr( $value['id'] ); ?>"
						value="<?php echo esc_attr( $value['name'] ); ?>" class="button-secondary"/>
					<span class="description with-spinner">
						<?php echo wp_kses_post( $value['desc'] ); ?>
					</span>
					<span class="spinner"></span>
				</td>
			</tr>
			<?php
		}

		/**
		 * Commissions bulk action
		 *
		 * @since 1.0.0
		 */
		public function process_bulk_action() {
			_deprecated_function( __METHOD__, '4.0.0' );
			return false;
		}

		/**
		 * Save extra taxonomy fields for product vendors taxonomy
		 *
		 * @since  1.0
		 * @param integer $vendor_id The vendor id.
		 * @return void
		 */
		public function save_taxonomy_fields( $vendor_id = 0 ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( ! isset( $_POST['yith_vendor_data'] ) ) {
				return;
			}

			$is_new = strpos( current_action(), 'created_' ) !== false;

			// if not is set $vendor_id check if there is the update_vendor_id field inside the $_POST array.
			if ( empty( $vendor_id ) && isset( $_POST['update_vendor_id'] ) ) {
				$vendor_id = $_POST['update_vendor_id'];
			}

			$vendor = yith_wcmv_get_vendor( $vendor_id );

			if ( ! $vendor->is_valid() ) {
				return;
			}

			$vendor_name = '';

			if ( isset( $_POST['tag-name'] ) ) {
				$vendor_name = $_POST['tag-name'];
			} elseif ( isset( $_POST['name'] ) ) {
				$vendor_name = $_POST['name'];
			}

			$check = $this->check_duplicate_term_name( $vendor_name, YITH_Vendors_Taxonomy::TAXONOMY_NAME );

			if ( is_wp_error( $check ) && ! empty( $check->error_data['term_exists'] ) && $check->error_data['term_exists']->term_id != $vendor->get_id() ) {
				$show_admin_notices = 'name_exists';
			} else {
				$post_value     = $_POST['yith_vendor_data'];
				$usermeta_owner = yith_wcmv_get_user_meta_owner();
				$usermeta_admin = yith_wcmv_get_user_meta_key();

				if ( ! $vendor->has_limited_access() ) {
					foreach ( apply_filters( 'yith_wpv_save_checkboxes', array( 'enable_selling' ), $vendor->has_limited_access() ) as $key ) {
						! isset( $post_value[ $key ] ) && $post_value[ $key ] = 'no';
					}
				} else {
					foreach ( apply_filters( 'yith_wpv_save_checkboxes', array(), $vendor->has_limited_access() ) as $key ) {
						! isset( $post_value[ $key ] ) && $post_value[ $key ] = 'no';
					}
				}

				// set values.
				$skip_wc_clean_for = apply_filters(
					'yith_wcmv_skip_wc_clean_for_fields_array',
					array(
						'description',
						'shipping_policy',
						'shipping_refund_policy',
					)
				);
				foreach ( $post_value as $key => $value ) {
					$setter = "set_{$key}";
					if ( ! in_array( $key, $skip_wc_clean_for ) ) {
						$value = ! is_array( $value ) ? wc_clean( $value ) : $value;
					}

					method_exists( $vendor, $setter ) ? $vendor->$setter( $value ) : $vendor->set_meta( $key, $value );
				}

				// Add vendor registration date.
				if ( $is_new ) {
					$vendor->set_meta( 'registration_date', current_time( 'mysql' ) );
					$vendor->set_meta( 'registration_date_gmt', current_time( 'mysql', 1 ) );
				}

				// Get current vendor admins and owner.
				$admins = $vendor->get_admins();
				$owner  = $vendor->get_owner();

				if ( empty( $post_value['admins'] ) && 'admin_action_yith_admin_save_fields' === current_action() ) {
					// If the vendor save a tab different of vendor settings.
					$post_value['admins'] = $admins;
				}

				// Remove all current admins (user meta).
				if ( $vendor->is_super_user() ) {
					foreach ( $admins as $user_id ) {
						$user = get_user_by( 'id', $user_id );
						delete_user_meta( $user_id, $usermeta_admin );
						$user->remove_cap( 'publish_products' );
						do_action( 'yith_wcmv_remove_vendor_extra_cap', $user );
						$user->remove_role( YITH_Vendors_Capabilities::ROLE_NAME );
						$user->add_role( 'customer' );
					}
				}

				// Remove current owner and update it.
				if ( ! empty( $post_value['owner'] ) && $owner != $post_value['owner'] ) {
					$vendor->set_owner( intval( $post_value['owner'] ) );
				} elseif ( empty( $post_value['owner'] ) && 'admin_action_yith_admin_save_fields' != current_action() && $vendor->is_super_user() ) {
					$vendor->remove_owner();
				}

				// Add Vendor Owner.
				if ( ! isset( $post_value['admins'] ) ) {
					$post_value['admins'] = array( $owner );
				} else {
					$temp_admins          = $post_value['admins'];
					$post_value['admins'] = maybe_unserialize( $temp_admins );

					if ( ! empty( $owner ) ) {
						$post_value['admins'][] = $owner;
					}
				}

				// Only add selected admins.
				if ( ! empty( $post_value['admins'] ) ) {
					$role_to_remove = apply_filters( 'yith_wcmv_remove_role_for_vendor_admins', array( 'customer', 'subscriber' ) );
					foreach ( $post_value['admins'] as $user_id ) {
						update_user_meta( $user_id, $usermeta_admin, $vendor->get_id() );
						$user = get_user_by( 'id', $user_id );
						if ( $user instanceof WP_User ) {
							$user->add_role( YITH_Vendors_Capabilities::ROLE_NAME );
							foreach ( $role_to_remove as $role ) {
								$user->remove_role( $role );
							}
						}
					}
				}

				$show_admin_notices = 'success';

				do_action( 'yith_wpv_after_save_taxonomy', $vendor, $post_value );
			}
		}

		/**
		 * Print an admin notice
		 *
		 * @since  2.1.1
		 * @return void
		 * @use    admin_notices hooks
		 */
		public function add_woocommerce_admin_notice() {
			$message = '';
			$type    = '';
			if ( isset( $_GET['message'] ) ) {
				switch ( $_GET['message'] ) {
					case 'success':
						$message = __( 'Option saved', 'yith-woocommerce-product-vendors' );
						$type    = 'updated';
						break;

					case 'name_exists':
						$message = __( 'A vendor with this name already exists.', 'yith-woocommerce-product-vendors' );
						$type    = 'error';
						break;

					case 'owner_exist':
						$message = __( "You can't associate more vendor shops with the same shop owner.", 'yith-woocommerce-product-vendors' );
						$type    = 'error';
						break;
				}
			}

			if ( ! empty( $message ) ) :
				?>
				<div class="<?php echo esc_attr( $type ); ?>">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<?php
			endif;
		}

		/**
		 * Skip duplicated term name for vendor name translation
		 *
		 * @return void
		 */
		public function wpml_save_term_action() {
			add_filter( 'yith_wcmv_skip_check_duplicate_term_name', '__return_true' );
		}

		/**
		 * Allowed WooCommerce Post Types
		 *
		 * @since  1.2.0
		 * @return void
		 * @deprecated
		 */
		public function allowed_wc_post_types() {
			_deprecated_function( __METHOD__, '4.0.0' );
			global $post_type;
			$_screen = get_current_screen();

			if ( 'shop_coupon' === $post_type || 'edit-product' === $_screen->id || 'edit-shop_order' === $_screen->id ) {
				return;
			}

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			$allowed_post_types = apply_filters(
				'yith_wpv_vendors_allowed_post_types',
				array(
					'product',
					'shop_coupon',
				)
			);

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$post_types_check = in_array( $post_type, $allowed_post_types, true );
				if ( ( 'admin_head-edit.php' === current_action() && ! $post_types_check ) || ( 'shop_order' === $_screen->id && 'add' === $_screen->action ) ) {
					// translators: %1$s stand for the open anchor html to admin dashboard, %2$s stand for the close anchor tag.
					wp_die( sprintf( __( 'You do not have sufficient permissions to access this page. %1$sClick here to return to your dashboard%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( admin_url() ) . '">', '</a>' ) );
				}
			}
		}

		/**
		 * Restrict vendors from editing other vendors' posts
		 *
		 * @since       1.3
		 * @return      void
		 * @deprecated
		 */
		public function disabled_manage_other_vendors_posts() {
			_deprecated_function( __METHOD__, '4.0.0' );
			global $typenow;
			$vendor    = yith_wcmv_get_vendor( 'current', 'user' );
			$is_seller = $vendor->is_valid() && $vendor->has_limited_access();

			if ( $is_seller && ! empty( $typenow ) && apply_filters( 'yith_wcmv_disable_post', 'post' === $typenow ) ) {
				// translators: %1$s stand for the open anchor html to admin dashboard, %2$s stand for the close anchor tag.
				wp_die( sprintf( __( 'You do not have sufficient permissions to access this page. %1$sClick here to return to your dashboard%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( admin_url() ) . '">', '</a>' ) );
			}

			if ( isset( $_POST['post_ID'] ) || ! isset( $_GET['post'] ) ) {
				return;
			}

			/* WPML Support */
			$default_language = function_exists( 'wpml_get_default_language' ) ? wpml_get_default_language() : null;
			$post_id          = yit_wpml_object_id( $_GET['post'], 'product', true, $default_language );
			$product_vendor   = yith_wcmv_get_vendor( $post_id, 'product' ); // If false, the product hasn't any vendor set
			$post             = get_post( $_GET['post'] );

			if ( $is_seller ) {

				if ( 'product' === $post->post_type && false !== $product_vendor && $vendor->get_id() !== $product_vendor->get_id() ) {
					// translators: %1$s and %2$s are placeholder for <a/> html tag opening and closing.
					wp_die( sprintf( __( 'You do not have permission to edit this product. %1$sClick here to view and edit your products%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' ) );
				} elseif ( 'shop_coupon' === $post->post_type && ! in_array( $post->post_author, $vendor->get_admins(), true ) ) {
					// translators: %1$s and %2$s are placeholder for <a/> html tag opening and closing.
					wp_die( sprintf( __( 'You do not have permission to edit this coupon. %1$sClick here to view and edit your coupons%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_coupon' ) . '">', '</a>' ) );
				} elseif ( 'shop_order' === $post->post_type && YITH_Vendors()->addons->has_plugin( 'request-quote' ) && 'no' === get_option( 'yith_wpv_vendors_enable_request_quote', 'no' ) && in_array( $post->post_status, YITH_YWRAQ_Order_Request()->raq_order_status, true ) ) {
					// translators: %1$s and %2$s are placeholder for <a/> html tag opening and closing.
					wp_die( sprintf( __( 'You do not have permission to edit this order. %1$sClick here to view your orders%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_order' ) . '">', '</a>' ) );
				} elseif ( 'shop_order' === $post->post_type && ! in_array( $post->ID, array_merge( $vendor->get_orders( 'suborder' ), $vendor->get_orders( 'all' ) ), true ) ) {
					// translators: %1$s and %2$s are placeholder for <a/> html tag opening and closing.
					wp_die( sprintf( __( 'You do not have permission to edit this order. %1$sClick here to view your orders%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_order' ) . '">', '</a>' ) );
				}
			}
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
		 * @deprecated
		 */
		public function add_sold_by_to_order( $item_id, $item, $product ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors()->admin->get_orders_handler()->add_sold_by_to_order()' );
			$this->orders->add_sold_by_to_order( $item_id, $item, $product );
		}

		/**
		 * Remove YIT Shortcodes button in YITH Themes
		 *
		 * @since    1.6
		 * @return  void
		 * @deprecated
		 */
		public function remove_shortcodes_button() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Enable duplicate product for vendor
		 *
		 * @since  1.9.6
		 * @param string $cap The capability.
		 * @return string
		 */
		public function enabled_duplicate_product_capability( $cap ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $cap;
		}

		/**
		 * Get vendor option panel object
		 *
		 * @since  2.4.0
		 * @return YIT_Plugin_Panel_WooCommerce object
		 * @deprecated
		 */
		public function get_vendor_panel() {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $this->panel;
		}

		/**
		 * Check if current vendor untrashed a product
		 *
		 * @since  1.9.6
		 * @param mixed   $check the current check value.
		 * @param WP_Post $post  the post object.
		 * @return mixed
		 */
		public function pre_untrash_product( $check, $post ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $check;
		}

		/**
		 * Is Email Hack
		 *
		 * @since  1.8.3
		 * @param boolean $check The check flag.
		 * @param string  $email Email flag.
		 * @return boolean
		 * @deprecated
		 */
		public function is_email_hack( $check, $email ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ) === $email ) {
				$check = true;
			}

			return $check;
		}

		/**
		 * Add vendor to product
		 *
		 * @since       1.0
		 * @param integer $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 * @return      void
		 * @deprecated
		 */
		public function add_vendor_taxonomy_to_product( $post_id, $post ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Create "Become a Vendor" and "Terms and Conditions" pages.
		 * Fire at register_activation_hook
		 *
		 * @since  1.7
		 * @return void
		 * @deprecated
		 */
		public static function create_plugins_page() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Install::create_pages' );
			YITH_Vendors_Install::create_pages();
		}

		/**
		 * Deprecated apply_revision_actions
		 *
		 * @param mixed  $old_value Old option value.
		 * @param mixed  $value     New option value.
		 * @param string $option    Option key.
		 * @deprecated
		 */
		public function apply_revision_actions( $old_value, $value, $option ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Deprecated reschedule_disable_event
		 *
		 * @param mixed  $old_value Old option value.
		 * @param mixed  $value     New option value.
		 * @param string $option    Option key.
		 * @deprecated
		 */
		public function reschedule_disable_event( $old_value, $value, $option ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Filter the post count for vendor
		 *
		 * @since    1.0
		 * @param mixed  $counts The post count.
		 * @param string $type   Post type.
		 * @param mixed  $perm   The read permission.
		 * @return mixed
		 * @deprecated
		 */
		public static function vendor_count_posts( $counts, $type, $perm ) {
			if ( 'shop_order' === $type && current_user_can( 'manage_woocommerce' ) ) {
				global $wpdb;

				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					$orders_table = OrdersTableDataStore::get_orders_table_name();
					$query        = "SELECT status, COUNT(*) AS num_posts FROM {$orders_table} WHERE type = %s AND parent_order_id = 0 GROUP BY status";
				} else {
					$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = 0 GROUP BY post_status";
				}

				$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A ); // phpcs:ignore
				$counts  = array_fill_keys( get_post_stati(), 0 );

				foreach ( $results as $row ) {
					if ( isset( $row['post_status'] ) && array_key_exists( $row['post_status'], $counts ) ) {
						$counts[ $row['post_status'] ] = $row['num_posts'];
					}
				}

				$counts = (object) $counts;
			}

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( ! $vendor || ! $vendor->is_valid() || ! $vendor->is_user_admin() ) {
				return $counts;
			}

			// Get a list of post statuses.
			$stati      = get_post_stati();
			$new_counts = new stdClass();

			// Update count object.
			foreach ( $stati as $status ) {
				$posts = '';
				if ( 'product' === $type ) {
					$posts = $vendor->get_products( array( 'post_status' => $status ) );
				} elseif ( 'shop_order' === $type ) {
					$posts = $vendor->get_orders( 'suborder', $status );
				}

				$new_counts->$status = ! empty( $posts ) ? count( $posts ) : 0;
			}

			$counts = $new_counts;

			return $counts;
		}

		/**
		 * Modules tab content
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function modules_tab_content() {
			_deprecated_function( __METHOD__, '5.0.0' );

			YITH_Vendors_Modules::instance()->modules_tab_content();
		}
	}
}
