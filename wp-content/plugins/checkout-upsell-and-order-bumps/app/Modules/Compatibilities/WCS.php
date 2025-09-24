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

namespace CUW\App\Modules\Compatibilities;

use CUW\App\Helpers\Discount;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\WC;

defined('ABSPATH') || exit;

class WCS extends Base
{
    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('cuw_product_price_html', [__CLASS__, 'getProductPriceHtml'], 100, 5);
        add_filter('cuw_discount_price_html', [__CLASS__, 'getDiscountPriceHtml'], 100, 6);
        add_action('cuw_post_purchase_offer_added_to_order', [__CLASS__, 'createOfferSubscription'], 100, 3);
        add_action('cuw_ppu_offer_added_to_order', [__CLASS__, 'createOfferSubscription'], 100, 3);
    }

    /**
     * Get product price html.
     */
    public static function getProductPriceHtml($price_html, $product, $tax_based_on, $regular_price, $price)
    {
        $new_html = self::getPriceHtml($product, $regular_price, $price, $tax_based_on);
        if (!empty($new_html)) {
            return $new_html;
        }
        return $price_html;
    }

    /**
     * Get discount price html.
     */
    public static function getDiscountPriceHtml($price_html, $product, $discount, $tax_based_on, $regular_price, $price)
    {
        if ($discount['type'] != 'no_discount') {
            $new_html = self::getPriceHtml($product, $regular_price, $price, $tax_based_on);
            if (!empty($new_html)) {
                return $new_html;
            }
        }
        return $price_html;
    }

    /**
     * Get subscription price html.
     */
    private static function getPriceHtml($product, $regular_price, $price, $tax_based_on = 'shop')
    {
        if (method_exists('\WC_Subscriptions_Product', 'is_subscription') && \WC_Subscriptions_Product::is_subscription($product)) {
            if (!WC::isVariableProduct($product)) {
                $product_copy = clone $product;
                $product_copy->set_regular_price($regular_price);
                $product_copy->set_sale_price($price);
                $product_copy->set_price($price);
                if ($tax_based_on != 'shop') {
                    add_filter('woocommerce_get_price_suffix', [__CLASS__, 'getPriceSuffix'], 100);
                }
                $price_html = $product_copy->get_price_html();
                if ($tax_based_on != 'shop') {
                    remove_filter('woocommerce_get_price_suffix', [__CLASS__, 'getPriceSuffix'], 100);
                }
                return $price_html;
            }
        }
        return '';
    }

    /**
     * Get price suffix.
     */
    public static function getPriceSuffix()
    {
        return '';
    }

    /**
     * Add subscription for post-purchase offer item.
     */
    public static function createOfferSubscription($order, $product_data, $offer_data)
    {
        $order_items = $order->get_items();
        $offer_item = end($order_items);
        if ($offer_item && is_object($offer_item) && class_exists('WC_Subscriptions_Product')) {
            $product = $offer_item->get_product();
            $is_sub_item = \WC_Subscriptions_Product::is_subscription($product);
            if ($is_sub_item && function_exists('wcs_create_subscription')) {
                $subscription = wcs_create_subscription([
                    'billing_period' => \WC_Subscriptions_Product::get_period($product),
                    'billing_interval' => \WC_Subscriptions_Product::get_interval($product),
                    'order_id' => $order->get_id(),
                ]);
                $product_price = Offer::getProductPrice($product, $offer_data['discount']);
                if (!is_wp_error($subscription)) {
                    $item_id = $subscription->add_product($product, $offer_item->get_quantity(), [
                        'subtotal' => Discount::getPrice($product, $offer_data['discount'], $product_price),
                        'total' => Discount::getPrice($product, $offer_data['discount'], $product_price),
                    ]);
                    if (!empty($item_id)) {
                        $subscription->set_payment_method($order->get_payment_method());
                        $subscription->set_payment_method_title($order->get_payment_method_title());
                        $subscription->set_transaction_id($order->get_transaction_id());
                        $subscription->set_address($order->get_address());
                        $subscription->set_address($order->get_address('shipping'), 'shipping');
                        $subscription->update_dates([
                            'end' => \WC_Subscriptions_Product::get_expiration_date($product),
                            'trial_end' => \WC_Subscriptions_Product::get_trial_expiration_date($product)
                        ]);
                        $subscription->calculate_totals();
                        $subscription->save();
                    }
                }
            }
        }
    }
}