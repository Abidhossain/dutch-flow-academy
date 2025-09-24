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

class AllProducts extends Base
{
    /**
     * To check filter.
     *
     * @return bool
     */
    public function check($filter, $data)
    {
        return true;
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return '';
    }
}