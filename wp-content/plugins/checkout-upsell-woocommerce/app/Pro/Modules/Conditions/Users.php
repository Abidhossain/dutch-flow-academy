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

use CUW\App\Helpers\WP;
use CUW\App\Modules\Conditions\Base;

defined('ABSPATH') || exit;

class Users extends Base
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

        $current_user_id = WP::getCurrentUserId();
        if (empty($current_user_id)) {
            return false;
        }
        return self::checkLists($condition['values'], [$current_user_id], $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/Users', $data, $print);
    }
}