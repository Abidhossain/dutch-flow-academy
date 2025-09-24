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

use CUW\App\Modules\Conditions\Base;

defined('ABSPATH') || exit;

class Time extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['values']) || !isset($condition['method']) || !function_exists('current_datetime')) {
            return false;
        }

        $current_time = self::convertToSecond(current_datetime()->format('H:i'));
        $from_time = self::convertToSecond(!empty($condition['values']['from']) ? $condition['values']['from'] : '00:00');
        $to_time = self::convertToSecond(!empty($condition['values']['to']) ? $condition['values']['to'] : '23:59');

        if ($from_time == $to_time) {
            return false;
        }
        if ($from_time > $to_time) {
            return (($current_time >= $from_time && $current_time <= (24 * 60 * 60)) || ($current_time >= 0 && $current_time <= $to_time)) == ($condition['method'] == 'in');
        } else {
            return ($current_time >= $from_time && $current_time <= $to_time) == ($condition['method'] == 'in');
        }
    }

    /**
     * Convert time (H:i) to seconds.
     *
     * @param string $time
     * @return int
     */
    private static function convertToSecond($time)
    {
        if (strpos($time, ':') !== false) {
            list($hours, $minutes) = explode(':', $time);
            return ($hours * 60 * 60) + ($minutes * 60);
        }
        return 0;
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/Time', $data, $print);
    }
}