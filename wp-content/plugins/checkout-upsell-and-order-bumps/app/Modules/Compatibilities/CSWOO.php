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

class CSWOO extends Base
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
            if (class_exists('Alg_WC_Currency_Switcher')) {
                if (function_exists('alg_wc_cs_get_currency_exchange_rate') && function_exists('alg_get_current_currency_code')) {
                    $exchange_rate = alg_wc_cs_get_currency_exchange_rate(alg_get_current_currency_code());
                    if ($exchange_rate != 0) {
                        self::$conversion_rate = $exchange_rate;
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
