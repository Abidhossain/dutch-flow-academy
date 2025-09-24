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

namespace CUW\App\Controllers\Store;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Order;

class Checkout extends Controller
{
    /**
     * Add order item meta.
     *
     * @hooked woocommerce_checkout_create_order_line_item
     */
    public static function addOrderItemMeta($order_item, $cart_item_key, $cart_item)
    {
        if (isset($cart_item['cuw_offer']) && $offer = $cart_item['cuw_offer']) {
            Order::saveDataToItemMeta($order_item, $offer);
        } elseif (isset($cart_item['cuw_product']) && $data = $cart_item['cuw_product']) {
            Order::saveDataToItemMeta($order_item, $data);
        }
    }

    /**
     * To save item data to order item meta.
     *
     * @hooked woocommerce_checkout_order_created|woocommerce_store_api_checkout_order_processed
     */
    public static function saveStats($order)
    {
        $update = (current_action() == 'woocommerce_store_api_checkout_order_processed');
        Order::saveStats($order, $update);
    }
}