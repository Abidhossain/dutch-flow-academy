<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App;

use CUW\App\Controllers\Common\Cron;
use CUW\App\Helpers\Config;
use CUW\App\Models\Campaign;
use CUW\App\Models\Offer;
use CUW\App\Models\Stats;

defined('ABSPATH') || exit;

class Setup
{
    /**
     * Init setup
     */
    public static function init()
    {
        register_activation_hook(CUW_PLUGIN_FILE, [__CLASS__, 'activate']);
        register_deactivation_hook(CUW_PLUGIN_FILE, [__CLASS__, 'deactivate']);
        register_uninstall_hook(CUW_PLUGIN_FILE, [__CLASS__, 'uninstall']);

        add_action('plugins_loaded', [__CLASS__, 'maybeRunMigration']);
        add_action('upgrader_process_complete', [__CLASS__, 'maybeRunMigration']);
    }

    /**
     * Run plugin activation scripts
     */
    public static function activate()
    {
        Cron::scheduleEvents();
        self::maybeRunMigration();
    }

    /**
     * Run plugin activation scripts
     */
    public static function deactivate()
    {
        Cron::unscheduleEvents();
    }

    /**
     * Run plugin activation scripts
     */
    public static function uninstall()
    {
        // silence is golden
    }

    /**
     * Maybe run database migration
     */
    public static function maybeRunMigration()
    {
        if (!is_admin()) {
            return;
        }

        $plugin_version = Config::get('plugin.version');
        $current_version = Config::get('current_version');
        if (empty($current_version) || version_compare($current_version, $plugin_version, '<')) {
            Cron::scheduleEvents();
            self::runDatabaseMigration();
            self::runSettingsMigration();
            Config::set('current_version', $plugin_version);

            do_action('cuw_core_migrated', $plugin_version);
        }
    }

    /**
     * Run database migration
     */
    private static function runDatabaseMigration()
    {
        $models = [
            new Campaign(),
            new Offer(),
            new Stats(),
        ];

        foreach ($models as $model) {
            $model->create();
        }
    }

    /**
     * Run settings migration.
     */
    public static function runSettingsMigration()
    {
        if (empty(Config::get('settings'))) {
            Config::set('settings', [
                'show_product_details' => Config::get('show_product_details', (Config::get('enabled_permalink') ? 'in_new_tab' : 'disable')),
                'calculate_discount_from' => Config::get('discount_from', 'regular_price'),
                'exclude_coupon_discounts' => Config::get('exclude_offer_from_discounts', ''),
                'smart_products_display' => (Config::get('smart_offer_display', '') || Config::get('smart_addon_display', '')) ? '1' : '',
                'dynamic_offer_display' => Config::get('dynamic_offer_display', ''),
                'offer_add_limit' => Config::get('offers_add_limit', ''),
                'offer_display_mode' => Config::get('offer_display_mode', 'first_matched'),
                'offer_added_notice_message' => Config::get('offer_added_notice_message', 'Offer applied successfully.'),
                'offer_notice_display_location' => Config::get('offer_added_notice_position', 'default'),
                'fbt_products_display_limit' => Config::get('fbt_display_limit', '2'),
            ]);
        }
    }

    /**
     * Add offer page if not exists
     */
    public static function addOfferPage()
    {
        if (!Config::get('default_offer_page_id') && function_exists('wp_insert_post')) {
            $content = Core::instance()->template('page/content-default', [], false);
            if ($content) {
                Config::set('default_offer_page_id', wp_insert_post([
                    'post_type' => 'page',
                    'post_name' => 'cuw-offer',
                    'post_status' => 'publish',
                    'post_title' => 'Exclusive Offer',
                    'post_content' => $content,
                    'ping_status' => 'closed',
                    'comment_status' => 'closed',
                ]));
            }
        }
    }
}