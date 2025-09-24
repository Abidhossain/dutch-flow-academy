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

use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Pro\Modules\Payments;

defined('ABSPATH') || exit;

class Payment
{
    /**
     * Initialized or not.
     *
     * @var bool
     */
    public static $loaded;

    /**
     * To hold payments.
     *
     * @var array
     */
    public static $payments;

    /**
     * Get payments.
     *
     * @param string $payment_method
     * @return array|false
     */
    public static function get($payment_method = '')
    {
        if (!isset(self::$payments)) {
            self::$payments = apply_filters('cuw_payments', [
                'stripe' => [
                    'name' => 'Stripe',
                    'handler' => new Payments\Stripe(),
                ],
                'ppcp-gateway' => [
                    'name' => 'PayPal',
                    'handler' => new Payments\PayPal(),
                ],
                'stripe_cc' => [
                    'name' => 'StripeCC',
                    'handler' => new Payments\StripeCC(),
                ],
            ]);
        }
        if ($payment_method !== '') {
            return isset(self::$payments[$payment_method]) ? self::$payments[$payment_method] : false;
        }
        return self::$payments;
    }

    /**
     * Get payment gateway.
     *
     * @param string $payment_method
     * @return object|null
     */
    public static function getGateway($payment_method)
    {
        $gateways = self::getGateways();
        return isset($gateways[$payment_method]) ? $gateways[$payment_method] : null;
    }

    /**
     * Get available payment gateways.
     *
     * @return array
     */
    public static function getGateways()
    {
        if (function_exists('WC') && method_exists(WC(), 'payment_gateways')) {
            return WC()->payment_gateways()->get_available_payment_gateways();
        }
        return [];
    }

    /**
     * Initialize payments.
     *
     * @return void
     */
    public static function init()
    {
        if (!isset(self::$loaded)) {
            foreach (self::get() as $payment) {
                if (!empty($payment['handler']) && is_a($payment['handler'], 'CUW\App\Pro\Modules\Payments\Base')) {
                    $payment['handler']->init();
                }
            }
            if (!WP::isAjax()) {
                self::capture();
            }
            self::$loaded = true;
        }
    }

    /**
     * Handle payments.
     *
     * @param \WC_Order $order
     * @param object $gateway
     * @return array
     */
    public static function handle($order, $gateway)
    {
        $result = [];
        $payment = self::get($order->get_payment_method());
        if (!empty($payment['handler']) && is_a($payment['handler'], 'CUW\App\Pro\Modules\Payments\Base')) {
            $result = $payment['handler']->process($order, $gateway);
        }
        return $result;
    }

    /**
     * Capture payments.
     *
     * @return void
     */
    private static function capture()
    {
        foreach (self::get() as $payment) {
            if (!empty($payment['handler']) && is_a($payment['handler'], 'CUW\App\Pro\Modules\Payments\Base')) {
                $payment['handler']->capture();
            }
        }
    }

    /**
     * Parse offer order payment successful result.
     *
     * @param array $result
     * @param \WC_Order $order
     * @param \WC_Order $main_order
     * @return array
     */
    public static function parseOfferPaymentSuccessfulResult($result, $order, $main_order)
    {
        $result['redirect'] = $main_order->get_checkout_order_received_url();
        return apply_filters('cuw_offer_payment_successful_result', $result, $order, $main_order);
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
        return apply_filters('cuw_payment_successful_result', $result, $order);
    }

    /**
     * Parse payment fail result.
     *
     * @param array $result
     * @param \WC_Order $order
     * @return array
     */
    public static function parseFailureResult($result, $order)
    {
        if ($main_order = Order::getMainOrder($order)) {
            $result['redirect'] = self::handleFailure($order, $main_order);
        }
        if (empty($result['redirect'])) {
            $result['redirect'] = wc_get_checkout_url();
        }
        return apply_filters('cuw_payment_failure_result', $result, $order);
    }

    /**
     * Handle payment success.
     *
     * @param \WC_Order $order
     * @param string $transaction_id
     * @param \WC_Order|null $main_order
     * @param bool $redirect
     * @return string|void
     */
    public static function handleSuccess($order, $transaction_id = '', $main_order = null, $redirect = false)
    {
        $order->payment_complete($transaction_id);
        if (!empty($main_order) || $main_order = Order::getMainOrder($order)) {
            $redirect_url = add_query_arg('cuw_offer_payment', 'success', $main_order->get_checkout_order_received_url());
        } else {
            $redirect_url = $order->get_checkout_order_received_url();
        }
        if ($redirect) {
            wp_redirect($redirect_url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
            exit;
        }
        return $redirect_url;
    }

    /**
     * Handle payment failure.
     *
     * @param \WC_Order $order
     * @param \WC_Order|null $main_order
     * @param bool $redirect
     * @return string|void
     */
    public static function handleFailure($order, $main_order = null, $redirect = false)
    {
        $order->set_status('failed');
        $order->save();
        if (!empty($main_order) || $main_order = Order::getMainOrder($order)) {
            $redirect_url = add_query_arg('cuw_offer_payment', 'failed', $main_order->get_checkout_order_received_url());
        } else {
            $redirect_url = $order->get_checkout_order_received_url();
        }
        if ($redirect) {
            wp_redirect($redirect_url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
            exit;
        }
        return $redirect_url;
    }

    /**
     * Handle error.
     *
     * @param string $message
     * @return void
     */
    public static function handleError($message = '')
    {
        if (empty($message)) {
            $message = __("Oops! Something went wrong.", 'checkout-upsell-woocommerce');
        }

        WC::addNotice($message, 'error');
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}