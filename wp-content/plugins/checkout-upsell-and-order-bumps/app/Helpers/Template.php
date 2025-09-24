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

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Core;
use CUW\App\Models\Campaign as CampaignModel;

class Template
{
    /**
     * Get template js data.
     *
     * @return array
     */
    public static function getScriptData()
    {
        return [
            'data' => [
                'woocommerce' => [
                    'price' => [
                        'format' => html_entity_decode(WC::get('price_format', '%1$s%2$s')),
                        'symbol' => html_entity_decode(WC::get('currency_symbol', '')),
                        'decimals' => WC::get('price_decimals', 2),
                        'decimal_separator' => WC::get('price_decimal_separator', '.'),
                        'thousand_separator' => WC::get('price_thousand_separator', ''),
                    ],
                ],
            ],
            'i18n' => [
                'add_to_cart' => [
                    'text' => esc_html__('Add to cart', 'checkout-upsell-and-order-bumps'),
                    'items' => esc_html__('items', 'checkout-upsell-and-order-bumps'),
                    'all_items' => esc_html__('all items', 'checkout-upsell-and-order-bumps'),
                    'selected_items' => esc_html__('selected items', 'checkout-upsell-and-order-bumps'),
                    'number_to_text' => [
                        1 => esc_html__('one', 'checkout-upsell-and-order-bumps'),
                        2 => esc_html__('both', 'checkout-upsell-and-order-bumps'),
                        3 => esc_html__('all three', 'checkout-upsell-and-order-bumps'),
                        4 => esc_html__('all four', 'checkout-upsell-and-order-bumps'),
                        5 => esc_html__('all five', 'checkout-upsell-and-order-bumps'),
                    ],
                ],
                'free' => esc_html__("Free", 'checkout-upsell-and-order-bumps'),
            ],
            'is_rtl' => WP::isRtl(),
        ];
    }

    /**
     * Returns available templates data.
     *
     * @param string $campaign_type
     * @return array
     */
    public static function get($campaign_type = '')
    {
        self::getDefaultTexts(); // load translated default strings

        $templates = apply_filters('cuw_templates', [
            // checkout upsell templates
            'offer/template-1' => [
                'title' => '{discount} offer',
                'description' => 'Hey there, you can get this offer by just clicking the checkbox below to add this offer to your order, you will never get such a discount on any other place on this site.',
                'cta_text' => 'Get this exclusive offer now!',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'medium', 'border-style' => 'dashed', 'border-color' => '#c3c4c7', 'background-color' => '#fbfbfb'],
                    'title' => ['font-size' => '', 'color' => '#ffffff', 'background-color' => '#32cd32'],
                    'description' => ['font-size' => '', 'color' => '#525f7a', 'background-color' => '#d1ecf1'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#e2e6ea'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-2' => [
                'title' => '{discount} off',
                'description' => 'Don\'t miss this offer, you may don\'t get this offer again!',
                'cta_text' => 'Get this offer now',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#c3c4c7', 'background-color' => '#fbfbfb'],
                    'title' => ['font-size' => '16px', 'color' => '#ffffff', 'background-color' => '#32cd32'],
                    'description' => ['font-size' => '', 'color' => '#525f7a', 'background-color' => '#fbfbfb'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#e2e6ea'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-3' => [
                'title' => '{discount} discount',
                'description' => 'Hey there, don\'t forget! you will never get such a discount on any other place on this site.',
                'cta_text' => 'Yes! Add to My Order',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#fafafa'],
                    'title' => ['font-size' => '', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-4' => [
                'title' => 'Special offer',
                'description' => 'Don\'t miss this offer!',
                'cta_text' => 'Add to Order',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '16px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-5-wide' => [
                'title' => '{discount} offer',
                'description' => 'Hey there, you can get this offer by just clicking the checkbox below. Add this offer to your order, you will never get such a discount on any other place on this site.',
                'cta_text' => 'Get this exclusive offer now!',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'medium', 'border-style' => 'dashed', 'border-color' => '#c3c4c7', 'background-color' => '#fbfbfb'],
                    'title' => ['font-size' => '24px', 'color' => '#ffffff', 'background-color' => '#32cd32'],
                    'description' => ['font-size' => '16px', 'color' => '#525f7a', 'background-color' => '#d1ecf1'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#e2e6ea'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-6-wide' => [
                'title' => '{discount} discount',
                'description' => 'Hey there, you can get this offer by just clicking the button below to add this offer to your order, you will never get such a discount on any other place on this site.',
                'cta_text' => 'Add to Order',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '16px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-7-mini' => [
                'title' => '{discount} discount',
                'description' => '',
                'cta_text' => 'Yes! I want it',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#f8fafc'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '12px', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#1e293b', 'background-color' => '#c4b5fd'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-8-mini' => [
                'title' => '{discount} discount',
                'description' => '',
                'cta_text' => 'Yes! I want it',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '12px', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-9' => [
                'title' => '{discount} discount',
                'description' => 'Hey there, you can get this offer by just clicking the button below to add this offer to your order, you will never get such a discount on any other place on this site.',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#DDE0E4', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-10' => [
                'title' => '{discount} discount',
                'description' => 'Hey there, you can get this offer by just clicking the button below to add this offer to your order, you will never get such a discount on any other place on this site.',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#DDE0E4', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '16px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-11-mini' => [
                'title' => '{discount} discount',
                'description' => '',
                'cta_text' => '+',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#DDE0E4', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '12px', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],
            'offer/template-12-mini' => [
                'title' => '{discount} discount',
                'description' => '',
                'cta_text' => '+',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#DDE0E4', 'background-color' => '#ffffff'],
                    'title' => ['font-size' => '14px', 'color' => '#0AC26C', 'background-color' => '#E6F9F0'],
                    'description' => ['font-size' => '12px', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#ffffff', 'background-color' => '#0A5CFF'],
                ],
                'campaigns' => ['checkout_upsells'],
            ],

            // cart upsell templates
            'offer/cart-template-1-wide' => [
                'title' => 'Offer: {discount}',
                'description' => '',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '14px', 'color' => '#222222', 'background-color' => '#f9f9f9'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#f3e141'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-2-wide' => [
                'title' => '{discount} OFF',
                'description' => '',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#d7f4ee', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '16px', 'color' => '#222222', 'background-color' => '#66ff33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#66ff33'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-3-wide' => [
                'title' => '{discount} OFF',
                'description' => '',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#ffffff', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '14px', 'color' => '#fafafa', 'background-color' => '#717af9'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '', 'color' => '#fafafa', 'background-color' => '#717af9'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-4-wide' => [
                'title' => 'Offer: {discount}',
                'description' => '',
                'cta_text' => '+',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'medium', 'border-style' => 'dashed', 'border-color' => '#cbd5e1', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '16px', 'color' => '#222222', 'background-color' => '#fb923c'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '16px', 'color' => '#222222', 'background-color' => '#fb923c'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-5-mini' => [
                'title' => '{discount} OFF',
                'description' => '',
                'cta_text' => '',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#cbd5e1', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#33cd33'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '16px', 'color' => '#222222', 'background-color' => ''],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-6-mini' => [
                'title' => 'Offer: {discount}',
                'description' => '',
                'cta_text' => '',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#cbd5e1', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#222222', 'background-color' => '#f9f9f9'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '12px', 'color' => '#f2f2f2', 'background-color' => '#1d4ed8'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-7-wide' => [
                'title' => 'Offer: {discount}',
                'description' => '',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#EAEAEA', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#0AC26C'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '16px', 'color' => '#ffffff', 'background-color' => '#0A5CFF'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-8-wide' => [
                'title' => 'Offer: {discount}',
                'description' => '',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#EAEAEA', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#0AC26C'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '16px', 'color' => '#ffffff', 'background-color' => '#0A5CFF'],
                ],
                'campaigns' => ['cart_upsells'],
            ],
            'offer/cart-template-9-wide' => [
                'title' => 'Offer: {discount}',
                'description' => '',
                'cta_text' => 'Add to cart',
                'image_id' => 0,
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#EAEAEA', 'background-color' => '#f9f9f9'],
                    'title' => ['font-size' => '12px', 'color' => '#ffffff', 'background-color' => '#0AC26C'],
                    'description' => ['font-size' => '', 'color' => '#7b7b7b', 'background-color' => '#ededed'],
                    'cta' => ['font-size' => '16px', 'color' => '#0A5CFF', 'background-color' => ''],
                ],
                'campaigns' => ['cart_upsells'],
            ],

            // simple action templates
            'action/simple-action-1' => [
                'cta_text' => 'Click here to double your order and get a {discount} discount!',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#efefef', 'background-color' => '', 'padding' => '0'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#fbfbfb'],
                ],
                'campaigns' => ['double_order'],
            ],
            'action/simple-action-2' => [
                'cta_text' => 'Click here to double your order and get a {discount} discount!',
                'styles' => [
                    'template' => ['border-width' => 'medium', 'border-style' => 'dashed', 'border-color' => '#c3c4c7', 'background-color' => '#fbfbfb', 'padding' => '4px'],
                    'cta' => ['font-size' => '', 'color' => '#333333', 'background-color' => '#e2e6ea'],
                ],
                'campaigns' => ['double_order'],
            ],
            'action/simple-action-3' => [
                'cta_text' => 'Click here to double your order and get a {discount} discount!',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#efefef', 'background-color' => '#0A5CFF', 'padding' => '2px'],
                    'cta' => ['font-size' => '', 'color' => '#181C25', 'background-color' => '#ffffff'],
                ],
                'campaigns' => ['double_order'],
            ],

            // next order coupon templates
            'noc/template-1' => [
                'title' => '{discount} Off Your Next Purchase',
                'description' => 'To thank you for being a loyal customer we want to offer you an exclusive voucher for your next order!',
                'cta_text' => 'Go!',
                'message' => 'hide',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#f3f4f6', 'background-color' => '#f8fafc', 'padding' => '4px'],
                    'title' => ['font-size' => '20px', 'color' => '#030712', 'background-color' => '#f8fafc'],
                    'description' => ['font-size' => '', 'color' => '#111827', 'background-color' => '#f8fafc'],
                    'coupon' => ['border-width' => 'medium', 'border-style' => 'dashed', 'border-color' => '#6e7cf7', 'font-size' => '', 'color' => '#6e7cf7', 'background-color' => '#ffffff'],
                    'cta' => ['font-size' => '', 'color' => '#ffffff', 'background-color' => '#6e7cf7'],
                ],
                'campaigns' => ['noc'],
            ],
            'noc/template-2' => [
                'title' => '{discount} Off Your Next Purchase',
                'description' => 'To thank you for being a loyal customer we want to offer you an exclusive voucher for your next order!',
                'cta_text' => 'Go!',
                'message' => 'hide',
                'styles' => [
                    'template' => ['border-width' => 'medium', 'border-style' => 'dashed', 'border-color' => '#9ca3af', 'background-color' => '#f3f4f6', 'padding' => '16px'],
                    'title' => ['font-size' => '20px', 'color' => '#030712', 'background-color' => '#f3f4f6'],
                    'description' => ['font-size' => '', 'color' => '#111827', 'background-color' => '#f3f4f6'],
                    'coupon' => ['border-width' => 'thin', 'border-style' => 'dotted', 'border-color' => '#9082b6', 'font-size' => '', 'color' => '#af4b55', 'background-color' => '#fafafa'],
                    'cta' => ['font-size' => '', 'color' => '#ffffff', 'background-color' => '#c3656e'],
                ],
                'campaigns' => ['noc'],
            ],
            'noc/template-3-classic' => [
                'title' => '{discount} Off Your Next Purchase',
                'description' => 'To thank you for being a loyal customer we want to offer you an exclusive voucher for your next order!',
                'cta_text' => 'Redeem Now!',
                'message' => 'hide',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#e5e5e5', 'background-color' => '#ffffff', 'padding' => '12px'],
                    'title' => ['font-size' => '20px', 'color' => '#1e293b', 'background-color' => '#ffffff'],
                    'description' => ['font-size' => '', 'color' => '#636363', 'background-color' => '#ffffff'],
                    'coupon' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#e5e5e5', 'font-size' => '', 'color' => '#636363', 'background-color' => '#ffffff'],
                    'cta' => ['font-size' => '', 'color' => '#ffffff', 'background-color' => '#7f54b3'],
                ],
                'campaigns' => ['noc'],
            ],

            // frequently bought together templates
            'fbt/template-1' => [
                'template' => 'template-1',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_text} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '200'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],
            'fbt/template-2' => [
                'template' => 'template-2',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_count} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#f0f0f0', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '200'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],
            'fbt/template-3' => [
                'template' => 'template-3',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_text} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '160'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],
            'fbt/template-4' => [
                'template' => 'template-1',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_text} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '200'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],
            'fbt/template-5' => [
                'template' => 'template-1',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_text} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '200'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],
            'fbt/template-6' => [
                'template' => 'template-1',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_text} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '88'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],
            'fbt/template-7' => [
                'template' => 'template-1',
                'title' => 'Frequently bought together',
                'cta_text' => 'Add {items_text} to cart',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '200'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['fbt'],
            ],

            // thankyou upsells campaign templates
            'products/template-1' => [
                'template' => 'template-1',
                'title' => 'You may also like…',
                'cta_text' => 'Buy now',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '160'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['thankyou_upsells'],
            ],
            'products/template-2' => [
                'template' => 'template-2',
                'title' => 'You may also like…',
                'cta_text' => 'Buy now',
                'checkbox' => 'checked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '200'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['thankyou_upsells'],
            ],
            'products/template-3' => [
                'template' => 'template-3',
                'title' => 'You may also like…',
                'cta_text' => 'Buy now',
                'checkbox' => 'unchecked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '180'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['thankyou_upsells'],
            ],
            'products/template-4' => [
                'template' => 'template-5',
                'title' => 'You may also like…',
                'cta_text' => 'Buy now',
                'checkbox' => 'unchecked',
                'save_badge' => 'do_not_display',
                'save_badge_text' => '-{price}',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'image' => ['size' => '180'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['thankyou_upsells'],
            ],

            // upsell popups campaign templates
            'popup/template-1' => [
                'template' => 'template-1',
                'title' => 'Wait! Don\'t miss our special deals',
                'cta_text' => 'Add',
                'styles' => [
                    'content' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'max-width' => 800],
                    'header' => ['font-size' => '28px', 'color' => '#1a202c', 'background-color' => '#f7fafc'],
                    'subheader' => ['font-size' => '24px', 'color' => '#1a202c', 'background-color' => '#dcf9df'],
                    'body' => ['padding' => '8px', 'background-color' => '#ffffff'],
                    'image' => ['size' => '100'],
                    'cta' => ['font-size' => '18px', 'color' => '#ffffff', 'background-color' => '#178a0c'],
                    'action' => ['font-size' => '20px', 'color' => '#ffffff', 'background-color' => '#000000'],
                    'footer' => ['font-size' => '18px', 'color' => '', 'background-color' => '#f7fafc'],
                ],
                'campaigns' => ['upsell_popups'],
            ],
            'popup/template-2' => [
                'template' => 'template-2',
                'title' => 'Exclusive offer of the day!',
                'cta_text' => 'Add',
                'styles' => [
                    'content' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#e2e8f0', 'max-width' => 800],
                    'header' => ['font-size' => '28px', 'color' => '#1a202c', 'background-color' => '#f7fafc'],
                    'subheader' => ['font-size' => '24px', 'color' => '#1a202c', 'background-color' => '#e0e7ff'],
                    'body' => ['padding' => '8px', 'background-color' => '#ffffff'],
                    'image' => ['size' => '160'],
                    'cta' => ['font-size' => '18px', 'color' => '#f0cb50', 'background-color' => '#111824'],
                    'action' => ['font-size' => '16px', 'color' => '#f3f4f6', 'background-color' => '#1f2937'],
                    'footer' => ['font-size' => '18px', 'color' => '', 'background-color' => '#f7fafc'],
                ],
                'campaigns' => ['upsell_popups'],
            ],
            'popup/template-3' => [
                'template' => 'template-3',
                'title' => 'Exclusive offer of the day!',
                'cta_text' => 'Add',
                'styles' => [
                    'content' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'max-width' => 800],
                    'header' => ['font-size' => '28px', 'color' => '#1a202c', 'background-color' => '#f7fafc'],
                    'subheader' => ['font-size' => '24px', 'color' => '#1a202c', 'background-color' => '#dcf9df'],
                    'body' => ['padding' => '8px', 'background-color' => '#ffffff'],
                    'image' => ['size' => '224'],
                    'cta' => ['font-size' => '18px', 'color' => '#ffffff', 'background-color' => '#178a0c'],
                    'action' => ['font-size' => '20px', 'color' => '#ffffff', 'background-color' => '#000000'],
                    'footer' => ['font-size' => '18px', 'color' => '', 'background-color' => '#f7fafc'],
                ],
                'campaigns' => ['upsell_popups'],
            ],
            'popup/template-4' => [
                'template' => 'template-4',
                'title' => 'Exclusive offer of the day!',
                'cta_text' => 'Add',
                'styles' => [
                    'content' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#e2e8f0', 'max-width' => 800],
                    'header' => ['font-size' => '28px', 'color' => '#1a202c', 'background-color' => '#f7fafc'],
                    'subheader' => ['font-size' => '24px', 'color' => '#1a202c', 'background-color' => '#e0e7ff'],
                    'body' => ['padding' => '8px', 'background-color' => '#ffffff'],
                    'image' => ['size' => '200'],
                    'cta' => ['font-size' => '18px', 'color' => '#f0cb50', 'background-color' => '#111824'],
                    'action' => ['font-size' => '16px', 'color' => '#f3f4f6', 'background-color' => '#1f2937'],
                    'footer' => ['font-size' => '18px', 'color' => '', 'background-color' => '#f7fafc'],
                ],
                'campaigns' => ['upsell_popups'],
            ],

            // product addons campaign templates
            'addon/template-1' => [
                'template' => 'template-1',
                'title' => '',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'image' => ['size' => '80'],
                ],
                'campaigns' => ['product_addons'],
            ],
            'addon/template-2' => [
                'template' => 'template-2',
                'title' => '',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'image' => ['size' => '100'],
                ],
                'campaigns' => ['product_addons'],
            ],
            'addon/template-3' => [
                'template' => 'template-3',
                'title' => '',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'image' => ['size' => '120'],
                ],
                'campaigns' => ['product_addons'],
            ],
            'addon/template-4' => [
                'template' => 'template-4',
                'title' => '',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'image' => ['size' => '120'],
                ],
                'campaigns' => ['product_addons'],
            ],

            // cart addons campaign templates
            'addon/cart-template-1' => [
                'title' => 'Add-Ons:',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '0'],
                    'title' => ['font-size' => '', 'color' => ''],
                ],
                'campaigns' => ['cart_addons'],
            ],
            'addon/cart-template-2' => [
                'title' => 'Add-Ons',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#deddda', 'background-color' => '', 'padding' => '12px'],
                    'title' => ['font-size' => '', 'color' => '#6d28d9'],
                ],
                'campaigns' => ['cart_addons'],
            ],
            'addon/cart-template-3' => [
                'title' => 'Add-Ons',
                'styles' => [
                    'template' => ['border-width' => 'thin', 'border-style' => 'solid', 'border-color' => '#deddda', 'background-color' => '', 'padding' => '0'],
                    'title' => ['font-size' => '', 'color' => '#6d28d9'],
                ],
                'campaigns' => ['cart_addons'],
            ],

            // product recommendations campaign templates
            'products/default' => [
                'title' => 'Recommended products',
                'cta_text' => 'Add to cart',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '12px'],
                    'image' => ['size' => '180'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['product_recommendations'],
            ],

            'product-recommendations/template-1' => [
                'title' => 'Recommended products',
                'cta_text' => 'Add to cart',
                'styles' => [
                    'template' => ['border-width' => '0', 'border-style' => 'solid', 'border-color' => '#000000', 'background-color' => '', 'padding' => '12px'],
                    'image' => ['size' => '180'],
                    'title' => ['font-size' => '', 'color' => ''],
                    'cta' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                ],
                'campaigns' => ['product_recommendations'],
            ],
        ]);

        foreach ($templates as $key => $template) {
            $template['template'] = $key;
            $templates[$key] = $template;
        }

        if ($campaign_type !== '') {
            $campaign_templates = [];
            foreach ($templates as $key => $template) {
                if (in_array($campaign_type, $template['campaigns'])) {
                    unset($template['campaigns']);
                    $campaign_templates[$key] = $template;
                }
            }
            return $campaign_templates;
        }
        return $templates;
    }

    /**
     * Returns default template name.
     *
     * @param string $campaign_type
     * @return string
     */
    public static function getDefault($campaign_type = '')
    {
        $default_templates = [
            'checkout_upsells' => 'offer/template-1',
            'cart_upsells' => 'offer/cart-template-1-wide',
            'fbt' => 'fbt/template-1',
            'noc' => 'noc/template-1',
            'double_order' => 'action/simple-action-1',
            'thankyou_upsells' => 'products/template-1',
            'upsell_popups' => 'popup/template-1',
            'product_addons' => 'addon/template-1',
            'cart_addons' => 'addon/cart-template-1',
            'product_recommendations' => 'products/default',
        ];
        $default_template = isset($default_templates[$campaign_type]) ? $default_templates[$campaign_type] : '';
        return apply_filters('cuw_default_template', $default_template, $campaign_type);
    }

    /**
     * Returns default template data.
     *
     * @param string $template
     * @param string $campaign_type
     * @return array
     */
    public static function getDefaultData($template = '', $campaign_type = '')
    {
        $templates_data = self::get($campaign_type);
        if (empty($template)) {
            $template = self::getDefault($campaign_type);
        }
        if (!empty($template) && isset($templates_data[$template])) {
            unset($templates_data[$template]['campaigns']);
            return $templates_data[$template];
        }
        return [];
    }

    /**
     * Get template html.
     *
     * @param int|array $campaign_or_id
     * @param array $extra_data
     * @return string
     */
    public static function getHtml($campaign_or_id, $extra_data = [])
    {
        if (is_numeric($campaign_or_id)) {
            $campaign = CampaignModel::get($campaign_or_id, ['id', 'type', 'data']);
            if ($campaign) {
                $data = $campaign['data'];
                $data['campaign_id'] = $campaign['id'];
                $data['campaign_type'] = $campaign['type'];
            }
        } elseif (is_array($campaign_or_id)) {
            $campaign = $campaign_or_id;
            $data = isset($campaign['data']) ? $campaign['data'] : [];
            $data['campaign_id'] = isset($campaign['id']) ? $campaign['id'] : 0;
            $data['campaign_type'] = isset($campaign['type']) ? $campaign['type'] : '';
        }
        if (!empty($campaign) && !empty($data)) {
            $template_name = Campaign::getTemplateName($campaign);
            $template_data = Template::getDefaultData($template_name);
            $data['template'] = array_merge($template_data, $campaign['data']['template']);
            $processed_data = self::prepareData($data, $campaign);
            if ($processed_data) {
                $params = array_merge(['data' => $processed_data], $extra_data);
                return Core::instance()->template($template_name, $params, false);
            }
        }
        return '';
    }

    /**
     * To get html for preview.
     *
     * @param array $campaign
     */
    public static function getPreviewHtml($campaign)
    {
        $extra_data = [];
        $campaign_type = isset($campaign['type']) ? $campaign['type'] : [];
        if (in_array($campaign_type, apply_filters('cuw_preview_html_campaign_types',['fbt', 'thankyou_upsells', 'upsell_popups', 'product_addons', 'cart_addons', 'product_recommendations']))) {
            $products = [];
            $dummy_data = [
                1 => [
                    'name' => 'T-shirt',
                    'slug' => 't-shirt',
                    'regular_price' => 40,
                    'sale_price' => 35,
                ],
                2 => [
                    'name' => 'Cap',
                    'slug' => 'cap',
                    'regular_price' => 30,
                    'sale_price' => 25,
                ],
                3 => [
                    'name' => 'Sunglasses',
                    'slug' => 'sunglasses',
                    'regular_price' => 20,
                    'sale_price' => 15,
                ],
            ];
            $dummy_products = [];
            foreach ($dummy_data as $key => $data) {
                $product = new \WC_Product();
                $data['id'] = $key;
                $data['type'] = 'simple';
                $product->set_props($data);
                $dummy_products[$key] = $product;
            }
            $dummy_campaign = ['id' => 0, 'type' => $campaign_type];
            $dummy_discount = ['type' => 'fixed_price', 'value' => '5'];
            foreach ($dummy_products as $key => $product) {
                $product_data = Product::getData($product, [
                    'discount' => $dummy_discount,
                    'to_display' => true,
                    'display_in' => 'shop',
                ]);
                $image_url = Assets::getUrl('img/products/' . $dummy_data[$key]['slug'] . '.png');
                $product_data['image'] = '<img src="' . esc_url($image_url) . '">'; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                if (!empty($product_data)) {
                    $product_data['classes'] = [];
                    $product_data['is_main'] = ($key == 1);
                    if ($product_data['is_main']) $product_data['classes'][] = 'is_main';
                    if ($product_data['is_variable']) $product_data['classes'][] = 'is_variable';
                    $products[] = $product_data;
                }
            }

            $extra_data = [
                'products' => $products,
                'campaign' => $dummy_campaign,
                'main_product_id' => 1,
                'trigger' => [
                    'popup_actions' => [
                        'view_cart' => [
                            'text' => __("View cart", 'checkout-upsell-and-order-bumps'),
                        ],
                    ],
                ]
            ];
        }
        return apply_filters('cuw_template_preview_html', str_replace(['<form', '</form>'], ['<div', '</div>'], self::getHtml($campaign, $extra_data)), $campaign);
    }

    /**
     * To get template data.
     *
     * @param array $data
     * @return array|false
     */
    public static function prepareData($data, $campaign)
    {
        // to kept original data before modify
        $original_data = $data;

        // add general details
        $data['is_rtl'] = WP::isRtl();

        // allow to translate template title, description and CTA text
        $data['template']['title'] = !empty($data['template']['title']) ? __($data['template']['title'], 'checkout-upsell-and-order-bumps') : ''; //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
        $data['template']['description'] = !empty($data['template']['description']) ? __($data['template']['description'], 'checkout-upsell-and-order-bumps') : ''; //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
        $data['template']['cta_text'] = !empty($data['template']['cta_text']) ? __($data['template']['cta_text'], 'checkout-upsell-and-order-bumps') : ''; //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText

        // to replace discount text
        if (!empty($data['discount'])) {
            $data['discount']['text'] = self::getText($data['discount']);
            $data['template']['title'] = str_replace('{discount}', $data['discount']['text'], $data['template']['title']);
            $data['template']['description'] = str_replace('{discount}', $data['discount']['text'], $data['template']['description']);
            $data['template']['cta_text'] = str_replace('{discount}', $data['discount']['text'], $data['template']['cta_text']);
        }

        // mark is active or not
        if (!empty($data['campaign_type']) && $data['campaign_type'] == 'double_order') {
            $data['is_active'] = Action::isActive($data['campaign_id']);
        }

        // unslash template data
        $data['template'] = wp_unslash($data['template']);

        // prepare template inline styles
        if (isset($data['template']['styles'])) {
            $data['styles'] = self::prepareInlineStyles($data['template']['styles']);
        }

        $data['allowed_html'] = Input::getAllowedHtmlTags();
        return apply_filters('cuw_template_processed_data', $data, $campaign, $original_data);
    }

    /**
     * Prepare inline styles
     *
     * @param array $styles_data
     * @return array
     */
    public static function prepareInlineStyles($styles_data)
    {
        $inline_styles = [];
        foreach ($styles_data as $section => $styles) {
            $style = '';
            foreach ($styles as $name => $value) {
                if ($name == 'size' && is_numeric($value)) {
                    $style .= 'height: ' . $value . 'px; width: ' . $value . 'px; ';
                    $inline_styles['card'] = 'width: ' . $value . 'px;';
                } elseif (in_array($name, ['height', 'min-height', 'max-height', 'width', 'min-width', 'max-width']) && is_numeric($value)) {
                    $style .= $name . ': ' . $value . 'px; ';
                } elseif ($value != '') {
                    $style .= $name . ': ' . $value . '; ';
                }
            }
            $inline_styles[$section] = rtrim($style);
        }
        return $inline_styles;
    }

    /**
     * Get discount text
     *
     * @param array $discount
     * @return string
     */
    public static function getText($discount)
    {
        $text = '';
        if (isset($discount['type'])) {
            $discount['value'] = isset($discount['value']) ? $discount['value'] : '';
            if (is_numeric($discount['value'])) {
                $discount['value'] = floatval($discount['value']);
            }
            if ($discount['type'] == "percentage") {
                $text = $discount['value'] . '%';
            } elseif ($discount['type'] == "fixed_price") {
                $price = apply_filters('cuw_convert_price', $discount['value'], 'fixed_price');
                $text = html_entity_decode(WC::formatPriceRaw($price, ['trim_zeros' => true]));
            } elseif ($discount['type'] == "free") {
                $text = esc_html__("Free", 'checkout-upsell-and-order-bumps');
            }
        }
        return $text;
    }

    /**
     * Returns translated default texts.
     */
    public static function getDefaultTexts()
    {
        return [
            __('{discount} offer', 'checkout-upsell-and-order-bumps'),
            __('Hey there, you can get this offer by just clicking the checkbox below to add this offer to your order, you will never get such a discount on any other place on this site.', 'checkout-upsell-and-order-bumps'),
            __('Get this exclusive offer now!', 'checkout-upsell-and-order-bumps'),
            __('Offer: {discount}', 'checkout-upsell-and-order-bumps'),
            __('Add to cart', 'checkout-upsell-and-order-bumps'),
            __('Click here to double your order and get a {discount} discount!', 'checkout-upsell-and-order-bumps'),
            __('{discount} Off Your Next Purchase', 'checkout-upsell-and-order-bumps'),
            __('To thank you for being a loyal customer we want to offer you an exclusive voucher for your next order!', 'checkout-upsell-and-order-bumps'),
            __('Go!', 'checkout-upsell-and-order-bumps'),
            __('Frequently bought together', 'checkout-upsell-and-order-bumps'),
            __('Add {items_text} to cart', 'checkout-upsell-and-order-bumps'),
            __('-{price}', 'checkout-upsell-and-order-bumps'),
            __('You may also like…', 'checkout-upsell-and-order-bumps'),
            __('Buy now', 'checkout-upsell-and-order-bumps'),
            __('Wait! Don\'t miss our special deals', 'checkout-upsell-and-order-bumps'),
            __('Add', 'checkout-upsell-and-order-bumps'),
            __('Add-Ons:', 'checkout-upsell-and-order-bumps'),
            __('Recommended products', 'checkout-upsell-and-order-bumps'),
        ];
    }

    /**
     * Checks Whether it is default template.
     *
     * @return boolean
     */
    public static function isDefaultTemplate($template_name) {
      if (strpos($template_name, 'default') !== false) {
          return true;
      }
      return false;
    }
}