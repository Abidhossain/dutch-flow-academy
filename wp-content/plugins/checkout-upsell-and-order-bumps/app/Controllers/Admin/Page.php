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

namespace CUW\App\Controllers\Admin;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Assets;
use CUW\App\Helpers\Condition;
use CUW\App\Helpers\Filter;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WP;
use CUW\App\Setup;

class Page extends Controller
{
    /**
     * Add Admin Menu
     *
     * @hooked admin_menu
     */
    public static function addMenu()
    {
        $tabs = self::getTabs();
        $page_slug = self::app()->plugin->slug;
        $page_title = esc_html__("UpsellWP", 'checkout-upsell-and-order-bumps');

        add_menu_page(
            $page_title,
            $page_title,
            'manage_woocommerce',
            $page_slug,
            [__CLASS__, 'show'],
            'dashicons-cart',
            56
        );

        foreach ($tabs as $slug => $title) {
            add_submenu_page(
                $page_slug,
                $page_title . ' – ' . $title,
                $title,
                'manage_woocommerce',
                $page_slug . '&tab=' . $slug,
                [__CLASS__, 'show']
            );
        }
    }

    /**
     * Get page url
     *
     * @return string
     */
    public static function getUrl($params = [], $update = false)
    {
        $page_args = ['page' => self::app()->plugin->slug];
        if (empty($params) || !is_array($params)) {
            return sanitize_url('admin.php?' . http_build_query($page_args));
        }
        $params = array_merge($params, $page_args);
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return sanitize_url('admin.php?' . http_build_query($update ? array_merge($_GET, $params) : $params));
    }

    /**
     * Change page (plugin) slug
     */
    private static function updatePageSlug($tab)
    {
        global $plugin_page;
        $plugin_page .= '&tab=' . (!empty($tab) ? $tab : self::getDefaultTab());
    }

    /**
     * Remove all Admin notices
     */
    private static function removeNotices()
    {
        remove_all_filters('admin_notices');
    }

    /**
     * Remove third party assets.
     */
    private static function removeThirdPartyAssets()
    {
        add_action('admin_enqueue_scripts', function () {
            $assets_to_remove = (array)apply_filters('cuw_page_removable_third_party_assets', ['bootstrap', 'select2', 'selectWoo']);
            if (!empty($assets_to_remove)) {
                if (function_exists('wp_scripts') && isset(wp_scripts()->registered)) {
                    foreach (array_keys(wp_scripts()->registered) as $script) {
                        if (is_string($script) && strpos($script, 'cuw_') === false) {
                            foreach ($assets_to_remove as $asset) {
                                if (strpos($script, $asset) !== false) {
                                    wp_deregister_script($script);
                                }
                            }
                        }
                    }
                }
                if (function_exists('wp_styles') && isset(wp_styles()->registered)) {
                    foreach (array_keys(wp_styles()->registered) as $style) {
                        if (is_string($style) && strpos($style, 'cuw_') === false) {
                            foreach ($assets_to_remove as $asset) {
                                if (strpos($style, $asset) !== false) {
                                    wp_deregister_style($style);
                                }
                            }
                        }
                    }
                }
            }
        }, 10000);
    }

    /**
     * To initialize page and load its assets
     *
     * @hooked admin_init
     */
    public static function init()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['page']) && $_GET['page'] == self::app()->plugin->slug) {
            $tab = self::getCurrentTab();
            self::updatePageSlug($tab);

            $campaign_type = self::app()->input->get('type', '', 'query');
            if ($campaign_type == 'post_purchase') {
                Setup::addOfferPage();
            }

            self::removeNotices();
            self::removeThirdPartyAssets();
            $url = self::getUrl();
            $data = [
                'is_rtl' => WP::isRtl(),
                'has_pro' => self::app()->plugin->has_pro,
                'page_url' => admin_url($url),
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => WP::createNonce('cuw_ajax'),
            ];

            if ($tab == 'campaigns' || $tab == 'engines') {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                if (isset($_GET['create']) || isset($_GET['edit'])) {
                    $campaign_id = self::app()->input->get('edit', '0', 'query');
                    if (empty($campaign_type) && is_numeric($campaign_id)) {
                        $campaign_type = \CUW\App\Helpers\Campaign::getType($campaign_id);
                    }

                    wp_enqueue_media();

                    self::app()->assets->addCss('select', 'select2')->addJs('select', 'select2')
                        ->addCss('template', 'template')->addJS('template', 'template', Template::getScriptData());

                    $data['views']['offer'] = [
                        'content' => self::app()->view('Admin/Campaign/Offer/Content', ['campaign_type' => $campaign_type], false),
                    ];

                    $data['views']['filters'] = [
                        'list' => self::app()->view('Admin/Campaign/Filters/List', [], false),
                        'wrapper' => self::app()->view('Admin/Campaign/Filters/Wrapper', [], false),
                    ];
                    foreach (Filter::get($campaign_type) as $key => $filter) {
                        if (!empty($filter['handler']) && method_exists($filter['handler'], 'template')) {
                            $data['views']['filters'][$key] = $filter['handler']->template();
                        } else {
                            $data['views']['filters'][$key] = false;
                        }
                    }

                    $data['views']['conditions'] = [
                        'list' => self::app()->view('Admin/Campaign/Conditions/List', [], false),
                        'wrapper' => self::app()->view('Admin/Campaign/Conditions/Wrapper', [], false),
                    ];
                    foreach (Condition::get($campaign_type) as $key => $condition) {
                        if (!empty($condition['handler']) && method_exists($condition['handler'], 'template')) {
                            $data['views']['conditions'][$key] = $condition['handler']->template();
                        } else {
                            $data['views']['conditions'][$key] = false;
                        }
                    }

                    $data['data']['offer']['max_limit'] = Offer::getMaxLimit($campaign_type);

                    $data['data']['templates'] = Template::get($campaign_type);
                    $data['data']['default_template'] = Template::getDefault($campaign_type);

                    $data['i18n'] = [
                        'offer_not_saved' => esc_html__("Offer not saved", 'checkout-upsell-and-order-bumps'),
                        'campaign_not_saved' => esc_html__("Campaign not saved", 'checkout-upsell-and-order-bumps'),
                        'this_field_is_required' => esc_html__("This field is required", 'checkout-upsell-and-order-bumps'),
                        'at_least_one_offer_required' => esc_html__("At least one offer is required", 'checkout-upsell-and-order-bumps'),
                        // translators: %s value.
                        'offer_max_limit' => esc_html__("Maximum: %s", 'checkout-upsell-and-order-bumps'),
                        'offer_max_limit_reached' => esc_html__("Maximum offer limit is reached", 'checkout-upsell-and-order-bumps'),
                        'offer_unable_to_remove' => esc_html__("Unable to remove offer (At least one offer required)", 'checkout-upsell-and-order-bumps'),
                        'offer_ab_unable_to_add' => esc_html__("Unable to add offer (A/B Testing Enabled)", 'checkout-upsell-and-order-bumps'),
                        'offer_ab_unable_to_remove' => esc_html__("Unable to remove offer (A/B Testing Enabled)", 'checkout-upsell-and-order-bumps'),
                        'offer_ab_requires_two_offers' => esc_html__("A/B Testing requires two offers", 'checkout-upsell-and-order-bumps'),
                        'offer_ab_requires_exactly_two_offers' => esc_html__("A/B Testing requires exactly two offers", 'checkout-upsell-and-order-bumps'),
                        'customize_template' => esc_html__("Customize template", 'checkout-upsell-and-order-bumps'),
                        'customize_change_template' => esc_html__("Change template", 'checkout-upsell-and-order-bumps'),
                        'select2_no_results' => esc_html__("No results", 'checkout-upsell-and-order-bumps'),
                        'select2_error_loading' => esc_html__("Unable to search results", 'checkout-upsell-and-order-bumps'),
                        'condition_text' => esc_html__("Condition", 'checkout-upsell-and-order-bumps'),
                        'filter_text' => esc_html__("Filter", 'checkout-upsell-and-order-bumps'),
                    ];

                    if ($tab == 'engines') {
                        $data = apply_filters('cuw_page_localize_engine_data', $data);
                    } else {
                        $data = apply_filters('cuw_page_localize_campaign_data', $data, $campaign_type);

                        do_action('cuw_before_campaign_page_load', $campaign_type, $campaign_id);
                    }
                } else {
                    $data['i18n']['campaign_max_limit_reached'] = esc_html__("Maximum campaign limit reached", 'checkout-upsell-and-order-bumps');
                }
            } elseif ($tab == 'reports' || $tab == 'dashboard') {
                $data['i18n']['since_last_week'] = esc_html__("Since last week", 'checkout-upsell-and-order-bumps');
                $data['i18n']['since_last_month'] = esc_html__("Since last month", 'checkout-upsell-and-order-bumps');
                $data['i18n']['since_previous_30_days'] = esc_html__("Since previous 30 days", 'checkout-upsell-and-order-bumps');
                $data['i18n']['since_previous_7_days'] = esc_html__("Since previous 7 days", 'checkout-upsell-and-order-bumps');

                $data['i18n']['revenue'] = esc_html__("Revenue", 'checkout-upsell-and-order-bumps');
                $data['i18n']['items'] = esc_html__("Items", 'checkout-upsell-and-order-bumps');
                $data['i18n']['products_purchased'] = esc_html__("Products Purchased", 'checkout-upsell-and-order-bumps');
                $data['i18n']['no_data_found'] = esc_html__("No data found", 'checkout-upsell-and-order-bumps');

                self::app()->assets->addJs('chart', 'chart');
            } else if ($tab == 'addons') {
                $data['i18n']['addon_activated'] = esc_html__("Add-on activated", 'checkout-upsell-and-order-bumps');
                $data['i18n']['addon_deactivated'] = esc_html__("Add-on deactivated", 'checkout-upsell-and-order-bumps');
                $data['i18n']['addon_activation_failed'] = esc_html__("Add-on activation failed", 'checkout-upsell-and-order-bumps');
                $data['i18n']['addon_deactivation_failed'] = esc_html__("Add-on deactivation failed", 'checkout-upsell-and-order-bumps');
            }

            $data['i18n']['save'] = esc_html__("Save", 'checkout-upsell-and-order-bumps');
            $data['i18n']['error'] = esc_html__("Something went wrong!", 'checkout-upsell-and-order-bumps');
            $data['i18n']['campaign'] = esc_html__("Campaign", 'checkout-upsell-and-order-bumps');
            $data['i18n']['offer'] = esc_html__("Offer", 'checkout-upsell-and-order-bumps');
            $data['i18n']['copied'] = esc_html__("Copied", 'checkout-upsell-and-order-bumps');

            self::app()->assets->addCss('admin', 'admin')->addJs('admin', 'admin', $data)
                ->addCss('bootstrap', 'bootstrap')->addJs('bootstrap', 'bootstrap')
                ->addCss('icons', 'icons')
                ->enqueue('admin');
        }
    }

    /**
     * To include styles or scripts in page (html) head
     *
     * @hooked admin_head
     */
    public static function head()
    {
        ?>
        <style>
            #toplevel_page_<?php echo esc_attr(self::app()->plugin->slug); ?> .wp-first-item {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Show page
     *
     * @return void
     */
    public static function show()
    {
        self::app()->view('Admin/Page', ['page' => new self()]);
    }

    /**
     * Menu tabs
     *
     * @return array
     */
    public static function getTabs()
    {
        return apply_filters('cuw_page_tabs', [
            'dashboard' => __("Dashboard", 'checkout-upsell-and-order-bumps'),
            'campaigns' => __("Campaigns", 'checkout-upsell-and-order-bumps'),
            'engines' => __("Engines", 'checkout-upsell-and-order-bumps'),
            'reports' => __("Reports", 'checkout-upsell-and-order-bumps'),
            'settings' => __("Settings", 'checkout-upsell-and-order-bumps'),
            'addons' => __("Add-ons", 'checkout-upsell-and-order-bumps'),
            'recommendations' => __("Recommendations", 'checkout-upsell-and-order-bumps'),
            'buy-pro' => __("Buy PRO", 'checkout-upsell-and-order-bumps'),
        ]);
    }

    /**
     * Get current tab
     *
     * @return string
     */
    public static function getCurrentTab()
    {
        $tabs = self::getTabs();
        $default_tab = self::getDefaultTab();
        if ($current_tab = self::app()->input->get('tab', '', 'query')) {
            if (array_key_exists($current_tab, $tabs)) {
                return $current_tab;
            }
        }
        return $default_tab;
    }

    /**
     * Get default tab
     *
     * @return string
     */
    public static function getDefaultTab()
    {
        return apply_filters('cuw_page_default_tab', 'dashboard');
    }

    /**
     * Get Recommendations list
     *
     * @return array
     *
     */
    public static function getRecommendations()
    {
        $recommendation_list_url = 'https://static.flycart.net/recommendation/product/upsellwp.json';

        $recommendations_list = get_transient('cuw_recommendations_list');
        if (empty($recommendations_list)) {
            $response = wp_remote_get($recommendation_list_url);
            if (!is_wp_error($response)) {
                $recommendations_list = (array)json_decode(wp_remote_retrieve_body($response), true);
                $site_name = !empty($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
                foreach ($recommendations_list as &$recommendation) {
                    $recommendation['plugin_url'] = str_replace('{site-name}', $site_name, $recommendation['plugin_url']);
                }
                set_transient('cuw_recommendations_list', $recommendations_list, 24 * 60 * 60);
            }
        }
        return $recommendations_list;
    }

    /**
     * Add links on plugins page.
     */
    public static function pluginLinks($links)
    {
        $links = array_merge([
            'campaigns' => '<a href="' . esc_url(self::getUrl(['tab' => 'campaigns'])) . '">' . esc_html__("Campaigns", 'checkout-upsell-and-order-bumps') . '</a>',
            'settings' => '<a href="' . esc_url(self::getUrl(['tab' => 'settings'])) . '">' . esc_html__("Settings", 'checkout-upsell-and-order-bumps') . '</a>',
        ], $links);
        if (!self::app()->plugin->has_pro) {
            $links['get_pro'] = '<a style="font-weight: bold; color: #16a34a;" href="' . esc_url(self::app()->plugin->getUrl('upgrade')) . '" target="_blank">' . esc_html__("Get PRO", 'checkout-upsell-and-order-bumps') . '</a>';
        }
        return $links;
    }

    /**
     * Default page query args
     *
     * @return array
     */
    public static function defaultQueryArgs()
    {
        return apply_filters('cuw_page_default_query_args', [
            'type' => '',
            'page_no' => 1,
            'status' => '',
            'search' => '',
            'order_by' => 'id',
            'sort' => 'desc',
        ]);
    }
}
