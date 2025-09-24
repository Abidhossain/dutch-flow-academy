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

namespace CUW\App\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;

class Campaigns extends Controller
{
    /**
     * To init campaigns.
     */
    public static function init()
    {
        foreach (Campaign::get() as $campaign) {
            if (!empty($campaign['handler']) && is_a($campaign['handler'], '\CUW\App\Modules\Campaigns\Base')) {
                $campaign['handler']->init();
            }
        }
    }

    /**
     * To load campaign assets.
     *
     * @hooked wp
     */
    public static function loadAssets()
    {
        $script_data = apply_filters('cuw_frontend_script_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => WP::createNonce('cuw_ajax'),
            'is_cart' => function_exists('is_cart') && is_cart(),
            'is_checkout' => function_exists('is_checkout') && is_checkout(),
            'has_cart_block' => function_exists('has_block') && has_block('woocommerce/cart'),
            'has_checkout_block' => function_exists('has_block') && has_block('woocommerce/checkout'),
            'dynamic_offer_display_enabled' => (bool)self::app()->config->getSetting('dynamic_offer_display'),
        ]);
        self::app()->assets->addCss('template', 'template')->addJS('template', 'template', Template::getScriptData())
            ->addCss('frontend', 'frontend')->addJs('frontend', 'frontend', $script_data)
            ->enqueue('front', self::app()->assets->getFrontendEnqueuePriority());
    }

    /**
     * To change display item meta key to text.
     *
     * @hooked woocommerce_order_item_display_meta_key
     */
    public static function displayItemMetaKey($display_key)
    {
        if ($display_key == 'cuw_offer_text') {
            $display_key = __("Offer", 'checkout-upsell-woocommerce');
        } elseif ($display_key == 'cuw_discount_text') {
            $display_key = __("Discount", 'checkout-upsell-woocommerce');
        }
        return $display_key;
    }

    /**
     * To add tab on product data metabox.
     *
     * @hooked woocommerce_product_data_tabs
     */
    public static function addProductDataTab($tabs)
    {
        global $post;
        if (is_object($post) && isset($post->ID)) {
            if (apply_filters('cuw_show_upsell_products_data_tab', false, $post->ID)) {
                $label = __("Upsell Products", 'checkout-upsell-woocommerce');
                $label .= ' (' . __("UpsellWP", 'checkout-upsell-woocommerce') . ')';
                $tabs['cuw_upsells'] = [
                    'label' => $label,
                    'target' => 'cuw_upsells_product_data_tab',
                    'class' => [],
                    'priority' => 45,
                ];
            }
        }
        return $tabs;
    }

    /**
     * To show section on product data metabox.
     *
     * @hooked woocommerce_product_data_panels
     */
    public static function showProductDataPanel()
    {
        global $post;
        ?>
        <div id="cuw_upsells_product_data_tab" class="panel woocommerce_options_panel hidden">
            <?php do_action('cuw_upsells_product_data_panel', $post); ?>
        </div>
        <?php
    }

    /**
     * Add column to orders table.
     */
    public static function addOrdersTableColumn($columns)
    {
        $reordered_columns = [];
        foreach ($columns as $key => $column) {
            $reordered_columns[$key] = $column;
            if ($key == 'order_status') {
                $reordered_columns['cuw_upsell_info'] = __("Upsell Revenue", 'checkout-upsell-woocommerce');
            }
        }
        return $reordered_columns;
    }

    /**
     * To show upsell info on orders table.
     */
    public static function displayOrdersTableRow($column, $order_or_id)
    {
        if ($column == 'cuw_upsell_info') {
            $order = WC::getOrder($order_or_id);
            if (!empty($order)) {
                if ($order->get_meta('_has_cuw_coupons')) {
                    $message = WC::formatPrice($order->get_total()) . ' (100%)';
                } elseif ($order->get_meta('_has_cuw_offers') || $order->get_meta('_has_cuw_products')) {
                    $items_price_total = 0;
                    $upsell_items_price_total = 0;
                    foreach (WC::getOrderItems($order) as $item) {
                        if (method_exists($item, 'get_subtotal') && method_exists($item, 'get_subtotal_tax')) {
                            $price = $item->get_subtotal() + $item->get_subtotal_tax();
                            if ($item->get_meta('_cuw_offer') || $item->get_meta('_cuw_product')) {
                                $upsell_items_price_total += $price;
                            }
                            $items_price_total += $price;
                        }
                    }
                    if ($items_price_total > 0) {
                        $message = WC::formatPrice($upsell_items_price_total);
                        $message .= ' (' . round(($upsell_items_price_total / $items_price_total) * 100, 2) . '%)';
                    }
                }
                if (!empty($message)) {
                    echo wp_kses_post($message);
                } else {
                    echo '<span style="opacity: 0.8;">' . esc_html__("N/A", 'checkout-upsell-woocommerce') . '</span>';
                }
            }
        }
    }

    /**
     * Remove offer notices.
     *
     * @hooked wp_loaded
     */
    public static function removeOfferNotices()
    {
        if (!WP::isAjax()) {
            Offer::removeNotices();
        }
    }

    /**
     * To exclude discounted upsell products from applying coupon discounts.
     *
     * @hooked woocommerce_coupon_get_items_to_validate
     */
    public static function excludeProductFromDiscounts($items)
    {
        foreach ($items as $key => $item) {
            if (isset($item->object['cuw_offer']['discount']['type']) && $item->object['cuw_offer']['discount']['type'] != 'no_discount') {
                unset($items[$key]);
            } elseif (isset($item->object['cuw_product']['discount']['type']) && $item->object['cuw_product']['discount']['type'] != 'no_discount') {
                unset($items[$key]);
            }
        }
        return $items;
    }
}