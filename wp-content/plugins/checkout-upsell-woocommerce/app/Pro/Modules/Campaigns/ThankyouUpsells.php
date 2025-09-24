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
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\Order;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Helpers\Product;

class ThankyouUpsells extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'thankyou_upsells';

    /**
     * To hold campaigns
     *
     * @var array
     */
    private static $campaigns;

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
            add_filter('cuw_products_template_savings', [__CLASS__, 'loadSavingsSection'], 10, 4);
        } else {
            if (self::isEnabled()) {
                // to show products
                foreach (self::getDisplayLocations() as $location => $name) {
                    if ($location != 'shortcode') {
                        $location = explode(":", $location);
                        add_action($location[0], [__CLASS__, 'showProducts'], (isset($location[1]) ? (int)$location[1] : 10));
                    }
                }
                // to add products to cart
                add_action('wp_loaded', [__CLASS__, 'addProductsToCart'], 15);
                add_filter('cuw_recheck_cart_conditions', function ($recheck, $campaign) {
                    if (isset($campaign['type']) && $campaign['type'] === self::TYPE) {
                        $recheck = false;
                    }
                    return $recheck;
                }, 10, 2);
                add_filter('cuw_products_template_savings', [__CLASS__, 'loadSavingsSection'], 10, 4);
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
        if ($campaign_type == self::TYPE) {
            self::app()->view('Pro/Admin/Campaign/ThankyouUpsells', [
                'action' => 'campaign_edit',
                'campaign' => $campaign,
            ]);
        }
    }

    /**
     * To load savings section.
     *
     * @hooked cuw_products_template_savings
     */
    public static function loadSavingsSection($html, $product, $data, $display)
    {
        return self::app()->template('products/savings', compact('product', 'data', 'display'), false);
    }

    /**
     * To get order object.
     *
     * @param \WC_Order|int $order_or_id
     * @return \WC_Order|false
     */
    public static function getOrder($order_or_id)
    {
        if (is_object($order_or_id) || is_numeric($order_or_id)) {
            $order = WC::getOrder($order_or_id);
        } else {
            $order = WC::getOrder(self::app()->input->get('key', '', 'query'));
        }
        return $order;
    }

    /**
     * To show products.
     */
    public static function showProducts($order)
    {
        self::getProductsHtml(current_action(), self::getOrder($order));
    }

    /**
     * Get products html.
     *
     * @param string $location
     * @param \WC_Order $order
     */
    public static function getProductsHtml($location, $order)
    {
        if (empty($order)) {
            return;
        }

        $campaign = self::getCampaignToDisplay($location, $order);
        if (!empty($campaign)) {
            $ids = [];
            $products = [];
            $order_data = Order::getData($order, true, true);
            $products_data = Campaign::getProductsData($campaign);
            $use_products = !empty($products_data['use']) ? $products_data['use'] : '';
            if ($use_products == 'specific') {
                $ids = !empty($products_data['ids']) ? $products_data['ids'] : [];
            } elseif (!empty($order_data)) {
                foreach ($order_data['products'] as $product) {
                    if (!empty($product['object'])) {
                        $ids = array_merge($ids, Product::getIds($product['object'], $use_products));
                    }
                }
            }

            $ids = (array)apply_filters('cuw_thankyou_upsell_product_ids_to_display', array_unique($ids), $order, $use_products, $campaign);
            if (empty($ids)) {
                return;
            }

            $order_products_ids = !empty($order_data['products']) ? array_column($order_data['products'], 'id') : [];
            $product_ids = array_diff($ids, $order_products_ids); // remove order item product ids

            $discount = isset($campaign['data']['discount']) ? $campaign['data']['discount'] : [];
            $display_limit = (int)self::app()->config->getSetting('thankyou_upsell_products_display_limit');
            foreach ($product_ids as $product_id) {
                if (count($products) >= $display_limit) {
                    break;
                }
                $product_data = Product::getData($product_id, [
                    'discount' => $discount,
                    'to_display' => true,
                    'display_in' => 'cart',
                    'include_variants' => true,
                    'filter_purchasable' => true,
                ]);
                if (!empty($product_data)) {
                    $product_data['classes'] = [];
                    if ($product_data['is_variable']) $product_data['classes'][] = 'is_variable';
                    $products[] = $product_data;
                }
            }

            if (!empty($products)) {
                $args = [
                    'products' => $products,
                    'campaign' => $campaign,
                ];

                echo apply_filters('cuw_thankyou_upsells_template_html', Template::getHtml($campaign, $args), $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
    }

    /**
     * Get product ids to display.
     *
     * @param string $location
     * @param \WC_Order $order
     * @return array
     */
    public static function getCampaignToDisplay($location, $order)
    {
        if (!isset(self::$campaigns)) {
            self::$campaigns = [];
            $available_locations = self::getDisplayLocations();
            $order_data = Order::getData($order);

            $default_location = self::app()->config->get('thankyou_upsells_display_location', 'woocommerce_before_thankyou');

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
                    $display_location = isset($campaign['data']['display_location']) ? $campaign['data']['display_location'] : 'use_global_setting';
                    if ($display_location == 'use_global_setting') {
                        $display_location = $default_location;
                    }

                    // skip campaign if the location is already loaded
                    if (isset(self::$campaigns[$display_location])) {
                        continue;
                    }

                    // check conditions
                    if (!Campaign::isConditionsPassed($campaign['conditions'], $order_data)) {
                        continue;
                    }

                    self::$campaigns[$display_location] = $campaign;

                    if (isset($available_locations[$display_location])) {
                        unset($available_locations[$display_location]);
                        if (empty($available_locations)) {
                            break;
                        }
                    }
                }
            }
        }
        return isset(self::$campaigns[$location]) ? self::$campaigns[$location] : [];
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
            if (is_numeric($campaign_id)) {
                $campaign = CampaignModel::get($campaign_id);
            }

            if (!empty($campaign) && !empty($products) && is_array($products)) {
                $items = [];
                foreach ($products as $product) {
                    if (!empty($product['id'])) {
                        $items[$product['id']] = [
                            'product_id' => $product['id'],
                            'quantity' => isset($product['qty']) ? $product['qty'] : 1,
                            'variation_id' => isset($product['variation_id']) ? $product['variation_id'] : 0,
                            'variation_attributes' => $product['variation_attributes'] ?? [],
                        ];
                    }
                }
                foreach ($items as $item) {
                    $updated_campaign = $campaign;
                    if (Cart::addProduct($updated_campaign, $item['product_id'], $item['quantity'], $item['variation_id'], $item['variation_attributes'])) {
                        $results[$item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id']] = $item['quantity'];
                    }
                }
            }
        }

        if (!empty($results) && !empty($campaign)) {
            if (function_exists('wc_add_to_cart_message') && apply_filters('cuw_thankyou_upsells_show_products_added_to_cart_notice', true)) {
                wc_add_to_cart_message($results, true);
            }

            do_action('cuw_thankyou_upsell_products_added_to_cart', $results);

            $redirect_url = Campaign::getRedirectURL($campaign);
            if (empty($redirect_url) && function_exists('wc_get_checkout_url')) {
                $redirect_url = wc_get_checkout_url();
            }
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Get product display locations.
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        return (array)apply_filters('cuw_thankyou_upsell_products_display_locations', [
            'woocommerce_before_thankyou' => esc_html__("Top of the Thankyou page", 'checkout-upsell-woocommerce'),
            'woocommerce_thankyou' => esc_html__("Bottom of the Thankyou page", 'checkout-upsell-woocommerce'),
            'woocommerce_order_details_before_order_table' => esc_html__("Before the Order items summary", 'checkout-upsell-woocommerce'),
            'woocommerce_order_details_after_order_table' => esc_html__("After the Order items summary", 'checkout-upsell-woocommerce'),
        ]);
    }
}