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

namespace CUW\App\Modules\Conditions;

defined('ABSPATH') || exit;

class QuantitiesCount extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['value']) || !isset($condition['operator']) || !isset($data['products'])) {
            return false;
        }
        $quantities_count = round(array_sum(array_column($data['products'], 'quantity')), 4);
        return self::checkValues($quantities_count, $condition['value'], $condition['operator']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Conditions/Count', $data, $print);
    }
}