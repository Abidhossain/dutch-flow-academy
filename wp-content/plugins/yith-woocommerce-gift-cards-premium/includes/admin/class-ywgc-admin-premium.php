<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class YITH_YWGC_Admin_Premium
 *
 * @package YITH\GiftCards\Includes\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Admin_Premium' ) ) {
	/**
	 * YITH_YWGC_Admin_Premium class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Admin_Premium extends YITH_YWGC_Admin_Extended {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Admin_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 */
		public function __construct() {
			parent::__construct();

			/**
			 * Register plugin to licence/update system.
			 */
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );		}

		/**
		 * Retrieve the documentation URL.
		 *
		 * @return string
		 */
		protected function get_doc_url(): string {
			return 'https://docs.yithemes.com/yith-woocommerce-gift-cards/';
		}

		/**
		 * Retrieve the admin panel tabs.
		 *
		 * @return array
		 */
		protected function get_admin_panel_tabs(): array {
			return apply_filters(
				'yith_ywgc_admin_panel_tabs',
				array(
					'dashboard' => array(
						'title' => _x( 'Dashboard', 'Settings tab name', 'yith-woocommerce-gift-cards' ),
						'icon'  => 'dashboard',
					),
					'settings'  => array(
						'title' => _x( 'Settings', 'Settings tab name', 'yith-woocommerce-gift-cards' ),
						'icon'  => 'settings',
					),
					'email'     => array(
						'title' => __( 'Email Settings', 'yith-woocommerce-gift-cards' ),
						'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>',
					),
					'modules'   => array(
						'title'       => _x( 'Modules', 'Modules tab name', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Enable the following modules to unlock additional features for your gift cards.', 'yith-woocommerce-gift-cards' ),
						'icon'        => 'add-ons',
					),
				)
			);
		}

		/**
		 * Retrieve the help tab content.
		 *
		 * @return array
		 */
		protected function get_help_tab(): array {
			return array(
				'main_video' => array(
					'desc' => _x( 'Check this video to learn how to <b>create your first virtual gift card and start selling:</b>', '[HELP TAB] Video title', 'yith-woocommerce-gift-cards' ),
					'url'  => array(
						'en' => 'https://www.youtube.com/embed/PL40S1IPrI8',
						'it' => 'https://www.youtube.com/embed/gIffwmlrgFM',
						'es' => 'https://www.youtube.com/embed/iYknYzwQDl8',
					),
				),
				'playlists'  => array(
					'en' => 'https://youtube.com/playlist?list=PLDriKG-6905mz6rYiuD3Yh1u31218PYO0',
					'it' => 'https://youtube.com/playlist?list=PL9c19edGMs0-g0CCdCwqqm1SQC_dhuhCI',
					'es' => 'https://youtube.com/playlist?list=PL9Ka3j92PYJOeBKVIc97SeWxwWKr_ljst',
				),
				'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003468397-YITH-WOOCOMMERCE-GIFT-CARDS',
				'doc_url'    => $this->get_doc_url(),
			);
		}

		/**
		 * Retrieve the content for the welcome modals.
		 *
		 * @return array
		 */
		protected function get_welcome_modals(): array {
			return array(
				'show_in'  => 'panel',
				'on_close' => function () {
					update_option( 'yith-ywgc-welcome-modal', 'no' );
				},
				'modals'   => array(
					'welcome' => array(
						'type'        => 'welcome',
						'description' => __( 'With this plugin you can create different gift card products and allow your customers to send it to a friend or a loved one.', 'yith-woocommerce-gift-cards' ),
						'show'        => get_option( 'yith-ywgc-welcome-modal', 'welcome' ) === 'welcome',
						'items'       => array(
							'documentation'  => array(
								'url' => $this->get_doc_url(),
							),
							'create-product' => array(
								'title'       => __( 'Are you ready? Create your first <mark>gift card product</mark>', 'yith-woocommerce-gift-cards' ),
								'description' => __( '...and start the adventure!', 'yith-woocommerce-gift-cards' ),
								'url'         => add_query_arg(
									array(
										'yith-ywgc-new-gift-card-product' => 1,
									)
								),
							),
						),
					),
				),
			);
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( function_exists( 'YIT_Plugin_Licence' ) ) {
				YIT_Plugin_Licence()->register( YITH_YWGC_INIT, YITH_YWGC_SECRET_KEY, YITH_YWGC_SLUG );
			}
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( function_exists( 'YIT_Upgrade' ) ) {
				YIT_Upgrade()->register( YITH_YWGC_SLUG, YITH_YWGC_INIT );
			}
		}
	}
}
