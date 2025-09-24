<?php
/**
 * Plugin Name:          UpsellWP – WooCommerce Upsell and Related Products Offers
 * Plugin URI:           https://upsellwp.com
 * Description:          Boost your store revenue by presenting upsell offers, products and next order coupons to your customers.
 * Version:              2.2.1
 * Requires at least:    6.0
 * Requires PHP:         7.0
 * Author:               UpsellWP
 * Slug:                 checkout-upsell-and-order-bumps
 * Text Domain:          checkout-upsell-and-order-bumps
 * Requires Plugins:     woocommerce
 * Domain Path:          /i18n/languages
 * License:              GPL v3 or later
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 *
 * WC requires at least: 6.5
 * WC tested up to:      10.0
 */

defined('ABSPATH') || exit;

if (!function_exists('cuw_pro_is_active')) {
    function cuw_pro_is_active(): bool // to check pro plugin is active or not
    {
        $plugin_file = 'checkout-upsell-woocommerce/checkout-upsell-woocommerce.php';
        $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));
        if (function_exists('is_multisite') && is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array($plugin_file, $active_plugins) || array_key_exists($plugin_file, $active_plugins);
    }
}

if (!cuw_pro_is_active()) {
    // define basic plugin constants
    defined('CUW_PLUGIN_FILE') || define('CUW_PLUGIN_FILE', __FILE__);
    defined('CUW_PLUGIN_PATH') || define('CUW_PLUGIN_PATH', plugin_dir_path(__FILE__));
    defined('CUW_PLUGIN_NAME') || define('CUW_PLUGIN_NAME', 'UpsellWP Lite');
    defined('CUW_VERSION') || define('CUW_VERSION', '2.2.1');

    // to load composer autoload (psr-4)
    if (file_exists(CUW_PLUGIN_PATH . '/vendor/autoload.php')) {
        require CUW_PLUGIN_PATH . '/vendor/autoload.php';
    }

    // to bootstrap the plugin
    if (class_exists('CUW\App\Core') && !function_exists('CUW')) {
        /**
         * Returns primary instance.
         *
         * @return \CUW\App\Core
         */
        function CUW(): \CUW\App\Core
        {
            return \CUW\App\Core::instance();
        }

        // init setup
        \CUW\App\Setup::init();

        // check dependencies and load plugin hooks
        add_action('plugins_loaded', function () {
            if (\CUW\App\Helpers\Plugin::checkDependencies()) {
                do_action('cuw_before_init');
                \CUW\App\Route::init();
                do_action('cuw_after_init');
            }
        }, 20);
    }
}

// to declare WooCommerce features compatibility
add_action('before_woocommerce_init', function () {
    if (class_exists('CUW\App\Helpers\WC')) {
        \CUW\App\Helpers\WC::declareFeatureCompatibility('custom_order_tables', __FILE__);
    }
});
