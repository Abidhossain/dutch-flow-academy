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

namespace CUW\App\Pro\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Config;
use CUW\App\Pro\Models\Engine as EngineModel;
use CUW\App\Pro\Modules\EngineAmplifiers;
use CUW\App\Pro\Modules\EngineFilters;
use CUW\App\Pro\Modules\Engines;

class Engine
{
    /**
     * To hold engines data.
     *
     * @var array
     */
    private static $data;

    /**
     * To hold filters
     *
     * @var array
     */
    private static $filters;

    /**
     * To get engine data.
     *
     * @param string $type
     * @param string $campaign_type
     * @return array
     */
    public static function get($type = '', $campaign_type = '')
    {
        if (!isset(self::$data)) {
            self::$data = apply_filters('cuw_engine_types', [
                'generic' => [
                    'title' => __("Generic", 'checkout-upsell-woocommerce'),
                    'icon' => 'generic-engine',
                    'description' => __("Choose this type to generate a list of products based on categories, tags and more", 'checkout-upsell-woocommerce'),
                    'pages' => ['loop', 'product', 'cart', 'checkout', 'order_received'],
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons', 'upsell_popups', 'thankyou_upsells'],
                    'handler' => new Engines\Generic(),
                ],
                'product' => [
                    'title' => __("Product", 'checkout-upsell-woocommerce'),
                    'icon' => 'product-engine',
                    'description' => __("Choose this type to generate a list of products based on the current product and more", 'checkout-upsell-woocommerce'),
                    'pages' => ['product'],
                    'campaigns' => ['fbt', 'product_addons', 'cart_addons'],
                    'handler' => new Engines\Product(),
                ],
                'cart' => [
                    'title' => __("Cart", 'checkout-upsell-woocommerce'),
                    'icon' => 'cart-engine',
                    'description' => __("Choose this type to generate a list of products based on the products in the cart and more", 'checkout-upsell-woocommerce'),
                    'pages' => ['loop', 'cart', 'checkout'],
                    'campaigns' => ['upsell_popups'],
                    'handler' => new Engines\Cart(),
                ],
                'order' => [
                    'title' => __("Order", 'checkout-upsell-woocommerce'),
                    'icon' => 'order-engine',
                    'description' => __("Choose this type to generate a list of products based on the products in an order and more", 'checkout-upsell-woocommerce'),
                    'pages' => ['order_received'],
                    'campaigns' => ['thankyou_upsells'],
                    'handler' => new Engines\Order(),
                ],
            ]);
        }

        if ($type !== '') {
            return isset(self::$data[$type]) ? self::$data[$type] : false;
        }

        if ($campaign_type !== '') {
            $campaigns = [];
            foreach (self::$data as $key => $data) {
                if (in_array($campaign_type, $data['campaigns'])) {
                    unset($data['campaigns']);
                    $campaigns[] = $key;
                }
            }
            return $campaigns;
        }

        return self::$data;
    }

    /**
     * Returns engine pages data.
     *
     * @return array
     */
    public static function getPages()
    {
        return [
            'loop' => __("Shop & category page", 'checkout-upsell-woocommerce'),
            'product' => __("Product page", 'checkout-upsell-woocommerce'),
            'cart' => __("Cart page", 'checkout-upsell-woocommerce'),
            'checkout' => __("Checkout page", 'checkout-upsell-woocommerce'),
            'order_received' => __("Thank you page", 'checkout-upsell-woocommerce'),
        ];
    }

    /**
     * Get products ids.
     *
     * @param array|int $engine_or_id
     * @param \WC_Product|\WC_Cart|\WC_Order|null $source
     * @return array|false
     */
    public static function getProductIds($engine_or_id, $source = null)
    {
        $engine = $engine_or_id;
        if (is_numeric($engine_or_id)) {
            $engine = EngineModel::get($engine_or_id, ['id', 'type', 'filters', 'amplifiers']);
        }
        if (!empty($engine) && !empty($engine['type']) && !empty($engine['filters'])) {
            $data = self::get($engine['type']);
            if (!empty($data) && !empty($data['handler'])) {
                $is_engine_cacheable = self::isCacheable($engine);
                if ($is_engine_cacheable && $product_ids = EngineModel::getCache($engine['id'], 'product_ids')) {
                    return $product_ids;
                } else {
                    $product_ids = (array)$data['handler']->getProductIds($engine, $source);
                    $product_ids = apply_filters('cuw_engine_generated_product_ids', array_unique($product_ids), $engine, $source);
                    if ($is_engine_cacheable) {
                        EngineModel::setCache($engine['id'], 'product_ids', $product_ids);
                    }
                    return $product_ids;
                }
            }
        }
        return false;
    }

    /**
     * Check if the engine product ids are cacheable.
     *
     * @param array $engine
     * @return bool
     */
    private static function isCacheable($engine)
    {
        $filters = self::getFilters();
        $cache_enabled = (bool)apply_filters('cuw_engine_caching_enabled', EngineModel::isCachingEnabled(), $engine);
        if ($cache_enabled && !empty($filters)) {
            foreach ($engine['filters'] as $filter) {
                if (!empty($filter['type']) && empty($filters[$filter['type']]['cache'])) {
                    return false;
                }
            }
        }
        return $cache_enabled;
    }

    /**
     * Get filters
     *
     * @param string $type
     * @return array
     */
    public static function getFilters($type = '')
    {
        if (!isset(self::$filters)) {
            self::$filters = apply_filters('cuw_engine_filters', [
                'products' => [
                    'name' => __("Specific products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Products(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include or exclude specific products.", 'checkout-upsell-woocommerce'),
                    'icon' => 'specific-products-filter',
                    'cache' => true,
                ],
                'categories' => [
                    'name' => __("Product categories", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Categories(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include or exclude specific category of products.", 'checkout-upsell-woocommerce'),
                    'icon' => 'product-categories-filter',
                    'cache' => true,
                ],
                'tags' => [
                    'name' => __("Product tags", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Tags(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include or exclude products with selected tags.", 'checkout-upsell-woocommerce'),
                    'icon' => 'product-tags-filter',
                    'cache' => true,
                ],
                'price' => [
                    'name' => __("Product price", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Price(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include products by product price.", 'checkout-upsell-woocommerce'),
                    'icon' => 'product-price-filter',
                    'cache' => true,
                ],
                'best_selling' => [
                    'name' => __("Best selling products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\BestSelling(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include your all-time best selling products.", 'checkout-upsell-woocommerce'),
                    'icon' => 'best-selling-filter',
                    'cache' => true,
                ],
                'top_rated' => [
                    'name' => __("Top rated products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\TopRated(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include your top rated products.", 'checkout-upsell-woocommerce'),
                    'icon' => 'top-rated-filter',
                    'cache' => true,
                ],
                'new_arrivals' => [
                    'name' => __("Newest products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\NewArrivals(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include recently created products.", 'checkout-upsell-woocommerce'),
                    'icon' => 'new-arrivals-filter',
                    'cache' => true,
                ],
                'featured' => [
                    'name' => __("Featured products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Featured(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include your featured products.", 'checkout-upsell-woocommerce'),
                    'icon' => 'featured-filter',
                    'cache' => true,
                ],
                'on_sale' => [
                    'name' => __("On sale products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\OnSale(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include products currently on sale.", 'checkout-upsell-woocommerce'),
                    'icon' => 'on-sale-filter',
                    'cache' => true,
                ],
                'recently_viewed' => [
                    'name' => __("Recently viewed products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\RecentlyViewed(),
                    'types' => ['generic', 'product', 'cart', 'order'],
                    'description' => __("Choose this filter to include recently viewed products by the customer.", 'checkout-upsell-woocommerce'),
                    'icon' => 'recently-viewed-filter',
                    'cache' => false,
                ],
                'fbt' => [
                    'name' => __("Brought together products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\FBT(),
                    'types' => ['product'],
                    'description' => __("Choose this filter to include products that were purchased with the current product.", 'checkout-upsell-woocommerce'),
                    'icon' => 'fbt-filter',
                    'cache' => false,
                ],
                'related' => [
                    'name' => __("Related products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Related(),
                    'types' => ['product', 'cart', 'order'],
                    'description' => __("Choose this filter to include related products based on the current product, cart or order items.", 'checkout-upsell-woocommerce'),
                    'icon' => 'related-products-filter',
                    'cache' => false,
                ],
                'cross_sell' => [
                    'name' => __("Cross-sell products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\CrossSell(),
                    'types' => ['product', 'cart', 'order'],
                    'description' => __("Choose this filter to include cross-selling products based on the current product, cart or order items.", 'checkout-upsell-woocommerce'),
                    'icon' => 'cross-sells-filter',
                    'cache' => false,
                ],
                'upsell' => [
                    'name' => __("Upsell products", 'checkout-upsell-woocommerce'),
                    'handler' => new EngineFilters\Upsell(),
                    'types' => ['product', 'cart', 'order'],
                    'description' => __("Choose this filter to include upselling products based on the current product, cart or order items.", 'checkout-upsell-woocommerce'),
                    'icon' => 'upsells-filter',
                    'cache' => false,
                ],
            ]);
        }

        if ($type !== '') {
            $filters = [];
            foreach (self::$filters as $key => $filter) {
                if (in_array($type, $filter['types'])) {
                    unset($filter['types']);
                    $filters[$key] = $filter;
                }
            }
            return $filters;
        }
        return self::$filters;
    }

    /**
     * Return filters query args
     *
     * @param array $filters
     * @param array $data
     * @return array
     */
    public static function getFiltersQueryArgs($filters, $data = [])
    {
        if (empty($filters)) {
            return [];
        }

        $args = [];
        $filters_data = self::getFilters();
        foreach ($filters as $filter) {
            if (isset($filters_data[$filter['type']]) && !empty($filters_data[$filter['type']]['handler'])) {
                $query_args = (array)$filters_data[$filter['type']]['handler']->getQueryArgs($filter, $data);
                if (!empty($query_args)) {
                    $args[] = $query_args;
                }
            }
        }
        return $args;
    }

    /**
     * Return amplifiers query args.
     *
     * @param array $amplifiers
     * @param array $data
     * @return array
     */
    public static function getAmplifiersQueryArgs($amplifiers, $data = [])
    {
        if (empty($amplifiers)) {
            return [];
        }

        $args = [];
        $amplifiers_data = self::getAmplifiers();
        foreach ($amplifiers as $filter) {
            if (isset($amplifiers_data[$filter['type']]) && !empty($amplifiers_data[$filter['type']]['handler'])) {
                $args[] = (array)$amplifiers_data[$filter['type']]['handler']->getQueryArgs($filter, $data);
            }
        }
        return $args;
    }

    /**
     * Get amplifiers
     *
     * @return array
     */
    public static function getAmplifiers()
    {
        return apply_filters('cuw_engine_amplifiers', [
            'default' => [
                'name' => __("Default", 'checkout-upsell-woocommerce'),
                'handler' => new EngineAmplifiers\PostIn(),
                'description' => __("Choose this sort to list products as they are generated by the recommendation engine.", 'checkout-upsell-woocommerce'),
                'icon' => 'default-sort',
            ],
            'random' => [
                'name' => __("Random", 'checkout-upsell-woocommerce'),
                'handler' => new EngineAmplifiers\Random(),
                'description' => __("Choose this sort to list the products in random order.", 'checkout-upsell-woocommerce'),
                'icon' => 'random-sort',
            ],
            'name' => [
                'name' => __("Name", 'checkout-upsell-woocommerce'),
                'handler' => new EngineAmplifiers\Name(),
                'description' => __("Choose this sort to list the products by product name.", 'checkout-upsell-woocommerce'),
                'icon' => 'name-sort',
            ],
            'top_rated' => [
                'name' => __("Top rated", 'checkout-upsell-woocommerce'),
                'handler' => new EngineAmplifiers\Rating(),
                'description' => __("Choose this sort to list the products by product rating.", 'checkout-upsell-woocommerce'),
                'icon' => 'top-rated-sort',
            ],
            'freshness' => [
                'name' => __("Freshness", 'checkout-upsell-woocommerce'),
                'handler' => new EngineAmplifiers\Freshness(),
                'description' => __("Choose this sort to list the products by recently created.", 'checkout-upsell-woocommerce'),
                'icon' => 'freshness-sort',
            ],
            'price' => [
                'name' => __("Price", 'checkout-upsell-woocommerce'),
                'handler' => new EngineAmplifiers\Price(),
                'description' => __("Choose this sort to list the products by product price.", 'checkout-upsell-woocommerce'),
                'icon' => 'price-sort',
            ],
        ]);
    }

    /**
     * Get type
     *
     * @param int|array $engine
     * @param bool $detailed
     * @return string|array|false
     */
    public static function getType($engine, $detailed = false)
    {
        if (is_numeric($engine)) {
            $engine = EngineModel::getRowById((int)$engine, ['type']);
        }
        if (!is_array($engine) || !isset($engine['type'])) {
            return false;
        }

        if ($data = self::get($engine['type'])) {
            $type = ['type' => $engine['type'], 'color' => !empty($data['color']) ? $data['color'] : '0,0,0', 'text' => $data['title']];
        }
        return isset($type) ? ($detailed ? $type : $type['type']) : false;
    }

    /**
     * Get types
     *
     * @param string $key
     * @return array|string|false
     */
    public static function getTypes($key = '')
    {
        $types = [];
        foreach (self::get() as $type => $engine) {
            $types[$type] = $engine['title'];
        }
        return $key === '' ? $types : (isset($types[$key]) ? $types[$key] : false);
    }

    /**
     * Get status
     *
     * @param int|array $engine
     * @param bool $detailed
     * @return array|string|false
     */
    public static function getStatus($engine, $detailed = false)
    {
        if (is_numeric($engine)) {
            $engine = EngineModel::getRowById((int)$engine, ['enabled']);
        }

        if ($engine['enabled'] == 1) {
            $status = ['code' => 'active', 'text' => self::getStatuses('active')];
        } else {
            $status = ['code' => 'draft', 'text' => self::getStatuses('draft')];
        }
        return $detailed ? $status : $status['code'];
    }

    /**
     * Get statuses
     */
    public static function getStatuses($key = '')
    {
        $statuses = [
            'active' => __("Active", 'checkout-upsell-woocommerce'),
            'draft' => __("Draft", 'checkout-upsell-woocommerce'),
        ];
        return $key === '' ? $statuses : (isset($statuses[$key]) ? $statuses[$key] : false);
    }

    /**
     * Engine products fetch limit.
     *
     * @return int
     */
    public static function getProductsFetchLimit()
    {
        return (int)Config::getSetting('engine_products_fetch_limit');
    }
}
