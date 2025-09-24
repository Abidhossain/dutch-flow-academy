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

use CUW\App\Core;
use CUW\App\Helpers\WC;
use CUW\App\Pro\Helpers\Order;
use CUW\App\Pro\Helpers\Payment;

defined('ABSPATH') || exit;

class PayPal extends Base
{
    /**
     * To add hooks.
     */
    public function init()
    {
        add_filter('cuw_post_purchase_is_order_processable', function ($processable, $order, $post_data, $process_type) {
            if ($process_type == 'after_payment' && !empty($post_data['ppcp-funding-source']) && $post_data['ppcp-funding-source'] == 'card') {
                $processable = false;
            }
            return $processable;
        }, 100, 4);
    }

    /**
     * To process payment.
     *
     * @param \WC_Order $order
     * @param object $gateway
     * @return array
     */
    public function process($order, $gateway)
    {
        $result = ['result' => 'fail'];
        $main_order = Order::getMainOrder($order);
        $payment_mode = $main_order->get_meta('_ppcp_paypal_payment_mode');
        $payment_intent = $main_order->get_meta('_ppcp_paypal_intent');
        $payment_settings = get_option('woocommerce-ppcp-settings');
        if (empty($payment_mode) || empty($payment_settings)) {
            return $result;
        }

        $api_url = self::getApiUrl($payment_mode);
        $token = self::getToken($api_url, $payment_settings);
        if (empty($api_url) || empty($token)) {
            return $result;
        }

        $order_data = Order::getData($order, true, true);

        $url = $api_url . '/v2/checkout/orders';
        $response = wp_remote_get($url, [
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'PayPal-Partner-Attribution-Id' => 'Wcf_Woo_PPCP',
            ),
            'body' => wp_json_encode(array(
                'intent' => $payment_intent,
                'purchase_units' => self::preparePurchaseUnits($order, $order_data, $payment_settings),
                'application_context' => array(
                    'user_action' => 'CONTINUE',
                    'landing_page' => 'LOGIN',
                    'brand_name' => html_entity_decode(get_bloginfo('name'), ENT_NOQUOTES, 'UTF-8'),
                    'return_url' => add_query_arg('cuw_capture_paypal_payment', $order->get_id(), wc_get_checkout_url()),
                    'cancel_url' => remove_query_arg('cuw_offer_order', $main_order->get_checkout_order_received_url()),
                ),
                'payment_method' => array(
                    'payee_preferred' => 'UNRESTRICTED',
                    'payer_selected' => 'PAYPAL',
                ),
                'payment_instruction' => array(
                    'disbursement_mode' => 'INSTANT',
                    'platform_fees' => array(
                        array(
                            'amount' => array(
                                'currency_code' => $order->get_currency(),
                                'value' => self::formatAmount($order_data['total']),
                            ),
                        ),
                    ),
                ),
            )),
        ]);
        if (is_wp_error($response)) {
            $result['message'] = $response->get_error_message();
        } else {
            $payment = json_decode(wp_remote_retrieve_body($response));
            if (isset($payment->status) && $payment->status === 'CREATED') {
                $approve_link = $payment->links[1]->href;
                Order::saveMeta($order, [
                    '_ppcp_paypal_order_id' => $payment->id,
                    '_ppcp_paypal_payment_mode' => $payment_mode,
                ]);
                $result = [
                    'result' => 'success',
                    'redirect' => $approve_link,
                    'force_redirect' => true,
                ];
            }
        }
        return $result;
    }

    /**
     * To capture payment.
     *
     * @return void
     */
    public function capture()
    {
        if (!isset($_GET['cuw_capture_paypal_payment'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $order_id = Core::instance()->input->get('cuw_capture_paypal_payment', '', 'query');
        if ($order = WC::getOrder($order_id)) {
            $main_order = Order::getMainOrder($order);

            $payment_id = $order->get_meta('_ppcp_paypal_order_id');
            $payment_mode = $order->get_meta('_ppcp_paypal_payment_mode');
            $payment_settings = get_option('woocommerce-ppcp-settings');
            if (empty(empty($payment_mode) || $payment_settings)) {
                return;
            }

            $api_url = self::getApiUrl($payment_mode);
            $token = self::getToken($api_url, $payment_settings);
            if (empty($api_url) || empty($token)) {
                return;
            }

            $capture_url = $api_url . '/v2/checkout/orders/' . $payment_id . '/capture';
            $response = wp_remote_get($capture_url, array(
                'method' => 'POST',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation',
                    'PayPal-Partner-Attribution-Id' => 'Wcf_Woo_PPCP',
                ),
            ));
            if (!is_wp_error($response)) {
                $body = json_decode(wp_remote_retrieve_body($response));
                if (isset($body->status) && $body->status == 'COMPLETED') {
                    $txn_id = $body->purchase_units[0]->payments->captures[0]->id;
                    Payment::handleSuccess($order, $txn_id, $main_order, true);
                }
            }

            Payment::handleFailure($order, $main_order, true);
        }
    }

    /**
     * Get API URl.
     *
     * @param string $payment_mode
     * @return string
     */
    private static function getApiUrl($payment_mode)
    {
        $url = 'sandbox' === $payment_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        return apply_filters('cuw_paypal_payment_api_url', $url, $payment_mode);
    }

    /**
     * Get access token.
     *
     * @param string $api_url
     * @param array $settings
     * @return string
     */
    private static function getToken($api_url, $settings)
    {
        $token = '';
        $bearer = get_transient('ppcp-paypal-bearerppcp-bearer');
        if (!empty($bearer)) {
            $bearer = json_decode($bearer);
            $token = $bearer->access_token;
        }

        if (empty($token)) {
            $url = $api_url . '/v1/oauth2/token?grant_type=client_credentials';
            $response = wp_remote_get($url, [
                'method' => 'POST',
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($settings['client_id'] . ':' . $settings['client_secret']),
                ),
            ]);
            if (!is_wp_error($response)) {
                $body = json_decode($response['body']);
                $token = $body->access_token;
            }
        }
        return $token;
    }

    /**
     * Prepare purchase units.
     *
     * @param \WC_Order $order
     * @param array $order_data
     * @param array $payment_settings
     * @return array
     */
    private static function preparePurchaseUnits($order, $order_data, $payment_settings)
    {
        $invoice_id = rtrim($payment_settings['prefix'], '-') . '-cuw-' . $order->get_id();
        $order_amount = self::formatAmount($order_data['total']);
        $order_currency = $order->get_currency();

        $order_items = [];
        foreach ($order_data['products'] as $product) {
            $order_items[] = array(
                'name' => self::formatText($product['object']->get_title()),
                'unit_amount' => array(
                    'currency_code' => $order_currency,
                    'value' => self::formatAmount($product['subtotal'] + $product['subtotal_tax']),
                ),
                'quantity' => $product['quantity'],
                'description' => self::formatText($product['object']->get_description()),
            );
        }

        return array(
            array(
                'reference_id' => 'default',
                'amount' => array(
                    'currency_code' => $order_currency,
                    'value' => $order_amount,
                    'breakdown' => array(
                        'item_total' => array(
                            'currency_code' => $order_currency,
                            'value' => $order_amount,
                        ),
                    ),
                ),
                'description' => 'Upsell order - ' . $order->get_id(),
                'items' => $order_items,
                'payee' => array(
                    'email_address' => $payment_settings['merchant_email'],
                    'merchant_id' => $payment_settings['merchant_id'],
                ),
                'shipping' => array(
                    'name' => array(
                        'full_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    ),
                ),
                'custom_id' => $invoice_id,
                'invoice_id' => $invoice_id,
            )
        );
    }

    /**
     * Format amount value.
     *
     * @param $amount int|float|string
     * @return string
     */
    private static function formatAmount($amount)
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Format texts.
     *
     * @param $text string
     * @return string
     */
    private static function formatText($text)
    {
        return wp_strip_all_tags(strlen($text) > 125 ? substr($text, 0, 125) . '..' : $text);
    }
}