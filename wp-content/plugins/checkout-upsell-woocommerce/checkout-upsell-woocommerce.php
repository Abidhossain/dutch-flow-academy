<?php
/**
 * Plugin Name:          UpsellWP PRO - WooCommerce Upsell, Cross-sell and Order Bumps
 * Plugin URI:           https://upsellwp.com
 * Description:          Boost your store revenue by presenting upsell offers, products, add-ons, popups, next order coupons and post-purchase offers to your customers.
 * Version:              2.1.4
 * Requires at least:    5.3
 * Requires PHP:         7.0
 * Author:               UpsellWP
 * Author URI:           https://upsellwp.com
 * Text Domain:          checkout-upsell-woocommerce
 * Domain Path:          /i18n/languages
 * License:              GPL v3 or later
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 *
 * WC requires at least: 4.4
 * WC tested up to:      9.3
 */

defined('ABSPATH') || exit;

// define basic plugin constants
defined('CUW_PLUGIN_FILE') || define('CUW_PLUGIN_FILE', __FILE__);
defined('CUW_PLUGIN_PATH') || define('CUW_PLUGIN_PATH', plugin_dir_path(__FILE__));
defined('CUW_PLUGIN_NAME') || define('CUW_PLUGIN_NAME', 'UpsellWP PRO');
defined('CUW_VERSION') || define('CUW_VERSION', '2.1.4');

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
    if (class_exists('\CUW\App\Pro\Setup')) {
        \CUW\App\Pro\Setup::init();
    }

    // check dependencies and load plugin hooks
    add_action('plugins_loaded', function () {
        if (\CUW\App\Helpers\Plugin::checkDependencies()) {
            do_action('cuw_before_init');
            if (class_exists('\CUW\App\Pro\Route')) {
                \CUW\App\Pro\Route::init();
            }
            \CUW\App\Route::init();
            do_action('cuw_after_init');
        }

        $i18n_path = dirname(plugin_basename(CUW_PLUGIN_FILE)) . '/i18n/languages';
        load_plugin_textdomain('checkout-upsell-woocommerce', false, $i18n_path);
    }, 20);
}

// to declare WooCommerce features compatibility
add_action('before_woocommerce_init', function () {
    if (class_exists('CUW\App\Helpers\WC')) {
        \CUW\App\Helpers\WC::declareFeatureCompatibility('custom_order_tables', __FILE__);
    }
});

