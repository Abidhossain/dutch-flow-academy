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

use CUW\App\Models\Offer as OfferModel;
use CUW\App\Models\Stats;

class Order
{
    /**
     * Get order data
     *
     * @param \WC_Order|int $order_or_id
     * @param bool $include_offers
     * @param bool $include_products
     * @return array
     */
    public static function getData($order_or_id, $include_offers = false, $include_products = true)
    {
        $order = WC::getOrder($order_or_id);

        $data = [];
        $data['id'] = $order->get_id();
        $data['type'] = 'order';
        $data['status'] = $order->get_status();
        $data['products'] = [];
        $data['added_products'] = [];
        $data['applied_offers'] = [];
        foreach (WC::getOrderItems($order) as $item_id => $order_item) {
            if ($meta = self::getOrderItemOfferData($order_item)) {
                $data['applied_offers'][$item_id] = $meta;
                if (!apply_filters('cuw_get_order_data_including_applied_offers', $include_offers, $order_item)) {
                    continue;
                }
            } elseif ($meta = self::getOrderItemProductData($order_item)) {
                $data['added_products'][$item_id] = $meta;
                if (!apply_filters('cuw_get_order_data_including_added_products', $include_products, $order_item)) {
                    continue;
                }
            }

            $item_data = $order_item->get_data();
            $data['products'][$item_id]['id'] = $item_data['product_id'];
            $data['products'][$item_id]['variation_id'] = $item_data['variation_id'];
            $data['products'][$item_id]['variation'] = $item_data['variation'] ?? [];
            $data['products'][$item_id]['quantity'] = $item_data['quantity'];

            $data['products'][$item_id]['subtotal'] = (float)$item_data['subtotal'];
            $data['products'][$item_id]['subtotal_tax'] = (float)$item_data['subtotal_tax'];

            if ($include_products && method_exists($order_item, 'get_product')) {
                $data['products'][$item_id]['object'] = $order_item->get_product();
            }
        }
        $data['subtotal'] = round(array_sum(array_column($data['products'], 'subtotal')), 4);
        $data['subtotal_tax'] = round(array_sum(array_column($data['products'], 'subtotal_tax')), 4);
        $data['total'] = round((float)$order->get_total('edit'), 4);
        return apply_filters('cuw_get_order_data', $data, $order);
    }

    /**
     * Add offer to order
     *
     * @param \Wc_Order $order
     * @param \WC_Product $product
     * @param array $offer_data
     * @return int|false order_item_id
     */
    public static function addOffer($order, $product, $offer_data)
    {
        $sign_up_fee = in_array($product->get_type(), ['subscription', 'variable-subscription', 'subscription_variation'])
            ? Product::getPrice($product, 'sign_up_fee') : 0;
        $item_id = WC::addToOrder($order, $product, [
            'price' => $offer_data['price'],
            'quantity' => $offer_data['product']['qty'],
            'variation' => $offer_data['product']['variation'],
            'extra_price' => [
                'sign_up_fee' => $sign_up_fee,
            ],
        ]);
        if ($item_id && $order_items = WC::getOrderItems($order)) {
            if (isset($order_items[$item_id]) && $item = $order_items[$item_id]) {
                Order::saveDataToItemMeta($item, $offer_data);
            }
        }
        $order->calculate_totals();
        return $item_id;
    }

    /**
     * Add or update order meta
     *
     * @param \WC_Order $order
     * @param array $data
     * @return bool
     */
    public static function saveMeta($order, $data)
    {
        if (empty($data) && !is_array($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $order->update_meta_data($key, $value);
        }

        if (WC::customOrdersTableIsEnabled()) {
            $order->save();
        } else {
            $order->save_meta_data();
        }
        return true;
    }

    /**
     * Add or update order item meta
     *
     * @param \WC_Order_Item $item
     * @param array $data
     * @return bool
     */
    public static function saveItemMeta($item, $data)
    {
        if (empty($data) && !is_array($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $item->update_meta_data($key, $value);
        }
        $item->save_meta_data();
        return true;
    }

    /**
     * Save data to item meta
     *
     * @param \WC_Order_Item $item
     * @param array $meta
     * @return void
     */
    public static function saveDataToItemMeta($item, $meta)
    {
        if ($meta['type'] == 'offer') {
            $data = ['_cuw_offer' => $meta];
            if (apply_filters('cuw_show_upsell_item_text', true, 'offer', 'order_item')) {
                $data['cuw_offer_text'] = $meta['discount']['text'];
            }
            self::saveItemMeta($item, $data);
        } elseif ($meta['type'] == 'product') {
            $data = ['_cuw_product' => $meta];
            if (isset($meta['discount']['type']) && $meta['discount']['type'] != 'no_discount') {
                if (apply_filters('cuw_show_upsell_item_text', true, 'product', 'order_item')) {
                    $data['cuw_discount_text'] = $meta['discount']['text'];
                }
            }
            self::saveItemMeta($item, $data);
        }
    }

    /**
     * Get order item offer data.
     *
     * @param \WC_Order_Item $order_item
     * @return array|false
     */
    public static function getOrderItemOfferData($order_item)
    {
        return $order_item->get_meta('_cuw_offer', true);
    }

    /**
     * Get order item added product data.
     *
     * @param \WC_Order_Item $order_item
     * @return array|false
     */
    public static function getOrderItemProductData($order_item)
    {
        return $order_item->get_meta('_cuw_product', true);
    }

    /**
     * Save stats
     *
     * @param \WC_Order $order
     * @param bool $update
     * @return void
     */
    public static function saveStats($order, $update = false)
    {
        $has_offers = false;
        $has_products = false;
        $has_coupons = false;
        $saved_item_ids = [];
        if ($update && is_object($order)) {
            $saved_item_ids = Stats::getSavedItemIds($order->get_id());
        }
        foreach (WC::getOrderItems($order) as $item_id => $item) {
            if (!in_array($item_id, $saved_item_ids)) {
                if ($offer = self::getOrderItemOfferData($item)) {
                    Stats::save($order, $item, $offer); // save item data to stats table
                    OfferModel::increaseCount($offer['id'], 'usage_count'); // update offer usage count
                    $has_offers = true;
                } elseif ($data = self::getOrderItemProductData($item)) {
                    Stats::save($order, $item, $data); // save item data to stats table
                    $has_products = true;
                }
            }
        }
        if (is_object($order) && method_exists($order, 'get_coupons')) {
            foreach ($order->get_coupons() as $item) {
                $coupon_data = $item->get_meta('coupon_data');
                if (empty($coupon_data) && !empty($item->get_meta('coupon_info'))) {
                    $coupon_info = json_decode($item->get_meta('coupon_info'), true);
                    if (!empty($coupon_info) && !empty($coupon_info[0]) && !empty($coupon_info[1])) {
                        $coupon_data = [
                            'id' => $coupon_info[0],
                            'code' => $coupon_info[1],
                        ];
                    }
                }

                if (isset($coupon_data['id']) && $campaign_id = get_post_meta($coupon_data['id'], 'cuw_created_campaign_id', true)) {
                    Stats::save($order, $item, ['campaign_id' => $campaign_id, 'coupon_id' => $coupon_data['id'], 'coupon_code' => $coupon_data['code']]); // save data to stats table
                    update_post_meta($coupon_data['id'], 'cuw_used_order_id', $order->get_id());
                    update_post_meta($coupon_data['id'], 'cuw_used_by', get_current_user_id() ? get_current_user_id() : $order->get_billing_email());
                    $has_coupons = true;
                }
            }
        }

        $meta_data = [];
        if ($has_offers) {
            $meta_data['_has_cuw_offers'] = true;
        }
        if ($has_products) {
            $meta_data['_has_cuw_products'] = true;
        }
        if ($has_coupons) {
            $meta_data['_has_cuw_coupons'] = true;
        }
        if (!empty($meta_data)) {
            self::saveMeta($order, $meta_data);
        }
    }

    /**
     * Check if the order has offers
     *
     * @param \WC_Order|int $order_or_id
     * @return bool
     */
    public static function hasOffers($order_or_id)
    {
        $order = WC::getOrder($order_or_id);
        return $order && $order->get_meta('_has_cuw_offers', true);
    }
}