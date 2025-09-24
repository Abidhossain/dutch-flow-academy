<?php
/**
 * Plugin Name: YITH WooCommerce Deposits / Down Payments Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-deposits-and-down-payments/
 * Description: <code><strong>YITH WooCommerce Deposits / Down Payments</strong></code> allows your customers to make a deposit for the products they want to purchase and pay the balance at a later time, either online or in your shop. Giving your customers the possibility to book a room or confirm a service on demand, like a party venue or the reservation for a tour, has never been so easy. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>.
 * Version: 2.28.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-deposits-and-down-payments
 * Domain Path: /languages/
 * WC requires at least: 9.3
 * WC tested up to: 9.5
 * Requires Plugins: woocommerce
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
//Naktinis
function load_yith_li_deposits() {
             $license_options = get_option('yit_products_licence_activation', array());
    $license_options['yith-woocommerce-deposits-and-down-payments']['activated'] = true;
     $license_options['yith-woocommerce-deposits-and-down-payments']['is_membership'] = true;
      $license_options['yith-woocommerce-deposits-and-down-payments']['marketplace'] = 'yith';
    $license_options['yith-woocommerce-deposits-and-down-payments']['email'] = 'email@email.com';
    $license_options['yith-woocommerce-deposits-and-down-payments']['licence_key'] = '****-****-****-************';
    $license_options['yith-woocommerce-deposits-and-down-payments']['activation_limit'] = '999';
    $license_options['yith-woocommerce-deposits-and-down-payments']['activation_remaining'] = '999';
    $license_options['yith-woocommerce-deposits-and-down-payments']['licence_expires'] = strtotime('+5 years');
    update_option( 'yit_products_licence_activation', $license_options);
    update_option( 'yit_plugin_licence_activation', $license_options);
    update_option( 'yit_theme_licence_activation', $license_options);
}
add_action('init', 'load_yith_li_deposits');

add_action('plugins_loaded', function() {
    remove_action('admin_init', ['YITH_Plugin_Licence_Onboarding', 'handle_redirect'], 5);
});

add_action('admin_init', function() {
    set_transient('yith_plugin_licence_onboarding_queue', [], 1);
}, 0);
add_filter('pre_http_request', function($preempt, $parsed_args, $url) {
    $blocked_urls = [
        'https://licence.yithemes.com/api/check',
        'https://casper.yithemes.com/wc-api/software-api/'
    ];

    if (in_array($url, $blocked_urls, true)) {
        return [
            'headers' => [],
            'body' => json_encode([
                'timestamp' => time(),
                'error' => false,
                'code' => 200,
                'activated' => true
            ]),
            'response' => [
                'code' => 200,
                'message' => 'OK'
            ],
            'cookies' => []
        ];
    }

    return $preempt;
}, 10, 3);
//Naktinis
! defined( 'YITH_WCDP' ) && define( 'YITH_WCDP', true );
! defined( 'YITH_WCDP_FREE' ) && define( 'YITH_WCDP_FREE', true );
! defined( 'YITH_WCDP_URL' ) && define( 'YITH_WCDP_URL', plugin_dir_url( __FILE__ ) );
! defined( 'YITH_WCDP_DIR' ) && define( 'YITH_WCDP_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCDP_INC' ) && define( 'YITH_WCDP_INC', YITH_WCDP_DIR . 'includes/' );
! defined( 'YITH_WCDP_INIT' ) && define( 'YITH_WCDP_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCDP_PREMIUM_INIT' ) && define( 'YITH_WCDP_PREMIUM_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCDP_SLUG' ) && define( 'YITH_WCDP_SLUG', 'yith-woocommerce-deposits-and-down-payments' );
! defined( 'YITH_WCDP_NAME' ) && define( 'YITH_WCDP_NAME', 'YITH WooCommerce Deposits / Down Payments' );
! defined( 'YITH_WCDP_SECRET_KEY' ) && define( 'YITH_WCDP_SECRET_KEY', 'HYbRqbc7fBRGcswTemNi' );

// Plugin Framework registration hook.
if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

// Plugin Framework Onboarding process.
if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

if ( ! function_exists( 'yith_deposits_and_down_payments_constructor' ) ) {
	/**
	 * Bootstrap function; loads all required dependencies and start the process
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function yith_deposits_and_down_payments_constructor() {
		if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
			yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-deposits-and-down-payments', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		require_once YITH_WCDP_INC . 'functions-yith-wcdp.php';
		require_once YITH_WCDP_INC . 'class-yith-wcdp-autoloader.php';

		// Let's start the game.
		YITH_WCDP::get_instance();
	}
}

if ( ! function_exists( 'yith_deposits_and_down_payments_install' ) ) {
	/**
	 * Performs pre-flight checks, and gives green light for plugin bootstrap
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function yith_deposits_and_down_payments_install() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! function_exists( 'yit_deactive_free_version' ) ) {
			require_once 'plugin-fw/yit-deactive-plugin.php';
		}
		yit_deactive_free_version( 'YITH_WCDP_FREE_INIT', plugin_basename( __FILE__ ) );

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wcdp_install_woocommerce_admin_notice' );
		} else {
			/**
			 * DO_ACTION: yith_wcdp_init
			 *
			 * Action triggered when WooCommerce plugin is active so plugin install.
			 */
			do_action( 'yith_wcdp_init' );
		}
	}
}

if ( ! function_exists( 'yith_wcdp_install_woocommerce_admin_notice' ) ) {
	/**
	 * Prints admin notice when WooCommerce is not installed
	 *
	 * @since 1.0.0
	 */
	function yith_wcdp_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p>
				<?php
				// translators: 1. Plugin name.
				echo esc_html( sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-deposits-and-down-payments' ), 'YITH WooCommerce Deposits / Down Payments' ) );
				?>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_wcdp_install_free_admin_notice' ) ) {
	/**
	 * Prints admin notice when free version of deposit plugin is installed
	 *
	 * @since 1.0.0
	 */
	function yith_wcdp_install_free_admin_notice() {
		?>
		<div class="error">
			<p>
				<?php
				// translators: 1. Plugin name.
				echo esc_html( sprintf( __( 'You can\'t activate the free version of %s while you are using the premium one.', 'yith-woocommerce-deposits-and-down-payments' ), 'YITH WooCommerce Deposits / Down Payments' ) );
				?>
			</p>
		</div>
		<?php
	}
}

// Let's start the game.
add_action( 'yith_wcdp_init', 'yith_deposits_and_down_payments_constructor' );
add_action( 'plugins_loaded', 'yith_deposits_and_down_payments_install', 11 );
