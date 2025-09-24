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

use CUW\App\Helpers\Action;
use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Campaign as CampaignModel;

defined('ABSPATH') || exit;

class DoubleOrder extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'double_order';

    /**
     * To hold processed offers
     *
     * @var array
     */
    private static $actions;

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            // on campaign page
            add_action('cuw_campaign_contents', [__CLASS__, 'loadCampaignView'], 10, 2);
        } else {
            if (self::isEnabled()) {
                // to show actions
                foreach (self::getDisplayLocations() as $location => $name) {
                    if ($location != 'shortcode') {
                        $location = explode(":", $location);
                        add_action($location[0], [__CLASS__, 'showActions'], (isset($location[1]) ? (int)$location[1] : 10));
                    }
                }

                // to run actions
                add_action('wp_loaded', [__CLASS__, 'runActions'], 100);

                // to add dynamic coupon data
                add_filter('woocommerce_get_shop_coupon_data', [__CLASS__, 'addDynamicCouponData'], 10, 2);

                // to apply dynamic coupon
                add_action('woocommerce_after_calculate_totals', [__CLASS__, 'applyDynamicCoupon']);
            }
        }

        if (self::isEnabled()) {
            // to handle actions
            add_filter('cuw_perform_action', function ($response, $campaign, $params) {
                if (isset($campaign['type']) && $campaign['type'] == self::TYPE) {
                    return self::performAction($campaign, $params);
                }
                return $response;
            }, 100, 3);

            // to avoid recheck conditions.
            add_filter('cuw_recheck_cart_conditions', [__CLASS__, 'ignoreRecheckCartConditions'], 100, 2);
        }
    }

    /**
     * To show actions.
     */
    public static function showActions()
    {
        echo self::getActionsHtml(current_action()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * To perform action.
     *
     * @param array $campaign
     * @param array $args
     * @return array
     */
    public static function performAction($campaign, $args = [])
    {
        $checked = false;
        $conditions_passed = false;
        if (isset($campaign['id'])) {
            $cart_data = Cart::getData([
                'include_applied_offers' => false,
                'include_added_products' => false,
            ], false);
            $conditions_passed = Campaign::isConditionsPassed($campaign['conditions'], $cart_data);
            if (!empty($args['checked']) && $conditions_passed && !Action::isActive($campaign['id']) && isset($campaign['data']['discount'])) {
                Action::set(self::TYPE, $campaign['id'], $campaign['data']['discount']);
                $checked = true;
            } else {
                Action::remove(self::TYPE, $campaign['id']);
                WC::removeCartCoupon($campaign['data']['discount']['label']);
            }
            self::runActions();
        }
        return [
            'status' => 'success',
            'checked' => $checked,
            'remove' => !$conditions_passed,
            'trigger' => 'update_checkout',
        ];
    }

    /**
     * To run actions.
     */
    public static function runActions()
    {
        $active_campaign_id = 0;
        $actions = Action::get(self::TYPE);
        if (!empty($actions)) {
            $active_campaign = [];
            $campaigns = CampaignModel::all([
                'status' => 'active',
                'type' => self::TYPE,
                'columns' => ['id', 'type', 'conditions', 'data'],
                'order_by' => 'priority',
                'sort' => 'asc',
            ]);

            $cart_data = Cart::getData([
                'include_applied_offers' => false,
                'include_added_products' => false,
            ], false);

            // to get active campaign
            if (!empty($cart_data['products']) && !empty($campaigns)) {
                $active_action_campaign_ids = array_keys($actions);
                foreach ($campaigns as $campaign) {
                    if (Campaign::isConditionsPassed($campaign['conditions'], $cart_data)) {
                        $active_campaign = in_array($campaign['id'], $active_action_campaign_ids) ? $campaign : [];
                        break;
                    }
                }
            }

            // to add active campaign products
            if (!empty($active_campaign)) {
                $active_campaign_id = $active_campaign['id'];
                $active_campaign['data']['discount'] = ['type' => 'no_discount', 'value' => 0]; // to avoid apply discount to products
                $already_doubled_products = array_filter($cart_data['added_products'], function ($added_product) {
                    return !empty($added_product['campaign_type']) && $added_product['campaign_type'] == self::TYPE;
                });
                foreach ($cart_data['products'] as $key => $product) {
                    if (!in_array($key, array_column($already_doubled_products, 'main_item_key'))) {
                        Cart::addProduct($active_campaign, $product['id'], $product['quantity'], $product['variation_id'], $product['variation'], [
                            'main_item_key' => $key,
                            'sync_quantity' => true,
                        ]);
                    }
                }
            }

            // to remove other active actions
            foreach ($actions as $campaign_id => $discount) {
                if ($campaign_id != $active_campaign_id) {
                    Action::remove(self::TYPE, $campaign_id);
                }
            }
        }

        // to remove other double order campaign products
        $added_products = Cart::getAddedProducts(true);
        foreach ($added_products as $key => $data) {
            if ($data['campaign_type'] == self::TYPE && $data['campaign_id'] != $active_campaign_id) {
                WC::removeCartItem($key);
            }
        }
    }

    /**
     * Add dynamic coupon data.
     */
    public static function addDynamicCouponData($response, $coupon_code)
    {
        if ($coupon_code !== false && $coupon_code !== 0) {
            $actions = Action::get(self::TYPE);
            if (!empty($actions) && function_exists('wc_format_coupon_code')) {
                foreach ($actions as $campaign_id => $discount) {
                    $dynamic_coupon_code = wc_format_coupon_code(strtolower($discount['label']));
                    if ($coupon_code == $dynamic_coupon_code) {
                        $discount_value = $discount['value'];
                        if ($discount['type'] == 'percentage') {
                            $discount_type = 'percent';
                        } elseif ($discount['type'] == 'fixed_price') {
                            $discount_type = 'fixed_cart';
                            $discount_value = apply_filters('cuw_convert_price', $discount_value, 'fixed_cart');
                        } else {
                            continue;
                        }
                        return [
                            'id' => time() . rand(0, 9),
                            'amount' => $discount_value,
                            'discount_type' => $discount_type,
                            'individual_use' => false,
                            'product_ids' => [],
                            'exclude_product_ids' => [],
                            'usage_limit' => '',
                            'usage_limit_per_user' => '',
                            'limit_usage_to_x_items' => '',
                            'usage_count' => '',
                            'date_created' => date('Y-m-d'),
                            'expiry_date' => '',
                            'apply_before_tax' => 'yes',
                            'free_shipping' => false,
                            'product_categories' => [],
                            'exclude_product_categories' => [],
                            'exclude_sale_items' => false,
                            'minimum_amount' => '',
                            'maximum_amount' => '',
                            'customer_email' => '',
                        ];
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Apply dynamic coupon to cart.
     */
    public static function applyDynamicCoupon()
    {
        foreach (Action::get(self::TYPE) as $campaign_id => $discount) {
            WC::applyCartCoupon($discount['label']);
        }
    }

    /**
     * Get actions html.
     *
     * @param string $location
     * @return string
     */
    public static function getActionsHtml($location)
    {
        $html = '';
        if ($actions = self::getActionsToDisplay($location)) {
            $html .= '<div class="cuw-actions">';
            foreach ($actions as $campaign) {
                $html .= Template::getHtml($campaign);
            }
            $html .= '</div>';
        }
        return apply_filters('cuw_double_order_template_html', $html);
    }

    /**
     * Get actions data to display
     *
     * @param string $location
     * @return array
     */
    public static function getActionsToDisplay($location)
    {
        if (!isset(self::$actions)) {
            self::$actions = [];
            $cart = Cart::getData([
                'include_applied_offers' => false,
                'include_added_products' => false,
            ], false);

            if (empty($cart['products'])) {
                return self::$actions;
            }
            if (WP::isAjax() && $offers = WC::getSession('cuw_double_order_actions')) {
                self::$actions = $offers;
            } else {
                self::$actions = [];
                $default_location = self::app()->config->get('double_order_display_location', 'woocommerce_review_order_before_payment');

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

                        // check conditions
                        if (!Campaign::isConditionsPassed($campaign['conditions'], $cart)) {
                            continue;
                        }

                        // set campaign data and exit
                        self::$actions[$display_location][$campaign['id']] = $campaign;
                        break;
                    }
                }

                WC::setSession('cuw_double_order_actions', self::$actions);
            }
        }
        return isset(self::$actions[$location]) ? self::$actions[$location] : [];
    }

    /**
     * To avoid recheck cart conditions.
     */
    public static function ignoreRecheckCartConditions($status, $campaign)
    {
        if (!empty($campaign['type']) && $campaign['type'] === self::TYPE) {
            $status = false;
        }
        return $status;
    }

    /**
     * To load campaign contents.
     */
    public static function loadCampaignView($campaign_type, $campaign)
    {
        if ($campaign_type == self::TYPE) {
            self::app()->view('Pro/Admin/Campaign/DoubleOrder', ['action' => current_action(), 'campaign' => $campaign]);
        }
    }

    /**
     * Get action display locations.
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        return (array)apply_filters('cuw_double_order_action_display_locations', [
            'woocommerce_review_order_before_payment' => esc_html__("Before Payment Gateways", 'checkout-upsell-woocommerce'),
            'woocommerce_review_order_before_submit' => esc_html__("Before Place Order Button", 'checkout-upsell-woocommerce'),
            'woocommerce_review_order_after_submit' => esc_html__("After Place Order Button", 'checkout-upsell-woocommerce'),
            'woocommerce_review_order_after_payment' => esc_html__("Bottom of the Checkout Page", 'checkout-upsell-woocommerce'),
        ]);
    }
}