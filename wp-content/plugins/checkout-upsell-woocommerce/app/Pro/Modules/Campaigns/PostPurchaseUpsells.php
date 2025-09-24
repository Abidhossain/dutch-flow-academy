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

use CUW\App\Helpers\Input;
use CUW\App\Helpers\Offer as OfferHelper;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Offer as OfferModel;
use CUW\App\Modules\Campaigns\Base;
use CUW\App\Pro\Helpers\Order;
use CUW\App\Pro\Helpers\Payment;
use CUW\App\Pro\Modules\PostPurchase\Offer;
use CUW\App\Pro\Modules\PostPurchase\Page;
use CUW\App\Pro\Modules\PostPurchase\Shortcodes;
use CUW\App\Pro\Modules\PostPurchase\Templates;

defined('ABSPATH') || exit;

class PostPurchaseUpsells extends Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'post_purchase_upsells';

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            // on campaign page
            add_filter('cuw_campaign_notices', [__CLASS__, 'addCampaignNotices'], 10, 3);
            add_action('cuw_campaign_contents', [__CLASS__, 'loadCampaignView'], 10, 2);
            add_filter('cuw_hide_campaign_form', [__CLASS__, 'hideCampaignForm'], 10, 3);
            add_action('cuw_after_campaign_page', [__CLASS__, 'loadCampaignView'], 10, 2);
            add_filter('cuw_page_localize_campaign_data', [__CLASS__, 'addCampaignPageData'], 10, 2);
            add_action('cuw_before_campaign_page_load', [__CLASS__, 'loadExtraAssets']);
            add_filter('cuw_process_offer_data_before_save', [__CLASS__, 'beforeOfferDataSave'], 10, 2);
            add_filter('cuw_campaign_data_before_duplicate', [__CLASS__, 'duplicateCampaignData'], 10, 1);
        } else {
            $process_type = PostPurchase::getProcessType();
            if (self::isEnabled()) {
                if (empty(WP::getCurrentUserId()) && empty(Input::get('cuw_ppu_offer', '', 'cookie'))) {
                    setcookie('cuw_ppu_offer', time() ,time() + ( 5 * 60 ), '/');
                }
                add_action('init', [__CLASS__, 'initialize'], 20);
                if ($process_type == 'before_payment') {
                    add_action('woocommerce_checkout_order_processed', [__CLASS__, 'beforePaymentRedirect'], 1000, 3);
                    add_action('woocommerce_store_api_checkout_order_processed', [__CLASS__, 'beforePaymentRedirect'], 1000);
                } elseif ($process_type == 'after_payment') {
                    add_action('woocommerce_checkout_order_processed', [__CLASS__, 'initAfterPayment'], 1000, 3);
                    add_action('woocommerce_store_api_checkout_order_processed', [__CLASS__, 'initAfterPayment'], 1000);
                    add_filter('woocommerce_get_checkout_order_received_url', [PostPurchase::class, 'updateOrderReceivedUrl'], 1000, 2);
                    add_action('woocommerce_before_thankyou', [PostPurchase::class, 'showTabsOnOrderReceivedPage'], 1);
                }
            }
        }

        Shortcodes::load();
        add_action('init', [Templates::class, 'registerPostTypes']);
        add_action('wp', [Page::class, 'init']);
    }

    /**
     * To load payments and listen actions.
     *
     * @hooked init
     */
    public static function initialize()
    {
        Payment::init();

        if (!empty($_GET['cuw_ppu_offer']) && !WP::isAjax()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            self::handleRequests();
        }
    }

    /**
     * To add campaign notices.
     *
     * @return array
     */
    public static function addCampaignNotices($notices, $campaign_type, $campaign_id)
    {
        if ($campaign_type == self::TYPE) {
            if ($not_supported_gateways = PostPurchase::notSupportedPaymentMethods()) {
                $notices[] = [
                    'status' => 'info',
                    'message' => sprintf(__("The following payment gateways do NOT support post-purchase campaigns: %s", 'checkout-upsell-woocommerce'),
                            '<strong>' . esc_html(implode(", ", $not_supported_gateways)) . '</strong>')
                        . '<br>' . sprintf(__("Contact our %s to check possible compatibility with the unsupported payment gateways.", 'checkout-upsell-woocommerce'),
                            '<a href="' . self::app()->plugin->getSupportUrl() . '" target="_blank">' . esc_html__("support team", 'checkout-upsell-woocommerce') . '</a>'),
                ];
            }

            $template = self::app()->input->get('template', '', 'query');
            if (empty($template) && $campaign_id && $page_data = Offer::getPageData($campaign_id)) {
                $template = Templates::getData($page_data['template']);
                if (empty($template)) {
                    $notices[] = [
                        'status' => 'warning',
                        'message' => sprintf(__("This campaign offer template is invalid or deleted. %s", 'checkout-upsell-woocommerce'),
                            '<a href="' . add_query_arg('page_builder', $page_data['builder']) . '">' . esc_html__("Click here to change offer template", 'checkout-upsell-woocommerce') . '</a>'),
                    ];
                }
            }
        }
        return $notices;
    }

    /**
     * Initially hide form.
     */
    public static function hideCampaignForm($status, $campaign_type, $campaign)
    {
        $page_builder = self::app()->input->get('page_builder', '', 'query');
        $template = self::app()->input->get('template', '', 'query');
        if ($campaign_type == self::TYPE && (empty($campaign['id']) || (!empty($page_builder) && empty($template)))) {
            return true;
        }
        return $status;
    }

    /**
     * Show offer before payment.
     *
     * @hooked woocommerce_checkout_order_processed|woocommerce_store_api_checkout_order_processed
     */
    public static function beforePaymentRedirect($order_id, $post_data = [], $order = null)
    {
        if (empty($order)) {
            $order = WC::getOrder($order_id);
            if ($order) {
                $order_id = $order->get_id();
            }
        }

        if (current_action() == 'woocommerce_store_api_checkout_order_processed') {
            $not_supported_gateways = apply_filters('cuw_post_purchase_before_payment_unsupported_block_checkout_gateways', [
                'stripe',
                'razorpay',
                'square_credit_card',
                'authnet',
                'ppcp-gateway',
                'stripe_cc',
            ]);
            if (in_array($order->get_payment_method(), $not_supported_gateways)) {
                return;
            }
        }

        $offer = self::getOfferToDisplay($order);
        if ($offer) {
            WC::maybeLoadSession(); // set customer session cookie if it is not set
            WC::setSession('order_awaiting_payment', $order_id);
            WC::setSession('cuw_order_awaiting_payment', $order_id);

            PostPurchase::storeCheckoutPostData();

            if (!self::isOrderProcessed($order) && $offer_page_url = Offer::getPageUrl($offer, $order)) {
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

        if (current_action() == 'woocommerce_store_api_checkout_order_processed') {
            $not_supported_gateways = apply_filters('cuw_post_purchase_after_payment_unsupported_block_checkout_gateways', [
                'authnet',
                'ppcp-gateway',
            ]);
            if (in_array($order->get_payment_method(), $not_supported_gateways)) {
                return;
            }
        }

        $offer = self::getOfferToDisplay($order);
        if ($offer) {
            WC::maybeLoadSession(); // set customer session cookie if it is not set
            PostPurchase::storeCheckoutPostData();
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
            if ($offer && $offer_page_url = Offer::getPageUrl($offer, $order)) {
                OfferModel::increaseCount($offer['id'], 'display_count');
                $url = $offer_page_url;
            }
        }
        return $url;
    }

    /**
     * Get offer to display.
     *
     * @param \WC_Order $order
     * @return array|false
     */
    public static function getOfferToDisplay($order)
    {
        if (!PostPurchase::isProcessable($order)) {
            return false;
        }

        $campaigns = CampaignModel::all([
            'status' => 'active',
            'type' => self::TYPE,
            'columns' => ['id', 'conditions', 'data'],
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
     * Pick offer from campaign.
     *
     * @param array $campaign
     * @param array $data
     * @return array|false
     */
    public static function pickOffer($campaign, $data)
    {
        // check conditions
        if (!\CUW\App\Helpers\Campaign::isConditionsPassed($campaign['conditions'], $data)) {
            return false;
        }

        // check campaign has valid template
        if (empty($campaign['data']['page']['template']) || !Templates::isValid($campaign['data']['page']['template'])) {
            return false;
        }

        // get offers
        $offers = OfferModel::all([
            'campaign_id' => $campaign['id'],
            'columns' => ['id', 'uuid', 'product', 'usage_limit', 'usage_limit_per_user', 'usage_count', 'campaign_id'],
        ]);
        if (empty($offers) || !is_array($offers)) {
            return false;
        }

        // get and validate main offer
        $main_offer = Offer::getMainOffer($campaign, $offers);
        if (!Offer::isValid($main_offer)) {
            return false;
        }
        return $main_offer;
    }

    /**
     * Handle offer accept or decline requests.
     */
    public static function handleRequests()
    {
        $offer_id = self::app()->input->get('cuw_ppu_offer', '0', 'query');
        $order_id = self::app()->input->get('cuw_order', '0', 'query');
        $nonce = self::app()->input->get('cuw_nonce', '', 'query');
        if (empty($order_id) || !is_numeric($offer_id) || !is_numeric($order_id)) {
            wp_die(esc_html__("Invalid request", 'checkout-upsell-woocommerce'));
        }
        if (empty($nonce) || !WP::verifyNonce($nonce, 'cuw_ppu_offer')) {
            if (get_option('woocommerce_enable_signup_and_login_from_checkout', '') == 'no'
                && !empty(Input::get('cuw_ppu_offer', '', 'cookie'))
            ) {
                wp_die(esc_html__("Invalid request", 'checkout-upsell-woocommerce'));
            }
        }

        $order = WC::getOrder($order_id);
        $offer = OfferModel::get($offer_id, ['id', 'uuid', 'campaign_id', 'product', 'discount']);
        if (empty($offer) || empty($order) || self::isOrderProcessed($order)) {
            wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
        }

        $is_awaiting_payment = false;
        $process_type = PostPurchase::getProcessType();
        if ($process_type == 'before_payment') {
            $is_awaiting_payment = apply_filters('cuw_ppu_order_awaiting_payment_status', ($order_id == WC::getSession('cuw_order_awaiting_payment')), $order, $offer);
            if (!$is_awaiting_payment || in_array($order->get_status(), ['processing', 'completed', 'on-hold', 'failed'])) {
                wp_die(esc_html__("Offer expired", 'checkout-upsell-woocommerce'));
            }
        }

        $action = self::app()->input->get('cuw_ppu_action', '', 'post');
        $nonce = self::app()->input->get('cuw_ppu_nonce', '', 'post');
        if (!empty($action) && ((empty($nonce) || !WP::verifyNonce($nonce, 'cuw_ppu_action')))) {
            wp_die(esc_html__("Invalid request", 'checkout-upsell-woocommerce'));
        }

        if ($action == 'accept_offer') {
            $main_order = $order;
            $quantity = self::app()->input->get('quantity', '1', 'post');
            $variation_id = self::app()->input->get('variation_id', '0', 'post');
            $variation_attributes = self::app()->input->get('variation_attributes', [], 'post');
            $offer_data = OfferHelper::prepareMetaData($offer, $quantity, $variation_id, $variation_attributes, self::TYPE);
            $product_id = $offer_data['product']['variation_id'] > 0 ? $offer_data['product']['variation_id'] : $offer_data['product']['id'];
            $product = WC::getProduct($product_id);
            if ($product && WC::isPurchasableProduct($product)) {
                if ($process_type == 'after_payment') {
                    $offer_order = Order::getOfferOrder($order);
                    if (!empty($offer_order)) {
                        Order::addOffer($offer_order, $product, $offer_data);
                    } else {
                        $offer_order = Order::generateOfferOrder($order, $product, $offer_data);
                    }
                    $order = $offer_order;
                    if (empty($order)) {
                        Payment::handleError(esc_html__("Unable to create an offer order", 'checkout-upsell-woocommerce'));
                    }
                    Order::saveStats($offer_order, true);
                } else {
                    Order::addOffer($order, $product, $offer_data);
                    Order::saveStats($order, true);
                }
            }

            self::processNextOffer($offer, $main_order, $action);

            self::setOrderProcessed($main_order);
            if ($order->get_total() > 0) {
                PostPurchase::processPayment($order);
            } else {
                PostPurchase::processWithoutPayment($order, ($process_type == 'after_payment' ? $main_order : null));
            }
        } elseif (in_array($action, ['decline_offer', 'ignore_offers'])) {
            if ($action != 'ignore_offers') {
                self::processNextOffer($offer, $order, $action);
            }

            self::setOrderProcessed($order);
            if ($is_awaiting_payment && $process_type == 'before_payment') {
                PostPurchase::processPayment($order);
            } elseif ($process_type == 'after_payment') {
                $offer_order = Order::getOfferOrder($order);
                if (!empty($offer_order)) {
                    if ($offer_order->get_total() > 0) {
                        PostPurchase::processPayment($offer_order);
                    } else {
                        PostPurchase::processWithoutPayment($offer_order, $order);
                    }
                } else {
                    PostPurchase::redirectAfterPayment($order, [
                        'result' => 'success',
                        'redirect' => $order->get_checkout_order_received_url(),
                    ]);
                }
            }
        }
    }

    /**
     * Set offer data before save.
     *
     * @param array $offer_data
     * @param array $campaign
     * @return array
     */
    public static function beforeOfferDataSave($offer_data, $campaign)
    {
        if ($campaign['type'] == self::TYPE && !empty($offer_data)) {
            if (empty($offer_data['order_details']['enabled'])) {
                $offer_data['order_details']['enabled'] = 0;
            }
            if (empty($offer_data['timer']['enabled'])) {
                $offer_data['timer']['enabled'] = 0;
            }
            if (empty($offer_data['order_totals']['enabled'])) {
                $offer_data['order_totals']['enabled'] = 0;
            }
        }
        return $offer_data;
    }

    /**
     * Duplicate campaign data
     *
     * @param array $campaign
     * @return array
     */
    public static function duplicateCampaignData($campaign)
    {
        if ($campaign['type'] == self::TYPE && !empty($campaign['offers'])) {
            foreach ($campaign['offers'] as $key => $offer) {
                if (!empty($campaign['data']['offers_map']) && is_array($campaign['data']['offers_map'])) {
                    foreach ($campaign['data']['offers_map'] as $offer_uuid => $offer_map) {
                        foreach ($offer_map as $mapping_key => $mapping_value) {
                            if ($mapping_value == $offer['old_uuid']) {
                                $campaign['data']['offers_map'][$offer_uuid][$mapping_key] = $offer['uuid'];
                            }
                        }
                        if ($offer_uuid == $offer['old_uuid']) {
                            $campaign['data']['offers_map'][$offer['uuid']] = $campaign['data']['offers_map'][$offer_uuid];
                            unset($campaign['data']['offers_map'][$offer_uuid]);
                        }
                    }
                }
                unset($campaign['offers'][$key]['old_uuid']);
            }
        }
        return $campaign;
    }

    /**
     * Process next offer if available.
     */
    private static function processNextOffer($offer, $order, $action)
    {
        if (empty($offer['campaign_id'])) {
            return;
        }
        $campaign = CampaignModel::get($offer['campaign_id'], ['id', 'data']);
        if ($campaign && !empty($campaign['data'])) {
            foreach ($campaign['data']['offers_map'] ?? [] as $node) {
                if ($node['uuid'] == $offer['uuid']) {
                    $next_offer_uuid = '';
                    if ($action == 'accept_offer' && !empty($node['accept_uuid'])) {
                        $next_offer_uuid = $node['accept_uuid'];
                    } elseif ($action == 'decline_offer' && !empty($node['decline_uuid'])) {
                        $next_offer_uuid = $node['decline_uuid'];
                    }
                    if ($next_offer_uuid) {
                        $next_offer = OfferModel::getByUuid(
                            $next_offer_uuid,
                            ['id', 'product', 'usage_limit', 'usage_limit_per_user', 'usage_count', 'campaign_id']
                        );
                        if (!Offer::isValid($next_offer)) {
                            return;
                        }

                        $next_offer_url = Offer::getPageUrl($next_offer, $order);
                        if ($next_offer_url) {
                            OfferModel::increaseCount($next_offer['id'], 'display_count');
                            wp_safe_redirect($next_offer_url);
                            exit;
                        }
                    }
                    break;
                }
            }
        }
    }

    /**
     * To add page data.
     */
    public static function addCampaignPageData($data, $campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $data['views']['offer']['post_purchase_offer'] = self::app()->view('Pro/Admin/Campaign/Offer/PostPurchaseOffer', ['campaign_type' => $campaign_type], false);
            $data['views']['offer']['post_purchase_offer_data'] = self::app()->view('Pro/Admin/Campaign/Offer/OfferOptionTabs', [], false);
        }
        return $data;
    }

    /**
     * To load campaign contents.
     */
    public static function loadCampaignView($campaign_type, $campaign)
    {
        if ($campaign_type == self::TYPE) {
            self::app()->view('Pro/Admin/Campaign/PostPurchaseUpsells', ['action' => current_action(), 'campaign' => $campaign]);
        }
    }

    /**
     * To load extra assets.
     */
    public static function loadExtraAssets($campaign_type)
    {
        if ($campaign_type == self::TYPE) {
            $assets = self::app()->assets;
            $assets->addJs('post-purchase', 'post-purchase')->addCss('post-purchase', 'post-purchase');
            if (defined('ELEMENTOR_ASSETS_URL')) {
                $frontend_url = ELEMENTOR_ASSETS_URL . 'css/templates/frontend' . (is_rtl() ? '-rtl' : '') . '.css';
                $assets->addCss('elementor_frontend', $frontend_url);
            }
            $assets->enqueue('admin');
        }
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
            return !empty($order->get_meta('_cuw_ppu_processed'));
        }
        return false;
    }

    /**
     * Mark the order is processed by post-purchase.
     */
    private static function setOrderProcessed($order)
    {
        Order::saveMeta($order, ['_cuw_ppu_processed' => true]);
    }
}
