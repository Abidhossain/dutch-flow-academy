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

class Discount
{
    /**
     * Get discounted price.
     *
     * @param \WC_Product $product
     * @param array $discount
     * @param int|float|null $product_price
     * @return int|float
     */
    public static function getPrice($product, $discount, $product_price = null,$qty = 1)
    {
        if (is_null($product_price)) {
            $product_price = self::getProductPrice($product, $discount);
        }

        $price = (float)$product_price;
        if (isset($discount['type']) && isset($discount['value'])) {
            if (!is_numeric($discount['value'])) {
                return $product_price;
            }
            if ($discount['type'] == "percentage") {
                if ($discount['value'] > 100) {
                    $discount['value'] = 100;
                }
                $price = $price - ($price * ($discount['value'] / 100));
            } elseif ($discount['type'] == "fixed_price") {
                $discount['value'] = apply_filters('cuw_convert_price', $discount['value'], 'fixed_price');
					$discount['value'] = $discount['value'] / $qty;
                if ($discount['value'] > $price) {
                    $discount['value'] = $price;
                }
                $price = $price - $discount['value'];
            } elseif ($discount['type'] == "free") {
                $price = 0;
            }
        }
        if ($price < 0) {
            $price = $product_price;
        }
        return $price;
    }

    /**
     * Get product price.
     *
     * @param \WC_Product $product
     * @param array $discount
     * @return int|float
     */
    public static function getProductPrice($product, $discount)
    {
        $from = null;
        if (empty($discount) || !isset($discount['type']) || $discount['type'] != 'no_discount') {
            $from = Config::getSetting('calculate_discount_from');
        }
        return Product::getPrice($product, $from);
    }

    /**
     * Get discount price html.
     *
     * @param \WC_Product $product
     * @param array $discount
     * @param string $tax_based_on
     * @param string $display_in
     * @param int|float|null $regular_price
     * @param int|float|null $price
     * @return string
     */
    public static function getPriceHtml($product, $discount, $tax_based_on = 'shop', $display_in = '', $regular_price = null, $price = null)
    {
        if ($discount['type'] == 'no_discount' || WC::isVariableProduct($product)) {
            $price_html = ($display_in != 'cart') ? $product->get_price_html() : '';
        } else {
            if ($regular_price === null || $price === null) {
                $regular_price = self::getProductPrice($product, $discount);
                $price = self::getPrice($product, $discount, $regular_price);
            }
            $regular_price_to_display = WC::getPriceToDisplay($product, $regular_price, 1, $tax_based_on);
            if ($regular_price > $price) {
                $offer_price_to_display = WC::getPriceToDisplay($product, $price, 1, $tax_based_on);
                $price_html = WC::formatSalePrice($regular_price_to_display, $offer_price_to_display);
            } else {
                $price_html = WC::formatPrice($regular_price_to_display);
            }
            if ($tax_based_on == 'shop') {
                $price_html .= $product->get_price_suffix($price);
            }
        }
        return apply_filters('cuw_discount_price_html', $price_html, $product, $discount, $tax_based_on, $regular_price, $price);
    }

    /**
     * Get offer text
     *
     * @param \WC_Product $product
     * @param array $discount
     * @param string $display_in
     * @return string
     */
    public static function getText($product, $discount, $display_in = 'shop')
    {
        $text = '';
        if (isset($discount['type'])) {
            $discount['value'] = isset($discount['value']) ? $discount['value'] : '';
            if (is_numeric($discount['value'])) {
                $discount['value'] = floatval($discount['value']);
            }
            if ($discount['type'] == "percentage" && is_numeric($discount['value'])) {
                $text = $discount['value'] . '%';
            } elseif ($discount['type'] == "fixed_price" && is_numeric($discount['value'])) {
                $price = apply_filters('cuw_convert_price', $discount['value'], 'fixed_price');
                $price = WC::getPriceToDisplay($product, $price, 1, $display_in);
                $text = html_entity_decode(WC::formatPriceRaw($price, ['trim_zeros' => true]));
            } elseif ($discount['type'] == "free") {
                $text = esc_html__("Free", 'checkout-upsell-and-order-bumps');
            } elseif ($discount['type'] == "no_discount") {
                $text = esc_html__("Product", 'checkout-upsell-and-order-bumps');
            }
            $text = apply_filters('cuw_discount_text', $text, $product, $discount, $display_in);
        }
        return $text;
    }

    /**
     * To get product discount prices.
     *
     * @param array $product_ids
     * @param array $discount
     * @return array
     */
    public static function splitFixedDiscount($product_ids, $discount)
    {
        $product_prices = [];
        $discount_prices = [];
        $last_product_id = 0;
        $fixed_price = $discount['value'];
        $discount_from = Config::getSetting('calculate_discount_from');
        $price_decimals = WC::get('price_decimals', 2);
        foreach ($product_ids as $product_id) {
            $product_data = Product::getData($product_id, ['include_variants' => true]);
            if (!empty($product_data)) {
                $product_prices[$product_id] = ($discount_from == 'regular_price') ? $product_data['regular_price'] : $product_data['price'];
            }
            $last_product_id = $product_id;
        }
        $total_price = array_sum($product_prices);
        foreach ($product_prices as $product_id => $product_price) {
            $discount_prices[$product_id] = round(($product_price / $total_price) * $fixed_price, $price_decimals);
        }
        $discount_price = array_sum($discount_prices);
        $difference_amount = round($discount_price - $fixed_price, $price_decimals);
        if ($difference_amount != 0) {
            $discount_prices[$last_product_id] -= $difference_amount;
        }
        return $discount_prices;
    }
}
