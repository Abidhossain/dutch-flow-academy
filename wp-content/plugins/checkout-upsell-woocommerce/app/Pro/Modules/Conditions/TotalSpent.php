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

class TotalSpent extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['value']) || !isset($condition['operator']) || !isset($condition['order_statuses'])) {
            return false;
        }

        $args = [
            'select' => '{order_total}',
            'sum' => 'order_total',
            'based_on_current_user' => true,
            'statuses' => $condition['order_statuses'],
            'return' => 'var',
        ];
        if (!empty($condition['order_date'])) {
            $args['date_after'] = Functions::getDateByString($condition['order_date'] . ' 00:00:00');
        }

        $result = Order::performOrderQuery($args);
        $orders_total = is_numeric($result) ? $result : 0;
        return self::checkValues($orders_total, $condition['value'], $condition['operator']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/TotalSpent', $data, $print);
    }
}
