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

namespace CUW\App\Controllers\Admin;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\Config;
use CUW\App\Helpers\Input;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\Validate;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Campaign;
use CUW\App\Models\Offer as OfferModel;
use CUW\App\Models\Stats;
use CUW\App\Modules\Campaigns\CartUpsells;
use CUW\App\Modules\Campaigns\CheckoutUpsells;

class Ajax extends Controller
{
    /**
     * Get authenticated user request handlers.
     *
     * @return array
     */
    private static function getAuthRequestHandlers()
    {
        return (array)apply_filters('cuw_ajax_auth_request_handlers', [
            'enable_campaign' => [__CLASS__, 'enableCampaign'],
            'duplicate_campaign' => [__CLASS__, 'duplicateCampaign'],
            'delete_campaign' => [__CLASS__, 'deleteCampaign'],
            'bulk_actions' => [__CLASS__, 'bulkActions'],
            'list_products' => [__CLASS__, 'listProducts'],
            'list_taxonomies' => [__CLASS__, 'listTaxonomies'],
            'list_coupons' => [__CLASS__, 'listCoupons'],
            'save_campaign' => [__CLASS__, 'saveCampaign'],
            'save_offer' => [__CLASS__, 'saveOffer'],
            'delete_offer' => [__CLASS__, 'deleteOffer'],
            'save_settings' => [__CLASS__, 'saveSettings'],
            'get_offer_template' => [__CLASS__, 'getOfferTemplate'],
            'get_offer_image' => [__CLASS__, 'getOfferImage'],
            'get_chart_data' => [__CLASS__, 'getChartData'],
            'get_upsell_info' => [__CLASS__, 'getUpsellInfo'],
            'add_offer_to_cart' => [__CLASS__, 'addOfferToCart'],
            'perform_action' => [__CLASS__, 'performAction'],
            'add_product_to_cart' => [__CLASS__, 'addProductToCart'],
            'remove_item_from_cart' => [__CLASS__, 'removeItemFromCart'],
            'change_cart_item_variant' => [__CLASS__, 'changeCartItemVariant'],
            'get_all_offers_html' => [__CLASS__, 'getAllOffersHtml'],
            'get_product_details_popup' => [__CLASS__, 'getProductDetailsPopup'],
            'set_campaigns_list_limit' => [__CLASS__, 'setCampaignsListLimit'],
            'set_revenue_tax_display' => [__CLASS__, 'setRevenueTaxDisplay'],
        ]);
    }

    /**
     * Get non-authenticated (guest) user request handlers.
     *
     * @return array
     */
    private static function getGuestRequestHandlers()
    {
        return (array)apply_filters('cuw_ajax_guest_request_handlers', [
            'get_offer_image' => [__CLASS__, 'getOfferImage'],
            'add_offer_to_cart' => [__CLASS__, 'addOfferToCart'],
            'perform_action' => [__CLASS__, 'performAction'],
            'add_product_to_cart' => [__CLASS__, 'addProductToCart'],
            'remove_item_from_cart' => [__CLASS__, 'removeItemFromCart'],
            'change_cart_item_variant' => [__CLASS__, 'changeCartItemVariant'],
            'get_all_offers_html' => [__CLASS__, 'getAllOffersHtml'],
            'get_product_details_popup' => [__CLASS__, 'getProductDetailsPopup'],
        ]);
    }

    /**
     * Get search list items limit.
     *
     * @return int
     */
    public static function getSearchLimit()
    {
        return (int)apply_filters('cuw_ajax_search_limit', 20);
    }

    /**
     * To verify nonce
     *
     * @return void
     */
    private static function verifyNonce()
    {
        $nonce = self::app()->input->get('nonce', '', 'post');
        if (empty($nonce) || !WP::verifyNonce($nonce, 'cuw_ajax')) {
            wp_send_json_error(['message' => __("Security check failed!", 'checkout-upsell-woocommerce')]);
        }
    }

    /**
     * To handle authenticated user requests.
     *
     * @return void
     */
    public static function handleAuthRequests()
    {
        self::verifyNonce();
        $method = self::app()->input->get('method', '', 'post');
        $handlers = self::getAuthRequestHandlers();
        if (!empty($method) && isset($handlers[$method]) && is_callable($handlers[$method])) {
            wp_send_json_success(call_user_func($handlers[$method]));
        }
        wp_send_json_error(['message' => __("Method not exists.", 'checkout-upsell-woocommerce')]);
    }

    /**
     * To handle non-authenticated (guest) user requests.
     *
     * @return void
     */
    public static function handleGuestRequests()
    {
        self::verifyNonce();
        $method = self::app()->input->get('method', '', 'post');
        $handlers = self::getGuestRequestHandlers();
        if (!empty($method) && isset($handlers[$method]) && is_callable($handlers[$method])) {
            wp_send_json_success(call_user_func($handlers[$method]));
        }
        wp_send_json_error(['message' => __("Method not exists.", 'checkout-upsell-woocommerce')]);
    }

    /**
     * List products
     *
     * @return array
     * @throws \Exception
     */
    private static function listProducts()
    {
        $query = self::app()->input->get('query', '', 'post');
        $params = self::app()->input->get('params', [], 'post');

        $search_limit = self::getSearchLimit();
        $include_variations = !(isset($params['include_variations']) && empty($params['include_variations']));
        if (class_exists('WC_Data_Store') && method_exists('WC_Data_Store', 'load')) {
            remove_all_filters('woocommerce_data_stores');
            $ids = \WC_Data_Store::load('product')->search_products($query, '', $include_variations, false, $search_limit);
        } else {
            $ids = get_posts([
                'post_type' => $include_variations ? ['product', 'product_variation'] : ['product'],
                //'post_status' => 'publish',
                's' => $query,
                'fields' => 'ids',
                'numberposts' => $search_limit,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);
        }

        return array_values(array_map(function ($id) {
            return [
                'id' => (string)$id,
                'text' => html_entity_decode(WC::getProductTitle($id, true)),
            ];
        }, array_filter($ids)));
    }

    /**
     * List taxonomies.
     *
     * @return array
     */
    private static function listTaxonomies()
    {
        $query = self::app()->input->get('query', '', 'post');
        $params = self::app()->input->get('params', [], 'post');
        if (!empty($params['taxonomy'])) {
            $terms = get_terms(array('taxonomy' => $params['taxonomy'],
                'name__like' => $query,
                'hide_empty' => false,
                'number' => self::getSearchLimit(),
            ));
            return array_map(function ($term) {
                return [
                    'id' => (string)$term->term_id,
                    'text' => WC::getTaxonomyName($term, true)
                ];
            }, $terms);
        }
        return [];
    }

    /**
     * List coupons
     *
     * @return array
     */
    private static function listCoupons()
    {
        $query = self::app()->input->get('query', '', 'post');
        $ids = get_posts([
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            's' => $query,
            'fields' => 'ids',
            'numberposts' => self::getSearchLimit(),
        ]);
        return array_map(function ($id) {
            $code = strtolower(WP::getTitle($id));
            return ['id' => $code, 'text' => $code];
        }, $ids);
    }

    /**
     * Save campaign
     *
     * @return array
     */
    private static function saveCampaign()
    {
        $form_data = self::app()->input->get('form_data', '', 'post', false);
        if (!empty($form_data)) {
            parse_str($form_data, $data);
            if (isset($data['offers'])) {
                $offers = $data['offers'];
                unset($data['offers']);
            }
            if (isset($data['data'])) {
                $campaign_data = $data['data'];
                unset($data['data']);
            }

            $sanitized_data = Input::sanitize($data);
            if (!empty($offers)) {
                foreach ($offers as $key => $offer) {
                    if (is_array($offer['data'])) {
                        $offer['data'] = apply_filters('cuw_process_offer_data_before_save', $offer['data'], $sanitized_data);
                        $offer['data'] = json_encode($offer['data'], JSON_UNESCAPED_UNICODE);
                    }
                    $offers[$key] = self::sanitizeOffer($offer, false);
                }
                $sanitized_data['offers'] = $offers;
            }
            if (!empty($campaign_data)) {
                $sanitized_data['data'] = Input::sanitize($campaign_data, 'content');
            }

            $errors = Validate::campaign($sanitized_data);
            if (!empty($errors)) {
                return [
                    'status' => "error",
                    'message' => $errors,
                ];
            }

            $sanitized_data = apply_filters('cuw_campaign_data_before_save', $sanitized_data);
            if (isset($sanitized_data['errors'])) {
                return [
                    'status' => "error",
                    'message' => $sanitized_data['errors'],
                ];
            }

            $result = Campaign::save($sanitized_data);
            if ($result && !empty($result['id'])) {
                $page_no = self::app()->input->get('page_no', '', 'post');
                return [
                    'status' => "success",
                    'message' => esc_html__("Campaign saved", 'checkout-upsell-woocommerce'),
                    'redirect' => "tab=campaigns" . ($page_no > 1 ? "&page_no=" . $page_no : '') . "&edit={$result['id']}",
                    'result' => $result,
                ];
            } else {
                do_action('cuw_campaign_save_failed', $sanitized_data);
            }
        }

        return ['status' => "error"];
    }

    /**
     * Save offer
     *
     * @return array
     */
    private static function saveOffer()
    {
        $campaign_id = self::app()->input->get('campaign_id', '0', 'post');
        $offer = self::app()->input->get('offer', [], 'post', false);
        if (!empty($offer)) {
            $sanitized_data = self::sanitizeOffer($offer);

            $errors = Validate::offer($sanitized_data);
            if (!empty($errors)) {
                return [
                    'status' => "error",
                    'message' => $errors,
                ];
            }

            $response = ['status' => "success"];
            if (!empty($campaign_id)) {
                $id = OfferModel::save($campaign_id, $sanitized_data);
                if ($id) {
                    $response['id'] = $id;
                    $response['message'] = !empty($sanitized_data['id'])
                        ? esc_html__("Offer saved", 'checkout-upsell-woocommerce')
                        : esc_html__("Offer added", 'checkout-upsell-woocommerce');
                }
            }
            return $response;
        }

        return ['status' => "error"];
    }

    /**
     * Sanitize offer before save.
     *
     * @param array $offer
     * @param bool $unslash
     * @return array
     */
    private static function sanitizeOffer($offer, $unslash = true)
    {
        $offer_data = [];
        if (isset($offer['data'])) {
            $offer_data = $offer['data'];
            unset($offer['data']);
        }
        $sanitized_offer = Input::sanitize(($unslash ? wp_unslash($offer) : $offer), 'html');
        if (!empty($offer_data)) {
            $sanitized_offer['data'] = Input::sanitize(
                str_replace(
                    ['\\"', '\\n', '\\r', '\\/', "\\'"],
                    ['\\\"', '\\\n', '\\\r', '/', "'"],
                    ($unslash ? wp_unslash($offer_data) : $offer_data)
                ),
                'html'
            );
        }
        return $sanitized_offer;
    }

    /**
     * Delete offer
     *
     * @return array
     */
    private static function deleteOffer()
    {
        $id = self::app()->input->get('id', '', 'post');
        if ($id) {
            $result = OfferModel::deleteById($id);
            if ($result) {
                return [
                    'status' => "success",
                    'message' => esc_html__("Offer deleted", 'checkout-upsell-woocommerce'),
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * Enable campaign
     *
     * @return array
     */
    private static function enableCampaign()
    {
        $id = self::app()->input->get('id', '', 'post');
        $enabled = self::app()->input->get('enabled', '0', 'post');
        if ($id) {
            $result = Campaign::updateById($id, ['enabled' => $enabled], ['%d']);
            if ($result) {
                return [
                    'status' => "success",
                    'message' => $enabled
                        ? esc_html__("Campaign published", 'checkout-upsell-woocommerce')
                        : esc_html__("Campaign drafted", 'checkout-upsell-woocommerce'),
                    'change' => ['id' => $id, 'status' => \CUW\App\Helpers\Campaign::getStatus($id, true)],
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * Duplicate campaign
     *
     * @return array
     */
    private static function duplicateCampaign()
    {
        $id = self::app()->input->get('id', '', 'post');
        if ($id) {
            $result = Campaign::duplicate($id);
            if ($result) {
                return [
                    'status' => "success",
                    'message' => esc_html__("Campaign duplicated", 'checkout-upsell-woocommerce'),
                    'refresh' => true,
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * Delete campaign
     *
     * @return array
     */
    private static function deleteCampaign()
    {
        $id = self::app()->input->get('id', '', 'post');
        if ($id) {
            $campaign = Campaign::get($id, ['type', 'data']);
            if ($campaign) {
                $result = Campaign::deleteById($id);
                if ($result) {
                    OfferModel::delete(['campaign_id' => $id], ['%d']);
                    do_action('cuw_campaign_deleted', $id, $campaign);
                    return [
                        'status' => "success",
                        'message' => esc_html__("Campaign deleted", 'checkout-upsell-woocommerce'),
                        'remove' => ['id' => $id],
                        'refresh' => true,
                    ];
                }
            }
        }

        return ['status' => "error"];
    }

    /**
     * Bulk action for campaigns
     *
     * @return array
     */
    private static function bulkActions()
    {
        $action = self::app()->input->get('bulk_action', '', 'post');
        $ids = (array)self::app()->input->get('ids', [], 'post');
        if (!empty($action)) {
            foreach ($ids as $id) {
                if ($action == 'delete') {
                    $campaign = Campaign::get($id, ['type', 'data']);
                    if ($campaign) {
                        $result = Campaign::deleteById($id);
                        if ($result) {
                            OfferModel::delete(['campaign_id' => $id], ['%d']);
                            do_action('cuw_campaign_deleted', $id, $campaign);
                        }
                    }
                }
            }
            if ($action == 'delete') {
                return [
                    'status' => "success",
                    'message' => esc_html__("Campaigns deleted", 'checkout-upsell-woocommerce'),
                    'remove' => ['ids' => $ids],
                    'refresh' => true,
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * Get offer template
     *
     * @return array
     */
    private static function getOfferTemplate()
    {
        $product = self::app()->input->get('product', [], 'post');
        $discount = self::app()->input->get('discount', [], 'post');
        $data = self::app()->input->get('data', [], 'post', 'html');
        $campaign_type = self::app()->input->get('campaign_type', '', 'post');
        return ['html' => Offer::getTemplateHtml([
            'product' => $product,
            'discount' => $discount,
            'data' => $data,
            'campaign_type' => $campaign_type,
        ])];
    }

    /**
     * Get offer image
     *
     * @return array
     */
    private static function getOfferImage()
    {
        $image_id = self::app()->input->get('image_id', '0', 'post');
        $product_id = self::app()->input->get('product_id', '0', 'post');

        $html = '';
        if ($image_id && $image = WP::getImage($image_id)) {
            $html = $image;
        } elseif ($product_id && $product = WC::getProduct($product_id)) {
            $html = $product->get_image();
        }

        return ['html' => $html];
    }

    /**
     * Get chart data
     *
     * @return array
     */
    private static function getChartData()
    {
        $campaign = self::app()->input->get('campaign', 'this_week', 'post');
        $range = self::app()->input->get('range', 'this_week', 'post');
        $date = self::app()->input->get('date', [], 'post');
        $currency = self::app()->input->get('currency', '', 'post');
        $tab = self::app()->input->get('tab', '', 'post');
        return Stats::getChartData($tab, $currency, $campaign, $range, $date);
    }

    /**
     * Get Upsell data
     *
     * @return array
     */
    private static function getUpsellInfo()
    {
        $campaign = self::app()->input->get('campaign', 'this_week', 'post');
        $range = self::app()->input->get('range', 'this_week', 'post');
        $date = self::app()->input->get('date', [], 'post');
        $currency = self::app()->input->get('currency', '', 'post');
        $tab = self::app()->input->get('tab', '', 'post');
        return Stats::getUpsellInfo($tab, $currency, $campaign, $range, $date);
    }

    /**
     * Add offer to cart
     *
     * @return array|false
     */
    private static function addOfferToCart()
    {
        $offer_id = self::app()->input->get('offer_id', '0', 'post');
        $quantity = self::app()->input->get('quantity', '1', 'post');
        $variation_id = self::app()->input->get('variation_id', '0', 'post');
        $variation_attributes = self::app()->input->get('variation_attributes', [], 'post');
        $location = self::app()->input->get('location', '', 'post');
        return Cart::addOffer($offer_id, $quantity, $variation_id, $variation_attributes, $location);
    }

    /**
     * Add product to cart
     *
     * @return array
     */
    private static function addProductToCart()
    {
        $campaign_id = self::app()->input->get('campaign_id', '', 'post');
        $product_id = self::app()->input->get('product_id', '', 'post');
        $quantity = self::app()->input->get('quantity', '1', 'post');
        $variation_id = self::app()->input->get('variation_id', '0', 'post');
        $variation_attributes = self::app()->input->get('variation_attributes', [], 'post');
        $page = self::app()->input->get('page', '', 'post');
        if (!empty($campaign_id) && !empty($product_id) && !empty($quantity)) {
            return [
                'item_key' => Cart::addProduct($campaign_id, $product_id, $quantity, $variation_id, $variation_attributes),
                'cart_subtotal' => WC::formatPrice(WC::getCartSubtotal(WC::getDisplayTaxSettingByPage($page))),
            ];
        }
        return ['status' => "error"];
    }

    /**
     * Remove item from cart
     *
     * @return array
     */
    public static function removeItemFromCart()
    {
        $item_key = self::app()->input->get('item_key', '', 'post');
        $page = self::app()->input->get('page', '', 'post');
        if (!empty($item_key)) {
            return [
                'item_removed' => WC::removeCartItem($item_key),
                'cart_subtotal' => WC::formatPrice(WC::getCartSubtotal(WC::getDisplayTaxSettingByPage($page))),
            ];
        }
        return ['status' => "error"];
    }

    /**
     * Change cart item variant
     *
     * @return array
     */
    public static function changeCartItemVariant()
    {
        $item_key = self::app()->input->get('item_key', '', 'post');
        $variation_id = self::app()->input->get('variation_id', '0', 'post');
        if (!empty($item_key)) {
            $cart_item = WC::getCartItem($item_key);
            if (!empty($cart_item) && (isset($cart_item['cuw_product']) || isset($cart_item['cuw_offer']))) {
                $data = isset($cart_item['cuw_product']) ? $cart_item['cuw_product'] : $cart_item['cuw_offer'];
                unset($data['product']);
                unset($data['discount']);
                if (isset($data['price'])) {
                    unset($data['price']);
                }
                if (isset($cart_item['cuw_product'])) {
                    $new_item_key = Cart::addProduct($data['campaign_id'], $cart_item['product_id'], $cart_item['quantity'], $variation_id, [], $data);
                } elseif ($cart_item['cuw_offer']) {
                    $data = Offer::prepareMetaData($data['id'], $cart_item['quantity'], $variation_id);
                    try {
                        $product = $data['product'];
                        $new_item_key = WC::addToCart($product['id'], $product['qty'], $product['variation_id'], $product['variation'], ['cuw_offer' => $data]);
                    } catch (\Exception $e) {
                    }
                }

                if (!empty($new_item_key)) {
                    if (apply_filters('cuw_do_replace_cart_item', true)) {
                        $item_removed = WC::replaceCartItem($item_key, $new_item_key);
                    } else {
                        $item_removed = WC::removeCartItem($item_key);
                    }
                    return [
                        'item_key' => $new_item_key,
                        'item_removed' => $item_removed,
                    ];
                }
            }
        }
        return ['status' => "error"];
    }

    /**
     * Perform action
     *
     * @return array
     */
    private static function performAction()
    {
        $params = self::app()->input->get('params', [], 'post');
        if (!empty($params['campaign_id']) && $campaign = Campaign::get($params['campaign_id'])) {
            return apply_filters('cuw_perform_action', [], $campaign, $params);
        }
        return ['status' => "error"];
    }

    /**
     * To get all offers html.
     *
     * @return array
     */
    private static function getAllOffersHtml()
    {
        $html = [];
        $campaign_type = self::app()->input->get('campaign_type', '', 'post');
        if (!empty($campaign_type)) {
            $locations = Offer::getDisplayLocations($campaign_type);
            if (!empty($locations) && $campaign_type == 'checkout_upsells') {
                foreach (array_keys($locations) as $location) {
                    $html[$location] = CheckoutUpsells::getOffersHtml($location, false, false);
                }
            } elseif (!empty($locations) && $campaign_type == 'cart_upsells') {
                foreach (array_keys($locations) as $location) {
                    $html[$location] = CartUpsells::getOffersHtml($location, false, false);
                }
            }
            return ['html' => $html];
        }
        return [];
    }

    /**
     * To get product details popup template.
     *
     * @return array
     */
    private static function getProductDetailsPopup()
    {
        $product_id = self::app()->input->get('product_id', '', 'post');
        if (!empty($product_id) && $product = WC::getProduct($product_id)) {
            return [
                'html' => self::app()->template('common/product-details-popup', [
                    'product_object' => $product,
                ], false),
            ];
        }
        return [];
    }

    /**
     * Save settings
     *
     * @return array
     */
    private static function saveSettings()
    {
        $form_data = self::app()->input->get('form_data', '', 'post', false);
        if (!empty($form_data)) {
            parse_str($form_data, $data);

            $data = Input::sanitize($data);

            $settings = Config::getSettings();
            foreach (Config::getDefaultSettings() as $key => $default_setting) {
                $settings[$key] = $data[$key] ?? ''; // DON'T set default setting when data missing
            }
            Config::set('settings', $settings);

            Config::updateEmailSettings('weekly_report', [
                'enabled' => isset($data['send_weekly_report']) ? 'yes' : 'no',
                'recipient' => !empty($data['report_recipient']) ? $data['report_recipient'] : get_option('admin_email'),
            ]);

            do_action('cuw_save_settings', $data, self::app()->config);

            return [
                'status' => "success",
                'message' => esc_html__("Changes saved", 'checkout-upsell-woocommerce'),
            ];
        } else {
            return ['status' => "error"];
        }
    }

    /**
     * Set campaigns list limit.
     *
     * @return array
     */
    public static function setCampaignsListLimit()
    {
        $value = self::app()->input->get('value', '5', 'post');
        $result = self::app()->config->set('campaigns_per_page', $value);
        if ($result) {
            return ['status' => "success", 'refresh' => true];
        }
        return ['status' => "error"];
    }

    /**
     * Set revenue type.
     *
     * @return array
     */
    public static function setRevenueTaxDisplay()
    {
        $type = self::app()->input->get('type', 'without_tax', 'post');
        $result = self::app()->config->set('revenue_tax_display', $type);
        if ($result) {
            return ['status' => "success", 'refresh' => true];
        }
        return ['status' => "error"];
    }
}