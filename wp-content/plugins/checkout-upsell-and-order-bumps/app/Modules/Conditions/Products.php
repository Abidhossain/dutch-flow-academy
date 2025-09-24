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

class Products extends Base
{
    /**
     * To hold product ids.
     *
     * @var array
     */
    private static $product_ids;

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

        if (!isset(self::$product_ids)) {
            $product_ids = [];
            foreach ($data['products'] as $product) {
                $product_ids[] = $product['id'];
                if ($product['variation_id']) {
                    $product_ids[] = $product['variation_id'];
                }
            }
            self::$product_ids = array_unique($product_ids);
        }

        return self::checkLists($condition['values'], self::$product_ids, $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Admin/Campaign/Conditions/Products', $data, $print);
    }
}