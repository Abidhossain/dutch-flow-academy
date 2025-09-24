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

namespace CUW\App\Pro\Modules\PostPurchase;

use CUW\App\Core;

defined('ABSPATH') || exit;

class Shortcodes
{
    /**
     * To get shortcodes.
     *
     * @return array
     */
    public static function get()
    {
        return [
            'ppu_order_details' => [
                'title' => __('Main order details', 'checkout-upsell-woocommerce'),
                'description' => __('Shows the main order current status and details.', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'orderDetails'],
            ],
            'ppu_offer_timer' => [
                'title' => __('Offer timer', 'checkout-upsell-woocommerce'),
                'description' => __('Shows the offer expiration notice with a countdown timer.', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerTimer'],
            ],
            'ppu_offer_title' => [
                'title' => __('Offer title', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer title', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerTitle'],
            ],
            'ppu_offer_description' => [
                'title' => __('Offer description', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer description', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerDescription'],
            ],
            'ppu_product_name' => [
                'title' => __('Product name', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer product name', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productName'],
            ],
            'ppu_product_image' => [
                'title' => __('Product image', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer product image', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productImage'],
            ],
            'ppu_product_price' => [
                'title' => __('Product price', 'checkout-upsell-woocommerce'),
                'description' => __('Shows product price (offer price)', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productPrice'],
            ],
            'ppu_product_quantity' => [
                'title' => __('Product quantity', 'checkout-upsell-woocommerce'),
                'description' => __('shows offer product quantity text or input', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productQuantity'],
            ],
            'ppu_product_variants' => [
                'title' => __('Product variants', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer product variant select', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productVariants'],
            ],
            'ppu_order_totals' => [
                'title' => __('Product totals', 'checkout-upsell-woocommerce'),
                'description' => __('Shows product price, discount, tax and total', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'orderTotals'],
            ],
            'ppu_accept_offer_button' => [
                'title' => __('Offer accept button', 'checkout-upsell-woocommerce'),
                'description' => __('Shows accept offer CTA button', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerAcceptButton'],
            ],
            'ppu_decline_offer_button' => [
                'title' => __('Offer decline button', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer decline button', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerDeclineButton'],
            ],
            'ppu_decline_offer_link' => [
                'title' => __('Offer decline link', 'checkout-upsell-woocommerce'),
                'description' => __('Shows offer decline link', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase template', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerDeclineLink'],
            ],
        ];
    }

    /**
     * To load shortcodes.
     */
    public static function load()
    {
        add_filter('cuw_shortcodes', function ($shortcodes) {
            return array_merge($shortcodes, self::get());
        });
    }

    /**
     * To render component.
     */
    private static function template($component, $data = [])
    {
        return Core::instance()->template('post-purchase/components/' . $component, $data, false);
    }

    /**
     * Load order details.
     */
    public static function orderDetails()
    {
        $offer = Offer::getData();
        return $offer ? self::template('order-details', ['offer' => $offer]) : '';
    }

    /**
     * Load offer timer.
     */
    public static function offerTimer()
    {
        $offer = Offer::getData();
        return $offer ? self::template('timer', ['offer' => $offer]) : '';
    }

    /**
     * Load offer title.
     */
    public static function offerTitle()
    {
        $offer = Offer::getData();
        $style = $offer['styles']['title'] ?? '';
        $style .= 'padding: 12px;';
        $title = wp_kses($offer['template']['title'] ?? '', $offer['allowed_html'] ?? []);
        return sprintf('<span class="cuw-ppu-offer-title" style="%s">%s</span>', $style, $title);
    }

    /**
     * Load offer description.
     */
    public static function offerDescription()
    {
        $offer = Offer::getData();
        $style = $offer['styles']['description'] ?? '';
        $description = wp_kses($offer['template']['description'] ?? '', $offer['allowed_html'] ?? []);
        return sprintf('<span class="cuw-ppu-offer-description" style="%s">%s</span>', $style, $description);
    }

    /**
     * Load offer product name.
     */
    public static function productName()
    {
        $name = wp_kses_post(Offer::getData()['product']['title'] ?? '');
        return sprintf('<span class="cuw-ppu-product-name">%s</span>', $name);
    }

    /**
     * Load offer image.
     */
    public static function productImage()
    {
        $offer = Offer::getData();
        if (!empty($offer) && isset($offer['product']['image'])) {
            $image = $offer['product']['image'];
            if (!empty($offer['product']['default_variant']['image'])) {
                $image = $offer['product']['default_variant']['image'];
            }
            return sprintf('<div class="cuw-ppu-product-image">%s</div>', $image);
        }
        return '';
    }

    /**
     * Load offer price.
     */
    public static function productPrice()
    {
        $offer = Offer::getData();
        if (!empty($offer) && isset($offer['product']['price_html'])) {
            $price_html = $offer['product']['price_html'];
            if (!empty($offer['product']['default_variant']['price_html'])) {
                $price_html = $offer['product']['default_variant']['price_html'];
            }
            return sprintf('<span class="cuw-ppu-product-price">%s</span>', $price_html);
        }
        return '';
    }

    /**
     * Load offer quantity input.
     */
    public static function productQuantity()
    {
        $offer = Offer::getData();
        return $offer ? self::template('product-quantity', ['offer' => $offer]) : '';
    }

    /**
     * Load offer variant select.
     */
    public static function productVariants()
    {
        $offer = Offer::getData();
        return $offer ? self::template('product-variants', ['offer' => $offer]) : '';
    }

    /**
     * Load product totals.
     */
    public static function orderTotals()
    {
        $offer = Offer::getData();
        return $offer ? self::template('order-totals', ['offer' => $offer]) : '';
    }

    /**
     * Load offer accept button.
     */
    public static function offerAcceptButton()
    {
        $offer = Offer::getData();
        return $offer ? self::template('accept-button', ['offer' => $offer]) : '';
    }

    /**
     * Load offer decline button.
     */
    public static function offerDeclineButton()
    {
        $offer = Offer::getData();
        return $offer ? self::template('decline-button', ['offer' => $offer]) : '';
    }

    /**
     * Load offer decline link.
     */
    public static function offerDeclineLink()
    {
        $offer = Offer::getData();
        return $offer ? self::template('decline-link', ['offer' => $offer]) : '';
    }
}