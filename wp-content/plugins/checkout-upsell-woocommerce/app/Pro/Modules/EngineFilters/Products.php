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

class Products extends Base
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

        $args = [];
        if ($filter['method'] == 'in_list') {
            $args['include'] = $filter['values'];
        } elseif ($filter['method'] == 'not_in_list') {
            $args['exclude'] = $filter['values'];
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
        return self::app()->view('Pro/Admin/Engine/Filters/Products', $data, $print);
    }
}