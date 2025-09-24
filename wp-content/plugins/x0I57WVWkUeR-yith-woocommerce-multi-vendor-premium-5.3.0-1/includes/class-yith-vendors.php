<?php
/**
 * Main plugin class
 *
 * @class      YITH_Vendors
 * @since      4.0.0
 * @author     YITH
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

if ( ! class_exists( 'YITH_Vendors' ) ) {
	/**
	 * Class YITH_Vendors
	 */
	class YITH_Vendors extends YITH_Vendors_Legacy {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Main Admin Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Admin
		 */
		public $admin = null;

		/**
		 * Main Frontpage Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Frontend
		 */
		public $frontend = null;

		/**
		 * Main Products Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Products
		 */
		public $products = null;

		/**
		 * Main Orders Instance
		 *
		 * @since 1.0
		 * @var YITH_Vendors_Orders
		 */
		public $orders = null;

		/**
		 * Main Commissions Instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Commissions
		 */
		public $commissions = null;

		/**
		 * YITH_Vendors_Payments class instance
		 *
		 * @var YITH_Vendors_Payments|null
		 */
		public $payments = null;

		/**
		 * YITH_Vendors_Request_Quote class instance
		 *
		 * @var YITH_Vendors_Request_Quote|null
		 */
		public $quote = null;

		/**
		 * YITH_Vendors_Coupons class instance
		 *
		 * @var YITH_Vendors_Coupons|null
		 */
		public $coupons = null;

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Vendors Main instance
		 */
		public static function instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );
			if ( is_null( $self::$instance ) ) {
				$self::$instance = new $self();
			}

			return $self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		protected function __construct() {

			$this->load_frontend_manager_files();
			// Load required files.
			$this->load_required( $this->get_required_files() );
			// Register plugin image size.
			$this->register_image_size();
			// Theme support classes.
			$this->theme_support_includes();

			YITH_Vendors_Install::install();

			add_action( 'init', array( $this, 'init' ), 5 );
			add_action( 'init', array( $this, 'terms_revision_hooks' ) );
			add_action( 'init', array( $this, 'flush_rewrite_rules' ), 20 );
			// Load widget.
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			// Remove wp admin bar for vendor.
			add_action( 'admin_bar_menu', array( $this, 'customize_wp_admin_bar' ), 50 );
			// Maybe block admin access.
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ) );
			add_filter( 'show_admin_bar', array( $this, 'customize_wp_admin_bar_visibility' ), 99, 1 );

			// Register common scripts and styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 5 );

			// Listen option change that needs capabilities to be updated.
			add_action( 'updated_option', array( $this, 'maybe_update_capabilities' ), 10, 3 );
			add_action( 'update_option_yith_wpv_vendors_option_staff_management', array( $this, 'handle_vendor_admins_capabilities' ), 10, 3 );

			// Allow html to vendor taxonomy description.
			add_filter( 'pre_term_description', array( $this, 'sanitize_vendor_description' ), 1, 2 );
			// Manage related transient.
			add_action( 'update_option_yith_vendors_related_products', array( $this, 'delete_related_products_transient' ) );
		}

		/**
		 * Class initialization. Instance the admin or frontend classes.
		 *
		 * @since  1.0
		 * @return void
		 * @access protected
		 */
		public function init() {

			if ( ! doing_action( 'init' ) ) {
				_doing_it_wrong( __METHOD__, 'This method should be called only once on init!', '4.0.0' );
				return;
			}

			// WPML Compatibility.
			YITH_Vendors_WPML::instance();
			// Load support for WC Blocks.
			YITH_Vendors_WC_Blocks_Support::instance();

			// Init classes.
			$this->products    = new YITH_Vendors_Products();
			$this->orders      = new YITH_Vendors_Orders();
			$this->coupons     = new YITH_Vendors_Coupons();
			$this->commissions = new YITH_Vendors_Commissions();
			$this->payments    = new YITH_Vendors_Payments();
			YITH_Vendors_Modules::instance();
			YITH_Vendors_Integrations::instance();
			YITH_Vendors_Emails::instance();
			YITH_Vendors_Gateways::instance();
			YITH_Vendors_Account_Endpoints::instance();

			// Load admin if admin request.
			if ( yith_wcmv_is_admin_request() ) {
				$this->admin = new YITH_Vendors_Admin();
			}
			// Load frontend if frontend request.
			if ( yith_wcmv_is_frontend_request() ) {
				$this->frontend = new YITH_Vendors_Frontend();
			}
		}

		/**
		 * Hooks to handle terms revision for vendor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function terms_revision_hooks() {
			$revision_management = get_option( 'yith_wpv_manage_terms_and_privacy_revision', 'no' );
			$privacy_required    = get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' );
			$terms_required      = get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' );
			if ( 'yes' !== $revision_management || ( 'no' === $privacy_required && 'no' === $terms_required ) ) {
				return;
			}

			add_action( 'save_post', array( $this, 'update_acceptance_details_for_vendors' ), 30, 1 );
			add_action( 'yith_wcmv_disable_vendor_to_sale', array( $this, 'disable_vendors_to_sale' ) );
			add_action( 'yith_wcmv_disable_vendor_to_sale_cron', array( $this, 'disable_vendors_to_sale' ) );
			if ( ! empty( $this->admin ) ) {
				add_action( 'admin_notices', array( $this->admin, 'print_check_revision_message' ), 20 );
			}
		}

		/**
		 * Include classes for theme support.
		 *
		 * @since  4.0.0
		 */
		protected function theme_support_includes() {

			$theme    = wp_get_theme();
			$template = $theme instanceof WP_Theme ? $theme->get_template() : '';

			if ( empty( $template ) ) {
				return;
			}

			$class = 'YITH_Vendors_Theme_' . preg_replace( '/[^a-zA-Z_]+/', '_', ucwords( trim( $template ), ' _-' ) );
			class_exists( $class ) && $class::init(); // init compatibility class.
		}

		/**
		 * Get default required classes
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_required_files() {
			$required = array(
				'common'   => array(
					// Deprecated hooks handler.
					'includes/class-yith-vendors-deprecated-filter-hooks.php',
					'includes/class-yith-vendors-deprecated-action-hooks.php',
					// Legacy.
					'includes/legacy/yith-vendors-legacy-functions.php',
					'includes/class.yith-reports-analytics.php',
				),
				'admin'    => array(
					'includes/admin/yith-vendors-admin-functions.php',
					'includes/class.yith-reports.php',
				),
				'frontend' => array(),
			);

			return $required;
		}

		/**
		 * Load the required plugin files.
		 *
		 * @since  4.0.0 <francesco.licandro@yithemes.com>
		 * @param array $required_files an array of required files to load.
		 * @return void
		 * @access protected
		 */
		protected function load_required( $required_files ) {

			// Load first common functions to be immediately available.
			$this->require_file( 'includes/yith-vendors-functions.php' );

			$is_admin = function_exists( 'yith_wcmv_is_admin_request' ) ? yith_wcmv_is_admin_request() : is_admin();
			foreach ( $required_files as $section => $files ) {
				if ( 'common' === $section || ( 'frontend' === $section && ! $is_admin ) || ( 'admin' === $section && $is_admin ) ) {
					$this->require_file( $required_files[ $section ] );
				}
			}
		}

		/**
		 * Require s plugin file.
		 *
		 * @since  4.0.0
		 * @param string $files A single file or an array of files to require .
		 * @return void
		 * @access protected
		 */
		protected function require_file( $files ) {
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					$this->require_file( $file );
				}
			} elseif ( file_exists( YITH_WPV_PATH . $files ) ) {
					require_once YITH_WPV_PATH . $files;
			}
		}

		/**
		 * Register common style and scripts
		 *
		 * @since    1.0
		 * @return   void
		 */
		public function enqueue_scripts() {
			wp_register_style( 'yith-wcmv-font-icons', YITH_WPV_ASSETS_URL . 'third-party/fontello/css/fontello-embedded.min.css', array(), YITH_WPV_VERSION );
			wp_register_style( 'yith-wc-product-vendors', YITH_WPV_ASSETS_URL . 'css/' . yit_load_css_file( 'product-vendors.css' ), array( 'yith-wcmv-font-icons' ), YITH_WPV_VERSION );
		}

		/**
		 * Widgets initialization
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function widgets_init() {

			$widgets = apply_filters(
				'yith_wcmv_register_widgets',
				array(
					'YITH_Vendors_List_Widget',
					'YITH_Vendors_Store_Location_Widget',
					'YITH_Vendors_Quick_Info_Widget',
				)
			);

			foreach ( $widgets as $widget ) {
				register_widget( $widget );
			}
		}

		/**
		 * Replace the Visit Store link from WooCommerce with the vendor store page link.
		 *
		 * @since  4.0.0
		 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar object.
		 * @return void
		 */
		public function customize_wp_admin_bar( $wp_admin_bar ) {

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
				remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );

				// Remove Yoast SEO admin icon.
				$wp_admin_bar->remove_menu( 'wpseo-menu' );
				// Remove my sites for multisite installation.
				$wp_admin_bar->remove_menu( 'my-sites' );

				if ( apply_filters( 'woocommerce_show_admin_bar_visit_store', true ) ) {
					$wp_admin_bar->add_node(
						array(
							'parent' => 'site-name',
							'id'     => 'view-store',
							'title'  => __( 'Visit Store', 'yith-woocommerce-product-vendors' ),
							'href'   => $vendor->get_url( 'frontend' ),
						)
					);
				}
			}
		}

		/**
		 * Refresh rewrite rules for frontpage
		 *
		 * @since  1.6.0
		 * @return void
		 */
		public function flush_rewrite_rules() {
			if ( get_option( 'yith_wcmv_flush_rewrite_rules', false ) ) {
				flush_rewrite_rules();
				update_option( 'yith_wcmv_flush_rewrite_rules', false );
			}
		}

		/**
		 * Add image size
		 *
		 * @since  1.11.4
		 * @return void
		 */
		protected function register_image_size() {

			$gravatar_size = get_option( 'yith_vendors_gravatar_image_size', 128 );
			$header_size   = get_option(
				'yith_wpv_header_image_size',
				array(
					'width'  => 1400,
					'height' => 460,
				)
			);

			$images = array(
				'yith_vendors_avatar' => array(
					'width'  => $gravatar_size,
					'height' => 0,
					'crop'   => false,
				),
				'yith_vendors_header' => array(
					'width'  => $header_size['width'],
					'height' => $header_size['height'],
					'crop'   => true,
				),
			);

			foreach ( $images as $image_name => $image_size ) {
				add_image_size( $image_name, intval( $image_size['width'] ), intval( $image_size['height'] ), $image_size['crop'] );
			}
		}

		/**
		 * Get the image size name
		 *
		 * @since  1.11.4
		 * @param string $image_type The image type name to retrieve.
		 * @return string
		 */
		public function get_image_size( $image_type ) {
			return 'yith_vendors_' . $image_type;
		}

		/**
		 * Get social feed array - Not available on SuperClass
		 *
		 * @return array
		 */
		public function get_social_fields() {
			$socials = array(
				'social_fields' => array(
					'facebook'    => array(
						'label' => 'Facebook',
						'icon'  => 'yith-wcmv-icon__facebook',
					),
					'twitter'     => array(
						'label' => 'X',
						'icon'  => 'yith-wcmv-icon__x',
					),
					'instagram'   => array(
						'label' => 'Instagram',
						'icon'  => 'yith-wcmv-icon__instagram',
					),
					'youtube'     => array(
						'label' => 'YouTube',
						'icon'  => 'yith-wcmv-icon__youtube',
					),
					'vimeo'       => array(
						'label' => 'Vimeo',
						'icon'  => 'yith-wcmv-icon__vimeo',
					),
					'linkedin'    => array(
						'label' => 'LinkedIn',
						'icon'  => 'yith-wcmv-icon__linkedin',
					),
					'pinterest'   => array(
						'label' => 'Pinterest',
						'icon'  => 'yith-wcmv-icon__pinterest',
					),
					'flickr'      => array(
						'label' => 'Flickr',
						'icon'  => 'yith-wcmv-icon__flickr',
					),
					'behance'     => array(
						'label' => 'Behance',
						'icon'  => 'yith-wcmv-icon__behance',
					),
					'tripadvisor' => array(
						'label' => 'Tripadvisor',
						'icon'  => 'yith-wcmv-icon__tripadvisor',
					),
				),
			);

			return apply_filters( 'yith_wcmv_vendor_social_fields', $socials );
		}

		/**
		 * Get the post datetime, ( Y-m-d H:i:s ) format, for the privacy policy page.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_last_modified_data_privacy_policy() {
			$privacy_page_id    = get_option( 'yith_wpv_privacy_page', 0 );
			$data_last_modified = $privacy_page_id ? get_post_datetime( $privacy_page_id, 'modified' ) : false;

			return $data_last_modified instanceof DateTimeImmutable ? $data_last_modified->format( 'Y-m-d H:i:s' ) : '';
		}

		/**
		 * Get the post datetime, ( Y-m-d H:i:s ) format, for the terms and conditions page.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_last_modified_data_terms_and_conditions() {
			$terms_page_id      = get_option( 'yith_wpv_terms_and_conditions_page_id', 0 );
			$data_last_modified = $terms_page_id ? get_post_datetime( $terms_page_id, 'modified' ) : false;

			return $data_last_modified instanceof DateTimeImmutable ? $data_last_modified->format( 'Y-m-d H:i:s' ) : '';
		}

		/**
		 * Maybe update vendor capabilities on option change.
		 *
		 * @since  4.1.0
		 * @param string $option    Option name.
		 * @param mixed  $old_value The old option value.
		 * @param mixed  $value     The new option value.
		 * @return void
		 */
		public function maybe_update_capabilities( $option, $old_value, $value ) {

			if ( false === strpos( $option, 'yith_wpv_' ) ) {
				return;
			}

			$options = apply_filters(
				'yith_wcmv_update_capabilities_required_options',
				array(
					'yith_wpv_vendors_option_coupon_management',
					'yith_wpv_vendors_option_review_management',
					'yith_wpv_vendors_option_order_management',
					'yith_wpv_vendors_option_product_import_management',
					'yith_wpv_vendors_option_product_export_management',
				)
			);

			if ( in_array( $option, $options, true ) ) {
				YITH_Vendors_Capabilities::update_capabilities();
			}
		}

		/**
		 * Listen option module yith_wpv_vendors_option_staff_management change, and cleanup admins capabilities
		 *
		 * @since  4.0.0
		 * @param mixed  $old_value The old option value.
		 * @param mixed  $value     The new option value.
		 * @param string $option    Option name.
		 * @return void
		 */
		public function handle_vendor_admins_capabilities( $old_value, $value, $option ) {
			$vendors = yith_wcmv_get_vendors( array( 'number' => -1 ) );
			foreach ( $vendors as $vendor ) {
				$admins = $vendor->get_meta( 'admins' );
				if ( empty( $admins ) ) {
					continue;
				}

				foreach ( $admins as $admin ) {
					if ( 'yes' === $value ) {
						YITH_Vendors_Capabilities::set_vendor_capabilities_for_user( $admin, $vendor );
					} else {
						YITH_Vendors_Capabilities::remove_vendor_capabilities_for_user( $admin );
					}
				}
			}
		}

		/**
		 * If current user has role vendor but no vendor associated.
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		protected function is_user_owner_without_vendor() {
			$user            = wp_get_current_user();
			$has_vendor_role = in_array( YITH_Vendors_Capabilities::ROLE_NAME, $user->roles, true );
			$vendor          = yith_wcmv_get_vendor( 'current', 'user' );

			return $has_vendor_role && ( empty( $vendor ) || ! $vendor->is_valid() );
		}

		/**
		 * If an user has role vendor but no vendor store associated, block admin access.
		 *
		 * @since  4.0.0
		 * @param boolean $prevent_access Current value: true to prevent admin access, false otherwise.
		 * @return boolean
		 */
		public function prevent_admin_access( $prevent_access ) {
			return $prevent_access || $this->is_user_owner_without_vendor();
		}

		/**
		 * Render or not tha admin bar based on current user
		 *
		 * @since  4.0.0
		 * @param boolean $visible True if the admin bar is visible, false otherwise.
		 * @return boolean
		 */
		public function customize_wp_admin_bar_visibility( $visible ) {
			return $visible && ! $this->is_user_owner_without_vendor();
		}


		/**
		 * Update policy and term post revision
		 *
		 * @since  4.0.0
		 * @param integer $post_id Post ID.
		 * @return void
		 */
		public function update_acceptance_details_for_vendors( $post_id ) {
			$privacy_page_id = absint( get_option( 'yith_wpv_privacy_page', 0 ) );
			$terms_page_id   = absint( get_option( 'yith_wpv_terms_and_conditions_page_id', 0 ) );

			$terms_req   = yith_wcmv_is_terms_and_conditions_required();
			$privacy_req = yith_wcmv_is_privacy_policy_required();

			if ( ( ( $terms_req && $post_id === $terms_page_id ) || ( $privacy_req && $post_id === $privacy_page_id ) ) && 'publish' === get_post_status( $post_id ) ) {

				$action = get_option( 'yith_wpv_manage_terms_and_privacy_revision_actions', 'no_action' );
				if ( 'disable_now' === $action ) {
					do_action( 'yith_wcmv_disable_vendor_to_sale' );
				} elseif ( 'disable_after' === $action ) {

					$days = get_option( 'yith_wpv_manage_terms_and_privacy_revision_disable_after', 3 );

					if ( ! wp_next_scheduled( 'yith_wcmv_disable_vendor_to_sale_cron' ) ) {
						wp_clear_scheduled_hook( 'yith_wcmv_disable_vendor_to_sale_cron' );
					}

					$timestamp = time() + ( $days * DAY_IN_SECONDS );
					wp_schedule_single_event( $timestamp, 'yith_wcmv_disable_vendor_to_sale_cron' );
				}
			}
		}

		/**
		 * Disable vendor to sale callback.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function disable_vendors_to_sale() {

			$vendors = yith_wcmv_get_vendors(
				array(
					'status' => 'enabled',
					'number' => -1,
				)
			);

			foreach ( $vendors as $vendor ) {
				$terms_check = yith_wcmv_is_terms_and_conditions_required();
				$terms_check = ! $terms_check || ( $terms_check && $vendor->has_terms_and_conditions_accepted() );

				$privacy_check = yith_wcmv_is_privacy_policy_required();
				$privacy_check = ! $privacy_check || ( $privacy_check && $vendor->has_privacy_policy_accepted() );

				if ( ! $terms_check || ! $privacy_check ) {
					$vendor->set_status( 'disabled' );
					$vendor->save();
				}
			}
		}

		/**
		 * Load YITH Frontend Manager for WooCommerce files.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function load_frontend_manager_files() {
			if ( defined( 'YITH_WCFM_CLASS_PATH' ) && file_exists( YITH_WCFM_CLASS_PATH . 'module/multi-vendor/module.yith-multi-vendor.php' ) ) {
				YITH_Vendors_Frontend_Manager::instance();
			}
		}

		/**
		 * Correctly sanitize vendor term description to allow HTML
		 *
		 * @since  4.1.1
		 * @param mixed  $value    Value of the term field.
		 * @param string $taxonomy Taxonomy slug.
		 * @return $value
		 */
		public function sanitize_vendor_description( $value, $taxonomy ) {
			if ( YITH_Vendors_Taxonomy::TAXONOMY_NAME === $taxonomy ) {
				remove_filter( 'pre_term_description', 'wp_filter_kses' );
				return wp_filter_post_kses( $value );
			}
			return $value;
		}

		/**
		 * On related option change delete related products transient for apply changes.
		 *
		 * @since  4.5.0
		 * @return void
		 */
		public function delete_related_products_transient() {
			global $wpdb;
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wc_related_%' OR option_name LIKE '_transient_timeout_wc_related_%'" ); //phpcs:ignore
		}
	}
}
