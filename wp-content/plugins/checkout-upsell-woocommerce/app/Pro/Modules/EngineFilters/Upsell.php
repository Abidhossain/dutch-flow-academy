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

namespace CUW\App\Pro\Modules\EngineFilters;

use CUW\App\Pro\Helpers\Engine;

defined('ABSPATH') || exit;

class Upsell extends Base
{
    /**
     * Build query args.
     *
     * @param array $filter
     * @param array $data
     * @return array
     */
    public function getQueryArgs($filter, $data = [])
    {
        if (empty($data)) {
            return [];
        }

        $upsell_product_ids = self::getIds($data, 'upsell');

        if (!empty($upsell_product_ids)) {
            return [
                'include' => $upsell_product_ids,
            ];
        }
        return [];
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