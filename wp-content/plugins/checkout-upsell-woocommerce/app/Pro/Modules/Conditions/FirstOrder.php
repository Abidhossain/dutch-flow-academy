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

use CUW\App\Models\Order;
use CUW\App\Modules\Conditions\Base;

defined('ABSPATH') || exit;

class FirstOrder extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['value'])) {
            return false;
        }

        $result = Order::performOrderQuery([
            'select' => 'COUNT({id})',
            'based_on_current_user' => true,
            'return' => 'var',
        ]);

        $orders_count = is_numeric($result) ? $result : 0;
        if ($orders_count > 0 && isset($data['type']) && $data['type'] == 'order') {
            $orders_count--; // to ignore current order count
        }
        if ($condition['value'] == "yes" && $orders_count == 0) {
            return true;
        } elseif ($condition['value'] == "no" && $orders_count > 0) {
            return true;
        }
        return false;
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/FirstOrder', $data, $print);
    }
}