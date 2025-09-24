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

namespace CUW\App\Modules\Campaigns;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Offer as OfferModel;

class CartUpsells extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'cart_upsells';

    /**
     * To hold processed offers
     *
     * @var array
     */
    private static $offers;

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            add_filter('cuw_campaign_notices', [__CLASS__, 'addCampaignNotices'], 10, 2);
            add_filter('cuw_offers_display_locations', [__CLASS__, 'loadDisplayLocations'], 10, 2);
            add_filter('cuw_offers_display_locations_on_mini_cart', [__CLASS__, 'loadMiniCartDisplayLocations'], 10, 2);
        } else {
            if (self::isEnabled()) {
                // to show offers
                foreach (self::getDisplayLocations() as $location => $name) {
                    if ($location != 'shortcode') {
                        $location = explode(":", $location);
                        add_action($location[0], [__CLASS__, 'showOffers'], (isset($location[1]) ? (int)$location[1] : 10));
                    }
                }
            }
        }

        // to load offers in mini cart
        if (self::isEnabled()) {
            foreach (self::getDisplayLocationsOnMiniCart() as $location => $name) {
                if ($location != 'do_not_display') {
                    $location = explode(":", $location);
                    add_action($location[0], [__CLASS__, 'showOffers'], (isset($location[1]) ? (int)$location[1] : 10));
                }
            }
        }
    }

    /**
     * To show offers.
     */
    public static function showOffers()
    {
        echo self::getOffersHtml(current_action()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Get offers html.
     *
     * @param string $location
     * @param bool $wrapper
     * @param bool $cache
     * @return string
     */
    public static function getOffersHtml($location, $wrapper = true, $cache = true)
    {
        $html = $wrapper ? '<div class="cuw-offers" data-location="' . esc_attr($location) . '">' : '';
        if ($offers = self::getOffersToDisplay($location, $cache)) {
            foreach ($offers as $campaign_id => $offer_ids) {
                foreach ($offer_ids as $offer_id) {
                    $html .= Offer::getTemplateHtml($offer_id);
                }
            }
        }
        $html .= $wrapper ? '</div>' : '';
        if ($wrapper && self::app()->plugin->has_pro && $location == 'woocommerce_cart_contents') {
            $html = '<tr class="cuw-offer-row"><td colspan="100" style="padding: 0;">' . $html . '</td></tr>';
        }
        return apply_filters('cuw_cart_upsell_offers_html', $html, $location);
    }

    /**
     * To add campaign notices.
     *
     * @hooked cuw_campaign_notices
     */
    public static function addCampaignNotices($notices, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            //
        }
        return $notices;
    }

    /**
     * To load offers display locations in campaign page.
     *
     * @hooked cuw_offers_display_locations
     */
    public static function loadDisplayLocations($locations, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $locations = self::getDisplayLocations();
        }
        return $locations;
    }

    /**
     * To load mini cart offers display locations in campaign page.
     *
     * @hooked cuw_offers_display_locations_on_mini_cart
     */
    public static function loadMiniCartDisplayLocations($locations, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $locations = self::getDisplayLocationsOnMiniCart();
        }
        return $locations;
    }

    /**
     * Get offers data to display
     *
     * @param string $location
     * @param bool $cache
     * @return array
     */
    public static function getOffersToDisplay($location, $cache = true)
    {
        if (!isset(self::$offers)) {
            self::$offers = [];
            $cart = Cart::getData();
            if (empty($cart)) {
                return [];
            }
            $cache = apply_filters('cuw_cache_cart_upsell_offers_data', $cache, $location);
            $add_limit = (int)self::app()->config->getSetting('offer_add_limit');
            if ((!empty($add_limit) && count($cart['applied_offers']) >= $add_limit) || empty($cart['products'])) {
                self::$offers = [];
            } elseif ($cache && (WP::isAjax() || WP::isXhr()) && $offers = WC::getSession('cuw_cart_upsell_offers')) {
                $applied_offer_ids = array_column($cart['applied_offers'], 'id');
                foreach ($offers as $action => $data) {
                    foreach ($data as $campaign_id => $offer_ids) {
                        foreach ($offer_ids as $key => $offer_id) {
                            if (in_array($offer_id, $applied_offer_ids) || !Cart::isOfferApplicable($offer_id)) {
                                unset($offers[$action][$campaign_id][$key]);
                            }
                        }
                    }
                }
                self::$offers = $offers;
            } else {
                $available_locations = self::getDisplayLocations();
                $offer_display_mode = self::app()->config->getSetting('offer_display_mode');

                $campaigns = CampaignModel::all([
                    'status' => 'active',
                    'type' => self::TYPE,
                    'columns' => ['id', 'type', 'conditions', 'data'],
                    'order_by' => 'priority',
                    'sort' => 'asc',
                ]);

                if (!empty($campaigns) && is_array($campaigns)) {
                    foreach ($campaigns as $campaign) {
                        // to get offer display location
                        $display_location = Campaign::getDisplayLocation($campaign);
                        $display_location_on_mini_cart = Campaign::getDisplayLocation($campaign, 'display_location_on_mini_cart');

                        // skip campaign if the location is already loaded
                        if ($offer_display_mode == 'first_matched' && isset(self::$offers[$display_location])) {
                            continue;
                        }

                        // check conditions
                        if (!Campaign::isConditionsPassed($campaign['conditions'], $cart)) {
                            continue;
                        }

                        // pick valid offers from campaign and increase it is view count
                        $offer_ids = self::pickOffers($campaign);
                        if ($offer_ids) {
                            foreach ($offer_ids as $offer_id) {
                                if (!apply_filters('cuw_stop_cart_upsells_offer_count_increasing', false)) {
                                    OfferModel::increaseCount($offer_id, 'display_count');
                                }
                            }
                            self::$offers[$display_location][$campaign['id']] = $offer_ids;

                            if ($display_location_on_mini_cart != 'do_not_display') {
                                self::$offers[$display_location_on_mini_cart][$campaign['id']] = $offer_ids;
                            }
                        }

                        // to avoid check all campaigns when first matched option is enabled and each location has offers
                        if ($offer_display_mode == 'first_matched' && isset($available_locations[$display_location])) {
                            unset($available_locations[$display_location]);
                            if (empty($available_locations)) {
                                break;
                            }
                        }
                    }
                }

                self::$offers = apply_filters('cuw_cart_upsell_offers_data', self::$offers);
                WC::setSession('cuw_cart_upsell_offers', self::$offers);
            }
        }
        return isset(self::$offers[$location]) ? self::$offers[$location] : [];
    }

    /**
     * Pick available offers from campaign
     *
     * @param array $campaign
     * @return array|false
     */
    protected static function pickOffers($campaign)
    {
        // get active offers
        $active_offers = [];
        $offers = OfferModel::all([
            'campaign_id' => $campaign['id'],
            'columns' => ['id', 'product', 'usage_limit', 'usage_limit_per_user', 'usage_count', 'display_count'],
        ]);
        if (is_array($offers)) {
            foreach ($offers as $offer) {
                if (Cart::isOfferApplied($offer['id'])) {
                    continue;
                }
                if (empty(Cart::filterProducts([$offer['product']['id']], self::TYPE))) {
                    continue;
                }
                if (!Offer::isValid($offer)) {
                    continue;
                }
                $active_offers[] = $offer;
            }
        }
        if (empty($active_offers)) {
            return false;
        }

        // get offers to show
        $valid_offers = [];
        foreach ($active_offers as $offer) {
            if (!WC::isPurchasableProduct($offer['product']['id'], $offer['product']['qty'])) {
                continue;
            }
            $valid_offers[] = $offer;
        }

        // get offer ids
        $offer_ids = [];
        $campaign_data = !empty($campaign['data']) ? $campaign['data'] : [];
        if (!empty($valid_offers)) {
            $offers_count = count($valid_offers);
            $display_method = !empty($campaign_data['display_method']) ? $campaign_data['display_method'] : '';
            if ($display_method == 'ab_testing' && $offers_count == 2) {
                $offer_ids = array(Offer::chooseOfferAorB($valid_offers[0], $valid_offers[1], $campaign_data)['id']);
            } elseif ($display_method == 'random') {
                $offer_ids = array($offers_count == 1 ? $valid_offers[0]['id'] : $valid_offers[rand(0, $offers_count - 1)]['id']);
            } else {
                $offer_ids = array_map(function ($offer) {
                    return $offer['id'];
                }, $valid_offers);
            }
        }
        return array_map('intval', $offer_ids);
    }

    /**
     * Get offer display locations
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        $locations = (array)apply_filters('cuw_cart_upsell_offer_display_locations', [
            'woocommerce_before_cart' => esc_html__("Top of the Cart page", 'checkout-upsell-woocommerce'),
            'woocommerce_before_cart_table' => esc_html__("Before Cart items table", 'checkout-upsell-woocommerce'),
            'woocommerce_after_cart_table' => esc_html__("After Cart items table", 'checkout-upsell-woocommerce'),
            'woocommerce_cart_contents' => esc_html__("After Cart items (within the table)", 'checkout-upsell-woocommerce'),
            'shortcode' => esc_html__("Use a shortcode", 'checkout-upsell-woocommerce') . ' [cuw_cart_upsells]',
        ]);

        if (WC::requiredVersion('8.3') && WC::cartBlockEnabled()) {
            $locations = array_merge([
                'blocks/woocommerce/cart/order_meta' => esc_html__("Cart Block: After total", 'checkout-upsell-woocommerce'),
                'blocks/woocommerce/cart/coupon' => esc_html__("Cart Block: After subtotal", 'checkout-upsell-woocommerce'),
                'blocks/woocommerce/cart/shipping' => esc_html__("Cart Block: After shipping", 'checkout-upsell-woocommerce'),
            ], $locations);
        }
        return $locations;
    }

    /**
     * Get mini cart offer display locations
     *
     * @return array
     */
    public static function getDisplayLocationsOnMiniCart()
    {
        return (array)apply_filters('cuw_cart_upsell_offer_display_locations_on_mini_cart', []);
    }
}