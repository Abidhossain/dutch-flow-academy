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

class WC
{
    /**
     * Get the product object.
     *
     * @param mixed $object_or_id
     * @return \WC_Product|false
     */
    public static function getProduct($object_or_id = false)
    {
        if (is_object($object_or_id) && is_a($object_or_id, '\WC_Product')) {
            return $object_or_id;
        } elseif (function_exists('wc_get_product') && $product = wc_get_product($object_or_id)) {
            return $product;
        }
        return false;
    }

    /**
     * Check if the product is purchasable
     *
     * @param mixed $object_or_id
     * @param int|float $quantity
     * @return bool
     */
    public static function isPurchasableProduct($object_or_id, $quantity = 1, $check_cart_existing_quantity = true)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'is_purchasable') && $product->is_purchasable()) {
            if (method_exists($product, 'get_status') && $product->get_status() != 'publish') {
                return false;
            }
            if (method_exists($product, 'is_in_stock') && !$product->is_in_stock()) {
                return false;
            }

            if (apply_filters('cuw_check_cart_existing_quantity', $check_cart_existing_quantity, $product, $quantity)) {
                if ($quantity == '') {
                    $quantity = 1;
                }
                $cart_items = self::getCartItems();
                if (!empty($cart_items)) {
                    $product_id = $product->get_id();
                    foreach ($cart_items as $cart_item_key => $cart_item) {
                        $cart_item_product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
                        if ($cart_item_product_id == $product_id) {
                            $quantity += (int)$cart_item['quantity'];
                        }
                    }
                }
            }

            if (method_exists($product, 'has_enough_stock') && !$product->has_enough_stock($quantity)) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Check if the product is visible or not.
     *
     * @param mixed $object_or_id
     * @return bool
     */
    public static function isProductVisible($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_catalog_visibility')) {
            if ($product->get_catalog_visibility() == 'hidden') {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the product can backorder
     *
     * @param mixed $object_or_id
     * @return bool
     */
    public static function isBackordersAllowedProduct($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'backorders_allowed')) {
            return $product->backorders_allowed();
        }
        return false;
    }

    /**
     * Check if the page is woocommerce page.
     *
     * @param string $page
     * @param bool $deep
     * @return bool
     */
    public static function is($page, $deep = false)
    {
        switch ($page) {
            case 'home':
                return function_exists('is_front_page') && is_front_page();
            case 'shop':
                return function_exists('is_shop') && is_shop();
            case 'woocommerce': // shop or product_taxonomy or product
                return function_exists('is_woocommerce') && is_woocommerce();
            case 'product_taxonomy':
                return function_exists('is_product_taxonomy') && is_product_taxonomy();
            case 'product_category':
                return function_exists('is_product_category') && is_product_category();
            case 'product_tag':
                return function_exists('is_product_tag') && is_product_tag();
            case 'product':
                return function_exists('is_product') && is_product();
            case 'cart':
                return function_exists('is_cart') && is_cart()
                    || ($deep && function_exists('has_block') && has_block('woocommerce/cart'));
            case 'checkout':
                return function_exists('is_checkout') && is_checkout()
                    || ($deep && function_exists('has_block') && has_block('woocommerce/checkout'));
            case 'endpoint':
                return function_exists('is_wc_endpoint_url') && is_wc_endpoint_url();
            default:
                return function_exists('is_wc_endpoint_url') && is_wc_endpoint_url(str_replace('_', '-', $page));
        }
    }

    /**
     * Get page.
     *
     * @param string|int $page_or_id
     * @return \WP_Post|null
     */
    public static function getPage($page_or_id)
    {
        $page_id = $page_or_id;
        if ($page_or_id == 'cart') {
            $page_id = get_option('woocommerce_cart_page_id');
        } elseif ($page_or_id == 'checkout') {
            $page_id = get_option('woocommerce_checkout_page_id');
        }
        return is_numeric($page_id) ? get_post($page_id) : null;
    }

    /**
     * Check cart block enabled.
     *
     * @return bool
     */
    public static function cartBlockEnabled()
    {
        $cart_page = self::getPage('cart');
        return $cart_page && function_exists('has_block') && has_block('woocommerce/cart', $cart_page);
    }

    /**
     * Check checkout block enabled.
     *
     * @return bool
     */
    public static function checkoutBlockEnabled()
    {
        $checkout_page = self::getPage('checkout');
        return $checkout_page && function_exists('has_block') && has_block('woocommerce/checkout', $checkout_page);
    }

    /**
     * Check if the current request is store api.
     *
     * @return bool
     */
    public static function isStoreApi()
    {
        return function_exists('WC') && method_exists(WC(), 'is_store_api_request') && WC()->is_store_api_request();
    }

    /**
     * Returns current WooCommerce version.
     *
     * @return bool
     */
    public static function getVersion()
    {
        return function_exists('WC') && isset(WC()->version) ? WC()->version : '';
    }

    /**
     * Check minimum version requirement.
     */
    public static function requiredVersion($minimum_version)
    {
        return self::getVersion() && version_compare(self::getVersion(), $minimum_version, '>=');
    }

    /**
     * Get page URL.
     *
     * @param string $page
     * @return string
     */
    public static function getPageUrl($page = 'home')
    {
        switch ($page) {
            case 'home':
                return function_exists('home_url') ? home_url() : '';
            case 'shop':
                return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
            case 'cart':
                return function_exists('wc_get_cart_url') ? wc_get_cart_url() : '';
            case 'checkout':
                return function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '';
        }
        return '';
    }

    /**
     * To get current endpoint.
     *
     * @return string
     */
    public static function getCurrentEndpoint()
    {
        global $wp;
        if (is_object($wp) && isset($wp->query_vars)) {
            if (function_exists('WC') && isset(WC()->query) && method_exists(WC()->query, 'get_query_vars')) {
                $wc_endpoints = WC()->query->get_query_vars();
                foreach ($wc_endpoints as $key => $value) {
                    if (isset($wp->query_vars[$key])) {
                        return $value;
                    }
                }
            }
        }
        return '';
    }

    /**
     * To get values by calling function.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = false)
    {
        if (function_exists("wc_get_$key")) {
            return call_user_func("wc_get_$key");
        } elseif (function_exists("get_woocommerce_$key")) {
            return call_user_func("get_woocommerce_$key");
        }
        return $default;
    }

    /**
     * Get cart object
     *
     * @return \WC_Cart|null
     */
    public static function getCart()
    {
        if (function_exists('WC') && isset(WC()->cart)) {
            return WC()->cart;
        }
        return null;
    }

    /**
     * Get cart subtotal
     *
     * @return int|float
     */
    public static function getCartSubtotal($tax = 'excl')
    {
        if (function_exists('WC') && isset(WC()->cart) && method_exists(WC()->cart, 'get_subtotal')) {
            $subtotal = WC()->cart->get_subtotal();
            if (($tax === 'incl' || self::displayPricesIncludingTax($tax)) && method_exists(WC()->cart, 'get_subtotal_tax')) {
                $subtotal += WC()->cart->get_subtotal_tax();
            }
            return $subtotal;
        }
        return 0;
    }

    /**
     * Display price is including tax.
     *
     * @param string $in
     * @return bool
     */
    public static function displayPricesIncludingTax($in = 'shop')
    {
        return in_array($in, ['shop', 'cart']) && get_option('woocommerce_tax_display_' . $in) === 'incl';
    }

    /**
     * Get display tax setting by page.
     *
     * @param string $page
     * @return string
     */
    public static function getDisplayTaxSettingByPage($page)
    {
        return in_array($page, ['cart', 'checkout']) ? 'cart' : 'shop';
    }

    /**
     * Get cart items
     *
     * @return array
     */
    public static function getCartItems()
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'get_cart_contents')) {
            return $cart->get_cart_contents();
        }
        return [];
    }

    /**
     * Get cart item
     *
     * @param string $key
     * @return array
     */
    public static function getCartItem($key)
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'get_cart_item')) {
            return $cart->get_cart_item($key);
        }
        return [];
    }

    /**
     * Add a product to the cart
     *
     * @param int $product_id
     * @param int|float $quantity
     * @param int $variation_id
     * @param array $variation
     * @param array $cart_item_data
     * @return string|false $cart_item_key
     * @throws \Exception
     */
    public static function addToCart($product_id = 0, $quantity = 1, $variation_id = 0, $variation = [], $cart_item_data = [])
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'add_to_cart')) {
            return $cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
        }
        return false;
    }

    /**
     * Remove cart item
     *
     * @param string $key
     * @return bool
     */
    public static function removeCartItem($key)
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'remove_cart_item')) {
            return $cart->remove_cart_item($key);
        }
        return false;
    }

    /**
     * Replace cart item
     *
     * @param string $old_key
     * @param string $new_key
     * @return bool
     */
    public static function replaceCartItem($old_key, $new_key)
    {
        global $woocommerce;
        if (!empty($woocommerce) && isset($woocommerce->cart) && !empty($woocommerce->cart->cart_contents)) {
            $new_item = WC::getCartItem($new_key);
            $old_item = WC::getCartItem($old_key);
            if (!empty($new_item) && !empty($old_item)) {
                $pos = 0;
                $count = count($woocommerce->cart->cart_contents);
                foreach ($woocommerce->cart->cart_contents as $key => $item) {
                    if ($old_key == $key) {
                        break;
                    }
                    $pos++;
                }

                self::removeCartItem($new_key);

                $woocommerce->cart->cart_contents = array_slice($woocommerce->cart->cart_contents, 0, $pos, true)
                    + array($new_key => $new_item)
                    + array_slice($woocommerce->cart->cart_contents, $pos + 1, $count - $pos, true);
                $woocommerce->cart->set_session();

                self::removeCartItem($old_key);
                return true;
            }
        }
        return false;
    }

    /**
     * Set cart item price
     *
     * @param array $item
     * @param int|float $price
     * @return bool
     */
    public static function setCartItemPrice($item, $price)
    {
        if (is_array($item) && isset($item['data'])) {
            if (is_object($item['data']) && method_exists($item['data'], 'set_price')) {
                $item['data']->set_price($price);
                return true;
            }
        }
        return false;
    }

    /**
     * Set regular price.
     *
     * @param array $cart_item
     * @param int|float $price
     * @return bool
     */
    public static function setCartItemRegularPrice($cart_item, $price)
    {
        if (!empty($cart_item) && is_object($cart_item['data']) && is_numeric($price)) {
            if (method_exists($cart_item['data'], 'set_regular_price')) {
                $cart_item['data']->set_regular_price($price);
                return true;
            }
        }
        return false;
    }

    /**
     * Set cart item quantity
     *
     * @param string $key
     * @param int|float $quantity
     * @param bool $refresh_totals
     * @return bool
     */
    public static function setCartItemQty($key, $quantity = 1, $refresh_totals = true)
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'set_quantity')) {
            return $cart->set_quantity($key, $quantity, $refresh_totals);
        }
        return false;
    }

    /**
     * Get applied coupons in cart
     *
     * @return array
     */
    public static function getAppliedCouponsInCart()
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'get_applied_coupons')) {
            return $cart->get_applied_coupons();
        }
        return [];
    }

    /**
     * Apply coupon to cart
     *
     * @param string $code
     * @return bool
     */
    public static function applyCartCoupon($code)
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'apply_coupon')) {
            if (method_exists($cart, 'has_discount') && !$cart->has_discount($code)) {
                return $cart->apply_coupon($code);
            }
            return true;
        }
        return false;
    }

    /**
     * Remove a coupon from cart
     *
     * @param string $code
     * @return bool
     */
    public static function removeCartCoupon($code)
    {
        $cart = self::getCart();
        if (is_object($cart) && method_exists($cart, 'remove_coupon')) {
            if (method_exists($cart, 'has_discount') && $cart->has_discount($code)) {
                return $cart->remove_coupon($code);
            }
            return true;
        }
        return false;
    }

    /**
     * Check is a coupon existence in DB.
     *
     * @param string $name
     * @return bool
     * */
    static function isCouponExists($name)
    {
        $posts = get_posts([
            'name' => $name,
            'post_type' => 'shop_coupon'
        ]);
        return !empty($posts) && count($posts) > 0;
    }

    /**
     * Get order object
     *
     * @param int|\WC_Order $object_or_id
     * @return \WC_Order|false
     */
    public static function getOrder($object_or_id)
    {
        if (is_object($object_or_id) && is_a($object_or_id, '\WC_Order')) {
            return $object_or_id;
        } elseif (function_exists('wc_get_order') && $order = wc_get_order($object_or_id)) {
            return $order;
        }
        return false;
    }

    /**
     * Get order items
     *
     * @param int|\WC_Order $object_or_id
     * @return null|\WC_Order_Item[]
     */
    public static function getOrderItems($object_or_id)
    {
        $order = $object_or_id;
        if (is_numeric($object_or_id)) {
            $order = self::getOrder($object_or_id);
        }
        if (is_object($order) && method_exists($order, 'get_items')) {
            return $order->get_items();
        }
        return [];
    }

    /**
     * Get order id by key
     *
     * @return int|string
     */
    public static function getOrderIdByKey($order_key)
    {
        $order_id = '';
        if (function_exists('wc_get_order_id_by_order_key') && !empty($order_key)) {
            $order_id = wc_get_order_id_by_order_key($order_key);
        }
        return $order_id;
    }

    /**
     * Get parent order
     *
     * @param int|\WC_Order $object_or_id
     * @return \WC_Order|false
     */
    public static function getParentOrder($object_or_id)
    {
        $order = self::getOrder($object_or_id);
        if (is_object($order) && method_exists($order, 'get_parent_id')) {
            return self::getOrder($order->get_parent_id());
        }
        return false;
    }

    /**
     * Add a product to order
     *
     * @param int|\WC_Order $order_or_id
     * @param int|\WC_Product $product_or_id
     * @param array $args
     * @return int|false $order_item_id
     */
    public static function addToOrder($order_or_id, $product_or_id, $args = [])
    {
        $order = self::getOrder($order_or_id);
        $product = self::getProduct($product_or_id);
        if (is_object($order) && is_object($product) && method_exists($order, 'add_product')) {
            $price = $args['price'] ?? null;
            $quantity = $args['quantity'] ?? 1;
            if (!empty($args['extra_price'])) {
                foreach ($args['extra_price'] as $extra_price) {
                    if (!empty($extra_price) && is_numeric($extra_price)) {
                        $price += $extra_price;
                    }
                }
            }
            if (function_exists('wc_prices_include_tax') && wc_prices_include_tax() && function_exists('wc_get_price_excluding_tax')) {
                $price = wc_get_price_excluding_tax($product, ['price' => $price]);
            }
            if (is_numeric($price)) {
                $args['subtotal'] = $price * $quantity;
                $args['total'] = $price * $quantity;
            }
            return $order->add_product($product, $quantity, $args);
        }
        return false;
    }

    /**
     * Returns available order statues.
     *
     * @return array
     */
    public static function getOrderStatuses($category = 'all')
    {
        $order_status = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
        switch ($category) {
            case 'all':
                return $order_status;
            case 'success':
                return apply_filters('cuw_success_order_status', array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed'));
            case 'failed':
                return apply_filters('cuw_failed_order_status', array('wc-cancelled', 'wc-refunded', 'wc-failed'));
        }
        return [];
    }

    /**
     * Get applied coupons in order
     *
     * @param int|\WC_Order $object_or_id
     * @return array
     */
    public static function getAppliedCouponsInOrder($object_or_id)
    {
        $order = self::getOrder($object_or_id);
        if (is_object($order) && method_exists($order, 'get_coupon_codes')) {
            return $order->get_coupon_codes();
        }
        return [];
    }

    /**
     * Add notice
     *
     * @param string $message
     * @param string $status
     * @param array $data
     * @return bool
     */
    public static function addNotice($message, $status = 'success', $data = [])
    {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($message, $status, $data);
            return true;
        }
        return false;
    }

    /**
     * Get notice
     *
     * @param string $message
     * @param string $status
     * @param array $data
     * @return string
     */
    public static function getNotice($message, $status = 'success', $data = [])
    {
        if (function_exists('wc_print_notice')) {
            return wc_print_notice($message, $status, $data, true);
        }
        return '';
    }

    /**
     * Print notice
     *
     * @param string $message
     * @param string $status
     * @param array $data
     * @return void
     */
    public static function printNotice($message, $status = 'success', $data = [])
    {
        if (function_exists('wc_print_notice')) {
            wc_print_notice($message, $status, $data);
        }
    }

    /**
     * Get product parent ID
     *
     * @param mixed $object_or_id
     * @return int|false
     */
    public static function getProductParentId($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_parent_id')) {
            return $product->get_parent_id();
        }
        return false;
    }

    /**
     * Get product children IDs
     *
     * @param mixed $object_or_id
     * @return array|false
     */
    public static function getProductChildrenIds($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_children')) {
            return $product->get_children();
        }
        return false;
    }

    /**
     * Get product categories IDs
     *
     * @param mixed $object_or_id
     * @return array
     */
    public static function getProductCategoryIds($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_parent_id')) {
            return $product->get_category_ids();
        }
        return [];
    }

    /**
     * Get the product title
     *
     * @param mixed $object_or_id
     * @param bool $formatted
     * @return string
     */
    public static function getProductTitle($object_or_id, $formatted = false)
    {
        $id = $title = '';
        if (is_numeric($object_or_id) && function_exists('get_the_title')) {
            $id = $object_or_id;
            $title = get_the_title($object_or_id);
        } elseif ($product = self::getProduct($object_or_id)) {
            if (is_object($product) && method_exists($product, 'get_id') && method_exists($product, 'get_title')) {
                $id = $product->get_id();
                $title = $product->get_title();
            }
        }
        if ($formatted && $id) {
            return '#' . $id . ' ' . $title;
        }
        return $title;
    }

    /**
     * Get product type
     *
     * @param mixed $object_or_id
     * @return string|false
     */
    public static function getProductType($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_type')) {
            return $product->get_type();
        }
        return false;
    }

    /**
     * Checks the product type
     *
     * @param mixed $object_or_id
     * @param array|string $type
     * @return bool
     */
    public static function isProductType($object_or_id, $type)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'is_type')) {
            return $product->is_type($type);
        }
        return false;
    }

    /**
     * Check if the product type is variable
     *
     * @param mixed $object_or_id
     * @return bool
     */
    public static function isVariableProduct($object_or_id)
    {
        if (self::isProductType($object_or_id, ['variable'])) {
            return true;
        } elseif (self::getProductChildrenIds($object_or_id)) {
            return true;
        }
        return false;
    }

    /**
     * Get product stock quantity
     *
     * @param mixed $object_or_id
     * @return int|null|false
     */
    public static function getProductStockQty($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_stock_quantity')) {
            return $product->get_stock_quantity();
        }
        return false;
    }

    /**
     * Get product SKU
     *
     * @param mixed $object_or_id
     * @return String|false
     */
    public static function getProductSku($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_sku')) {
            return $product->get_sku();
        }
        return false;
    }

    /**
     * Get product tag IDs
     *
     * @param mixed $object_or_id
     * @return array
     */
    public static function getProductTagIds($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_tag_ids')) {
            return $product->get_tag_ids();
        }
        return [];
    }

    /**
     * Get product attributes
     *
     * @param mixed $object_or_id
     * @return array
     */
    public static function getProductAttributes($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_attributes')) {
            return $product->get_attributes();
        }
        return [];
    }

    /**
     * Get variable product attributes used for variations
     *
     * @param int|string|object|\WC_Product_Variable|false $object_or_id
     * @return array
     */
    public static function getVariationAttributes($object_or_id)
    {
        $variable = self::getProduct($object_or_id);
        if (is_object($variable) && method_exists($variable, 'get_variation_attributes')) {
            return $variable->get_variation_attributes();
        }
        return [];
    }

    /**
     * Find matching product variation id by attributes
     *
     * @param int|string|object|\WC_Product_Variable|false $object_or_id
     * @param array $attributes
     * @return int|false
     */
    public static function getVariationIdByAttributes($object_or_id, $attributes)
    {
        $variable = self::getProduct($object_or_id);
        if (class_exists('\WC_Product_Data_Store_CPT') && method_exists('\WC_Product_Data_Store_CPT', 'find_matching_product_variation')) {
            return (new \WC_Product_Data_Store_CPT())->find_matching_product_variation($variable, $attributes);
        }
        return false;
    }

    /**
     * Get formatted variation info.
     *
     * @param int|string|object|\WC_Product_Variation|false $object_or_id
     * @param bool $with_attribute_labels
     * @return string|false
     */
    public static function getFormattedVariationInfo($object_or_id, $with_attribute_labels = true)
    {
        $variation = self::getProduct($object_or_id);
        if (is_object($variation) && is_a($variation, '\WC_Product_Variation') && function_exists('wc_get_formatted_variation')) {
            $with_attribute_labels = apply_filters('cuw_show_variation_info_with_attribute_labels', $with_attribute_labels, $variation);
            $variation_name = wc_get_formatted_variation($variation, true, $with_attribute_labels);
            return apply_filters('cuw_formatted_variation_info', $variation_name, $variation, $with_attribute_labels);
        }
        return false;
    }

    /**
     * Returns default variation id.
     *
     * @param \WC_Product_Variable|\WC_Product $product
     * @return int
     */
    public static function getDefaultVariationId($product)
    {
        if (method_exists($product, 'get_available_variations') && method_exists($product, 'get_variation_default_attribute')) {
            foreach ($product->get_available_variations() as $variation_values) {
                $is_default_variation = false;
                foreach ($variation_values['attributes'] as $key => $attribute_value) {
                    $attribute_name = str_replace('attribute_', '', $key);
                    $default_value = $product->get_variation_default_attribute($attribute_name);
                    if ($default_value == $attribute_value) {
                        $is_default_variation = true;
                    } else {
                        $is_default_variation = false;
                        break;
                    }
                }
                if ($is_default_variation) {
                    return $variation_values['variation_id'];
                }
            }
        }
        return 0;
    }

    /**
     * Get the product image
     *
     * @param mixed $object_or_id
     * @return string
     */
    public static function getProductImage($object_or_id)
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && method_exists($product, 'get_parent_id')) {
            return $product->get_image();
        }
        return '';
    }

    /**
     * Get the taxonomy name
     *
     * @param int|\WP_Term $term
     * @param bool $formatted
     * @return string
     */
    public static function getTaxonomyName($term, $formatted = false)
    {
        $name = $parent_name = '';
        if (is_numeric($term)) {
            $term = get_term($term);
        }
        if (is_object($term) && isset($term->name)) {
            $name = $term->name;
        }
        if ($formatted && !empty($term->parent)) {
            $parent = get_term($term->parent);
            if ($parent && isset($parent->name)) {
                $parent_name = $parent->name . ' -> ';
                if (isset($parent->parent) && !empty($parent->parent)) {
                    $grand_parent = get_term($parent->parent);
                    if ($grand_parent && isset($grand_parent->name)) {
                        $parent_name .= $grand_parent->name . ' -> ';
                        if (isset($grand_parent->parent) && !empty($grand_parent->parent)) {
                            $grand_grand_parent = get_term($grand_parent->parent);
                            if ($grand_grand_parent && isset($grand_grand_parent->name)) {
                                $parent_name .= $grand_grand_parent->name . ' -> ';
                            }
                        }
                    }
                }
            }
        }
        return $parent_name . $name;
    }

    /**
     * Returns the price including or excluding tax
     *
     * @param mixed $object_or_id
     * @param int|float $price
     * @param int|float $qty
     * @param string $in
     * @return float|string|false
     */
    public static function getPriceToDisplay($object_or_id, $price = '', $qty = 1, $in = 'shop')
    {
        $product = self::getProduct($object_or_id);
        if (is_object($product) && function_exists('wc_get_price_including_tax') && function_exists('wc_get_price_excluding_tax')) {
            if ($in === 'incl' || self::displayPricesIncludingTax($in)) {
                return wc_get_price_including_tax($product, compact('price', 'qty'));
            }
            return wc_get_price_excluding_tax($product, compact('price', 'qty'));
        }
        return false;
    }

    /**
     * Get formatted price html
     *
     * @param int $price
     * @param array $args
     * @return string
     */
    public static function formatPrice($price, $args = [])
    {
        if (function_exists('wc_price')) {
            return wc_price($price, $args);
        }
        return (string)$price;
    }

    /**
     * Get formatted price raw
     *
     * @param int|float $price
     * @param array $args
     * @return string
     */
    public static function formatPriceRaw($price, $args = [])
    {
        $args = array_merge([
            'with_currency' => true,
            'trim_zeros' => false,
        ], $args);
        if (is_numeric($price)) {
            $decimals = function_exists('wc_get_price_decimals') ? wc_get_price_decimals() : 2;
            $decimal_separator = function_exists('wc_get_price_decimal_separator') ? wc_get_price_decimal_separator() : '.';
            $thousand_separator = function_exists('wc_get_price_thousand_separator') ? wc_get_price_thousand_separator() : '';
            $price = number_format($price, $decimals, $decimal_separator, $thousand_separator);
            if ($args['trim_zeros']) {
                $price = strpos((string)$price, $decimal_separator) !== false ? rtrim(rtrim($price, '0'), $decimal_separator) : $price;
            }
            if ($args['with_currency'] && function_exists('get_woocommerce_price_format') && function_exists('get_woocommerce_currency_symbol')) {
                $price = sprintf(get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $price);
            }
        }
        return (string)$price;
    }

    /**
     * Get formatted price html (raw)
     *
     * @param int|float $price
     * @param int|float|null $regular_price
     * @param array $args
     * @return string
     */
    public static function formatPriceHtmlRaw($price, $regular_price = null, $args = [])
    {
        if ($regular_price && $regular_price > $price) {
            return '<del>' . WC::formatPriceRaw($regular_price, $args) . '</del> <ins>' . WC::formatPriceRaw($price, $args) . '</ins>';
        }
        return '<ins>' . WC::formatPriceRaw($price) . '</ins>';
    }

    /**
     * Get formatted sale price html
     *
     * @param int|float $regular_price
     * @param int|float $sale_price
     * @return string
     */
    public static function formatSalePrice($regular_price, $sale_price)
    {
        if (function_exists('wc_format_sale_price')) {
            return wc_format_sale_price($regular_price, $sale_price);
        }
        return '<del>' . $regular_price . '</del>' . ' ' . '<ins>' . $sale_price . '</ins>';
    }

    /**
     * Get formatted price range
     *
     * @param int|float $from_price
     * @param int|float $to_price
     * @return string
     */
    public static function formatPriceRange($from_price, $to_price)
    {
        if (function_exists('wc_format_price_range')) {
            return wc_format_price_range($from_price, $to_price);
        }
        return self::formatPrice($from_price) . ' – ' . self::formatPrice($to_price);
    }

    /**
     * Get base currency code
     *
     * @return string
     */
    public static function getCurrency()
    {
        if (function_exists('get_woocommerce_currency')) {
            return get_woocommerce_currency();
        }
        return '';
    }

    /**
     * Get currency symbol
     *
     * @param string $currency
     * @return string
     */
    public static function getCurrencySymbol($currency = '')
    {
        if (function_exists('get_woocommerce_currency_symbol')) {
            return get_woocommerce_currency_symbol($currency);
        }
        return '';
    }

    /**
     * Get data from session
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSession($key, $default = false)
    {
        if (function_exists('WC') && is_object(WC()->session) && method_exists(WC()->session, 'get')) {
            return WC()->session->get($key, $default);
        }
        return $default;
    }

    /**
     * Set data to session
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setSession($key, $value)
    {
        if (function_exists('WC') && is_object(WC()->session) && method_exists(WC()->session, 'set')) {
            WC()->session->set($key, $value);
            return true;
        }
        return false;
    }

    /**
     * Maybe to set customer session.
     *
     * @return void
     */
    public static function maybeLoadSession()
    {
        if (function_exists('WC') && isset(WC()->session) && is_object(WC()->session) && method_exists(WC()->session, 'has_session')) {
            if (!WC()->session->has_session() && method_exists(WC()->session, 'set_customer_session_cookie')) {
                WC()->session->set_customer_session_cookie(true);
            }
        }
    }

    /**
     * To get current customer's billing email.
     *
     * @return string|null
     */
    public static function getCustomerBillingEmail()
    {
        $current_user_email = null;
        if (function_exists('WC') && is_object(WC()->customer) && method_exists(WC()->customer, 'get_billing_email')) {
            $current_user_email = sanitize_email(WC()->customer->get_billing_email());
        }
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (empty($current_user_email) && isset($_GET['wc-ajax']) && $_GET['wc-ajax'] == 'update_order_review' && isset($_POST['post_data'])) {
            parse_str((string)wp_unslash($_POST['post_data']), $post_data);
            if (!empty($post_data['billing_email'])) {
                $current_user_email = sanitize_email($post_data['billing_email']);
            }
        }
        return $current_user_email;
    }

    /**
     * To declare feature compatibility.
     *
     * @param string $feature_id
     * @param string $plugin_file
     * @return bool
     */
    public static function declareFeatureCompatibility($feature_id, $plugin_file)
    {
        if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            if (method_exists('Automattic\WooCommerce\Utilities\FeaturesUtil', 'declare_compatibility')) {
                return \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility($feature_id, $plugin_file);
            }
        }
        return false;
    }

    /**
     * Check Custom Orders Table feature (HPOS) is enabled or not.
     *
     * @return bool
     */
    public static function customOrdersTableIsEnabled()
    {
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
            if (method_exists('Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled')) {
                return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            }
        }
        return false;
    }

    /**
     * To check if the variable product is visible.
     *
     * @param $object_or_id
     * @return bool
     */
    public static function isVariationVisible($object_or_id)
    {
        if (!empty($object_or_id)) {
            $product = self::getProduct($object_or_id);
            if (!empty($product) && is_a($product, 'WC_Product_Variation') && method_exists($product, 'variation_is_visible')) {
                return $product->variation_is_visible();
            }
        }
        return false;
    }

    /**
     * To check if the variable product is active.
     *
     * @param $object_or_id
     * @return bool
     */
    public static function isVariationActive($object_or_id)
    {
        if (!empty($object_or_id)) {
            $product = self::getProduct($object_or_id);
            if (!empty($product) && is_a($product, 'WC_Product_Variation') && method_exists($product, 'variation_is_active')) {
                return $product->variation_is_active();
            }
        }
        return false;
    }
}