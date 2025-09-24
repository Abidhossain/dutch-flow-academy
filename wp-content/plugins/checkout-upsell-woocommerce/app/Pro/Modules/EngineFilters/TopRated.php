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

namespace CUW\App\Pro\Modules\EngineFilters;

use CUW\App\Pro\Helpers\Engine;
use CUW\App\Pro\Models\Product;

defined('ABSPATH') || exit;

class TopRated extends Base
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
        return [
            'include' => Product::performQuery([
                'numberposts' => Engine::getProductsFetchLimit(),
                'orderby' => 'meta_value_num',
                'meta_key' => '_wc_average_rating',
                'sort' => 'desc'
            ]),
        ];
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
