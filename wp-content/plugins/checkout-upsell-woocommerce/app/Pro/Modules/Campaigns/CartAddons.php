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

namespace CUW\App\Pro\Modules\Campaigns;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\Config;
use CUW\App\Helpers\Product;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Models\Campaign as CampaignModel;

class CartAddons extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'cart_addons';

    /**
     * To hold matched campaign data.
     *
     * @var array|false
     */
    private static $campaign = [];

    /**
     * To hold product meta key.
     */
    const PRODUCTS_META_KEY = 'cuw_cart_addon_product_ids';

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            // on campaign page
            add_filter('cuw_campaign_notices', [__CLASS__, 'addCampaignNotices'], 10, 2);
            add_action('cuw_campaign_contents', [__CLASS__, 'campaignEditView'], 10, 2);

            if (self::isEnabled()) {
                add_filter('cuw_show_upsell_products_data_tab', function ($status, $product_id) {
                    return empty($status) ? !empty(self::getMatchedCampaign($product_id, false)) : $status;
                }, 10, 2);
                add_action('cuw_upsells_product_data_panel', [__CLASS__, 'showProductDataPanel']);
                add_action('woocommerce_process_product_meta', [__CLASS__, 'saveProductMeta']);
            }
        } else {
            if (self::isEnabled()) {
                add_action('wp', function () {
                    if (WC::is('cart')) {
                        foreach (self::getDisplayLocations() as $location => $name) {
                            if ($location != 'shortcode') {
                                $location = explode(":", $location);
                                add_action($location[0], [__CLASS__, 'showProducts'], (isset($location[1]) ? (int)$location[1] : 10), 2);
                            }
                        }
                    }
                }, 1000);

                add_action('woocommerce_cart_emptied', [__CLASS__, 'maybeRemoveCacheFromSession']);
                add_action('woocommerce_remove_cart_item', [__CLASS__, 'maybeRemoveCacheFromSession']);
            }
        }
    }

    /**
     * Show addon products.
     *
     * @hooked woocommerce_after_cart_item_name
     *
     * @param array $cart_item
     * @param string $cart_item_key
     */
    public static function showProducts($cart_item, $cart_item_key)
    {
        $cart_item_product_id = !empty($cart_item['product_id']) && $cart_item['product_id'] > 0 ? $cart_item['product_id'] : 0;
        if (!empty($cart_item['variation_id']) && $cart_item['variation_id'] > 0) {
            $cart_item_product_id = $cart_item['variation_id'];
        }
        if (!isset($cart_item['cuw_product']) && !isset($cart_item['cuw_offer']) && !empty($cart_item_product_id)) {
            $product_ids = self::getProductIdsToDisplay($cart_item_product_id, $cart_item_key);
            if (empty($product_ids)) {
                return;
            }
            $products = [];
            $campaign = self::getMatchedCampaign($cart_item_product_id);
            $extra_data = Campaign::getProductsExtraData($campaign);
            $product_ids = array_diff($product_ids, [$cart_item_product_id]); // remove this product id
            $display_limit = (int)self::app()->config->getSetting('cart_addon_products_display_limit');
            $product_added = false;
            $cart_added_products = Cart::getAddedProducts();
            foreach ($product_ids as $product_id) {
                $item_exists = false;
                $current_item_key = '';
                foreach ($cart_added_products as $key => $added_product) {
                    if ($added_product['product']['id'] == $product_id) {
                        if ($added_product['campaign_id'] != $campaign['id']) {
                            $item_exists = true;
                            break;
                        }
                    }
                    if (isset($added_product['main_item_key']) && $added_product['main_item_key'] == $cart_item_key) {
                        if (isset($added_product['product']['id']) && $added_product['product']['id'] == $product_id) {
                            $current_item_key = $key;
                            $product_added = true;
                            break;
                        }
                    }
                }
                if (empty($current_item_key) && Config::getSetting('smart_products_display') && in_array($product_id, Cart::getProductIds())) {
                    continue;
                }
                $product_data = Product::getData($product_id, [
                    'discount' => isset($campaign['data']['discount']) ? $campaign['data']['discount'] : [],
                    'quantity' => !empty($extra_data['fixed_quantity']) ? $extra_data['fixed_quantity'] : 1,
                    'to_display' => true,
                    'format_title' => true,
                    'display_in' => 'cart',
                    'include_variants' => true,
                    'filter_purchasable' => true,
                ]);
                if (!empty($product_data) && empty($item_exists)) {
                    $product_data['cart_item_key'] = $current_item_key;
                    $products[] = $product_data;
                    if (count($products) == $display_limit) {
                        break;
                    }
                }
            }

            if (!empty($products)) {
                $args = [
                    'products' => $products,
                    'campaign' => $campaign,
                    'cart_item_key' => $cart_item_key,
                    'product_added' => $product_added,
                ];

                echo apply_filters('cuw_cart_addons_template_html', Template::getHtml($campaign, $args), $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
    }

    /**
     * Get product ids to display.
     *
     * @param int|\WC_Product $product_or_id
     * @param string $cart_item_key
     * @return array
     */
    public static function getProductIdsToDisplay($product_or_id, $cart_item_key)
    {
        $ids = [];
        $product = WC::getProduct($product_or_id);
        if (is_object($product) && $campaign = self::getMatchedCampaign($product)) {
            $products_data = Campaign::getProductsData($campaign);
            $use_products = !empty($products_data['use']) ? $products_data['use'] : '';
            if ($use_products == 'custom') {
                $ids = self::getIds($product);
            } elseif ($use_products == 'specific') {
                $ids = !empty($products_data['ids']) ? $products_data['ids'] : [];
            } else {
                $ids = Product::getIds($product, $use_products);
            }

            if ($use_products == 'related') {
                $cached_related_ids = WC::getSession('cart_addons_related_products', []);
                if (!isset($cached_related_ids[$cart_item_key])) {
                    $cached_related_ids[$cart_item_key] = $ids;
                    WC::setSession('cart_addons_related_products', $cached_related_ids);
                }
                $ids = $cached_related_ids[$cart_item_key];
            }

            $cache_recommended_ids = WC::getSession('cart_addons_recommended_products', []);
            if ($use_products == 'engine' && isset($cache_recommended_ids[$cart_item_key])) {
                $ids = $cache_recommended_ids[$cart_item_key];
            } else {
                $ids = (array)apply_filters('cuw_cart_addon_product_ids_to_display', array_unique($ids), $product, $use_products, $campaign);
                if ($use_products == 'engine') {
                    $cache_recommended_ids[$cart_item_key] = $ids;
                    WC::setSession('cart_addons_recommended_products', $cache_recommended_ids);
                }
            }
        }
        return $ids;
    }

    /**
     * Get matched campaign.
     *
     * @param int|\WC_Product $product_or_id
     * @param bool $check_conditions
     * @return array|false
     */
    private static function getMatchedCampaign($product_or_id, $check_conditions = true)
    {
        $product_data = Product::getData($product_or_id, ['filter_purchasable' => true]);
        if (empty($product_data)) {
            return false;
        }
        $product_id = $product_data['id'];
        if (!isset(self::$campaign[$product_id])) {
            self::$campaign[$product_id] = false;

            $campaigns = CampaignModel::all([
                'status' => 'active',
                'type' => 'cart_addons',
                'columns' => ['id', 'title', 'type', 'filters', 'conditions', 'data'],
                'order_by' => 'priority',
                'sort' => 'asc',
            ]);

            if (!empty($campaigns) && is_array($campaigns)) {
                foreach ($campaigns as $campaign) {
                    // check filters
                    if (!Campaign::isFiltersPassed($campaign['filters'], $product_data)) {
                        continue;
                    }

                    // get cart data excluding current campaign products
                    $cart_data = Cart::getData(['exclude_campaign_id' => $campaign['id']]);

                    // check conditions
                    if ($check_conditions && !Campaign::isConditionsPassed($campaign['conditions'], $cart_data)) {
                        continue;
                    }

                    self::$campaign[$product_id] = $campaign;
                    break;
                }
            }
        }
        if (isset(self::$campaign[$product_id]) && is_array(self::$campaign[$product_id])) {
            return (array)self::$campaign[$product_id];
        }
        return false;
    }

    /**
     * Get product IDs.
     *
     * @param int|\WC_Product $product_or_id
     * @return array
     */
    public static function getIds($product_or_id)
    {
        $ids = [];
        $product = WC::getProduct($product_or_id);
        if (!empty($product) && !empty($product->get_parent_id())) {
            $product = WC::getProduct($product->get_parent_id());
        }
        if (!empty($product)) {
            $product_ids = $product->get_meta(self::PRODUCTS_META_KEY);
            if ($product_ids && is_array($product_ids)) {
                $ids = $product_ids;
            }
        }
        return $ids;
    }

    /**
     * To clear cached related product ids form session.
     *
     * @hooked woocommerce_remove_cart_item|woocommerce_cart_emptied
     */
    public static function maybeRemoveCacheFromSession($cart_item_key = null)
    {
        $keys = [
            'cart_addons_related_products',
            'cart_addons_recommended_products',
        ];
        foreach ($keys as $key) {
            $cached_data = WC::getSession($key, []);
            if (!empty($cart_item_key) && is_string($cart_item_key)) {
                if (isset($cached_data[$cart_item_key])) {
                    unset($cached_data[$cart_item_key]);
                    WC::setSession($key, $cached_data);
                }
            } elseif (!empty($cached_data)) {
                WC::setSession($key, null);
            }
        }
    }

    /**
     * To show section on product data metabox.
     *
     * @hooked woocommerce_product_data_panels
     */
    public static function showProductDataPanel($post)
    {
        if (is_object($post) && $product = WC::getProduct()) {
            self::app()->view('Pro/Admin/Campaign/CartAddons', [
                'action' => 'product_edit',
                'post_id' => $post->ID,
                'product_ids' => self::getIds($product),
                'matched_campaign' => self::getMatchedCampaign($product, false),
            ]);
        }
    }

    /**
     * Save data to product meta.
     *
     * @hooked woocommerce_process_product_meta
     */
    public static function saveProductMeta($post_id)
    {
        // save product ids
        if (!empty($_POST['cuw_cart_addon_product_ids'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $product_ids = self::app()->input->get('cuw_cart_addon_product_ids', [], 'post');
            if (is_array($product_ids) && !empty($product_ids)) {
                update_post_meta($post_id, self::PRODUCTS_META_KEY, array_unique($product_ids));
            }
        } else {
            delete_post_meta($post_id, self::PRODUCTS_META_KEY);
        }
    }

    /**
     * To add campaign notices.
     *
     * @hooked cuw_campaign_notices
     */
    public static function addCampaignNotices($notices, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $cart_page_id = get_option('woocommerce_cart_page_id');
            if ($cart_page_id && function_exists('has_block') && $cart_page = get_post($cart_page_id)) {
                if (has_block('woocommerce/cart', $cart_page)) {
                    $notices[] = [
                        'status' => 'info',
                        'message' => __('Seems your cart page is built by using WooCommerce Cart block and we unable to show addon inside the block.', 'checkout-upsell-woocommerce'),
                    ];
                }
            }
        }
        return $notices;
    }

    /**
     * To show campaign customization.
     *
     * @hooked cuw_before_campaign_contents
     */
    public static function campaignEditView($campaign_type, $campaign)
    {

        if ($campaign_type == 'cart_addons') {
            self::app()->view('Pro/Admin/Campaign/CartAddons', [
                'action' => 'campaign_edit',
                'campaign' => $campaign,
            ]);
        }
    }

    /**
     * Get product display locations.
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        return (array)apply_filters('cuw_cart_addon_products_display_locations', [
            'woocommerce_after_cart_item_name' => esc_html__("After Cart item name", 'checkout-upsell-woocommerce'),
        ]);
    }
}