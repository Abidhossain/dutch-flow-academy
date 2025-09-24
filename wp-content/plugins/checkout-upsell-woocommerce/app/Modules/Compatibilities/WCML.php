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

use CUW\App\Helpers\Discount;

defined('ABSPATH') || exit;

class WCML extends Base
{
    /**
     * To store a conversion rate.
     *
     * @var float
     */
    private static $conversion_rate;

    /**
     * To store cart item prices.
     *
     * @var array
     */
    private static $cart_item_prices = [];

    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('cuw_cart_item_offer_price', [__CLASS__, 'getCartItemDiscountPrice'], 100, 3);
        add_filter('cuw_cart_item_discount_price', [__CLASS__, 'getCartItemDiscountPrice'], 100, 3);
        add_filter('cuw_cart_item_offer_price_html', [__CLASS__, 'getCartItemPriceHtml'], 100, 3);
        add_filter('cuw_cart_item_discount_price_html', [__CLASS__, 'getCartItemPriceHtml'], 100, 3);
        add_filter('cuw_convert_price', [__CLASS__, 'getConvertedPrice']);

        add_filter('wcml_multi_currency_ajax_actions', function ($ajax_actions) {
            $ajax_actions[] = 'cuw_ajax';
            return $ajax_actions;
        });
    }

    /**
     * To get conversion rate.
     *
     * @return float
     */
    public static function getConversionRate()
    {
        if (!isset(self::$conversion_rate)) {
            self::$conversion_rate = 1;
            $current_currency = apply_filters('wcml_client_currency', null);
            $options = get_option('_wcml_settings');
            if (!empty($options) && !empty($current_currency)) {
                if (isset($wcml_options['currency_options'][$current_currency]['rate']) && $wcml_options['currency_options'][$current_currency]['rate'] != 0) {
                    self::$conversion_rate = $wcml_options['currency_options'][$current_currency]['rate'];
                }
            }
        }
        return (float)self::$conversion_rate;
    }

    /**
     * To get cart item discount price html.
     *
     * @return string
     */
    public static function getCartItemPriceHtml($price_html, $cart_item, $offer)
    {
        return WC()->cart->get_product_price($cart_item['data']);
    }

    /**
     * To get cart item discount price.
     *
     * @return float|int
     */
    public static function getCartItemDiscountPrice($price, $cart_item, $offer)
    {
        if (did_action('woocommerce_before_calculate_totals') == 1 && isset($cart_item['key'])) {
            self::$cart_item_prices[$cart_item['key']] = Discount::getPrice($cart_item['data'], $offer['discount']);
        }
        return isset($cart_item['key']) && isset(self::$cart_item_prices[$cart_item['key']]) ? self::$cart_item_prices[$cart_item['key']] : $price;
    }

    /**
     * To convert price.
     *
     * @param int|float $price
     * @return int|float
     */
    public static function getConvertedPrice($price)
    {
        return (float)apply_filters('wcml_raw_price_amount', $price);
    }
}