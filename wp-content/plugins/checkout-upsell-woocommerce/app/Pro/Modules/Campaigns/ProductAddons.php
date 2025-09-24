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

use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\Product;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Models\Campaign as CampaignModel;

defined('ABSPATH') || exit;

class ProductAddons extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'product_addons';

    /**
     * To hold matched campaign data.
     *
     * @var array|false
     */
    private static $campaign = [];

    /**
     * To hold product meta key.
     */
    const PRODUCTS_META_KEY = 'cuw_product_addons_product_ids';

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            // on campaign page
            add_action('cuw_campaign_contents', [__CLASS__, 'campaignEditView'], 10, 2);

            if (self::isEnabled()) {
                add_filter('cuw_show_upsell_products_data_tab', function ($status, $product_id) {
                    return empty($status) ? !empty(self::getMatchedCampaign($product_id)) : $status;
                }, 10, 2);
                add_action('cuw_upsells_product_data_panel', [__CLASS__, 'showProductDataPanel']);
                add_action('woocommerce_process_product_meta', [__CLASS__, 'saveProductMeta']);
            }
        } else {
            if (self::isEnabled()) {
                add_action('wp', function () {
                    if (WC::is('product') && $location = self::getProductsDisplayLocation()) {
                        if ($location != 'shortcode') {
                            $location = explode(":", $location);
                            add_action($location[0], [__CLASS__, 'showProducts'], (isset($location[1]) ? (int)$location[1] : 10));
                        }
                    }
                }, 1000);
            }
            add_filter('cuw_product_addons_template_product_variants', [__CLASS__, 'loadVariantSelect'], 10, 3);
        }

        if (self::isEnabled()) {
            add_action('woocommerce_add_to_cart', [__CLASS__, 'addProductsToCart'], 15);
        }
    }

    /**
     * To load product variants select
     *
     * @hooked cuw_product_addon_variants
     */
    public static function loadVariantSelect($html, $product, $args = [])
    {
        $template_name = self::app()->config->getSetting('variant_select_template');
        return self::app()->template('addon/' . $template_name, ['product' => $product, 'args' => $args], false);
    }

    /**
     * To add products to the cart.
     */
    public static function addProductsToCart($cart_item_key)
    {
        if (!empty($_REQUEST['cuw_add_to_cart']) && $_REQUEST['cuw_add_to_cart'] == self::TYPE && !empty($cart_item_key) && did_action('woocommerce_add_to_cart') == 1) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $campaign = [];
            $products = self::app()->input->get('products', [], 'post');
            $campaign_id = self::app()->input->get('campaign_id', '', 'post');
            if (is_numeric($campaign_id)) {
                $campaign = CampaignModel::get($campaign_id);
            }

            if (!empty($campaign) && !empty($products) && is_array($products)) {
                $items = [];
                foreach ($products as $product) {
                    if (!empty($product['id'])) {
                        $items[$product['id']] = [
                            'product_id' => $product['id'],
                            'quantity' => $product['qty'] ?? 1,
                            'variation_id' => $product['variation_id'] ?? 0,
                            'variation_attributes' => $product['variation_attributes'] ?? [],
                        ];
                    }
                }

                if (!empty($items)) {
                    $extra_data = Campaign::getProductsExtraData($campaign);
                    $extra_data = array_merge($extra_data, ['main_item_key' => $cart_item_key]);
                    foreach ($items as $item) {
                        $item_quantity = !empty($extra_data['fixed_quantity']) ? $extra_data['fixed_quantity'] : $item['quantity'];
                        Cart::addProduct($campaign, $item['product_id'], $item_quantity, $item['variation_id'], $item['variation_attributes'], $extra_data);
                    }
                }
            }
        }
    }

    /**
     * To show products.
     */
    public static function showProducts()
    {
        $product = WC::getProduct();
        if (is_object($product) && $product_ids = self::getProductIdsToDisplay($product)) {
            $products = [];
            $main_product_id = $product->get_id();
            $display_limit = (int)self::app()->config->getSetting('product_addon_products_display_limit');
            $campaign = self::getMatchedCampaign($product);
            $discount = isset($campaign['data']['discount']) ? $campaign['data']['discount'] : [];
            $extra_data = Campaign::getProductsExtraData($campaign);
            $product_ids = array_diff($product_ids, [$main_product_id]); // ignore main product id
            foreach ($product_ids as $product_id) {
                $product_data = Product::getData($product_id, [
                    'discount' => $discount,
                    'quantity' => !empty($extra_data['fixed_quantity']) ? $extra_data['fixed_quantity'] : 1,
                    'to_display' => true,
                    'display_in' => 'shop',
                    'format_title' => true,
                    'include_variants' => true,
                    'filter_purchasable' => true,
                ]);
                if (!empty($product_data)) {
                    $product_data['classes'] = [];
                    $product_data['main_product_id'] = $main_product_id;
                    if ($product_data['is_variable']) {
                        $product_data['classes'][] = 'is_variable';
                    }
                    $products[] = $product_data;
                    if (count($products) == $display_limit) {
                        break;
                    }
                }
            }

            $product_regular_price = apply_filters('cuw_raw_product_regular_price', $product->get_regular_price(), $product, 'shop');
            $product_price = apply_filters('cuw_raw_product_price', $product->get_price(), $product, 'shop');
            $campaign['main_product_regular_price'] = WC::getPriceToDisplay($product, $product_regular_price);
            $campaign['main_product_price'] = WC::getPriceToDisplay($product, $product_price);

            if (!empty($products)) {
                $args = [
                    'products' => $products,
                    'campaign' => $campaign,
                ];

                echo apply_filters('cuw_product_addons_template_html', Template::getHtml($campaign, $args), $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
    }

    /**
     * To show campaign customization.
     *
     * @hooked cuw_before_campaign_contents
     */
    public static function campaignEditView($campaign_type, $campaign)
    {
        if ($campaign_type == 'product_addons') {
            self::app()->view('Pro/Admin/Campaign/ProductAddons', [
                'action' => 'campaign_edit',
                'campaign' => $campaign,
            ]);
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
            self::app()->view('Pro/Admin/Campaign/ProductAddons', [
                'action' => 'product_edit',
                'post_id' => $post->ID,
                'product_ids' => self::getIds($product),
                'matched_campaign' => self::getMatchedCampaign($product),
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
        if (!empty($_POST['cuw_product_addons_product_ids'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $product_ids = self::app()->input->get('cuw_product_addons_product_ids', [], 'post');
            if (is_array($product_ids) && !empty($product_ids)) {
                update_post_meta($post_id, self::PRODUCTS_META_KEY, array_unique($product_ids));
            }
        } else {
            delete_post_meta($post_id, self::PRODUCTS_META_KEY);
        }
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
     * Get matched campaign.
     *
     * @param int|\WC_Product $product_or_id
     * @return array|false
     */
    private static function getMatchedCampaign($product_or_id)
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
                'type' => 'product_addons',
                'columns' => ['id', 'title', 'type', 'filters', 'data'],
                'order_by' => 'priority',
                'sort' => 'asc',
            ]);

            if (!empty($campaigns) && is_array($campaigns)) {
                foreach ($campaigns as $campaign) {
                    // check filters
                    if (!Campaign::isFiltersPassed($campaign['filters'], $product_data)) {
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
     * Get product ids to display.
     *
     * @param mixed $product_or_id
     * @return array
     */
    public static function getProductIdsToDisplay($product_or_id = false)
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

            $ids = (array)apply_filters('cuw_product_addon_product_ids_to_display', array_unique($ids), $product, $use_products, $campaign);
            $ids = Cart::filterProducts($ids, self::TYPE);
        }
        return $ids;
    }

    /**
     * Get products display location.
     *
     * @param mixed $product_or_id
     * @return string
     */
    public static function getProductsDisplayLocation($product_or_id = false)
    {
        $product = WC::getProduct($product_or_id);
        if (!empty($product) && $campaign = self::getMatchedCampaign($product)) {
            return Campaign::getDisplayLocation($campaign);
        }
        return '';
    }

    /**
     * Get product display locations.
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        return (array)apply_filters('cuw_product_addons_display_locations', [
            'woocommerce_before_add_to_cart_button' => esc_html__("Before Add to cart Button", 'checkout-upsell-woocommerce'),
            'woocommerce_after_add_to_cart_button' => esc_html__("After Add to cart Button", 'checkout-upsell-woocommerce'),
        ]);
    }
}