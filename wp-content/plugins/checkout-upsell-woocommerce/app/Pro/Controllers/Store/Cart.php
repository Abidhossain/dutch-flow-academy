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

namespace CUW\App\Pro\Controllers\Store;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Config;
use CUW\App\Pro\Modules\Campaigns\CartAddons;
use CUW\App\Pro\Modules\Campaigns\ProductAddons;

class Cart extends Controller
{
    /**
     * Add an add-ons label after item name.
     *
     * @hooked woocommerce_cart_item_name
     */
    public static function addAddonLabel($product_title, $cart_item)
    {
        if (isset($cart_item['cuw_product']['campaign_type']) && (in_array($cart_item['cuw_product']['campaign_type'], [ProductAddons::TYPE, CartAddons::TYPE]))) {
            $addon_label = Config::getSetting('addon_badge_text');
            if (!empty($addon_label)) {
                $addon_label = apply_filters('cuw_addon_badge_text', __($addon_label, 'checkout-upsell-woocommerce'), $cart_item['cuw_product'], $cart_item);
                $product_title .= ' <small class="cuw-addon-text">' . $addon_label . '</small>';
            }
        }
        return $product_title;
    }
}