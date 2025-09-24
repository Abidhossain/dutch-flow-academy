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

use CUW\App\Helpers\WC;

defined('ABSPATH') || exit;

class Coupons extends Base
{
    /**
     * To hold applied coupon codes.
     *
     * @var array
     */
    private static $applied_coupons;

    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['method'])) {
            return false;
        }
        if (!isset(self::$applied_coupons)) {
            if ($data['type'] == 'order') {
                self::$applied_coupons = WC::getAppliedCouponsInOrder($data['id']);
            } else {
                self::$applied_coupons = WC::getAppliedCouponsInCart();
            }
        }
        if ($condition['method'] == 'empty') {
            return empty(self::$applied_coupons);
        } elseif ($condition['method'] == 'not_empty') {
            return !empty(self::$applied_coupons);
        } elseif (isset($condition['values'])) {
            return self::checkLists($condition['values'], self::$applied_coupons, $condition['method']);
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
        return self::app()->view('Admin/Campaign/Conditions/Coupons', $data, $print);
    }
}