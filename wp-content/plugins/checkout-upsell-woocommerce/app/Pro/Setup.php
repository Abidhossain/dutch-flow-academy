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

namespace CUW\App\Pro;

use CUW\App\Helpers\Config;
use CUW\App\Pro\Helpers\License;
use CUW\App\Pro\Models\Engine;
use CUW\App\Pro\Models\CampaignEngine;

defined('ABSPATH') || exit;

class Setup
{
    /**
     * Init setup.
     */
    public static function init()
    {
        License::init();

        add_action('cuw_core_migrated', [__CLASS__, 'maybeRunMigration']);
        add_action('plugins_loaded', [__CLASS__, 'maybeRunMigration']);
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
        $current_pro_version = Config::get('current_pro_version');
        if (empty($current_pro_version) || version_compare($current_pro_version, $plugin_version, '<')) {
            self::runDatabaseMigration();
            self::runSettingsMigration();
            Config::set('current_pro_version', $plugin_version);

            do_action('cuw_pro_migrated', $plugin_version);
        }
    }

    /**
     * Run database migration
     */
    public static function runDatabaseMigration()
    {
        $models = [
            new Engine(),
            new CampaignEngine(),
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
        $settings = Config::get('settings');
        if (!empty($settings) && !isset($settings['process_post_purchase'])) {
            Config::set('settings', array_merge($settings, [
                'process_post_purchase' => Config::get('process_post_purchase', 'before_payment'),
                'show_order_info_notice' => Config::get('show_order_status_notice', '1'),
                'addon_badge_text' => Config::get('addon_text', 'Add-On'),
                'product_addon_products_display_limit' => Config::get('product_addon_products_display_limit', '3'),
                'cart_addon_products_display_limit' => Config::get('cart_addon_products_display_limit', '2'),
                'upsell_popup_products_display_limit' => Config::get('upsell_popup_products_display_limit', '3'),
                'thankyou_upsell_products_display_limit' => Config::get('thankyou_upsells_display_limit', '3'),
            ]));
        }

        $license = Config::get('license');
        if ($license === false) {
            Config::set('license', [
                'key' => Config::get('license_key', ''),
            ]);
        }
    }
}