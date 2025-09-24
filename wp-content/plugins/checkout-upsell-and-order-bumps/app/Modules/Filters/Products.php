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

class Products extends Base
{
    /**
     * To check filter.
     *
     * @return bool
     */
    public function check($filter, $data)
    {
        if (!isset($filter['values']) || !isset($filter['method'])) {
            return false;
        }

        $product_ids = [];
        if (!empty($data['id'])) {
            $product_ids[] = $data['id'];
        }
        if (!empty($data['parent_id'])) {
            $product_ids[] = $data['parent_id'];
        }
        return self::checkLists($filter['values'], $product_ids, $filter['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Filters/Products', $data, $print);
    }
}