<?php

namespace FluentSupportPro\App\Services\Integrations;

use FluentSupport\App\Services\Helper;
use FluentSupportPro\App\Services\ProHelper;

class WooCommerce
{
    public function boot()
    {
        //add_filter('fluent_support/customer_extra_widgets', array($this, 'getWooPurchaseWidgets'), 120, 2);
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getWooCommercePurchaseWidgets'), 120, 2);

        /*
         * Custom Fields Support For products
         */
        add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
            $fieldTypes['woo_products'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('WooCommerce products will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'woo_products',
                'label'       => __('WooCommerce Products', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];
            $fieldTypes['woo_orders'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('WooCommerce orders will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'woo_orders',
                'label'       => __('WooCommerce Orders', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];
            return $fieldTypes;
        });

        add_filter('fluent_support/render_custom_field_options_woo_orders', function ($field, $customer) {

            $orders = wc_get_orders([
                'billing_email' => $customer->email,
                'limit'         => 1000,
                'offset'        => 0,
                'order'         => 'DESC',
                'paginate'      => false
            ]);

            if (!$orders) {
                return $field;
            }

            $options = [];

            foreach ($orders as $order) {
                $options[] = [
                    'id'    => strval($order->get_id()),
                    'title' => sprintf(__('Order #%d - %s', 'fluent-support-pro'), $order->get_id(), wc_format_datetime($order->get_date_created()))
                ];
            }

            $field['type'] = 'select';
            $field['filterable'] = true;
            $field['rendered'] = true;
            $field['options'] = $options;
            return $field;

        }, 10, 2);

        add_filter('fluent_support/render_custom_field_options_woo_products', function ($field, $customer) {

            $products = $this->getWooProducts();

            if (!$products) {
                return false;
            }

            $options = [];

            foreach ($products as $product) {
                $options[] = [
                    'id'    => strval($product->get_id()),
                    'title' => $product->get_title()
                ];
            }

            $field['type'] = 'select';
            $field['rendered'] = true;
            $field['filterable'] = true;
            $field['options'] = $options;
            return $field;

        }, 10, 2);

        add_filter('fluent_support/custom_field_render_woo_orders', function ($value, $scope) {
            if (!is_numeric($value)) {
                return $value;
            }

            $orderId = absint($value);

            if ($scope == 'admin') {
                return '<a target="_blank" rel="nofollow" href="' . get_edit_post_link($orderId) . '">' . sprintf(__('Order #%d', 'fluent-support-pro'), $orderId) . '</a>';
            }

            $order = wc_get_order($orderId);

            if (!$order) {
                return 'Order #' . $orderId;
            }

            $url = $order->get_view_order_url();

            return '<a target="_blank" rel="nofollow" href="' . $url . '">' . sprintf(__('Order #%d', 'fluent-support-pro'), $orderId) . '</a>';

        }, 10, 2);

        add_filter('fluent_support/custom_field_render_woo_products', function ($value) {
            if (!is_numeric($value)) {
                return $value;
            }

            $productId = absint($value);
            $product = get_post($productId);
            if (!$product) {
                return $value;
            }

            return '<a target="_blank" rel="nofollow" href="' . get_permalink($product) . '">' . $product->post_title . '</a>';

        }, 10, 1);


        if (!apply_filters('fluent_support/disable_woo_menu', false)) {
            add_filter('woocommerce_account_menu_items', function ($menuLinks) {

                $ticketConfig = ProHelper::getTicketFormConfig();
                if ($ticketConfig['enable_woo_menu'] != 'yes') {
                    return $menuLinks;
                }

                /**
                 * Filter the support page link position in WooCommerce customer menu
                 * @param int $supportTicketPosition
                 * @return int $supportTicketPosition
                 */
                $supportTicketPosition = apply_filters('fluent_support/woo_menu_link_position', 3);

                if (count($menuLinks) <= $supportTicketPosition) {
                    $supportTicketPosition = count($menuLinks) - 1;
                }
                $supportLabel = __('Support', 'fluent-support-pro');

                /**
                 * Filter the support page link label in WooCommerce customer menu
                 * @param string $supportLabel
                 * @return string $supportLabel
                 */
                $supportLabel = apply_filters('fluent_support/woo_menu_label', $supportLabel);

                return array_slice($menuLinks, 0, $supportTicketPosition, true)
                    + array('support-tickets' => $supportLabel)
                    + array_slice($menuLinks, $supportTicketPosition, NULL, true);

            });

            add_action('init', function () {
                add_rewrite_endpoint('support-tickets', EP_PAGES);
            });

            add_action('woocommerce_account_support-tickets_endpoint', function () {
                $ticketConfig = ProHelper::getTicketFormConfig();
                if ($ticketConfig['enable_woo_menu'] == 'yes') {
                    echo do_shortcode('[fluent_support_portal]');
                }
            });
        }

    }

    public function getWooPurchaseWidgets($widgets, $customer)
    {
        $app = Helper::FluentSupport();
        $page = intval($app->request->get('page', 1));
        $per_page = intval($app->request->get('per_page', 10));

        $customer_orders = wc_get_orders([
            'billing_email' => $customer->email,
            'limit'         => $per_page,
            'offset'        => $per_page * ($page - 1),
            'order'         => 'DESC',
            'paginate'      => true
        ]);

        $formattedOrders = [];
        $lastOrder = false;

        $customerQuery = \FluentSupport\App\App::db()->table('wc_customer_lookup')
            ->where('email', $customer->email);

        if ($customer->user_id) {
            $customerQuery = $customerQuery->orWhere('user_id', $customer->user_id);
        }

        $customer = $customerQuery->first();

        if (!$customer) {
            return $widgets;
        }

        $orderStatsQuery = \FluentSupport\App\App::db()->table('wc_order_stats')
            ->where('customer_id', $customer->customer_id);

        // Check if the customer has any orders as guest customer
        // We didn't check `woocommerce_enable_guest_checkout` as if admin disbale it
        // after some guest orders, the guest orders will still be shown
        $guestCustomers = \FluentSupport\App\App::db()->table('wc_customer_lookup')
            ->whereNull('user_id')
            ->where('email', $customer->email)
            ->select('customer_id')
            ->first();

        if ($guestCustomers){
            $orderStatsQuery->orWhereIn('customer_id', [$customer->customer_id, $guestCustomers->customer_id]);
        }

        $orderStats = $orderStatsQuery->get();
        $orderIds = [];

        foreach ($orderStats as $order) {
            $orderIds[] = $order->order_id;
        }

        if (is_array($orderIds)) {
            $orderIds = array_unique($orderIds);
        }

        if (empty($orderIds)) {
            return $widgets;
        }

        $orderedProducts = \FluentSupport\App\App::db()->table('wc_order_product_lookup')
            ->select([
                'posts.ID', 'posts.post_title', 'posts.guid', 'product_gross_revenue', 'order_id'
            ])
            ->join('posts', 'wc_order_product_lookup.product_id', '=', 'posts.ID')
            ->whereIn('order_id', $orderIds)
            ->groupBy('posts.ID')
            ->get();

        if (!$customer || !$orderStats || !$orderedProducts) {
            return $widgets;
        }

        foreach ($customer_orders->orders as $order) {
            $item_count = $order->get_item_count() - $order->get_item_count_refunded();

            foreach ($orderedProducts as $product) {
                if($product->order_id ==  $order->get_id()) {
                    $formattedOrders[] = $this->makeFormattedOrder($order, $item_count, $product->post_title, $product->guid, $product->product_gross_revenue);
                }
            }

            if (!$lastOrder) {
                $lastOrder = $order;
            }
        }

        ob_start();
        ?>
        <ul>
            <?php foreach ($formattedOrders as $orderKey => $orderValue): ?>
                <li title="<?php echo __('Purchase Date: ', 'fleunt-support') . $orderValue['date'] ?>">
                    <?php
                    echo $orderValue['product_name'] . ' <code>' . $orderValue['status'] . '</code>';
                    ?>
                    - <?php echo get_woocommerce_currency_symbol().$orderValue['total']; ?>
                    <a target="_blank" rel="nofollow"
                       href="<?php echo admin_url('post.php?post=' . $orderValue['order_id'] . '&action=edit'); ?>"> <i class="dashicons dashicons-visibility"></i> </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();

        $widgets['woo_purchases'] = [
            'header'    => __('WooCommerce Purchases', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    public function getWooCommercePurchaseWidgets($widgets, $customer)
    {
        $app = Helper::FluentSupport();
        $page = intval($app->request->get('page', 1));
        $per_page = intval($app->request->get('per_page', 10));

        $customer_orders = wc_get_orders([
            'billing_email' => $customer->email,
            'limit'         => $per_page,
            'offset'        => $per_page * ($page - 1),
            'order'         => 'DESC',
            'paginate'      => true
        ]);

        $orders = [];

        $customerQuery = \FluentSupport\App\App::db()->table('wc_customer_lookup')
            ->where('email', $customer->email);

        if ($customer->user_id) {
            $customerQuery = $customerQuery->orWhere('user_id', $customer->user_id);
        }

        $customer = $customerQuery->first();

        if (!$customer) {
            return $widgets;
        }

        $orderStatsQuery = \FluentSupport\App\App::db()->table('wc_order_stats')
            ->where('customer_id', $customer->customer_id);

        // Check if the customer has any orders as guest customer
        // We didn't check `woocommerce_enable_guest_checkout` as if admin disbale it
        // after some guest orders, the guest orders will still be shown
        $guestCustomers = \FluentSupport\App\App::db()->table('wc_customer_lookup')
            ->whereNull('user_id')
            ->where('email', $customer->email)
            ->select('customer_id')
            ->first();

        if ($guestCustomers){
            $orderStatsQuery->orWhereIn('customer_id', [$customer->customer_id, $guestCustomers->customer_id]);
        }

        $orderStats = $orderStatsQuery->get();

        foreach ($customer_orders->orders as $key => $order) {
            $orders[$key] = $this->getOrderInfo($order);
            $orderIds[] = $order->get_id();
        }

        if(!isset($orderIds)) {
            return $widgets;
        }

        if (is_array($orderIds)) {
            $orderIds = array_unique($orderIds);
        }

        if (empty($orderIds)) {
            return $widgets;
        }

        $orderedProducts = \FluentSupport\App\App::db()->table('wc_order_product_lookup')
            ->select([
                'posts.ID', 'posts.post_title', 'posts.guid', 'product_gross_revenue', 'order_id', 'product_qty'
            ])
            ->join('posts', 'wc_order_product_lookup.product_id', '=', 'posts.ID')
            ->whereIn('order_id', $orderIds)
            //->groupBy('posts.ID')
            ->get();

        $products = [];
        foreach ($orderedProducts as $product) {
            $products[$product->order_id][] = $product;
        }

        if (!$customer || !$orderStats || !$orderedProducts) {
            return $widgets;
        }

        $widgets['woo_purchases'] = [
            'title' => __('WooCommerce Purchases', 'fluent-support-pro'),
            'orders'  => $orders,
            'products' => $products,
        ];

        return $widgets;
    }

    private function makeFormattedOrder($order, $item_count, $productTitle, $productUrl, $productGrossRevenue)
    {
        return [
            'order'        => '#' . $order->get_order_number(),
            'order_id'     => $order->get_id(),
            'date'         => esc_html(wc_format_datetime($order->get_date_created())),
            'status'       => esc_html($order->get_status()),
            'total'        => esc_html($productGrossRevenue),
            'item_count'   => esc_html($item_count),
            'product_name' => esc_html($productTitle),
            'product_url'  => esc_html($productUrl)
        ];
    }

    /**
     * @param $order
     * @return array
     */
    private function getOrderInfo($order){
        return [
            'id' => $order->get_id(),
            'status' => esc_html($order->get_status()),
            'total' => esc_html($order->get_total()),
            'currency' => esc_html(get_woocommerce_currency_symbol()),
            'date' => esc_html(wc_format_datetime($order->get_date_created())),
            'billing_address' => $order->get_formatted_billing_address(),
            'shipping_address' => $order->get_formatted_shipping_address(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'payment_method' => $order->get_payment_method_title(),
            'shipping_method' => $order->get_shipping_method(),
            'order_link' => admin_url('post.php?post=' . $order->get_id() . '&action=edit'),
        ];
    }

    private function getWooProducts()
    {
        if(!function_exists('wc_get_products')){
            return [];
        }

        return wc_get_products([
            'limit'   => 1000,
            'orderby' => 'name',
            'order'   => 'ASC',
            'return'  => 'objects',
            'status'  => 'publish'
        ]);
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];
        if($key=='woo_products'){
            $wooProducts = $this->getWooProducts();

            foreach ($wooProducts as $product) {
                $options[$product->get_id()] = $product->get_name();
            }
        } else{
            if(!function_exists('wc_get_orders')){
                return [];
            }

            $wooOrders = wc_get_orders(['order' => 'DESC']);

            foreach ($wooOrders as $order) {
                $options[$order->get_id()] = sprintf(__('Order #%d - %s', 'fluent-support-pro'), $order->get_id(), wc_format_datetime($order->get_date_created()));
            }
        }

        return [
            'title'     => $customField['label'],
            'data_type' => 'single_dropdown',
            'group'     => 'Custom Fields',
            'options'   => $options
        ];
    }
}
