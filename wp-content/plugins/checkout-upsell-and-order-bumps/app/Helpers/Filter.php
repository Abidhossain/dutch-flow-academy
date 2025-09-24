<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-and-order-bumps
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Modules\Filters;

class Filter
{
    /**
     * To hold filters
     *
     * @var array
     */
    public static $filters;

    /**
     * Get filters
     *
     * @param string $campaign_type
     * @return array
     */
    public static function get($campaign_type = '')
    {
        if (!isset(self::$filters)) {
            self::$filters = (array)apply_filters('cuw_filters', [
                // common filters
                'all_products' => [
                    'name' => __("All Products", 'checkout-upsell-and-order-bumps'),
                    'group' => __("Product", 'checkout-upsell-and-order-bumps'),
                    'handler' => new Filters\AllProducts(),
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons'],
                ],
                'products' => [
                    'name' => __("Specific products", 'checkout-upsell-and-order-bumps'),
                    'group' => __("Product", 'checkout-upsell-and-order-bumps'),
                    'handler' => new Filters\Products(),
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons'],
                ],
                'categories' => [
                    'name' => __("Product categories", 'checkout-upsell-and-order-bumps'),
                    'group' => __("Product", 'checkout-upsell-and-order-bumps'),
                    'handler' => new Filters\Categories(),
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons'],
                ],
                'tags' => [
                    'name' => __("Product tags", 'checkout-upsell-and-order-bumps'),
                    'group' => __("Product", 'checkout-upsell-and-order-bumps'),
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons'],
                ],
                'skus' => [
                    'name' => __("Product SKUs", 'checkout-upsell-and-order-bumps'),
                    'group' => __("Product", 'checkout-upsell-and-order-bumps'),
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons'],
                ],
            ]);
        }

        if ($campaign_type !== '') {
            $filters = [];
            foreach (self::$filters as $key => $filter) {
                if (in_array($campaign_type, $filter['campaigns'])) {
                    unset($filter['campaigns']);
                    $filters[$key] = $filter;
                }
            }
            return $filters;
        }
        return self::$filters;
    }

    /**
     * Check if the given filter is passed or not
     *
     * @param array $filter
     * @param array|object $data
     * @return bool
     */
    public static function check($filter, $data)
    {
        if (!isset(self::$filters)) {
            self::get();
        }

        if (isset(self::$filters[$filter['type']]) && isset(self::$filters[$filter['type']]['handler'])) {
            $is_passed = (bool)self::$filters[$filter['type']]['handler']->check($filter, $data);
        } else {
            $is_passed = false;
        }
        return (bool)apply_filters('cuw_filter_is_passed', $is_passed, $filter, $data);
    }
}