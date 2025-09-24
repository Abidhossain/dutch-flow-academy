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

use CUW\App\Controllers\Controller;

abstract class Base extends Controller
{
    /**
     * To check condition.
     *
     * @param array $condition
     * @param array $data
     * @return bool
     */
    abstract function check($condition, $data);

    /**
     * To get template.
     *
     * @param array $data
     * @param bool $print
     * @return bool
     */
    abstract function template($data = [], $print = false);

    /**
     * Check two arrays.
     *
     * @param array $array1
     * @param array $array2
     * @param string $method
     * @return bool
     */
    protected static function checkLists($array1, $array2, $method)
    {
        if ($method == "in_list") {
            return !empty(array_intersect($array1, $array2));
        } elseif ($method == "not_in_list") {
            return empty(array_intersect($array1, $array2));
        }
        return false;
    }

    /**
     * Check two values.
     *
     * @param int|string $value1
     * @param int|string $value2
     * @param string $operator
     * @return bool
     */
    protected static function checkValues($value1, $value2, $operator)
    {
        if ($operator == "ge") {
            return $value1 >= $value2;
        } elseif ($operator == "gt") {
            return $value1 > $value2;
        } elseif ($operator == "le") {
            return $value1 <= $value2;
        } elseif ($operator == "lt") {
            return $value1 < $value2;
        } elseif ($operator == "eq") {
            return $value1 == $value2;
        } elseif ($operator == "range") {
            if (strpos($value2, '-') !== false) {
                list($range_from, $range_to) = explode('-', $value2, 2);
                if ($range_from < $range_to) {
                    return ($range_from <= $value1) && ($value1 <= $range_to);
                }
            }
        }
        return false;
    }
}