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

namespace CUW\App\Pro\Modules\EngineFilters;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Product;

abstract class Base extends Controller
{
    /**
     * Build query args.
     *
     * @param array $filter
     * @param array $data
     * @return array
     */
    abstract function getQueryArgs($filter, $data = []);

    /**
     * To get template.
     *
     * @param array $data
     * @param bool $print
     * @return bool
     */
    abstract function template($data = [], $print = false);

    /**
     * Get product IDs
     *
     * @param array $data
     * @param string $from
     * @return array
     */
    public static function getIds($data, $from = '')
    {
        if (!empty($data) && is_object($data)) {
            $ids = [];
            if (is_a($data, '\WC_Cart') && method_exists($data, 'get_cart_contents')) {
                foreach ($data->get_cart_contents() as $cart_item) {
                    $ids = array_merge($ids, Product::getIds($cart_item['product_id'], $from));
                }
            } else if (is_a($data, '\WC_Order') && method_exists($data, 'get_items')) {
                foreach ($data->get_items() as $order_item) {
                    $ids = array_merge($ids, Product::getIds($order_item->get_product_id(), $from));
                }
            } else if (is_a($data, '\WC_Product')) {
                $ids = array_merge($ids, Product::getIds($data, $from));
            }

            if (!empty($ids)) {
                return $ids;
            }
        }

        return [];
    }
}