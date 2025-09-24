<?php
/*
Plugin Name: Moneybird API integration for WooCommerce
Plugin URI: https://extensiontree.com/nl/producten/woocommerce-extensies/moneybird-api-koppeling/
Version: 5.35.0
Author: ExtensionTree.com
Author URI: https://extensiontree.com
Description: Automatically create Moneybird invoices for WooCommerce orders. Configure via WooCommerce settings &rarr; Integration.
Text Domain: woocommerce_moneybird
Requires at least: 4.4
Tested up to: 6.8
WC requires at least: 3.0
WC tested up to: 10.0
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('WC_MONEYBIRD_VERSION', '5.35.0');

// Plugin updater
require_once('plugin-update-checker/plugin-update-checker.php');
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

function mb_filter_update_checks($query_args) {
    $settings = get_option('woocommerce_moneybird2_settings');
    if ($settings && !empty($settings['licensekey'])) {
        $query_args['license_key'] = trim($settings['licensekey']);
    } elseif (function_exists('get_sites')) {
        // In case of a multi-site setup, look for a license key in all sites
        foreach (get_sites(array('fields' => 'ids')) as $site_id) {
            $settings = get_blog_option($site_id, 'woocommerce_moneybird2_settings');
            if ($settings && !empty($settings['licensekey'])) {
                $query_args['license_key'] = trim($settings['licensekey']);
                break;
            }
        }
    }
    $query_args['url'] = network_site_url('/');
    return $query_args;
}

function mb_update_request_process($plugin_info, $request_result) {
    if (is_array($request_result)) {
        if ($request_result['response']['code'] > 400) {
            if (!empty($request_result['body'])) {
                global $mb_update_error;
                $mb_update_error = $request_result['body'];
                function mbUpdateErrorNotice() {
                    global $mb_update_error;
                    echo '<div class="error"><p>' . $mb_update_error . '</p></div>';
                }

                add_action('admin_notices', 'mbUpdateErrorNotice');
            }
        }
    }
    return $plugin_info;
}

add_action('plugins_loaded', function () {
    $update_checker = PucFactory::buildUpdateChecker(
        'https://my.extensiontree.com/wp-updates/QBBXQ84NURJVGTQR/',
        __FILE__
    );
    $update_checker->addQueryArgFilter('mb_filter_update_checks');
    $update_checker->addResultFilter('mb_update_request_process');
});

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        // \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('product_block_editor', __FILE__, true);
    }
});

if (is_plugin_active('woocommerce/woocommerce.php')) {
    function add_woocommerce_moneybird_integration($integrations) {
        if (class_exists('WC_Integration')) {
            global $woocommerce;
            if (version_compare($woocommerce->version, '3.0', '>=')) {
                $integrations[] = 'WC_MoneyBird2';
            }
        }
        return $integrations;
    }

    add_filter('woocommerce_integrations', 'add_woocommerce_moneybird_integration');

    function add_woocommerce_moneybirdestimate_gateway($gateways) {
        global $woocommerce;
        if (version_compare($woocommerce->version, '3.0', '>=')) {
            $gateways[] = 'WC_Gateway_MoneybirdEstimate';
        }
        return $gateways;
    }

    add_filter('woocommerce_payment_gateways', 'add_woocommerce_moneybirdestimate_gateway');

    function woocommerce_moneybird_load_textdomain() {
        $locale = apply_filters('plugin_locale', get_locale(), 'woocommerce_moneybird');
        if (strpos($locale, '_') === 2) {
            // Try to load country-independent translations
            $language_code = substr($locale, 0, 2);
            load_textdomain('woocommerce_moneybird', trailingslashit(WP_LANG_DIR) . "woocommerce-moneybird/woocommerce_moneybird-$language_code.mo");
            load_textdomain('woocommerce_moneybird', dirname(__FILE__) . "/languages/woocommerce_moneybird-$language_code.mo");
        }
        load_textdomain('woocommerce_moneybird', trailingslashit(WP_LANG_DIR) . "woocommerce-moneybird/woocommerce_moneybird-$locale.mo");
        load_textdomain('woocommerce_moneybird', dirname(__FILE__) . "/languages/woocommerce_moneybird-$locale.mo");
    }

    function woocommerce_moneybird_init() {
        global $woocommerce;
        if (version_compare($woocommerce->version, '3.0', '>=')) {
            if (class_exists('WC_Integration')) {
                woocommerce_moneybird_load_textdomain();
                require_once('includes/backend_integrations.php');
                require_once('includes/class-wc-gateway-moneybirdestimate.php');
                require_once('includes/class-wc-moneybird2.php');
                require_once('includes/emails.php');
                require_once('includes/endpoints.php');
                require_once('includes/frontend.php');
                require_once('includes/functions.php');
                require_once('includes/shortcodes.php');
                require_once('includes/third_party_integrations.php');
                require_once('includes/webhook.php');
            }

            if (is_admin()) {
                // Make sure recurring tasks are scheduled
                if (!wp_next_scheduled('wc_mb_gc')) {
                    wp_schedule_event(time(), 'hourly', 'wc_mb_gc');
                }
                if (!wp_next_scheduled('wc_mb_update_webhooks')) {
                    wp_schedule_event(time(), 'daily', 'wc_mb_update_webhooks');
                }
            }

            function woocommerce_moneybird_action_links($links) {
                $settings_url = admin_url('admin.php?page=wc-settings&tab=integration&section=moneybird2');
                $plugin_links = array(
                    '<a href="' . $settings_url . '">' . __('Settings', 'woocommerce') . '</a>',
                    '<a href="https://extensiontree.com/nl/documentation/woocommerce-moneybird-api/">' . __('Docs', 'woocommerce') . '</a>',
                );
                return array_merge($plugin_links, $links);
            }

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woocommerce_moneybird_action_links');
        }
    }

    add_action('plugins_loaded', 'woocommerce_moneybird_init');

} // if woocommerce active

function woocommerce_moneybird_deactivate() {
    wp_clear_scheduled_hook('wc_mb_gc');
    wp_clear_scheduled_hook('wc_mb_handle_invoice_queue');
    wp_clear_scheduled_hook('wc_mb_maybe_handle_invoice_queue');
    if (function_exists('\ExtensionTree\WCMoneyBird\update_webhooks')) {
        \ExtensionTree\WCMoneyBird\update_webhooks(true);

    }
}

function WCMB() {
    // Return WC_MoneyBird2 instance or false if it is not available
    $woocommerce = WC();
    if ($woocommerce && isset($woocommerce->integrations->integrations)) {
        $integrations = $woocommerce->integrations->integrations;
        if (isset($integrations['moneybird2'])) {
            return $integrations['moneybird2'];
        }
    }
    return false;
}

register_deactivation_hook(__FILE__, 'woocommerce_moneybird_deactivate');
