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

namespace CUW\App\Pro\Modules\Campaigns;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Assets;
use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\Filter;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Helpers\Product;

class UpsellPopups extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'upsell_popups';

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
            // on campaign page
            add_action('cuw_campaign_contents', [__CLASS__, 'campaignEditView'], 10, 2);
        } else {
            if (self::isEnabled()) {
                add_action('wp_footer', [__CLASS__, 'maybeTriggerEvent'], Assets::getFrontendEnqueuePriority());
                add_action('wp_footer', [__CLASS__, 'loadModalAndListener'], Assets::getFrontendEnqueuePriority());
            }
        }

        if (self::isEnabled()) {
            add_filter('cuw_filter_cart_products', [__CLASS__, 'filterCartProducts'], 10, 3);

            // to load popup in cart page when Redirect to the cart page option is enabled in woocommerce settings
            if ('yes' === get_option('woocommerce_cart_redirect_after_add', 'no')) {
                add_filter('woocommerce_add_to_cart_redirect', [__CLASS__, 'addShowPopupQueryArgs'], 1000, 2);
                add_action('wp_loaded', [__CLASS__, 'removeShowPopupQueryArgs']);
                add_action('wp_footer', [__CLASS__, 'loadPopupInCartPage']);
            }
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
            self::app()->view('Pro/Admin/Campaign/UpsellPopups', [
                'action' => 'campaign_edit',
                'campaign' => $campaign,
            ]);
        }
    }

    /**
     * Maybe trigger event.
     */
    public static function maybeTriggerEvent()
    {
        if (!empty($_REQUEST['add-to-cart']) && did_action('woocommerce_add_to_cart')) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ?>
            <script>
                jQuery(document).ready(function () {
                    jQuery(document.body).trigger('added_to_cart', {});
                });
            </script>
            <?php
        }
    }

    /**
     * To load modal html and listener script.
     */
    public static function loadModalAndListener()
    {
        global $post;
        $current_product_id = WC::is('product') && !empty($post->ID) ? $post->ID : null;

        foreach (self::getTriggers() as $trigger => $data) {
            foreach ($data['pages'] as $page) {
                $is_custom_page = apply_filters("cuw_is_{$page}_page", false);
                if (WC::is($page, true) || $is_custom_page) {
                    $data['key'] = $trigger;
                    $view_data = [
                        'action' => 'load_popup_script',
                        'trigger' => $data,
                        'page' => $page,
                    ];
                    if (empty($data['dynamic_content'])) {
                        $campaign = self::getCampaignToDisplay($trigger, $current_product_id);
                        if (empty($campaign)) {
                            break;
                        }

                        $view_data['campaign'] = $campaign;
                        $modal_html = self::getProductsHtml($campaign, $trigger, $page, $current_product_id);
                        if (empty($modal_html)) {
                            break;
                        }
                        echo $modal_html; // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    self::app()->view('Pro/Store/Footer', $view_data);
                    break;
                }
            }
        }
    }

    /**
     * Get products html.
     *
     * @param array $campaign
     * @param string|array $trigger
     * @param string $page
     * @param int $current_product_id
     * @return string
     */
    public static function getProductsHtml($campaign, $trigger, $page = 'shop', $current_product_id = null)
    {
        if (!empty($campaign)) {
            $ids = [];
            $products = [];
            $products_data = Campaign::getProductsData($campaign);
            $trigger_options = $campaign['data']['trigger_options'][$trigger] ?? [];
            $use_products = !empty($products_data['use']) ? $products_data['use'] : '';
            if ($use_products == 'specific') {
                $ids = !empty($products_data['ids']) ? $products_data['ids'] : [];
            } else if ($trigger == 'added_to_cart' && ($trigger_options['suggestion_method'] ?? '') == 'current_product') {
                if (!empty($current_product_id) && is_numeric($current_product_id)) {
                    $ids = Product::getIds($current_product_id, $use_products);
                }
            } else {
                $cart_data = Cart::getData(['include_applied_offers' => true, 'with_product_object' => true], false);
                if (!empty($cart_data)) {
                    foreach ($cart_data['products'] as $product) {
                        if (!empty($product['object'])) {
                            $ids = array_merge($ids, Product::getIds($product['object'], $use_products));
                        }
                    }
                }
            }

            $product_ids = (array)apply_filters('cuw_upsell_popup_product_ids_to_display', array_unique($ids), WC::getCart(), $use_products, $campaign);
            $product_ids = Cart::filterProducts($product_ids, self::TYPE);
            if (empty($product_ids)) {
                return '';
            }

            $discount = isset($campaign['data']['discount']) ? $campaign['data']['discount'] : [];
            $display_limit = (int)self::app()->config->getSetting('upsell_popup_products_display_limit');
            foreach ($product_ids as $product_id) {
                if (count($products) >= $display_limit) {
                    break;
                }
                $product_data = Product::getData($product_id, [
                    'discount' => $discount,
                    'to_display' => true,
                    'display_in' => WC::getDisplayTaxSettingByPage($page),
                    'include_variants' => true,
                    'filter_purchasable' => true,
                ]);
                if (!empty($product_data)) {
                    $products[] = $product_data;
                }
            }

            if (!empty($products)) {
                if (is_string($trigger)) {
                    $trigger = self::getTriggerData($trigger);
                }
                $args = [
                    'products' => $products,
                    'campaign' => $campaign,
                    'trigger' => $trigger,
                    'page' => $page,
                    'cart_subtotal' => WC::formatPrice(WC::getCartSubtotal(WC::getDisplayTaxSettingByPage($page))),
                ];

                return apply_filters('cuw_upsell_popup_template_html', Template::getHtml($campaign, $args), $args);
            }
        }
        return '';
    }

    /**
     * Get campaign to display.
     *
     * @return array
     */
    public static function getCampaignToDisplay($trigger, $product_id = null, $cache = true)
    {
        if (!isset(self::$campaigns) || !$cache) {
            self::$campaigns = [];
            $available_triggers = self::getTriggers();

            $campaigns = CampaignModel::all([
                'status' => 'active',
                'type' => self::TYPE,
                'columns' => ['id', 'type', 'conditions', 'data'],
                'order_by' => 'priority',
                'sort' => 'asc',
            ]);

            if (!empty($campaigns) && is_array($campaigns)) {
                foreach ($campaigns as $campaign) {
                    $triggers = $campaign['data']['triggers'] ?? [];
                    $trigger_filter = $campaign['data']['trigger_options'][$trigger]['filter'] ?? [];

                    foreach ($triggers as $popup_trigger) {
                        // skip campaign if the trigger is already loaded
                        if (isset(self::$campaigns[$popup_trigger])) {
                            continue;
                        }

                        // get cart data excluding current campaign products
                        $cart_data = Cart::getData(['exclude_campaign_id' => $campaign['id']]);

                        // check trigger extra configurations
                        if ($trigger == 'added_to_cart' && !empty($product_id) && is_numeric($product_id)) {
                            $product_data = Product::getData($product_id);
                            if (!empty($product_data) && !empty($trigger_filter)) {
                                if (!Filter::check($trigger_filter, $product_data)) {
                                    continue;
                                }
                            }
                        }

                        // check conditions
                        if (!Campaign::isConditionsPassed($campaign['conditions'], $cart_data)) {
                            continue;
                        }

                        self::$campaigns[$popup_trigger] = $campaign;

                        if (isset($available_triggers[$popup_trigger])) {
                            unset($available_triggers[$popup_trigger]);
                            if (empty($available_triggers)) {
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        return self::$campaigns[$trigger] ?? [];
    }

    /**
     * Filter cart product IDs.
     *
     * @return array
     */
    public static function filterCartProducts($product_ids, $original_product_ids, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $product_ids = array_diff($product_ids, Cart::getProductIds());
        }
        return $product_ids;
    }

    /**
     * Get triggers.
     *
     * @return array
     */
    public static function getTriggers()
    {
        return (array)apply_filters('cuw_upsell_popup_triggers', [
            'added_to_cart' => [
                'title' => __("After product added to cart", 'checkout-upsell-woocommerce'),
                'description' => __('Popup shows after a product is added to the cart on shop / category / product pages.', 'checkout-upsell-woocommerce'),
                'target' => '',
                'event' => 'added_to_cart',
                'pages' => ['woocommerce', 'home'],
                'show_once' => false,
                'dynamic_content' => true,
                'popup_actions' => [
                    'view_cart' => [
                        'text' => __("View cart", 'checkout-upsell-woocommerce'),
                        'url' => wc_get_cart_url(),
                    ],
                ],
            ],
            'proceed_to_checkout' => [
                'title' => __('Click "Proceed to checkout" button', 'checkout-upsell-woocommerce'),
                'description' => __('Shows when the "Proceed to Checkout" button is clicked.', 'checkout-upsell-woocommerce'),
                'target' => '.checkout-button, .wc-block-cart__submit-button',
                'event' => 'click',
                'pages' => ['cart'],
                'show_once' => true,
                'dynamic_content' => false,
                'popup_actions' => [
                    'proceed_to_checkout' => [
                        'text' => __("Proceed to checkout", 'checkout-upsell-woocommerce'),
                        'url' => wc_get_checkout_url(),
                    ],
                ],
            ],
            //'place_order' => [
            //    'title' => __('Click "Place order" button', 'checkout-upsell-woocommerce'),
            //    'description' => __('Shows when the "Place order" button is clicked.', 'checkout-upsell-woocommerce')
            //        . '<br><span class="text-dark">' . __('NOTE: This trigger does not work on the WooCommerce Checkout block.', 'checkout-upsell-woocommerce') . '</span>',
            //    'target' => '#place_order',
            //    'event' => 'click',
            //    'pages' => ['checkout'],
            //    'show_once' => true,
            //    'dynamic_content' => false,
            //    'popup_actions' => [
            //        'place_order' => [
            //            'text' => __("Place order", 'checkout-upsell-woocommerce'),
            //        ],
            //    ],
            //],
        ]);
    }

    /**
     * To get trigger data.
     *
     * @param string $key
     * @return array
     */
    public static function getTriggerData($key)
    {
        $triggers = self::getTriggers();
        return $triggers[$key] ?? [];
    }

    /**
     * Add query arguments when Redirect to the cart page option is enabled in woocommerce settings.
     *
     * @param string $url
     * @return string
     */
    public static function addShowPopupQueryArgs($url, $product)
    {
        $query_args = ['cuw_show_upsell_popup' => 1];
        if (!empty($product) && is_object($product)) {
            $query_args['cuw_product_id'] = $product->get_id();
        }
        return add_query_arg($query_args, empty($url) ? wc_get_cart_url() : $url);
    }


    /**
     * To load popup in cart page when Redirect to the cart page option is enabled in woocommerce settings.
     *
     * @return void
     */
    public static function loadPopupInCartPage()
    {
        $session_data = WC::getSession('cuw_show_upsell_popup');
        if (WC::is('cart') && !empty($session_data)) {
            WC::setSession('cuw_show_upsell_popup', null);
            $product_id = $session_data['product_id'] ?? Cart::getRecentlyAddedProductId();
            $campaign = self::getCampaignToDisplay('added_to_cart', $product_id, false);
            $html = self::getProductsHtml($campaign, 'added_to_cart', 'cart', $product_id);
            if (!empty($html)) { ?>
                <div id="cuw-upsell-popup-wrapper">
                    <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <script>
                    jQuery(document).ready(function () {
                        cuw_modal.show('#cuw-upsell-popup-wrapper .cuw-modal');
                    });
                </script><?php
            }
        }
    }

    /**
     * To redirect cart page after set the session.
     *
     * @return void
     */
    public static function removeShowPopupQueryArgs()
    {
        if (!empty($_GET['cuw_show_upsell_popup'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            WC::setSession('cuw_show_upsell_popup', [
                'product_id' => self::app()->input->get('cuw_product_id', null),
            ]);
            wp_safe_redirect(remove_query_arg(['cuw_show_upsell_popup', 'cuw_product_id']));
            exit;
        }
    }
}