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

use CUW\App\Helpers\WC;

defined('ABSPATH') || exit;

class Categories extends Base
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
        $category_ids = WC::getProductCategoryIds(!empty($data['parent_id']) ? $data['parent_id'] : $data['id']);
        return self::checkLists($filter['values'], $category_ids, $filter['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Filters/Categories', $data, $print);
    }
}