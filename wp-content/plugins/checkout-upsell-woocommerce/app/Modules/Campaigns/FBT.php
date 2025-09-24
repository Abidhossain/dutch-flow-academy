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
use CUW\App\Helpers\Discount;
use CUW\App\Helpers\Plugin;
use CUW\App\Helpers\Product;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Model;

class FBT extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'fbt';

    /**
     * To hold matched campaign data.
     *
     * @var array|false
     */
    private static $campaign = [];

    /**
     * To hold product meta key.
     */
    const PRODUCTS_META_KEY = 'cuw_fbt_product_ids';

    /**
     * To hold products override meta key.
     */
    const PRODUCTS_OVERRIDE_META_KEY = 'cuw_fbt_use_custom_products';

    /**
     * To hold product discounts meta key.
     */
    const PRODUCTS_DISCOUNT_META_KEY = 'cuw_fbt_products_discount';

    /**
     * To hold generated product data meta key.
     */
    const PRODUCTS_FROM_ORDERS_META_KEY = 'cuw_fbt_products_from_orders';

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

            // to load savings section for preview
            add_filter('cuw_fbt_template_savings', [__CLASS__, 'loadSavingsSection'], 10, 4);

            if (self::isEnabled() && Plugin::hasPro()) {
                self::mayLoadDeprecatedTab(); // deprecated since v2.0.0

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
                        } else {
                            add_action('cuw_fbt_shortcode', [__CLASS__, 'showProducts']);
                        }
                    }
                }, 1000);

                add_action('wp_loaded', [__CLASS__, 'addProductsToCart'], 15);
                add_filter('cuw_fbt_template_choose_variants_modal', [__CLASS__, 'loadVariantSelectModal'], 10, 3);
                add_filter('cuw_fbt_template_product_variants', [__CLASS__, 'loadVariantSelect'], 10, 3);
                add_filter('cuw_fbt_template_savings', [__CLASS__, 'loadSavingsSection'], 10, 4);
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
            $display_limit = (int)self::app()->config->getSetting('fbt_products_display_limit');
            $this_product_id = $product->get_id();
            $product_ids = array_diff($product_ids, [$this_product_id]); // remove this product id
            $product_ids = array_merge([$this_product_id], $product_ids);
            $campaign = self::getMatchedCampaign($product);
            $is_bundle = !empty($campaign['data']['bundle']) || !empty($campaign['data']['products']['bundle']);
            $discount = self::getDiscount($product, $campaign);
            $discount_apply_to = isset($discount['apply_to']) ? $discount['apply_to'] : 'no_products';
            foreach ($product_ids as $product_id) {
                $is_main_product = ($product_id == $this_product_id);
                $has_discount = ($discount_apply_to != 'no_products' && ($discount_apply_to != 'only_upsells' || !$is_main_product));
                $product_data = Product::getData($product_id, [
                    'discount' => $has_discount ? $discount : [],
                    'to_display' => true,
                    'display_in' => 'shop',
                    'format_title' => true,
                    'include_variants' => true,
                    'filter_purchasable' => true,
                ]);
                if (!empty($product_data)) {
                    $product_data['classes'] = [];
                    $product_data['is_main'] = $is_main_product;
                    if ($product_data['is_main']) $product_data['classes'][] = 'is_main';
                    if ($product_data['is_variable']) $product_data['classes'][] = 'is_variable';
                    $products[] = $product_data;
                    if (count($products) == (1 + $display_limit)) {
                        break;
                    }
                }
            }

            if (!empty($products) && count($products) > 1) {
                if ($is_bundle && $discount['apply_to'] != 'no_products' && $discount['type'] == 'fixed_price') {
                    $product_ids = array_column($products, 'id');
                    if ($discount_apply_to == 'only_upsells') {
                        $main_product_key = array_search($this_product_id, $product_ids);
                        if (is_numeric($main_product_key)) {
                            unset($product_ids[$main_product_key]);
                        }
                    }
                    $product_prices = Discount::splitFixedDiscount($product_ids, $discount);
                    foreach ($products as $key => $product) {
                        $discount['value'] = isset($product_prices[$product['id']]) ? $product_prices[$product['id']] : 0;
                        $discount['is_bundle'] = true;
                        $has_discount = ($discount_apply_to != 'no_products' && ($discount_apply_to != 'only_upsells' || !$product['is_main']));
                        $products[$key] = Product::getData($product['id'], [
                            'discount' => $has_discount ? $discount : [],
                            'to_display' => true,
                            'display_in' => 'shop',
                            'format_title' => true,
                            'include_variants' => true,
                            'filter_purchasable' => true,
                        ]);
                        $products[$key]['classes'] = $product['classes'];
                        $products[$key]['is_main'] = $product['is_main'];
                    }
                }

                $args = [
                    'products' => $products,
                    'campaign' => $campaign,
                    'is_bundle' => $is_bundle,
                    'main_product_id' => $this_product_id,
                ];

                echo apply_filters('cuw_fbt_template_html', Template::getHtml($campaign, $args), $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
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
            $use_products = '';
            $products_data = Campaign::getProductsData($campaign);
            if (self::isUseCustomProducts($product)) {
                $use_products = 'custom';
            } elseif (!empty($products_data['use'])) {
                $use_products = $products_data['use'];
            }

            if ($use_products == 'custom') {
                $ids = self::getIds($product);
            } elseif ($use_products == 'specific') {
                $ids = !empty($products_data['ids']) ? $products_data['ids'] : [];
            } else {
                $ids = Product::getIds($product, $use_products);
            }

            $ids = (array)apply_filters('cuw_fbt_product_ids_to_display', array_unique($ids), $product, $use_products, $campaign);
            $ids = Cart::filterProducts($ids, self::TYPE);
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
                'type' => 'fbt',
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
     * To load choose variant modal if any one of a chosen product is variable
     *
     * @hooked cuw_fbt_product_variants
     */
    public static function loadVariantSelectModal($html, $products, $data)
    {
        return self::app()->template('fbt/variant-select-modal', compact('products', 'data'), false);
    }

    /**
     * To load product variants select
     *
     * @hooked cuw_fbt_product_variants
     */
    public static function loadVariantSelect($html, $product, $args = [])
    {
        $template_name = self::app()->config->getSetting('variant_select_template');
        return self::app()->template('fbt/' . $template_name, ['product' => $product, 'args' => $args], false);
    }

    /**
     * To load savings section.
     *
     * @hooked cuw_fbt_template_savings
     */
    public static function loadSavingsSection($html, $product, $data, $display)
    {
        return self::app()->template('fbt/savings', compact('product', 'data', 'display'), false);
    }

    /**
     * To add products to the cart.
     */
    public static function addProductsToCart()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (isset($_POST['cuw_add_to_cart']) && $_POST['cuw_add_to_cart'] == self::TYPE) {
            $campaign = $results = [];
            $products = self::app()->input->get('products', [], 'post');
            $campaign_id = self::app()->input->get('campaign_id', '', 'post');
            $main_product_id = self::app()->input->get('main_product_id', '', 'post');
            $displayed_product_ids = explode(',', self::app()->input->get('displayed_product_ids', '', 'post'));
            if (is_numeric($campaign_id) && is_numeric($main_product_id)) {
                $campaign = CampaignModel::get($campaign_id);
                if (!empty($campaign)) {
                    $campaign['data']['discount'] = self::getDiscount($main_product_id, $campaign);
                }
            }

            if (!empty($campaign) && !empty($products) && is_array($products)) {
                $items = [];
                $is_bundle = !empty($campaign['data']['bundle']) || !empty($campaign['data']['products']['bundle']);
                $discount_apply_to = isset($campaign['data']['discount']['apply_to']) ? $campaign['data']['discount']['apply_to'] : 'no_products';
                foreach ($products as $product) {
                    if (!empty($product['id'])) {
                        $items[$product['id']] = [
                            'product_id' => $product['id'],
                            'quantity' => $product['qty'] ?? 1,
                            'variation_id' => $product['variation_id'] ?? 0,
                            'variation_attributes' => isset($product['variation_attributes_json'])
                                ? json_decode(wp_unslash($product['variation_attributes_json']), true)
                                : ($product['variation_attributes'] ?? []),
                        ];
                    }
                }

                if ($is_bundle) {
                    if (isset($items[$main_product_id]) && !empty($displayed_product_ids)) {
                        $main_item = $items[$main_product_id];
                        unset($items[$main_product_id]);
                        $child_items = $items;
                        if ($discount_apply_to == 'only_upsells') {
                            $main_product_key = array_search($main_product_id, $displayed_product_ids);
                            if (is_numeric($main_product_key)) {
                                unset($displayed_product_ids[$main_product_key]);
                            }
                        }
                        $updated_campaign = $campaign;
                        if ($discount_apply_to != 'no_products' && $campaign['data']['discount']['type'] == 'fixed_price') {
                            $product_prices = Discount::splitFixedDiscount($displayed_product_ids, $campaign['data']['discount']);
                            $updated_campaign['data']['discount']['value'] = $discount_apply_to == 'all_products' ? $product_prices[$main_product_id] : 0;
                        }

                        if ($discount_apply_to != 'all_products') {
                            $updated_campaign['data']['discount'] = ['type' => 'no_discount', 'value' => 0];
                        }

                        $main_item_key = Cart::addProduct($updated_campaign, $main_item['product_id'], $main_item['quantity'], $main_item['variation_id'], $main_item['variation_attributes'], ['is_main_item' => true]);
                        if ($main_item_key) {
                            $extra_data = Campaign::getProductsExtraData($campaign);
                            $extra_data = array_merge($extra_data, ['main_item_key' => $main_item_key]);
                            $results[$main_item['variation_id'] > 0 ? $main_item['variation_id'] : $main_item['product_id']] = $main_item['quantity'];
                            foreach ($child_items as $child) {
                                $updated_campaign = $campaign;
                                if ($discount_apply_to == 'no_products') {
                                    $updated_campaign['data']['discount'] = ['type' => 'no_discount', 'value' => 0];
                                } elseif ($updated_campaign['data']['discount']['type'] == 'fixed_price' && !empty($product_prices)) {
                                    $updated_campaign['data']['discount']['value'] = $product_prices[$child['product_id']];
                                }
                                if (Cart::addProduct($updated_campaign, $child['product_id'], $child['quantity'], $child['variation_id'], $child['variation_attributes'], $extra_data)) {
                                    $results[$child['variation_id'] > 0 ? $child['variation_id'] : $child['product_id']] = $child['quantity'];
                                }
                            }
                        }
                    }
                } else {
                    foreach ($items as $item) {
                        $is_main_item = ($item['product_id'] == $main_product_id);
                        $has_discount = ($discount_apply_to != 'no_products' && ($discount_apply_to != 'only_upsells' || !$is_main_item));
                        $updated_campaign = $campaign;
                        if (!$has_discount) {
                            $updated_campaign['data']['discount'] = ['type' => 'no_discount', 'value' => 0];
                        }
                        if (Cart::addProduct($updated_campaign, $item['product_id'], $item['quantity'], $item['variation_id'], $item['variation_attributes'])) {
                            $results[$item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id']] = $item['quantity'];
                        }
                    }
                }
            }

            if (!empty($results) && function_exists('wc_add_to_cart_message')) {
                wc_add_to_cart_message($results);

                do_action('cuw_fbt_products_added_to_cart', $results);

                $url = Campaign::getRedirectURL($campaign);
                if (!empty($url)) {
                    wp_safe_redirect($url);
                    exit;
                }
            }
        }
    }

    /**
     * Get product IDs.
     *
     * @param int|\WC_Product $product_or_id
     * @param string $from
     * @return array
     */
    public static function getIds($product_or_id, $from = 'meta')
    {
        $ids = [];
        $product = WC::getProduct($product_or_id);
        if (!empty($product)) {
            if ($from == 'meta') {
                if (!empty($product->get_parent_id())) {
                    $product = WC::getProduct($product->get_parent_id());
                }
                if (!empty($product)) {
                    $product_ids = $product->get_meta(self::PRODUCTS_META_KEY);
                    if ($product_ids && is_array($product_ids)) {
                        $ids = $product_ids;
                    }
                }
            } elseif ($from == 'orders') {
                $ids = array_keys(self::getProductsFromOrders($product));
            }
        }
        return $ids;
    }

    /**
     * To check if the product is use custom products.
     *
     * @param int|\WC_Product $product_or_id
     * @return bool
     */
    public static function isUseCustomProducts($product_or_id)
    {
        $product = WC::getProduct($product_or_id);
        if (is_object($product)) {
            return !empty($product->get_meta(self::PRODUCTS_OVERRIDE_META_KEY));
        }
        return false;
    }

    /**
     * Get products discount.
     *
     * @param int|\WC_Product $product_or_id
     * @param array $campaign
     * @return array|false
     */
    public static function getDiscount($product_or_id, $campaign = [])
    {
        $product = WC::getProduct($product_or_id);
        if (is_object($product)) {
            $discount = $product->get_meta(self::PRODUCTS_DISCOUNT_META_KEY);
            if ($discount && is_array($discount)) {
                return $discount;
            }
        }
        if (!empty($campaign) && !empty($campaign['data']['discount'])) {
            return $campaign['data']['discount'];
        }
        return [];
    }

    /**
     * Get products from orders.
     *
     * @param \WC_Product $product
     * @return array
     */
    private static function getProductsFromOrders($product)
    {
        $products = [];
        $data = $product->get_meta(self::PRODUCTS_FROM_ORDERS_META_KEY);
        $cache_time = (int)apply_filters('cuw_fbt_product_suggestions_cache_expiration_time_in_seconds', (24 * 60 * 60));
        if (!empty($data) && is_array($data) && isset($data['timestamp']) && $data['timestamp'] > ((current_time('timestamp', true) - $cache_time))) {
            $products = $data['products'];
        } else {
            $product_id = $product->get_id();
            $table_prefix = Model::db()->prefix;
            $order_items_table = $table_prefix . 'woocommerce_order_items';
            $order_item_meta_table = $table_prefix . 'woocommerce_order_itemmeta';
            $results = Model::getResults("
                SELECT r.product_id, COUNT(r.product_id) AS products_count FROM (
                    SELECT oi.order_id, oi.order_item_id, oim.meta_value as product_id 
                    FROM {$order_items_table} as oi LEFT JOIN {$order_item_meta_table} as oim ON oi.order_item_id = oim.order_item_id AND meta_key = '_product_id' 
                    WHERE oi.order_id IN (
                        SELECT DISTINCT order_id FROM {$order_items_table} 
                        WHERE order_item_id IN (SELECT DISTINCT order_item_id FROM {$order_item_meta_table} WHERE meta_key = '_product_id' AND meta_value = %d)) AND oi.order_item_type = 'line_item'
                    ) AS r 
                    WHERE r.product_id != %d GROUP BY r.product_id ORDER BY COUNT(r.product_id) DESC LIMIT 10",
                [$product_id, $product_id]
            );
            if (is_array($results)) {
                foreach ($results as $row) {
                    if (isset($row->product_id) && isset($row->products_count)) {
                        $products[(int)$row->product_id] = (int)$row->products_count;
                    }
                }
                update_post_meta($product_id, self::PRODUCTS_FROM_ORDERS_META_KEY, [
                    'timestamp' => current_time('timestamp', true),
                    'products' => $products,
                ]);
            }
        }
        return $products;
    }

    /**
     * Get product IDs from past orders.
     *
     * @param \WC_Product $product
     * @return array
     */
    public static function getProductIdsFromOrders($product)
    {
        return array_keys(self::getProductsFromOrders($product));
    }

    /**
     * To add tab on product data metabox.
     *
     * @hooked woocommerce_product_data_tabs
     */
    public static function addProductDataTab($tabs)
    {
        global $post;
        if (is_object($post) && $product = WC::getProduct()) {
            if (self::getMatchedCampaign($product)) {
                $tabs['cuw_fbt'] = [
                    'label' => __("Frequently Bought Together", 'checkout-upsell-woocommerce'),
                    'target' => 'cuw_fbt_product_data',
                    'class' => [],
                    'priority' => 45
                ];
            }
        }
        return $tabs;
    }

    /**
     * To show section on product data metabox.
     *
     * @hooked woocommerce_product_data_panels
     */
    public static function showProductDataPanel()
    {
        global $post;
        if (is_object($post) && $product = WC::getProduct()) {
            self::app()->view('Admin/Campaign/FBT', [
                'action' => 'product_edit',
                'post_id' => $post->ID,
                'product_ids' => self::getIds($product),
                'products_from_orders' => self::getProductsFromOrders($product),
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
        // save product ids
        if (!empty($_POST['cuw_fbt_product_ids'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $product_ids = self::app()->input->get('cuw_fbt_product_ids', [], 'post');
            if (is_array($product_ids) && !empty($product_ids)) {
                update_post_meta($post_id, self::PRODUCTS_META_KEY, array_unique($product_ids));
            }
        } else {
            delete_post_meta($post_id, self::PRODUCTS_META_KEY);
        }

        // save products use custom products
        if (!empty($_POST['cuw_fbt_products_override'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            update_post_meta($post_id, self::PRODUCTS_OVERRIDE_META_KEY, true);
        } else {
            delete_post_meta($post_id, self::PRODUCTS_OVERRIDE_META_KEY);
        }

        // save product discount
        if (!empty($_POST['cuw_fbt_discount_override'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $discount = self::app()->input->get('cuw_fbt_products_discount', [], 'post');
            if (is_array($discount) && !empty($discount)) {
                update_post_meta($post_id, self::PRODUCTS_DISCOUNT_META_KEY, $discount);
            }
        } else {
            delete_post_meta($post_id, self::PRODUCTS_DISCOUNT_META_KEY);
        }
    }

    /**
     * To show campaign customization.
     *
     * @hooked cuw_before_campaign_contents
     */
    public static function campaignEditView($campaign_type, $campaign)
    {
        if ($campaign_type == 'fbt') {
            self::app()->view('Admin/Campaign/FBT', [
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
        return (array)apply_filters('cuw_fbt_products_display_locations', [
            'woocommerce_before_single_product' => esc_html__("Top of the Product page", 'checkout-upsell-woocommerce'),
            'woocommerce_after_single_product' => esc_html__("Bottom of the Product Page", 'checkout-upsell-woocommerce'),
            'woocommerce_after_single_product_summary' => esc_html__("After Product summary", 'checkout-upsell-woocommerce'),
            'woocommerce_after_single_product_summary:1' => esc_html__("Before Product tabs", 'checkout-upsell-woocommerce'),
            'shortcode' => esc_html__("Use a shortcode", 'checkout-upsell-woocommerce') . ' [cuw_fbt]',
        ]);
    }

    /**
     * May load deprecated (old) tab.
     */
    public static function mayLoadDeprecatedTab()
    {
        $postmeta_table = Model::db()->prefix . 'postmeta';
        $results = Model::getScalar("SELECT COUNT(meta_id) FROM $postmeta_table WHERE meta_key = %s OR meta_key = %s",
            [self::PRODUCTS_OVERRIDE_META_KEY, self::PRODUCTS_DISCOUNT_META_KEY]
        );
        if (empty($results) && !apply_filters('cuw_force_show_fbt_deprecated_tab', false)) {
            return;
        }

        add_filter('woocommerce_product_data_tabs', function ($tabs) {
            global $post;
            if (is_object($post) && $product = WC::getProduct()) {
                if (self::getMatchedCampaign($product)) {
                    $tabs['cuw_fbt'] = [
                        'label' => __("Frequently Bought Together", 'checkout-upsell-woocommerce'),
                        'target' => 'cuw_fbt_product_data',
                        'class' => [],
                        'priority' => 45
                    ];
                }
            }
            return $tabs;
        });

        add_action('woocommerce_product_data_panels', function () {
            global $post;
            if (is_object($post) && $product = WC::getProduct()) {
                self::app()->view('Admin/Campaign/FBT', [
                    'action' => 'deprecated_product_edit',
                    'post_id' => $post->ID,
                    'product_ids' => self::getIds($product),
                    'products_override' => self::isUseCustomProducts($product),
                    'products_discount' => self::getDiscount($product),
                    'products_from_orders' => self::getProductsFromOrders($product),
                    'matched_campaign' => self::getMatchedCampaign($product),
                ]);
            }
        });
    }
}