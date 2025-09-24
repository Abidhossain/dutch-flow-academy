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

class Tags extends Base
{
    /**
     * To hold tag ids.
     *
     * @var array
     */
    private static $tag_ids;

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
        if (!isset(self::$tag_ids)) {
            $tag_ids = [];
            foreach ($data['products'] as $product) {
                $tag_ids = array_merge($tag_ids, WC::getProductTagIds($product['id']));
            }
            self::$tag_ids = array_unique($tag_ids);
        }
        return self::checkLists($condition['values'], self::$tag_ids, $condition['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Conditions/Tags', $data, $print);
    }
}
