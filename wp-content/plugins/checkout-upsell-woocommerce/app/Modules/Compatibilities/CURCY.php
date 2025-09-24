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

class CURCY extends Base
{
    /**
     * To store a conversion rate.
     *
     * @var float
     */
    private static $conversion_rate;

    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('cuw_cart_item_offer_price', [__CLASS__, 'getCartItemDiscountPrice'], 100, 3);
        add_filter('cuw_cart_item_discount_price', [__CLASS__, 'getCartItemDiscountPrice'], 100, 3);
        add_filter('cuw_cart_item_offer_price_html', [__CLASS__, 'getCartItemPriceHtml'], 100, 3);
        add_filter('cuw_cart_item_discount_price_html', [__CLASS__, 'getCartItemPriceHtml'], 100, 3);
        add_filter('cuw_convert_price', [__CLASS__, 'getConvertedPrice'], 100, 2);
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
            $convert_to_current_currency = false;
            $setting = null;

            if (class_exists('\WOOMULTI_CURRENCY_F_Data')) {
                $setting = new \WOOMULTI_CURRENCY_F_Data();
                $convert_to_current_currency = true;
            } elseif (class_exists('\WOOMULTI_CURRENCY_Data')) {
                $setting = new \WOOMULTI_CURRENCY_Data();
                $convert_to_current_currency = true;
            }
            if ($convert_to_current_currency && is_object($setting)) {
                if (method_exists($setting, 'get_list_currencies') && method_exists($setting, 'get_current_currency')) {
                    $selected_currencies = $setting->get_list_currencies();
                    $current_currency = $setting->get_current_currency();
                    if (isset($selected_currencies[$current_currency]) && $selected_currencies[$current_currency]['rate'] != 0) {
                        self::$conversion_rate = $selected_currencies[$current_currency]['rate'];
                    }
                }
            }
        }
        return (float)self::$conversion_rate;
    }

    /**
     * To get cart item discount price.
     *
     * @return float|int
     */
    public static function getCartItemDiscountPrice($price, $cart_item, $offer)
    {
        return Discount::getPrice($cart_item['data'], $offer['discount']) / self::getConversionRate();
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
     * To convert price.
     *
     * @param int|float $price
     * @return int|float
     */
    public static function getConvertedPrice($price, $type)
    {
        if ($type != 'fixed_cart') {
            return is_numeric($price) ? $price * self::getConversionRate() : $price;
        }
        return $price;
    }
}