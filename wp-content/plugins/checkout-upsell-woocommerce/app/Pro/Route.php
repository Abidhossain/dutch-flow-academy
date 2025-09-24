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

use CUW\App\Pro\Controllers\Admin\Ajax;
use CUW\App\Pro\Controllers\Common\Campaigns;
use CUW\App\Pro\Controllers\Common\Conditions;
use CUW\App\Pro\Controllers\Common\Engines;
use CUW\App\Pro\Controllers\Common\Filters;
use CUW\App\Pro\Controllers\Common\Shortcodes;
use CUW\App\Pro\Controllers\Store\Cart;

defined('ABSPATH') || exit;

class Route
{
    /**
     * To add hooks
     */
    public static function init()
    {
        self::addGeneralHooks();
        if (is_admin()) {
            self::addAdminHooks();
        } else {
            self::addStoreHooks();
        }

        Ajax::load();
        Campaigns::load();
        Conditions::load();
        Filters::load();
        Shortcodes::load();
        Engines::init();
    }

    /**
     * To add general hooks
     */
    private static function addGeneralHooks()
    {
        add_filter('cuw_default_settings', [Campaigns::class, 'loadDefaultSettings']);
        add_filter('cuw_default_settings', [Engines::class, 'loadDefaultSettings']);
        add_filter('cuw_campaign_default_display_locations', [Campaigns::class, 'loadDefaultDisplayLocations']);
    }

    /**
     * To add admin area and ajax hooks
     */
    private static function addAdminHooks()
    {
        add_filter('cuw_settings_default_tab', function () {
            return 'license';
        }, 100);
        add_action('cuw_before_settings_tabs', [Campaigns::class, 'loadLicenseTabView'], 1);
        add_action('cuw_before_settings_tab_contents', [Campaigns::class, 'loadLicenseTabView'], 1);

        add_filter('cuw_offers_per_campaign', [Campaigns::class, 'offersPerPage'], 10, 2);
        add_action('cuw_after_campaigns_settings', [Campaigns::class, 'loadSettingsView']);
        add_action('cuw_after_campaigns_setting_tab', [Campaigns::class, 'loadSettingsView']);
        add_action('cuw_after_settings_tab_contents', [Campaigns::class, 'loadSettingsView']);

        add_filter('cuw_validate_campaign', [Campaigns::class, 'validate'], 10, 2);

        add_filter('cuw_page_localize_engine_data', [Engines::class, 'localizeEngineData']);

        add_action('cuw_campaign_saved', [Engines::class, 'linkEngine'], 10, 2);
        add_action('cuw_campaign_deleted', [Engines::class, 'unLinkEngine']);
    }

    /**
     * To add store (front-end) hooks
     */
    private static function addStoreHooks()
    {
        // to add add-on label after cart item name
        add_filter('woocommerce_cart_item_name', [Cart::class, 'addAddonLabel'], 100, 2);

        // to add recently viewed products cookie
        add_action('wp_footer', [Engines::class, 'setRecentlyViewedProductsCookie']);
    }
}