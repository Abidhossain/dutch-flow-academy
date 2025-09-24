<?php

namespace FluentSupportPro\App\Services\Integrations;

use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;

class Edd
{
    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getPurchaseWidgets'), 60, 2);

        // Custom Fields Support For EDD Products
        $this->renderCustomFields();
    }

    public function getEddPurchase($widgets, $customer)
    {
        $by = 'email';
        $value = $customer->email;
        if ($customer->user_id) {
            $by = 'ID';
            $value = $customer->user_id;
        }

        $user = get_user_by($by, $value);

        if (!$user) {
            return false;
        }

        $purchases = edd_get_payments([
            'user'   => $user->ID,
            'output' => 'payments'
        ]);

        if(!$purchases) {
            return false;
        }

        return $purchases;
    }

    public function renderCustomFields()
    {
        add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
            $fieldTypes['edd_products'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('EDD products will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'edd_products',
                'label'       => __('EDD Products', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];
            $fieldTypes['edd_orders'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('EDD orders will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'edd_orders',
                'label'       => __('EDD Orders', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];

            return $fieldTypes;
        }, 10, 1);

        add_filter('fluent_support/render_custom_field_options_edd_orders', function ($field, $customer) {

            $orders = $this->getEddPurchase($field, $customer);

            if (!$orders) {
                return $field;
            }

            $options = [];

            foreach ($orders as $order) {

                $options[] = [
                    'id'    => strval($order->ID),
                    'title' => sprintf(__('Order #%d - %s', 'fluent-support-pro'), $order->ID, date_i18n(get_option('date_format'), strtotime($order->completed_date)))
                ];

            }
            if (!$options) return $field;

            $field['type'] = 'select';
            $field['filterable'] = true;
            $field['rendered'] = true;
            $field['options'] = $options;

            return $field;

        }, 10, 2);

        add_filter('fluent_support/custom_field_render_edd_orders', function ($value, $scope) {
            if (!is_numeric($value)) {
                return $value;
            }

            $orderId = absint($value);

            if ($scope == 'admin') {

                return '<a href="' . admin_url('edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $orderId) . '">' . sprintf(__('Order #%d', 'fluent-support-pro'), $orderId) . '</a>';
            }

            global $edd_options;

            if (class_exists('\EDD_Payment')) {
                $edd = new \EDD_Payment($orderId);

                $url = get_permalink($edd_options['success_page']) . '?payment_key=' . $edd->key;

                return '<a target="_blank" rel="nofollow" href="' . $url . '">' . sprintf(__('Order #%d', 'fluent-support-pro'), $orderId) . '</a>';
            }

        }, 10, 2);

        add_filter('fluent_support/render_custom_field_options_edd_products', function ($field, $customer) {

            $products = $this->getEddProducts();

            if (!$products) {
                return false;
            }

            $options = [];

            foreach ($products as $product) {
                $options[] = [
                    'id'    => strval($product->ID),
                    'title' => (new \EDD_Download($product->ID))->post_title
                ];
            }
            if (!$options) return $field;

            $field['type'] = 'select';
            $field['rendered'] = true;
            $field['filterable'] = true;
            $field['options'] = $options;
            return $field;

        }, 10, 2);

        add_filter('fluent_support/custom_field_render_edd_products', function ($value) {
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
    }

    public function getPurchaseWidgets($widgets, $customer)
    {

        $payments = $this->getEddPurchase($widgets, $customer);

        if (!$payments) return $widgets;

        $licenses = [];

        ob_start();
        ?>
        <ul>
            <?php foreach ($payments as $payment): ?>
            <?php
                $licenses = $this->getLicenses($payment);
                $wrapperClass = (count($payment->cart_details) > 1) ? 'fs_multi_product' : '';
            ?>
                <li class="<?php echo $wrapperClass;?>" title="<?php echo __('Purchase Date: ', 'fluent-support-pro') . $payment->completed_date; ?>">
                    <?php foreach ($payment->cart_details as $cart_detail): ?>
                        <div class="fs_product_line"><?php echo $cart_detail['name'] . ' <span class="fs_purchase_status">' . $payment->status_nicename . '</span>'; ?>
                            <?php if($licenses && $status = Arr::get($licenses, $cart_detail['id'].'.status')) : ?>
                                <span class="fs_purchase_status <?php echo 'fs_license_status_'.esc_attr(strtolower($status)); ?>" title="License Status"><span class="dashicons dashicons-lock"></span> <?php echo $status; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    - <?php echo edd_currency_symbol($payment->currency) . edd_format_amount($payment->total); ?>
                    <a target="_blank" rel="nofollow"
                       href="<?php echo admin_url('edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->ID); ?>"><i
                            class="dashicons dashicons-visibility"></i></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();
        $widgets['edd_purchases'] = [
            'header'    => __('EDD Purchases', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    private function getLicenses($payment)
    {
        if (!defined('EDD_SL_VERSION') || !$payment->user_id) {
            return [];
        }

        $licenses = Helper::FluentSupport('db')
            ->table('edd_licenses')
            ->where('user_id', $payment->user_id)
            ->where('payment_id', $payment->ID)
            ->get();

        if(!$licenses) {
            return [];
        }
        $formattedLicenses = [];
        foreach ($licenses as $license) {
            $formattedLicenses[$license->download_id] = (array) $license;
        }
        return $formattedLicenses;
    }

    private function getEddProducts()
    {
        return get_posts(array(
                'post_type'   => 'download',
                'post_status' => 'publish',
            )
        );
    }
    public function addToWorkflow($customField, $key)
    {
        $options = [];

        if('edd_products' == $key){
            $products = $this->getEddProducts();

            foreach ($products as $product) {
                $options[strval($product->ID)] = (new \EDD_Download($product->ID))->post_title;
            }

        } else {
            $orders = edd_get_payments();

            foreach ($orders as $order) {
                $options[strval($order->ID)] = sprintf(__('Order #%d - %s', 'fluent-support-pro'), $order->ID, date_i18n(get_option('date_format'), strtotime($order->completed_date)));
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
