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

defined('ABSPATH') || exit;

class Price extends Base
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
        if (empty($filter['operator']) || empty($filter['value'])) {
            return [];
        }

        return [
            'sub_query' => 'meta',
            'key' => '_price',
            'compare' => html_entity_decode($filter['operator']),
            'value' => $filter['value'],
            'type' => 'NUMERIC',
        ];
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Engine/Filters/Price', $data, $print);
    }
}