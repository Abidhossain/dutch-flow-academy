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

namespace CUW\App\Pro\Modules\Payments;

defined('ABSPATH') || exit;

class StripeCC extends Base
{
    /**
     * To add hooks.
     */
    public function init() {
        add_filter('cuw_payment_successful_result', [__CLASS__, 'parseSuccessfulResult'], 10, 2);
    }

    /**
     * To capture payment.
     *
     * @return void
     */
    public function capture() {
        if (!empty($_GET['cuw_capture_stripe_cc_payment'])) {
            add_action('wp_head', function () {
                ?>
                <style>
                    #cuw-overlay {
                        position: fixed;
                        width: 100%;
                        height: 100%;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        z-index: 10000;
                        background: #fff;
                        cursor: pointer
                    }
                </style><?php
            }, 1);
            add_action('wp_body_open', function () { ?>
                <div id="cuw-overlay"></div><?php
            }, 1);
            add_action('wp_footer', function () {
                ?>
                <script>
                    jQuery(document).ready(function ($) {
                        $("#cuw-overlay").block({
                            message: null,
                            overlayCSS: {'background': '#fff', 'z-index': 10001, 'opacity': 1}
                        })
                    })
                </script><?php
            }, 1);
            add_action('wp_footer', function () { ?>
                <script>
                    jQuery(document).ready(function () {
                        window.location.hash = '#response=' + (new URLSearchParams(window.location.search)).get('cuw_capture_stripe_cc_payment');
                        jQuery(document).trigger('hashchange');
                    });
                </script>
            <?php }, 1);
        }
    }

    /**
     * Parse payment successful result.
     *
     * @param array $result
     * @param \WC_Order $order
     * @return array
     */
    public static function parseSuccessfulResult($result, $order)
    {
        if ($order->get_payment_method() == 'stripe_cc') {
            if (!empty($result['redirect']) && substr($result['redirect'], 0, 1) == '#' && function_exists('wc_get_checkout_url')) {
                $result['redirect'] = add_query_arg('cuw_capture_stripe_cc_payment', str_replace('#response=', '', $result['redirect']), wc_get_checkout_url());
            }
        }
        return $result;
    }

    /**
     * To process payment.
     *
     * @param \WC_Order $order
     * @param object $gateway
     */
    public function process($order, $gateway)
    {
        //silence is golden
    }
}
