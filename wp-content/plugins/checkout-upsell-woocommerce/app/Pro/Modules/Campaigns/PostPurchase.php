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

namespace CUW\App\Pro\Modules\Campaigns;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Offer as OfferModel;
use CUW\App\Pro\Helpers\OfferPage;
use CUW\App\Pro\Helpers\Order;
use CUW\App\Pro\Helpers\Payment;

class PostPurchase extends \CUW\App\Modules\Campaigns\Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'post_purchase';

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        $process_type = self::getProcessType();
        if (is_admin()) {
            // on campaign page
            add_filter('cuw_campaign_notices', [__CLASS__, 'addCampaignNotices'], 10, 2);
            add_filter('cuw_page_localize_campaign_data', [__CLASS__, 'addCampaignPageData'], 10, 2);
            add_action('cuw_campaign_contents', [__CLASS__, 'loadCampaignView'], 10, 2);
            add_action('cuw_after_offer_tabs', [__CLASS__, 'loadCampaignView'], 10, 2);
            add_action('cuw_after_offer_tab_contents', [__CLASS__, 'loadCampaignView'], 10, 2);

            // to add order details on order edit page
            add_action('woocommerce_admin_order_data_after_order_details', [__CLASS__, 'addOrderDetailsOnEditPage']);

            // to add post purchase supported colum in woocommerce payment gateways table
            add_filter('woocommerce_payment_gateways_setting_columns', [__CLASS__, 'addPaymentGatewaysTableColumn'], 1000);
            add_action('woocommerce_payment_gateways_setting_column_cuw_post_purchase', [__CLASS__, 'addPaymentGatewaysTableData']);
        } else {
            // to show offer and its preview
            if (self::isEnabled() || isset($_GET['cuw_offer_preview'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                add_action('template_redirect', [__CLASS__, 'showOffer']);
            }

            if (self::isEnabled()) {
                add_action('init', [__CLASS__, 'initialize']);
                if ($process_type == 'before_payment') {
                    add_action('woocommerce_checkout_order_processed', [__CLASS__, 'beforePayment'], 1000, 3);
                    // add_action('woocommerce_store_api_checkout_order_processed', [__CLASS__, 'beforePayment'], 1000);
                } elseif ($process_type == 'after_payment') {
                    add_action('woocommerce_checkout_order_processed', [__CLASS__, 'initAfterPayment'], 1000, 3);
                    add_action('woocommerce_store_api_checkout_order_processed', [__CLASS__, 'initAfterPayment'], 1000);
                    add_filter('woocommerce_get_checkout_order_received_url', [__CLASS__, 'updateOrderReceivedUrl'], 1000, 2);
                    add_action('woocommerce_before_thankyou', [__CLASS__, 'showTabsOnOrderReceivedPage'], 1);
                }
            }
        }
    }

    /**
     * To show offer page.
     *
     * @hooked template_redirect
     */
    public static function showOffer()
    {
        OfferPage::show();
    }

    /**
     * To add campaign notices.
     *
     * @return array
     */
    public static function addCampaignNotices($notices, $campaign_type)
    {
        if ($campaign_type == 'post_purchase') {
            if ($not_supported_gateways = self::notSupportedPaymentMethods()) {
                $notices[] = [
                    'status' => 'info',
                    'message' => sprintf(__("The following payment gateways do NOT support post-purchase campaigns: %s", 'checkout-upsell-woocommerce'),
                            '<strong>' . esc_html(implode(", ", $not_supported_gateways)) . '</strong>')
                        . '<br>' . sprintf(__("Contact our %s to check possible compatibility with the unsupported payment gateways.", 'checkout-upsell-woocommerce'),
                            '<a href="' . self::app()->plugin->getSupportUrl() . '" target="_blank">' . esc_html__("support team", 'checkout-upsell-woocommerce') . '</a>'),
                ];
            }
        }
        return $notices;
    }

    /**
     * To add page data.
     */
    public static function addCampaignPageData($data, $campaign_type)
    {
        if ($campaign_type == 'post_purchase') {
            $data['data']['offer']['default_page_data'] = OfferPage::defaultPageData();
        }
        return $data;
    }

    /**
     * To load campaign contents
     */
    public static function loadCampaignView($campaign_type, $campaign)
    {
        if ($campaign_type == self::TYPE) {
            self::app()->view('Pro/Admin/Campaign/PostPurchase', ['action' => current_action(), 'campaign' => $campaign]);
        }
    }

    /**
     * Get offer to display
     *
     * @param \WC_Order $order
     * @return array|false
     */
    public static function getOfferToDisplay($order)
    {
        if (!self::isProcessable($order)) {
            return false;
        }

        $campaigns = CampaignModel::all([
            'status' => 'active',
            'type' => 'post_purchase',
            'columns' => ['id', 'conditions'],
            'order_by' => 'priority',
            'sort' => 'asc',
        ]);

        if (!empty($campaigns) && is_array($campaigns)) {
            $data = Order::getData($order);
            foreach ($campaigns as $campaign) {
                $offer = self::pickOffer($campaign, $data);
                if ($offer !== false) {
                    return $offer;
                }
            }
        }
        return false;
    }

    /**
     * Pick offer from campaign
     *
     * @param array $campaign
     * @param array $data
     * @return array|false
     */
    private static function pickOffer($campaign, $data)
    {
        // check conditions
        if (!Campaign::isConditionsPassed($campaign['conditions'], $data)) {
            return false;
        }

        // get active offers
        $active_offers = [];
        $offers = OfferModel::all([
            'campaign_id' => $campaign['id'],
            'columns' => ['id', 'product', 'usage_limit', 'usage_limit_per_user', 'usage_count'],
        ]);
        if (is_array($offers)) {
            foreach ($offers as $offer) {
                if (!Offer::isValid($offer)) {
                    continue;
                }
                $active_offers[] = $offer;
            }
        }
        if (empty($active_offers)) {
            return false;
        }

        // return offer
        foreach ($active_offers as $offer) {
            if (!WC::isPurchasableProduct($offer['product']['id'], $offer['product']['qty'])) {
                continue;
            }
            return $offer;
        }
        return [];
    }

    /**
     * Get process type
     *
     * @return string
     */
    public static function getProcessType()
    {
        return self::app()->config->getSetting('process_post_purchase');
    }

    /**
     * Check if the order is processable or not
     *
     * @param \WC_Order $order
     * @return bool
     */
    public static function isProcessable($order)
    {
        if (!is_object($order) || !is_a($order, '\WC_Order')) {
            return false;
        }
        if (!array_key_exists($order->get_payment_method(), self::supportedPaymentMethods())) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        return apply_filters('cuw_post_purchase_is_order_processable', true, $order, $_POST, self::getProcessType());
    }

    /**
     * List of the supported payment methods
     *
     * @param string|null $process_type
     * @return array
     */
    public static function supportedPaymentMethods($process_type = null)
    {
        $payment_methods = apply_filters('cuw_post_purchase_supported_payment_gateways', [
            'cod' => esc_html__('Cash on delivery', 'woocommerce'),
            'bacs' => esc_html__('Direct bank transfer', 'woocommerce'),
            'cheque' => esc_html__('Check payments', 'woocommerce'),
            'stripe' => esc_html__('Stripe', 'woocommerce-gateway-stripe'), // WooCommerce Stripe Gateway by WooCommerce
            'ppcp-gateway' => esc_html__('PayPal', 'woocommerce-paypal-payments'), // WooCommerce PayPal Payments by WooCommerce
            'authnet' => esc_html__('Authorize.net', 'woo-authorize-net-gateway-aim'), // Authorize.net by Pledged Plugins
        ]);

        $process_type = !empty($process_type) ? $process_type : self::getProcessType();
        if ($process_type == 'before_payment') {
            $payment_methods = array_merge($payment_methods, apply_filters('cuw_post_purchase_before_payment_supported_payment_gateways', [
                'razorpay' => esc_html__('Razorpay', 'woo-razorpay'), // Razorpay for WooCommerce by Team Razorpay
                'stripe_cc' => __('Credit Cards (Stripe) by Payment Plugins', 'woo-stripe-payment'), // Payment Plugins for Stripe WooCommerce by Payment Plugins
                'square_credit_card' => esc_html__('Square Credit Card', 'woocommerce-square'), // WooCommerce Square by WooCommerce
            ]));
        } elseif ($process_type == 'after_payment') {
            $payment_methods = array_merge($payment_methods, apply_filters('cuw_post_purchase_after_payment_supported_payment_gateways', [
                //
            ]));
        }
        return $payment_methods;
    }

    /**
     * List of the not supported payment methods
     *
     * @param string|null $process_type
     * @return array
     */
    public static function notSupportedPaymentMethods($process_type = null)
    {
        $not_supported_gateways = [];
        $available_gateways = Payment::getGateways();
        $supported_gateways = array_keys(self::supportedPaymentMethods($process_type));
        foreach ($available_gateways as $gateway) {
            if (!in_array($gateway->id, $supported_gateways)) {
                $not_supported_gateways[$gateway->id] = !empty($gateway->method_title) ? $gateway->method_title : $gateway->title;
            }
        }
        return $not_supported_gateways;
    }

    /**
     * Show offer before payment
     *
     * @hooked woocommerce_checkout_order_processed|woocommerce_store_api_checkout_order_processed
     */
    public static function beforePayment($order_id, $post_data = [], $order = null)
    {
        if (empty($order)) {
            $order = WC::getOrder($order_id);
            if ($order) {
                $order_id = $order->get_id();
            }
        }

        $offer = self::getOfferToDisplay($order);
        if ($offer) {
            WC::maybeLoadSession(); // set customer session cookie if it is not set
            WC::setSession('order_awaiting_payment', $order_id);
            WC::setSession('cuw_order_awaiting_payment', $order_id);

            self::storeCheckoutPostData();

            if (!self::isOrderProcessed($order)) {
                $offer_page_url = self::getOfferPageUrl($offer['id'], $order_id);
                if ($offer_page_url) {
                    OfferModel::increaseCount($offer['id'], 'display_count');
                    if (did_action('woocommerce_store_api_checkout_order_processed')) {
                        wp_send_json(['payment_result' => ['redirect_url' => $offer_page_url]]);
                    }
                    if (WP::isAjax()) {
                        wp_send_json(['result' => 'success', 'redirect' => $offer_page_url]);
                    }
                    wp_safe_redirect($offer_page_url);
                    exit;
                }
            }
        }
    }

    /**
     * Initialise after payment.
     *
     * @hooked woocommerce_checkout_order_processed|woocommerce_store_api_checkout_order_processed
     */
    public static function initAfterPayment($order_id, $post_data = [], $order = null)
    {
        if (empty($order)) {
            $order = WC::getOrder($order_id);
            if ($order) {
                $order_id = $order->get_id();
            }
        }

        $offer = self::getOfferToDisplay($order);
        if ($offer) {
            WC::maybeLoadSession(); // set customer session cookie if it is not set
            self::storeCheckoutPostData();
            do_action('cuw_init_post_purchase_after_payment', $order, $offer);
            add_filter('woocommerce_get_checkout_order_received_url', [__CLASS__, 'afterPaymentOfferPageUrl'], 1000, 2);
        }
    }

    /**
     * Show offer page by replace order received url.
     *
     * @hooked woocommerce_get_checkout_order_received_url
     */
    public static function afterPaymentOfferPageUrl($url, $order)
    {
        if (!self::isOrderProcessed($order)) {
            $offer = self::getOfferToDisplay($order);
            if ($offer && $offer_page_url = self::getOfferPageUrl($offer['id'], $order->get_id())) {
                OfferModel::increaseCount($offer['id'], 'display_count');
                $url = $offer_page_url;
            }
        }
        return $url;
    }

    /**
     * Get checkout post data
     *
     * @return array|null
     */
    public static function getCheckoutPostData()
    {
        return WC::getSession('cuw_checkout_post_data', null);
    }

    /**
     * Store post data
     *
     * @hooked woocommerce_checkout_order_processed
     */
    public static function storeCheckoutPostData()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        WC::setSession('cuw_checkout_post_data', $_POST);
    }

    /**
     * Get offer page url
     */
    private static function getOfferPageUrl($offer_id, $order_id)
    {
        $offer = OfferModel::get($offer_id, ['data']);
        $page_id = isset($offer['data']['page_id']) ? $offer['data']['page_id'] : null;
        return OfferPage::getUrl($page_id, [
            'cuw_offer_id' => $offer_id,
            'cuw_order_id' => $order_id,
            'cuw_nonce' => WP::createNonce('cuw_nonce'),
        ]);
    }

    /**
     * Check if the order is already processed or not.
     *
     * @param \WC_Order $order
     * @return bool
     */
    private static function isOrderProcessed($order)
    {
        if (is_object($order) && method_exists($order, 'get_meta')) {
            return !empty($order->get_meta('_cuw_offer_processed'));
        }
        return false;
    }

    /**
     * Mark the order is processed by post-purchase.
     */
    private static function setOrderProcessed($order)
    {
        Order::saveMeta($order, ['_cuw_offer_processed' => true]);
    }

    /**
     * To load payments and listen actions
     *
     * @hooked init
     */
    public static function initialize()
    {
        Payment::init();

        if (!WP::isAjax()) {
            self::handleOfferActions();
        }
    }

    /**
     * Handle offer apply actions
     */
    public static function handleOfferActions()
    {
        if (isset($_GET['cuw_offer_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $offer_id = self::app()->input->get('cuw_offer_id', '0', 'query');
            $order_id = self::app()->input->get('cuw_order_id', '0', 'query');
            $action = self::app()->input->get('cuw_action', '', 'params');
            $nonce = self::app()->input->get('cuw_nonce', '', 'query');

            if (empty($nonce) || !WP::verifyNonce($nonce, 'cuw_nonce')) {
                wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
            }

            if (empty($order_id) || empty($order = WC::getOrder($order_id))) {
                wp_die(esc_html__("Invalid offer", 'checkout-upsell-woocommerce'));
            }

            if (self::isOrderProcessed($order)) {
                wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
            }

            $is_awaiting_payment = false;
            $process_type = self::getProcessType();
            if ($process_type == 'before_payment') {
                $is_awaiting_payment = ($order_id == WC::getSession('cuw_order_awaiting_payment'));
                if (!$is_awaiting_payment) {
                    wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
                }

                $already_processed_order_statuses = ['processing', 'completed', 'on-hold', 'failed'];
                if ($is_awaiting_payment && in_array($order->get_status(), $already_processed_order_statuses, true)) {
                    wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
                }
            }

            if (empty($offer_id) || empty($offer = OfferModel::get($offer_id, ['id', 'campaign_id', 'product', 'discount']))) {
                wp_die(esc_html__("Invalid offer", 'checkout-upsell-woocommerce'));
            }

            if (empty($action)) {
                if (self::app()->config->getSetting('show_order_info_notice')) {
                    if ($process_type == 'before_payment') {
                        OfferPage::showOrderStatusNotice(esc_html__('Your order is not processed yet.', 'checkout-upsell-woocommerce'));
                    } elseif ($process_type == 'after_payment') {
                        OfferPage::showOrderStatusNotice(esc_html__('Your order has been received.', 'checkout-upsell-woocommerce'));
                    }
                }
            } elseif ($action == 'accept_offer') {
                $nonce = self::app()->input->get('cuw_nonce', '', 'post');
                if (empty($nonce) || !WP::verifyNonce($nonce, 'cuw_offer_accept')) {
                    wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
                }
                $main_order = $order;
                self::setOrderProcessed($main_order);
                $quantity = self::app()->input->get('quantity', '1', 'post');
                $variation_id = self::app()->input->get('variation_id', '0', 'post');
                $variation_attributes = self::app()->input->get('variation_attributes', [], 'post');
                $offer_data = Offer::prepareMetaData($offer, $quantity, $variation_id, $variation_attributes, 'post_purchase');
                $product_id = $offer_data['product']['variation_id'] > 0 ? $offer_data['product']['variation_id'] : $offer_data['product']['id'];
                $product = WC::getProduct($product_id);
                if ($product && WC::isPurchasableProduct($product)) {
                    if ($process_type == 'after_payment') {
                        $order = Order::generateOfferOrder($order, $product, $offer_data);
                        if (empty($order)) {
                            Payment::handleError(esc_html__("Unable to create an offer order", 'checkout-upsell-woocommerce'));
                        }
                        Order::saveStats($order);
                    } else {
                        Order::addOffer($order, $product, $offer_data);
                        Order::saveStats($order, true);
                    }
                    do_action('cuw_post_purchase_offer_added_to_order', $order, $product, $offer_data);
                }

                if ($order->get_total() > 0) {
                    self::processPayment($order);
                } else {
                    self::processWithoutPayment($order, $process_type == 'after_payment' ? $main_order : null);
                }
            } elseif ($action == 'decline_offer') {
                self::setOrderProcessed($order);
                if ($is_awaiting_payment && $process_type == 'before_payment') {
                    self::processPayment($order);
                } elseif ($process_type == 'after_payment') {
                    self::redirectAfterPayment($order, [
                        'result' => 'success',
                        'redirect' => $order->get_checkout_order_received_url(),
                    ]);
                }
            }
        }
    }

    /**
     * To process payment.
     *
     * @param int|\WC_Order $order_or_id
     * @return void
     */
    public static function processPayment($order_or_id)
    {
        $order = WC::getOrder($order_or_id);
        if (empty($order)) {
            return;
        }

        $post_data = self::getCheckoutPostData();
        $_POST = $post_data; // override $_POST data with previously posted data

        do_action('cuw_before_process_order_payment', $order, $post_data);

        $order_id = $order->get_id();
        $payment_method = $order->get_payment_method();
        $payment_gateway = Payment::getGateway($payment_method);

        $result = apply_filters('cuw_process_order_payment', array(), $order, $payment_gateway, self::getProcessType());
        if (empty($result) && !empty($payment_gateway) && self::getProcessType() == 'after_payment') {
            $result = Payment::handle($order, $payment_gateway);
        }
        if (empty($result)) {
            if (empty($payment_gateway)) {
                wp_die(esc_html__("Unable to process payment", 'checkout-upsell-woocommerce'));
            }
            $result = $payment_gateway->process_payment($order_id);
        }

        if ($order_id == WC::getSession('cuw_order_awaiting_payment')) {
            WC::setSession('cuw_order_awaiting_payment', false);
        }

        self::redirectAfterPayment($order, $result);
    }

    /**
     * Skip payment.
     *
     * @param \WC_Order $order
     * @param \WC_Order $main_order
     * @return void
     */
    public static function processWithoutPayment($order, $main_order)
    {
        try {
            $order->set_payment_method();
        } catch (\Exception $e) {
        }
        Payment::handleSuccess($order, '', $main_order, true);
    }

    /**
     * Redirect to order received page
     *
     * @param \WC_Order $order
     * @param array $result
     * @return void
     */
    public static function redirectAfterPayment($order, $result = [])
    {
        if (!empty($result['force_redirect']) && !empty($result['redirect'])) {
            wp_redirect($result['redirect']); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
            exit;
        }

        if (isset($result['result'])) {
            if ($result['result'] === 'success') {
                $order_id = $order->get_id();
                $result['order_id'] = $order_id;
                if ($main_order = Order::getMainOrder($order)) {
                    $result = Payment::parseOfferPaymentSuccessfulResult($result, $order, $main_order);
                }
                $result = apply_filters('woocommerce_payment_successful_result', $result, $order_id);
                $result = Payment::parseSuccessfulResult($result, $order);
            } else {
                $result = Payment::parseFailureResult($result, $order);
            }
            wp_redirect($result['redirect']); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
            exit;
        }
        Payment::handleError();
    }

    /**
     * To update order received url.
     *
     * @hooked woocommerce_get_checkout_order_received_url
     */
    public static function updateOrderReceivedUrl($url, $order)
    {
        if ($offer_order = Order::getOfferOrder($order)) {
            $url = add_query_arg('cuw_offer_order', $offer_order->get_id(), $url);
        } elseif ($main_order = Order::getMainOrder($order)) {
            $url = add_query_arg('cuw_main_order', $main_order->get_id(), $url);
        }
        return $url;
    }

    /**
     * To show tabs (links) on order-received page.
     *
     * @hooked woocommerce_before_thankyou
     */
    public static function showTabsOnOrderReceivedPage($order_id)
    {
        $order = WC::getOrder($order_id);
        if (empty($order)) {
            return;
        }

        $main_order = $offer_order = $current_page = null;
        if (isset($_GET['cuw_offer_order']) && $offer_order = Order::getOfferOrder($order)) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $main_order = $order;
            $current_page = 'main_order';
        } elseif (isset($_GET['cuw_main_order']) && $main_order = Order::getMainOrder($order)) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $offer_order = $order;
            $current_page = 'offer_order';
        }
        if ($main_order && $offer_order && $current_page) {
            self::app()->template('page/order-tabs', compact('main_order', 'offer_order', 'current_page'));
        }
    }

    /**
     * To add extra order details on order edit page.
     *
     * @hooked woocommerce_admin_order_data_after_order_details
     */
    public static function addOrderDetailsOnEditPage($order)
    {
        if ($offer_order = Order::getOfferOrder($order)) { ?>
            <p class="form-field form-field-wide cuw-main-order" style="margin-top: 20px;">
            <strong><?php esc_html_e("Offer order", 'checkout-upsell-woocommerce'); ?>:</strong>
            <span style="display: block;">
                    <a href="<?php echo esc_url($offer_order->get_edit_order_url()); ?>">
                        <?php echo '#' . esc_html($offer_order->get_order_number()); ?>
                    </a>
                </span>
            </p><?php
        } elseif ($main_order = Order::getMainOrder($order)) { ?>
            <p class="form-field form-field-wide cuw-main-order" style="margin-top: 20px;">
            <strong><?php esc_html_e("Main order", 'checkout-upsell-woocommerce'); ?>:</strong>
            <span style="display: block;">
                    <a href="<?php echo esc_url($main_order->get_edit_order_url()); ?>">
                        <?php echo '#' . esc_html($main_order->get_order_number()); ?>
                    </a>
                </span>
            </p><?php
        }
    }

    /**
     * To add payment gateways table column
     *
     * @hooked woocommerce_payment_gateways_setting_columns
     */
    public static function addPaymentGatewaysTableColumn($columns)
    {
        $title = esc_html__("Post-purchase Supported", 'checkout-upsell-woocommerce');
        return array_slice($columns, 0, count($columns) - 1, true)
            + array('cuw_post_purchase' => $title)
            + array_slice($columns, count($columns) - 1, 1, true);
    }

    /**
     * To add payment gateways table data
     *
     * @hooked woocommerce_payment_gateways_setting_column_cuw_post_purchase
     */
    public static function addPaymentGatewaysTableData($gateway)
    {
        echo '<td class="cuw-post-purchase-supported">';
        if (array_key_exists($gateway->id, self::supportedPaymentMethods())) {
            echo '<span class="status-enabled">' . esc_html__("Yes", 'checkout-upsell-woocommerce') . '</span>';
        } else {
            echo '-';
        }
        echo '</td>';
    }
}