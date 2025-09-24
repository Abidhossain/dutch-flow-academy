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

namespace CUW\App\Pro\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Helpers\WC;

class Order extends \CUW\App\Helpers\Order
{
    /**
     * Create an offer order
     *
     * @param \WC_Order $parent_order
     * @return \WC_Order|false
     */
    public static function generateOfferOrder($parent_order, $product, $offer_data)
    {
        $parent_order_id = $parent_order->get_id();
        try {
            if (function_exists('wc_create_order')) {
                $order = wc_create_order([
                    'status' => null,
                    'parent' => $parent_order_id,
                    'customer_id' => apply_filters('woocommerce_checkout_customer_id', get_current_user_id()),
                    'created_via' => 'cuw',
                ]);
            }
            if (empty($order) || is_wp_error($order)) {
                return false;
            }

            $clone = [
                'billing_first_name',
                'billing_last_name',
                'billing_company',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_postcode',
                'billing_country',
                'billing_email',
                'billing_phone',

                'shipping_first_name',
                'shipping_last_name',
                'shipping_company',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_postcode',
                'shipping_country',
                'shipping_phone',

                'customer_note',
                'payment_method',
                'payment_method_title',
            ];
            foreach ($clone as $method) {
                if (method_exists($parent_order, "get_$method") && method_exists($order, "set_$method")) {
                    $order->{"set_$method"}($parent_order->{"get_$method"}());
                }
            }

            self::addOffer($order, $product, $offer_data);

            $order = apply_filters('cuw_offer_order', $order, $parent_order);
            $order_id = $order->save();

            Order::saveMeta($parent_order, ['_cuw_offer_order_id' => $order_id]);
        } catch (\Exception $e) {
        }

        return !empty($order) ? $order : false;
    }

    /**
     * Check if the order is an offer order
     *
     * @param int|\WC_Order $order_or_id
     * @return bool
     */
    public static function isOfferOrder($order_or_id)
    {
        $order = WC::getOrder($order_or_id);
        return $order && $order->is_created_via('cuw');
    }

    /**
     * Get an offer order by parent order or id
     *
     * @param int|\WC_Order $parent_order_or_id
     * @return \WC_Order|false
     */
    public static function getOfferOrder($parent_order_or_id)
    {
        $order = WC::getOrder($parent_order_or_id);
        if ($order && $offer_order_id = $order->get_meta('_cuw_offer_order_id', true)) {
            return WC::getOrder($offer_order_id);
        }
        return false;
    }

    /**
     * Get a main order by offer order or id
     *
     * @param int|\WC_Order $offer_order_or_id
     * @return \WC_Order|false
     */
    public static function getMainOrder($offer_order_or_id)
    {
        $order = WC::getOrder($offer_order_or_id);
        if ($order && self::isOfferOrder($order) && $parent_id = $order->get_parent_id()) {
            return WC::getOrder($parent_id);
        }
        return false;
    }
}