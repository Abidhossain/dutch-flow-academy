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

class UserLoggedIn extends Base
{
    /**
     * To check condition.
     *
     * @return bool
     */
    public function check($condition, $data)
    {
        if (!isset($condition['value']) || !function_exists('is_user_logged_in')) {
            return false;
        }

        if ($condition['value'] == "yes" && is_user_logged_in()) {
            return true;
        } elseif ($condition['value'] == "no" && !is_user_logged_in()) {
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
        return self::app()->view('Admin/Campaign/Conditions/UserLoggedIn', $data, $print);
    }
}