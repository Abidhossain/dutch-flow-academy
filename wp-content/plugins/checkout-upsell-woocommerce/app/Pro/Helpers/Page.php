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

namespace CUW\App\Pro\Helpers;

defined('ABSPATH') or exit;

class Page
{
    /**
     * To hold locations.
     *
     * @var array
     */
    public static $locations;

    /**
     * Get pages.
     *
     * @return array
     */
    public static function get($page = '')
    {
        $pages = apply_filters('cuw_pages', [
            'shop' => [
                'title' => __("Shop page", 'checkout-upsell-woocommerce'),
                'engine_type' => 'generic',
                'atc_redirect' => '',
            ],
            'product' => [
                'title' => __("Product page", 'checkout-upsell-woocommerce'),
                'engine_type' => 'product',
                'atc_redirect' => '',
            ],
            'cart' => [
                'title' => __("Cart page", 'checkout-upsell-woocommerce'),
                'engine_type' => 'cart',
                'atc_redirect' => '',
            ],
            'checkout' => [
                'title' => __("Checkout page", 'checkout-upsell-woocommerce'),
                'engine_type' => 'cart',
                'atc_redirect' => wc_get_checkout_url(),
            ],
            'order_received' => [
                'title' => __("Thankyou page", 'checkout-upsell-woocommerce'),
                'engine_type' => 'order',
                'atc_redirect' => wc_get_checkout_url(),
            ],
        ]);

        if ($page !== '') {
            return $pages[$page];
        }
        return $pages;
    }

    /**
     * Get locations.
     *
     * @param string $page
     * @return array
     */
    public static function getLocations($page = '')
    {
        if (!isset(self::$locations)) {
            $locations = [
                'shop' => [
                    'woocommerce_before_shop_loop' => [
                        'hook' => 'woocommerce_before_shop_loop',
                        'title' => esc_html__("Before products", 'checkout-upsell-woocommerce'),
                        'priority' => 2,
                    ],
                    'woocommerce_after_shop_loop' => [
                        'hook' => 'woocommerce_after_shop_loop',
                        'title' => esc_html__("After products", 'checkout-upsell-woocommerce'),
                        'priority' => 20,
                    ],
                    'woocommerce_no_products_found' => [
                        'hook' => 'woocommerce_no_products_found',
                        'title' => esc_html__("No products found", 'checkout-upsell-woocommerce'),
                    ],
                ],

                'cart' => [
                    'woocommerce_before_cart' => [
                        'hook' => 'woocommerce_before_cart',
                        'title' => esc_html__("Top of the Cart page", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_before_cart_table' => [
                        'hook' => 'woocommerce_before_cart_table',
                        'title' => esc_html__("Before Cart items table", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_cart_collaterals' => [
                        'hook' => 'woocommerce_cart_collaterals',
                        'title' => esc_html__("Cart Collaterals", 'checkout-upsell-woocommerce'),
                        'class' => ['cross-sells'],
                    ],
                    'woocommerce_after_cart' => [
                        'hook' => 'woocommerce_after_cart',
                        'title' => esc_html__("Bottom of the cart", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_cart_is_empty' => [
                        'hook' => 'woocommerce_cart_is_empty',
                        'title' => esc_html__("Empty cart", 'checkout-upsell-woocommerce'),
                    ],
                ],

                'product' => [
                    'woocommerce_before_single_product' => [
                        'hook' => 'woocommerce_before_single_product',
                        'title' => esc_html__("Top of the Product page", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_after_single_product' => [
                        'hook' => 'woocommerce_after_single_product',
                        'title' => esc_html__("Bottom of the Product Page", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_after_single_product_summary' => [
                        'hook' => 'woocommerce_after_single_product_summary',
                        'title' => esc_html__("After Product summary", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_after_add_to_cart_button' => [
                        'hook' => 'woocommerce_after_add_to_cart_button',
                        'title' => esc_html__("After Add to cart Button", 'checkout-upsell-woocommerce'),
                    ],
                ],

                'checkout' => [
                    'woocommerce_before_checkout_form' => [
                        'hook' => 'woocommerce_before_checkout_form',
                        'title' => esc_html__("Before checkout Form", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_checkout_billing' => [
                        'hook' => 'woocommerce_checkout_billing',
                        'title' => esc_html__("Before Billing section", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_after_order_notes' => [
                        'hook' => 'woocommerce_after_order_notes',
                        'title' => esc_html__("After Order notes", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_review_order_before_payment' => [
                        'hook' => 'woocommerce_review_order_before_payment',
                        'title' => esc_html__("Before Payment Gateways", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_after_checkout_form' => [
                        'hook' => 'woocommerce_after_checkout_form',
                        'title' => esc_html__("After Checkout Form", 'checkout-upsell-woocommerce'),
                    ],
                ],

                'order_received' => [
                    'woocommerce_order_details_before_order_table' => [
                        'hook' => 'woocommerce_order_details_before_order_table',
                        'title' => esc_html__("Before the Order items summary", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_order_details_after_order_table' => [
                        'hook' => 'woocommerce_order_details_after_order_table',
                        'title' => esc_html__("After the Order items summary", 'checkout-upsell-woocommerce'),
                    ],
                    'woocommerce_order_details_after_customer_details' => [
                        'hook' => 'woocommerce_order_details_after_customer_details',
                        'title' => esc_html__("After Customer details", 'checkout-upsell-woocommerce'),
                    ],
                ]
            ];

            self::$locations = apply_filters('cuw_page_locations', $locations);
        }

        if ($page !== '') {
            return self::$locations[$page];
        }
        return self::$locations;
    }
}
