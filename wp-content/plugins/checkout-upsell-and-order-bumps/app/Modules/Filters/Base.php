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

namespace CUW\App\Modules\Filters;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;

abstract class Base extends Controller
{
    /**
     * To check filter.
     *
     * @param array $filter
     * @param array $data
     * @return bool
     */
    abstract function check($filter, $data);

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
}