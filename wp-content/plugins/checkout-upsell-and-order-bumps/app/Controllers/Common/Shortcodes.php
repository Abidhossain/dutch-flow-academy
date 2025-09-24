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

namespace CUW\App\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\WC;
use CUW\App\Modules\Campaigns\CartUpsells;
use CUW\App\Modules\Campaigns\CheckoutUpsells;

class Shortcodes extends Controller
{
    /**
     * To get shortcodes.
     *
     * @return array
     */
    public static function get()
    {
        $shortcodes = apply_filters('cuw_shortcodes', [
            'cart_upsells' => [
                'title' => __('Cart upsell offers', 'checkout-upsell-and-order-bumps'),
                'description' => __('To show Cart Upsell offers on the cart page', 'checkout-upsell-and-order-bumps'),
                'group' => __("Cart page", 'checkout-upsell-and-order-bumps'),
                'callback' => [__CLASS__, 'cartUpsellOffers'],
            ],
            'checkout_upsells' => [
                'title' => __('Checkout upsell offers', 'checkout-upsell-and-order-bumps'),
                'description' => __('To show Checkout Upsell offers on the checkout page', 'checkout-upsell-and-order-bumps')
                    . '<br><span class="form-text text-dark">' . __('NOTE: [cuw_offers] shortcode has been deprecated since v1.3.2. Use this shortcode instead.', 'checkout-upsell-and-order-bumps') . '</span>',
                'group' => __("Checkout page", 'checkout-upsell-and-order-bumps'),
                'callback' => [__CLASS__, 'checkoutUpsellOffers'],
            ],
            'fbt' => [
                'title' => __('Frequently bought together', 'checkout-upsell-and-order-bumps'),
                'description' => __('To show Frequently bought together products on the product page', 'checkout-upsell-and-order-bumps'),
                'group' => __('Product page', 'checkout-upsell-and-order-bumps'),
                'callback' => [__CLASS__, 'fbtProducts'],
            ],
        ]);

        foreach ($shortcodes as $key => $shortcode) {
            $tag = 'cuw_' . $key;
            $shortcodes[$key]['tag'] = $tag;
            $shortcodes[$key]['code'] = '[' . $tag . ']';
        }
        return $shortcodes;
    }

    /**
     * To load shortcodes.
     */
    public static function add()
    {
        foreach (self::get() as $shortcode) {
            add_shortcode($shortcode['tag'], $shortcode['callback']);
        }

        // [cuw_offers] shortcode deprecated since 1.3.1. Use [cuw_checkout_upsells] instead.
        add_shortcode('cuw_offers', [__CLASS__, 'checkoutUpsellOffers']);
    }

    /**
     * To show cart upsell offers.
     */
    public static function cartUpsellOffers()
    {
        if (WC::is('cart', true) || (WC::is('checkout', true) && !WC::is('endpoint'))) {
            return CartUpsells::getOffersHtml('shortcode');
        }
        return '';
    }

    /**
     * To show checkout upsell offers.
     */
    public static function checkoutUpsellOffers()
    {
        if (WC::is('cart', true) || (WC::is('checkout', true) && !WC::is('endpoint'))) {
            return CheckoutUpsells::getOffersHtml('shortcode');
        }
        return '';
    }

    /**
     * To show FBT products.
     */
    public static function fbtProducts()
    {
        if (WC::is('product')) {
            ob_start();
            do_action('cuw_fbt_shortcode');
            return ob_get_clean();
        }
        return '';
    }
}