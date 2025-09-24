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

use CUW\App\Helpers\WC;
use CUW\App\Modules\Conditions\Base;

defined('ABSPATH') || exit;

class SKUs extends Base
{
    /**
     * To hold sku ids.
     *
     * @var array
     */
    private static $skus;

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
        if (!isset(self::$skus)) {
            $skus = [];
            foreach ($data['products'] as $product) {
                $sku = WC::getProductSku($product['id']);
                if (!empty($sku)) {
                    $skus[] = $sku;
                }
            }
            self::$skus = array_unique($skus);
        }
        return self::checkLists($condition['values'], self::$skus, $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/SKUs', $data, $print);
    }
}