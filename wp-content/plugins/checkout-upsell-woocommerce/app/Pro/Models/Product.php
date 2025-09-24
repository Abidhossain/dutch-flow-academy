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

namespace CUW\App\Pro\Models;

use CUW\App\Helpers\Functions;

defined('ABSPATH') || exit;

class Product
{
    /**
     * To cache results.
     *
     * @var array
     */
    public static $results = [];

    /**
     * Perform product query
     *
     * @param array $args
     * @param bool $cache
     * @return mixed
     */
    public static function performQuery($args, $cache = true)
    {
        $cache_key = Functions::generateHash($args);
        if ($cache && isset(self::$results[$cache_key])) {
            return self::$results[$cache_key];
        }

        $ids = [];
        if (!isset($args['filters']) || !empty($args['filters'])) {
            $ids = get_posts(self::prepareArgs($args));
        }

        if ($cache) {
            self::$results[$cache_key] = $ids;
        }
        return $ids;
    }

    /**
     * Parse args.
     *
     * @param array $args
     * @return array
     */
    private static function prepareArgs($args)
    {
        $args = array_merge([
            'post_type' => ['product'],
            'post_status' => 'publish',
            'numberposts' => apply_filters('cuw_product_query_limit', $args['limit'] ?? 20),
            'fields' => 'ids',
            'orderby' => 'post__in',
            'order' => 'DESC',
        ], $args);

        if (isset($args['filters'])) {
            $args['include'] = [];
            $args['exclude'] = [];
            foreach ($args['filters'] as $filter) {
                if (isset($filter['include'])) {
                    $args['include'] = array_merge($args['include'], $filter['include']);
                } elseif (isset($filter['exclude'])) {
                    $args['exclude'] = array_merge($args['exclude'], $filter['exclude']);
                } elseif (isset($filter['sub_query']) && $filter['sub_query'] == 'meta') {
                    unset($filter['sub_query']);
                    if (!isset($args['meta_query'])) {
                        $args['meta_query']['relation'] = 'AND';
                    }
                    $args['meta_query'][] = $filter;
                } elseif (isset($filter['sub_query']) && $filter['sub_query'] == 'taxonomy') {
                    unset($filter['sub_query']);
                    if (!isset($args['tax_query'])) {
                        $args['tax_query']['relation'] = 'AND';
                    }
                    $args['tax_query'][] = $filter;
                }
            }

            if (!empty($args['include']) && (!empty($args['tax_query']) || !empty($args['meta_query']))) {
                $sub_query_args = [
                    'numberposts' => apply_filters('cuw_product_sub_query_limit', ($args['limit'] ?? 20) * 100),
                ];
                if (!empty($args['meta_query'])) {
                    $sub_query_args['meta_query'] = $args['meta_query'];
                    unset($args['meta_query']);
                }
                if (!empty($args['tax_query'])) {
                    $sub_query_args['tax_query'] = $args['tax_query'];
                    unset($args['tax_query']);
                }
                $args['include'] = array_merge($args['include'], self::performQuery($sub_query_args));
            }
            if (!empty($args['include']) && !empty($args['exclude'])) {
                $args['include'] = array_diff($args['include'], $args['exclude']);
                unset($args['exclude']);
            }
            unset($args['filters']);
        }

        if (isset($args['amplifiers'])) {
            foreach ($args['amplifiers'] as $amplifier) {
                if (!empty($amplifier['order_by'])) {
                    $args['orderby'] = $amplifier['order_by'];
                    if (isset($amplifier['meta_key'])) {
                        $args['meta_key'] = $amplifier['meta_key'];
                    }
                    if (!empty($amplifier['sort'])) {
                        $args['order'] = strtoupper($amplifier['sort']);
                    }
                    break;
                }
            }
            unset($args['amplifiers']);
        }
        return $args;
    }
}