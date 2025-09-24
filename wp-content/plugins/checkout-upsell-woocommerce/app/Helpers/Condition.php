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

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Modules\Conditions;
use CUW\App\Helpers\Plugin;

class Condition
{
    /**
     * To hold conditions
     *
     * @var array
     */
    public static $conditions;

    /**
     * Get conditions
     *
     * @param string $campaign_type
     * @return array
     */
    public static function get($campaign_type = '')
    {
        if (!isset(self::$conditions)) {
            $conditions = [
                // pre-purchase conditions
                'products' => [
                    'name' => __("Products in the Cart", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Products(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'categories' => [
                    'name' => __("Categories of items in the Cart", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Categories(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'tags' => [
                    'name' => __("Tags of items in the Cart", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'skus' => [
                    'name' => __("Product SKUs in the Cart", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'items_count' => [
                    'name' => __("Number of line items in the Cart", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\ItemsCount(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'quantities_count' => [
                    'name' => __("Cart items quantity", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\QuantitiesCount(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'coupons' => [
                    'name' => __("Applied Coupons in the Cart", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Coupons(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                'subtotal' => [
                    'name' => __("Cart subtotal", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Subtotal(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order', 'upsell_popups', 'cart_addons'],
                ],
                /* 'total' => [
                    'name' => __("Cart total", 'checkout-upsell-woocommerce'),
                    'group' => __("Cart", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Total(),
                    'campaigns' => ['checkout_upsells', 'cart_upsells', 'double_order'],
                ], */

                // post-purchase conditions
                'order_products' => [
                    'name' => __("Products in Order", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Products(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                'order_categories' => [
                    'name' => __("Categories of items in Order", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Categories(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                'order_tags' => [
                    'name' => __("Tags of items in Order", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                'order_skus' => [
                    'name' => __("Product SKUs in Order", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                'order_items_count' => [
                    'name' => __("Number of line items in Order", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\ItemsCount(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                'order_quantities_count' => [
                    'name' => __("Order items quantity", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\QuantitiesCount(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                'order_coupons' => [
                    'name' => __("Applied Coupons in Order", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Coupons(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],
                /* 'order_subtotal' => [
                    'name' => __("Order subtotal", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Subtotal(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells'],
                ], */
                'order_total' => [
                    'name' => __("Order total", 'checkout-upsell-woocommerce'),
                    'group' => __("Order", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Total(),
                    'campaigns' => ['post_purchase', 'noc', 'thankyou_upsells', 'post_purchase_upsells'],
                ],

                // purchase history based conditions
                'first_order' => [
                    'name' => __("First order", 'checkout-upsell-woocommerce'),
                    'group' => __("Purchase History", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'orders_made' => [
                    'name' => __("Number of orders made", 'checkout-upsell-woocommerce'),
                    'group' => __("Purchase History", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'orders_made_with_products' => [
                    'name' => __("Number of orders made with specific products", 'checkout-upsell-woocommerce'),
                    'group' => __("Purchase History", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'total_spent' => [
                    'name' => __("Total spent", 'checkout-upsell-woocommerce'),
                    'group' => __("Purchase History", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],

                // common conditions
                'user_role' => [
                    'name' => __("User role", 'checkout-upsell-woocommerce'),
                    'group' => __("User", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\UserRole(),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'user_logged_in' => [
                    'name' => __("User logged in", 'checkout-upsell-woocommerce'),
                    'group' => __("User", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\UserLoggedIn(),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'users' => [
                    'name' => __("Specific users", 'checkout-upsell-woocommerce'),
                    'group' => __("User", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'time' => [
                    'name' => __("Time", 'checkout-upsell-woocommerce'),
                    'group' => __("Date & Time", 'checkout-upsell-woocommerce'),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
                'days' => [
                    'name' => __("Days", 'checkout-upsell-woocommerce'),
                    'group' => __("Date & Time", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Days(),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ],
            ];

            // to add WPML language condition only when WPML plugin is active
            if (Plugin::isActive('sitepress-multilingual-cms/sitepress.php')) {
                $conditions['wpml_language'] = [
                    'name' => __("Language", 'checkout-upsell-woocommerce'),
                    'group' => __("WPML", 'checkout-upsell-woocommerce'),
                    'handler' => new Conditions\Languages(),
                    'campaigns' => ['checkout_upsells', 'post_purchase', 'cart_upsells', 'double_order', 'noc', 'thankyou_upsells', 'upsell_popups', 'cart_addons', 'post_purchase_upsells'],
                ];
            }

            self::$conditions = (array)apply_filters('cuw_conditions', $conditions);
        }

        if ($campaign_type !== '') {
            $conditions = [];
            foreach (self::$conditions as $key => $condition) {
                if (in_array($campaign_type, $condition['campaigns'])) {
                    unset($condition['campaigns']);
                    $conditions[$key] = $condition;
                }
            }
            return $conditions;
        }
        return self::$conditions;
    }

    /**
     * Check if the given condition is passed or not
     *
     * @param array $condition
     * @param array|object $data
     * @return bool
     */
    public static function check($condition, $data)
    {
        if (!isset(self::$conditions)) {
            self::get();
        }

        if (isset(self::$conditions[$condition['type']]) && isset(self::$conditions[$condition['type']]['handler'])) {
            $is_passed = (bool)self::$conditions[$condition['type']]['handler']->check($condition, $data);
        } else {
            $is_passed = false;
        }
        return (bool)apply_filters('cuw_condition_is_passed', $is_passed, $condition, $data);
    }
}