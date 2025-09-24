<?php
/**
 * Admin class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Admin' ) ) {
	/**
	 * WooCommerce Deposits / Down Payments Admin
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * The panel
		 *
		 * @var YIT_Plugin_Panel_WooCommerce $panel
		 */
		protected $panel;

		/**
		 * Deposits panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wcdp_panel';

		/**
		 * An array of meta available for the product
		 *
		 * @var array
		 */
		protected $product_meta = array();

		/**
		 * Docs url
		 *
		 * @var string Official documentation url
		 * @since 1.0.0
		 */
		public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-deposits-and-down-payments/';

		/**
		 * Premium landing url
		 *
		 * @var string Premium landing url
		 * @since 1.0.0
		 */
		public $premium_landing_url = 'https://yithemes.com/themes/plugins/yith-woocommerce-deposits-and-down-payments/';

		/**
		 * List of available tab for deposit panel
		 *
		 * @var array
		 * @access public
		 * @since  1.0.0
		 */
		public $available_tabs = array();

		/**
		 * Constructor method
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );

			// register plugin panel.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

			// register plugin links & meta row.
			add_filter( 'plugin_action_links_' . YITH_WCDP_INIT, array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'add_plugin_meta' ), 10, 5 );

			add_action( 'yith_wcdp_email_settings', array( $this, 'email_settings' ) );
			add_action( 'yith_wcdp_print_email_settings', array( $this, 'print_email_settings' ) );

			add_action( 'wp_ajax_yith_wcdp_save_email_settings', array( $this, 'save_email_settings' ) );
			add_action( 'wp_ajax_nopriv_yith_wcdp_save_email_settings', array( $this, 'save_email_settings' ) );

			add_action( 'wp_ajax_yith_wcdp_save_mail_status', array( $this, 'save_mail_status' ) );
			add_action( 'wp_ajax_nopriv_yith_wcdp_save_mail_status', array( $this, 'save_mail_status' ) );
		}

		/**
		 * Init admin functionalities
		 */
		public function init() {
			// init functions.
			YITH_WCDP_Ajax_Handler::init();
			YITH_WCDP_Admin_Products::init();
			YITH_WCDP_Admin_Notices::init();

			// create instances.
			new YITH_WCDP_Admin_Orders();
			new YITH_WCDP_Admin_Panel();
		}

		/* === PANEL METHODS === */

		/**
		 * Return array of screen ids for affiliate plugin
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return mixed Array of available screens
		 * @since 1.0.0
		 */
		public function get_screen_ids( $context = 'view' ) {
			$screen_ids = array(
				'yith-plugins_page_yith_wcdp_panel',
			);

			// in case we're checking for enqueue operation, append additional screens.
			if ( 'enqueue' === $context ) {
				$screen_ids = array_merge(
					$screen_ids,
					array(
						'edit-shop_order',
						'shop_order',
						'product',
						'woocommerce_page_wc-orders',
					)
				);
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_screen_ids
			 *
			 * Filters available screen IDs for Affiliate's plugin.
			 *
			 * @param mixed $screen_ids Array of available screens.
			 *
			 * @return mixed
			 */
			return apply_filters( 'yith_wcdp_screen_ids', $screen_ids );
		}

		/**
		 * Returns true if we're on plugin screen
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return bool Whether we're on plugin's own screen or not
		 */
		public function is_own_screen( $context = 'view' ) {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return false;
			}

			return apply_filters( 'yith_wcdp_is_own_screen', in_array( $screen->id, $this->get_screen_ids( $context ), true ) );
		}

		/**
		 * Returns an array of available tabs for current plugin
		 *
		 * @return array Array of available tabs
		 */
		public function get_available_tabs() {
			if ( ! $this->available_tabs ) {
				// sets available tab.
				/**
				 * APPLY_FILTERS: yith_wcdp_available_admin_tabs
				 *
				 * Filters admin available tabs.
				 *
				 * @param array Array of tabs.
				 *
				 * @return array
				 */
				$this->available_tabs = apply_filters(
					'yith_wcdp_available_admin_tabs',
					array(
						'settings'       => array(
							'title'       => __( 'Deposit options', 'yith-woocommerce-deposits-and-down-payments' ),
							'icon'        => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m9 14.25 6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
							'description' => _x( 'Deposit rules allow overriding the default deposit amount (defined in General Options) for specific user roles, product categories, or products.', '[ADMIN] Deposits table description', 'yith-woocommerce-deposits-and-down-payments' ),
						),
						'balances'       => array(
							'title'       => __( 'Balance options', 'yith-woocommerce-deposits-and-down-payments' ),
							'icon'        => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
							'description' => __( 'Set up balance options for the products in your shop.', 'yith-woocommerce-deposits-and-down-payments' ),
						),
						'customizations' => array(
							'title'       => __( 'Customization', 'yith-woocommerce-deposits-and-down-payments' ),
							'icon'        => '<svg data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m15 11.25 1.5 1.5.75-.75V8.758l2.276-.61a3 3 0 1 0-3.675-3.675l-.61 2.277H12l-.75.75 1.5 1.5M15 11.25l-8.47 8.47c-.34.34-.8.53-1.28.53s-.94.19-1.28.53l-.97.97-.75-.75.97-.97c.34-.34.53-.8.53-1.28s.19-.94.53-1.28L12.75 9M15 11.25 12.75 9" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
							'description' => __( 'Customize texts and labels related to deposit options.', 'yith-woocommerce-deposits-and-down-payments' ),
						),
						'email'          => array(
							'title' => __( 'Email settings', 'yith-woocommerce-deposits-and-down-payments' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>',
						),
					)
				);
			}

			return $this->available_tabs;
		}

		/**
		 * Register panel
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_panel() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$welcome_modals = array(
				'show_in'  => 'panel',
				'on_close' => function () {
					update_option( 'yith_wcdp_welcome_modal_status', 'no' );
				},
				'modals'   => array(
					'welcome' => array(
						'type'        => 'welcome',
						'description' => __( 'Now you can allow your customers to pay a deposit to book products or services and pay the balance at a later time.', 'yith-test-plugin' ),
						'show'        => get_option( 'yith_wcdp_welcome_modal_status', 'welcome' ) === 'welcome',
						'items'       => array(
							'documentation'      => array(),
							'stripe-integration' => array(
								'url'         => 'https://yithemes.com/themes/plugins/yith-woocommerce-stripe/',
								'title'       => __( 'Optional: <mark>get our Stripe plugin</mark> to unlock advanced features', 'yith-woocommerce-deposits-and-down-payments' ),
								'description' => __( 'Use Stripe to automatically charge the orders\' balance to your customers\' credit cards', 'yith-woocommerce-deposits-and-down-payments' ),
							),
							'settings-panel'     => array(
								'url'         => YITH_WCDP_Admin()->get_panel_url(),
								'title'       => __( 'Are you ready? Start by <mark>configuring your deposit options</mark>', 'yith-woocommerce-deposits-and-down-payments' ),
								'description' => __( '... and start the adventure!', 'yith-woocommerce-deposits-and-down-payments' ),
							),
						),
					),
					'update'  => array(
						'type'  => 'update',
						'show'  => get_option( 'yith_wcdp_welcome_modal_status', 'welcome' ) === 'update',
						'since' => '2.0',
						'items' => array(
							'deposit-rules'       => array(
								'url'         => YITH_WCDP_Admin()->get_panel_url( 'deposits' ),
								'title'       => __( 'Welcome our "Deposit Rules" concept', 'yith-woocommerce-deposits-and-down-payments' ),
								'description' => __( 'Override the global amount by creating advanced rules and setting different values for specific user roles, products, or categories.', 'yith-woocommerce-deposits-and-down-payments' ),
							),
							'booking-integration' => array(
								'url'         => 'https://yithemes.com/themes/plugins/yith-woocommerce-booking/',
								'title'       => __( 'New integration: YITH Booking and Appointment for WooCommerce', 'yith-woocommerce-deposits-and-down-payments' ),
								'description' => __( 'Allow your customers to pay a deposit to book products or services, and ask them to pay for the balance on the booking start date or XX days before that date.', 'yith-woocommerce-deposits-and-down-payments' ),
								'cta'         => __( 'Discover our YITH Booking and Appointment for WooCommerce plugin &gt;', 'yith-woocommerce-deposits-and-down-payments' ),
							),
							'stripe-integration'  => array(
								'url'         => 'https://yithemes.com/themes/plugins/yith-woocommerce-stripe/',
								'title'       => __( 'New integration: YITH WooCommerce Stripe', 'yith-woocommerce-deposits-and-down-payments' ),
								'description' => __( 'Automatically charge the balance amount to the same credit card used by the customer to pay for the deposit. This feature allows you to lower the number of unpaid orders and simplify the purchase process.', 'yith-woocommerce-deposits-and-down-payments' ),
								'cta'         => __( 'Discover our YITH WooCommerce Stripe plugin &gt;', 'yith-woocommerce-deposits-and-down-payments' ),
							),
						),
					),
				),

			);

			$args = apply_filters(
				'yith_wcdp_admin_menu_args',
				array(
					'ui_version'       => 2,
					'admin-tabs'       => $this->get_available_tabs(),
					'capability'       => 'manage_options',
					'class'            => yith_set_wrapper_class( 'yith-plugin-ui--classic-wp-list-style' ),
					'create_menu_page' => true,
					'help_tab'         => $this->get_help_tab(),
					'your_store_tools' => $this->get_store_tools_tab(),
					'is_extended'      => false,
					'is_premium'       => true,
					'menu_title'       => 'Deposits / Down Payments',
					'options-path'     => YITH_WCDP_DIR . 'plugin-options',
					'page'             => 'yith_wcdp_panel',
					'page_title'       => 'YITH WooCommerce Deposits / Down Payments',
					'parent'           => '',
					'parent_page'      => 'yith_plugin_panel',
					'parent_slug'      => '',
					'plugin_icon'      => YITH_WCDP_URL . 'assets/images/icon.svg',
					'plugin_slug'      => YITH_WCDP_SLUG,
					'plugin_version'   => YITH_WCDP::VERSION,
					'welcome_modals'   => $welcome_modals,
				)
			);

			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WCDP_DIR . 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Returns url to panel page
		 *
		 * @param string $tab    Tab of the panel to link.
		 * @param array  $params Params to add to url.
		 *
		 * @return string Formatted panel url.
		 */
		public function get_panel_url( $tab = '', $params = array() ) {
			$url = add_query_arg( 'page', 'yith_wcdp_panel', admin_url( 'admin.php' ) );

			if ( ! empty( $tab ) ) {
				$url = add_query_arg( 'tab', $tab, $url );
			}

			if ( ! empty( $params ) ) {
				$url = add_query_arg( $params, $url );
			}

			return $url;
		}

		/**
		 * Retrieve the help tab content.
		 *
		 * @return array
		 */
		protected function get_help_tab(): array {
			return array(
				'main_video' => array(
					'desc' => _x( 'Check this video to learn how to <b>allow your customers to pay a deposit to book products or services and pay the balance at a later time:</b>', '[HELP TAB] Video title', 'yith-woocommerce-deposits-and-down-payments' ),
					'url'  => array(
						'en' => 'https://www.youtube.com/embed/aX0GkGCRMvA',
						'es' => 'https://www.youtube.com/embed/JuXtRPpbWKI',
					),
				),
				'playlists'  => array(
					'en' => 'https://www.youtube.com/playlist?list=PLDriKG-6905l9RTp-SgxCXl85uVDybZPv',
					'es' => 'https://www.youtube.com/playlist?list=PL9Ka3j92PYJNG4iVdMeNvFMcYsb-CnnC4',
				),
				'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003474878-YITH-WOOCOMMERCE-DEPOSITS-AND-DOWN-PAYMENTS',
				'doc_url'    => $this->doc_url,
			);
		}

		/**
		 * Retrieve the store tools tab content.
		 *
		 * @return array
		 */
		protected function get_store_tools_tab(): array {
			return array(
				'items' => array(
					'wishlist'             => array(
						'name'           => 'Wishlist',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/wishlist.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-wishlist/',
						'description'    => _x(
							'Allow your customers to create lists of products they want and share them with family and friends.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Wishlist',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_WCWL_PREMIUM' ),
						'is_recommended' => true,
					),
					'gift-cards'           => array(
						'name'           => 'Gift Cards',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/gift-cards.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-gift-cards/',
						'description'    => _x(
							'Sell gift cards in your shop to increase your earnings and attract new customers.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Gift Cards',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_YWGC_PREMIUM' ),
						'is_recommended' => true,
					),
					'ajax-product-filter'  => array(
						'name'           => 'Ajax Product Filter',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/ajax-product-filter.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/',
						'description'    => _x(
							'Help your customers to easily find the products they are looking for and improve the user experience of your shop.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Ajax Product Filter',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_WCAN_PREMIUM' ),
						'is_recommended' => false,
					),
					'product-addons'       => array(
						'name'           => 'Product Add-Ons & Extra Options',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/product-add-ons.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
						'description'    => _x(
							'Add paid or free advanced options to your product pages using fields like radio buttons, checkboxes, drop-downs, custom text inputs, and more.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Product Add-Ons',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_WAPO_PREMIUM' ),
						'is_recommended' => false,
					),
					'dynamic-pricing'      => array(
						'name'           => 'Dynamic Pricing and Discounts',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/dynamic-pricing-and-discounts.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-dynamic-pricing-and-discounts/',
						'description'    => _x(
							'Increase conversions through dynamic discounts and price rules, and build powerful and targeted offers.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Dynamic Pricing and Discounts',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_YWDPD_PREMIUM' ),
						'is_recommended' => false,
					),
					'customize-my-account' => array(
						'name'           => 'Customize My Account Page',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/customize-myaccount-page.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-customize-my-account-page/',
						'description'    => _x(
							'Customize the My Account page of your customers by creating custom sections with promotions and ad-hoc content based on your needs.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Customize My Account',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_WCMAP_PREMIUM' ),
						'is_recommended' => false,
					),
					'points'               => array(
						'name'        => 'Points and Rewards',
						'icon_url'    => YITH_WCDP_URL . 'assets/images/plugins/points.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-points-and-rewards/',
						'description' => _x(
							'Loyalize your customers with an effective points-based loyalty program and instant rewards.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Points and Rewards',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'   => defined( 'YITH_YWPAR_PREMIUM' ),
					),
					'ajax-search'          => array(
						'name'           => 'Ajax Search',
						'icon_url'       => YITH_WCDP_URL . 'assets/images/plugins/ajax-search.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-search/',
						'description'    => _x(
							'Add an instant search form to your e-commerce shop and help your customers quickly find the products they want to buy.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Ajax Search',
							'yith-woocommerce-deposits-and-down-payments'
						),
						'is_active'      => defined( 'YITH_WCAS_PREMIUM' ),
						'is_recommended' => false,
					),
				),
			);
		}

		/**
		 * Enqueue admin side scripts
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue() {
			if ( ! $this->is_own_screen( 'enqueue' ) ) {
				return;
			}

			$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';

			// register scripts.
			wp_register_script( 'yith-wcdp', YITH_WCDP_URL . 'assets/js/admin/yith-wcdp.bundle' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-mediaelement', 'yith-plugin-fw-fields' ), YITH_WCDP::VERSION, true );
			wp_localize_script(
				'yith-wcdp',
				'yith_wcdp',
				array(
					'labels' => array(
						'add_rule_title'  => __( 'Add rule', 'yith-woocommerce-deposits-and-down-payments' ),
						'edit_rule_title' => __( 'Edit rule', 'yith-woocommerce-deposits-and-down-payments' ),
					),
				)
			);

			wp_register_script( 'yith-wcdp-handle-email-settings', YITH_WCDP_URL . 'assets/js/admin/yith-wcdp-handle-emails-settings.js', array( 'jquery' ), YITH_WCDP::VERSION, true );
			wp_localize_script(
				'yith-wcdp-handle-email-settings',
				'wcdp_data',
				array(
					'loader'                    => YITH_WCDP_URL . 'assets/images/loading.gif',
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'save_email_settings_nonce' => wp_create_nonce( 'yith_wcdp_save_email_settings' ),
					'save_email_status_nonce'   => wp_create_nonce( 'yith_wcdp_save_email_status' ),
				)
			);

			// enqueue scripts.
			/**
			 * DO_ACTION: yith_wcdp_before_admin_script_enqueue
			 *
			 * Action triggered before enqueueing 'yith-wcdp' admin script.
			 */
			do_action( 'yith_wcdp_before_admin_script_enqueue' );
			wp_enqueue_script( 'yith-wcdp' );
			wp_enqueue_script( 'yith-wcdp-handle-email-settings' );

			// register styles.
			wp_register_style( 'yith-wcdp', YITH_WCDP_URL . 'assets/css/yith-wcdp-admin.css', array( 'yith-plugin-fw-fields' ), YITH_WCDP::YITH_WCDP_VERSION );

			// enqueue styles.
			/**
			 * DO_ACTION: yith_wcdp_before_admin_style_enqueue
			 *
			 * Action triggered before enqueueing 'yith-wcdp' admin style.
			 */
			do_action( 'yith_wcdp_before_admin_style_enqueue' );
			wp_enqueue_style( 'yith-wcdp' );
		}

		/* === PLUGIN LINK METHODS === */

		/**
		 * Get the premium landing uri
		 *
		 * @return  string The premium landing link
		 * @since   1.0.0
		 */
		public function get_premium_landing_uri() {
			return $this->premium_landing_url;
		}

		/**
		 * Add plugin action links
		 *
		 * @param array $links Plugins links array.
		 *
		 * @return array Filtered link array
		 * @since 1.0.0
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, 'yith_wcdp_panel', defined( 'YITH_WCDP_PREMIUM_INIT' ), YITH_WCDP_SLUG );

			return $links;
		}

		/**
		 * Adds plugin row meta
		 *
		 * @param array    $new_row_meta_args  New arguments.
		 * @param string[] $plugin_meta        An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
		 * @param string   $plugin_file        Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data        An array of plugin data.
		 * @param string   $status             Status filter currently applied to the plugin list.
		 * @param string   $init_file          Constant with plugin_file.
		 *
		 * @return array Filtered array of plugin meta
		 * @since 1.0.0
		 */
		public function add_plugin_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCDP_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WCDP_SLUG;

				if ( defined( 'YITH_WCDP_PREMIUM_INIT' ) ) {
					$new_row_meta_args['is_premium'] = true;
				}
			}

			return $new_row_meta_args;
		}

		/**
		 * Handle email settings tab
		 * This method based on query string load single email options or the general table
		 *
		 * @since  1.5.0
		 */
		public function email_settings() {
			$emails = apply_filters(
				'yith_wcdp_plugin_emails_array',
				array(
					'YITH_WCDP_Admin_Deposit_Created_Email',
					'YITH_WCDP_Customer_Deposit_Created_Email',
					'YITH_WCDP_Customer_Deposit_Expiring_Email',
				)
			);

			// is a single email view?
			$active = '';

			if ( isset( $_GET['section'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				foreach ( $emails as $email ) {
					if ( strtolower( $email ) === sanitize_text_field( wp_unslash( $_GET['section'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$active = $email;
						break;
					}
				}
			}

			// load mailer.
			$mailer       = WC()->mailer();
			$emails_table = array();

			foreach ( $emails as $email ) {
				$email_class            = $mailer->emails[ $email ];
				$emails_table[ $email ] = array(
					'title'       => $email_class->get_title(),
					'description' => $email_class->get_description(),
					'enable'      => $email_class->is_enabled(),
					'content'     => $email_class->get_content_type(),
				);
			}

			include_once YITH_WCDP_DIR . '/templates/admin/email-settings-tab.php';
		}

		/**
		 * Outout emal settings section
		 *
		 * @param string $email_key Email ID.
		 *
		 * @return void
		 */
		public function print_email_settings( $email_key ) {
			global $current_section;

			$current_section = strtolower( $email_key );
			$mailer          = WC()->mailer();
			$class           = $mailer->emails[ $email_key ];
			WC_Admin_Settings::get_settings_pages();

			if ( ! empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$class->process_admin_options();
			}

			include YITH_WCDP_DIR . '/templates/admin/email-settings-single.php';

			$current_section = null;
		}

		/**
		 * Save email settings in ajax.
		 *
		 * @return void
		 */
		public function save_email_settings() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['security'], $_POST['params'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'yith_wcdp_save_email_settings' ) && current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				parse_str( $_POST['params'], $params ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				unset( $_POST['params'] );

				foreach ( $params as $key => $value ) {
					$_POST[ $key ] = $value;
				}

				global $current_section;

				$email_key       = isset( $_POST['email_key'] ) ? sanitize_text_field( wp_unslash( $_POST['email_key'] ) ) : '';
				$current_section = $email_key;

				$mailer = WC()->mailer();
				$class  = $mailer->emails[ $email_key ];
				$class->process_admin_options();

				$current_section = null;

				wp_send_json_success( array( 'msg' => 'Email updated' ) );
				die();
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Save email status in ajax.
		 *
		 * @return void
		 */
		public function save_mail_status() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['security'], $_POST['email_key'], $_POST['enabled'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'yith_wcdp_save_email_status' ) && current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				$email_key    = strtolower( sanitize_text_field( wp_unslash( $_POST['email_key'] ) ) );
				$email_option = '';

				switch ( $email_key ) {
					case 'yith_wcdp_admin_deposit_created_email':
						$email_option = 'woocommerce_admin_new_deposit_settings';

						break;

					case 'yith_wcdp_customer_deposit_created_email':
						$email_option = 'woocommerce_new_deposit_settings';

						break;

					case 'yith_wcdp_customer_deposit_expiring_email':
						$email_option = 'woocommerce_expiring_deposit_settings';

						break;
				}

				$email_settings = get_option( $email_option );

				if ( is_array( $email_settings ) && ! empty( $email_option ) ) {
					$email_settings['enabled'] = sanitize_text_field( wp_unslash( $_POST['enabled'] ) );
					update_option( $email_option, $email_settings );
				}
			}

			die();
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Build single email settings page
		 *
		 * @param string $email_key The email key.
		 *
		 * @return string
		 * @since  1.5.0
		 */
		public function build_single_email_settings_url( $email_key ) {
			return admin_url( "admin.php?page={$this->panel_page}&tab=email&section=" . strtolower( $email_key ) );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_Admin
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCDP_Admin class
 *
 * @return \YITH_WCDP_Admin
 * @since 1.0.0
 */
function YITH_WCDP_Admin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, Universal.Files.SeparateFunctionsFromOO
	return YITH_WCDP_Admin::get_instance();
}

/**
 * Legacy function, left just for backward compatibility
 *
 * @return \YITH_WCDP_Admin
 */
function YITH_WCDP_Admin_Premium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	_deprecated_function( 'YITH_WCDP_Admin_Premium', '2.0.0', 'YITH_WCDP_Admin' );

	return YITH_WCDP_Admin();
}

// create legacy class alias.
class_alias( 'YITH_WCDP_Admin', 'YITH_WCDP_Admin_Premium' );
