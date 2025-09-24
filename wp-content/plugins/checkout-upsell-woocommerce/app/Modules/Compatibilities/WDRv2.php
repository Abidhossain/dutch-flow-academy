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

namespace CUW\App\Modules\Compatibilities;

defined('ABSPATH') || exit;

class WDRv2 extends Base
{
    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('advanced_woo_discount_rules_calculate_discount_for_cart_item', function ($calculate_discount, $cart_item) {
            if (!empty($cart_item['cuw_offer'])) {
                if (isset($cart_item['cuw_offer']['discount']['type']) && $cart_item['cuw_offer']['discount']['type'] != 'no_discount') {
                    $calculate_discount = false;
                }
            } elseif (!empty($cart_item['cuw_product'])) {
                if (isset($cart_item['cuw_product']['discount']['type']) && $cart_item['cuw_product']['discount']['type'] != 'no_discount') {
                    $calculate_discount = false;
                }
            }
            return $calculate_discount;
        }, 100, 2);

        add_filter('advanced_woo_discount_rules_include_cart_item_to_count_quantity', function ($take_count, $cart_item) {
            if (!empty($cart_item['cuw_offer'])) {
                $take_count = false;
            }
            return $take_count;
        }, 100, 2);

        add_filter('advanced_woo_discount_rules_process_cart_item_for_cheapest_rule', function ($calculate_discount, $cart_item) {
            if (!empty($cart_item['cuw_offer'])) {
                $calculate_discount = false;
            } elseif (!empty($cart_item['cuw_product'])) {
                $calculate_discount = false;
            }
            return $calculate_discount;
        }, 100, 2);

        add_filter('cuw_raw_product_price', function ($price, $product, $context) {
            return self::getDiscountedPrice($product, $price, $context);
        }, 100, 3);
    }

    /**
     * Get discounted price.
     */
    private static function getDiscountedPrice($product, $default = false, $context = 'shop')
    {
        $discounted_price = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', false, $product, 1, 0, 'discounted_price', true, ($context == 'cart'));
        if ($discounted_price !== false) {
            return $discounted_price;
        }
        return $default;
    }
}