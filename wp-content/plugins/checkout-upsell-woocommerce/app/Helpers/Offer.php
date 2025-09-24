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

use CUW\App\Core;
use CUW\App\Models\Offer as OfferModel;
use CUW\App\Models\Stats;

class Offer
{
    /**
     * Get offer product price.
     *
     * @param \WC_Product $product
     * @param array $discount
     * @return float
     */
    public static function getProductPrice($product, $discount = [])
    {
        $product_price = Discount::getProductPrice($product, $discount);
        return (float)apply_filters('cuw_offer_product_price', $product_price, $product, $discount);
    }

    /**
     * Get offer price
     *
     * @param \WC_Product $product
     * @param array $discount
     * @param int|float|null $product_price
     * @return int|float
     */
    public static function getPrice($product, $discount, $product_price = null)
    {
        if ($product_price === null) {
            $product_price = self::getProductPrice($product, $discount);
        }
        $offer_price = Discount::getPrice($product, $discount, $product_price);
        return apply_filters('cuw_offer_price', $offer_price, $product, $discount, $product_price);
    }

    /**
     * Get offer text
     *
     * @param \WC_Product $product
     * @param array $discount
     * @param string $display_in
     * @return string
     */
    public static function getText($product, $discount, $display_in = 'offer')
    {
        $display_in = self::getDisplayPriceTaxBasedOn($display_in);
        $offer_text = Discount::getText($product, $discount, $display_in);
        return apply_filters('cuw_offer_text', $offer_text, $product, $discount, $display_in);
    }

    /**
     * Get offer price html.
     *
     * @param \WC_Product $product
     * @param array $discount
     * @param string $display_in
     * @param int|float|null $product_price
     * @param int|float|null $offer_price
     * @return string
     */
    public static function getPriceHtml($product, $discount, $display_in = 'offer', $product_price = null, $offer_price = null)
    {
        $tax_based_on = self::getDisplayPriceTaxBasedOn($display_in);
        if ($product_price === null && $offer_price === null) {
            $product_price = self::getProductPrice($product, $discount);
            $offer_price = self::getPrice($product, $discount, $product_price);
        }
        $price_html = Discount::getPriceHtml($product, $discount, $tax_based_on, $display_in, $product_price, $offer_price);
        return apply_filters('cuw_offer_price_html', $price_html, $product, $discount, $display_in, $product_price, $offer_price);
    }

    /**
     * Set offer notice to session.
     *
     * @param string $location
     * @param int $offer_id
     * @param string $message
     * @param string $status
     */
    public static function setNotice($location, $offer_id, $message, $status)
    {
        $notices = WC::getSession('cuw_offer_notices', []);
        $notices[$location][$offer_id]['status'] = $status;
        $notices[$location][$offer_id]['message'] = $message;
        WC::setSession('cuw_offer_notices', $notices);
    }

    /**
     * Get offer notices HTML.
     *
     * @param string $location
     * @return string
     */
    public static function getNoticesHtml($location)
    {
        $html = '';
        $notices = WC::getSession('cuw_offer_notices', []);
        if (isset($notices[$location])) {
            foreach ($notices[$location] as $notice) {
                $html .= WC::getNotice($notice['message'], $notice['status']);
            }
        }
        return $html;
    }

    /**
     * Remove offer notices from session.
     */
    public static function removeNotices()
    {
        $notices = WC::getSession('cuw_offer_notices', []);
        if (!empty($notices)) {
            WC::setSession('cuw_offer_notices', null);
        }
    }

    /**
     * Returns tax based on shop or cart.
     *
     * @param string $display_in
     * @return string
     */
    private static function getDisplayPriceTaxBasedOn($display_in)
    {
        return apply_filters('cuw_offer_display_price_tax_based_on', 'cart', $display_in);
    }

    /**
     * Get offers display locations.
     *
     * @param $campaign_type
     * @return array
     */
    public static function getDisplayLocations($campaign_type)
    {
        return (array)apply_filters('cuw_offers_display_locations', [], $campaign_type);
    }

    /**
     * Get mini cart offers display locations.
     *
     * @param $campaign_type
     * @return array
     */
    public static function getDisplayLocationsOnMiniCart($campaign_type)
    {
        return (array)apply_filters('cuw_offers_display_locations_on_mini_cart', [], $campaign_type);
    }

    /**
     * Get maximum offer limit per campaign.
     *
     * @param $campaign_type
     * @return int
     */
    public static function getMaxLimit($campaign_type)
    {
        return (int)apply_filters('cuw_offers_per_campaign', 1, $campaign_type);
    }

    /**
     * Get template html
     *
     * @param int|array $offer_id_or_data
     * @return string
     */
    public static function getTemplateHtml($offer_id_or_data)
    {
        if (is_numeric($offer_id_or_data)) {
            $offer_id = $offer_id_or_data;
            $offer = OfferModel::get($offer_id, ['id', 'product', 'discount', 'data']);
            if ($offer) {
                $offer_data = [
                    'id' => $offer_id,
                    'product' => $offer['product'],
                    'discount' => $offer['discount'],
                    'data' => $offer['data'],
                ];
            }
        } elseif (is_array($offer_id_or_data)) {
            $offer_data = $offer_id_or_data;
            $offer_data['id'] = 0;
            if (isset($offer_data['data']['template'])) {
                $data = Template::getDefaultData($offer_data['data']['template']);
                $offer_data['data'] = array_merge($data, $offer_data['data']);
            } else {
                $campaign_type = isset($offer_data['campaign_type']) ? $offer_data['campaign_type'] : '';
                $offer_data['data'] = Template::getDefaultData('', $campaign_type);
            }
        }
        if (!empty($offer_data)) {
            $processed_data = self::prepareData($offer_data);
            if ($processed_data) {
                return Core::instance()->template($processed_data['template']['name'], ['offer' => $processed_data], false);
            }
        }
        return '';
    }

    /**
     * To get template data (processed offer data)
     *
     * @param array $offer
     * @return array|false
     */
    public static function prepareData($offer)
    {
        if (empty($offer['product']['id']) && empty($offer['data'])) {
            return false;
        }
        if (!$product = WC::getProduct($offer['product']['id'])) {
            return false;
        }
        if (is_object($offer['product']['id'])) {
            $offer['product']['id'] = $product->get_id();
        }

        // to kept original data before modify
        $original_data = $offer;

        // check if the offer product is valid
        $offer['is_rtl'] = WP::isRtl();
        $offer['is_preview'] = empty($offer['id']) || !empty($offer['is_preview']);
        $offer['is_valid'] = WC::isPurchasableProduct($product, $offer['product']['qty']);
        if (!$offer['is_valid'] && !$offer['is_preview']) {
            return false;
        }

        // load product data
        $product_data = Product::getData($product, [
            'discount' => $offer['discount'],
            'to_display' => true,
            'display_in' => 'cart',
            'format_title' => !$offer['is_preview'],
            'format_image' => !$offer['is_preview'],
            'include_variants' => true,
            'filter_purchasable' => !$offer['is_preview'],
            'load_tax' => $offer['load_tax'] ?? false,
        ]);
        $product_data['fixed_qty'] = $offer['product']['qty'];
        $product_data['fixed_image'] = '';
        $offer['product'] = $product_data;

        // remove slashes
        $offer['template'] = wp_unslash($offer['data']);
        if (isset($offer['template']['template'])) {
            $offer['template']['name'] = $offer['template']['template'];
            unset($offer['template']['template']);
        }
        unset($offer['data']);

        // allow to translate offer title, description and CTA text
        $offer['template']['title'] = !empty($offer['template']['title']) ? __($offer['template']['title'], 'checkout-upsell-woocommerce') : '';
        $offer['template']['description'] = !empty($offer['template']['description']) ? nl2br(__($offer['template']['description'], 'checkout-upsell-woocommerce')) : '';
        $offer['template']['cta_text'] = !empty($offer['template']['cta_text']) ? __($offer['template']['cta_text'], 'checkout-upsell-woocommerce') : '';

        $offer['discount']['text'] = self::getText($product, $offer['discount']);
        if (!empty($offer['discount']['text'])) {
            $offer['template']['title'] = str_replace('{discount}', $offer['discount']['text'], $offer['template']['title']);
        }

        if (isset($offer['template']['styles'])) {
            $offer['styles'] = Template::prepareInlineStyles($offer['template']['styles']);
            unset($offer['template']['styles']);
        }

        if (isset($offer['template']['timer'])) {
            $offer['timer'] = $offer['template']['timer'];
            unset($offer['template']['timer']);
        }

        $applied_offers = Cart::getAppliedOffers();
        foreach ($applied_offers as $key => $applied_offer) {
            if ($applied_offer['id'] == $offer['id']) {
                $cart_item = WC::getCartItem($key);
                $offer['cart_item_key'] = $key;
                $offer['product']['qty'] = $cart_item['quantity'] ?? 1;
                if ($offer['product']['is_variable'] && !empty($offer['product']['default_variant'])) {
                    foreach ($offer['product']['variants'] as $variant) {
                        if ($variant['id'] == $cart_item['variation_id'] ?? 0) {
                            $offer['product']['default_variant'] = $variant;
                            break;
                        }
                    }
                }
                break;
            }
        }

        // process fixed offer image
        if (!empty($offer['template']['image_id'])) {
            $offer['product']['image'] = Product::formatImage($product, $offer['template']['image_id']);
            $offer['product']['fixed_image'] = $offer['product']['image'];
            if ($offer['product']['is_variable'] && !empty($offer['product']['default_variant'])) {
                $offer['product']['default_variant']['image'] = $offer['product']['image'];
            }
        }

        $offer['allowed_html'] = Input::getAllowedHtmlTags();
        return apply_filters('cuw_offer_processed_data', $offer, $product, $original_data);
    }

    /**
     * To prepare meta data
     *
     * @param array|int $offer_id_or_data
     * @param int|float $quantity
     * @param int $variation_id
     * @param array $variation_attributes
     * @param string $campaign_type
     * @return array|false
     */
    public static function prepareMetaData($offer_id_or_data, $quantity = 1, $variation_id = 0, $variation_attributes = [], $campaign_type = '')
    {
        $offer = $offer_id_or_data;
        if (is_numeric($offer)) {
            $offer = OfferModel::get($offer, ['id', 'campaign_id', 'product', 'discount']);
        }

        if (empty($campaign_type) && isset($offer['campaign_id'])) {
            $campaign_type = Campaign::getType($offer['campaign_id']);
        }

        if (empty($offer) || !isset($offer['campaign_id']) || !isset($offer['product']) || !isset($offer['discount'])) {
            return false;
        }

        $product_id = intval($offer['product']['id']);
        $product = WC::getProduct($product_id);
        $variation = [];

        if (WC::isVariableProduct($product) && !empty($variation_id) && $variation_product = WC::getProduct($variation_id)) {
            $variation = !empty($variation_attributes) ? $variation_attributes : Product::getVariationAttributes($product, $variation_product);
            $product = $variation_product;
        }

        $product_qty = floatval(!empty($offer['product']['qty']) ? $offer['product']['qty'] : $quantity);
        $product_price = self::getProductPrice($product, $offer['discount']);
        $offer_price = self::getPrice($product, $offer['discount'], $product_price);
        $offer_text = self::getText($product, $offer['discount'], 'cart');
        $meta_data = [
            'id' => $offer['id'],
            'type' => 'offer',
            'price' => $offer_price,
            'product' => [
                'id' => $product_id,
                'qty' => $product_qty,
                'price' => $product_price,
                'variation' => $variation,
                'variation_id' => $variation_id,
            ],
            'discount' => [
                'text' => $offer_text,
                'type' => $offer['discount']['type'],
                'value' => $offer['discount']['value'],
                'price' => $product_price - $offer_price,
            ],
            'campaign_id' => $offer['campaign_id'],
            'campaign_type' => $campaign_type,
        ];
        if (!empty($offer['product']['qty'])) {
            $meta_data['fixed_quantity'] = $offer['product']['qty'];
        }
        return apply_filters('cuw_offer_item_data', $meta_data, $product, $offer);
    }

    /**
     * Check if the schedule is passed
     *
     * @param int|string|null $start_on
     * @param int|string|null $end_on
     * @return bool
     */
    public static function isSchedulePassed($start_on, $end_on)
    {
        if (!empty($start_on) && strtotime(get_date_from_gmt(date('Y-m-d H:i:s', $start_on))) >= current_time('timestamp')) {
            return false;
        }
        if (!empty($end_on) && strtotime(get_date_from_gmt(date('Y-m-d H:i:s', $end_on))) <= current_time('timestamp')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the offer is valid
     *
     * @param array $offer
     * @return bool
     */
    public static function isValid($offer)
    {
        if (!empty($offer['usage_limit']) && $offer['usage_limit'] <= $offer['usage_count']) {
            return false;
        }
        if (!empty($offer['usage_limit_per_user'])) {
            $usage_count = Stats::getOfferUsageCountBasedOnCurrentUser($offer['id']);
            if ($offer['usage_limit_per_user'] <= $usage_count) {
                return false;
            }
        }
        return true;
    }

    /**
     * Choose Offer A or B for A/B Testing
     *
     * @param array $offer_a
     * @param array $offer_b
     * @param array $data
     * @return array
     */
    public static function chooseOfferAorB($offer_a, $offer_b, $data)
    {
        $a_percentage = $b_percentage = 50;
        if (isset($data['a']['percentage']) && isset($data['b']['percentage'])) {
            $a_percentage = (int)$data['a']['percentage'];
            $b_percentage = (int)$data['b']['percentage'];
        }

        $a_count = (int)$offer_a['display_count'];
        $b_count = (int)$offer_b['display_count'];
        $total_count = $a_count + $b_count;

        $a_level = $total_count * ($a_percentage / 100);
        $b_level = $total_count * ($b_percentage / 100);

        return $a_count <= $a_level ? $offer_a : $offer_b;
    }
}