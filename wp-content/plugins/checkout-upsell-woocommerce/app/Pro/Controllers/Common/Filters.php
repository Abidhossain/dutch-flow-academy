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

namespace CUW\App\Pro\Controllers\Common;

use CUW\App\Controllers\Controller;
use CUW\App\Pro\Modules\Filters\SKUs;
use CUW\App\Pro\Modules\Filters\Tags;

defined('ABSPATH') || exit;

class Filters extends Controller
{
    /**
     * To get filters.
     *
     * @return array
     */
    public static function get()
    {
        return [
            'tags' => [
                'handler' => new Tags(),
            ],
            'skus' => [
                'handler' => new SKUs(),
            ]
        ];
    }

    /**
     * To load filters.
     */
    public static function load()
    {
        add_filter('cuw_filters', function ($filters) {
            return array_merge_recursive($filters, self::get());
        });
    }
}
