<?php

use CUW\App\Modules\Campaigns\CartUpsells;
/**
 * Order/Cart/Checkout REST API Controller
 */

defined('ABSPATH') || exit;

/**
 * Order/Cart/Checkout API Controller class
 */
class LLMS_Extend_REST_Order_Controller {
    /**
     * Register REST API routes
     *
     * @return void
     */
    public function register_routes($namespace) {
        // Add to Cart
        register_rest_route(
            $namespace,
            '/cart/add',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'add_to_cart'),
                'permission_callback' => array($this, 'check_cart_permissions'),
                'args' => array(
                    'product_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                    'quantity' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 1,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Cart View
        register_rest_route(
            $namespace,
            '/cart',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_cart'),
                'permission_callback' => array($this, 'check_cart_permissions'),
            )
        );

        // Update Cart Item
        register_rest_route(
            $namespace,
            '/cart/update',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'update_cart_item'),
                'permission_callback' => array($this, 'check_cart_permissions'),
                'args' => array(
                    'cart_item_key' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'quantity' => array(
                        'required' => true,
                        'type' => 'integer',
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param >= 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Remove Cart Item
        register_rest_route(
            $namespace,
            '/cart/remove',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'remove_cart_item'),
                'permission_callback' => array($this, 'check_cart_permissions'),
                'args' => array(
                    'cart_item_key' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // Apply Coupon
        register_rest_route(
            $namespace,
            '/cart/coupon',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'apply_coupon'),
                'permission_callback' => array($this, 'check_cart_permissions'),
                'args' => array(
                    'coupon_code' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // Add Upsell to Cart
        register_rest_route(
            $namespace,
            '/cart/add-upsell',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'add_upsell_to_cart'),
                'permission_callback' => array($this, 'check_cart_permissions'),
                'args' => array(
                    'offer_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                    'quantity' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 1,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Confirm Payment (for mobile payments)
        register_rest_route(
            $namespace,
            '/checkout/confirm-payment',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'confirm_payment'),
                'permission_callback' => array($this, 'check_payment_permissions'),
                'args' => array(
                    'order_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                    'payment_data' => array(
                        'required' => true,
                        'type' => 'object',
                    ),
                    'transaction_id' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // Check Order Status
        register_rest_route(
            $namespace,
            '/order/(?P<order_id>\d+)/status',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_order_status'),
                'permission_callback' => array($this, 'check_order_permissions'),
                'args' => array(
                    'order_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // Get Checkout Data
        register_rest_route(
            $namespace,
            '/checkout',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_checkout_data'),
                'permission_callback' => array($this, 'check_cart_permissions'),
            )
        );

        // Process Checkout
        register_rest_route(
            $namespace,
            '/checkout',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'process_checkout'),
                'permission_callback' => array($this, 'check_cart_permissions'),
                'args' => array(
                    'billing_first_name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_last_name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_email' => array(
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => 'is_email',
                        'sanitize_callback' => 'sanitize_email',
                    ),
                    'billing_phone' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_address_1' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_address_2' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_city' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_state' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_postcode' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'billing_country' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'payment_method' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'coupon_code' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'terms' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'default' => false,
                    ),
                ),
            )
        );
    }

    /**
     * Ensure WooCommerce is properly initialized
     */
    private function ensure_woocommerce_initialized() {
        // Check if WooCommerce is available
        if (!function_exists('WC') || !WC()) {
            throw new Exception(__('WooCommerce is not available.', 'lifterlms-extend'));
        }

        // Initialize session if not already done
        if (!WC()->session) {
            WC()->initialize_session();
        }

        // Ensure cart is initialized
        if (!WC()->cart) {
            WC()->initialize_cart();
        }
    }

    /**
     * Check permissions for cart operations
     */
    public function check_cart_permissions($request) {
        return is_user_logged_in();
    }

    /**
     * Add product to cart
     */
    public function add_to_cart($request) {
        // Ensure WooCommerce is properly initialized
        if (!function_exists('WC') || !WC()) {
            return new WP_Error(
                'woocommerce_not_available',
                __('WooCommerce is not available.', 'lifterlms-extend'),
                array('status' => 500)
            );
        }

        // Initialize cart session if not already done
        if (!WC()->session) {
            WC()->initialize_session();
        }

        // Ensure cart is initialized
        if (!WC()->cart) {
            WC()->initialize_cart();
        }

        $product_id = $request->get_param('product_id');
        $quantity = $request->get_param('quantity');

        // Check if product exists
        $product = wc_get_product($product_id);
        if (!$product) {
            return new WP_Error(
                'product_not_found',
                __('Product not found.', 'lifterlms-extend'),
                array('status' => 404)
            );
        }

        // Add to cart
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);

        if (!$cart_item_key) {
            return new WP_Error(
                'cart_add_failed',
                __('Failed to add product to cart.', 'lifterlms-extend'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'cart_item_key' => $cart_item_key,
            'message' => __('Product added to cart successfully.', 'lifterlms-extend'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ));
    }

    /**
     * Get cart contents with upsell suggestions
     */
    public function get_cart($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $cart = WC()->cart;
        $cart_items = array();
        $cart_totals = $cart->get_totals();

        // Get cart items
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];

            // Check if this is an upsell offer
            $is_upsell = isset($cart_item['cuw_offer']);
            $special_price = null;
            $offer_data = null;

            if ($is_upsell) {
                $offer_data = $cart_item['cuw_offer'];
                $special_price = $offer_data['price'] ?? null;
            }

            $cart_items[] = array(
                'key' => $cart_item_key,
                'id' => $cart_item['product_id'],
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'quantity' => $cart_item['quantity'],
                'subtotal' => $cart_item['line_subtotal'],
                'total' => $cart_item['line_total'],
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'permalink' => $product->get_permalink(),
                'is_upsell' => $is_upsell,
                'offer_data' => $offer_data,
                'special_price' => $special_price,
            );
        }

        // Get upsell suggestions (using checkout-upsell-and-order-bumps plugin)
        $upsell_suggestions = $this->get_upsell_suggestions();

        return rest_ensure_response(array(
            'items' => $cart_items,
            'totals' => $cart_totals,
            'item_count' => $cart->get_cart_contents_count(),
            'upsell_suggestions' => $upsell_suggestions,
            'applied_coupons' => $cart->get_applied_coupons(),
        ));
    }

    /**
     * Update cart item quantity
     */
    public function update_cart_item($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $cart_item_key = $request->get_param('cart_item_key');
        $quantity = $request->get_param('quantity');

        $cart = WC()->cart;

        if ($quantity === 0) {
            $cart->remove_cart_item($cart_item_key);
            $message = __('Item removed from cart.', 'lifterlms-extend');
        } else {
            $cart->set_quantity($cart_item_key, $quantity);
            $message = __('Cart updated successfully.', 'lifterlms-extend');
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => $message,
            'cart_count' => $cart->get_cart_contents_count(),
        ));
    }

    /**
     * Remove cart item
     */
    public function remove_cart_item($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $cart_item_key = $request->get_param('cart_item_key');

        $removed = WC()->cart->remove_cart_item($cart_item_key);

        if (!$removed) {
            return new WP_Error(
                'cart_remove_failed',
                __('Failed to remove item from cart.', 'lifterlms-extend'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Item removed from cart.', 'lifterlms-extend'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ));
    }

    /**
     * Apply coupon to cart
     */
    public function apply_coupon($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $coupon_code = $request->get_param('coupon_code');

        $applied = WC()->cart->apply_coupon($coupon_code);

        if (!$applied) {
            return new WP_Error(
                'coupon_invalid',
                __('Invalid coupon code.', 'lifterlms-extend'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Coupon applied successfully.', 'lifterlms-extend'),
            'totals' => WC()->cart->get_totals(),
        ));
    }

    /**
     * Remove coupon from cart
     */
    public function remove_coupon($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $coupon_code = $request->get_param('coupon_code');

        $removed = WC()->cart->remove_coupon($coupon_code);

        if (!$removed) {
            return new WP_Error(
                'coupon_remove_failed',
                __('Failed to remove coupon.', 'lifterlms-extend'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Coupon removed successfully.', 'lifterlms-extend'),
            'totals' => WC()->cart->get_totals(),
        ));
    }

    /**
     * Add upsell offer to cart
     */
    public function add_upsell_to_cart($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $offer_id = $request->get_param('offer_id');
        $quantity = $request->get_param('quantity');

        // Check if UpsellWP plugin is active and has the required methods
        if (!class_exists('\CUW\App\Helpers\Cart') ||
            !method_exists('\CUW\App\Helpers\Cart', 'addOffer')) {
            return new WP_Error(
                'upsell_plugin_not_active',
                __('Upsell plugin is not active or compatible.', 'lifterlms-extend'),
                array('status' => 400)
            );
        }

        try {
            // Use the plugin's addOffer method which handles all the complex logic
            $result = \CUW\App\Helpers\Cart::addOffer($offer_id, $quantity);
            if ($result['status'] === 'success') {
                // Get updated cart data
                $cart = WC()->cart;
                $cart_items = array();
                $cart_totals = $cart->get_totals();

                foreach ($cart->get_cart() as $key => $cart_item) {
                    $prod = $cart_item['data'];
                    $cart_items[] = array(
                        'key' => $key,
                        'id' => $cart_item['product_id'],
                        'name' => $prod->get_name(),
                        'price' => $prod->get_price(),
                        'quantity' => $cart_item['quantity'],
                        'subtotal' => $cart_item['line_subtotal'],
                        'total' => $cart_item['line_total'],
                        'image' => wp_get_attachment_image_url($prod->get_image_id(), 'thumbnail'),
                        'permalink' => $prod->get_permalink(),
                        'is_upsell' => isset($cart_item['cuw_offer']),
                        'offer_data' => $cart_item['cuw_offer'] ?? null,
                        'special_price' => $cart_item['cuw_offer']['price'] ?? null,
                    );
                }

                return rest_ensure_response(array(
                    'success' => true,
                    'cart_item_key' => $result['cart_item_key'] ?? null,
                    'message' => $result['message'] ?: __('Upsell offer added to cart successfully.', 'lifterlms-extend'),
                    'cart_count' => $cart->get_cart_contents_count(),
                    'cart_items' => $cart_items,
                    'totals' => $cart_totals,
                    'reload_page' => $result['reload_page'] ?? false,
                    'remove_offer' => $result['remove_offer'] ?? null,
                    'remove_all_offers' => $result['remove_all_offers'] ?? false,
                ));
            } else {
                // Handle different error types from the plugin
                $error_message = $result['message'] ?: __('Failed to add upsell offer to cart.', 'lifterlms-extend');

                return new WP_Error(
                    'upsell_add_failed',
                    $error_message,
                    array('status' => 400)
                );
            }

        } catch (Exception $e) {
            return new WP_Error(
                'upsell_add_error',
                $e->getMessage(),
                array('status' => 400)
            );
        }
    }

    /**
     * Check permissions for payment confirmation operations
     */
    public function check_payment_permissions($request) {
        return is_user_logged_in();
    }

    /**
     * Check permissions for order operations
     */
    public function check_order_permissions($request) {
        return is_user_logged_in();
    }

    /**
     * Confirm payment and complete order (for mobile payments)
     */
    public function confirm_payment($request) {
        try {
            $order_id = $request->get_param('order_id');
            $payment_data = $request->get_param('payment_data');
            $transaction_id = $request->get_param('transaction_id');

            // Get the order
            $order = wc_get_order($order_id);
            if (!$order) {
                return new WP_Error(
                    'order_not_found',
                    __('Order not found.', 'lifterlms-extend'),
                    array('status' => 404)
                );
            }

            // Check if order belongs to current user
            if ($order->get_customer_id() !== get_current_user_id()) {
                return new WP_Error(
                    'unauthorized',
                    __('You are not authorized to access this order.', 'lifterlms-extend'),
                    array('status' => 403)
                );
            }

            // Check if order is still pending
            if ($order->get_status() !== 'pending') {
                return new WP_Error(
                    'invalid_order_status',
                    __('Order is not in pending status.', 'lifterlms-extend'),
                    array('status' => 400)
                );
            }

            // Verify payment data (this should be customized based on your payment processor)
            $payment_verified = $this->verify_payment($payment_data, $order);

            if (!$payment_verified) {
                return new WP_Error(
                    'payment_verification_failed',
                    __('Payment verification failed.', 'lifterlms-extend'),
                    array('status' => 400)
                );
            }

            // Update order with transaction ID if provided
            if (!empty($transaction_id)) {
                $order->set_transaction_id($transaction_id);
            }

            // Mark order as paid
            $order->payment_complete();

            // Enroll user in purchased courses
            $this->enroll_user_in_courses($order);

            return rest_ensure_response(array(
                'success' => true,
                'order_id' => $order_id,
                'order_number' => $order->get_order_number(),
                'status' => $order->get_status(),
                'message' => __('Payment confirmed and order completed successfully.', 'lifterlms-extend'),
            ));

        } catch (Exception $e) {
            return new WP_Error(
                'payment_confirmation_error',
                $e->getMessage(),
                array('status' => 400)
            );
        }
    }

    /**
     * Verify payment data (customize based on your payment processor)
     */
    private function verify_payment($payment_data, $order) {
        // This is a placeholder - implement actual payment verification logic
        // For Apple Pay/Google Pay, you would verify the payment token/response

        // Example verification (customize based on your needs):
        if (!isset($payment_data['status']) || $payment_data['status'] !== 'success') {
            return false;
        }

        // Verify amount matches
        if (isset($payment_data['amount']) && abs($payment_data['amount'] - $order->get_total()) > 0.01) {
            return false;
        }

        // Add more verification logic here based on your payment processor
        // - Verify signature
        // - Check transaction details
        // - Validate against payment gateway API

        return true; // Return true if payment is verified
    }

    /**
     * Get order status
     */
    public function get_order_status($request) {
        try {
            $order_id = $request->get_param('order_id');

            // Get the order
            $order = wc_get_order($order_id);
            if (!$order) {
                return new WP_Error(
                    'order_not_found',
                    __('Order not found.', 'lifterlms-extend'),
                    array('status' => 404)
                );
            }

            // Check if order belongs to current user
            if ($order->get_customer_id() !== get_current_user_id()) {
                return new WP_Error(
                    'unauthorized',
                    __('You are not authorized to access this order.', 'lifterlms-extend'),
                    array('status' => 403)
                );
            }

            // Get enrollment status for courses
            $enrollments = array();
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                if ($product && $product->is_type('course')) {
                    $course_id = $product->get_id();
                    $enrolled = llms_is_user_enrolled(get_current_user_id(), $course_id);
                    $enrollments[] = array(
                        'course_id' => $course_id,
                        'course_name' => $product->get_name(),
                        'enrolled' => $enrolled,
                    );
                }
            }

            return rest_ensure_response(array(
                'order_id' => $order_id,
                'order_number' => $order->get_order_number(),
                'status' => $order->get_status(),
                'date_created' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : null,
                'total' => $order->get_total(),
                'transaction_id' => $order->get_transaction_id(),
                'enrollments' => $enrollments,
            ));

        } catch (Exception $e) {
            return new WP_Error(
                'order_status_error',
                $e->getMessage(),
                array('status' => 400)
            );
        }
    }

    /**
     * Get checkout data
     */
    public function get_checkout_data($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        $cart = WC()->cart;

        // Get available payment methods
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $payment_methods = array();

        foreach ($available_gateways as $gateway) {
            $payment_methods[] = array(
                'id' => $gateway->id,
                'title' => $gateway->get_title(),
                'description' => $gateway->get_description(),
                'icon' => $gateway->get_icon(),
            );
        }

        // Get shipping methods if applicable
        $shipping_methods = array();
        if (WC()->cart->needs_shipping()) {
            $packages = WC()->shipping->get_packages();
            foreach ($packages as $package) {
                foreach ($package['rates'] as $rate) {
                    $shipping_methods[] = array(
                        'id' => $rate->id,
                        'label' => $rate->label,
                        'cost' => $rate->cost,
                        'method_id' => $rate->method_id,
                    );
                }
            }
        }

        return rest_ensure_response(array(
            'cart' => $this->get_cart($request)->get_data(),
            'payment_methods' => $payment_methods,
            'shipping_methods' => $shipping_methods,
            'countries' => WC()->countries->get_countries(),
            'states' => WC()->countries->get_states(),
            'customer' => array(
                'billing' => WC()->customer->get_billing(),
                'shipping' => WC()->customer->get_shipping(),
            ),
        ));
    }

    /**
     * Process checkout - Create order with pending status for mobile payments
     */
    public function process_checkout($request) {
        try {
            $this->ensure_woocommerce_initialized();
        } catch (Exception $e) {
            return new WP_Error(
                'woocommerce_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }

        try {
            // Set customer data
            WC()->customer->set_billing_first_name($request->get_param('billing_first_name'));
            WC()->customer->set_billing_last_name($request->get_param('billing_last_name'));
            WC()->customer->set_billing_email($request->get_param('billing_email'));
            WC()->customer->set_billing_phone($request->get_param('billing_phone'));
            WC()->customer->set_billing_address_1($request->get_param('billing_address_1'));
            WC()->customer->set_billing_address_2($request->get_param('billing_address_2'));
            WC()->customer->set_billing_city($request->get_param('billing_city'));
            WC()->customer->set_billing_state($request->get_param('billing_state'));
            WC()->customer->set_billing_postcode($request->get_param('billing_postcode'));
            WC()->customer->set_billing_country($request->get_param('billing_country'));

            // Set shipping address same as billing if not provided
            WC()->customer->set_shipping_first_name($request->get_param('billing_first_name'));
            WC()->customer->set_shipping_last_name($request->get_param('billing_last_name'));
            WC()->customer->set_shipping_address_1($request->get_param('billing_address_1'));
            WC()->customer->set_shipping_address_2($request->get_param('billing_address_2'));
            WC()->customer->set_shipping_city($request->get_param('billing_city'));
            WC()->customer->set_shipping_state($request->get_param('billing_state'));
            WC()->customer->set_shipping_postcode($request->get_param('billing_postcode'));
            WC()->customer->set_shipping_country($request->get_param('billing_country'));

            // Set payment method
            WC()->session->set('chosen_payment_method', $request->get_param('payment_method'));

            // Apply coupon if provided
            $coupon_code = $request->get_param('coupon_code');
            if (!empty($coupon_code)) {
                WC()->cart->apply_coupon($coupon_code);
            }

            // Validate checkout
            $checkout = WC()->checkout;
            $errors = new WP_Error();

            // Validate terms
            if (!$request->get_param('terms')) {
                $errors->add('terms', __('Please accept the terms and conditions.', 'lifterlms-extend'));
            }

            // Validate payment method
            $payment_method = $request->get_param('payment_method');
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            if (!isset($available_gateways[$payment_method])) {
                $errors->add('payment_method', __('Invalid payment method.', 'lifterlms-extend'));
            }

            if ($errors->has_errors()) {
                return $errors;
            }

            // Create order with pending status (no payment processing)
            $order_id = $checkout->create_order(array(
                'payment_method' => $payment_method,
                'billing_email' => $request->get_param('billing_email'),
                'billing_first_name' => $request->get_param('billing_first_name'),
                'billing_last_name' => $request->get_param('billing_last_name'),
                'billing_company' => '',
                'billing_address_1' => $request->get_param('billing_address_1'),
                'billing_address_2' => $request->get_param('billing_address_2'),
                'billing_city' => $request->get_param('billing_city'),
                'billing_state' => $request->get_param('billing_state'),
                'billing_postcode' => $request->get_param('billing_postcode'),
                'billing_country' => $request->get_param('billing_country'),
                'billing_phone' => $request->get_param('billing_phone'),
            ));

            if (is_wp_error($order_id)) {
                return $order_id;
            }

            $order = wc_get_order($order_id);

            // Ensure order status is pending
            if ($order->get_status() !== 'pending') {
                $order->set_status('pending');
                $order->save();
            }

            return rest_ensure_response(array(
                'success' => true,
                'order_id' => $order_id,
                'order_number' => $order->get_order_number(),
                'order_key' => $order->get_order_key(),
                'status' => 'pending_payment',
                'message' => __('Order created successfully. Awaiting payment confirmation.', 'lifterlms-extend'),
            ));

        } catch (Exception $e) {
            return new WP_Error(
                'checkout_error',
                $e->getMessage(),
                array('status' => 400)
            );
        }
    }

    /**
     * Get upsell suggestions from checkout-upsell-and-order-bumps plugin
     */
    private function get_upsell_suggestions() {
        $suggestions = array();

        // Check if UpsellWP plugin is active and has the required methods
        if (!class_exists('\CUW\App\Modules\Campaigns\CartUpsells') ||
            !method_exists('\CUW\App\Modules\Campaigns\CartUpsells', 'getOffersToDisplay')) {
            return $suggestions;
        }

        try {
            // Get the defined display locations for cart and checkout upsells
            $cart_upsells_locations     = \CUW\App\Modules\Campaigns\CartUpsells::getDisplayLocations();
            $checkout_upsells_locations = \CUW\App\Modules\Campaigns\CheckoutUpsells::getDisplayLocations();

            // Initialize arrays to hold merged offers
            $cart_offers_merged     = [];
            $checkout_offers_merged = [];

            // Fetch and merge offers for each cart location
            foreach ($cart_upsells_locations as $location => $name) {
                $offers = \CUW\App\Modules\Campaigns\CartUpsells::getOffersToDisplay($location, false);
                if (is_array($offers) && !empty($offers)) {
                    $cart_offers_merged = array_merge($cart_offers_merged, $offers);
                }
            }

            // Fetch and merge offers for each checkout location
            foreach ($checkout_upsells_locations as $location => $name) {
                $offers = \CUW\App\Modules\Campaigns\CheckoutUpsells::getOffersToDisplay($location, false);
                if (is_array($offers) && !empty($offers)) {
                    $checkout_offers_merged = array_merge($checkout_offers_merged, $offers);
                }
            }

            // error_log(print_r($cart_offers_merged, true));
            // error_log(print_r($checkout_offers_merged, true));
            $offers_data = $cart_offers_merged;

            if (!empty($offers_data) && is_array($offers_data)) {
                foreach ($offers_data as $campaign_id => $offer_ids) {
                    if (is_array($offer_ids)) {
                        foreach ($offer_ids as $offer_id) {
                            // Get offer details from the database
                            $offer_data = \CUW\App\Models\Offer::get($offer_id);
                            if ($offer_data && isset($offer_data['product'])) {
                                $product_data = $offer_data['product'];
                                if ($product_data && isset($product_data['id'])) {
                                    $product = wc_get_product($product_data['id']);
                                    if ($product && $product->is_in_stock()) {
                                        $discount_data = $offer_data['discount'];
                                        $offer_price = \CUW\App\Helpers\Offer::getPrice($product, $discount_data);

                                        $suggestions[] = array(
                                            'id' => $product->get_id(),
                                            'name' => $product->get_name(),
                                            'price' => $product->get_price(),
                                            'location' => $offer_data,
                                            'offer_price' => $offer_price,
                                            'discount' => $discount_data,
                                            'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                                            'permalink' => $product->get_permalink(),
                                            'type' => 'upsell',
                                            'campaign_id' => $campaign_id,
                                            'offer_id' => $offer_id,
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but continue with fallback
            error_log('UpsellWP API error: ' . $e->getMessage());
        }

        return $suggestions;
    }
}