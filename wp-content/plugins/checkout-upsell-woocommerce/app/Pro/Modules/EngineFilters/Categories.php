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

class Categories extends Base
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
        if (empty($filter['method']) || empty($filter['values'])) {
            return [];
        }

        $args = [
            'sub_query' => 'taxonomy',
            'taxonomy' => 'product_cat',
            'terms' => $filter['values'],
        ];
        if ($filter['method'] == 'in_list') {
            $args['operator'] = 'IN';
        } elseif ($filter['method'] == 'not_in_list') {
            $args['operator'] = 'NOT IN';
        }
        return $args;
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Engine/Filters/Categories', $data, $print);
    }
}