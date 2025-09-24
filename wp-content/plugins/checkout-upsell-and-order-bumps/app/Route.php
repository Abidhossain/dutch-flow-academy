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

namespace CUW\App;

use CUW\App\Controllers\Admin\Ajax;
use CUW\App\Controllers\Admin\Notice;
use CUW\App\Controllers\Admin\Page;
use CUW\App\Controllers\Common\AddOns;
use CUW\App\Controllers\Common\Campaigns;
use CUW\App\Controllers\Common\Cron;
use CUW\App\Controllers\Common\Events;
use CUW\App\Controllers\Common\Shortcodes;
use CUW\App\Controllers\Store\Blocks;
use CUW\App\Controllers\Store\Cart;
use CUW\App\Controllers\Store\Checkout;
use CUW\App\Helpers\Compatibility;
use CUW\App\Helpers\Config;
use CUW\App\Helpers\WP;
use CUW\App\Models\Stats;

defined('ABSPATH') || exit;

class Route
{
    /**
     * To add hooks
     */
    public static function init()
    {
        self::addGeneralHooks();
        if (WP::isAdmin() && !WP::isAjax()) {
            self::addAdminHooks();
        } else {
            self::addStoreHooks();
        }
		add_action('woocommerce_init',function(){
			Events::add();
			Campaigns::init();
			Shortcodes::add();
			Blocks::register();
			Compatibility::init();
			Cron::handleEvents();
		});

    }

    /**
     * To add general hooks.
     */
    private static function addGeneralHooks()
    {
        // ajax request handlers
        add_action('wp_ajax_cuw_ajax', [Ajax::class, 'handleAuthRequests']);
        add_action('wp_ajax_nopriv_cuw_ajax', [Ajax::class, 'handleGuestRequests']);

        // to change order item display meta key to text
        add_filter('woocommerce_order_item_display_meta_key', [Campaigns::class, 'displayItemMetaKey']);
        add_action('woocommerce_order_status_changed', [Stats::class, 'updateOrderStatus'], 10, 3);
    }

    /**
     * To add admin area hooks.
     */
    private static function addAdminHooks()
    {
        // general admin hooks
        add_action('admin_init', [Page::class, 'init']);
        add_action('admin_init', [AddOns::class, 'handleActions']);
        add_action('admin_head', [Page::class, 'head']);
        add_action('admin_menu', [Page::class, 'addMenu']);

        // to show review notice
        add_action('admin_notices', [Notice::class, 'showReviewNotice']);
        add_action('admin_init', [Notice::class, 'handleReviewNoticeActions']);

        // to show upsell products data tab
        add_filter('woocommerce_product_data_tabs', [Campaigns::class, 'addProductDataTab']);
        add_action('woocommerce_product_data_panels', [Campaigns::class, 'showProductDataPanel']);

        // to show upsell revenue on orders table
        add_filter('manage_edit-shop_order_columns', [Campaigns::class, 'addOrdersTableColumn'], 100);
        add_action('manage_shop_order_posts_custom_column', [Campaigns::class, 'displayOrdersTableRow'], 20, 2);
        add_filter('woocommerce_shop_order_list_table_columns', [Campaigns::class, 'addOrdersTableColumn'], 100);
        add_action('woocommerce_shop_order_list_table_custom_column', [Campaigns::class, 'displayOrdersTableRow'], 20, 2);

        // to add plugin page links
        add_filter('plugin_action_links_' . plugin_basename(CUW_PLUGIN_FILE), [Page::class, 'pluginLinks']);

        // to load email templates
        add_filter('woocommerce_email_classes', [Cron::class, 'loadEmailTemplates']);
    }

    /**
     * To add store (front-end) hooks.
     */
    private static function addStoreHooks()
    {
        if (!WP::isAjax()) {
            // to load campaign assets
            add_action('wp', [Campaigns::class, 'loadAssets'], 100);

            // to clear offer notices from session
            add_action('wp_loaded', [Campaigns::class, 'removeOfferNotices']);
        }

        // to exclude offers from applying coupon discounts
        if (!empty(Config::getSetting('exclude_coupon_discounts'))) {
            add_filter('woocommerce_coupon_get_items_to_validate', [Campaigns::class, 'excludeProductFromDiscounts'], 100);
        }

        // to add item text, update price and remove quantity input in cart
        add_filter('woocommerce_get_item_data', [Cart::class, 'addItemText'], 10, 2);
        add_filter('woocommerce_cart_item_price', [Cart::class, 'updateItemPrice'], 10000, 2);
        add_filter('woocommerce_cart_item_quantity', [Cart::class, 'maybeRemoveQuantityInput'], 10000, 3);
        add_filter('woocommerce_cart_item_remove_link', [Cart::class, 'maybeRemoveRemoveLink'], 10000, 2);
        add_action('woocommerce_before_calculate_totals', [Cart::class, 'applyDiscounts'], 10000);
        add_action('woocommerce_after_calculate_totals', [Cart::class, 'removeInvalidItems'], 1);
        add_action('woocommerce_remove_cart_item', [Cart::class, 'maybeRemoveOtherItems'], 1, 2);
        add_action('woocommerce_cart_item_restored', [Cart::class, 'maybeRestoreOtherItems'], 1, 2);
        add_action('woocommerce_after_cart_item_name', [Cart::class, 'changeCartItemVariant'], 1);
        add_action('woocommerce_cart_updated', [Cart::class, 'updateRegularPrice'], 10000);

        add_filter('cuw_cart_item_check_quantity_for_product', [Cart::class, 'checkCartItemExistingQuantity'], 5, 100);
        add_filter('cuw_cart_item_check_quantity_for_offer', [Cart::class, 'checkCartItemExistingQuantity'], 5, 100);

        // to save stats and add order meta
        add_action('woocommerce_checkout_create_order_line_item', [Checkout::class, 'addOrderItemMeta'], 100, 3);
        add_action('woocommerce_checkout_order_created', [Checkout::class, 'saveStats'], 100);
        add_action('woocommerce_store_api_checkout_order_processed', [Checkout::class, 'saveStats'], 1);
    }
}