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

use CUW\App\Helpers\Input;

defined('ABSPATH') || exit;

class RecentlyViewed extends Base
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
        $recently_viewed_products = Input::get('cuw_recently_viewed_products', '', 'cookie');
        if (empty($recently_viewed_products) || !is_string($recently_viewed_products)) {
            return [];
        }

        return [
            'include' => explode('|', $recently_viewed_products),
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