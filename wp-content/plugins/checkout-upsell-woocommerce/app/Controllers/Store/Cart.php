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

namespace CUW\App\Controllers\Store;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Cart as CartHelper;
use CUW\App\Helpers\Discount;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\Product;
use CUW\App\Helpers\WC;

class Cart extends Controller
{
    /**
     * To apply discounts to added products.
     *
     * @hooked woocommerce_before_calculate_totals
     */
    public static function applyDiscounts()
    {
        foreach (WC::getCartItems() as $key => $cart_item) {
            if (isset($cart_item['cuw_offer']) && $offer = $cart_item['cuw_offer']) {
                // to apply offer discount
                if (isset($offer['discount']['type']) && $offer['discount']['type'] != 'no_discount') {
                    $offer_price = apply_filters('cuw_cart_item_offer_price', $offer['price'], $cart_item, $offer);
                    WC::setCartItemPrice($cart_item, $offer_price);
                }

                // to avoid fixed offer quantity increasing
                if (!empty($offer['fixed_quantity']) && $cart_item['quantity'] != $offer['fixed_quantity']) {
                    WC::setCartItemQty($key, $offer['product']['qty'], false);
                }
            } elseif (isset($cart_item['cuw_product']) && $data = $cart_item['cuw_product']) {
                if (isset($data['discount']['type']) && $data['discount']['type'] != 'no_discount') {
                    // to apply basic product discount
                    if (!$data['discount']['is_bundle'] || $data['discount']['type'] != 'fixed_price') {
                        $discount_price = apply_filters('cuw_cart_item_discount_price', $data['price'], $cart_item, $data);
                        WC::setCartItemPrice($cart_item, $discount_price);
                    }
                }

                // to avoid fixed product quantity increasing
                if (!empty($data['fixed_quantity']) && $cart_item['quantity'] != $data['fixed_quantity']) {
                    WC::setCartItemQty($key, $data['product']['qty'], false);
                }

                // to sync main item quantity with child item quantities
                if (!empty($data['main_item_key']) && !empty($data['sync_quantity'])) {
                    $main_item = WC::getCartItem($data['main_item_key']);
                    if (!empty($main_item) && isset($main_item['quantity'])) {
                        WC::setCartItemQty($key, $main_item['quantity'], false);
                    }
                }
            }
        }

        // to split and apply fixed bundle discount
        foreach (WC::getCartItems() as $key => $cart_item) {
            if (isset($cart_item['cuw_product']) && $data = $cart_item['cuw_product']) {
                if (isset($data['discount']['type']) && $data['discount']['is_bundle'] && $data['discount']['type'] == 'fixed_price') {
                    $discount_price = CartHelper::getPricePerItem($key, $data);
                    $discount_price = apply_filters('cuw_cart_item_discount_price', $discount_price, $cart_item, $data);
                    WC::setCartItemPrice($cart_item, $discount_price);
                }
            }
        }
    }

    /**
     * To add item text.
     *
     * @hooked woocommerce_get_item_data
     */
    public static function addItemText($item_data, $cart_item)
    {
        if (isset($cart_item['cuw_offer']) && $offer = $cart_item['cuw_offer']) {
            if (apply_filters('cuw_show_upsell_item_text', true, 'offer', 'cart_item')) {
                if ($text = Offer::getText($cart_item['data'], $offer['discount'], 'cart')) {
                    $label = esc_html__("Offer", 'checkout-upsell-woocommerce');
                    $text = '<span class="cuw-offer-text">' . $text . '</span>';
                    $item_data['cuw_offer'] = [
                        'key' => apply_filters('cuw_cart_item_offer_label', $label),
                        'value' => apply_filters('cuw_cart_item_offer_text', $text, $cart_item, $offer),
                    ];
                }
            }
        } elseif (isset($cart_item['cuw_product']) && $data = $cart_item['cuw_product']) {
            if (apply_filters('cuw_show_upsell_item_text', true, 'product', 'cart_item')) {
                if (isset($data['discount']['type']) && $data['discount']['type'] != 'no_discount') {
                    if ($data['discount']['is_bundle'] && $data['discount']['type'] == 'fixed_price') {
                        $data['discount']['value'] = $data['product']['price'] - CartHelper::getPricePerItem($cart_item['key'], $data);
                    }
                    if ($text = Discount::getText($cart_item['data'], $data['discount'], 'cart')) {
                        $label = esc_html__("Discount", 'checkout-upsell-woocommerce');
                        $text = '<span class="cuw-discount-text">' . $text . '</span>';
                        $item_data['cuw_product'] = [
                            'key' => apply_filters('cuw_cart_item_discount_label', $label),
                            'value' => apply_filters('cuw_cart_item_discount_text', $text, $cart_item, $data),
                        ];
                    }
                }
            }
        }
        return $item_data;
    }

    /**
     * To update offer item price html.
     *
     * @hooked woocommerce_cart_item_price
     */
    public static function updateItemPrice($price_html, $cart_item)
    {
        if (isset($cart_item['cuw_offer']) && $offer = $cart_item['cuw_offer']) {
            if ($html = Offer::getPriceHtml($cart_item['data'], $offer['discount'], 'cart', $offer['product']['price'], $offer['price'])) {
                $price_html = '<span class="cuw-cart-item-price">' . $html . '</span>';
            }
            $price_html = apply_filters('cuw_cart_item_offer_price_html', $price_html, $cart_item, $offer);
        } elseif (isset($cart_item['cuw_product']) && $data = $cart_item['cuw_product']) {
            if ($data['discount']['is_bundle'] && $data['discount']['type'] == 'fixed_price') {
                $data['discount']['value'] = $data['product']['price'] - CartHelper::getPricePerItem($cart_item['key'], $data);
            }
            if ($html = Discount::getPriceHtml($cart_item['data'], $data['discount'], 'cart', 'cart', $data['product']['price'], $data['price'])) {
                $price_html = '<span class="cuw-cart-item-price">' . $html . '</span>';
            }
            $price_html = apply_filters('cuw_cart_item_discount_price_html', $price_html, $cart_item, $data);
        }
        return $price_html;
    }

    /**
     * To remove quantity input.
     *
     * @hooked woocommerce_cart_item_quantity
     */
    public static function maybeRemoveQuantityInput($quantity_html, $cart_item_key, $cart_item)
    {
        if (isset($cart_item['cuw_offer'])) {
            if (!empty($cart_item['cuw_offer']['fixed_quantity'])) {
                $quantity_html = $cart_item['quantity'];
            }
        } elseif (isset($cart_item['cuw_product']['main_item_key'])) {
            if (!empty($cart_item['cuw_product']['fixed_quantity']) || !empty($cart_item['cuw_product']['sync_quantity'])) {
                $quantity_html = $cart_item['quantity'];
            }
        }
        return $quantity_html;
    }

    /**
     * To remove item remove link.
     *
     * @hooked woocommerce_cart_item_remove_link
     */
    public static function maybeRemoveRemoveLink($remove_link, $cart_item_key)
    {
        $cart_item = WC::getCartItem($cart_item_key);
        if (isset($cart_item['cuw_product']['main_item_key'])) {
            if (empty($cart_item['cuw_product']['allow_remove'])) {
                $remove_link = '';
            }
        }
        return $remove_link;
    }

    /**
     * To remove invalid items from cart.
     *
     * @hooked woocommerce_after_calculate_totals
     */
    public static function removeInvalidItems()
    {
        foreach (CartHelper::getAppliedOffers() as $key => $offer) {
            if (!CartHelper::isOfferApplicable($offer['id'])) {
                WC::removeCartItem($key);
            }
        }
        foreach (CartHelper::getAddedProducts() as $key => $product) {
            if (!CartHelper::isProductApplicable($product['campaign_id'])) {
                WC::removeCartItem($key);
            }
        }
    }

    /**
     * Maybe remove other items.
     *
     * @hooked woocommerce_remove_cart_item
     */
    public static function maybeRemoveOtherItems($cart_item_key, $cart)
    {
        foreach (CartHelper::getAddedProducts() as $key => $product) {
            if (isset($product['main_item_key']) && $product['main_item_key'] == $cart_item_key) {
                WC::removeCartItem($key);
            }
        }
    }

    /**
     * Maybe restore other items.
     *
     * @param \WC_Cart $cart
     * @hooked woocommerce_cart_item_restored
     */
    public static function maybeRestoreOtherItems($cart_item_key, $cart)
    {
        $cart_item = WC::getCartItem($cart_item_key);
        if (isset($cart_item['cuw_product']['is_main_item'])) {
            if (method_exists($cart, 'get_removed_cart_contents') && method_exists($cart, 'restore_cart_item')) {
                foreach ($cart->get_removed_cart_contents() as $key => $item) {
                    if (isset($item['cuw_product']['main_item_key']) && $item['cuw_product']['main_item_key'] == $cart_item_key) {
                        $cart->restore_cart_item($key);
                    }
                }
            }
        }
    }

    /**
     * Variant select in cart page.
     *
     * @param \WC_Cart $cart
     * @hooked woocommerce_after_cart_item_name
     */
    public static function changeCartItemVariant($cart_item)
    {
        $app = \CUW\App\Core::instance();
        if (!empty($cart_item['cuw_product']['change_variant']) || !empty($cart_item['cuw_offer']['change_variant'])) {
            if (empty($cart_item['cuw_product']['product']['variation_id']) && empty($cart_item['cuw_offer']['product']['variation_id'])) {
                return;
            }

            $product_data = Product::getData($cart_item['product_id'], [
                'to_display' => true,
                'display_in' => 'cart',
                'include_variants' => true,
                'filter_purchasable' => true,
            ]);
            if (!empty($product_data['variants'])) {
                $app->template('common/cart-item-variant-select', [
                    'product' => $product_data,
                    'cart_item' => $cart_item,
                    'cart_item_key' => $cart_item['key'],
                ]);
            }
        }
    }
}