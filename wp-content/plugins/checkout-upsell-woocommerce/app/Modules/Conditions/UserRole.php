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

use CUW\App\Helpers\WP;

defined('ABSPATH') || exit;

class UserRole extends Base
{
    /**
     * To hold current user roles.
     *
     * @var array
     */
    private static $current_user_roles;

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

        if (!isset(self::$current_user_roles)) {
            $current_user = WP::getCurrentUser();
            self::$current_user_roles = !empty($current_user) ? WP::getRole($current_user) : ['cuw_guest'];
        }

        return self::checkLists($condition['values'], self::$current_user_roles, $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Conditions/UserRole', $data, $print);
    }
}