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

namespace CUW\App\Pro\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Input;
use CUW\App\Helpers\WP;
use CUW\App\Pro\Helpers\OfferPage;

class Shortcodes extends Controller
{
    /**
     * To get shortcodes.
     *
     * @return array
     */
    public static function get()
    {
        return [
            'notices' => [
                'title' => __('Notices', 'checkout-upsell-woocommerce'),
                'description' => __('To show notices (woocommerce)', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'notices'],
            ],
            'offer_expire_notice' => [
                'title' => __('Offer expire notice', 'checkout-upsell-woocommerce'),
                'description' => __('To show the offer expiration notice with a countdown timer', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerExpireNotice'],
            ],
            'offer_expire_message' => [
                'title' => __('Offer expire message', 'checkout-upsell-woocommerce'),
                'description' => __('To show the offer expiration message instead of the notice', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerExpireMessage'],
            ],
            'offer_title' => [
                'title' => __('Offer title', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer title', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerTitle'],
            ],
            'offer_price' => [
                'title' => __('Offer price', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer price (product sale price)', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productPrice'],
            ],
            'offer_description' => [
                'title' => __('Offer description', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer description', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerDescription'],
            ],
            'offer_form' => [
                'title' => __('Offer form', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer form that contains inputs like quantity and accept offer button', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerForm'],
            ],
            'product_quantity' => [
                'title' => __('Product quantity', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer product quantity text or input', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productQuantity'],
            ],
            'product_variants' => [
                'title' => __('Product variants', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer product variant select', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productVariants'],
            ],
            'offer_accept_button' => [
                'title' => __('Offer accept button', 'checkout-upsell-woocommerce'),
                'description' => __('To show accept offer CTA button', 'checkout-upsell-woocommerce')
                    . '<br><span class="form-text text-dark">' . __('NOTE: To work properly, you should keep both the [cuw_product_quantity] and [cuw_product_variants] shortcodes in offer page.', 'checkout-upsell-woocommerce') . '</span>',
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerAcceptButton'],
            ],
            'offer_decline_link' => [
                'title' => __('Offer decline link', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer decline or skip link', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerDeclineLink'],
            ],
            'offer_decline_url' => [
                'title' => __('Offer decline URL', 'checkout-upsell-woocommerce'),
                'description' => __('To get offer decline or skip URL', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'offerDeclineUrl'],
            ],
            'product_title' => [
                'title' => __('Offer product title', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer product title', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productTitle'],
            ],
            'product_image' => [
                'title' => __('Offer product image', 'checkout-upsell-woocommerce'),
                'description' => __('To show offer product image', 'checkout-upsell-woocommerce'),
                'group' => __('Post-purchase offer page', 'checkout-upsell-woocommerce'),
                'callback' => [__CLASS__, 'productImage'],
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
     * Load offer title
     */
    public static function offerTitle()
    {
        $allowed_html = Input::getAllowedHtmlTags();
        $title = wp_kses((string)OfferPage::getOfferData('template.title', ''), $allowed_html);
        return sprintf('<span class="cuw-page-offer-title">%s</span>', $title);
    }

    /**
     * Load offer description
     */
    public static function offerDescription()
    {
        $allowed_html = Input::getAllowedHtmlTags();
        $description = wp_kses((string)OfferPage::getOfferData('template.description', ''), $allowed_html);
        return sprintf('<span class="cuw-page-offer-description">%s</span>', $description);
    }

    /**
     * Load offer CTA text
     */
    public static function offerCtaText()
    {
        $allowed_html = Input::getAllowedHtmlTags();
        $cta_text = wp_kses((string)OfferPage::getOfferData('template.cta_text', ''), $allowed_html);
        return sprintf('<span class="cuw-page-offer-cta-text">%s</span>', $cta_text);
    }

    /**
     * Load offer product title
     */
    public static function productTitle()
    {
        $title = wp_kses_post((string)OfferPage::getOfferData('product.title', ''));
        return sprintf('<span class="cuw-page-product-title">%s</span>', $title);
    }

    /**
     * Load offer price
     */
    public static function productPrice()
    {
        $offer = OfferPage::getOfferData();
        if (!empty($offer) && isset($offer['product']['price_html'])) {
            $price_html = $offer['product']['price_html'];
            if (!empty($offer['product']['default_variant']['price_html'])) {
                $price_html = $offer['product']['default_variant']['price_html'];
            }
            return sprintf('<span class="cuw-page-product-price">%s</span>', $price_html);
        }
        return '';
    }

    /**
     * Load offer image
     */
    public static function productImage()
    {
        $offer = OfferPage::getOfferData();
        if (!empty($offer) && isset($offer['product']['image'])) {
            $image = $offer['product']['image'];
            if (!empty($offer['product']['default_variant']['image'])) {
                $image = $offer['product']['default_variant']['image'];
            }
            return sprintf('<div class="cuw-page-product-image">%s</div>', $image);
        }
        return '';
    }

    /**
     * Load offer form
     */
    public static function offerForm()
    {
        $offer = OfferPage::getOfferData();
        if ($offer) {
            return self::app()->template('page/offer-form', [
                'offer' => $offer,
                'nonce' => WP::createNonce('cuw_offer_accept'),
                'allowed_html' => Input::getAllowedHtmlTags(),
            ], false);
        }
        return '';
    }

    /**
     * Load offer quantity input
     */
    public static function productQuantity()
    {
        $offer = OfferPage::getOfferData();
        if ($offer) {
            return self::app()->template('page/product-quantity', ['offer' => $offer], false);
        }
        return '';
    }

    /**
     * Load offer variant input
     */
    public static function productVariants()
    {
        $offer = OfferPage::getOfferData();
        if ($offer) {
            return self::app()->template('page/product-variants', ['offer' => $offer], false);
        }
        return '';
    }

    /**
     * Load offer accept button
     */
    public static function offerAcceptButton()
    {
        $offer = OfferPage::getOfferData();
        if ($offer) {
            return self::app()->template('page/offer-accept-button', [
                'offer' => $offer,
                'nonce' => WP::createNonce('cuw_offer_accept'),
                'allowed_html' => Input::getAllowedHtmlTags(),
            ], false);
        }
        return '';
    }

    /**
     * Load offer decline link
     */
    public static function offerDeclineLink()
    {
        $offer = OfferPage::getOfferData();
        if ($offer) {
            return self::app()->template('page/offer-decline-link', ['offer' => $offer], false);
        }
        return '';
    }

    /**
     * Decline offer
     */
    public static function offerDeclineUrl()
    {
        if (OfferPage::getOfferId()) {
            return WP::getCurrentPageUrl(true, ['cuw_action' => 'decline_offer']);
        }
        return '#';
    }

    /**
     * Print woocommerce notices
     */
    public static function notices()
    {
        if (did_action('woocommerce_init')) {
            if (function_exists('woocommerce_output_all_notices') && function_exists('wc_print_notices')) {
                ob_start();
                woocommerce_output_all_notices();
                return ob_get_clean();
            }
        }
        return '';
    }

    /**
     * To show offer expire notice.
     */
    public static function offerExpireNotice()
    {
        if (function_exists('wc_print_notice') && $message = self::offerExpireMessage()) {
            return wc_print_notice($message, 'notice', [], true);
        }
        return '';
    }

    /**
     * To show offer expire message.
     */
    public static function offerExpireMessage()
    {
        $data = OfferPage::getCampaignData();
        if ($data && !empty($data['timer']['enabled'])) {
            $minutes = $data['timer']['minutes'];
            $seconds = $data['timer']['seconds'];
            __('Offer expires in: <strong>{minutes}:{seconds}</strong>', 'checkout-upsell-woocommerce');
            $message = __($data['timer']['message'], 'checkout-upsell-woocommerce');
            $formatted_minutes = ($minutes < 10 ? '0' . $minutes : $minutes);
            $formatted_seconds = ($seconds < 10 ? '0' . $seconds : $seconds);

            // restore the timer from a cookie when we refresh the page
            $order_id = self::app()->input->get('cuw_order_id', '', 'query');
            if (!empty($order_id) && $duration = self::app()->input->get(('cuw_timer_' . $order_id), '', 'cookie')) {
                $formatted_minutes = gmdate('i', (int)$duration);
                $formatted_seconds = gmdate('s', (int)$duration);
            }

            $text = str_replace(['{minutes}', '{seconds}'], [$formatted_minutes, $formatted_seconds], $message);
            return sprintf(
                '<span class="cuw-page-timer" data-minutes="%s" data-seconds="%s" data-message="%s" data-redirect="%s">%s</span>',
                $minutes, $seconds, esc_html($message), esc_url(self::offerDeclineUrl()), $text
            );
        }
        return '';
    }
}