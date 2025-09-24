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
use CUW\App\Helpers\Config;
use CUW\App\Helpers\Input;
use CUW\App\Helpers\WC;
use CUW\App\Pro\Helpers\Engine;
use CUW\App\Pro\Models\Engine as EngineModel;
use CUW\App\Pro\Models\CampaignEngine;

class Engines extends Controller
{

    /**
     * To init engines.
     */
    public static function init()
    {
        add_filter('cuw_fbt_product_ids_to_display', [__CLASS__, 'productIdsToDisplay'], 10, 4);
        add_filter('cuw_upsell_popup_product_ids_to_display', [__CLASS__, 'productIdsToDisplay'], 10, 4);
        add_filter('cuw_thankyou_upsell_product_ids_to_display', [__CLASS__, 'productIdsToDisplay'], 10, 4);
        add_filter('cuw_product_addon_product_ids_to_display', [__CLASS__, 'productIdsToDisplay'], 10, 4);
        add_filter('cuw_cart_addon_product_ids_to_display', [__CLASS__, 'productIdsToDisplay'], 10, 4);
        add_filter('cuw_get_engine', [__CLASS__, 'getEngine'], 10, 3);
    }

    /**
     * Load engine product ids.
     *
     * @param array $ids
     * @param array $source
     * @param string $use_products
     * @param array $campaign
     *
     * @return array
     */
    public static function productIdsToDisplay($ids, $source, $use_products, $campaign)
    {
        if ($use_products == 'engine' && isset($campaign['data']['products']['engine_id'])) {
            $ids = Engine::getProductIds($campaign['data']['products']['engine_id'], $source);
        }
        return $ids;
    }

    /**
     * Localized engine data
     *
     * @hooked cuw_page_localize_engine_data
     *
     * @param $data
     * @return array
     */
    public static function localizeEngineData($data)
    {
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

        $data['i18n']['engine_not_saved'] = esc_html__("Engine not saved", 'checkout-upsell-woocommerce');
        $data['i18n']['filter_required'] = esc_html__("Add any one filter", 'checkout-upsell-woocommerce');
        $data['i18n']['amplifier_required'] = esc_html__("Add any one amplifier", 'checkout-upsell-woocommerce');

        return $data;
    }

    /**
     * Link engine with campaign after save campaign.
     *
     * @param int $id
     * @param array $campaign
     * @return void
     */
    public static function linkEngine($id, $campaign)
    {
        if (isset($campaign['data']['products']['use']) && $campaign['data']['products']['use'] == 'engine') {
            CampaignEngine::linkEngine($id, $campaign['data']['products']['engine_id']);
        } elseif (!empty($campaign['data']['engine_id'])) {
            CampaignEngine::linkEngine($id, $campaign['data']['engine_id']);
        } else {
            CampaignEngine::unLinkEngine($id);
        }
    }

    /**
     * Delete engine entry while deleting campaign.
     *
     * @param int $campaign_id
     * @return void
     */
    public static function unLinkEngine($campaign_id)
    {
        CampaignEngine::unLinkEngine($campaign_id);
    }

    /**
     * Get engine.
     *
     * @param array|false $engine
     * @param integer $id
     * @param array $columns
     *
     * @return array|false
     */
    public static function getEngine($engine, $id, $columns)
    {
        if (!empty($id)) {
            $engine = EngineModel::get($id, $columns);
        }
        return $engine;
    }

    /**
     * To load engine settings.
     *
     * @hooked cuw_default_settings
     */
    public static function loadDefaultSettings($settings)
    {
        return array_merge($settings, [
            'engine_products_fetch_limit' => 20,
            'engine_cache_enabled' => 1,
            'engine_cache_expiration' => 24,
        ]);
    }

    /**
     * To set recently viewed product ids in cookie.
     *
     * @return void
     */
    public static function setRecentlyViewedProductsCookie()
    {
        if (WC::is('product') && $product = WC::getProduct()) {
            if (!WC::isPurchasableProduct($product) || empty(EngineModel::getActiveEnginesCount())) {
                return;
            }

            $already_viewed_products = [];
            $viewed_products_in_cookie = Input::get('cuw_recently_viewed_products', '', 'cookie');
            if (!empty($viewed_products_in_cookie) && is_string($viewed_products_in_cookie)) {
                $already_viewed_products = array_unique(explode('|', $viewed_products_in_cookie));
            }

            $product_id = $product->get_id();
            if (in_array($product_id, $already_viewed_products)) {
                unset($already_viewed_products[array_search($product_id, $already_viewed_products)]);
            }

            $recently_viewed_products = array_merge([(string)$product_id], $already_viewed_products);

            $limit = Config::getSetting('engine_products_fetch_limit');
            if (count($recently_viewed_products) > $limit) {
                $recently_viewed_products = array_slice($recently_viewed_products, 0, $limit);
            }
            ?>
            <script>
                let cookie_value = '<?php echo esc_js(implode('|', $recently_viewed_products)); ?>';
                let cookie_expires = new Date();
                cookie_expires.setTime(cookie_expires.getTime() + (2 * 24 * 60 * 60 * 1000));
                document.cookie = 'cuw_recently_viewed_products=' + cookie_value + '; expires=' + cookie_expires.toUTCString() + '; path=/';
            </script>
            <?php
        }
    }
}