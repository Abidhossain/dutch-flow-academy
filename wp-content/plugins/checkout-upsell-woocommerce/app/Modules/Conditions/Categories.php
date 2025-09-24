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

class Categories extends Base
{
    /**
     * To hold category ids.
     *
     * @var array
     */
    private static $category_ids;

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

        if (!isset(self::$category_ids)) {
            $category_ids = [];
            foreach ($data['products'] as $product) {
                $category_ids = array_merge($category_ids, WC::getProductCategoryIds($product['id']));
            }
            self::$category_ids = array_unique($category_ids);
        }

        return self::checkLists($condition['values'], self::$category_ids, $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Conditions/Categories', $data, $print);
    }
}