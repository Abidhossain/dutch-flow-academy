<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Team UpsellWP <team@upsellwp.com>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Pro\Modules\Filters;

use CUW\App\Helpers\WC;
use CUW\App\Modules\Filters\Base;

class Tags extends Base
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
        $tag_ids = WC::getProductTagIds(!empty($data['parent_id']) ? $data['parent_id'] : $data['id']);
        return self::checkLists($filter['values'], $tag_ids, $filter['method']);
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Campaign/Filters/Tags', $data, $print);
    }
}