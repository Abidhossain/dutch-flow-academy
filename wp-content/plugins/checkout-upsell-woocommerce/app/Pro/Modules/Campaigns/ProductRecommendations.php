<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 Flycart
 * @license   GPL-3.0-or-later
 * @link      https://flycart.org
 */

namespace CUW\App\Pro\Modules\Campaigns;

use CUW\App\Helpers\Product;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Pro\Helpers\Engine;
use CUW\App\Pro\Helpers\Validate;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Pro\Models\Engine as EngineModel;
use CUW\App\Pro\Helpers\Page;
use CUW\App\Helpers\Order;
use CUW\App\Helpers\Cart;

defined('ABSPATH') or exit;

class ProductRecommendations extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'product_recommendations';

    /**
     * To hold campaigns
     *
     * @var array
     */
    private static $campaigns;

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            add_action('cuw_campaign_contents', [__CLASS__, 'campaignEditView'], 10, 2);
            add_filter('cuw_page_localize_campaign_data', [__CLASS__, 'addCampaignPageData'], 10, 2);
            add_filter('cuw_campaign_data_before_save', [__CLASS__, 'saveEngine'], 10);
            add_action('cuw_campaign_data_before_duplicate', [__CLASS__, 'duplicateEngine'], 10, 2);
            add_action('cuw_campaign_save_failed', [__CLASS__, 'deleteEngine'], 10);
            add_action('cuw_campaign_deleted', [__CLASS__, 'afterCampaignDeleted'], 10, 2);
        } else {
            if (self::isEnabled()) {
                add_action('wp', function () {
                    $display_locations = [];
                    $all_display_locations = self::getDisplayLocations();
                    if (WC::is('endpoint')) {
                        $endpoint = str_replace('-', '_', WC::getCurrentEndpoint());
                        $GLOBALS['cuw_current_page'] = $endpoint;
                        $display_locations = $all_display_locations[$endpoint] ?? [];
                    } else {
                        foreach ($all_display_locations as $page => $locations) {
                            if (WC::is($page, true)) {
                                $GLOBALS['cuw_current_page'] = $page;
                                $display_locations = $locations;
                                break;
                            }
                        }
                    }
                    foreach ($display_locations as $location) {
                        if ($location['hook'] !== 'shortcode') {
                            $priority = isset($location['priority']) ? (int)$location['priority'] : 10;
                            add_action($location['hook'], [__CLASS__, 'showProducts'], $priority);
                        }
                    }
                });

                add_action('cuw_before_products_loop', [__CLASS__, 'beforeProductsLoop'], 10, 2);
                add_action('cuw_after_products_loop', [__CLASS__, 'afterProductsLoop'], 10);
                add_action('woocommerce_after_add_to_cart_button', [__CLASS__, 'maybeLoadCampaignInputs']);
            }
        }

        if (self::isEnabled()) {
            add_filter('woocommerce_add_cart_item_data', [__CLASS__, 'prepareCartItemData'], 10, 4);
        }
    }

    /**
     * To show campaign customization.
     *
     * @hooked cuw_before_campaign_contents
     */
    public static function campaignEditView($campaign_type, $campaign)
    {
        if ($campaign_type == self::TYPE) {
            self::app()->view('Pro/Admin/Campaign/ProductRecommendations', [
                'campaign' => $campaign,
            ]);
        }
    }

    /**
     * To show products.
     */
    public static function showProducts($data = null)
    {
        self::getProductsHtml(current_action(), $data);
    }

    /**
     * Get products html.
     */
    public static function getProductsHtml($location, $data = null)
    {
        $html = '';
        $campaigns = self::getMatchedCampaigns($location);
        if (!empty($campaigns)) {
            foreach ($campaigns as $campaign) {
                $engine_product_ids = [];
                $order_products_ids = [];
                $classes = '';
                $display_limit = $campaign['data']['display_limit'] ?? '';
                if (isset($campaign['data']['engine_id']) && isset($campaign['data']['page'])) {
                    $source = null;
                    $page = Page::get($campaign['data']['page']);
                    $locations = Page::getLocations($campaign['data']['page']);
                    if (!empty($locations[$location]['class'])) {
                        $classes = implode(' ', $locations[$location]['class']);
                    }

                    if (!empty($page) && !empty($page['engine_type'])) {
                        if ($page['engine_type'] == 'cart') {
                            $source = WC::getCart();
                        } elseif ($page['engine_type'] == 'order' && !empty($data) && (is_numeric($data) || is_object($data))) {
                            $source = WC::getOrder($data);
                            $order_data = Order::getData($source, true);
                            $order_products_ids = !empty($order_data['products']) ? array_column($order_data['products'], 'id') : [];
                        } elseif ($page['engine_type'] == 'product') {
                            $source = WC::getProduct();
                        }

                        $use_products = 'engine';
                        $engine_product_ids = Engine::getProductIds($campaign['data']['engine_id'], $source);
                        $engine_product_ids = apply_filters('cuw_product_recommendations_product_ids_to_display', array_unique($engine_product_ids), $source, $use_products, $campaign);
                        $engine_product_ids = Cart::filterProducts($engine_product_ids, self::TYPE);
                        if (!empty($engine_product_ids) && !empty($order_products_ids)) {
                            $engine_product_ids = array_diff($engine_product_ids, $order_products_ids);
                        }
                    }
                }

                $product_ids = [];
                foreach ($engine_product_ids as $product_id) {
                    if (!empty($display_limit) && count($product_ids) >= $display_limit) {
                        break;
                    }

                    if (WC::isPurchasableProduct($product_id) && WC::isProductVisible($product_id)) {
                        $product_ids[] = $product_id;
                    }
                }

                if (!empty($classes) && isset($campaign['data']['template'])) {
                    $campaign['data']['template']['class'] = $classes;
                }

                if (!empty($product_ids)) {
                    $args = [
                        'product_ids' => $product_ids,
                        'campaign' => $campaign,
                    ];
                    echo apply_filters('cuw_product_recommendations_template_html', Template::getHtml($campaign, $args), $args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            }
        }
        return $html;
    }

    /**
     * To load extra query args.
     *
     * @hooked cuw_before_products_loop
     */
    public static function beforeProductsLoop($campaign_id, $campaign_type)
    {
        $GLOBALS['cuw_campaign_id'] = $campaign_id;
        $GLOBALS['cuw_campaign_type'] = $campaign_type;

        if (!empty($GLOBALS['cuw_current_page'])) {
            $page_data = Page::get($GLOBALS['cuw_current_page']);
            $GLOBALS['cuw_atc_redirect'] = !empty($page_data['atc_redirect']) ? $page_data['atc_redirect'] : '';
        }

        add_filter('woocommerce_loop_add_to_cart_args', [__CLASS__, 'addButtonAttributes'], 100);
        add_filter('woocommerce_product_add_to_cart_url', [__CLASS__, 'addQueryParams'], 100);
        add_filter('woocommerce_loop_product_link', [__CLASS__, 'addQueryParams'], 100);
    }

    /**
     * To remove extra query args from loop.
     *
     * @hooked cuw_after_products_loop
     */
    public static function afterProductsLoop()
    {
        remove_filter('woocommerce_loop_add_to_cart_args', [__CLASS__, 'addButtonAttributes'], 100);
        remove_filter('woocommerce_product_add_to_cart_url', [__CLASS__, 'addQueryParams'], 100);
        remove_filter('woocommerce_loop_product_link', [__CLASS__, 'addQueryParams'], 100);

        unset($GLOBALS['cuw_campaign_id'], $GLOBALS['cuw_campaign_type']);
    }

    /**
     * Add ATC button attributes.
     *
     * @param array $args
     * @return array
     */
    public static function addButtonAttributes($args)
    {
        $args['attributes'] = array_merge($args['attributes'], [
            'data-cuw_campaign_id' => $GLOBALS['cuw_campaign_id'],
            'data-cuw_campaign_type' => $GLOBALS['cuw_campaign_type'],
        ]);

        if (!empty($GLOBALS['cuw_atc_redirect']) && !empty($args['class'])) {
            $args['class'] = str_replace(' ajax_add_to_cart', '', $args['class']);
        }
        return $args;
    }

    /**
     * Add ATC url params.
     *
     * @param string $url
     * @return string
     */
    public static function addQueryParams($url)
    {
        $url = add_query_arg([
            'cuw_campaign_id' => $GLOBALS['cuw_campaign_id'],
            'cuw_campaign_type' => $GLOBALS['cuw_campaign_type'],
        ], $url);

        if (!empty($GLOBALS['cuw_atc_redirect']) && substr($url, 0, 1) == '?') {
            $url = $GLOBALS['cuw_atc_redirect'] . $url;
        }
        return $url;
    }

    /**
     * To load hidden inputs on single product page.
     *
     * @hooked woocommerce_after_add_to_cart_button
     */
    public static function maybeLoadCampaignInputs()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['cuw_campaign_type']) && $_GET['cuw_campaign_type'] === self::TYPE && WC::is('product')) {
            $campaign_type = self::app()->input->get('cuw_campaign_type');
            $campaign_id = self::app()->input->get('cuw_campaign_id');
            echo '<input type="hidden" name="cuw_campaign_id" value="' . esc_attr($campaign_id) . '">';
            echo '<input type="hidden" name="cuw_campaign_type" value="' . esc_attr($campaign_type) . '">';
        }
    }

    /**
     * Prepare cart item data.
     *
     * @hooked woocommerce_add_cart_item_data
     */
    public static function prepareCartItemData($cart_item_data, $product_id, $variation_id, $quantity)
    {
        if (!isset($cart_item_data['cuw_product'])) {
            $campaign_id = self::app()->input->get('cuw_campaign_id');
            if (!empty($campaign_id) && !empty($product_id)) {
                $campaign = CampaignModel::get($campaign_id);
                $product = WC::getProduct($product_id);
                if (!empty($campaign) && !empty($product) && $campaign['type'] == self::TYPE) {
                    $data = Product::prepareMetaData($campaign, $product, $quantity, $variation_id);
                    if (!empty($data)) {
                        $cart_item_data['cuw_product'] = $data;
                    }
                }
            }
        }
        return $cart_item_data;
    }

    /**
     * To get a matched campaign.
     */
    public static function getMatchedCampaigns($location)
    {
        if (!isset(self::$campaigns)) {
            self::$campaigns = [];
            $campaigns = CampaignModel::all([
                'status' => 'active',
                'type' => self::TYPE,
                'columns' => ['id', 'title', 'type', 'data'],
                'order_by' => 'priority',
                'sort' => 'asc',
            ]);

            if (!empty($campaigns) && is_array($campaigns)) {
                foreach ($campaigns as $campaign) {
                    $display_location = $campaign['data']['display_location'] ?? 'use_global_setting';
                    self::$campaigns[$display_location][] = $campaign;
                }
            }
        }
        return self::$campaigns[$location] ?? [];
    }

    /**
     * Delete engine if campaign not saved.
     */
    public static function deleteEngine($data)
    {
        if (!empty($data['data']['engine_id'])) {
            EngineModel::deleteById($data['data']['engine_id']);
        }
    }

    /**
     * Delete engine after campaign deleted.
     */
    public static function afterCampaignDeleted($campaign_id, $campaign)
    {
        if (!empty($campaign['type']) && $campaign['type'] == self::TYPE) {
            self::deleteEngine($campaign);
        }
    }

    /**
     * Duplicate engine before campaign duplicate.
     */
    public static function duplicateEngine($campaign)
    {
        if (!empty($campaign['type']) && $campaign['type'] == self::TYPE && !empty($campaign['data']['engine_id'])) {
            $result = EngineModel::duplicate($campaign['data']['engine_id']);
            if ($result && !empty($result['id'])) {
                $campaign['data']['engine_id'] = $result['id'];
            }
        }
        return $campaign;
    }

    /**
     * To add extra data to campaign.
     */
    public static function addCampaignPageData($data, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $data['views']['engine_filters'] = [
                'list' => self::app()->view('Pro/Admin/Engine/Filters/List', [], false),
                'wrapper' => self::app()->view('Pro/Admin/Engine/Filters/Wrapper', [], false),
            ];

            foreach (Engine::getFilters() as $key => $filter) {
                if (!empty($filter['handler']) && method_exists($filter['handler'], 'template')) {
                    $data['views']['engine_filters'][$key] = $filter['handler']->template();
                } else {
                    $data['views']['engine_filters'][$key] = false;
                }
            }

            $data['views']['engine_amplifiers'] = [
                'list' => self::app()->view('Pro/Admin/Engine/Amplifiers/List', [], false),
                'wrapper' => self::app()->view('Pro/Admin/Engine/Amplifiers/Wrapper', [], false),
            ];

            foreach (Engine::getAmplifiers() as $key => $amplifier) {
                if (!empty($amplifier['handler']) && method_exists($amplifier['handler'], 'template')) {
                    $data['views']['engine_amplifiers'][$key] = $amplifier['handler']->template();
                } else {
                    $data['views']['engine_amplifiers'][$key] = false;
                }
            }
            $data['i18n']['filter_required'] = esc_html__("Add any one filter", 'checkout-upsell-woocommerce');
        }
        return $data;
    }

    /**
     * Validate and save engine and returns formatted campaign data.
     *
     * @param array $data
     * @return array
     */
    public static function saveEngine($data)
    {
        if (!isset($data['type']) || $data['type'] !== self::TYPE) {
            return $data;
        }

        $engine_data = [
            'hidden' => 1,
            'enabled' => 1,
            'engine_filters' => $data['engine_filters'] ?? null,
            'engine_amplifiers' => $data['engine_amplifiers'] ?? null,
            'id' => $data['engine_id'] ?? null,
        ];

        if (!empty($data['data']['page'])) {
            $page = Page::get($data['data']['page']);
            if (!empty($page['engine_type'])) {
                $engine_data['type'] = $page['engine_type'];
            }
        }

        $errors = Validate::engine($engine_data);
        if (!empty($errors)) {
            $data['errors'] = $errors;
            return $data;
        }

        $result = EngineModel::save($engine_data);
        if ($result) {
            $data['data']['engine_id'] = $result['id'];
        } else {
            $data['errors'] = [];
        }
        return $data;
    }

    /**
     * Get display locations based on the page.
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        return apply_filters('cuw_product_recommendations_display_locations', Page::getLocations());
    }
}