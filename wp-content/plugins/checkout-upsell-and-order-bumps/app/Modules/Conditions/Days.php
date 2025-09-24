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

class Days extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['values']) || !isset($condition['method'])) {
            return false;
        }

        $day = array(strtolower(gmdate('l', current_time('timestamp'))));
        return self::checkLists($condition['values'], $day, $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Conditions/Days', $data, $print);
    }
}