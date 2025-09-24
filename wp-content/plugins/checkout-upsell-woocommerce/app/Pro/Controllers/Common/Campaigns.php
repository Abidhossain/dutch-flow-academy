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

namespace CUW\App\Pro\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Pro\Helpers\Validate;
use CUW\App\Pro\Modules\Campaigns\CartAddons;
use CUW\App\Pro\Modules\Campaigns\DoubleOrder;
use CUW\App\Pro\Modules\Campaigns\PostPurchase;
use CUW\App\Pro\Modules\Campaigns\PostPurchaseUpsells;
use CUW\App\Pro\Modules\Campaigns\ProductAddons;
use CUW\App\Pro\Modules\Campaigns\ThankyouUpsells;
use CUW\App\Pro\Modules\Campaigns\UpsellPopups;
use CUW\App\Pro\Modules\Campaigns\ProductRecommendations;

class Campaigns extends Controller
{
    /**
     * To get shortcodes.
     *
     * @return array
     */
    private static function get()
    {
        return [
            'post_purchase' => [
                'handler' => new PostPurchase(),
            ],
            'double_order' => [
                'handler' => new DoubleOrder(),
            ],
            'thankyou_upsells' => [
                'handler' => new ThankyouUpsells()
            ],
            'upsell_popups' => [
                'handler' => new UpsellPopups()
            ],
            'product_addons' => [
                'handler' => new ProductAddons()
            ],
            'cart_addons' => [
                'handler' => new CartAddons()
            ],
            'product_recommendations' => [
                'handler' => new ProductRecommendations()
            ],
            'post_purchase_upsells' => [
                'handler' => new PostPurchaseUpsells()
            ],
        ];
    }

    /**
     * To load pro campaigns.
     */
    public static function load()
    {
        add_filter('cuw_campaigns', function ($campaigns) {
            return array_merge_recursive($campaigns, self::get());
        });
    }

    /**
     * To update max offer limit per campaign.
     *
     * @hooked cuw_offers_per_campaign
     */
    public static function offersPerPage($limit, $campaign_type)
    {
        if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells'])) {
            $limit = 10;
        }
        return $limit;
    }

    /**
     * To load offer section view.
     *
     * @hooked cuw_after_offer_tabs|cuw_after_offer_tab_contents
     */
    public static function loadOfferSectionView($campaign_type, $campaign)
    {
        self::app()->view('Pro/Admin/Offer/Section', compact('campaign_type', 'campaign'));
    }

    /**
     * To load license tab and view.
     *
     * @hooked cuw_before_settings_tabs|cuw_before_settings_tab_contents
     */
    public static function loadLicenseTabView()
    {
        self::app()->view('Pro/Admin/Tabs/License');
    }

    /**
     * To load settings view.
     *
     * @hooked cuw_after_campaigns_settings
     */
    public static function loadSettingsView($settings)
    {
        self::app()->view('Pro/Admin/Tabs/Settings', ['settings' => $settings]);
    }

    /**
     * To load pro settings.
     *
     * @hooked cuw_default_settings
     */
    public static function loadDefaultSettings($settings)
    {
        // translatable default texts
        __('Add-On', 'checkout-upsell-woocommerce');
        __('Exclusive offer', 'checkout-upsell-woocommerce');

        return array_merge($settings, [
            'process_post_purchase' => 'before_payment',
            'show_order_info_notice' => '1',
            'ppu_page_title' => 'Exclusive offer',
            'addon_badge_text' => 'Add-On',
            'product_addon_products_display_limit' => '3',
            'cart_addon_products_display_limit' => '2',
            'upsell_popup_products_display_limit' => '3',
            'thankyou_upsell_products_display_limit' => '3',
        ]);
    }

    /**
     * To load default display locations.
     *
     * @hooked cuw_campaign_default_display_locations
     */
    public static function loadDefaultDisplayLocations($locations)
    {
        return array_merge($locations, [
            'product_addons_display_location' => 'woocommerce_before_add_to_cart_button',
            'cart_addons_display_location' => 'woocommerce_after_cart_item_name',
            'thankyou_upsells_display_location' => 'woocommerce_before_thankyou',
            'double_order_display_location' => 'woocommerce_review_order_before_payment',
        ]);
    }

    /**
     * Validate campaign
     *
     * @hooked cuw_campaign_validate
     */
    public static function validate($errors, $data)
    {
        return Validate::validateCampaign($errors, $data);
    }
}