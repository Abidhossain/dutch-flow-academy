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

namespace CUW\App\Pro\Modules\Conditions;

use CUW\App\Helpers\Functions;
use CUW\App\Models\Order;
use CUW\App\Modules\Conditions\Base;

defined('ABSPATH') || exit;

class OrdersMadeWithProducts extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['value']) || !isset($condition['operator']) || !isset($condition['order_statuses']) || !isset($condition['order_product_ids'])) {
            return false;
        }

        $args = [
            'select' => ['{id}', 'product_id', 'variation_id'],
            'join' => 'order_items',
            'where' => [
                [
                    'column' => 'product_id',
                    'operator' => 'IN',
                    'value' => $condition['order_product_ids'],
                ],
                [
                    'column' => 'variation_id',
                    'operator' => 'IN',
                    'value' => $condition['order_product_ids'],
                ]
            ],
            'where_relation' => 'OR',
            'count' => 'product_id',
            'count_results' => true,
            'count_distinct' => true,
            'based_on_current_user' => true,
            'group_by' => 'id',
            'statuses' => $condition['order_statuses'],
            'return' => 'var',
        ];
        if (!empty($condition['order_date'])) {
            $args['date_after'] = Functions::getDateByString($condition['order_date'] . ' 00:00:00');
        }

        $result = Order::performOrderQuery($args);
        $orders_count = is_numeric($result) ? $result : 0;
        return self::checkValues($orders_count, $condition['value'], $condition['operator']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/OrdersMadeWithProducts', $data, $print);
    }
}