<?php
/**
 * YITH Vendors My Account Endpoints Class.
 * THis class is useful to manage my account endpoints and actions.
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package    YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


if ( ! class_exists( 'YITH_Vendors_Account_Endpoints' ) ) {
	/**
	 * Class YITH_Vendors_Account_Endpoints
	 */
	class YITH_Vendors_Account_Endpoints {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * An array of plugin frontend endpoints.
		 *
		 * @since 5.0.0
		 * @const array
		 */
		const ENDPOINTS = array(
			'vendor-dashboard',
			'terms-of-service',
		);

		/**
		 * Class constructor.
		 *
		 * @since  5.0.0
		 */
		private function __construct() {
			// Make sure register endpoints method is fired.
			add_filter( 'woocommerce_get_query_vars', array( $this, 'register_endpoints' ), 20, 1 );
			add_action( 'wp_loaded', array( $this, 'init_endpoints' ), 20 );
			add_action( 'wp_loaded', array( $this, 'handle_form_submit' ), 20 );

			// Support to YITH WooCommerce Customize My Account Page.
			add_filter( 'yith_wcmap_get_before_initialization', array( $this, 'register_items' ) );
			add_filter( 'yith_wcmap_get_default_endpoint_options', array( $this, 'default_endpoints_options' ), 10, 2 );
			add_filter( 'yith_wcmap_menu_items_initialized', array( $this, 'filter_items_for_vendor' ) );
		}

		/**
		 * Register plugin endpoints filtering woocommerce_get_query_vars
		 *
		 * @since 5.0.0
		 * @param array $endpoints Current registered WC endpoints.
		 * @return array
		 */
		public function register_endpoints( $endpoints ) {
			return array_merge( $endpoints, self::get_endpoints() );
		}

		/**
		 * Get plugin endpoints
		 *
		 * @since 5.0.0
		 * @return array
		 */
		public function get_endpoints() {
			$endpoints = array();
			foreach ( self::ENDPOINTS as $endpoint ) {
				$key                    = str_replace( '-', '_', $endpoint );
				$endpoints[ $endpoint ] = apply_filters( "yith_wcmv_{$key}_endpoint", $endpoint );
			}

			return $endpoints;
		}

		/**
		 * Get single plugin endpoint
		 *
		 * @since 5.0.0
		 * @param string $endpoint The endpoint key to retrieve.
		 * @return string
		 */
		public function get_endpoint( $endpoint ) {
			return $this->get_endpoints()[ $endpoint ] ?? '';
		}

		/**
		 * Init class hooks and filters
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function init_endpoints() {

			// Register menu items.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_vendor_menu_items' ), 20, 1 );

			foreach ( $this->get_valid_endpoints() as $endpoint ) {
				add_action( 'woocommerce_account_' . $endpoint . '_endpoint', array( $this, 'output_endpoint_' . str_replace( '-', '_', $endpoint ) ) );
				add_filter( 'woocommerce_endpoint_' . $endpoint . '_title', array( $this, 'filter_endpoint_title' ), 10, 2 );
			}
		}

		/**
		 * Register item as plugins in Customize My Account
		 *
		 * @since 5.0.0
		 * @param array $items The array of registered items.
		 * @return array
		 */
		public function register_items( $items ) {

			return array_merge(
				$items,
				array(
					'vendor-dashboard' => array(),
					'terms-of-service' => array(),
				)
			);
		}

		/**
		 * Default endpoint options
		 *
		 * @since 5.0.0
		 * @param array  $options  Endpoint options.
		 * @param string $endpoint Endpoint key.
		 * @return array
		 */
		public function default_endpoints_options( $options, $endpoint ) {
			if ( ! in_array( $endpoint, self::ENDPOINTS, true ) ) {
				return $options;
			}

			return array_merge(
				$options,
				array(
					'slug'       => $this->get_endpoint( $endpoint ),
					'label'      => $this->get_endpoint_title( $endpoint ),
					'visibility' => 'roles',
					'usr_roles'  => array( YITH_Vendors_Capabilities::ROLE_NAME ),
				)
			);
		}

		/**
		 * Filter frontend items based on current vendor
		 *
		 * @since 5.0.0
		 * @param array $items The frontend items.
		 * @return array
		 */
		public function filter_items_for_vendor( $items ) {
			$to_exclude = array_diff( self::ENDPOINTS, $this->get_valid_endpoints() );
			return array_diff_key(
				$items,
				array_flip( $to_exclude )
			);
		}

		/**
		 * Get valid endpoints for current vendor
		 *
		 * @since 5.0.0
		 * @return array
		 */
		protected function get_valid_endpoints() {
			static $endpoints;

			if ( is_null( $endpoints ) ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( ! $vendor || ! $vendor->is_valid() ) {
					return array();
				}

				$endpoints = array_filter(
					self::ENDPOINTS,
					function ( $endpoint ) use ( $vendor ) {
						return $this->is_endpoint_valid_for_vendor( $endpoint, $vendor );
					}
				);
			}

			return $endpoints;
		}

		/**
		 * Get endpoint title
		 *
		 * @since 5.0.0
		 * @param string $endpoint The endpoint key.
		 * @return string
		 */
		protected function get_endpoint_title( $endpoint ) {

			switch ( $endpoint ) {
				case 'terms-of-service':
					$title = __( 'Terms of service', 'yith-woocommerce-product-vendors' );
					break;
				case 'vendor-dashboard':
					$title = __( 'Vendor dashboard', 'yith-woocommerce-product-vendors' );
					break;
				default:
					$title = ucfirst( str_replace( array( '-', '_' ), ' ', $endpoint ) );
					break;
			}

			return apply_filters( 'yith_wcmv_endpoint_title', $title, $endpoint );
		}

		/**
		 * Check if current endpoint is valid for current vendor
		 *
		 * @since 5.0.0
		 * @param string      $endpoint The endpoint to check.
		 * @param YITH_Vendor $vendor   The current vendor.
		 * @return bool
		 */
		protected function is_endpoint_valid_for_vendor( $endpoint, $vendor ) {
			$valid = true;

			if ( 'terms-of-service' === $endpoint ) {
				$manage_revision  = get_option( 'yith_wpv_manage_terms_and_privacy_revision', 'no' );
				$privacy_required = get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' );
				$terms_required   = get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' );

				$valid = $vendor->has_status( array( 'enabled', 'disabled' ) ) && 'yes' === $manage_revision && ( 'yes' === $privacy_required || 'yes' === $terms_required );
			}

			return apply_filters( 'yith_wcmv_is_endpoint_valid_for_vendor', $valid, $endpoint, $vendor );
		}

		/**
		 * Add my account menu item
		 *
		 * @since  1.0.0
		 * @param array $menu_items The array of My Account menu items.
		 * @return array
		 */
		public function add_vendor_menu_items( $menu_items ) {

			$endpoints = $this->get_valid_endpoints();
			if ( empty( $endpoints ) ) {
				return $menu_items;
			}

			if ( isset( $menu_items['customer-logout'] ) ) {
				$logout = $menu_items['customer-logout'];
				unset( $menu_items['customer-logout'] );
			}

			foreach ( $endpoints as $endpoint ) {
				$menu_items[ $endpoint ] = $this->get_endpoint_title( $endpoint );
			}

			if ( ! empty( $logout ) ) {
				$menu_items['customer-logout'] = $logout;
			}

			return $menu_items;
		}

		/**
		 * Filter vendor endpoint title
		 *
		 * @since 5.0.0
		 * @param string $title    The current endpoint title.
		 * @param string $endpoint The endpoint key.
		 * @return string
		 */
		public function filter_endpoint_title( $title, $endpoint ) {
			if ( ! in_array( $endpoint, self::ENDPOINTS, true ) ) {
				return $title;
			}

			return $this->get_endpoint_title( $endpoint );
		}

		/**
		 * Output terms of service endpoint content
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function output_endpoint_vendor_dashboard() {

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( ! $vendor || ! $vendor->is_valid() ) {
				return;
			}

			$status      = $vendor->get_status();
			$status_args = apply_filters(
				'yith_wcmv_myaccount_vendor_status_args',
				array(
					'pending'  => array(
						'title'   => __( 'Your vendor application is currently under review.', 'yith-woocommerce-product-vendors' ),
						'message' => __( 'Keep an eye on your email inbox. We\'ll get back to you as soon as possible!', 'yith-woocommerce-product-vendors' ),
					),
					'enabled'  => array(
						// translators: %s is the site name.
						'title'   => sprintf( __( 'Welcome, sellers of %s!', 'yith-woocommerce-product-vendors' ), get_bloginfo( 'name' ) ),
						'message' => __( 'You can manage products, orders, and your store through the Vendor dashboard:', 'yith-woocommerce-product-vendors' ),
						'cta'     => array(
							'label' => __( 'Access to your dashboard', 'yith-woocommerce-product-vendors' ),
							'url'   => apply_filters( 'yith_wcmv_my_vendor_dashboard_uri', esc_url( admin_url() ), $vendor->get_name() ),
						),
					),
					'rejected' => array(
						'title'   => __( 'We are sorry, but your vendor application has been rejected.', 'yith-woocommerce-product-vendors' ),
						'message' => __( 'Mistakes happen! If you believe we haven’t assessed your request accurately, please reach out to us with more details.', 'yith-woocommerce-product-vendors' ),
					),
				),
				$vendor
			);

			if ( ! isset( $status_args[ $status ] ) ) {
				return;
			}

			yith_wcmv_get_template(
				'vendor-dashboard',
				array_merge(
					array( 'vendor' => $vendor ),
					$status_args[ $status ] ?? array(),
				),
				'woocommerce/myaccount'
			);
		}

		/**
		 * Output terms of service endpoint content
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function output_endpoint_terms_of_service() {
			add_filter( 'wp_kses_allowed_html', array( $this, 'add_style_to_allowed_post_tags' ), 10, 2 );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			yith_wcmv_get_template( 'terms-of-service', array( 'vendor' => $vendor ), 'woocommerce/myaccount' );

			remove_filter( 'wp_kses_allowed_html', array( $this, 'add_style_to_allowed_post_tags' ), 10 );
		}

		/**
		 * Allow </style> html tag for wp_kses_post. Using filter wp_kses_allowed_html.
		 * This is useful when using template builders like elementor
		 *
		 * @since  4.0.4
		 * @param array  $tags    An array of allowed tags.
		 * @param string $context Allowed tag context.
		 * @return array
		 */
		public function add_style_to_allowed_post_tags( $tags, $context ) {
			if ( 'post' === $context ) {
				$tags['style'] = array();
			}

			return $tags;
		}

		/**
		 * Handle terms form submit
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_form_submit() {

			if ( empty( $_REQUEST['yith_mv_accept_temrs_and_privacy_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['yith_mv_accept_temrs_and_privacy_nonce'] ) ), 'yith-mv-accept-terms-and-privacy' ) ) {
				return;
			}

			$vendor_id = isset( $_REQUEST['yith_vendor_id'] ) ? absint( $_REQUEST['yith_vendor_id'] ) : '';

			$vendor = $vendor_id ? yith_wcmv_get_vendor( $vendor_id, 'vendor' ) : false;
			if ( $vendor && $vendor->is_valid() ) {

				$enable_vendor_selling = true;

				if ( yith_wcmv_is_terms_and_conditions_required() ) {
					if ( isset( $_REQUEST['yith_mv_accept_terms'] ) ) {
						$vendor->set_meta( 'data_terms_and_condition', YITH_Vendors()->get_last_modified_data_terms_and_conditions() );
					} else {
						$enable_vendor_selling = false;
					}
				}

				if ( yith_wcmv_is_privacy_policy_required() ) {
					if ( isset( $_REQUEST['yith_mv_accept_privacy'] ) ) {
						$vendor->set_meta( 'data_privacy_policy', YITH_Vendors()->get_last_modified_data_privacy_policy() );
					} else {
						$enable_vendor_selling = false;
					}
				}

				if ( $enable_vendor_selling ) {
					$vendor->set_status( 'enabled' );
				}

				$vendor->save();
			}

			wp_safe_redirect( wc_get_account_endpoint_url( 'terms-of-service' ) );
			exit;
		}

		/**
		 * Show my account terms of service endpoint content
		 *
		 * @since  1.0.0
		 * @return void
		 * @deprecated
		 */
		public function show_term_of_service_content() {
			$this->output_endpoint_terms_of_service();
		}

		/**
		 * Return my account terms of service endpoint title
		 *
		 * @since  1.0.0
		 * @param string $title THe default title value.
		 * @return string
		 * @deprecated
		 */
		public function show_term_of_service_endpoint_title( $title ) {
			return $this->filter_endpoint_title( $title, 'terms-of-service' );
		}

		/**
		 * Add vendor dashboard endpoint in my Account
		 *
		 * @since  4.0.0
		 * @param boolean | YITH_Vendor $vendor (Optional) The vendor object instance or false to get current. Default false.
		 * @return void
		 * @deprecated
		 */
		public function vendor_dashboard_endpoint( $vendor = false ) {
			$this->output_endpoint_vendor_dashboard();
		}
	}
}
