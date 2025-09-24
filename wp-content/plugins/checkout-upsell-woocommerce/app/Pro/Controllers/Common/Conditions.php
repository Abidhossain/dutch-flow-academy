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

namespace CUW\App\Pro\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Pro\Modules\Conditions\FirstOrder;
use CUW\App\Pro\Modules\Conditions\OrdersMade;
use CUW\App\Pro\Modules\Conditions\OrdersMadeWithProducts;
use CUW\App\Pro\Modules\Conditions\SKUs;
use CUW\App\Pro\Modules\Conditions\Tags;
use CUW\App\Pro\Modules\Conditions\Time;
use CUW\App\Pro\Modules\Conditions\TotalSpent;
use CUW\App\Pro\Modules\Conditions\Users;

class Conditions extends Controller
{
    /**
     * To get conditions.
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
            ],
            'order_tags' => [
                'handler' => new Tags(),
            ],
            'order_skus' => [
                'handler' => new SKUs(),
            ],
            'users' => [
                'handler' => new Users(),
            ],
            'time' => [
                'handler' => new Time(),
            ],
            'first_order' => [
                'handler' => new FirstOrder(),
            ],
            'orders_made' => [
                'handler' => new OrdersMade(),
            ],
            'orders_made_with_products' => [
                'handler' => new OrdersMadeWithProducts(),
            ],
            'total_spent' => [
                'handler' => new TotalSpent(),
            ],
        ];
    }

    /**
     * To load conditions.
     */
    public static function load()
    {
        add_filter('cuw_conditions', function ($conditions) {
            return array_merge_recursive($conditions, self::get());
        });
    }
}