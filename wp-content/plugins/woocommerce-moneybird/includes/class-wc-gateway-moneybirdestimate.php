<?php

use ExtensionTree\WCMoneyBird;

if (!defined('ABSPATH')) { exit; }

/**
 * Moneybird Estimate Gateway.
 *
 * Provides a Gateway to send an (automatically generated) Moneybird estimate.
 *
 * @class       WC_Gateway_MoneybirdEstimate
 * @extends     WC_Payment_Gateway
 * @package WooCommerce
 * @subpackage Moneybird
 */

class WC_Gateway_MoneybirdEstimate extends WC_Payment_Gateway {

    protected $instructions;
    protected $mark_paid;
    protected $auto_generate;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        // Setup general properties.
        $this->setup_properties();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Get settings.
        $this->title              = $this->get_option(
            'title',
            __('Estimate', 'woocommerce_moneybird')
        );
        $this->availability       = $this->get_option('availability', 'all');
        $this->description        = $this->get_option('description');
        $this->instructions       = $this->get_option('instructions');
        $this->mark_paid          = $this->get_option('mark_paid', 'yes') === 'yes';
        $this->auto_generate      = $this->get_option('auto_generate', 'no');

        // Actions.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);

        // Customer Emails.
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
    }

    /**
     * Setup general properties for the gateway.
     */
    protected function setup_properties() {
        $this->id                 = 'moneybirdestimate';
        $this->icon               = apply_filters('woocommerce_moneybirdestimate_icon', '');
        $this->method_title       = 'Moneybird ' . __('estimate', 'woocommerce_moneybird');
        $this->method_description = __('Automatically generate and send a Moneybird estimate upon checkout.', 'woocommerce_moneybird');
        $this->has_fields         = false;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'           => array(
                'title'       => __('Enable/Disable', 'woocommerce'),
                'label'       => __('Enable Moneybird estimate checkout', 'woocommerce_moneybird'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'availability'      => array(
                'title'       => __('Availability', 'woocommerce'),
                'description' => __('Select the types of users for which this gateway is available. If the Login as User plugin by Yiannis Christodoulou is used, the gateway will be available if either the current or the old user qualifies.', 'woocommerce_moneybird'),
                'type'        => 'select',
                'options'     => array(
                    'all'          => __('All users', 'woocommerce_moneybird'),
                    'logged_in'    => __('Only logged in users', 'woocommerce_moneybird'),
                    'guest'        => __('Only guests without account', 'woocommerce_moneybird'),
                    'admin'        => __('Only admin users (manage WooCommerce permission)', 'woocommerce_moneybird'),
                ),
            ),
            'title'             => array(
                'title'       => __('Title', 'woocommerce'),
                'type'        => 'safe_text',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce'),
                'default'     => __('Cash on delivery', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'description'       => array(
                'title'       => __('Description', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('Payment method description that the customer will see on your website.', 'woocommerce'),
                'default'     => __('Receive an estimate by email.', 'woocommerce_moneybird'),
                'desc_tip'    => true,
            ),
            'instructions'      => array(
                'title'       => __('Instructions', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('Instructions that will be added to the thank you page.', 'woocommerce'),
                'default'     => __('We will send an estimate by email.', 'woocommerce_moneybird'),
                'desc_tip'    => true,
            ),
            'mark_paid'           => array(
                'title'       => __('Mark as paid', 'woocommerce_moneybird'),
                'label'       => __('Immediately mark order as paid.', 'woocommerce_moneybird'),
                'description' => __('If disabled, the order status will be set to Pending payment.', 'woocommerce_moneybird'),
                'type'        => 'checkbox',
                'default'     => 'yes',
            ),
            array(
                'title'       => __('Automatic estimate generation', 'woocommerce_moneybird'),
                'type'        => 'title',
                'description' => sprintf(
                    __('Estimates can be generated automatically or manually. Estimate settings can be configured on the <a href="%s">Moneybird plugin settings page</a>.', 'woocommerce_moneybird'),
                    admin_url('admin.php?page=wc-settings&tab=integration&section=moneybird2')
                )
            ),
            'auto_generate'     => array(
                'title'       => __('Generate automatically', 'woocommerce_moneybird'),
                'description' => __('Select when the estimate should be generated automatically.', 'woocommerce_moneybird'),
                'type'        => 'select',
                'options'     => array(
                    'no'        => __('Do not generate automatically', 'woocommerce_moneybird'),
                    'now'       => __('Immediately after checkout', 'woocommerce_moneybird'),
                    '+1 hour'   => __('1 hour after checkout', 'woocommerce_moneybird'),
                    '+2 hours'  => sprintf(__('%d hours after checkout', 'woocommerce_moneybird'), 2),
                    '+4 hours'  => sprintf(__('%d hours after checkout', 'woocommerce_moneybird'), 4),
                    '+8 hours'  => sprintf(__('%d hours after checkout', 'woocommerce_moneybird'), 8),
                    '+1 day'    => __('1 day after checkout', 'woocommerce_moneybird'),
                    '+2 days'   => sprintf(__('%d days after checkout', 'woocommerce_moneybird'), 2),
                    '+4 days'   => sprintf(__('%d days after checkout', 'woocommerce_moneybird'), 4),
                ),
                'default'     => 'now',
            )
       );
    }

    function process_admin_options() {
        $result = parent::process_admin_options();
        if ($this->settings['enabled'] === 'yes') {
            // Ensure Moneybird estimates functionality is enabled
            WCMB()->ensure_estimates_enabled();
        }
        return $result;
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        if ($this->mark_paid) {
            $order->payment_complete();
        } else {
            // Maybe update status
            $status = apply_filters('woocommerce_moneybirdestimate_process_payment_order_status', 'pending', $order);
            if ($status !== $order->get_status()) {
                $order->update_status($status);
            }
        }

        // Remove cart.
        WC()->cart->empty_cart();

        // Schedule estimate generation
        if ($this->auto_generate != 'no') {
            if (strpos($this->auto_generate, '+') === 0) {
                $scheduled_time = strtotime($this->auto_generate, time());
            } else {
                $scheduled_time = time() - 1;
            }
            $wcmb = WCMB();
            if ($wcmb) {
                $wcmb->add_to_queue('generate_estimate', array($order_id), 5, $scheduled_time);
            }
        }

        // Return thankyou redirect.
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
       );
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ($this->instructions) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)));
        }
    }

    /**
     * Check if this gateway is available.
     *
     * @return bool
     */
    public function is_available() {
        if (!parent::is_available()) {
            return false;
        }
        if ($this->availability == 'admin') {
            if (current_user_can('manage_woocommerce')) {
                return true;
            }
            if (function_exists('initialize_w357_login_as_user')) {
                $w357_login_as_user = initialize_w357_login_as_user();
                if (!empty($w357_login_as_user) && method_exists($w357_login_as_user, 'get_old_user')) {
                    // Check if the old user has manage_woocommerce permission
                    $old_user = $w357_login_as_user->get_old_user();
                    if ($old_user) {
                        if (user_can($old_user, 'manage_woocommerce')) {
                            return true;
                        }
                    }
                }
            }
        } elseif ($this->availability == 'guest') {
            if (!is_user_logged_in()) {
                return true;
            }
        } elseif ($this->availability == 'logged_in') {
            if (is_user_logged_in()) {
                return true;
            }
        } elseif ($this->availability == 'all') {
            return true;
        }

        return false;
    }

    /**
     * Change payment complete order status to completed for MoneybirdEstimate orders.
     *
     * @param  string         $status Current order status.
     * @param  int            $order_id Order ID.
     * @param  WC_Order|false $order Order object.
     * @return string
     */
    public function change_payment_complete_order_status($status, $order_id = 0, $order = false) {
        if ($order && 'moneybirdestimate' === $order->get_payment_method()) {
            $status = 'completed';
        }
        return $status;
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order Order object.
     * @param bool     $sent_to_admin  Sent to admin.
     * @param bool     $plain_text Email format: plain text or HTML.
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false) {
        if ($this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method()) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
        }
    }
}
