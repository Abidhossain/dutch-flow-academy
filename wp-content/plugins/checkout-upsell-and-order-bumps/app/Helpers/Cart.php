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

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Offer as OfferModel;

class Cart
{
    /**
     * Cart data
     *
     * @var array
     */
    private static $data = [];

    /**
     * Cart added products
     *
     * @var array
     */
    private static $added_products;

    /**
     * Cart applied offers
     *
     * @var array
     */
    private static $applied_offers;

    /**
     * Cart bundled items
     *
     * @var array
     */
    private static $bundled_items = [];

    /**
     * Cart price per item
     *
     * @var array
     */
    private static $price_per_item = [];

    /**
     * To hold validation data
     *
     * @var array
     */
    private static $campaign_results, $offer_results;

    /**
     * Get cart data
     *
     * @param array $args
     * @param bool $cache
     * @return array
     */
    public static function getData($args = [], $cache = true)
    {
        $args = wp_parse_args($args, [
            'include_applied_offers' => false,
            'include_added_products' => true,
            'exclude_campaign_id' => 0,
            'with_product_object' => false,
        ]);

        $hash = Functions::generateHash($args);
        if ($cache && isset(self::$data[$hash])) {
            return self::$data[$hash];
        }

        $data = [];
        $cart = WC::getCart();
        if (empty($cart)) {
            return $data;
        }

        $data['type'] = 'cart';
        $data['products'] = [];
        $data['added_products'] = [];
        $data['applied_offers'] = [];
        foreach (WC::getCartItems() as $key => $cart_item) {
            if (isset($cart_item['cuw_offer'])) {
                $data['applied_offers'][$key] = $cart_item['cuw_offer'];
                if (!apply_filters('cuw_get_cart_data_including_applied_offers', $args['include_applied_offers'], $cart_item)) {
                    continue;
                }
                if ($args['exclude_campaign_id'] == $cart_item['cuw_offer']['campaign_id']) {
                    continue;
                }
            } elseif (isset($cart_item['cuw_product'])) {
                $data['added_products'][$key] = $cart_item['cuw_product'];
                if (!apply_filters('cuw_get_cart_data_including_added_products', $args['include_added_products'], $cart_item)) {
                    continue;
                }
                if ($args['exclude_campaign_id'] == $cart_item['cuw_product']['campaign_id']) {
                    continue;
                }
            }

            $data['products'][$key]['id'] = $cart_item['product_id'];
            $data['products'][$key]['variation_id'] = $cart_item['variation_id'];
            $data['products'][$key]['variation'] = $cart_item['variation'] ?? [];
            $data['products'][$key]['quantity'] = $cart_item['quantity'];

            $data['products'][$key]['subtotal'] = (float)$cart_item['line_subtotal'];
            $data['products'][$key]['subtotal_tax'] = (float)$cart_item['line_subtotal_tax'];

            if ($args['with_product_object']) {
                $data['products'][$key]['object'] = $cart_item['data'];
            }
        }
        $data['subtotal'] = round(array_sum(array_column($data['products'], 'subtotal')), 4);
        $data['subtotal_tax'] = round(array_sum(array_column($data['products'], 'subtotal_tax')), 4);
        $data['subtotal_display'] = round($cart->display_prices_including_tax() ? $data['subtotal'] + $data['subtotal_tax'] : $data['subtotal'], 4);
        $data['total'] = round((float)$cart->get_total('edit'), 4);
        $data = apply_filters('cuw_get_cart_data', $data, $cart, $args);
        if ($cache) {
            self::$data[$hash] = $data;
        }
        return $data;
    }

    /**
     * Get added products
     *
     * @param bool $refresh
     * @return array
     */
    public static function getAddedProducts($refresh = false)
    {
        if (!$refresh && isset(self::$added_products)) {
            return self::$added_products;
        }

        $products = [];
        foreach (WC::getCartItems() as $key => $cart_item) {
            if (isset($cart_item['cuw_product'])) {
                $products[$key] = $cart_item['cuw_product'];
            }
        }
        return self::$added_products = $products;
    }

    /**
     * Get most recently added product ID in cart.
     *
     * @return int|null
     */
    public static function getRecentlyAddedProductId()
    {
        $cart_items = WC::getCartItems();
        if (!empty($cart_items)) {
            $last_cart_item = end($cart_items);
            return !empty($last_cart_item['product_id']) ? $last_cart_item['product_id'] : null;
        }
        return null;
    }

    /**
     * Get applied offers
     *
     * @param bool $refresh
     * @return array
     */
    public static function getAppliedOffers($refresh = false)
    {
        if (!$refresh && isset(self::$applied_offers)) {
            return self::$applied_offers;
        }

        $offers = [];
        foreach (WC::getCartItems() as $key => $cart_item) {
            if (isset($cart_item['cuw_offer'])) {
                $offers[$key] = $cart_item['cuw_offer'];
            }
        }
        return self::$applied_offers = $offers;
    }

    /**
     * Get cart product ids
     *
     * @param bool $include_variants
     * @return array
     */
    public static function getProductIds($include_variants = true)
    {
        $product_ids = [];
        foreach (WC::getCartItems() as $cart_item) {
            if (!empty($cart_item['product_id'])) {
                $product_ids[] = $cart_item['product_id'];
            }
            if ($include_variants && !empty($cart_item['variation_id'])) {
                $product_ids[] = $cart_item['variation_id'];
            }
        }
        return array_unique($product_ids);
    }

    /**
     * Filter cart products.
     *
     * @param array $product_ids
     * @param string $campaign_type
     * @return array
     */
    public static function filterProducts($product_ids, $campaign_type)
    {
        $original_product_ids = $product_ids;
        if (!empty(Config::getSetting('smart_products_display'))) {
            $product_ids = array_diff($product_ids, self::getProductIds());
        }
        return apply_filters('cuw_filter_cart_products', $product_ids, $original_product_ids, $campaign_type);
    }

    /**
     * Get bundled items.
     *
     * @param int|string $id_or_key
     * @param bool $fresh
     * @return array
     */
    public static function getBundledItems($id_or_key, $fresh = false)
    {
        if (!$fresh && isset(self::$bundled_items[$id_or_key])) {
            return self::$bundled_items[$id_or_key];
        }

        foreach (self::getAddedProducts($fresh) as $key => $product) {
            if (is_numeric($id_or_key)) {
                if (isset($product['campaign_id']) && $product['campaign_id'] == $id_or_key) {
                    self::$bundled_items[$id_or_key][$key] = $product;
                }
            } else {
                if (isset($product['main_item_key']) && $product['main_item_key'] == $id_or_key) {
                    self::$bundled_items[$id_or_key][$key] = $product;
                }
            }
        }
        return isset(self::$bundled_items[$id_or_key]) ? self::$bundled_items[$id_or_key] : [];
    }

    /**
     * Get per item discount price.
     *
     * @param string $key
     * @param array $data
     * @return int|float
     */
    public static function getPricePerItem($key, $data)
    {
        if (isset(self::$price_per_item[$key])) {
            return self::$price_per_item[$key];
        }
        $price = $data['price'];
        if ($data['discount']['is_bundle'] && $data['discount']['type'] == 'fixed_price') {
            $items = [];
            if ($data['discount']['bundle_by'] == 'campaign_id') {
                $items = self::getBundledItems($data['campaign_id']);
            }
            if (!empty($items)) {
                $total_price = array_sum(array_column(array_column($items, 'product'), 'price'));
                $fixed_discount = $data['discount']['value'];
                foreach ($items as $item_key => $item) {
                    $cart_item = WC::getCartItem($item_key);
                    $price = $item['product']['price'];
                    $qty = !empty($cart_item) && isset($cart_item['quantity']) ? $cart_item['quantity'] : 1;
                    self::$price_per_item[$item_key] = $price - (($fixed_discount * ($price / $total_price)) / $qty);
                }
            }
        }
        return isset(self::$price_per_item[$key]) ? self::$price_per_item[$key] : $price;
    }

    /**
     * Add product to cart
     *
     * @param int|array $campaign_or_id
     * @param int|\WC_Product $product_or_id
     * @param int|float $quantity
     * @param int $variation_id
     * @param array $extra_item_data
     * @param array $variation_attributes
     * @return string|false
     */
    public static function addProduct($campaign_or_id, $product_or_id, $quantity = 1, $variation_id = 0, $variation_attributes = [], $extra_item_data = [])
    {
        $product = WC::getProduct($product_or_id);
        if (empty($product) || (WC::isVariableProduct($product) && empty($variation_id))) {
            return false;
        }

        $campaign = is_numeric($campaign_or_id) ? CampaignModel::get($campaign_or_id) : $campaign_or_id;
        if (empty($campaign)) {
            return false;
        }

        $product_data = Product::getData($product, ['filter_purchasable' => true]);
        if (!empty($product_data)) {
            $data = Product::prepareMetaData($campaign, $product, $quantity, $variation_id, $variation_attributes);
            try {
                $product = $data['product'];
                $item_data = ['cuw_product' => array_merge($data, $extra_item_data)];
                $key = WC::addToCart($product['id'], $product['qty'], $product['variation_id'], $product['variation'], $item_data);
                if ($key) {
                    return $key;
                }
            } catch (\Exception $e) {
            }
        }
        return false;
    }

    /**
     * Add offer to cart
     *
     * @param int $offer_id
     * @param int|float $quantity
     * @param int $variation_id
     * @param string $location
     * @return array
     */
    public static function addOffer($offer_id, $quantity = 1, $variation_id = 0, $variation_attributes = [], $location = '')
    {
        $result = [
            'status' => 'error',
            'message' => '',
            'remove_offer' => null,
            'remove_all_offers' => false,
        ];
        $is_checkout_upsells = false;
        $always_display_offer = !empty(Config::getSetting('always_display_offer'));
        if (!empty($offer_id) && self::isOfferApplicable($offer_id)) {
            $data = Offer::prepareMetaData($offer_id, $quantity, $variation_id, $variation_attributes);
            $is_checkout_upsells = ($data && $data['campaign_type'] == 'checkout_upsells');
            $allow_remove = !($is_checkout_upsells && $always_display_offer);
            $add_limit = (int)Config::getSetting('offer_add_limit');
            if ($is_checkout_upsells && $always_display_offer && !empty($add_limit) && $add_limit <= count(self::getAppliedOffers(true))) {
                $result['status'] = 'notice';
                $result['message'] = apply_filters('cuw_offer_limit_reached_message',
                    // translators: %s offer add limit.
                    esc_html(sprintf(__("You can add only %s offer(s) at a time.", 'checkout-upsell-and-order-bumps'), $add_limit))
                );
            } elseif ($data) {
                try {
                    $product = $data['product'];
                    $key = WC::addToCart($product['id'], $product['qty'], $product['variation_id'], $product['variation'], ['cuw_offer' => $data]);
                    if ($key) {
                        do_action('cuw_offer_added_to_cart', $key, $data);
                        $success_message = Config::getSetting('offer_added_notice_message');
                        $result['message'] = apply_filters('cuw_offer_add_to_cart_success_message', __($success_message, 'checkout-upsell-and-order-bumps')); //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
                        $result['status'] = 'success';
                        $result['remove_offer'] = $allow_remove;
                    } else {
                        $result['remove_offer'] = true;
                    }

                    if (!empty($add_limit) && $add_limit <= count(self::getAppliedOffers(true))) {
                        $result['remove_all_offers'] = $allow_remove;
                    }

                    if ($is_checkout_upsells) {
                        $result['cart_item_key'] = $key;
                    }

                    $to_reload = apply_filters('cuw_offer_reload_page', false, $data['campaign_type'] ?? '');
                    $result['reload_page'] = $to_reload;

                    $result['offer_id'] = $offer_id;
                } catch (\Exception $e) {
                    $result['message'] = 'Unexpected error occurred.';
                }
            }
        } else {
            $result['message'] = apply_filters('cuw_offer_add_to_cart_error_message', esc_html__("Unable to apply offer.", 'checkout-upsell-and-order-bumps'));
            $result['remove_offer'] = true;
        }

        if (!empty($result['message'])) {
            if ($is_checkout_upsells && !$always_display_offer && Config::getSetting('offer_notice_display_location') == 'offer_location') {
                $result['notice'] = WC::getNotice($result['message'], $result['status']);
                if ($location) {
                    Offer::setNotice($location, $offer_id, $result['message'], $result['status']);
                }
            } else {
                WC::addNotice($result['message'], $result['status']);
            }
        }
        return $result;
    }

    /**
     * Check if the cart has a product.
     *
     * @param int $product_id
     * @return bool
     */
    public static function hasProduct($product_id)
    {
        return in_array($product_id, self::getProductIds());
    }

    /**
     * Check if the offer is applied
     *
     * @param int $offer_id
     * @return bool
     */
    public static function isOfferApplied($offer_id)
    {
        return in_array($offer_id, array_column(self::getAppliedOffers(), 'id'));
    }

    /**
     * Check if the product is applicable
     *
     * @param int $campaign_id
     * @return bool
     */
    public static function isProductApplicable($campaign_id)
    {
        if (!isset(self::$campaign_results[$campaign_id])) {
            $is_valid = false;
            $campaign = CampaignModel::get($campaign_id, ['id', 'type', 'enabled', 'start_on', 'end_on', 'conditions']);
            if (!empty($campaign) && $campaign['enabled']) {
                if (Campaign::isSchedulePassed($campaign['start_on'], $campaign['end_on'])) {
                    $is_valid = true;
                    if (apply_filters('cuw_recheck_cart_conditions', true, $campaign)) {
                        $cart_data = self::getData(['exclude_campaign_id' => $campaign_id]);
                        $is_valid = Campaign::isConditionsPassed($campaign['conditions'], $cart_data);
                    }
                }
            }

            self::$campaign_results[$campaign_id] = [
                'is_valid' => $is_valid,
            ];
        }

        if (!self::$campaign_results[$campaign_id]['is_valid']) {
            return false;
        }
        return true;
    }

    /**
     * Check if the offer is applicable
     *
     * @param int $offer_id
     * @return bool
     */
    public static function isOfferApplicable($offer_id)
    {
        $cart = self::getData();
        if (empty($cart['products']) && apply_filters('cuw_remove_applied_offers_when_cart_contains_no_other_products', true)) {
            return false;
        }

        if (!isset(self::$offer_results[$offer_id])) {
            $is_valid = false;
            $offer = OfferModel::get($offer_id, ['id', 'campaign_id', 'usage_limit', 'usage_limit_per_user', 'usage_count']);
            if (!empty($offer) && Offer::isValid($offer)) {
                $is_valid = true;
            }

            self::$offer_results[$offer_id] = [
                'is_valid' => $is_valid,
                'data' => $offer,
            ];
        }

        if (!self::$offer_results[$offer_id]['is_valid']) {
            return false;
        }

        $offer = self::$offer_results[$offer_id]['data'];
        $campaign_id = $offer['campaign_id'];
        if (!isset(self::$campaign_results[$campaign_id])) {
            $is_valid = false;
            $campaign = CampaignModel::get($campaign_id, ['id', 'enabled', 'conditions', 'start_on', 'end_on']);
            if (!empty($campaign) && $campaign['enabled']) {
                if (Campaign::isSchedulePassed($campaign['start_on'], $campaign['end_on'])) {
                    if (Campaign::isConditionsPassed($campaign['conditions'], $cart)) {
                        $is_valid = true;
                    }
                }
            }

            self::$campaign_results[$campaign_id] = [
                'is_valid' => $is_valid,
            ];
        }

        if (!self::$campaign_results[$campaign_id]['is_valid']) {
            return false;
        }
        return true;
    }
}