<?php

use ExtensionTree\WCMoneyBird;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Moneybird2 class, extends WC_Integration
 *
 * @package WooCommerce
 * @subpackage Moneybird
 */

class WC_MoneyBird2 extends WC_Integration {
    public $mb_api = null;
    public static $log = null;
    public static $log_enabled = false;
    public $custom_field_placeholders = '{{amazon_order_id}}, {{bol_order_id}}, {{customer_order_note}}, {{first_product_name}}, {{order_id}}, {{order_ids}}, {{product_skus}}, {{shipping_method_title}}, {{subscription_next_payment_date}}, {{ANY_META_KEY}}';
    public $currency_rates = array();

    function __construct() {
        $this->id                   = 'moneybird2';
        $this->method_title         = __('Moneybird API', 'woocommerce_moneybird');
        $this->method_description   = __('Automatically create Moneybird invoices for WooCommerce orders.', 'woocommerce_moneybird') . '<br/>';
        $this->method_description  .= __('Documentation', 'woocommerce_moneybird') . ': <a target="_blank" href="https://docs.extensiontree.com/woocommerce-moneybird/">https://docs.extensiontree.com/woocommerce-moneybird/</a><br/>';

        // Settings
        $this->init_settings();
        self::$log_enabled = ('yes' === $this->get_option('debug', 'no'));

        // AJAX actions
        add_action('wc_mb_handle_invoice_queue', array(&$this, 'handle_invoice_queue'));
        add_action('wc_mb_maybe_handle_invoice_queue', array(&$this, 'handle_invoice_queue'));
        add_action('wp_ajax_wcmb_api', array(&$this, 'handle_api_request'));

        // Admin-specific actions
        if (is_admin() && current_user_can('edit_shop_orders')) {
            $this->init_form_fields();
            add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
            add_filter('woocommerce_settings_api_sanitized_fields_' . $this->id, array($this, 'filter_sanitized_fields'));
            add_action('admin_notices', array($this, 'admin_notices'));
            if (isset($this->settings['administration_id']) && $this->settings['administration_id']) {
                add_filter('woocommerce_order_actions', array($this, 'add_order_actions'), 10, 2);
                add_action('woocommerce_order_action_moneybird-invoice', array($this, 'generate_invoice'));
                add_action('woocommerce_order_action_moneybird-estimate', array($this, 'generate_estimate'));

                // Order hooks for legacy WC_Order storage
                add_filter('bulk_actions-edit-shop_order', array($this, 'add_bulk_actions'));
                add_filter('handle_bulk_actions-edit-shop_order', array(&$this, 'handle_bulk_actions'), 10, 3);
                add_filter('manage_shop_order_posts_custom_column', array($this, 'fill_moneybird_column'), 10, 1);
                add_filter('manage_edit-shop_order_columns', array($this, 'add_moneybird_column'), 10, 1);

                // Order hooks for HPOS
                add_filter('bulk_actions-woocommerce_page_wc-orders', array($this, 'add_bulk_actions'));
                add_filter('handle_bulk_actions-woocommerce_page_wc-orders', array($this, 'handle_bulk_actions'), 10, 3);
                add_filter('manage_woocommerce_page_wc-orders_custom_column', array($this, 'fill_moneybird_column'), 10, 2);
                add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'add_moneybird_column'), 10, 1);
            }
        }

        // Disable contacts syncing
        if (wp_next_scheduled('woocommerce_moneybird2_sync_contacts')) {
            wp_clear_scheduled_hook('woocommerce_moneybird2_sync_contacts');
        }
        if (wp_next_scheduled('woocommerce_moneybird2_fetch_contacts_queue')) {
            wp_clear_scheduled_hook('woocommerce_moneybird2_fetch_contacts_queue');
        }

        // Make sure the invoice queue is being processed
        if (!wp_next_scheduled('wc_mb_maybe_handle_invoice_queue')) {
            wp_schedule_event(time(), 'hourly', 'wc_mb_maybe_handle_invoice_queue');
        }
        $queue_handler_time = wp_next_scheduled('wc_mb_handle_invoice_queue');
        if ($queue_handler_time !== false) {
            $queue_handler_delay = $queue_handler_time - time();
            if (($queue_handler_delay > 80) || ($queue_handler_delay < -300)) {
                wp_clear_scheduled_hook('wc_mb_handle_invoice_queue');
                wp_schedule_single_event(time()+60, 'wc_mb_handle_invoice_queue');
            }
        }

        if (isset($this->settings['administration_id']) && $this->settings['administration_id']) {
            // Hooks for automatic invoice generation
            $triggers = array_keys($this->get_order_statuses());
            foreach($triggers as $trigger) {
                if (isset($this->settings['auto_invoice_trigger_'.$trigger])) {
                    if ($this->settings['auto_invoice_trigger_'.$trigger] == 'yes') {
                        add_action('woocommerce_order_status_' . $trigger, array($this, 'maybe_generate_invoice_without_notices'), 10, 2);
                    }
                }
            }
            add_action('woocommerce_new_order', array($this, 'maybe_generate_invoice_without_notices'), 10, 2);
            add_filter('wcs_new_order_created', array($this, 'maybe_generate_wcs_renewal_invoice'), 10, 1);

            // Hook for automatic credit invoice generation
            if (isset($this->settings['refund_automatic_invoice'])) {
                if ($this->settings['refund_automatic_invoice'] == 'yes') {
                    add_action('woocommerce_order_partially_refunded', array($this, 'generate_credit_invoice_without_notices'), 4, 2);
                    add_action('woocommerce_order_fully_refunded', array($this, 'generate_credit_invoice_without_notices'), 4, 2);
                }
            }

            // Hook for payment processing after an invoice has been created
            if (isset($this->settings['register_payment']) && ($this->settings['register_payment'] == 'yes')) {
                add_action('woocommerce_payment_complete', array($this, 'register_payment'));
            }

            // Hook for order failure processing
            if (isset($this->settings['delete_payments_failed_orders']) && ($this->settings['delete_payments_failed_orders'] == 'yes')) {
                add_action('woocommerce_order_status_failed', array($this, 'delete_invoice_payments'));
            }
        }
    }

    public static function log($message) {
        if (self::$log_enabled) {
            if (empty(self::$log)) {
                self::$log = new WC_Logger();
            }
            self::$log->add('woocommerce-moneybird', $message);
        }
    }

    public static function is_hpos_active() {
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
            return Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return false;
    }

    public static function get_queued_items($type='generate', $limit='', $only_ready_for_processing=true) {
        global $wpdb;

        if (WC_MoneyBird2::is_hpos_active()) {
            $wc_orders = $wpdb->prefix . 'wc_orders';
            $wc_orders_meta = $wpdb->prefix . 'wc_orders_meta';
            $sql = "SELECT $wc_orders.id, $wc_orders_meta.meta_value FROM $wc_orders_meta";
            $sql .= " LEFT JOIN $wc_orders ON ($wc_orders.id = $wc_orders_meta.order_id)";
            $sql .= " WHERE `meta_key` LIKE 'moneybird_queue_$type'";
            if ($only_ready_for_processing) {
                $sql .= " AND `meta_value` <= " . time();
            }
            $sql .= " ORDER BY $wc_orders.date_created_gmt ASC";
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit;
            }
            $results = $wpdb->get_results($sql);
            $queued_order_ids = array_map(function ($e) {
                return is_object($e) ? (int) $e->id : (int) $e['id'];
            }, $results);
        } else {
            $sql = "SELECT $wpdb->posts.ID, $wpdb->postmeta.meta_value FROM $wpdb->postmeta";
            $sql .= " LEFT JOIN $wpdb->posts ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)";
            $sql .= " WHERE post_type LIKE 'shop_order%' AND `meta_key` LIKE 'moneybird_queue_$type'";
            if ($only_ready_for_processing) {
                $sql .= " AND `meta_value` <= " . time();
            }
            $sql .= " ORDER BY $wpdb->posts.post_date ASC";
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit;
            }
            $results = $wpdb->get_results($sql);
            $queued_order_ids = array_map(function ($e) {
                return is_object($e) ? (int) $e->ID : (int) $e['ID'];
            }, $results);
        }

        return $queued_order_ids;
    }

    function document_id_to_order_ids($doc_id, $doc_type) {
        // Return list of order ids that have value $doc_id
        // in the moneybird_$doc_type_id meta field.
        global $wpdb;
        if (empty($doc_id)) {
            return array();
        }
        $meta_key = 'moneybird_' . $doc_type . '_id';
        if (WC_MoneyBird2::is_hpos_active()) {
            $wc_orders = $wpdb->prefix . 'wc_orders';
            $wc_orders_meta = $wpdb->prefix . 'wc_orders_meta';
            $sql = "SELECT order_id FROM $wc_orders_meta WHERE `meta_key` LIKE '$meta_key' AND `meta_value` LIKE '$doc_id' ORDER BY order_id ASC";
            $results = $wpdb->get_results($sql);
            return array_map(function ($e) {
                return is_object($e) ? (int) $e->order_id : (int) $e['order_id'];
            }, $results);
        } else {
            $sql = "SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` LIKE '$meta_key' AND `meta_value` LIKE '$doc_id' ORDER BY post_id ASC";
            $results = $wpdb->get_results($sql);
            return array_map(function ($e) {
                return is_object($e) ? (int) $e->post_id : (int) $e['post_id'];
            }, $results);
        }
    }

    function document_id_valid_for_order($order_id, $doc_id, $doc_type) {
        // Check if $doc_id is valid for the specified $order_id and $doc_type.
        // The document id is valid if and only if there is not an older
        // order with the same document id and document type.
        $order_ids = $this->document_id_to_order_ids($doc_id, $doc_type);
        if (empty($order_ids)) {
            return true;
        } else {
            return ($order_ids[0] == $order_id);
        }
    }

    function load_api_connector() {
        // Load the Moneybird 2 API connector
        if (!empty($this->mb_api)) {
            return $this->mb_api; // Already loaded
        }
        if (isset($this->settings['access_token']) && $this->settings['access_token']) {
            $admin_id = (isset($this->settings['administration_id'])) ? $this->settings['administration_id'] : '';
            require_once('moneybird2_api/moneybird2_api.php');
            try {
                $this->mb_api = new Moneybird2Api($this->settings['access_token'], $admin_id);
                // $this->mb_api->debug_log = "mb2_debug.log"; // Uncomment to log all Moneybird API requests to the specified file
                if ($admin_id == '') {
                    $this->settings['administration_id'] = $this->mb_api->getAdminId();
                    update_option('woocommerce_moneybird2_settings', $this->settings);
                }
                return $this->mb_api;
            } catch (Exception $e) {
                $this->log("Cannot load Moneybird API library: " . $e->getMessage());
            }
        }
        return false;
    }

    function handle_api_request() {
        // Method to handle all WooCommerce API requests
        if (isset($_REQUEST['mb_action'])) {
            if ($_REQUEST['mb_action'] == 'save_auth_settings'
                && isset($_POST['licensekey']) && (trim($_POST['licensekey']) != "")
                && isset($_POST['access_token']) && (trim($_POST['access_token']) != "")) {

                if (current_user_can('manage_options')) {
                    // Check license key validity
                    if (!wcmb_is_license_valid(trim($_POST['licensekey']), true)) {
                        printf(__('The configured license key (%s) is invalid.', 'woocommerce_moneybird'), trim($_POST['licensekey']));
                        http_response_code(406);
                        exit();
                    }
                    // Save the posted fields
                    $options = array('licensekey'    => trim($_POST['licensekey']),
                                     'access_token'  => trim($_POST['access_token']));
                    update_option('woocommerce_moneybird2_settings', $options);
                    $this->log("Authentication settings saved");
                    $this->delete_all_transients();
                    $this->init_settings();
                }
            }

            if (($_REQUEST['mb_action'] == 'load_moneybirdbox_refund')
                && isset($_REQUEST['refund_id']) && (trim($_REQUEST['refund_id']) != '')) {
                ob_end_clean();
                if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) { exit(); }
                \ExtensionTree\WCMoneyBird\render_refund_moneybird_block(intval($_REQUEST['refund_id']));
                exit();
            }

            if (($_REQUEST['mb_action'] == 'generate_refund_invoice')
                && isset($_POST['refund_id']) && (trim($_POST['refund_id']) != '')) {
                ob_end_clean();
                if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) { exit(); }
                $refund_id = intval($_REQUEST['refund_id']);
                $order = wc_get_order($refund_id);
                if ($order) {
                    $this->generate_invoice($order);
                    $this->admin_notices();
                }
                \ExtensionTree\WCMoneyBird\render_refund_moneybird_block($refund_id);
                exit();
            }

            if (($_REQUEST['mb_action'] == 'unlink_refund_invoice')
                && isset($_POST['refund_id']) && (trim($_POST['refund_id']) != '')) {
                ob_end_clean();
                if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) { exit(); }
                $refund_id = intval($_REQUEST['refund_id']);
                $refund = wc_get_order($refund_id);
                if ($refund) {
                    $refund->delete_meta_data('moneybird_invoice_id');
                    $refund->save_meta_data();
                }
                \ExtensionTree\WCMoneyBird\render_refund_moneybird_block($refund_id);
                exit();
            }

        }
    }


    function delete_all_transients() {
        delete_transient('moneybird2_mb_tax_rates');
        delete_transient('moneybird2_revenue_ledger_accounts');
        delete_transient('moneybird2_custom_fields');
        delete_transient('moneybird2_workflows');
        delete_transient('moneybird2_document_styles');
        delete_transient('moneybird2_projects');
        $this->log('Flushed all transients');
    }

    function filter_sanitized_fields($fields) {
        // Always flush the tax rates when saving the settings
        delete_transient('moneybird2_mb_tax_rates');

        return $fields;
    }

    function add_order_actions($actions, $order) {
        $actions['moneybird-invoice'] = sprintf(
                __('Generate Moneybird %s', 'woocommerce_moneybird'),
                __('invoice', 'woocommerce_moneybird')
        );
        if ($this->settings['estimate_enabled'] == 'yes') {
            if (empty($order->get_meta('moneybird_invoice_id', true))) {
                if (empty($order->get_meta('moneybird_queue_generate', true))) {
                    // Estimate generation is only available if order is not (being) invoiced
                    $actions['moneybird-estimate'] = sprintf(
                        __('Generate Moneybird %s', 'woocommerce_moneybird'),
                        __('estimate', 'woocommerce_moneybird')
                    );
                }
            }
        }
        return $actions;
    }

    function get_currency_rate($code) {
        // Get currency exchange rate against the Euro
        if (isset($this->currency_rates[$code])) {
            return $this->currency_rates[$code];
        } else {
            $rate_request = wp_remote_get('https://api.extensiontree.com/currency_rates/?code=' . $code, array('timeout' => 10));
            if (is_array($rate_request) && !is_wp_error($rate_request)) {
                $rate = wp_remote_retrieve_body($rate_request);
            } else {
                $rate = '';
            }
            if (empty($rate)) {
                return 1.0;
            }

            $rate = floatval($rate);
            if ($rate > 0.0) {
                $this->currency_rates[$code] = $rate;
                return $rate;
            } else {
                return 1.0;
            }
        }
    }

    function get_invoice_from_order($order) {
        // If the order has a Moneybird invoice attached to it, return the invoice object.
        // Else, return false
        $invoice_id = trim($order->get_meta('moneybird_invoice_id', true));
        if ($invoice_id) {
            if ($this->load_api_connector()) {
                $invoice = $this->mb_api->getSalesInvoice($invoice_id);
                if ($invoice) {
                    return $invoice;
                }
            }
        }

        return false;
    }

    function get_estimate_from_order($order) {
        // If the order has a Moneybird estimate attached to it, return the estimate object.
        // Else, return false
        $estimate_id = trim($order->get_meta('moneybird_estimate_id', true));
        if ($estimate_id) {
            if ($this->load_api_connector()) {
                if ($estimate = $this->mb_api->getEstimate($estimate_id)) {
                    return $estimate;
                }
            }
        }

        return false;
    }

    function render_admin_invoice_link($invoice, $order) {
        $state_names = array(
            'draft' => __('draft', 'woocommerce_moneybird'),
            'open' => __('open', 'woocommerce_moneybird'),
            'paid' => __('paid', 'woocommerce_moneybird'),
            'late' => __('late', 'woocommerce_moneybird'),
        );

        $invoice_deeplink = sprintf(
            'https://moneybird.com/%s/sales_invoices/%s',
            $this->settings['administration_id'],
            $invoice->id
        );
        $state_color = '#333';
        if ($invoice->state == 'open') {
            $state_color = '#f60';
        } else if ($invoice->state == 'late') {
            $state_color = '#c00';
        } else if ($invoice->state == 'paid') {
            $state_color = '#693';
        }
        if ($invoice->invoice_id) {
            echo $invoice->invoice_id . ' (<span style="font-weight: bold; color: ' . $state_color . '">' . ((isset($state_names[$invoice->state])) ? $state_names[$invoice->state] : $invoice->state) . '</span>)';
        } else {
            echo '<i>' . __('Draft', 'woocommerce_moneybird') . '</i>';
        }
        echo ' [<a href="' . $invoice_deeplink . '">' . __('open in Moneybird', 'woocommerce_moneybird') . '</a>]';
        $invoice_pdf_url = wcmb_get_invoice_pdf_url($order);
        $invoice_packing_slip_pdf_url = wcmb_get_packing_slip_pdf_url($order);
        echo "&nbsp;|&nbsp;<span class=\"dashicons dashicons-media-document\"></span><a href=\"$invoice_pdf_url\">PDF</a>";
        echo "&nbsp;|&nbsp;<span class=\"dashicons dashicons-media-document\"></span><a href=\"$invoice_packing_slip_pdf_url\">";
        _e('Packing slip', 'woocommerce_moneybird');
        echo "</a>";
    }

    private static function flattened_array_keys($array) {
        // Return a flattened array of keys
        $keys = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $subkeys = WC_MoneyBird2::flattened_array_keys($value);
                foreach ($subkeys as $subkey) {
                    $keys[] = $key . '_' . $subkey;
                }
            } else {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    function get_wc_custom_fields() {
        // Get meta keys used on shop orders
        global $woocommerce;
        global $wpdb;

        // Collect meta keys from wp postmeta table
        $sql = "SELECT DISTINCT meta_key FROM $wpdb->postmeta LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id where post_type LIKE 'shop_order%' order by meta_key ASC";
        $keys = $wpdb->get_results($sql);
        $keys = array_map(function ($e) {
            return is_object($e) ? $e->meta_key : $e['meta_key'];
        }, $keys);

        // Collect meta keys from wc_orders_meta table (HPOS table)
        if (WC_MoneyBird2::is_hpos_active()) {
            $sql = "SELECT DISTINCT meta_key FROM " . $wpdb->prefix . "wc_orders_meta ORDER BY meta_key ASC";
            $wc_orders_meta_keys = $wpdb->get_results($sql);
            $wc_orders_meta_keys = array_map(function ($e) {
                return is_object($e) ? $e->meta_key : $e['meta_key'];
            }, $wc_orders_meta_keys);
            $keys = array_unique(array_merge($keys, $wc_orders_meta_keys, array('order_id')));
        }

        // Collect data fields from most recent WC_Order
        $orders = wc_get_orders(array('limit' => 1, 'orderby' => 'date', 'order' => 'DESC'));
        foreach ($orders as $order) {
            $data_keys = WC_MoneyBird2::flattened_array_keys($order->get_base_data());
            foreach ($data_keys as $data_key) {
                if (!in_array($data_key, $keys)) {
                    if (!in_array('_' . $data_key, $keys)) {
                        $keys[] = $data_key;
                    }
                }
            }
        }

        // Add some custom keys
        $keys = array_unique(
            array_merge(
                $keys,
                array(
                    'amazon_order_id', 'bol_order_id', 'customer_order_note',
                    'order_ids', 'shipping_method_title', 'subscription_next_payment_date'
                )
            )
        );

        sort($keys);
        return apply_filters('woocommerce_moneybird_custom_fields', $keys);
    }

    function get_mb_custom_fields($force_refresh=false) {
        // Return array of Moneybird custom fields.
        // Format: [custom_field_id] => [name]
        // The key (custom_field_id) is prepended by "s" or "c" to indicate a sales_invoice or contact custom field.

        // Try to load transient or query API
        $custom_fields = array();
        if (!$force_refresh) {
            $custom_fields = get_transient('moneybird2_custom_fields');
        }
        if (!$custom_fields) {
            $custom_fields = array();
            if (!$this->load_api_connector()) { return array(); }
            $results = $this->mb_api->getCustomFields();
            if ($results) {
                foreach ($results as $result) {
                    if ($result->source == 'sales_invoice') {
                        $custom_fields['s' . $result->id] = __('Invoice', 'woocommerce_moneybird') . ' > ' . $result->name;
                    } elseif ($result->source == 'estimate') {
                        $custom_fields['e' . $result->id] = __('Estimate', 'woocommerce_moneybird') . ' > ' . $result->name;
                    } elseif ($result->source == 'contact') {
                        $custom_fields['c' . $result->id] = 'Contact > ' . $result->name;
                    }
                }
                asort($custom_fields);
                set_transient('moneybird2_custom_fields', $custom_fields, 24*60*60);
            }
        }

        return $custom_fields;
    }


    function get_revenue_ledger_accounts($force_refresh=false) {
        // Return array of Moneybird revenue ledger accounts.
        // Format: [ledger_account_id] => [name]
        // The key (ledger_account_id) is prepended by "s" to prevent php from parsing it as an integer (which causes problems on some machines).
        if (!$force_refresh) {
            $revenue_ledger_accounts = get_transient('moneybird2_revenue_ledger_accounts');
            if ($revenue_ledger_accounts !== false) {
                return $revenue_ledger_accounts;
            }
        }

        // Query the Moneybird API
        $revenue_ledger_accounts = array();
        if (!$this->load_api_connector()) { return $revenue_ledger_accounts; }
        $results = $this->mb_api->getLedgerAccounts();
        if ($results) {
            $ledger_account_details = array();
            foreach ($results as $result) {
                $ledger_account_details['s' . $result->id] = (array)$result;
                if (in_array('sales_invoice', $result->allowed_document_types)) {
                    $revenue_ledger_accounts['s' . $result->id] = $result->name;
                }
            }
            set_transient('moneybird2_ledger_account_details', $ledger_account_details, 24*60*60);
            set_transient('moneybird2_revenue_ledger_accounts', $revenue_ledger_accounts, 24*60*60);
        }

        return apply_filters('woocommerce_moneybird_revenue_ledger_accounts', $revenue_ledger_accounts);
    }

    function get_ledger_account_details($id) {
        // Get the full details of a Moneybird ledger account
        // Return array containing details, empty array in case of API request failure

        $ledger_account_details = get_transient('moneybird2_ledger_account_details');
        if (!is_array($ledger_account_details)) {
            // Refresh ledger account details
            $this->get_revenue_ledger_accounts(true);
        }
        $ledger_account_details = get_transient('moneybird2_ledger_account_details');

        if (is_array($ledger_account_details) && isset($ledger_account_details['s' . $id])) {
            return $ledger_account_details['s' . $id];
        } else {
            return array();
        }
    }

    function get_revenue_ledger_account_id($product_id) {
        // Product-level configuration
        $ledger_account_id = $this->get_product_meta($product_id, '_mb_revenue_ledger_account_id');
        if (!empty($ledger_account_id)) {
            return $ledger_account_id;
        }

        // Category-level configuration
        if ($product_id) {
            if (get_post_type($product_id) == 'product_variation') {
                $woocommerce = WC();
                $product = wc_get_product($product_id);
                if ($product) {
                    $product_id = $product->get_parent_id();
                }
            }
            if ($product_id) {
                foreach ($this->get_product_categories($product_id) as $category) {
                    $ledger_account_id = get_term_meta($category->term_id, 'mb_revenue_ledger_account_id', true);
                    if ($ledger_account_id) {
                        return $ledger_account_id;
                    }
                }
            }
        }

        // Store-wide default
        return $this->settings['products_ledger_account_id'];
    }

    function get_project_id($product_id) {
        // Product-level configuration
        $project_id = $this->get_product_meta(
            $product_id,
            '_mb_project_id',
            (!empty($this->settings['project_id'])) ? $this->settings['project_id'] : ''
        );
        if (!empty($project_id)) {
            return substr($project_id, 1);
        }

        return '';
    }

    function get_product_meta($product_id, $meta_key, $default='') {
        // Get product meta from the specified product or its parent product.
        // $default is returned if no setting is found.
        global $woocommerce;
        $val = get_post_meta($product_id, $meta_key, true);
        if (!empty($val)) {
            return $val;
        }
        if (get_post_type($product_id) == 'product_variation') {
            $product = wc_get_product($product_id);
            $parent_id = $product->get_parent_id();
            if ($product && $parent_id) {
                $val = get_post_meta($parent_id, $meta_key, true);
                if (!empty($val)) {
                    return $val;
                }
            }
        }

        return $default;
    }

    function get_product_categories($product_id) {
        // Return array of product categories to which the product belongs.
        // If a primary category is set through the Yoast SEO plugin,
        // only that category is returned.
        $primary_cat_id = get_post_meta($product_id, '_yoast_wpseo_primary_product_cat', true);
        $categories = array();
        $category_terms = get_the_terms($product_id, 'product_cat');
        if ($category_terms) {
            foreach ($category_terms as $category_term) {
                if ($primary_cat_id && ($category_term->term_id != $primary_cat_id)) {
                    continue;
                }
                $categories[] = $category_term;
            }
        }

        return $categories;
    }

    function get_revenue_ledger_account_reason($product_id) {
        // Product-level configuration
        if (get_post_meta($product_id, '_mb_revenue_ledger_account_id', true)) {
            return __('product-specific setting', 'woocommerce_moneybird');
        }

        // Category-level configuration
        foreach ($this->get_product_categories($product_id) as $category) {
            if (get_term_meta($category->term_id, 'mb_revenue_ledger_account_id', true)) {
                return sprintf(__('product category %s', 'woocommerce_moneybird'), $category->name);
            }
        }

        // Store-wide default
        return __('store-wide default', 'woocommerce_moneybird');
    }

    function migrate_old_settings() {
        if (isset($this->settings['settings_version'])) {
            $settings_version = $this->settings['settings_version'];
        } else {
            $settings_version = '';
        }

        if (empty($settings_version)) {
            $settings_version = '2.5.9';
        }

        if (WC_MONEYBIRD_VERSION == $settings_version) {
            return; // Nothing to migrate
        }

        if (version_compare($settings_version, '2.5.1', '<')) {
            // Old auto_invoice setting
            if (isset($this->settings['auto_invoice'])) {
                $triggers = array_keys($this->get_order_statuses());
                foreach($triggers as $trigger) {
                    if ($this->settings['auto_invoice'] == $trigger) {
                        $this->settings['auto_invoice_trigger_'.$trigger] = 'yes';
                    } else {
                        $this->settings['auto_invoice_trigger_'.$trigger] = 'no';
                    }
                }
                unset($this->settings['auto_invoice']);
            }
        }

        if (version_compare($settings_version, '2.6.0', '<')) {
            // invoice_reference setting
            if (!isset($this->settings['invoice_reference'])) {
                $this->settings['invoice_reference'] = __('Order #{{order_id}}', 'woocommerce_moneybird');
            }
            if (isset($this->settings['order_id_reference'])) {
                if ($this->settings['order_id_reference'] == 'yes') {
                    if (stripos(get_locale(), 'nl_') === 0) {
                        $this->settings['invoice_reference'] = 'bestelnummer {{order_id}}';
                    } else {
                        $this->settings['invoice_reference'] = 'order {{order_id}}';
                    }
                }
                unset($this->settings['order_id_reference']);
            }
        }

        if (version_compare($settings_version, '3.0.0', '<')) {
            // Fill credit invoice settings with defaults
            if (!isset($this->settings['refund_automatic_invoice'])) {
                $this->settings['refund_automatic_invoice'] = 'no';
            }
            if (!isset($this->settings['refund_workflow_id'])) {
                if (isset($this->settings['workflow_id'])) {
                    $this->settings['refund_workflow_id'] = $this->settings['workflow_id'];
                } else {
                    $this->settings['refund_workflow_id'] = 'auto';
                }
            }
            if (!isset($this->settings['refund_mark_paid'])) {
                $this->settings['refund_mark_paid'] = 'yes';
            }
            if (!isset($this->settings['refund_send_invoice'])) {
                $this->settings['refund_send_invoice'] = 'Manual';
            }
            if (!isset($this->settings['refund_invoice_reference'])) {
                $this->settings['refund_invoice_reference'] = __('Refund order #{{order_id}}', 'woocommerce_moneybird');
            }
        }

        if (version_compare($settings_version, '3.5.0', '<')) {
            if (!isset($this->settings['frontend_button'])) {
                $this->settings['frontend_button'] = 'no';
            }
        }

        if (version_compare($settings_version, '3.6.0', '<')) {
            if (!isset($this->settings['email_attachments'])) {
                $this->settings['email_attachments'] = '';
            }
        }

        if (version_compare($settings_version, '3.7.0', '<')) {
            // Enable automatic invoicing triggers for all payment methods
            $triggers = array_keys($this->get_order_statuses());
            foreach($triggers as $trigger) {
                if (isset($this->settings['auto_invoice_trigger_'.$trigger])) {
                    if ($this->settings['auto_invoice_trigger_'.$trigger] == 'yes') {
                        $pmf_field = 'auto_invoice_trigger_'.$trigger.'_p-all';
                        if (!isset($this->settings[$pmf_field])) {
                            $this->settings[$pmf_field] = 'yes';
                        }
                    }
                }
            }
        }

        if (version_compare($settings_version, '3.8.0', '<')) {
            if (!isset($this->settings['tax_rate_reverse_charge'])) {
                $this->settings['tax_rate_reverse_charge'] = '';
                if (isset($this->settings['tax_rate_none'])) {
                    $this->settings['tax_rate_reverse_charge'] = $this->settings['tax_rate_none'];
                }
            }
        }

        if (version_compare($settings_version, '3.9.0', '<')) {
            if (!isset($this->settings['serialized_custom_field_mappings'])) {
                $this->settings['serialized_custom_field_mappings'] = '';
            }
        }

        if (version_compare($settings_version, '3.9.0', '<=')) {
            if (!isset($this->settings['reverse_charge_text'])) {
                $this->settings['reverse_charge_text'] = 'Note: VAT reverse-charged';
            }
        }

        if (version_compare($settings_version, '3.12.0', '<')) {
            if (!isset($this->settings['project_id'])) {
                $this->settings['project_id'] = '';
            }
        }

        if (version_compare($settings_version, '3.19.0', '<')) {
            if (!isset($this->settings['shipping_ledger_account_id'])) {
                $this->settings['shipping_ledger_account_id'] = '';
                if (isset($this->settings['fees_ledger_account_id'])) {
                    $this->settings['shipping_ledger_account_id'] = $this->settings['fees_ledger_account_id'];
                }
            }
        }

        if (version_compare($settings_version, '3.22.0', '<')) {
            if (!isset($this->settings['rounding_error_correction_line'])) {
                $this->settings['rounding_error_correction_line'] = 'yes';
            }
        }

        if (version_compare($settings_version, '3.24.0', '<')) {
            if (!isset($this->settings['product_info'])) {
                $this->settings['product_info'] = 'name+options';
            }
            if (isset($this->settings['sku_on_invoice'])) {
                if ($this->settings['sku_on_invoice'] == 'yes') {
                    $this->settings['product_info'] = 'name+sku+options';
                }
            }
        }

        if (version_compare($settings_version, '3.26.0', '<')) {
            if (!isset($this->settings['ignore_zero_items'])) {
                $this->settings['ignore_zero_items'] = 'no';
            }
        }

        if (version_compare($settings_version, '3.27.0', '<')) {
            // Migrate invoice generation queue
            $invoice_queue = get_option('woocommerce_moneybird2_invoice_queue', array());
            if ($invoice_queue && is_array($invoice_queue)) {
                $this->add_to_queue('generate', $invoice_queue);
                update_option('woocommerce_moneybird2_invoice_queue', array());
            }
            delete_option('woocommerce_moneybird2_invoice_queue');

            // Migrate invoice deletion queue
            $delete_queue = get_option('woocommerce_moneybird2_invoice_delete_queue', array());
            if ($delete_queue && is_array($delete_queue)) {
                $this->add_to_queue('delete', $invoice_queue);
                update_option('woocommerce_moneybird2_invoice_delete_queue', array());
            }
            delete_option('woocommerce_moneybird2_invoice_delete_queue');
        }

        if (version_compare($settings_version, '3.35.0', '<')) {
            if (!isset($this->settings['respect_contact_workflow'])) {
                $this->settings['respect_contact_workflow'] = 'yes';
            }
        }

        if (version_compare($settings_version, '3.37.0', '<')) {
            if (!isset($this->settings['gift_card_redemption_ledger_account_id'])) {
                $this->settings['gift_card_redemption_ledger_account_id'] = '';
            }
        }
        if (version_compare($settings_version, '4.0', '<')) {
            if (!isset($this->settings['estimate_enabled'])) {
                $this->settings['estimate_enabled'] = 'no';
                $this->settings['estimate_sendmode'] = 'draft';
                $this->settings['estimate_document_style_id'] = '';
                $this->settings['estimate_workflow_id'] = 'auto';
                $this->settings['estimate_reference'] = '';
            }
        }
        if (version_compare($settings_version, '5.5.0', '<')) {
            wp_schedule_single_event(time() + 5, 'wc_mb_update_webhooks');
            if (!isset($this->settings['register_payment_order'])) {
                $this->settings['register_payment_order'] = 'no';
            }
        }
        if (version_compare($settings_version, '5.16.0', '<')) {
            if (!isset($this->settings['rounding_error_correction_line_ledger_account_id'])) {
                $this->settings['rounding_error_correction_line_ledger_account_id'] = '';
            }
        }
        if (version_compare($settings_version, '5.21.0', '<')) {
            if (isset($this->settings['specify_discounts']) && ($this->settings['specify_discounts'] == 'yes')) {
                $this->settings['specify_discounts'] = 'line:unit';
            }
        }
        if (version_compare($settings_version, '5.24.1', '<')) {
            $estimate_statuses = array('accepted', 'rejected', 'billed');
            foreach ($estimate_statuses as $status) {
                $setting_key = 'estimate_' . $status . '_order_status_update';
                if (!isset($this->settings[$setting_key])) {
                    $this->settings[$setting_key] = '';
                }
            }
            // Force webhook (re)creation
            \ExtensionTree\WCMoneyBird\update_webhooks(false, true);
        }

        // Update settings
        $this->settings['settings_version'] = WC_MONEYBIRD_VERSION;
        update_option('woocommerce_moneybird2_settings', $this->settings);
    }

    public function ensure_estimates_enabled() {
        if (!isset($this->settings['estimate_enabled']) || ($this->settings['estimate_enabled'] !== 'yes')) {
            $this->settings['estimate_enabled'] = 'yes';
        }
        update_option('woocommerce_moneybird2_settings', $this->settings);
    }

    function init_settings() {
        parent::init_settings();
        $this->migrate_old_settings();
    }

    function get_order_statuses() {
        $order_statuses = array();
        foreach (wc_get_order_statuses() as $code => $name) {
            if (substr($code, 0, 3) === 'wc-') {
                $code = substr($code, 3);
            }
            $order_statuses[$code] = $name;
        }

        return $order_statuses;
    }

    function init_form_fields() {
        // Only init the form fields if we're on the settings page to avoid site slowdown
        if (!isset($_GET['page']) || ($_GET['page'] != 'wc-settings')) {
            return;
        }
        if (!isset($_GET['tab']) || ($_GET['tab'] != 'integration')) {
            return;
        }
        if (isset($_GET['section']) && ($_GET['section'] != 'moneybird2')) {
            return;
        }

        $ledger_accounts = $this->get_revenue_ledger_accounts(true);
        $invoice_workflow_ids = $this->get_workflows(true, 'invoice');
        $document_styles = $this->get_document_styles(true);
        $projects = $this->get_projects(true);

        // Filter order statuses that can be used as invoice generation triggers
        $orderstatus_invoice_trigger_options = array();
        $register_payment_order_new_status_choices = array(
            '' => __('Let WooCommerce decide new order status when invoice is paid', 'woocommerce_moneybird'),
        );
        $orderstatus_estimate_update_options = array(
            '' => __('No action', 'woocommerce_moneybird'),
        );
        $disallowed_status_triggers = array('cancelled', 'refunded','failed');
        foreach ($this->get_order_statuses() as $code => $name) {
            $orderstatus_estimate_update_options[$code] = sprintf(
                __('Update order status to "%s"', 'woocommerce_moneybird'),
                $name
            );
            if (in_array($code, $disallowed_status_triggers)) {
                continue;
            }
            $orderstatus_invoice_trigger_options[$code] = $name;
            if (!in_array($code, array('pending', 'on-hold', 'checkout-draft'))) {
                $register_payment_order_new_status_choices[$code] = sprintf(
                    __('Update order status to "%s" when invoice is paid', 'woocommerce_moneybird'),
                    $name
                );
            }
        }

        $this->form_fields = array(
            'settings_version' => array(
                'type'      => 'hidden',
                'default'   => WC_MONEYBIRD_VERSION
            ),
            'licensekey' => array(
                'title'             => __('Extension license key', 'woocommerce_moneybird'),
                'description'       => __("A valid license key is required to use the plugin. You can keep using the plugin even if the license key has expired. However, you won't receive updates and support in that case.", 'woocommerce_moneybird'),
                'desc_tip'          => true,
                'type'              => 'text',
                'class'             => 'licensekey'
            ),

            //*****************************************************************
            // Invoice settings
            //*****************************************************************

            'invoice_settings_title' => array(
                'title' => __( 'Invoice settings', 'woocommerce_moneybird' ),
                'description' => __('Configure the (automatic) generation of Moneybird invoices for orders. Some settings apply to both invoices and estimates.', 'woocommerce_moneybird'),
                'type' => 'title',
                'id' => 'invoice_settings'
            ),
            'access_token' => array(
                'type'              => 'hidden',
                'default'           => '',
            ),
            'administration_id' => array(
                'type'              => 'hidden',
                'default'           => '',
            ),
            'document_style_id' => array(
                'title'             => __('Document style', 'woocommerce_moneybird'),
                'description'       => __('Select the document style that should be used.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => $document_styles
            ),
            'workflow_id' => array(
                'title'             => __('Invoice workflow [unpaid]', 'woocommerce_moneybird'),
                'description'       => __('Select the invoice workflow that should be used for unpaid orders.', 'woocommerce_moneybird') . ' ' . __('This setting can be overridden for individual products.', 'woocommerce_moneybird') . ' ' . __('Note: the workflow of existing invoices will never be updated.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => $invoice_workflow_ids
            ),
            'workflow_id_paid' => array(
                'title'             => __('Invoice workflow [paid]', 'woocommerce_moneybird'),
                'description'       => __('Select the invoice workflow that should be used for paid orders.', 'woocommerce_moneybird') . ' ' . __('This setting can be overridden for individual products.', 'woocommerce_moneybird') . ' ' . __('Note: the workflow of existing invoices will never be updated.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => $invoice_workflow_ids
            ),
            'respect_contact_workflow' => array(
                'title'             => __('Contact-specific workflow', 'woocommerce_moneybird'),
                'label'             => __('Always respect the preferred workflow setting of a contact if set.', 'woocommerce_moneybird'),
                'desc_tip'          => true,
                'description'       => __('It is possible to specify the preferred workflow for contacts in Moneybird. Enable this option to always respect the contact-specific workflow setting (if set).', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'yes',
            ),
            'auto_invoice_trigger'  => array(
                'title'             => __('Automatically create invoice when an order gets the following status', 'woocommerce_moneybird'),
                'description'       => __('If you want to automatically create invoices for WooCommerce orders, select the order statuses that should trigger invoice generation. Orders that already have an invoice will not be invoiced again.', 'woocommerce_moneybird') . ' ' .
                                       __('Optionally, you can filter the invoicing trigger on the used payment method.', 'woocommerce_moneybird'),
                'type'              => 'trigger_multichecks',
                'desc_tip'          => true,
                'default'           => 'none',
                'options'           => $orderstatus_invoice_trigger_options
            ),
            'register_payment' => array(
                'title'             => __('Register payments', 'woocommerce_moneybird'),
                'label'             => __('Automatically mark Moneybird invoice as paid if the WooCommerce order is paid.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'yes',
            ),
            'register_payment_order' => array(
                'title'             => '',
                'label'             => __('Automatically mark WooCommerce order as paid when the Moneybird invoice is fully paid.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'no',
            ),
            'register_payment_order_new_status' => array(
                'type'              => 'select',
                'default'           => '',
                'options'           => $register_payment_order_new_status_choices,
                'custom_attributes' => array(
                    'data-display-dependency' => '#woocommerce_moneybird2_register_payment_order',
                    'data-display-if' => '#woocommerce_moneybird2_register_payment_order:checked',
                    'data-display-parent-selector' => 'tr',
                ),
                'css'               => 'display:none;',
                'class'             => 'conditional_display'
            ),
            'send_invoice' => array(
                'title'             => __('Invoice sending', 'woocommerce_moneybird'),
                'description'       => __('Select if invoices should be send to the customer.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'default'           => 'Manual',
                'options'           => array(
                                        'draft'                 => __('Don\'t send, create invoice as draft', 'woocommerce_moneybird'),
                                        'Manual'                => __('Activate the invoice but don\'t send (mark as manually sent)', 'woocommerce_moneybird'),
                                        'default'               => sprintf(
                                            __('Send the %s according to the workflow and contact settings', 'woocommerce_moneybird'),
                                            __('invoice', 'woocommerce_moneybird')),
                                        'Email'                 => __('Send the invoice by email', 'woocommerce_moneybird'),
                                        'Simplerinvoicing'      => __('Send the invoice by Peppol', 'woocommerce_moneybird'),
                                        )
            ),
            'invoice_date' => array(
                'title'             => __('Invoice date', 'woocommerce_moneybird'),
                'description'       => __('Select which date should should be used as invoice/estimate date.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => array('invoice_generate_date' => __('Date on which the invoice/estimate is generated', 'woocommerce_moneybird'),
                                             'order_date'            => __('Date on which the order is placed', 'woocommerce_moneybird')),
                'default'           => 'invoice_generate_date',
            ),
            'products_ledger_account_id' => array(
                'title'             => __('Default revenue ledger account for products', 'woocommerce_moneybird'),
                'description'       => __('Select the default revenue ledger account that should be assigned to invoice lines corresponding to products. This default can be overruled for specific categories and/or individual products.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => array_merge(array('' => __('Default', 'woocommerce_moneybird')), $ledger_accounts),
                'default'           => '',
            ),
            'shipping_ledger_account_id' => array(
                'title'             => __('Revenue category for shipping', 'woocommerce_moneybird'),
                'description'       => __('Select the revenue ledger account that should be assigned to invoice lines corresponding to shipping costs.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => array_merge(array('' => __('Default', 'woocommerce_moneybird')), $ledger_accounts),
                'default'           => '',
            ),
            'fees_ledger_account_id' => array(
                'title'             => __('Revenue category for miscellaneous fees', 'woocommerce_moneybird'),
                'description'       => __('Select the revenue ledger account that should be assigned to invoice lines corresponding to fees.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => array_merge(array('' => __('Default', 'woocommerce_moneybird')), $ledger_accounts),
                'default'           => '',
            ),
            'gift_card_redemption_ledger_account_id' => array(
                'title'             => __('Revenue category for voucher redemption', 'woocommerce_moneybird'),
                'description'       => __('Select the revenue ledger account that should be assigned to invoice lines corresponding to voucher or gift card redemption.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => array_merge(array('' => __('Default', 'woocommerce_moneybird')), $ledger_accounts),
                'default'           => '',
            ),
            'project_id' => array(
                'title'             => __('Project', 'woocommerce_moneybird'),
                'description'       => __('Select a Moneybird project to book all invoice lines on.', 'woocommerce_moneybird'),
                'type'              => (empty($projects)) ? 'hidden' : 'select',
                'desc_tip'          => true,
                'options'           => array_merge(array('' => __('None', 'woocommerce_moneybird')), $projects),
                'default'           => '',
            ),
            'contact_reuse_policy'  => array(
                'title'             => __('Contact reuse policy', 'woocommerce_moneybird'),
                'description'       => __('Select the desired strategy for reusing existing Moneybird contacts. You can explicitly link a WP user to a Moneybird contact on the WP user edit page.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => array('all'   => __('Only reuse existing Moneybird contact if all fields are identical', 'woocommerce_moneybird'),
                    'email' => __('Reuse existing Moneybird contact if email is identical', 'woocommerce_moneybird'),
                    'email_update' => __('Update and reuse existing Moneybird contact if email is identical', 'woocommerce_moneybird')
                ),
                'default'           => 'all',
            ),
            'empty_invoice_details_user_id' => array(
                'title'             => __('WP user id if invoice address is empty', 'woocommerce_moneybird'),
                'description'       => __('WP user id to take invoice address from in case the order has no WP user and no invoice address.', 'woocommerce_moneybird'),
                'type'              => 'text',
                'desc_tip'          => true,
                'default'           => '',
            ),
            'invoice_reference' => array(
                'title'             => __('Reference on invoice', 'woocommerce_moneybird'),
                'description'       => __('Specify the content of the reference field on the invoice. Available placeholders:', 'woocommerce_moneybird') . ' ' . $this->custom_field_placeholders . '.',
                'type'              => 'text',
                'desc_tip'          => true,
                'default'           => __('Order {{order_ids}}', 'woocommerce_moneybird'),
            ),
            'product_info' => array(
                'title'             => __('Product info on invoice', 'woocommerce_moneybird'),
                'description'       => __('Select the product information to include on Moneybird documents', 'woocommerce_moneybird'),
                'desc_tip'          => true,
                'type'              => 'select',
                'options'           => array(
                    'name'                  => __('Product name', 'woocommerce_moneybird'),
                    'name+options'          => __('Product name', 'woocommerce_moneybird') . " + " . __('options', 'woocommerce_moneybird'),
                    'name+options+meta'     => __('Product name', 'woocommerce_moneybird') . " + " . __('options', 'woocommerce_moneybird') . " + meta data",
                    'name+sku'              => __('Product name', 'woocommerce_moneybird') . " + SKU",
                    'name+sku+options'      =>__('Product name', 'woocommerce_moneybird') . " + " . __('options', 'woocommerce_moneybird') . " + SKU",
                    'name+sku+options+meta' =>__('Product name', 'woocommerce_moneybird') . " + " . __('options', 'woocommerce_moneybird') . " + SKU + meta data",
                ),
            ),
            'specify_discounts' => array(
                'title'             => __('Specify discounts', 'woocommerce_moneybird'),
                'label'             => __('Specify applied discounts per item on the invoice.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'options'        => array(
                    'no'                        => __('Do not specify discounts', 'woocommerce_moneybird'),
                    'line:unit'                 => __('Show discount per item and regular price per item', 'woocommerce_moneybird'),
                    'line:total'                => __('Show total discount per line', 'woocommerce_moneybird'),
                    'order:total'               => __('Show total order discount on separate line', 'woocommerce_moneybird'),
                    'line:unit,order:total'     => __('Show discount per item and regular price per item + total order discount on separate line', 'woocommerce_moneybird'),
                    'line:total,order:total'    => __('Show total discount per line + total order discount on separate line', 'woocommerce_moneybird'),
                ),
                'default'           => 'no',
            ),
            'ignore_zero_orders' => array(
                'title'             => __('Ignore 0.0 orders', 'woocommerce_moneybird'),
                'label'             => __('Don\'t create Moneybird invoice for orders with order total 0.0.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'yes',
            ),
            'ignore_zero_items' => array(
                'title'             => __('Ignore free items', 'woocommerce_moneybird'),
                'label'             => __('Skip free items on the Moneybird invoice.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'no',
            ),
            'delete_payments_failed_orders' => array(
                'title'             => __('Processing of failed orders', 'woocommerce_moneybird'),
                'label'             => __('Delete invoice payments if the corresponding order is set to "failed".', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'no',
            ),
            'rounding_error_correction_line' => array(
                'title'             => __('Prevent rounding errors', 'woocommerce_moneybird'),
                'label'             => __('Add a correction line if there is a rounding difference between the order and invoice.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'yes',
            ),
            'rounding_error_correction_line_ledger_account_id' => array(
                'type'              => 'select',
                'default'           => '',
                'description'       => __('Select the ledger account that should be used for the rounding error correction line.', 'woocommerce_moneybird'),
                'options'           => array_merge(array('' => __('Default', 'woocommerce_moneybird')), $ledger_accounts),
                'custom_attributes' => array(
                    'data-display-dependency' => '#woocommerce_moneybird2_rounding_error_correction_line',
                    'data-display-if' => '#woocommerce_moneybird2_rounding_error_correction_line:checked',
                    'data-display-parent-selector' => 'tr',
                ),
                'css'               => 'display:none;',
                'class'             => 'conditional_display'
            ),
            'frontend_button' => array(
                'title'             => __('Front-end invoice PDF link', 'woocommerce_moneybird'),
                'label'             => __('Show invoice PDF link in front-end for orders that have an invoice', 'woocommerce_moneybird'),
                'description'       => __('The link is added to the order details page in the My Account section.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'no',
            ),
        );

        $this->form_fields = array_merge($this->form_fields, array(
            //*****************************************************************
            // Credit invoice settings
            //*****************************************************************

            array(  'title' => __('Credit invoice settings', 'woocommerce_moneybird'),
                    'description' => __('Configure the (automatic) generation of credit invoices for refunds.', 'woocommerce_moneybird'),
                    'type' => 'title',
                    'id' => 'credit_invoice_settings' ),

            'refund_automatic_invoice'  => array(
                'title'             => __('Automatic generation', 'woocommerce_moneybird'),
                'label'             => __('Automatically create credit invoices for refunds.', 'woocommerce_moneybird'),
                'description'       => __('Only refunds for an order that already has a Moneybird invoice will trigger automatic credit invoice generation.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'no',
            ),
            'refund_workflow_id' => array(
                'title'             => __('Credit invoice workflow', 'woocommerce_moneybird'),
                'description'       => __('Select the workflow that should be used for credit invoices.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'options'           => $invoice_workflow_ids
            ),
            'refund_mark_paid' => array(
                'title'             => __('Mark as paid', 'woocommerce_moneybird'),
                'label'             => __('Mark credit invoices as paid in Moneybird.', 'woocommerce_moneybird'),
                'type'              => 'checkbox',
                'default'           => 'yes',
            ),
            'refund_send_invoice' => array(
                'title'             => __('Credit invoice sending', 'woocommerce_moneybird'),
                'description'       => __('Select if credit invoices should be send to the customer.', 'woocommerce_moneybird'),
                'type'              => 'select',
                'desc_tip'          => true,
                'default'           => 'Manual',
                'options'           => array(
                                        'draft'             => __('Don\'t send, create invoice as draft', 'woocommerce_moneybird'),
                                        'Manual'            => __('Activate the invoice but don\'t send (mark as manually sent)', 'woocommerce_moneybird'),
                                        'default'           => sprintf(
                                            __('Send the %s according to the workflow and contact settings', 'woocommerce_moneybird'),
                                            __('invoice', 'woocommerce_moneybird')),
                                        'Email'             => __('Send the invoice by email', 'woocommerce_moneybird'),
                                        'Simplerinvoicing'  => __('Send the invoice by Peppol', 'woocommerce_moneybird'),
                                        )
            ),
            'refund_invoice_reference' => array(
                'title'             => __('Reference on credit invoice', 'woocommerce_moneybird'),
                'description'       => __('Specify the content of the reference field on the credit invoice. You may use the {{order_id}} placeholder to insert the order number.', 'woocommerce_moneybird'),
                'type'              => 'text',
                'desc_tip'          => true,
                'default'           => __('Refund order {{order_id}}', 'woocommerce_moneybird'),
            ),
        ));

        // WooCommerce email attachments
        $mailer = WC()->mailer();
        $wc_emails = array();
        foreach ($mailer->get_emails() as $email) {
            if (strpos($email->id, 'customer') !== 0) {
                $wc_emails[$email->id] = $email->title;
            } else {
                $wc_emails[$email->id] = $email->title . ' (customer)';
            }
        }
        $this->form_fields['email_attachments_section'] = array(
            'title'       => __('WooCommerce e-mail attachments', 'woocommerce_moneybird'),
            'type'        => 'title',
            'description' => '',
        );
        $this->form_fields['email_attachments'] = array(
            'title'       => __('Attach PDF invoice to e-mails', 'woocommerce_moneybird'),
            'type'        => 'multiselect',
            'description' => __('Attach the Moneybird invoice as PDF to the selected WooCommerce e-mails (if available at the time of sending). Use Ctrl/Cmd to select multiple.', 'woocommerce_moneybird'),
            'options'     => $wc_emails
        );

        //*****************************************************************
        // ESTIMATES
        //*****************************************************************

        $this->form_fields['estimate_section'] = array(
            'title'       => __('Estimates', 'woocommerce_moneybird'),
            'type'        => 'title',
            'description' => sprintf(
                    __('Moneybird estimates can be generated manually or automatically. For automatic generation, use the <a href="%s">Moneybird Estimate payment gateway</a>. Settings that apply to both invoices and estimates (revenue ledger accounts, projects, etc.) can be configured in the invoice settings section.', 'woocommerce_moneybird'),
                    admin_url('admin.php?page=wc-settings&tab=checkout&section=moneybirdestimate')
            )
        );
        $this->form_fields['estimate_enabled'] = array(
            'title'       => __('Enable/Disable', 'woocommerce'),
            'label'       => __('Enable Moneybird estimate functionality.', 'woocommerce_moneybird'),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no',
            'class'       => 'wcmb-estimates-enabled',
        );
        $this->form_fields['estimate_sendmode'] = array(
            'title'             => __('Estimate sending', 'woocommerce_moneybird'),
            'type'              => 'select',
            'css'               => 'width: 400px;',
            'default'           => 'draft',
            'description'       => __('Select which action should be taken after the estimate is generated.', 'woocommerce_moneybird'),
            'options'           => array(
                'draft'     => __('Leave estimate in draft state, do not send', 'woocommerce_moneybird'),
                'Manual'    => __('Activate but do not send to customer', 'woocommerce_moneybird'),
                'default'   => sprintf(
                        __('Send the %s according to the workflow and contact settings', 'woocommerce_moneybird'),
                        __('estimate', 'woocommerce_moneybird')),
                'Email'     => __('Send to customer by email', 'woocommerce_moneybird'),
            ),
            'desc_tip'          => true,
        );
        $this->form_fields['estimate_document_style_id'] = array(
            'title'             => __('Document style', 'woocommerce_moneybird'),
            'description'       => __('Select the document style that should be used.', 'woocommerce_moneybird'),
            'type'              => 'select',
            'desc_tip'          => true,
            'options'           => $document_styles
        );
        $this->form_fields['estimate_workflow_id'] = array(
            'title'             => __('Estimate', 'woocommerce_moneybird') . ' workflow',
            'description'       => __('Select the estimate workflow that should be used.', 'woocommerce_moneybird'),
            'type'              => 'select',
            'desc_tip'          => true,
            'options'           => $this->get_workflows(false, 'estimate')
        );
        $this->form_fields['estimate_reference'] = array(
            'title'             => __('Reference on estimate', 'woocommerce_moneybird'),
            'description'       => __('Specify the content of the reference field on the estimate. Available placeholders:', 'woocommerce_moneybird') . ' ' . $this->custom_field_placeholders . '.',
            'type'              => 'text',
            'desc_tip'          => true,
            'default'           => __('Order {{order_ids}}', 'woocommerce_moneybird'),
        );
        $this->form_fields['estimate_accepted_order_status_update'] = array(
            'title'             => __('Estimate accepted', 'woocommerce_moneybird'),
            'description'       => __('Select the order status that should be set when an estimate is', 'woocommerce_moneybird') . ' ' . __('accepted', 'woocommerce_moneybird') . '.',
            'type'              => 'select',
            'desc_tip'          => true,
            'default'           => '',
            'options'           => $orderstatus_estimate_update_options,
        );
        $this->form_fields['estimate_rejected_order_status_update'] = array(
            'title'             => __('Estimate rejected', 'woocommerce_moneybird'),
            'description'       => __('Select the order status that should be set when an estimate is', 'woocommerce_moneybird') . ' ' . __('rejected', 'woocommerce_moneybird') . '.',
            'type'              => 'select',
            'desc_tip'          => true,
            'default'           => '',
            'options'           => $orderstatus_estimate_update_options,
        );
        $this->form_fields['estimate_billed_order_status_update'] = array(
            'title'             => __('Estimate billed', 'woocommerce_moneybird'),
            'description'       => __('Select the order status that should be set when an estimate is', 'woocommerce_moneybird') . ' ' . __('billed', 'woocommerce_moneybird') . '.',
            'type'              => 'select',
            'desc_tip'          => true,
            'default'           => '',
            'options'           => $orderstatus_estimate_update_options,
        );

        //*****************************************************************
        // CUSTOM FIELD MAPPINGS
        //*****************************************************************

        $this->form_fields['custom_field_mappings'] = array(
            'title'       => __('Custom field mappings', 'woocommerce_moneybird'),
            'type'        => 'title',
            'description' => __('Optionally map checkout fields or extra order fields to Moneybird custom fields.', 'woocommerce_moneybird' ),
        );
        // The custom_field_mapping field is a dummy to display the mapping table
        $this->form_fields['custom_field_mapping'] = array(
            'title'       => __('Custom field mappings', 'woocommerce_moneybird'),
            'type'        => 'custom_fields_mapping',
            'desc_tip'    => __("Link fields to automatically fill custom fields in Moneybird with the contents of the corresponding order fields.", "woocommerce_moneybird"),
        );
        // The hidden serialized_custom_field_mappings field holds the actual mappings
        $this->form_fields['serialized_custom_field_mappings'] = array(
            'type'        => 'hidden',
            'default'     => ''
        );



        $this->form_fields = array_merge($this->form_fields, array(
            //*****************************************************************
            // TAX MAPPING
            //*****************************************************************

            array(
                'title'         => __( 'Tax mapping', 'woocommerce_moneybird' ),
                'description'   =>
                    __('Explicitly map WooCommerce tax rates to Moneybird tax rates.', 'woocommerce_moneybird')
                    . ' ' . __( 'Optionally select a revenue ledger account if it should be assigned based on the VAT rate.', 'woocommerce_moneybird'),
                'type' => 'title',
                'id' => 'tax_mapping' ),
        ));

        // Order status triggers
        $gateways = WC()->payment_gateways->payment_gateways();
        foreach(array_keys($orderstatus_invoice_trigger_options) as $orderstatus) {
            $this->form_fields['auto_invoice_trigger_'.$orderstatus] = array('type' => 'hiddencheckbox');
            $this->form_fields['auto_invoice_trigger_'.$orderstatus.'_p-all'] = array('type' => 'hiddencheckbox');
            foreach ($gateways as $code => $gateway) {
                $this->form_fields['auto_invoice_trigger_'.$orderstatus.'_p-'.$code] = array('type' => 'hiddencheckbox');
            }
            // Also add the gateways that are not in the gateways array but are in the woocommerce_gateway_order option
            $gateways_order = (array) get_option('woocommerce_gateway_order');
            if ($gateways_order) {
                foreach ($gateways_order as $code) {
                    if (!isset($this->form_fields['auto_invoice_trigger_'.$orderstatus.'_p-'.$code])) {
                        $this->form_fields['auto_invoice_trigger_'.$orderstatus.'_p-'.$code] = array('type' => 'hiddencheckbox');
                    }
                }
            }
        }

        // Tax rate mappings
        $mb_tax_rates = array('' => 'Auto-detect');
        foreach ($this->get_mb_tax_rates(true) as $mb_tax_rate_id => $mb_tax_rate) {
            $mb_tax_rates[$mb_tax_rate_id] = $mb_tax_rate->name;
        }

        $wc_tax = new WC_Tax();
        $ledger_account_overrides = array_merge(
                array('' => ''),
                $ledger_accounts
        );
        foreach ($this->get_tax_rate_mappings() as $wc_tax_rate_id => $mb_tax_rate_id) {
            $this->form_fields['tax_rate_' . $wc_tax_rate_id] = array(
                    'title'         => $wc_tax->get_rate_code($wc_tax_rate_id) . ' (' . $wc_tax->get_rate_percent($wc_tax_rate_id) . ')',
                    'type'          => 'select',
                    'description'   => __('Moneybird VAT rate.', 'woocommerce_moneybird'),
                    'default'       => $mb_tax_rate_id,
                    'options'       => $mb_tax_rates
            );
            $this->form_fields['tax_rate_' . $wc_tax_rate_id . '_ledger_account_id'] = array(
                'title'         => '',
                'type'          => 'select',
                'description'   => __('Optional revenue ledger account override.', 'woocommerce_moneybird'),
                'tooltip'       => true,
                'default'       => '',
                'options'       => $ledger_account_overrides
            );
        }

        $this->form_fields['tax_rate_none'] = array(
            'title'         => __('No tax', 'woocommerce_moneybird'),
            'type'          => 'select',
            'description'   => __('Moneybird VAT rate.', 'woocommerce_moneybird'),
            'default'       => $this->detect_mb_taxrate(0.0),
            'options'       => $mb_tax_rates
        );
        $this->form_fields['tax_rate_none_ledger_account_id'] = array(
            'title'         => '',
            'type'          => 'select',
            'description'   => __('Optional revenue ledger account override.', 'woocommerce_moneybird'),
            'tooltip'       => true,
            'default'       => '',
            'options'       => $ledger_account_overrides
        );

        $this->form_fields['tax_rate_reverse_charge'] = array(
            'title'         => __('Reverse charge', 'woocommerce_moneybird'),
            'type'          => 'select',
            'description'   => __('Moneybird VAT rate.', 'woocommerce_moneybird'),
            'default'       => $this->guess_reverse_charge_mb_taxrate(),
            'options'       => $mb_tax_rates
        );
        $this->form_fields['tax_rate_reverse_charge_ledger_account_id'] = array(
            'title'         => '',
            'type'          => 'select',
            'description'   => __('Optional revenue ledger account override.', 'woocommerce_moneybird'),
            'tooltip'       => true,
            'default'       => '',
            'options'       => $ledger_account_overrides
        );
        $this->form_fields['reverse_charge_text'] = array(
            'title'         => '',
            'description'   => __('Reverse charge text', 'woocommerce_moneybird'),
            'desc_tip'      => __('This text will be placed on the invoice if VAT reverse charge is applicable.', 'woocommerce_moneybird'),
            'type'          => 'text',
            'default'       => 'Note: VAT reverse-charged',
        );

        // Debugging
        $this->form_fields['advanced'] = array(
            'title'       => __( 'Advanced options', 'woocommerce' ),
            'type'        => 'title',
            'description' => '',
        );
        $this->form_fields['debug'] = array(
            'title'       => __('Debug Log', 'woocommerce'),
            'type'        => 'checkbox',
            'label'       => __('Enable logging', 'woocommerce'),
            'default'     => 'no',
            'description' => $this->get_debug_description()
        );
        $this->form_fields['queue_status'] = array(
            'title'       => __('Queue status', 'woocommerce'),
            'type'        => 'queue_status',
        );
    }


    function get_debug_description() {
        global $woocommerce;
        if (version_compare($woocommerce->version, '8.6', '>=')) {
            $output = __( 'Log Moneybird events and errors', 'woocommerce_moneybird' );
            $output .= sprintf(' (<a href="%s">open</a>)', admin_url('admin.php?page=wc-status&tab=logs&source=woocommerce-moneybird'));
        } else {
            $output = sprintf(__( 'Log Moneybird events and errors in <code>%s</code>', 'woocommerce_moneybird' ), wc_get_log_file_path('woocommerce-moneybird'));
            $log_file = str_replace('.log', '-log', wc_get_log_file_name('woocommerce-moneybird'));
            $output .= sprintf('(<a href="%s">open</a>)', admin_url('admin.php?page=wc-status&tab=logs&log_file=' . $log_file));
        }

        return $output;
    }


    function generate_hidden_html($k, $options) {
        // Output settings form html for items of type "hidden"
        ?>
        <tr style="display:none;">
            <th scope="row">Hidden</th>
            <td>
                <input type="hidden" name="woocommerce_moneybird2_<?php echo $k; ?>" id="woocommerce_moneybird2_<?php echo $k; ?>" value="<?php echo $this->get_option($k); ?>">
            </td>
        </tr>
        <?php
    }

    function generate_queue_status_html($k, $options) {
        ob_start();
        ?>
        <tr>
            <th scope="row"><?php _e('Queue status', 'woocommerce_moneybird'); ?></th>
            <td>
                <?php
                $anything_in_queue = false;
                $invoice_queue_status = __('no actions in queue', 'woocommerce_moneybird');
                $invoice_queue = self::get_queued_items('generate', false, false);
                if (is_array($invoice_queue) && !empty($invoice_queue)) {
                    $invoice_queue_status = sprintf(__('%d invoices in queue', 'woocommerce_moneybird'), count($invoice_queue));
                    $anything_in_queue = true;
                }
                $estimate_queue_status = __('no actions in queue', 'woocommerce_moneybird');
                $estimate_queue = self::get_queued_items('generate_estimate', false, false);
                if (is_array($estimate_queue) && !empty($estimate_queue)) {
                    $estimate_queue_status = sprintf(__('%d estimates in queue', 'woocommerce_moneybird'), count($estimate_queue));
                    $anything_in_queue = true;
                }
                $deletion_queue_status = __('no actions in queue', 'woocommerce_moneybird');
                $delete_queue = self::get_queued_items('delete', false, false);
                if (is_array($delete_queue) && !empty($delete_queue)) {
                    $deletion_queue_status = sprintf(__('%d deletions in queue', 'woocommerce_moneybird'), count($delete_queue));
                    $anything_in_queue = true;
                }
                ?>
                <b><?php _e('Invoice generation', 'woocommerce_moneybird'); ?></b>: <?php echo $invoice_queue_status; ?><br/>
                <b><?php _e('Estimate generation', 'woocommerce_moneybird'); ?></b>: <?php echo $estimate_queue_status; ?><br/>
                <b><?php _e('Invoice deletion', 'woocommerce_moneybird'); ?></b>: <?php echo $deletion_queue_status; ?><br/>
                <?php
                if ($anything_in_queue) {
                    $next_scheduled = wp_next_scheduled('wc_mb_handle_invoice_queue');
                    if ($next_scheduled) { ?>
                        <b>Next call</b>: in <?php echo $next_scheduled - time(); ?> sec.
                    <?php } ?>
                <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=integration&section=moneybird2&flush_queues=1'); ?>">
                    <?php _e('Flush queues', 'woocommerce_moneybird'); ?>
                </a><br/>
                <?php } ?>
            </td>
        </tr>
        <?php

        return ob_get_clean();
    }

    function generate_hiddencheckbox_html($k, $options) {
        // Don't render a checkbox that is part of a trigger_multichecks field
        return;
    }

    public function validate_hiddencheckbox_field( $key, $value ) {
        return ! is_null( $value ) ? 'yes' : 'no';
    }

    function generate_trigger_multichecks_html($k, $data) {
        $field_key = $this->get_field_key($k);
        $defaults  = array(
            'title'             => '',
            'label'             => '',
            'disabled'          => false,
            'class'             => 'trigger_multichecks_input',
            'css'               => '',
            'type'              => 'trigger_multichecks',
            'desc_tip'          => false,
            'description'       => '',
            'options'           => array(),
            'custom_attributes' => array(),
        );

        $data = wp_parse_args( $data, $defaults );

        if ( ! $data['label'] ) {
            $data['label'] = $data['title'];
        }

        $payment_gateways = array();
        foreach (WC()->payment_gateways->payment_gateways() as $code => $gateway) {
            if (isset($gateway->enabled) && $gateway->enabled === 'yes') {
                $payment_gateways[$code] = $gateway->get_title();
            }
        }
        ob_start();
        ?>
        <tr style="vertical-align: top;">
            <th scope="row" class="titledesc">
                <label for="<?php echo $k; ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
            </th>
            <td class="forminp">
                <?php foreach($data['options'] as $opt => $opt_name): ?>
                <?php $opt_key = $k . '_' . $opt; ?>
                <?php $opt_field_key = $field_key . '_' . $opt; ?>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo $opt_name; ?></span></legend>
                    <label for="<?php echo esc_attr( $opt_field_key ); ?>">
                    <input <?php disabled( $data['disabled'], true ); ?> class="<?php echo esc_attr( $data['class'] ); ?>" type="checkbox" name="<?php echo esc_attr( $opt_field_key ); ?>" id="<?php echo esc_attr( $opt_field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="1" <?php checked( $this->get_option( $opt_key ), 'yes' ); ?> <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?> /> <?php echo $opt_name; ?></label><br/>


                    <?php
                    if ($this->get_option($opt_key)=='yes') {
                        $payment_triggers_style = 'display:block;';
                    } else {
                        $payment_triggers_style = 'display:none;';
                    }

                    ?>
                    <div id="<?php echo esc_attr( $opt_field_key ); ?>_payment_triggers" class="payment_method_triggers" style="<?php echo $payment_triggers_style; ?>">
                        <?php
                        $payment_opt_field_key = esc_attr($opt_field_key) . '_p-all';
                        $payment_opt_key = $opt_key . '_p-all';
                        ?>
                        <input type="checkbox" name="<?php echo $payment_opt_field_key; ?>" value="1" style="margin: 0 10px;" class="payment_method_trigger_filter pmf_all" <?php checked( $this->get_option( $payment_opt_key ), 'yes' ); ?> />
                        <?php _e('All payment methods', 'woocommerce_moneybird'); ?>
                        <?php foreach ($payment_gateways as $pg_code => $pg_title): ?>
                            <?php
                            $payment_opt_field_key = esc_attr($opt_field_key) . '_p-' . $pg_code;
                            $payment_opt_key = $opt_key . '_p-' . $pg_code;
                            ?>
                            <input type="checkbox" name="<?php echo $payment_opt_field_key; ?>" value="1" style="margin: 0 10px;" class="payment_method_trigger_filter pmf_specific" <?php checked( $this->get_option( $payment_opt_key ), 'yes' ); ?> />
                            <?php echo $pg_title;?>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <?php endforeach; ?>
            </td>
        </tr>
        <?php

        return ob_get_clean();
    }

    function validate_serialized_custom_field_mappings_field($key, $value) {
        // Get existing mappings
        $mappings = $this->get_custom_field_mappings();

        // Maybe add POSTed mapping
        $post_data = $this->get_post_data();
        $new_mapping = array();
        if (isset($post_data['woocommerce_moneybird2_custom_field_mapping'])) {
            $new_mapping = $post_data['woocommerce_moneybird2_custom_field_mapping'];
            if (is_array($new_mapping) && isset($new_mapping['wc']) && isset($new_mapping['mb'])) {
                if (!empty($new_mapping['wc']) && !empty($new_mapping['mb'])) {
                    // Add new mapping
                    $new_mapping = array(
                            'wc' => trim($new_mapping['wc']),
                            'mb' => trim($new_mapping['mb'])
                    );
                }
            }
        }

        // Maybe delete mapping
        $delete_mapping = '';
        if (isset($_POST['delete_mb_custom_field_mapping'])) {
            if (!empty($_POST['delete_mb_custom_field_mapping'])) {
                $delete_mapping = $_POST['delete_mb_custom_field_mapping'];
            }
        }

        // Serialize mappings
        $filtered_mappings = array();
        foreach ($mappings as $mapping) {
            if ($new_mapping && ($new_mapping['mb'] == $mapping['mb'])) {
                continue; // Drop old mapping to same MB field as new mapping
            }
            if ($mapping['mb'] == $delete_mapping) {
                continue; // Mapping has been marked for deletion
            }
            $filtered_mappings[] = $mapping;
        }
        if ($new_mapping) {
            $filtered_mappings[] = $new_mapping;
        }

        return serialize($filtered_mappings);
    }


    function validate_custom_fields_mapping_field($key, $value) {
        // The method prevents WooCommerce from reverting to text field validation,
        // which would trigger a PHP Warning since $value is an array.
        return '';
    }

    function generate_custom_fields_mapping_html($k, $data) {
        $field_key = $this->get_field_key($k);
        $defaults  = array(
            'title'         => '',
            'label'         => '',
            'disable'       => false,
            'type'          => 'custom_fields_mapping',
            'desc_tip'      => false,
            'description'   => '',
        );

        $data = wp_parse_args($data, $defaults);

        if (!$data['label']) {
            $data['label'] = $data['title'];
        }

        $wc_field_choices = $this->get_wc_custom_fields();
        $mb_field_choices = $this->get_mb_custom_fields(true);
        $mapped_fields = $this->get_custom_field_mappings();
        ob_start();
        ?>
        <tr style="vertical-align: top;">
            <th scope="row" class="titledesc">
                <label for="<?php echo $k; ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
            </th>
            <td class="forminp forminp-text">
                <table>
                    <tr>
                        <td style="padding-top: 0;" colspan="2">
                            <?php _e("WooCommerce field", "woocommerce_moneybird"); ?>
                        </td>
                        <td style="padding-top: 0;">
                            <?php _e("Moneybird custom field", "woocommerce_moneybird"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <select name="<?php echo $field_key ?>[wc]">
                                <option value=""><?php _e("Select WooCommerce field", "woocommerce_moneybird") ?></option>
                                <?php
                                foreach ($wc_field_choices as $wc_field) { ?>
                                    <option value="<?php echo $wc_field; ?>"><?php echo $wc_field; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>&rarr;</td>
                        <td>
                            <select name="<?php echo $field_key; ?>[mb]">
                                <option value=""><?php _e("Select Moneybird custom field", "woocommerce_moneybird") ?></option>
                                <?php foreach ($mb_field_choices as $id => $name) { ?>
                                    <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                                <?php } ?>
                            </select>
                        </td>


                        <td>
                            <button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save"><?php _e("Add", "woocommerce_moneybird"); ?></button>
                        </td>
                    </tr>
                    <?php
                    foreach ($mapped_fields as $mapped_field) {
                        if (isset($mb_field_choices[$mapped_field['mb']])) { ?>
                            <tr>
                                <td>
                                    <?php echo $mapped_field['wc']; ?>
                                </td>
                                <td>&rarr;</td>
                                <td>
                                    <?php echo $mb_field_choices[$mapped_field['mb']]; ?>
                                </td>
                                <td>
                                    <a style="cursor: pointer; text-decoration: none;"
                                       onclick="jQuery('#delete_mb_custom_field_mapping').val('<?php echo $mapped_field['mb']; ?>'); jQuery('.woocommerce-save-button').click();">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } ?>
                </table>
                <input type="hidden" name="delete_mb_custom_field_mapping" id="delete_mb_custom_field_mapping" value="" />
            </td>
        </tr>

        <?php

        return ob_get_clean();
    }

    function admin_options() {
        if (isset($_GET['reset']) && ($_GET['reset'] == '1')) {
            // Clear all settings
            update_option('woocommerce_moneybird2_settings', array());
            $this->settings = array();
            $this->init_settings();
            $this->delete_all_transients();
            \ExtensionTree\WCMoneyBird\update_webhooks(true);

            // Redirect to settings page
            header("Location: " . $this->get_settings_url());
            exit();
        }

        if (isset($_GET['flush_queues']) && ($_GET['flush_queues'] == '1')) {
            // Clear queues
            foreach (array('generate', 'generate_estimate', 'delete') as $queue) {
                $queued_ids = self::get_queued_items($queue, false, false);
                if ($queued_ids) {
                    foreach ($queued_ids as $order_id) {
                        $order = wc_get_order($order_id);
                        if ($order) {
                            $order->delete_meta_data('moneybird_queue_' . $queue);
                            $order->save_meta_data();
                        }
                    }
                }
            }

            // Redirect to settings page
            header("Location: " . $this->get_settings_url());
            exit();
        }

        echo '<h3>' . $this->method_title . '</h3>';
        echo isset($this->method_description) ? wpautop($this->method_description) : '';

        ?>
            <script>
                function saveMoneybirdAuthorization() {
                    var access_token = jQuery('#moneybird2_auth_access_token').val();
                    if ((access_token.length < 32) || (access_token.length > 64)) {
                        alert("<?php _e('Please enter a valid access token of at most 64 characters.', 'woocommerce_moneybird'); ?>");
                        return;
                    }

                    var license_key = jQuery('#moneybird2_license_key').val();
                    if ((license_key.length < 8) || (license_key.length > 16)) {
                        alert("<?php _e('Please enter a valid license key first.', 'woocommerce_moneybird'); ?>");
                        return;
                    }
                    jQuery("body").attr('onbeforeunload', '');

                    // Save authentication settings and reload settings page
                    var api_url = "<?php echo str_replace('http://', '//', admin_url('admin-ajax.php')); ?>";
                    jQuery.post(api_url, { 'action': 'wcmb_api', 'mb_action': 'save_auth_settings', 'access_token': access_token, 'licensekey': license_key })
                        .fail(function(data) {
                            if (data.responseText) {
                                alert(data.responseText);
                            }
                        })
                        .done(function() {
                            jQuery('#TB_window').hide();
                            window.location = "<?php echo $this->get_settings_url(); ?>";
                        });
                }

                function clearSettings() {
                    if (confirm("<?php _e('Are you sure you want to clear all settings?', 'woocommerce_moneybird'); ?>")) {
                        window.location = window.location.href + '&reset=1';
                    }
                }
            </script>
        <?php

        // Output the settings form
        if (isset($this->settings['access_token']) && !empty($this->settings['access_token'])) {
            if (!wcmb_is_license_valid($this->settings['licensekey'])) {
                ?>
                <p>
                <span style="color:#FF0000; font-weight: bold;">
                    <?php printf(__('The configured license key (%s) is invalid.', 'woocommerce_moneybird'), $this->settings['licensekey']); ?>
                </span>
                </p><p>
                <a class="button-primary" style="cursor:pointer;" onClick="clearSettings();">
                    <?php _e("Reset and start over", "woocommerce_moneybird"); ?>
                </a>
                </p>
                <?php
                return;
            }
            $admin_name = $this->test_api_connection();
            echo '<p>' . __('Connection status:', 'woocommerce_moneybird') . ' ';
            if ($admin_name) {
                echo '<span style="color:#009900;"><b>' . sprintf(__('connected to "%s".', 'woocommerce_moneybird'), $admin_name) . '</b></span>&nbsp;&nbsp;[ <a style="cursor:pointer;" onClick="clearSettings();">' . __("Reset/disable", "woocommerce_moneybird") . '</a> ]<br/>';
            } else {
                echo '<span style="color:#FF0000;"><b>' . __('cannot connect to the Moneybird API.', 'woocommerce_moneybird') . '</b></span>&nbsp;&nbsp;[ <a style="cursor:pointer;" onClick="clearSettings();">' . __("Reset/disable", "woocommerce_moneybird") . '</a> ]<br/>';
            }
            echo '</p>';

            if ($admin_name): ?>
                <table class="form-table">
                <?php $this->generate_settings_html(); ?>
                </table>
                <div><input type="hidden" name="section" value="' . <?php echo $this->id; ?> . '" /></div>

                <?php
                // Insert license key status block
                $status_url = 'https://my.extensiontree.com/licenses/QBBXQ84NURJVGTQR/';
                $status_url .= $this->settings['licensekey'] . '/';
                $status_url .= rtrim(strtr(base64_encode(network_site_url('/')), '+/', '-_'), '=');
                $status_url .= '.html';
                if (isset($this->settings['licensekey']) && !empty($this->settings['licensekey'])): ?>
                    <script>
                    jQuery.get("<?php echo $status_url; ?>").done(function (data) {
                        jQuery('.woocommerce_page_wc-settings input.licensekey').parent().append('<p>'+data+'</p>');
                    });
                    </script>
                <?php endif; ?>
            <?php endif;
        } else { ?>
            <?php add_thickbox(); ?>
            <div id="auth-modal" style="display:none;">
                <h2><?php _e("Set up Moneybird API authorization", "woocommerce_moneybird"); ?></h2>
                <p><?php _e("Follow these instructions step by step to grant this plugin access to your Moneybird account.", "woocommerce_moneybird"); ?></p>

                <h3><?php _e("Step 1: Create API token", "woocommerce_moneybird"); ?></h3><hr>

                <ol>
                    <li><?php _e("Visit the settings page of your Moneybird administration and create an API token:", "woocommerce_moneybird"); ?>
                        <ol>
                            <li><?php _e('Click \'External applications\' at the bottom of the Moneybird settings page.', "woocommerce_moneybird"); ?></li>
                            <li><?php _e('Click the green \'Create API token\' button.', "woocommerce_moneybird"); ?></li>
                            <li><?php _e('Enter a descriptive name, for example', "woocommerce_moneybird"); ?> <code>WooCommerce Moneybird plugin</code>.</li>
                            <li><?php _e('Make sure the permission checkboxes for Invoices, Estimates and Settings are checked.', "woocommerce_moneybird"); ?></li>
                            <li><?php _e('Press save.', "woocommerce_moneybird"); ?></li>
                        </ol>
                    </li>
                    <li>
                        <?php _e('Copy the generated API token in here:', "woocommerce_moneybird"); ?><br/>
                        <input name="moneybird2_auth_access_token" id="moneybird2_auth_access_token" type="text" style="min-width:550px;"><br/>
                    </li>
                </ol>

                <h3><?php _e("Step 2: Enter license key", "woocommerce_moneybird"); ?></h3><hr>

                <p>
                    <?php echo sprintf(__('Please enter your license key to receive plugin updates. You received this key by email when you purchased the license. In case you lost your license key, please contact us through <a href="%s">%s</a>', 'woocommerce_moneybird'), 'https://extensiontree.com/nl/support', 'https://extensiontree.com/nl/support'); ?><br/>
                    <b><?php _e("Extension license key", "woocommerce_moneybird"); ?></b>: <input name="moneybird2_license_key" id="moneybird2_license_key" type="text">
                </p>

                <h3><?php _e("Step 3: Link the plugin to your Moneybird account", "woocommerce_moneybird"); ?></h3><hr>

                <p><?php _e("Click the button below to save the authentication details.", "woocommerce_moneybird"); ?></p>
                <a onClick="saveMoneybirdAuthorization()" class="button-primary"><?php _e("Save details and connect to Moneybird", "woocommerce_moneybird"); ?></a>
            </div>

            <a href="#TB_inline?width=600&inlineId=auth-modal" class="thickbox button-primary" id="mb_auth_button"><?php _e("Set up Moneybird API authorization", "woocommerce_moneybird"); ?></a>
        <?php
        }
    }


    public function get_settings_url() {
        return admin_url('admin.php?page=wc-settings&tab=integration&section=moneybird2');
    }

    public function add_moneybird_column($columns) {
        if (!empty($this->settings['administration_id'])) {
            $columns['wc-mb'] = 'Moneybird';
        }
        return $columns;
    }

    public function fill_moneybird_column($column_name, $order=null) {
        if ($column_name != 'wc-mb' || empty($this->settings['administration_id'])) {
            return;
        }
        if (empty($order)) {
            $order = wc_get_order(get_the_ID());
        }
        $order_id = $order->get_id();
        $invoice_id = $order->get_meta('moneybird_invoice_id', true);
        if ($this->settings['estimate_enabled']==='yes') {
            $estimate_id = $order->get_meta('moneybird_estimate_id', true);
            if ($estimate_id) {
                $deeplink = sprintf(
                    'https://moneybird.com/%s/estimates/%s',
                    $this->settings['administration_id'],
                    $estimate_id
                );
                echo '<a style="display: block; clear: both;" href="'.$deeplink.'">Open ';
                _e('estimate', 'woocommerce_moneybird');
                echo '<span class="dashicons dashicons-external"></span></a>';
            } elseif (!empty($order->get_meta('moneybird_queue_generate_estimate', true))) {
                echo '<div><span class="dashicons dashicons-clock"></span> ';
                _e('Estimate generation queued', 'woocommerce_moneybird');
                echo '</div>';
            } elseif (empty($invoice_id)) {
                $onclick = "jQuery('#cb-select-$order_id').prop('checked', true ); jQuery('#bulk-action-selector-top').val('generate_mb_estimate'); jQuery('#doaction').click();";
                echo " <a class=\"button\" href=\"#\" onclick=\"$onclick\">";
                echo __('Generate estimate', 'woocommerce_moneybird') . '</a> ';
            }
        }
        if ($invoice_id) {
            $deeplink = sprintf(
                'https://moneybird.com/%s/sales_invoices/%s',
                $this->settings['administration_id'],
                $invoice_id
            );
            $invoice_pdf_url = wcmb_get_invoice_pdf_url($order);
            $invoice_packing_slip_pdf_url = wcmb_get_packing_slip_pdf_url($order);
            echo "<div><a href=\"$deeplink\">";
            _e('Open invoice', 'woocommerce_moneybird');
            echo '<span class="dashicons dashicons-external"></span></a>';
            echo "&nbsp;|&nbsp;<a href=\"$invoice_pdf_url\"><span class=\"dashicons dashicons-media-document\"></span>PDF</a>";
            echo "&nbsp;|&nbsp;<a href=\"$invoice_packing_slip_pdf_url\"><span class=\"dashicons dashicons-media-document\"></span>";
            _e('Packing slip', 'woocommerce_moneybird');
            echo "</a></div>";
        } elseif (!empty($order->get_meta('moneybird_queue_generate', true))) {
            echo '<div><span class="dashicons dashicons-clock"></span> ';
            _e('Invoice generation queued', 'woocommerce_moneybird');
            echo '</div>';
        } else {
            $onclick = "jQuery('#cb-select-$order_id').prop('checked', true ); jQuery('#bulk-action-selector-top').val('generate_mb_invoice'); jQuery('#doaction').click();";
            echo "<a class=\"button\" href=\"#\" onclick=\"$onclick\">";
            echo __('Generate invoice', 'woocommerce_moneybird') . '</a>';
        }
    }

    function add_bulk_actions($actions) {
        $actions['generate_mb_invoice'] = sprintf(
                __('Moneybird %s generate', 'woocommerce_moneybird'),
                __('invoice', 'woocommerce_moneybird')
        );
        $actions['generate_mb_invoice_combined'] = __('Moneybird combined invoice generate', 'woocommerce_moneybird');
        $actions['download_mb_invoices'] = sprintf(
            __('Moneybird %s PDF download', 'woocommerce_moneybird'),
            __('invoices', 'woocommerce_moneybird')
        );
        $actions['unlink_mb_invoice'] = sprintf(
            __('Moneybird %s unlink', 'woocommerce_moneybird'),
            __('invoice', 'woocommerce_moneybird')
        );
        $actions['delete_mb_invoice'] = sprintf(
            __('Moneybird %s delete', 'woocommerce_moneybird'),
            __('invoice', 'woocommerce_moneybird')
        );
        if ($this->settings['estimate_enabled'] == 'yes') {
            $actions['generate_mb_estimate'] = sprintf(
                __('Moneybird %s generate', 'woocommerce_moneybird'),
                __('estimate', 'woocommerce_moneybird')
            );
            $actions['unlink_mb_estimate'] = sprintf(
                __('Moneybird %s unlink', 'woocommerce_moneybird'),
                __('estimate', 'woocommerce_moneybird')
            );
        }

        return $actions;
    }

    function handle_bulk_actions($redirect_to, $action, $ids) {
        if (empty($ids)) {
            return $redirect_to;
        }
        if ($action == 'generate_mb_invoice') {
            if (count($ids) == 1) {
                $this->generate_invoice(wc_get_order(array_pop($ids)));
            } else {
                $this->add_to_queue('generate', $ids);
                $this->add_admin_notice(sprintf(
                        __('Moneybird %s will be generated for %d orders (3 per minute).', 'woocommerce_moneybird'),
                        __('invoices', 'woocommerce_moneybird'),
                        count($ids)));
            }
        } if ($action == 'generate_mb_invoice_combined') {
            if (count($ids) == 1) {
                $this->generate_invoice(wc_get_order(array_pop($ids)));
            } else {
                $this->generate_combined_invoice($ids);
            }
        } elseif ($action == 'generate_mb_estimate') {
            if (count($ids) == 1) {
                $this->generate_mb_document(wc_get_order(array_pop($ids)), 'estimate');
            } else {
                $this->add_to_queue('generate_estimate', $ids);
                $this->add_admin_notice(sprintf(
                    __('Moneybird %s will be generated for %d orders (3 per minute).', 'woocommerce_moneybird'),
                    __('estimates', 'woocommerce_moneybird'),
                    count($ids)));
            }
        } elseif ($action == 'download_mb_invoices') {
            $invoice_ids = array();
            foreach ($ids as $order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $invoice_id = $order->get_meta('moneybird_invoice_id', true);
                    if ($invoice_id) {
                        $invoice_ids[] = $invoice_id;
                    }
                }
            }
            if ($invoice_ids && $this->settings['administration_id']) {
                return sprintf(
                    'https://moneybird.com/%s/sales_invoices/download_sales_invoices/%s',
                    $this->settings['administration_id'],
                    implode(',', $invoice_ids)
                );
            }
        } elseif ($action == 'unlink_mb_invoice') {
            foreach ($ids as $order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    if ($order->get_meta('moneybird_invoice_id', true)) {
                        $order->delete_meta_data('moneybird_invoice_id');
                        $order->save_meta_data();
                    }
                }
            }
            $this->add_admin_notice(sprintf(
                __('Moneybird %s have been unlinked from the selected orders.', 'woocommerce_moneybird'),
                __('invoices', 'woocommerce_moneybird')));
        } elseif ($action == 'unlink_mb_estimate') {
            foreach ($ids as $order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    if ($order->get_meta('moneybird_estimate_id', true)) {
                        $order->delete_meta_data('moneybird_estimate_id');
                        $order->save_meta_data();
                    }
                }
            }
            $this->add_admin_notice(sprintf(
                __('Moneybird %s have been unlinked from the selected orders.', 'woocommerce_moneybird'),
                __('estimates', 'woocommerce_moneybird')));
        } elseif ($action == 'delete_mb_invoice') {
            $this->add_to_queue('delete', $ids);
            $this->add_admin_notice(sprintf(__('Moneybird invoices will be deleted for %d orders (max. 15 invoices per minute).', 'woocommerce_moneybird'), count($ids)));
        }

        return $redirect_to;
    }

    function add_to_queue($queue, $order_ids, $handler_delay=5, $scheduled_time=false) {
        // Add array of post ids to the invoice generation queue
        if (!in_array($queue, array('generate', 'delete', 'generate_estimate'))) {
            return;
        }
        if (empty($scheduled_time)) {
            $scheduled_time = time() - 1;
        }
        $added_order_ids = array();
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                if (empty($order->get_meta('moneybird_queue_'.$queue, true))) {
                    $order->update_meta_data('moneybird_queue_'.$queue, $scheduled_time);
                    $order->save_meta_data();
                    $added_order_ids[] = $order->get_id();
                }
            }
        }

        $this->log('Added orders to ' . $queue . ' queue: ' . implode(", ", $added_order_ids) . '.');

        // Maybe schedule queue handler
        if ($scheduled_time <= time()) {
            if (wp_next_scheduled('wc_mb_handle_invoice_queue') === false) {
                wp_schedule_single_event(time() + $handler_delay, 'wc_mb_handle_invoice_queue');
                $this->log('Scheduled queue handler.');
            }
        }
    }

    function handle_invoice_queue() {
        $request_budget = 15; // API request budget per call

        if (!$this->load_api_connector()) {
            return;
        }

        // Handle invoice generation tasks
        $invoice_queue = self::get_queued_items('generate', '0,3'); // Maybe get a couple of items from the generation queue
        if (!empty($invoice_queue)) {
            $this->log('Invoice generation queue handler invoked.');

            // Schedule next call if not yet scheduled
            if (wp_next_scheduled('wc_mb_handle_invoice_queue') === false) {
                wp_schedule_single_event(time() + 60, 'wc_mb_handle_invoice_queue');
            }

            // Process up to 3 items from the queue
            while ((count($invoice_queue) > 0) && ($request_budget > 0)) {
                $order_id = array_shift($invoice_queue);
                if ($order_id) {
                    $this->generate_invoice_without_notices($order_id);
                    $request_budget -= 5;
                }
            }
        }

        // Handle estimate generation tasks
        $estimate_queue = self::get_queued_items('generate_estimate', '0,3'); // Maybe get a couple of items from the generation queue
        if (!empty($estimate_queue)) {
            $this->log('Estimate generation queue handler invoked.');

            // Schedule next call if not yet scheduled
            if (wp_next_scheduled('wc_mb_handle_invoice_queue') === false) {
                wp_schedule_single_event(time() + 60, 'wc_mb_handle_invoice_queue');
            }

            // Process up to 3 items from the queue
            while ((count($estimate_queue) > 0) && ($request_budget > 0)) {
                $order_id = array_shift($estimate_queue);
                if ($order_id) {
                    $this->generate_mb_document_without_notices($order_id, 'estimate');
                    $request_budget -= 4;
                }
            }
        }

        // Handle invoice deletion tasks
        $invoice_queue = self::get_queued_items('delete', '0,15'); // Maybe get a couple of items from the deletion queue
        if ($invoice_queue) {
            $this->log('Invoice deletion queue handler invoked.');

            // Schedule next call if not yet scheduled
            if (wp_next_scheduled('wc_mb_handle_invoice_queue') === false) {
                wp_schedule_single_event(time() + 60, 'wc_mb_handle_invoice_queue');
            }

            // Process items
            while ((count($invoice_queue) > 0) && ($request_budget > 0)) {
                $order_id = array_shift($invoice_queue);
                if ($order_id) {
                    $order = wc_get_order($order_id);
                    if ($order) {
                        $invoice_id = $order->get_meta('moneybird_invoice_id', true);
                        if ($invoice_id) {
                            $this->log('Delete invoice '.$invoice_id.' linked to order '.$order_id.'.');
                            $this->mb_api->deleteSalesInvoice($invoice_id);
                            $request_budget -= 1;
                            $order->delete_meta_data('moneybird_invoice_id');
                            $order->delete_meta_data('moneybird_queue_delete');
                            $order->save_meta_data();
                        }
                    }
                }
            }
        }
    }

    function get_document_styles($force_refresh=false) {
        // Load the Moneybird document styles
        // Returns: Array [document style id] => [document style name]

        // Maybe load transient
        if (!$force_refresh) {
            $document_styles = get_transient('moneybird2_document_styles');
            if (!empty($document_styles) && is_array($document_styles)) {
                return $document_styles;
            }
        }

        $default = array('' => __('Default', 'woocommerce_moneybird'));
        if (!$this->load_api_connector()) { return $default; }
        $results = $this->mb_api->getDocumentStyles();
        if (!$results) {
            return $default;
        } else {
            $document_styles = array();
            foreach ($results as $result) {
                $document_styles[$result->id] = $result->name;
            }
            if (empty($document_styles)) {
                return $default;
            }
            set_transient('moneybird2_document_styles', $document_styles, 24*60*60);
            return $document_styles;
        }
    }

    function get_workflows($force_refresh=false, $document_type='invoice') {
        // Load the Moneybird document styles
        // $document_type can be 'invoice' or 'estimate'.
        // Returns: Array [workflow id] => [workflow name]

        if (($document_type != 'invoice') && ($document_type != 'estimate')) {
            $document_type = 'invoice';
        }

        // Maybe load transient
        if (!$force_refresh) {
            $workflows = get_transient('moneybird2_workflows');
            if (($workflows !== false) && is_array($workflows) && isset($workflows[$document_type])) {
                return $workflows[$document_type];
            }
        }

        // Query the Moneybird API
        $workflows = array(
                'invoice'   => array('auto' => __('Default (let Moneybird decide)', 'woocommerce_moneybird')),
                'estimate'  => array('auto' => __('Default (let Moneybird decide)', 'woocommerce_moneybird'))
        );
        if ($this->load_api_connector()) {
            $results = $this->mb_api->getWorkflows();
            if ($results) {
                foreach ($results as $result) {
                    if ($result->type == 'InvoiceWorkflow') {
                        $workflows['invoice'][$result->id] = $result->name;
                    } elseif ($result->type == 'EstimateWorkflow') {
                        $workflows['estimate'][$result->id] = $result->name;
                    }
                }
            }
        }

        // Update transient if we have a non-empty list
        if ((count($workflows['invoice']) > 1) || (count($workflows['estimate']) > 1)) {
            set_transient('moneybird2_workflows', $workflows, 24*60*60);
        }

        return $workflows[$document_type];
    }


    function get_projects($force_refresh=false) {
        // Load the Moneybird document styles
        // Returns: Array [project id] => [project name]
        // The key (project id) is prepended by "s" to prevent php from parsing it as an integer (which causes problems on some machines).

        // Maybe load transient
        if (!$force_refresh) {
            $projects = get_transient('moneybird2_projects');
            if ($projects !== false) {
                return $projects;
            }
        }

        // Query the Moneybird API
        $projects = array();
        if ($this->load_api_connector()) {
            $results = $this->mb_api->getProjects();
            if ($results) {
                foreach ($results as $result) {
                    $projects['s'.$result->id] = $result->name;
                }
            }
        }
        if (!empty($projects)) {
            set_transient('moneybird2_projects', $projects, 24*60*60);
        }

        return $projects;
    }


    function get_mb_tax_rates($force_refresh=false) {
        // Get the Moneybird tax rates
        // Returns: array indexed by (string)[mb_tax_rate_id]

        if (!$force_refresh) {
            $mb_tax_rates = get_transient('moneybird2_mb_tax_rates');
            if ($mb_tax_rates !== false) {
                return $mb_tax_rates;
            }
        }

        // Query the Moneybird API
        $mb_tax_rates = array(); // [mb_tax_rate_id] => [name]
        if (!$this->load_api_connector()) { return $mb_tax_rates; }
        $results = $this->mb_api->getTaxRates();
        if ($results) {
            foreach ($results as $result) {
                if (($result->tax_rate_type == 'sales_invoice') && $result->active) {
                    $mb_tax_rates[$result->id] = $result;
                }
            }
            set_transient('moneybird2_mb_tax_rates', $mb_tax_rates, 24*60*60);
        }

        return $mb_tax_rates;
    }

    function get_all_wc_tax_rate_ids() {
        // Get the ids of all WooCommerce tax rates
        global $wpdb;

        $query = "
            SELECT *
            FROM {$wpdb->prefix}woocommerce_tax_rates
            ORDER BY tax_rate_order
        ";

        return $wpdb->get_results($query);
    }

    function get_tax_rate_mappings() {
        // Get mappings from WooCommerce tax rate to Moneybird tax rate
        // Returns: Array (int)[wc_tax_rate_id] => (string)[mb_tax_rate_id]

        $mb_tax_rates = $this->get_mb_tax_rates();
        $tax_rate_mappings = array();

        foreach ($this->get_all_wc_tax_rate_ids() as $wc_tax_rate) {
            $wc_tax_rate_id = $wc_tax_rate->tax_rate_id;

            if (isset($this->settings['tax_rate_'.$wc_tax_rate_id]) && $this->settings['tax_rate_'.$wc_tax_rate_id]) {
                $saved_mapping = $this->settings['tax_rate_'.$wc_tax_rate_id];
                if (isset($mb_tax_rates[$saved_mapping])) {
                    $tax_rate_mappings[(int)$wc_tax_rate_id] = $saved_mapping;
                }
            }

            if (!isset($tax_rate_mappings[(int)$wc_tax_rate_id]) || ($tax_rate_mappings[(int)$wc_tax_rate_id]=='')) {
                // Try to make an educated guess based on percentage matching.
                $wc_tax_percent = round(floatval($wc_tax_rate->tax_rate), 1);
                foreach ($mb_tax_rates as $mb_tax_rate) {
                    $mb_tax_percent = round((float)$mb_tax_rate->percentage, 1);
                    if ($wc_tax_percent == $mb_tax_percent) {
                        $tax_rate_mappings[(int)$wc_tax_rate_id] = $mb_tax_rate->id;
                        break;
                    }
                }
            }

            if (!isset($tax_rate_mappings[(int)$wc_tax_rate_id])) {
                // No saved setting and could not guess a mapping.
                $tax_rate_mappings[(int)$wc_tax_rate_id] = '';
            }
        }

        return $tax_rate_mappings;
    }

    function get_wc_tax_rate_id_by_rate_code($code) {
        // Try to find the rate_id corresponding to tax rate code $code
        $wc_tax = new WC_Tax();
        foreach ($this->get_all_wc_tax_rate_ids() as $wc_tax_rate) {
            $candidate_code = $wc_tax->get_rate_code($wc_tax_rate->tax_rate_id);
            if ($candidate_code == $code) {
                return $wc_tax_rate->tax_rate_id;
            }
        }
        return false;
    }

    function get_mapped_mb_tax_rate_id($tax_item_id, $tax_rate_mappings=null, $mb_tax_rates=null, $pct_check=false) {
        // Return id of Moneybird tax rate id mapped to the specified tax_item_id.
        // If $pct_check contains a percentage, the mapped Moneybird tax rate is required
        // to deviate at most 5% from the specified percentage.
        if (empty($tax_rate_mappings)) {
            $tax_rate_mappings = $this->get_tax_rate_mappings();
        }
        if (empty($mb_tax_rates)) {
            $mb_tax_rates = $this->get_mb_tax_rates();
        }
        $mb_tax_rate_id = false;
        if (isset($tax_rate_mappings[(int)$tax_item_id])) {
            if (isset($mb_tax_rates[$tax_rate_mappings[(int)$tax_item_id]])) {
                $mb_tax_rate_id = $tax_rate_mappings[(int)$tax_item_id];
            }
        }
        if ($mb_tax_rate_id && is_numeric($pct_check)) {
            $pct_check = floatval($pct_check);
            if (empty($mb_tax_rates)) {
                $mb_tax_rates = $this->get_mb_tax_rates();
            }
            if (abs($mb_tax_rates[$mb_tax_rate_id]->percentage - $pct_check) > 5.0) {
                $mb_tax_rate_id = false;
            }
        }

        return $mb_tax_rate_id;
    }


    function get_custom_field_mappings() {
        if (!isset($this->settings['serialized_custom_field_mappings'])) {
            return array();
        }
        if (empty($this->settings['serialized_custom_field_mappings'])) {
            return array();
        }

        return $this->unserialize_custom_field_mappings($this->settings['serialized_custom_field_mappings']);
    }

    function get_custom_field_value($order, $key, $secondary_orders=array()) {
        // Get the value of a mapped custom field
        global $woocommerce;
        if (!is_string($key) || empty($key)) {
            return false;
        }

        $val = false;

        // Special fields
        if ($key == 'order_id') {
            $val = strval($order->get_order_number());
        } elseif ($key == 'order_ids') {
            $val = strval($order->get_order_number());
            foreach ($secondary_orders as $secondary_order) {
                $val .= ', ' . $secondary_order->get_order_number();
            }
        } elseif ($key == 'shipping_method_title') {
            $val = $order->get_shipping_method();
        } elseif ($key == 'customer_order_note') {
            $val = $order->get_customer_note();
        } elseif ($key == 'first_product_name') {
            foreach ($order->get_items(apply_filters('woocommerce_admin_order_item_types', 'line_item')) as $item) {
                $val = str_replace('&#8211;', '-', apply_filters('woocommerce_order_item_name', $item['name'], $item, true));
                if (!empty($val)) {
                    break;
                }
            }
        } elseif ($key == 'product_skus') {
            $skus = array();
            foreach ($order->get_items(apply_filters('woocommerce_admin_order_item_types', 'line_item')) as $item) {
                if (version_compare($woocommerce->version, '4.4', '<')) {
                    $product = $order->get_product_from_item($item);
                } else {
                    $product = $item->get_product();
                }
                $skus[] = $product->get_sku();
            }
            $val = implode(', ', $skus);
        } elseif ($key == 'bol_order_id') {
            $val = $order->get_meta('bol_order_id', true);
            if (empty($val)) {
                $val = $order->get_meta('_bol_orderid', true);
            }
            if (empty($val)) {
                $val = $order->get_meta('_bol_order_id', true);
            }
            if (empty($val)) {
                foreach ($order->get_items('shipping') as $shipping_line) {
                    $title = $shipping_line->get_method_title();
                    if (is_string($title) && (stripos($title, 'bol.com')!==false)) {
                        if (preg_match('/\([0-9]+\)/', $title, $matches) === 1) {
                            $val = substr($matches[0], 1, strlen($matches[0]) - 2);
                        }
                    }
                }
            }
        } elseif ($key == 'amazon_order_id') {
            $val = $order->get_meta($key, true);
            if (empty($val)) {
                foreach ($order->get_items('shipping') as $shipping_line) {
                    $title = $shipping_line->get_method_title();
                    if (is_string($title) && (stripos($title, 'amazon')!==false)) {
                        if (preg_match('/\([\-0-9]+\)/', $title, $matches) === 1) {
                            $val = substr($matches[0], 1, strlen($matches[0]) - 2);
                        }
                    }
                }
            }
        } elseif ($key == 'subscription_next_payment_date') {
            // Get next renewal date of WooCommerce Subscriptions subscription
            if (function_exists('wcs_get_subscriptions_for_order')) {
                $subscriptions = wcs_get_subscriptions_for_order($order);
                if (!$subscriptions) {
                    $subscriptions = wcs_get_subscriptions_for_renewal_order($order);
                }
                if ($subscriptions) {
                    foreach ($subscriptions as $subscription) {
                        $next_payment_timestamp = $subscription->get_time('next_payment');
                        if ($next_payment_timestamp) {
                            $val = date('d-m-Y', $next_payment_timestamp);
                            break;
                        }
                    }
                }
            }
        } else {
            if (strpos($key, '_') === 0) {
                $cleaned_key = substr($key, 1);
            } else {
                $cleaned_key = $key;
            }
            $try_get_post_meta = true;
            // First try getter: $order->get_$cleaned_key()
            if (method_exists($order, 'get_'.$cleaned_key)) {
                $try_get_post_meta = false; // We can use the getter, prevent warning
                $val = $order->{'get_'.$cleaned_key}();
                if (is_numeric($val)) {
                    $val = strval($val);
                }
                if (empty($val) || !is_string($val)) {
                    $val = false;
                }
            }
            // Try $order->get_meta($cleaned_key)
            if (!$val) {
                $val = $order->get_meta($cleaned_key, true);
                if (empty($val) || !is_string($val)) {
                    $val = false;
                }
            }
            // Try $order->get_meta('_'.$cleaned_key)
            if (!$val) {
                $val = $order->get_meta('_'.$cleaned_key, true);
                if (empty($val) || !is_string($val)) {
                    $val = false;
                }
            }
            // Try get_post_meta
            if (!$val && $try_get_post_meta) {
                $val = get_post_meta($order->get_id(), $key, true);
                if (empty($val) || !is_string($val)) {
                    $val = false;
                }
            }
        }

        if (is_string($val)) {
            // In case of a date in format YYYY-MM-DD, convert to DD-MM-YYYY
            if (preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/', $val)) {
                $val = preg_replace('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', '$3-$2-$1', $val);
            }
        }

        $val = apply_filters('woocommerce_moneybird_custom_field_value', $val, $key, $order->get_id());

        if (!is_string($val)) {
            return false;
        } else {
            return $val;
        }
    }

    function unserialize_custom_field_mappings($serialized_mappings) {
        // Unserialize custom field mappings, remove mappings to non-existent fields
        if ($serialized_mappings && is_string($serialized_mappings)) {
            $mappings = unserialize($serialized_mappings);
        } elseif (is_array($serialized_mappings)) {
            $mappings = $serialized_mappings;
        } else {
            $mappings = array();
        }

        $filtered_mappings = array();
        if ($mappings) {
            $mb_field_choices = $this->get_mb_custom_fields();
            foreach ($mappings as $mapping) {
                if (isset($mapping['mb']) && key_exists($mapping['mb'], $mb_field_choices)) {
                    $filtered_mappings[] = $mapping;
                }
            }
        }

        return $filtered_mappings;
    }


    function get_order_contact_details($order) {
        // Try to determine the tax number from the order.
        $tax_number = '';
        foreach (array('getter', 'get_meta', 'get_meta_underscore', 'post_meta', 'post_meta_underscore', 'attribute') as $method) {
            foreach (array('vat_number', 'billing_vat_number', 'billing_eu_vat_number', 'VAT Number') as $field) {
                if ($method == 'getter') {
                    $getter = 'get_' . $field;
                    if (method_exists($order, $getter)) {
                        $tax_number = $order->{$getter}();
                    }
                } elseif ($method == 'get_meta') {
                    $tax_number = $order->get_meta($field, true);
                } elseif ($method == 'get_meta_underscore') {
                    $tax_number = $order->get_meta('_' . $field, true);
                } elseif ($method == 'post_meta') {
                    $tax_number = get_post_meta($order->get_id(), $field, true);
                } elseif ($method == 'post_meta_underscore') {
                    $tax_number = get_post_meta($order->get_id(), '_' . $field, true);
                } elseif ($method == 'attribute') {
                    if (isset($order->{$field})) {
                        $tax_number = $order->{$field};
                    } elseif (isset($order->{'_' . $field})) {
                        $tax_number = $order->{'_' . $field};
                    }
                }
                if (!empty($tax_number)) {
                    break;
                }
            }
            if (!empty($tax_number)) {
                break;
            }
        }

        $contact_details = array(
            'company_name' => trim($order->get_billing_company()),
            'firstname'    => trim($order->get_billing_first_name()),
            'lastname'     => trim($order->get_billing_last_name()),
            'address1'     => trim($order->get_billing_address_1()),
            'address2'     => trim($order->get_billing_address_2()),
            'zipcode'      => trim($order->get_billing_postcode()),
            'city'         => trim($order->get_billing_city()),
            'country'      => trim($order->get_billing_country()),
            'email'        => trim($order->get_billing_email()),
            'phone'        => trim($order->get_billing_phone()),
            'tax_number'   => $tax_number
        );

        // Maybe append _billing_house_number and _billing_house_number_suffix to address1
        // This is needed for the official PostNL plugin, which stores the house number in these
        // meta fields. The house number is not stored in the billing address fields.
        if (!empty($contact_details['address1'])) {
            $house_number = $order->get_meta('_billing_house_number', true);
            if (!empty($house_number)) {
                $house_number_suffix = $order->get_meta('_billing_house_number_suffix', true);
                if (!empty($house_number_suffix)) {
                    $house_number .= '-' . $house_number_suffix;
                }
                // Only append house number if it's not already at the end of address1
                if (strtolower(substr($contact_details['address1'], -strlen($house_number))) !== strtolower($house_number)) {
                    $contact_details['address1'] = trim($contact_details['address1']) . ' ' . $house_number;
                }
            }
        }

        // Try to load billing details from user if the order fields are empty
        if (empty($contact_details['company_name'])
            && empty($contact_details['firstname'])
            && empty($contact_details['lastname'])) {

            $user_id = $order->get_user_id();
            if (empty($user_id)) {
                // Maybe use default user id
                if (isset($this->settings['empty_invoice_details_user_id'])) {
                    if (!empty($this->settings['empty_invoice_details_user_id'])) {
                        $default_user_id = $this->settings['empty_invoice_details_user_id'];
                        if (get_user_by('id', $default_user_id)) {
                            $user_id = $default_user_id;
                        }
                    }
                }
            }
            if ($user_id) {
                $contact_details['company_name']    = get_user_meta($user_id, 'billing_company', true);
                $contact_details['firstname']       = get_user_meta($user_id, 'billing_first_name', true);
                $contact_details['lastname']        = get_user_meta($user_id, 'billing_last_name', true);
                $contact_details['address1']        = get_user_meta($user_id, 'billing_address_1', true);
                $contact_details['address2']        = get_user_meta($user_id, 'billing_address_2', true);
                $contact_details['zipcode']         = get_user_meta($user_id, 'billing_postcode', true);
                $contact_details['city']            = get_user_meta($user_id, 'billing_city', true);
                $contact_details['country']         = get_user_meta($user_id, 'billing_country', true);
                $contact_details['email']           = get_user_meta($user_id, 'billing_email', true);
                $contact_details['phone']           = get_user_meta($user_id, 'billing_phone', true);
            }
        }

        // Extra fields
        $custom_fields = array();
        $custom_field_mappings = $this->get_custom_field_mappings();
        foreach ($custom_field_mappings as $mapping) {
            if (substr($mapping['mb'], 0, 1) == 'c') {
                $val = $this->get_custom_field_value($order, $mapping['wc']);
                if ($val) {
                    $custom_fields[] = array(
                        'id'    => substr($mapping['mb'], 1),
                        'value' => $val
                    );
                }
            }
        }
        if (!empty($custom_fields)) {
            $contact_details['custom_fields_attributes'] = $custom_fields;
        }

        return apply_filters('woocommerce_moneybird_contact_details', $contact_details, $order);
    }


    /** @noinspection PhpIllegalStringOffsetInspection */
    public function maybe_update_moneybird_contact($mb_contact, $contact_details, $reuse_policy) {
        // Update existing Moneybird contact if needed.
        // Returns the (updated) Moneybird contact or false in case of error.
        $updatable = apply_filters('woocommerce_moneybird_contact_updatable', true, $mb_contact, $contact_details);
        if (!$updatable) {
            return $mb_contact;
        }
        $contact_updates = array();
        $contact_person = array();

        // Tax number can always be set/updated
        if (!empty($contact_details['tax_number'])) {
            if (trim(strtolower($contact_details['tax_number'])) != trim(strtolower($mb_contact->tax_number))) {
                $contact_updates['tax_number'] = trim($contact_details['tax_number']);
            }
        }

        // Find fields that should be updated
        if (strpos($reuse_policy, 'update') !== false) {
            $mb = (array)$mb_contact;
            foreach ($contact_details as $field => $val) {
                if (($field == 'country') && empty($val)) {
                    $val = 'NL'; // Country is automatically set by Moneybird
                }
                if (in_array($field, array('firstname', 'lastname'))) {
                    if (!empty($mb[$field])) {
                        if ($mb[$field] != $val) {
                            $contact_updates[$field] = $val;
                        }
                    } elseif (!empty($val)) {
                        $contact_person[$field] = $val;
                    }
                    continue;
                }
                if (isset($mb[$field])) {
                    if ($mb[$field] != $val) {
                        $contact_updates[$field] = $val;
                    }
                } elseif ($field == 'custom_fields_attributes') {
                    foreach ($val as $custom_field) {
                        $update_required = true;
                        foreach ($mb['custom_fields'] as $existing_custom_field) {
                            if ($existing_custom_field->id == $custom_field['id']) {
                                if ($existing_custom_field->value == $custom_field['value']) {
                                    $update_required = false;
                                    break;
                                }
                            }
                        }
                        if ($update_required) {
                            if (!isset($contact_updates['custom_fields_attributes'])) {
                                $contact_updates['custom_fields_attributes'] = array();
                            }
                            $contact_updates['custom_fields_attributes'][] = $custom_field;
                        }
                    }
                }
            }

            // Make sure required fields are filled
            $name_set_after_update = false;
            foreach (array('firstname', 'lastname', 'company_name') as $field) {
                $val = null;
                if (isset($contact_updates[$field])) {
                    $val = $contact_updates[$field];
                } else {
                    $val = $mb[$field];
                }
                if (!empty($val)) {
                    $name_set_after_update = true;
                    break;
                }
            }
            if (!$name_set_after_update) {
                $contact_updates['firstname'] = $contact_details['firstname'];
                $contact_updates['lastname'] = $contact_details['lastname'];
                $contact_updates['company_name'] = $contact_details['company_name'];
            }
        }

        // Maybe create contact person
        if (!empty($contact_person)) {
            $contact_person_exists = false;
            foreach (array('firstname', 'lastname') as $field) {
                if (!isset($contact_person[$field])) {
                    $contact_person[$field] = '';
                }
            }
            foreach ($mb_contact->contact_people as $mb_contact_person) {
                if ($mb_contact_person->firstname == $contact_person['firstname']) {
                    if ($mb_contact_person->lastname == $contact_person['lastname']) {
                        $contact_person_exists = true;
                        break;
                    }
                }
            }
            if (!$contact_person_exists) {
                $this->log('Create contact person on Moneybird contact ' . $mb_contact->id . ': ' . print_r($contact_person, true));
                $new_contact_person = $this->mb_api->createContactPerson($mb_contact->id, $contact_person);
                if (!$new_contact_person) {
                    $last_api_error = $this->mb_api->getLastError();
                    if (is_string($last_api_error)) {
                        $this->log("Contact person could not be created: " . $last_api_error);
                    } else {
                        $this->log("Contact person could not be created.");
                    }
                    return false;
                }
                $mb_contact->contact_people[] = $new_contact_person;
            }
        }

        // Maybe submit contact updates
        if (count($contact_updates) > 0) {
            $this->log('Update Moneybird contact ' . $mb_contact->id . ': ' . print_r($contact_updates, true));
            $updated_contact = $this->mb_api->updateContact($mb_contact->id, $contact_updates);
            if (!$updated_contact) {
                $last_api_error = $this->mb_api->getLastError();
                if (is_string($last_api_error)) {
                    $this->log("Contact update could not be performed: " . $last_api_error);
                } else {
                    $this->log("Contact update could not be performed.");
                }
                return false;
            }
            return $updated_contact;
        }

        return $mb_contact;
    }


    public function get_or_create_moneybird_contact($order) {
        // Find Moneybird contact that matches the billing contact details in $order.
        // If no match is found, create a new Moneybird contact.
        // Returns the Moneybird contact object or false in case of failure.

        if (!$this->load_api_connector()) { return false; }

        $contact_details = $this->get_order_contact_details($order);
        $contact_details = apply_filters('woocommerce_moneybird_new_contact', $contact_details, $order);
        $reuse_contact = null;

        // Check for explicitly linked Moneybird contact
        $user_id = $order->get_user_id();
        if ($user_id) {
            $moneybird_customer_id = get_user_meta($user_id, 'moneybird_customer_id', true);
            if ($moneybird_customer_id) {
                $reuse_contact = $this->mb_api->getContactByCustomerId($moneybird_customer_id);
                if (!$reuse_contact) {
                    $this->log("Could not find explicitly linked Moneybird contact with customer ID " . $moneybird_customer_id . " for user " . $user_id);
                }
                if (!apply_filters('woocommerce_moneybird_use_mb_contact', true, $reuse_contact, $contact_details, $order)) {
                    $reuse_contact = null;
                }
            }
        }

        // Look for existing Moneybird contact to reuse
        $reuse_policy = 'all';
        $contact_signature = $this->get_contact_signatures($contact_details)[0];
        if (empty($reuse_contact)) {
            if (!empty($this->settings['contact_reuse_policy'])) {
                $reuse_policy = $this->settings['contact_reuse_policy'];
            }

            $query = '';
            if (trim($contact_details['email'])) {
                $query = trim($contact_details['email']);
            } elseif (trim($contact_details['company_name'])) {
                $query = trim($contact_details['company_name']);
            } else {
                $query = trim($contact_details['firstname']) . ' ' . trim($contact_details['lastname']);
            }

            $query = apply_filters('woocommerce_moneybird_contact_query', $query, $contact_details, $order);

            if ($query) {
                $candidates = $this->mb_api->getContactsByQuery($query);
            } else {
                $candidates = false;
            }

            if ($candidates && is_array($candidates)) {
                foreach ($candidates as $candidate) {
                    $use_mb_contact = false;
                    if (strpos($reuse_policy, 'email') !== false) {
                        $reuse_contact = null;
                        if (property_exists($candidate, 'email')) {
                            if (trim($candidate->email) == trim($contact_details['email'])) {
                                $use_mb_contact = true;
                            }
                        }
                    } else { // reuse_policy 'all'
                        // Reuse if candidate_signature starts with contact_signature
                        foreach ($this->get_contact_signatures((array)$candidate) as $candidate_signature) {
                            //$this->log('Check existing contact: ' . $candidate_signature . ' starts with ' . $contact_signature . '?');
                            if (strpos($candidate_signature, $contact_signature) === 0) {
                                $use_mb_contact = true;
                            }
                        }
                    }
                    $use_mb_contact = apply_filters('woocommerce_moneybird_use_mb_contact', $use_mb_contact, $candidate, $contact_details, $order);
                    if ($use_mb_contact) {
                        $reuse_contact = $candidate;
                        break;
                    }
                }
            }
        }

        if ($reuse_contact) {
            $this->log('Reusing existing Moneybird contact '.$reuse_contact->id.' for ' . $contact_details['firstname'] . ' ' . $contact_details['lastname']);
            return $this->maybe_update_moneybird_contact($reuse_contact, $contact_details, $reuse_policy);
        }

        $this->log('No existing Moneybird contact could be found with the required signature: ' . $contact_signature);

        // Create new contact
        $this->log("Submitting contact object: \n" . print_r($contact_details, true));
        $mb_contact = $this->mb_api->createContact($contact_details);
        if (!$mb_contact) {
            $last_api_error = $this->mb_api->getLastError();
            if (is_string($last_api_error) && (strpos($last_api_error, 'unexpected http response code: 422') !== false)) {
                // Try to create contact without email address
                $this->log("Could not create contact, trying again without email address now.");
                $contact_details['email'] = '';
                $mb_contact = $this->mb_api->createContact($contact_details);
            }
        }
        if (!$mb_contact) {
            return false;
        }
        $this->log('Created Moneybird contact for ' . $contact_details['firstname'] . ' ' . $contact_details['lastname']);
        return $mb_contact;
    }

    public function get_contact_signatures($contact) {
        // Generate an array of string signatures for a contact array.
        // These signatures are used to determine equivalence of contact details.
        // A contact can have multiple signatures if it has multiple contact people.
        $sigs = array();
        $sig_fields = array('company_name', 'firstname', 'lastname', 'address1', 'address2', 'zipcode', 'city',
                            'country', 'email', 'phone');
        if (isset($contact['contact_people']) && !empty($contact['contact_people'])) {
            foreach ($contact['contact_people'] as $contact_person) {
                $contact_person = (array)$contact_person;
                $sig = '';
                foreach ($sig_fields as $field) {
                    // Take firstname and lastname of contact person
                    if (($field == 'firstname') || ($field == 'lastname')) {
                        if (isset($contact_person[$field])) {
                            $sig .= (string) $contact_person[$field];
                        }
                    } elseif (isset($contact[$field]) && !empty($contact[$field])) {
                        $sig .= (string) $contact[$field];
                    } elseif ($field == 'country') {
                        // Moneybird automatically sets country to 'nl' if not specified
                        $sig .= 'nl';
                    }
                }
                $sigs[] = strtolower(str_replace(array(' ', "\n", '-', '(', ')'), '', $sig));
            }
        } else {
            $sig = '';
            foreach ($sig_fields as $field) {
                if (isset($contact[$field]) && !empty($contact[$field])) {
                    $sig .= (string) $contact[$field];
                } elseif ($field == 'country') {
                    // Moneybird automatically sets country to 'nl' if not specified
                    $sig .= 'nl';
                }
            }
            $sigs[] = strtolower(str_replace(array(' ', "\n", '-', '(', ')'), '', $sig));
        }

        return $sigs;
    }

    function test_api_connection() {
        // Test the connection with the Moneybird API
        // Returns: name of the Moneybird administration or false.
        if ($this->load_api_connector()) {
            if ($administrations = $this->mb_api->getAdministrations()) {
                foreach ($administrations as $administration) {
                    if ($this->settings['administration_id'] == $administration->id) {
                        return $administration->name;
                    }
                }
            }
        }

        return false;
    }

    function generate_invoice_without_notices($order_id) {
        $this->generate_mb_document_without_notices($order_id, 'invoice');
    }

    function generate_mb_document_without_notices($order_id, $doctype) {
        // 'Silent' Moneybird document generation, used in order status update handlers and queue handlers
        $order = wc_get_order($order_id);
        if ($order) {
            $this->generate_mb_document($order, $doctype);
            delete_option('woocommerce_moneybird2_deferred_admin_notices');
        }
    }

    function generate_credit_invoice_without_notices($order_id, $refund_id) {
        // 'Silent' credit invoice generation
        $order = wc_get_order($refund_id);
        if ($order) {
            // Only create credit invoice if the parent order has an invoice
            $parent_order_id = $order->get_parent_id();
            if ($parent_order_id) {
                $parent_order = wc_get_order($parent_order_id);
                if ($parent_order) {
                    if (!empty($parent_order->get_meta('moneybird_invoice_id', true))) {
                        $this->generate_invoice($order);
                        delete_option('woocommerce_moneybird2_deferred_admin_notices');
                    }
                }
            }
        }
    }

    function check_invoice_trigger($order) {
        // Check if we should trigger automatic invoice generation for the specified order.
        // Returns true or false.
        $status = is_callable(array($order, 'get_status')) ? $order->get_status() : $order->status;
        $triggers = array_keys($this->get_order_statuses());
        if (in_array($status, $triggers)) {
            if (isset($this->settings['auto_invoice_trigger_'.$status])) {
                if ($this->settings['auto_invoice_trigger_'.$status] == 'yes') {
                    if ($this->settings['auto_invoice_trigger_'.$status.'_p-all'] == 'yes') {
                        // Invoicing for this order status is enabled for all payment methods
                        return true;
                    } else {
                        if (is_callable(array($order, 'get_payment_method'))) {
                            $gateway_code = $order->get_payment_method();
                        } else {
                            $gateway_code = $order->payment_method;
                        }
                        $pmf_field = 'auto_invoice_trigger_'.$status.'_p-'.$gateway_code;
                        if (isset($this->settings[$pmf_field]) && ($this->settings[$pmf_field]=='yes')) {
                            // Invoicing for this order status is enabled for the used payment method
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    function maybe_generate_invoice_without_notices($order_id, $order=null) {
        // 'Silent' invoice generation, used for automatic invoice generation upon order status change.
        if (empty($order)) {
            $order = wc_get_order($order_id);
        }
        if ($order) {
            $mb_doc_id = $order->get_meta("moneybird_invoice_id", true);
            if (!empty($mb_doc_id)) {
                if ($this->document_id_valid_for_order($order_id, $mb_doc_id, 'invoice')) {
                    return; // Invoice already exists
                }
            }
            if ($this->check_invoice_trigger($order)) {
                $this->generate_invoice($order);
                delete_option('woocommerce_moneybird2_deferred_admin_notices');
            }
        }
    }

    function maybe_generate_wcs_renewal_invoice($order) {
        // 'Silent' invoice generation for WooCommerce Subscriptions renewal orders.
        $mb_doc_id = $order->get_meta("moneybird_invoice_id", true);
        if (!empty($mb_doc_id)) {
            if ($this->document_id_valid_for_order($order->get_id(), $mb_doc_id, 'invoice')) {
                return $order; // Invoice already exists
            }
        }
        if ($this->check_invoice_trigger($order)) {
            $this->generate_invoice($order);
            delete_option('woocommerce_moneybird2_deferred_admin_notices');
        }
        return $order;
    }

    function get_subscription_payment_periods($order) {
        // Return array indicating the subscription period that
        // this order covers for each item.
        // Return format: array([item_id] => [period string])
        $product_payment_periods = array();
        if (!function_exists('wcs_get_subscriptions_for_order') ||
            !function_exists('wcs_get_subscriptions_for_renewal_order')) {
            // WooCommerce Subscriptions not installed or active
            return $product_payment_periods;
        }

        $order_id = $order->get_id();
        $subscriptions = wcs_get_subscriptions_for_order($order_id);
        if (!$subscriptions) {
            $subscriptions = wcs_get_subscriptions_for_renewal_order($order_id);
        }

        if ($subscriptions) {
            foreach($subscriptions as $subscription) {
                $from_date = $order->get_date_created()->getTimestamp();
                $start_paid_period = $from_date;
                $trial_end = $subscription->get_date('trial_end');
                if ($trial_end) {
                    $trial_end = strtotime($trial_end);
                    if ($trial_end > $from_date) {
                        $start_paid_period = $trial_end;
                    }
                }
                $billing_period = $subscription->get_billing_period();
                if (in_array($billing_period, array('day', 'week', 'month', 'year'))) {
                    $until_date = strtotime("+1 ".$billing_period, $start_paid_period);
                }

                $period = date('Ymd', $from_date) . '..' . date('Ymd', $until_date);

                foreach($subscription->get_items() as $item) {
                    $product_payment_periods[$item->get_product_id()] = $period;
                }
            }
        }

        return $product_payment_periods;
    }

    function get_order_items($order, $tax_rate_mappings, $prices_include_tax=false) {
        // Return an array of order items, used by generate_invoice()
        global $woocommerce;
        $items = array();
        $order_is_refund = ($order->get_type() == 'shop_order_refund');
        $subscription_payment_periods = $this->get_subscription_payment_periods($order);

        foreach ($order->get_items(apply_filters('woocommerce_admin_order_item_types', 'line_item')) as $item) {
            if (version_compare($woocommerce->version, '4.4', '<')) {
                $_product = apply_filters('woocommerce_order_item_product', $order->get_product_from_item($item), $item);
            } else {
                $_product = apply_filters('woocommerce_order_item_product', $item->get_product(), $item);
            }

            // Check if product should be skipped
            $product_id = null;
            $parent_product_id = null;
            if ($_product) {
                $product_id = $_product->get_id();
                if (get_post_meta($product_id, '_mb_exclude', true) === 'yes') {
                    continue;
                }
                if (get_post_type($product_id) == 'product_variation') {
                    $parent_product_id = $_product->get_parent_id();
                    if (get_post_meta($parent_product_id, '_mb_exclude', true) === 'yes') {
                        continue;
                    }
                }
            }

            $item_line_total = $order->get_line_total($item, false, false);
            $item_qty = $item['qty'];
            $price_adj_factor = 1.0;
            if ($item_qty == 0) {
                // Item qty can be 0 for refunds in which the item quantity is left blank.
                // In those cases we set it to 1 to make sure the price and tax appear correctly on the invoice.
                $item_qty = 1;
            } elseif (($item_qty < 0) && $order_is_refund) {
                // Refund, check if refund amount is specified on item level, otherwise use parent item
                if (abs(round($item_line_total, 2)) == 0.0) {
                    if (!empty($item['refunded_item_id'])) {
                        // Use parent order item to calculate price and tax rate
                        if ($order->get_type() == 'shop_order_refund') {
                            $order = wc_get_order($order->get_parent_id());
                        }
                        $item = $order->get_item($item['refunded_item_id']);
                        $price_adj_factor = abs($item_qty / $item['qty']);
                        $item_line_total = $price_adj_factor * $order->get_line_total($item, false, false);
                    }
                }
            }


            $description = str_replace('&#8211;', '-', apply_filters('woocommerce_order_item_name', $item['name'], $item, true)); // replace ndash by dash to prevent encoding problems

            // Some configuration plugins put html with meta data in the item name
            // Try process any html content
            $description = str_ireplace('<li', "\n- <li", $description);
            $description = strip_tags($description);
            $description_lines = explode("\n", $description);
            if (is_array($description_lines) && (count($description_lines) > 1)) {
                $description = '';
                for ($i=0; $i<count($description_lines); $i++) {
                    if (strpos($description_lines[$i], '- :') !== false) {
                        continue;
                    }
                    $description .= $description_lines[$i] . "\n";
                }
                $description = trim($description);
            }

            if (isset($this->settings['product_info']) && (strpos($this->settings['product_info'], 'sku') !== false)) {
                if ($_product && $_product->get_sku()) {
                    $description .= ' (' . $_product->get_sku() . ')';
                }
            }

            if (isset($this->settings['product_info']) && (strpos($this->settings['product_info'], 'options') !== false)) {
                $meta_list = array();
                // Allow other plugins to add additional product information
                do_action('woocommerce_order_item_meta_start', $item->get_id(), $item, $order, false);
                foreach ($item->get_formatted_meta_data() as $meta) {
                    // Filter out backorder notices
                    if ($meta->display_key == __('Backordered', 'woocommerce')) {
                        continue;
                    }

                    $meta_value = trim($meta->display_value);

                    // Maybe filter out SKU codes
                    if (strpos($this->settings['product_info'], 'sku') === false) {
                        if (strpos($meta->display_key, 'SKU') !== false) {
                            continue;
                        }
                        $pos = strpos($meta_value, '(SKU:');
                        if ($pos) {
                            $meta_value = trim(substr($meta_value, 0, $pos));
                        }
                    }

                    // Format meta values with multiple lines
                    $meta_value = nl2br($meta_value);
                    $meta_value = str_ireplace(array('<br>', '<br />', '<BR/>'), array('<br/>', '<br/>', '<br/>'), $meta_value);
                    if (substr_count($meta_value, '<br/>') >= 1) {
                        $meta_value_parts = explode('<br/>', $meta_value);
                        $meta_value = '';
                        foreach ($meta_value_parts as $meta_value_part) {
                            $meta_value .= "\n   " . $meta_value_part;
                        }
                    }

                    // Extra formatting, strip html tags
                    $meta_value = str_ireplace('</div>', ' </div>', $meta_value);
                    $meta_value = wp_kses($meta_value, array());
                    while (strpos($meta_value, '  ') !== false) {
                        $meta_value = str_replace('  ', ' ', $meta_value);
                    }
                    $pos = stripos($meta_value, 'Action: Edit');
                    if ($pos) {
                        $meta_value = trim(substr($meta_value, 0, $pos));
                    }

                    // Add to $meta_list
                    $meta_list[] = '• ' . trim(wp_kses($meta->display_key, array())) . ': ' . trim($meta_value);
                }

                $output = (!empty($meta_list)) ? implode("\n", $meta_list) : '';
                $args = array(
                            'before'    => '• ',
                            'after'     => '',
                            'separator' => "\n",
                            'echo'      => false,
                            'autop'     => true);
                $meta_desc = apply_filters( 'woocommerce_display_item_meta', $output, $item, $args);

                if ($meta_desc) {
                    $description .= "\n" . $meta_desc;
                }
            }

            // Maybe add extra meta data
            if ($_product && strpos($this->settings['product_info'], 'meta') !== false) {
                ob_start();
                do_action( 'woocommerce_before_order_itemmeta', $item->get_id(), $item, $_product );
                do_action( 'woocommerce_after_order_itemmeta', $item->get_id(), $item, $_product );
                $extra_meta = ob_get_clean();
                if ($extra_meta && is_string($extra_meta)) {
                    $extra_meta = trim(str_ireplace(array('<br>', '<br/>', '<br />'), array("\n", "\n", "\n"), $extra_meta));
                    if ($extra_meta) {
                        $description .= "\n" . $extra_meta;
                    }
                }
            }

            // Maybe add WooCommerce Bookings period to description
            if (class_exists('WC_Booking_Data_Store')) {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id($item->get_id());
                if ($booking_ids) {
                    $booking = get_wc_booking($booking_ids[0]);
                    if ($booking) {
                        $booking_period = $booking->get_start_date();
                        if ($booking->get_end_date() != $booking_period) {
                            $booking_period .= ' - ' . $booking->get_end_date();
                        }
                    }
                    $description .= "\n" . $booking_period;
                }
            }

            // Tax rate
            $mb_tax_rates = $this->get_mb_tax_rates();
            $mb_tax_rate_id = $this->get_notax_mb_taxrate($order);
            if ($this->is_vat_exempt($order)) {
                $wc_tax_rate_id = 'reverse_charge';
            } else {
                $wc_tax_rate_id = 'none';
            }
            $individual_taxes = array();
            $line_tax = 0.0;
            if (wc_tax_enabled()) {
                $order_taxes = $order->get_taxes();
                $tax_data = $item->get_taxes();
                foreach ($order_taxes as $tax_item) {
                    $tax_item_id = $tax_item->get_rate_id();
                    if (isset($tax_data['total'][$tax_item_id]) && is_numeric($tax_data['total'][$tax_item_id])) {
                        $tax_item_total = $price_adj_factor * floatval($tax_data['total'][$tax_item_id]);
                    } else {
                        $tax_item_total = 'none';
                    }
                    if (is_float($tax_item_total)) {
                        if (abs($item_line_total) > 1e-3) {
                            $pct = 100.0 * ($tax_item_total / $item_line_total);
                        } else {
                            $pct = 0.0;
                        }
                        $explicitly_linked_rate_id = $this->get_mapped_mb_tax_rate_id($tax_item_id, $tax_rate_mappings, $mb_tax_rates, $pct);
                        if ($explicitly_linked_rate_id) {
                            $mb_tax_rate_id = $explicitly_linked_rate_id;
                            $wc_tax_rate_id = $tax_item_id;
                        } else {
                            // Try to find rate id based on rate code
                            $tax_item_id = $this->get_wc_tax_rate_id_by_rate_code($tax_item->get_rate_code());
                            if ($tax_item_id && isset($tax_rate_mappings[(int)$tax_item_id])) {
                                $mb_tax_rate_id = $tax_rate_mappings[(int)$tax_item_id];
                                $wc_tax_rate_id = $tax_item_id;
                            } elseif ($tax_item_total > 0) {
                                $percentage_matching_rate_id = $this->detect_mb_taxrate($pct);
                                if ($percentage_matching_rate_id) {
                                    $mb_tax_rate_id = $percentage_matching_rate_id;
                                    $wc_tax_rate_id = null;
                                }
                            }
                        }
                        if (!isset($mb_tax_rates[$mb_tax_rate_id])) {
                            continue;
                        }
                        if (!is_callable(array($tax_item, 'get_rate_percent'))) {
                            // No support for multiple taxes if WC_Order_Item->get_rate_percent() is not available (before WC 3.7.0).
                            $line_tax = $tax_item_total;
                            break;
                        }
                        $rate_pct = $tax_item->get_rate_percent();
                        if ((abs($rate_pct - $pct) > 1.0) && ($pct > 0.0)) {
                            // Replace $rate_pct with calculated percentage
                            // This is necessary since $tax_item->get_rate_percent()
                            // can return 0 instead of the actual percentage
                            // for subscription renewals
                            $rate_pct = $pct;
                        }
                        if ($rate_pct < 0.1) {
                            continue; // Ignore zero tax rate
                        }
                        $individual_taxes[] = array(
                            'name'          => $mb_tax_rates[$mb_tax_rate_id]->name,
                            'gross_amount'  => min($item_line_total, round($tax_item_total * 100.0 / $rate_pct, 2)),
                            'tax_amount'    => $tax_item_total,
                            'tax_percentage'=> $rate_pct / 100.0,
                            'tax_rate_id'   => $mb_tax_rate_id,
                        );
                        $line_tax += $tax_item_total;
                    }
                }
                if (count($individual_taxes) == 1) {
                    $mb_tax_rate_id = $individual_taxes[0]['tax_rate_id'];
                } elseif (count($individual_taxes) > 1) {
                    // Try to make sure the individual gross amounts sum to the total gross amount
                    $wc_tax_rate_id = null;
                    $gross_amount_diff = $item_line_total;
                    $tax_percentages = array();
                    foreach ($individual_taxes as $i => $tax) {
                        $gross_amount_diff -= $tax['gross_amount'];
                        $tax_percentages[$i] = $individual_taxes[$i]['tax_percentage'];
                    }
                    asort($tax_percentages);
                    while (abs($gross_amount_diff) >= 0.008) {
                        $correction = false;
                        $tax_percentages = array();
                        for ($i=0; $i<count($individual_taxes); $i++) {
                            $tax_percentages[$i] = $individual_taxes[$i]['tax_percentage'];
                        }
                        asort($tax_percentages);
                        foreach ($tax_percentages as $i => $_) {
                            $delta = ($gross_amount_diff > 0) ? 0.01 : -0.01;
                            $new_gross = $individual_taxes[$i]['gross_amount'] + $delta;
                            $new_tax_amount = $new_gross * $individual_taxes[$i]['tax_percentage'];
                            if (round($new_tax_amount, 2) == round($individual_taxes[$i]['tax_amount'], 2)) {
                                $individual_taxes[$i]['gross_amount'] = $new_gross;
                                $gross_amount_diff -= $delta;
                                $correction = true;
                                break;
                            }
                        }
                        if (!$correction) {
                            // It is not possible to match the gross amounts exactly
                            break;
                        }
                    }
                }
            }

            // Maybe enforce 0% tax rate
            if (isset($mb_tax_rates[$mb_tax_rate_id]) && ($line_tax == 0.0) && (floatval($item_line_total) > 1e-3)) {
                if (floatval($mb_tax_rates[$mb_tax_rate_id]->percentage) > 1e-2) {
                    $mb_tax_rate_id = $this->get_notax_mb_taxrate($order);
                    $wc_tax_rate_id = 'none';
                }
            }

            if ($mb_tax_rate_id == '') {
                $this->log('Cannot find Moneybird tax rate for ' . $item['name']);
                $this->add_admin_notice(sprintf(__('Moneybird error: cannot find appropriate Moneybird tax rate for %s. Please make sure your WooCommerce tax rates have matching Moneybird tax rates.', 'woocommerce_moneybird'), $item['name']), true);
                do_action('woocommerce_moneybird_generate_invoice_error', $order, 'Cannot find Moneybird tax rate for ' . $item['name']);
                return false;
            }

            // Maybe explicitly specify discount
            $discount = 0.0;
            if ($item->get_subtotal() !== $item->get_total()) {
                if ($prices_include_tax) {
                    $original_price = $order->get_item_subtotal($item, true, true);
                    $discount = $original_price - $order->get_item_total($item, true, true);
                } else {
                    $original_price = $order->get_item_subtotal($item, false, true);
                    $discount = $original_price - $order->get_item_total($item, false, true);
                }
            }
            if (isset($this->settings['specify_discounts']) && stripos($this->settings['specify_discounts'], 'line') !== false) {
                $specify_discounts = $this->settings['specify_discounts'];
            } else {
                $specify_discounts = false;
            }
            if ($specify_discounts && ($discount > 0.0)) {
                if (stripos($specify_discounts, 'line:unit') !== false) {
                    $original_price_str = wc_price($original_price, array('currency' => $order->get_currency()));
                    $discount_str = wc_price($discount, array('currency' => $order->get_currency()));
                    if ($item_qty == 1) {
                        $description .= "\n" . __('Regular price:', 'woocommerce_moneybird') . ' ' . $original_price_str;
                        $description .= ", " . __('discount:', 'woocommerce_moneybird') . ' ' . $discount_str;
                    } else {
                        $description .= "\n" . __('Regular price per item:', 'woocommerce_moneybird') . ' ' . $original_price_str . "\n";
                        $description .= __('Discount per item:', 'woocommerce_moneybird') . ' ' . $discount_str;
                    }
                } elseif (stripos($specify_discounts, 'line:total') !== false) {
                    $total_discount = $discount * (float)$item_qty;
                    $description .= "\n" . __('Discount:', 'woocommerce_moneybird') . ' ';
                    $description .= wc_price($total_discount, array('currency' => $order->get_currency()));
                }
            }

            // Revenue ledger account
            if ($_product) {
                $ledger_account_id = $this->get_revenue_ledger_account_id($_product->get_id());
            } else {
                $ledger_account_id = $this->settings['products_ledger_account_id'];
            }
            if (!empty($wc_tax_rate_id)) {
                if (isset($this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'])) {
                    if (!empty($this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'])) {
                        $ledger_account_id = $this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'];
                    }
                }
            }

            // Project
            $project_id = '';
            if ($_product) {
                $project_id = $this->get_project_id($_product->get_id());
            }

            $multiple_lines = count($individual_taxes) > 1;
            if ($multiple_lines) {
                $sublines_total = 0.0;
                foreach ($individual_taxes as $tax_line) {
                    $sublines_total += $tax_line['gross_amount'];
                }
                if ($sublines_total > $item_line_total + 0.01) {
                    $multiple_lines = false;
                }
            }

            if ($order_is_refund && $item_qty < 0) {
                // Refuse negative qty and negative price in case of refund
                // This can happen if prices are taken from the parent order
                if ($item_line_total < -1e-3) {
                    $item_line_total = -$item_line_total;
                }
                if ($line_tax < -1e-3) {
                    $line_tax = -$line_tax;
                }
                if ($multiple_lines) {
                    foreach ($individual_taxes as $i => $tax_line) {
                        if ($individual_taxes[$i]['gross_amount'] < -1e-3) {
                            $individual_taxes[$i]['gross_amount'] = -$individual_taxes[$i]['gross_amount'];
                        }
                        if ($individual_taxes[$i]['tax_amount'] < -1e-3) {
                            $individual_taxes[$i]['tax_amount'] = -$individual_taxes[$i]['tax_amount'];
                        }
                    }
                }
            }

            if (!$multiple_lines) {
                $invoice_item = array(
                    'qty'                       => $item_qty,
                    'description'               => $description,
                    'price'                     => $item_line_total / abs((float)$item_qty),
                    'tax'                       => $line_tax / abs((float)$item_qty),
                    'tax_rate_id'               => $mb_tax_rate_id,
                    'ledger_account_id'         => $ledger_account_id,
                    'sublines'                  => array()
                );
            } else {
                $invoice_item = array(
                    'qty'                   => '',
                    'description'           => '',
                    'price'                 => false,
                    'tax'                   => false,
                    'tax_rate_id'           => false,
                    'ledger_account_id'     => false,
                    'sublines'              => array()
                );
                foreach ($individual_taxes as $tax_line) {
                    $invoice_item['sublines'][] = array(
                        'qty'                   => $item_qty,
                        'description'           => $description . ' - ' . $tax_line['name'],
                        'price'                 => $tax_line['gross_amount'] / abs((float)$item_qty),
                        'tax'                   => $tax_line['tax_amount'] / abs((float)$item_qty),
                        'tax_rate_id'           => $tax_line['tax_rate_id'],
                        'ledger_account_id'     => $ledger_account_id
                    );
                }
            }

            if ($project_id) {
                $invoice_item['project_id'] = $project_id;
            }
            if ($_product) {
                $workflow_id = $this->get_product_meta($_product->get_id(), '_mb_workflow_id');
                if ($workflow_id) {
                    $invoice_item['workflow_id'] = $workflow_id;
                }
                $document_style_id = $this->get_product_meta($_product->get_id(), '_mb_document_style_id');
                if ($document_style_id) {
                    $invoice_item['document_style_id'] = $document_style_id;
                }
                $product_extra_text = $this->get_product_meta($_product->get_id(), '_mb_extra_product_text');
                if (!empty($product_extra_text)) {
                    $invoice_item['description'] .= "\n" . $product_extra_text;
                }
            }

            // Maybe add WooCommerce Subscriptions period
            // This is only relevant for subscription products
            if (!empty($product_id) && !empty($subscription_payment_periods[$product_id])) {
                $invoice_line['period'] = $subscription_payment_periods[$product_id];
            }
            if (empty($invoice_item['period']) && !empty($subscription_payment_periods[$parent_product_id])) {
                $invoice_item['period'] = $subscription_payment_periods[$parent_product_id];
            }

            $invoice_item = apply_filters('woocommerce_moneybird_invoice_item', $invoice_item, $item, $order);
            if (!empty($invoice_item)) {
                $items[] = $invoice_item;
            }
        }

        return $items;
    }

    function get_order_shipping($order, $tax_rate_mappings) {
        // Return an array of order shipping costs, used by generate_invoice()
        global $woocommerce;
        $shipping_lines = array();

        $line_items_shipping = $order->get_items('shipping');
        foreach ($line_items_shipping as $item) {
            // Determine tax rate
            $mb_tax_rate_id = $this->get_notax_mb_taxrate($order);
            if ($this->is_vat_exempt($order)) {
                $wc_tax_rate_id = 'reverse_charge';
            } else {
                $wc_tax_rate_id = 'none';
            }
            $line_tax = 0.0;

            if (wc_tax_enabled()) {
                $order_taxes = $order->get_taxes();
                $tax_data = $item->get_taxes();
                foreach ($order_taxes as $tax_item) {
                    $tax_item_id = $tax_item->get_rate_id();
                    if (isset($tax_data['total'][$tax_item_id]) && is_numeric($tax_data['total'][$tax_item_id])) {
                        $tax_item_total = floatval($tax_data['total'][$tax_item_id]);
                    } else {
                        $tax_item_total = 'none';
                        $wc_tax_rate_id = null;
                    }
                    if (is_float($tax_item_total)) {
                        if (isset($tax_rate_mappings[(int)$tax_item_id])) {
                            $mb_tax_rate_id = $tax_rate_mappings[(int)$tax_item_id];
                            $wc_tax_rate_id = $tax_item_id;
                        } else {
                            // Try to find rate id based on rate code
                            $tax_item_id = $this->get_wc_tax_rate_id_by_rate_code($tax_item->get_rate_code());
                            if ($tax_item_id && isset($tax_rate_mappings[(int)$tax_item_id])) {
                                $mb_tax_rate_id = $tax_rate_mappings[(int)$tax_item_id];
                                $wc_tax_rate_id = $tax_item_id;
                            }
                        }

                        $line_tax = $tax_item_total;
                        break;
                    }
                }
            }

            // Maybe enforce 0% tax rate
            $line_total = $order->get_line_total($item, false, false);
            if (isset($mb_tax_rates[$mb_tax_rate_id]) && ($line_tax == 0.0) && (floatval($line_total) > 1e-3)) {
                if (floatval($mb_tax_rates[$mb_tax_rate_id]->percentage) > 1e-2) {
                    $mb_tax_rate_id = $this->get_notax_mb_taxrate($order);
                    $wc_tax_rate_id = 'none';
                }
            }

            // Add invoice line
            $price = wc_round_tax_total($item['cost']);
            if ($mb_tax_rate_id != '') {
                $title = __('Shipping costs', 'woocommerce_moneybird');
                if (is_callable(array($item, 'get_method_title'))) {
                    $title .= ': ' . $item->get_method_title();
                }
                $line = array(
                    'price'       => $price,
                    'tax'         => $line_tax,
                    'tax_rate_id' => $mb_tax_rate_id,
                    'description' => $title
                );
                if (!empty($wc_tax_rate_id)) {
                    if (isset($this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'])) {
                        if (!empty($this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'])) {
                            $line['ledger_account_id'] = $this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'];
                        }
                    }
                }
                $shipping_lines[] = $line;
            } else {
                $this->log("Cannot find Moneybird tax rate for the shipping costs");
                $this->add_admin_notice(__('Moneybird error: cannot find appropriate Moneybird tax rate for the shipping costs. Please make sure your WooCommerce tax rates have matching Moneybird tax rates.', 'woocommerce_moneybird'), true);
                do_action('woocommerce_moneybird_generate_invoice_error', $order, 'Cannot find Moneybird tax rate for the shipping costs');
                return false;
            }
        }

        return apply_filters('woocommerce_moneybird_invoice_shipping', $shipping_lines, $order);
    }

    function get_order_fees($order, $tax_rate_mappings) {
        // Return an array of order fees, used by generate_invoice()
        global $woocommerce;
        $fee_lines = array();

        $line_items_fee = $order->get_items('fee');
        foreach ($line_items_fee as $item) {
            // Determine tax rate
            $mb_tax_rate_id = $this->get_notax_mb_taxrate($order);
            if ($this->is_vat_exempt($order)) {
                $wc_tax_rate_id = 'reverse_charge';
            } else {
                $wc_tax_rate_id = 'none';
            }
            $line_tax = 0.0;

            if (wc_tax_enabled()) {
                $order_taxes = $order->get_taxes();
                $tax_data = $item->get_taxes();
                foreach ($order_taxes as $tax_item) {
                    $tax_item_id = $tax_item->get_rate_id();
                    if (isset($tax_data['total'][$tax_item_id]) && is_numeric($tax_data['total'][$tax_item_id])) {
                        $tax_item_total = floatval($tax_data['total'][$tax_item_id]);
                    } else {
                        $tax_item_total = 'none';
                        $wc_tax_rate_id = null;
                    }
                    if (is_float($tax_item_total)) {
                        if (isset($tax_rate_mappings[(int)$tax_item_id])) {
                            $mb_tax_rate_id = $tax_rate_mappings[(int)$tax_item_id];
                            $wc_tax_rate_id = $tax_item_id;
                        } else {
                            // Try to find rate id based on rate code
                            $tax_item_id = $this->get_wc_tax_rate_id_by_rate_code($tax_item->get_rate_code());
                            if ($tax_item_id && isset($tax_rate_mappings[(int)$tax_item_id])) {
                                $mb_tax_rate_id = $tax_rate_mappings[(int)$tax_item_id];
                                $wc_tax_rate_id = $tax_item_id;
                            }
                        }

                        $line_tax = $tax_item_total;
                        break;
                    }
                }
            }

            // Maybe enforce 0% tax rate
            $line_total = $order->get_line_total($item, false, false);
            if (isset($mb_tax_rates[$mb_tax_rate_id]) && ($line_tax == 0.0) && (floatval($line_total) > 1e-3)) {
                if (floatval($mb_tax_rates[$mb_tax_rate_id]->percentage) > 1e-2) {
                    $mb_tax_rate_id = $this->get_notax_mb_taxrate($order);
                    $wc_tax_rate_id = 'none';
                }
            }

            // Add invoice line
            if ($mb_tax_rate_id == '') {
                $this->log("Cannot find Moneybird tax rate for " . $item['name']);
                $this->add_admin_notice(sprintf(__('Moneybird error: cannot find appropriate Moneybird tax rate for %s. Please make sure your WooCommerce tax rates have matching Moneybird tax rates.', 'woocommerce_moneybird'), $item['name']), true);
                do_action('woocommerce_moneybird_generate_invoice_error', $order, 'Cannot find Moneybird tax rate for ' . $item['name']);
                return false;
            }

            $price = wc_round_tax_total($item['line_total']);
            $line = array(
                'description' => str_replace('&#8211;', '-', $item['name']), // replace ndash by dash to prevent encoding problems,
                'price'       => $price,
                'tax'         => $line_tax,
                'tax_rate_id' => $mb_tax_rate_id
            );
            if (!empty($wc_tax_rate_id)) {
                if (isset($this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'])) {
                    if (!empty($this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'])) {
                        $line['ledger_account_id'] = $this->settings['tax_rate_'.$wc_tax_rate_id.'_ledger_account_id'];
                    }
                }
            }
            $fee_lines[] = $line;
        }

        return apply_filters('woocommerce_moneybird_invoice_fees', $fee_lines, $order);
    }

    function get_order_gift_card_redemptions($order) {
        // Return an array of order gift card redemptions, used by generate_invoice()
        global $woocommerce;
        $gc_lines = array();

        // WooCommerce Gift Cards plugin
        foreach ($order->get_items('gift_card') as $item_id => $item) {
            if (wc_get_order_item_meta($item_id, 'gift_card_debited', true) != 'yes') {
                continue;
            }
            $amount = wc_get_order_item_meta($item_id, 'amount', true);
            if ($amount == '') {
                continue;
            }
            $invoice_line = array(
                'description' => __('Gift card', 'woocommerce_moneybird') . ': ' . $item->get_name(),
                'price'       => -1.0 * round(floatval($amount), 2),
                'tax'         => 0,
                'tax_rate_id' => $this->get_notax_mb_taxrate($order),
            );
            if (!empty($this->settings['gift_card_redemption_ledger_account_id'])) {
                $invoice_line['ledger_account_id'] = substr($this->settings['gift_card_redemption_ledger_account_id'], 1);
            }
            if (abs($invoice_line['price']) >= 0.01) {
                $gc_lines[] = $invoice_line;
            }
        }

        // PW Gift Cards plugin
        foreach ($order->get_items('pw_gift_card') as $item) {
            $card_nr = $item->get_card_number();
            $amount = $item->get_amount();
            if (empty($card_nr) || empty($amount)) {
                continue;
            }
            $amount = round(floatval($amount), 2);
            if ($amount == 0.0) {
                continue;
            }
            $invoice_line = array(
                'description'       => __('Gift card', 'woocommerce_moneybird') . ': ' .$card_nr,
                'price'             => -1.0 * $amount,
                'tax_rate_id'       => $this->get_notax_mb_taxrate($order),
            );
            if (!empty($this->settings['gift_card_redemption_ledger_account_id'])) {
                $invoice_line['ledger_account_id'] = substr($this->settings['gift_card_redemption_ledger_account_id'], 1);
            }
            $gc_lines[] = $invoice_line;
        }

        return apply_filters('woocommerce_moneybird_invoice_gift_cards', $gc_lines, $order);
    }

    function get_order_coupon_redemptions($order) {
        // Return an array of coupon redemptions, used by generate_invoice()
        global $woocommerce;
        $coupon_lines = array();

        foreach ($order->get_items('coupon') as $item_id => $coupon_item) {
            $amount = $coupon_item->get_discount();
            if (empty($amount)) {
                continue;
            }

            // Only include certain types of coupons
            $coupon_data = wc_get_order_item_meta($item_id, 'coupon_data', true);
            if (empty($coupon_data)) {
                $coupon_data = $coupon_item->get_meta('coupon_info');
            }
            if (empty($coupon_data)) {
                continue;
            }
            $accepted_discount_types = array('multi_purpose_voucher', 'ign_store_credit');
            if (is_array($coupon_data)) {
                if (empty($coupon_data['discount_type']) || !in_array($coupon_data['discount_type'], $accepted_discount_types)) {
                    continue;
                }
            } elseif (is_string($coupon_data)) {
                $discount_type_accepted = false;
                foreach ($accepted_discount_types as $accepted_discount_type) {
                    if (stripos($coupon_data, $accepted_discount_type) !== false) {
                        $discount_type_accepted = true;
                        break;
                    }
                }
                if (!$discount_type_accepted) {
                    continue;
                }
            } else {
                continue;  // Unknown coupon data format
            }

            // Add invoice line
            $amount = floatval($amount);
            $code = $coupon_item->get_code();
            $description = __('Coupon', 'woocommerce');
            if ($code) {
                $description .= ': ' . $code;
            }
            $invoice_line = array(
                        'description' => $description,
                        'price'       => -1.0 * round(floatval($amount), 2),
                        'tax'         => 0,
                        'tax_rate_id' => $this->get_notax_mb_taxrate($order),
            );
            if (!empty($this->settings['gift_card_redemption_ledger_account_id'])) {
                $invoice_line['ledger_account_id'] = substr($this->settings['gift_card_redemption_ledger_account_id'], 1);
            }
            if (abs($invoice_line['price']) >= 0.01) {
                $coupon_lines[] = $invoice_line;
            }
        }

        return apply_filters('woocommerce_moneybird_invoice_coupons', $coupon_lines, $order);
    }

    static function is_vat_exempt($order) {
        if ($order->get_meta('is_vat_exempt', true) === 'yes') {
            return true;
        }
        // Check parent order in case of refund
        if ($order->get_type() == 'shop_order_refund') {
            $parent_id = $order->get_parent_id();
            if ($parent_id) {
                $parent_order = wc_get_order($parent_id);
                if ($parent_order) {
                    return ($parent_order->get_meta('is_vat_exempt', true) === 'yes');
                }
            }
        }

        return false;
    }

    function get_notax_mb_taxrate($order=false) {
        // Try to return the Moneybird tax rate id for items without tax.
        // If $order is specified we check if reverse change is applicable.

        // Maybe use reverse charge rate
        if ($order) {
            if ($this->is_vat_exempt($order)) {
                return $this->get_reverse_charge_mb_taxrate();
            }
        }

        // No reverse charge
        if (isset($this->settings['tax_rate_none']) && $this->settings['tax_rate_none']) {
            return $this->settings['tax_rate_none'];
        } else {
            return $this->detect_mb_taxrate(0.0);
        }
    }

    function detect_mb_taxrate($tax_percentage) {
        // Try to match a Moneybird tax rate to the specified tax rate percentage (0.0 - 100.0)
        // Return: Moneybird tax rate id

        $detected_mb_tax_rate_id = '';
        $rounding_error = 5.0; // Allow up to 5% rounding error
        foreach ($this->get_mb_tax_rates() as $mb_tax_rate_id => $mb_tax_rate) {
            if (abs($tax_percentage - $mb_tax_rate->percentage) < $rounding_error) {
                $detected_mb_tax_rate_id = $mb_tax_rate_id;
                $rounding_error = abs($tax_percentage - $mb_tax_rate->percentage);
                if ($rounding_error < 0.5) { break; }
            }
        }

        return $detected_mb_tax_rate_id;
    }

    function guess_reverse_charge_mb_taxrate() {
        // Try to make an informed guess about the reverse charge tax rate
        foreach ($this->get_mb_tax_rates() as $mb_tax_rate_id => $mb_tax_rate) {
            if (stripos($mb_tax_rate->name, 'verlegd') !== false) {
                return $mb_tax_rate_id;
            }
        }

        return $this->get_notax_mb_taxrate();
    }

    function get_reverse_charge_mb_taxrate() {
        // Try to return the Moneybird tax rate id for items without tax
        if (isset($this->settings['tax_rate_reverse_charge']) && $this->settings['tax_rate_reverse_charge']) {
            return $this->settings['tax_rate_reverse_charge'];
        } else {
            return $this->guess_reverse_charge_mb_taxrate();
        }
    }

    function postpone_generation($doctype, $order, $state=false) {
        // Store generation state, clear lock, add to queue
        if ($state) {
            $order->update_meta_data('moneybird_state', $state);
        } else {
            $order->delete_meta_data('moneybird_state');
        }
        $order->save_meta_data();
        $order_id = $order->get_id();
        $this->log("Generation of $doctype for order " . $order_id . ' postponed.');
        if ($doctype == 'estimate') {
            $this->add_to_queue('generate_estimate', array($order_id), 60);
        } else {
            $this->add_to_queue('generate', array($order_id), 60);
        }
    }

    function get_generation_settings($doctype, $order_type, $is_paid) {
        /**
         * Return array of document generation settings that are dependent on the document type and/or order type.
         * @param string $doctype       should be either 'invoice' or 'estimate'.
         * @param string $order_type
         * @param bool   $is_paid
         * @return array containing keys: document_style_id, reference, register_payment, workflow_id, sendmode.
         */
        $is_estimate = ($doctype == 'estimate');
        $is_refund = ($order_type == 'shop_order_refund');
        $settings = array(
            'document_style_id' => ($is_estimate) ? $this->settings['estimate_document_style_id'] : $this->settings['document_style_id'],
        );
        if ($is_estimate) {
            // Estimate
            $settings['workflow_id'] = $this->settings['estimate_workflow_id'];
            $settings['reference'] = $this->settings['estimate_reference'];
            $settings['register_payment'] = false;
            $settings['sendmode'] = $this->settings['estimate_sendmode'];
        } else {
            // Invoice
            if ($is_refund) {
                // Refund
                $settings['workflow_id'] = $this->settings['refund_workflow_id'];
                $settings['reference'] = $this->settings['refund_invoice_reference'];
                $settings['register_payment'] = ($this->settings['refund_mark_paid'] == 'yes');
                $settings['sendmode'] = $this->settings['refund_send_invoice'];
            } else {
                // No refund
                $settings['reference'] = $this->settings['invoice_reference'];
                $settings['register_payment'] = ($this->settings['register_payment'] == 'yes');
                $settings['sendmode'] = $this->settings['send_invoice'];
                if ($is_paid) {
                    $settings['workflow_id'] = $this->settings['workflow_id_paid'];
                } else {
                    $settings['workflow_id'] = $this->settings['workflow_id'];
                }
            }
        }
        return $settings;
    }

    function generate_invoice($order) {
        $this->generate_mb_document($order, 'invoice');
    }

    function generate_combined_invoice($ids) {
        $orders = array();
        $billing_email = '';
        $order_type = '';
        foreach ($ids as $id) {
            $order = wc_get_order($id);
            if (empty($order)) {
                continue;
            }
            // Make sure orders have the same billing_email
            if (empty($billing_email)) {
                $billing_email = trim(strtolower($order->get_billing_email()));
            } else {
                if ($billing_email != trim(strtolower($order->get_billing_email()))) {
                    $this->add_admin_notice(
                        __('All orders should have the same invoice email address.', 'woocommerce_moneybird'),
                        true
                    );
                    return false;
                }
            }
            // Make sure orders have the same type
            if (empty($order_type)) {
                $order_type = $order->get_type();
            } else {
                if ($order_type != $order->get_type()) {
                    $this->add_admin_notice(
                        __('All orders should have the same type.', 'woocommerce_moneybird'),
                        true
                    );
                    return false;
                }
            }
            $orders[$order->get_date_created()->getTimestamp()] = $order;
        }

        ksort($orders);
        $primary_order = array_shift($orders);
        $secondary_order_ids = array();
        foreach ($orders as $order) {
            $secondary_order_ids[] = $order->get_id();
        }
        if (!empty($orders)) {
            $primary_order->update_meta_data(
                'moneybird_combined_order_ids',
                implode(',', $secondary_order_ids)
            );
            $primary_order->save_meta_data();
        }

        return $this->generate_mb_document($primary_order, 'invoice', $orders);
    }

    function generate_estimate($order) {
        $this->generate_mb_document($order, 'estimate');
    }

    function generate_mb_document($order, $doctype, $secondary_orders = array()) {
        /**
         * Generate a Moneybird invoice or estimate for $order.
         *
         * @param WC_Order $order WC_Order object.
         * @param string $doctype should be either 'invoice' or 'estimate'.
         * @param array $secondary_orders array of WC_Orders to be combined into a single invoice.
         * @return bool true on success, false on failure.
         * The invoice generation process is governed by the following meta fields on the order:
         *   - moneybird_estimate_id:   the ID of the Moneybird estimate.
         *   - moneybird_invoice_id:    the ID of the Moneybird invoice.
         *   - moneybird_state:         specifies the state of the generation flow in case of an interruption.
         * If the moneybird_state field is filled, the generation process picks up where it left of during the
         * last call. This recovery process can be triggered in case the API request limit is reached.
         * Once the document generation is completed, meta field moneybird_{{doctype}}_id will be filled
         * and moneybird_state will be empty.
         */

        /******************************************************************************
         * Initialization
         ******************************************************************************/
        global $woocommerce;
        $is_estimate = false;
        if ($doctype == 'invoice') {
            $queue_meta_key = 'moneybird_queue_generate';
        } elseif ($doctype == 'estimate') {
            $is_estimate = true;
            $queue_meta_key = 'moneybird_queue_generate_estimate';
            if ($this->settings['estimate_enabled'] != 'yes') {
                return false;
            }
        } else {
            return false;
        }
        if (!wcmb_is_license_valid($this->settings['licensekey'])) {
            return false; // A valid license is required for the invoicing functionality to work
        }
        $order_id = is_callable(array($order, 'get_id')) ? $order->get_id() : $order->id;
        $order_type = is_callable(array($order, 'get_type')) ? $order->get_type() : 'shop_order';
        if (apply_filters('woocommerce_moneybird_process_order', true, $order, $doctype) === false) {
            return false;
        }

        // Maybe load secondary orders
        $secondary_order_ids = array();
        if (empty($secondary_orders)) {
            $secondary_order_ids_meta = $order->get_meta('moneybird_combined_order_ids');
            if ($secondary_order_ids_meta) {
                $secondary_order_ids = explode(',', $secondary_order_ids_meta);
                foreach ($secondary_order_ids as $secondary_order_id) {
                    $secondary_order = wc_get_order($secondary_order_id);
                    if ($secondary_order) {
                        $secondary_orders[] = $secondary_order;
                    }
                }
            }
        } else {
            foreach ($secondary_orders as $secondary_order) {
                $secondary_order_ids[] = $secondary_order->get_id();
            }
            if (empty($order->get_meta('moneybird_combined_order_ids'))) {
                // Store secondary order ids in primary order
                $order->update_meta_data(
                    'moneybird_combined_order_ids',
                    implode(',', $secondary_order_ids)
                );
                $order->save_meta_data();
            }
        }
        if ($doctype != 'invoice') {
            $secondary_orders = array();
        }

        // Build array of all orders to include in the MB document
        if ($order_type == 'shop_order' && $doctype == 'invoice') {
            $all_orders = array($order);
            // Get refunds for main order that haven't been invoiced yet
            foreach ($order->get_refunds() as $refund) {
                if (empty($refund->get_meta('moneybird_invoice_id'))) {
                    $all_orders[] = $refund;
                }
            }

            // Get refunds for secondary orders that haven't been invoiced yet
            foreach ($secondary_orders as $secondary_order) {
                $all_orders[] = $secondary_order;
                if ($secondary_order->get_type() == 'shop_order') {
                    foreach ($secondary_order->get_refunds() as $refund) {
                        if (empty($refund->get_meta('moneybird_invoice_id'))) {
                            $all_orders[] = $refund;
                        }
                    }
                }
            }
        } else {
            $all_orders = array_merge(array($order), $secondary_orders);
        }

        $parent_order = null;
        if ($order_type == 'shop_order') {
            if ($secondary_orders) {
                $this->log(
                    "Generating combined $doctype for orders " . $order_id . ", " .
                    implode(',', $secondary_order_ids)
                );
            } else {
                $this->log("Generating $doctype for order " . $order_id);
            }
            if (count($all_orders) > count($secondary_orders) + 1) {
                $this->log("Including 1 or more uninvoiced refunds.");
            }
        } elseif ($order_type == 'shop_order_refund' && $doctype == 'invoice') {
            if ($secondary_orders) {
                $this->log("Generation of combined credit invoices is not supported.");
                return false;
            } else {
                $this->log("Generating invoice for refund " . $order_id . " of order " . $order->get_parent_id());
                $parent_order = wc_get_order($order->get_parent_id());
            }
        } elseif ($order_type=='wcdp_payment' && $doctype=='invoice') {
            if ($secondary_orders) {
                $this->log("Generation of combined invoices for partial payments is not supported.");
                return false;
            }
            $this->log("Generating invoice for partial payment order " . $order_id);
        } else {
            $this->log("Cancel $doctype generation: unsupported order type " . $order_type . " for id " . $order_id);
            return false;
        }

        // Make sure API is available
        if (!$this->load_api_connector()) {
            $this->add_admin_notice(__('Cannot connect to the Moneybird API', 'woocommerce_moneybird'), true);
            return false;
        }

        // Determine state, should be empty or one of ['saved', 'paid', 'sent']
        $moneybird_state = $order->get_meta('moneybird_state', true);
        if (!empty($moneybird_state)) {
            if (!in_array($moneybird_state, array('saved', 'paid', 'sent'))) {
                $order->delete_meta_data('moneybird_state');
                $order->save_meta_data();
                $moneybird_state = '';
            }
        }

        // Check if order already has specified document type
        $mb_doc_id = $order->get_meta("moneybird_".$doctype."_id", true);
        if (!empty($mb_doc_id)) {
            if ($this->document_id_valid_for_order($order_id, $mb_doc_id, $doctype) == false) {
                $mb_doc_id = '';
            }
        }
        if (!empty($mb_doc_id)) {
            // Existing MB document
            if (empty($moneybird_state)) {
                // Generation process is already complete
                $this->log("Cancel $doctype generation: document was already created.");
                $order->delete_meta_data($queue_meta_key);
                if ($secondary_orders) {
                    $order->delete_meta_data('moneybird_combined_order_ids');
                }
                $order->save();
                $this->add_admin_notice(
                    sprintf(
                        __('Cannot create Moneybird %s because this order already has one.', 'woocommerce_moneybird'),
                        __($doctype, 'woocommerce_moneybird')
                    ),
                    true
                );
                return false;
            } else {
                // Load partly generated document
                if ($is_estimate) {
                    $mb_doc = $this->mb_api->getEstimate($mb_doc_id);
                } else {
                    $mb_doc = $this->mb_api->getSalesInvoice($mb_doc_id);
                }
                if (!$mb_doc) {
                    if ($this->mb_api->request_limit_reached) {
                        $this->postpone_generation($doctype, $order);
                    } else {
                        $this->log("Error loading $doctype: " . $this->mb_api->getLastError());
                        $this->add_admin_notice('Moneybird error: ' . $this->mb_api->getLastError(), true);
                        do_action('woocommerce_moneybird_generate_'.$doctype.'_error', $order, 'Error loading existing document.');
                    }
                    return false;
                }
            }
        }

        // Acquire lock to prevent duplicate documents when processing bulk requests
        try {
            $generation_lock = $this->acquire_generation_lock($order_id);
        } catch (Exception $e) {
            $generation_lock = true;
        }
        if ($generation_lock === false) {
            // Lock is still active
            $this->log("Cancel $doctype generation: document is already being generated for order " . $order_id);
            $this->add_admin_notice(__('Cannot create Moneybird document: another document is already being created for order', 'woocommerce_moneybird') . ' ' . $order_id . '.', true);
            return false;
        }

        /******************************************************************************
         * Prepare data
         ******************************************************************************/

        // Check whether all orders that should be included on the document ($all_orders) are paid
        // An order is consider paid if it meets at least one of the following criteria:
        // - Date paid and payment method are set
        // - Order total is 0.0
        // - Order is fully refunded
        // - Order is a refund
        // Use the woocommerce_moneybird_is_order_paid filter to overwrite these rules
        $all_orders_paid = true;
        foreach ($all_orders as $_order) {
            $_order_type = $_order->get_type();
            $_order_is_paid = false;

            if ($_order_type == 'shop_order_refund') {
                // Always mark refunds as paid
                $_order_is_paid = true;
            } else {
                $date_paid = $_order->get_date_paid();
                if (!empty($date_paid)) {
                    if (is_callable(array($_order, 'get_payment_method'))) {
                        $_payment_method = $_order->get_payment_method();
                    } else {
                        $_payment_method = $_order->payment_method;
                    }
                    if (!empty($_payment_method)) {
                        $_order_is_paid = true;
                    }
                }
            }

            // Check if order total is 0
            if ($_order->get_total() == 0) {
                $_order_is_paid = true;
            }

            // Check if order is fully refunded
            if ($_order_type == 'shop_order' && $_order->get_total() == $_order->get_total_refunded()) {
                $_order_is_paid = true;
            }

            if (!$_order_is_paid) {
                $all_orders_paid = false;
                break;
            }
        }
        // The filter for $all_orders_paid is named woocommerce_moneybird_is_order_paid for backwards compatibility with legacy filters
        $all_orders_paid = apply_filters(
            'woocommerce_moneybird_is_order_paid',
            $all_orders_paid,
            $order,
            $secondary_orders
        );

        $tax_rate_mappings = $this->get_tax_rate_mappings();
        if (method_exists($order, 'get_prices_include_tax')) {
            $prices_include_tax = $order->get_prices_include_tax();
        } else {
            $order_prices_include_tax = $order->get_meta('_prices_include_tax', true);
            if ($order_prices_include_tax) {
                $prices_include_tax = ($order_prices_include_tax == 'yes');
            } else {
                $prices_include_tax = (get_option('woocommerce_prices_include_tax') == 'yes');
            }
        }
        $prices_include_tax = apply_filters(
            'woocommerce_moneybird_prices_include_tax',
            $prices_include_tax,
            $order,
            $secondary_orders
        );

        $gen_settings = $this->get_generation_settings($doctype, $order_type, $all_orders_paid);

        /******************************************************************************
         * STAGE 1: create document (empty state)
         ******************************************************************************/
        $total_amount = 0.0;
        foreach ($all_orders as $_order) {
            $total_amount += floatval($_order->get_total());
        }
        $total_amount = round($total_amount, 2);
        if (empty($moneybird_state)) {
            // Maybe cancel if total amount is 0.0
            if (($this->settings['ignore_zero_orders'] == 'yes')) {
                if ($total_amount == 0.0) {
                    $this->log("Cancel $doctype generation: order total is 0.0");
                    $this->add_admin_notice(__('Moneybird document was not created since the order total is 0.0.', 'woocommerce_moneybird'), true);
                    $this->release_generation_lock($order_id, $generation_lock);
                    $order->delete_meta_data($queue_meta_key);
                    if ($secondary_orders) {
                        $order->delete_meta_data('moneybird_combined_order_ids');
                    }
                    $order->save();
                    return false;
                }
            }
        }

        // Init document object
        $doc = array();

        // Document style
        if ($gen_settings['document_style_id']) {
            $doc['document_style_id'] = $gen_settings['document_style_id'];
        }

        // Workflow
        if ($gen_settings['workflow_id']) {
            $doc['workflow_id'] = $gen_settings['workflow_id'];
        }

        // Reference
        $doc['reference'] = apply_filters(
            'woocommerce_moneybird_reference',
            $gen_settings['reference'],
            $order,
            $secondary_orders
        );
        if (!empty($parent_order) && ($order_type == 'shop_order_refund')) {
            $order_meta = $parent_order;
        } else {
            $order_meta = $order;
        }
        preg_match_all('/\{\{([^}]+)}}/', $doc['reference'], $matches);
        if ($matches) {
            foreach ($matches[1] as $custom_field_key) {
                $custom_field_val = $this->get_custom_field_value($order_meta, $custom_field_key, $secondary_orders);
                if (!$custom_field_val) {
                    $custom_field_val = '';
                }
                $doc['reference'] = trim(str_replace('{{'.$custom_field_key.'}}', $custom_field_val, $doc['reference']));
            }
        }

        if ($this->settings['invoice_date'] == 'order_date') {
            // Only set the document date if it should be equal to the order date.
            // Otherwise, Moneybird will automatically set the date upon sending.
            $doc[$doctype.'_date'] = $order->get_date_created()->date_i18n();
        }

        $doc['currency'] = $order->get_currency();
        $doc['prices_are_incl_tax'] = $prices_include_tax;

        // Collect document lines
        $doc_lines = array();
        $all_lines_zero = true;
        $ledger_accounts = $this->get_revenue_ledger_accounts();
        $projects = $this->get_projects();

        // Items
        $qty_factor = 1;
        $item_orders = $all_orders;
        if (($order_type == 'shop_order_refund') && !empty($parent_order)) {
            $non_refunded_amount = abs(abs(floatval($order->get_total())) - abs(floatval($parent_order->get_total())));
            if ($non_refunded_amount < 0.01) {
                // Take items, shipping, fees from parent order since full order is refunded
                $item_orders = array($parent_order);
                $qty_factor = -1;
            }
        }
        if (empty($moneybird_state)) {
            $order_idx = 0;
            foreach ($item_orders as $_order) {
                $row_order = 1000 * $order_idx++;
                if ($items = $this->get_order_items($_order, $tax_rate_mappings, $prices_include_tax)) {
                    if ($secondary_orders) {
                        $combined_invoice_order_heading = apply_filters(
                            'woocommerce_moneybird_combined_invoice_order_heading',
                            '*' . __('Order', 'woocommerce_moneybird') . ' ' . $_order->get_order_number() . '*',
                            $_order
                        );
                        if ($combined_invoice_order_heading) {
                            $doc_lines[] = array(
                                'description' => $combined_invoice_order_heading,
                                'price' => 0,
                                'row_order' => $row_order++
                            );
                        }
                    }
                    foreach ($items as $item) {
                        if (($item['price'] !== false) && ((float)$item['price'] == 0.0)) {
                            if (isset($this->settings['ignore_zero_items']) && ($this->settings['ignore_zero_items'] == 'yes')) {
                                continue;
                            }
                        }

                        $doc_line = array(
                            'amount' => $qty_factor * $item['qty'],
                            'description' => $item['description'],
                            'row_order' => $row_order++
                        );
                        if ($item['price'] !== false) {
                            $doc_line['price'] = ($prices_include_tax) ? $item['price'] + $item['tax'] : $item['price'];
                            $doc_line['tax_rate_id'] = $item['tax_rate_id'];
                        }

                        if (isset($item['period'])) {
                            $doc_line['period'] = $item['period'];
                        }
                        if ($item['ledger_account_id']) {
                            // Only specify revenue ledger account if the account id exists in the linked administration
                            if (isset($ledger_accounts[$item['ledger_account_id']])) {
                                $doc_line['ledger_account_id'] = substr($item['ledger_account_id'], 1);
                            }
                        }
                        if (isset($item['document_style_id'])) {
                            $doc['document_style_id'] = $item['document_style_id'];
                        }
                        if (isset($item['workflow_id'])) {
                            if ($order_type != 'shop_order_refund') {
                                $doc['workflow_id'] = $item['workflow_id'];
                            }
                        }
                        if (isset($item['project_id'])) {
                            $doc_line['project_id'] = $item['project_id'];
                        }
                        if (!empty($doc_line['description'])) {
                            $doc_lines[] = $doc_line;
                        }
                        $all_lines_zero = $all_lines_zero && ((float)$item['price'] == 0.0);
                        if (isset($item['sublines']) && !empty($item['sublines'])) {
                            foreach ($item['sublines'] as $subline) {

                                if (($subline['price'] !== false) && ((float)$subline['price'] == 0.0)) {
                                    if (isset($this->settings['ignore_zero_items']) && ($this->settings['ignore_zero_items'] == 'yes')) {
                                        continue;
                                    }
                                }

                                $doc_line = array(
                                    'amount' => $qty_factor * $subline['qty'],
                                    'description' => $subline['description'],
                                    'row_order' => $row_order++
                                );
                                if ($subline['price'] !== false) {
                                    $doc_line['price'] = ($prices_include_tax) ? $subline['price'] + $subline['tax'] : $subline['price'];
                                    $doc_line['tax_rate_id'] = $subline['tax_rate_id'];
                                }
                                if (isset($subline['period'])) {
                                    $doc_line['period'] = $subline['period'];
                                } elseif (isset($item['period'])) {
                                    $doc_line['period'] = $item['period'];
                                }
                                if ($subline['ledger_account_id']) {
                                    // Only specify revenue ledger account if the account id exists in the linked administration
                                    if (isset($ledger_accounts[$subline['ledger_account_id']])) {
                                        $doc_line['ledger_account_id'] = substr($subline['ledger_account_id'], 1);
                                    }
                                }
                                $doc_lines[] = $doc_line;
                                $all_lines_zero = $all_lines_zero && ((float)$subline['price'] == 0.0);
                            }
                        }
                    }
                }
            }
        }

        // Shipping
        if (empty($moneybird_state)) {
            $order_idx = 0;
            foreach ($item_orders as $_order) {
                $row_order = 1000 * $order_idx++ + 800;
                if (($items = $this->get_order_shipping($_order, $tax_rate_mappings)) !== false) {
                    $ledger_account_id = '';
                    if (isset($this->settings['shipping_ledger_account_id']) && isset($ledger_accounts[$this->settings['shipping_ledger_account_id']])) {
                        $ledger_account_id = substr($this->settings['shipping_ledger_account_id'], 1);
                    }
                    foreach ($items as $item) {
                        if ((float)$item['price'] == 0.0) {
                            // Skip shipping lines with subtotal 0
                            continue;
                        }

                        $doc_line = array(
                            'description' => $item['description'],
                            'price' => ($prices_include_tax) ? $item['price'] + $item['tax'] : $item['price'],
                            'tax_rate_id' => $item['tax_rate_id'],
                            'row_order' => $row_order++
                        );
                        if ($qty_factor < 0)  {
                            $doc_line['amount'] = $qty_factor;
                        }
                        if (isset($item['period'])) {
                            $doc_line['period'] = $item['period'];
                        }
                        if (isset($item['ledger_account_id']) && isset($ledger_accounts[$item['ledger_account_id']])) {
                            $doc_line['ledger_account_id'] = substr($item['ledger_account_id'], 1);
                        } elseif ($ledger_account_id) {
                            $doc_line['ledger_account_id'] = $ledger_account_id;
                        }
                        if (isset($item['wc_tax_rate_id']) && !empty($item['wc_tax_rate_id'])) {
                            $wc_tax_rate_id = $item['wc_tax_rate_id'];
                            if (isset($this->settings['tax_rate_' . $wc_tax_rate_id . '_ledger_account_id'])) {
                                if (!empty($this->settings['tax_rate_' . $wc_tax_rate_id . '_ledger_account_id'])) {
                                    $doc_line['ledger_account_id'] = substr($this->settings['tax_rate_' . $wc_tax_rate_id . '_ledger_account_id'], 1);
                                }
                            }
                        }
                        $doc_lines[] = $doc_line;
                        $all_lines_zero = false;
                    }
                }
            }
        }

        // Fees, coupons and gift cards
        if (empty($moneybird_state)) {
            $order_idx = 0;
            foreach ($item_orders as $_order) {
                $row_order = 1000 * $order_idx++ + 900;

                // Fees
                if (($items = $this->get_order_fees($_order, $tax_rate_mappings)) !== false) {
                    $ledger_account_id = '';
                    if (isset($this->settings['fees_ledger_account_id']) && isset($ledger_accounts[$this->settings['fees_ledger_account_id']])) {
                        $ledger_account_id = substr($this->settings['fees_ledger_account_id'], 1);
                    }
                    foreach ($items as $item) {
                        if ((float)$item['price'] == 0.0) {
                            continue;
                        } // Don't include fee lines with subtotal 0
                        $doc_line = array(
                            'description' => $item['description'],
                            'price' => ($prices_include_tax) ? $item['price'] + $item['tax'] : $item['price'],
                            'tax_rate_id' => $item['tax_rate_id'],
                            'row_order' => $row_order++
                        );
                        if ($qty_factor < 0)  {
                            $doc_line['amount'] = $qty_factor;
                        }
                        if (isset($item['period'])) {
                            $doc_line['period'] = $item['period'];
                        }
                        if (isset($item['ledger_account_id']) && isset($ledger_accounts[$item['ledger_account_id']])) {
                            $doc_line['ledger_account_id'] = substr($item['ledger_account_id'], 1);
                        } elseif ($ledger_account_id) {
                            $doc_line['ledger_account_id'] = $ledger_account_id;
                        }
                        $doc_lines[] = $doc_line;
                        $all_lines_zero = false;
                    }
                }

                // Coupons
                foreach ($this->get_order_coupon_redemptions($_order) as $doc_line) {
                    $doc_line['row_order'] = $row_order++;
                    $doc_lines[] = $doc_line;
                    $all_lines_zero = false;
                }

                // Gift cards
                foreach ($this->get_order_gift_card_redemptions($_order) as $doc_line) {
                    $doc_line['row_order'] = $row_order++;
                    $doc_lines[] = $doc_line;
                    $all_lines_zero = false;
                }
            }

            if (count($doc_lines) == 0) {
                $this->log("Cancel generation: no lines in document.");
                $this->release_generation_lock($order_id, $generation_lock);
                $order->delete_meta_data($queue_meta_key);
                if ($secondary_orders) {
                    $order->delete_meta_data('moneybird_combined_order_ids');
                }
                $order->save();
                if ($doctype=='invoice' && $order_type=='shop_order_refund') {
                    $this->add_admin_notice(__('A credit invoice can only be created if the refunded items are specified. If you only specified the total refund amount, you should manually create a credit invoice to make sure that the appropriate tax rates are applied.', 'woocommerce_moneybird'), true);
                }
                return false;
            }

            // Price rounding
            for ($i = 0; $i < count($doc_lines); $i++) {
                if (empty($doc_lines[$i]['price'])) {
                    // No price to round
                    continue;
                }
                if (empty($doc_lines[$i]['amount']) || (intval($doc_lines[$i]['amount'])==0)) {
                    // Amount is empty or zero
                    $doc_lines[$i]['price'] = round($doc_lines[$i]['price'], 2);
                    continue;
                }
                $amount = floatval($doc_lines[$i]['amount']);
                $rounded_line_total = round($doc_lines[$i]['price'] * $amount, 2);
                for ($num_decimals=2; $num_decimals<=4; $num_decimals++) {
                    $rounded_price = round($doc_lines[$i]['price'], $num_decimals);
                    if (round($rounded_price * $amount, 2) == $rounded_line_total) {
                        // Rounding on $num_decimals does not introduce error in calculated line total
                        $doc_lines[$i]['price'] = $rounded_price;
                        continue;
                    }
                    if ($num_decimals == 4) {
                        // Round on 4 decimals even if it introduces a rounding error
                        $doc_lines[$i]['price'] = $rounded_price;
                    }
                }
            }
        }

        // Maybe show total order discount
        if (isset($this->settings['specify_discounts']) && stripos($this->settings['specify_discounts'], 'order') !== false) {
            if ($prices_include_tax) {
                $order_discount = $order->get_total_discount();
            } else {
                $order_discount = $order->get_total_discount(true);
            }
            if ($order_discount > 0) {
                $discount_line = __('Total discount:', 'woocommerce_moneybird') . ' ';
                $discount_line .= wc_price($order_discount, array('currency' => $order->get_currency()));
                if ($prices_include_tax) {
                    $discount_line .= ' ' . __('incl. tax', 'woocommerce_moneybird');
                } else {
                    $discount_line .= ' ' . __('excl. tax', 'woocommerce_moneybird');
                }
                $doc_lines[] = array(
                    'description' => $discount_line,
                    'price' => '',
                    'row_order' => $row_order++
                );
            }
        }

        // Make sure the workflow is valid
        if (isset($doc['workflow_id'])) {
            if ($doc['workflow_id'] == 'auto') {
                unset($doc['workflow_id']);
            } else {
                $workflows = $this->get_workflows(false, $is_estimate ? 'estimate' : 'invoice');
                if (count($workflows) > 1) {
                    // Only verify workflows if we have a valid list of available workflows.
                    // Note: the up-to-date list might not be available due to API request throttling.
                    if (!isset($workflows[$doc['workflow_id']])) {
                        $this->log("Unsetting workflow id " . $doc['workflow_id'] . " because it is unavailable. Available workflows are:\n" . print_r($workflows, true));
                        unset($doc['workflow_id']);
                    }
                }
            }
        }

        // Make sure the document style is valid
        if (isset($doc['document_style_id'])) {
            $document_styles = $this->get_document_styles();
            if (!isset($document_styles[''])) {
                // Only verify workflows if we have a valid list of document styles.
                if (!isset($document_styles[$doc['document_style_id']])) {
                    $this->log("Unsetting document style id " . $doc['document_style_id'] . " because it is unavailable. Available document styles are:\n" . print_r($document_styles, true));
                    unset($doc['document_style_id']);
                }
            }
        }

        // Try to fix case in which document total and order total do
        // not match due to different rounding strategies.
        // This fix only applies if prices are inclusive of tax, since
        // we can only observe the Moneybird tax calculation after the document
        // has been submitted.
        $no_tax_rate_id = $this->get_notax_mb_taxrate();
        if ($doc['prices_are_incl_tax'] && $no_tax_rate_id) {
            if (isset($this->settings['rounding_error_correction_line'])
                && ($this->settings['rounding_error_correction_line'] == 'yes')
                && empty($secondary_orders)) {

                $doc_total = 0.0;
                foreach ($doc_lines as $doc_line) {
                    if (!empty($doc_line['price']) && is_numeric($doc_line['price'])) {
                        if (!empty($doc_line['amount'])) {
                            $doc_total += floatval($doc_line['amount']) * floatval($doc_line['price']);
                        } else {
                            $doc_total += floatval($doc_line['price']);
                        }
                    }
                }
                $diff = round($total_amount - $doc_total, 2);

                if ((0.01 <= abs($diff)) && (abs($diff) <= 0.07)) {
                    // Add line to correct rounding error
                    $correction_line = array(
                        'price'       => $diff,
                        'description' => __('Rounding error correction', 'woocommerce_moneybird'),
                        'tax_rate_id' => $no_tax_rate_id,
                    );
                    if (!empty($this->settings['rounding_error_correction_line_ledger_account_id'])) {
                        $correction_line['ledger_account_id'] = substr($this->settings['rounding_error_correction_line_ledger_account_id'], 1);
                    }
                    $doc_lines[] = $correction_line;
                }
            }
        }

        // Maybe apply project
        if ($this->settings['project_id']) {
            // Apply project to all lines
            for ($i=0; $i<count($doc_lines); $i++) {
                if (isset($doc_lines[$i]['project_id'])) {
                    // Project has already been assigned.
                    // Replace by default project if the assigned project does not exist.
                    $project_id = $doc_lines[$i]['project_id'];
                    if (!isset($projects['s'.$project_id])) {
                        $doc_lines[$i]['project_id'] = substr($this->settings['project_id'], 1);
                    }
                } else {
                    $doc_lines[$i]['project_id'] = substr($this->settings['project_id'], 1);
                }
            }
        }

        // Make sure assigned projects exist and do not conflict with assigned ledger account
        for ($i=0; $i<count($doc_lines); $i++) {
            if (isset($doc_lines[$i]['project_id'])) {
                // Project has already been assigned.
                // Replace by default project if the assigned project does not exist.
                $project_id = $doc_lines[$i]['project_id'];
                if (!isset($projects['s'.$project_id])) {
                    unset($doc_lines[$i]['project_id']);
                }

                // Unset project id if a balance sheet ledger account is assigned
                if (isset($doc_lines[$i]['project_id']) && isset($doc_lines[$i]['ledger_account_id'])) {
                    $ledger_account_details = $this->get_ledger_account_details($doc_lines[$i]['ledger_account_id']);
                    $balance_sheet_account_types = array('non_current_assets', 'current_assets', 'equity', 'provisions',
                                                         'non_current_liabilities', 'current_liabilities');
                    if (isset($ledger_account_details['account_type'])) {
                        if (in_array($ledger_account_details['account_type'], $balance_sheet_account_types)) {
                            unset($doc_lines[$i]['project_id']);
                        }
                    }
                }
            }
        }

        // Maybe add refund reason to document
        if ($order_type == 'shop_order_refund') {
            if (method_exists($order, 'get_refund_reason')) {
                $refund_reason = $order->get_refund_reason();
            } else {
                $refund_reason = $order->get_meta('_refund_reason', true);
            }
            if ($refund_reason) {
                $doc_lines[] = array(
                    'description' => __('Reason for refund', 'woocommerce_moneybird') . ': ' . $refund_reason . '.',
                );
            }
        }

        // Maybe add VAT reverse-charge text
        if ($this->settings['reverse_charge_text'] && $this->settings['tax_rate_reverse_charge']) {
            $reverse_charge_applied = false;
            foreach ($doc_lines as $doc_line) {
                if (!isset($doc_line['tax_rate_id'])) {
                    continue;
                }
                if ($doc_line['tax_rate_id'] == $this->settings['tax_rate_reverse_charge']) {
                    if ($this->is_vat_exempt($order)) {
                        $reverse_charge_applied = true;
                        break;
                    }
                }
            }
            if ($reverse_charge_applied) {
                $doc_lines[] = array(
                    'description' => $this->settings['reverse_charge_text'],
                );
            }
        }

        // Extra fields
        $custom_fields = array();
        $custom_field_mappings = $this->get_custom_field_mappings();
        $custom_field_type_filter = ($is_estimate) ? 'e' : 's';
        foreach ($custom_field_mappings as $mapping) {
            if (substr($mapping['mb'], 0, 1) == $custom_field_type_filter) {
                if (($order_type == 'shop_order_refund') && !empty($parent_order)) {
                    $val = $this->get_custom_field_value($parent_order, $mapping['wc'], $secondary_orders);
                } else {
                    $val = $this->get_custom_field_value($order, $mapping['wc'], $secondary_orders);
                }
                if ($val) {
                    $custom_fields[] = array(
                        'id'    => substr($mapping['mb'], 1),
                        'value' => $val
                    );
                }
            }
        }
        if (!empty($custom_fields)) {
            $doc['custom_fields_attributes'] = $custom_fields;
        }

        if (empty($moneybird_state)) {
            // If all line amounts are 0.0, don't save the document
            if ($all_lines_zero && ($this->settings['ignore_zero_orders'] == 'yes')) {
                $this->log("Cancel document generation: all lines have amount 0.0");
                $this->add_admin_notice(__('Moneybird document was not created since all lines have amount 0.0', 'woocommerce_moneybird'), true);
                $this->release_generation_lock($order_id, $generation_lock);
                $order->delete_meta_data($queue_meta_key);
                if ($secondary_orders) {
                    $order->delete_meta_data('moneybird_combined_order_ids');
                }
                $order->save();
                return false;
            }

            // Fix the row order
            $last_row_order = 0;
            for ($i = 0; $i < count($doc_lines); $i++) {
                if (!isset($doc_lines[$i]['row_order'])) {
                    $doc_lines[$i]['row_order'] = $last_row_order + 1;
                }
                $last_row_order = intval($doc_lines[$i]['row_order']);
            }

            $doc['details_attributes'] = $doc_lines;
            $sequence_id = trim(apply_filters('woocommerce_moneybird_'.$doctype.'_sequence_id', '', $order));
            if ($sequence_id) {
                $doc[$doctype.'_sequence_id'] = $sequence_id;
            }

            // Reuse existing Moneybird contact or create a new contact
            $contact = $this->get_or_create_moneybird_contact($order_meta);
            if (!$contact) {
                if ($this->mb_api->request_limit_reached) {
                    $this->postpone_generation($doctype, $order);
                    $this->release_generation_lock($order_id, $generation_lock);
                } else {
                    $this->log("Cancel $doctype generation: error finding or creating Moneybird contact");
                    $this->log($this->mb_api->getLastError());
                    $this->add_admin_notice('Cannot find or create Moneybird contact. Please check your Moneybird API settings.', true);
                    do_action('woocommerce_moneybird_generate_' . $doctype . '_error', $order, 'Cannot find or create Moneybird contact');
                    $this->release_generation_lock($order_id, $generation_lock);
                    $order->delete_meta_data($queue_meta_key);
                    if ($secondary_orders) {
                        $order->delete_meta_data('moneybird_combined_order_ids');
                    }
                    $order->save();
                }
                return false;
            }
            $doc['contact_id'] = $contact->id;
            if (count($contact->contact_people) == 1) {
                $doc['contact_person_id'] = $contact->contact_people[0]->id;
            } elseif (count($contact->contact_people) > 1) {
                $contact_details = $this->get_order_contact_details($order_meta);
                foreach ($contact->contact_people as $contact_person) {
                    if ($contact_person->firstname == $contact_details['firstname']) {
                        if ($contact_person->lastname == $contact_details['lastname']) {
                            $doc['contact_person_id'] = $contact_person->id;
                        }
                    }
                }
            }
            if ($this->settings['respect_contact_workflow'] == 'yes') {
                $contact_workflow_id = ($is_estimate) ? $contact->estimate_workflow_id : $contact->invoice_workflow_id;
                if (!empty($contact_workflow_id)) {
                    // Use the preferred workflow specified on the contact
                    $doc['workflow_id'] = $contact_workflow_id;
                }
            }

            if ($order_type == 'shop_order_refund') {
                $doc = apply_filters('woocommerce_moneybird_credit_invoice', $doc, $order, $parent_order);
            } else {
                $doc = apply_filters('woocommerce_moneybird_'.$doctype, $doc, $order, $secondary_orders);
            }
            if (empty($doc)) {
                $this->log("Document generation canceled due to woocommerce_moneybird_$doctype filter.");
                $this->release_generation_lock($order_id, $generation_lock);
                $order->delete_meta_data($queue_meta_key);
                if ($secondary_orders) {
                    $order->delete_meta_data('moneybird_combined_order_ids');
                }
                $order->save();
                return false;
            }

            $this->log("Submitting document: \n" . print_r($doc, true));

            if ($is_estimate) {
                $mb_doc = $this->mb_api->createEstimate($doc);
            } else {
                $mb_doc = $this->mb_api->createSalesInvoice($doc);
            }

            if (!$mb_doc) {
                if ($this->mb_api->request_limit_reached) {
                    $this->postpone_generation($doctype, $order);
                    $this->release_generation_lock($order_id, $generation_lock);
                } else {
                    $this->log('Error saving document: ' . $this->mb_api->getLastError());
                    $this->add_admin_notice('Moneybird error: ' . $this->mb_api->getLastError(), true);
                    do_action('woocommerce_moneybird_generate_'.$doctype.'_error',
                        $order, 'The generated document could not be saved');
                    $this->release_generation_lock($order_id, $generation_lock);
                    $order->delete_meta_data($queue_meta_key);
                    if ($secondary_orders) {
                        $order->delete_meta_data('moneybird_combined_order_ids');
                    }
                    $order->save();
                }
                return false;
            }
            $order->update_meta_data('moneybird_'.$doctype.'_id', $mb_doc->id);
            $order->save();
            foreach ($all_orders as $_order) {
                $_order->update_meta_data('moneybird_'.$doctype.'_id', $mb_doc->id);
                $_order->save_meta_data();
            }
            $moneybird_state = 'saved';
        }

        // Maybe add rounding error correction line if document total differs from order total
        $no_tax_rate_id = $this->get_notax_mb_taxrate();
        if ($no_tax_rate_id
            && isset($this->settings['rounding_error_correction_line'])
            && ($this->settings['rounding_error_correction_line'] == 'yes')
            && empty($secondary_orders)) {

            $diff = round($total_amount - $mb_doc->total_price_incl_tax, 2);

            if ((0.01 <= abs($diff)) && (abs($diff) <= 0.07)) {
                // Add line to correct rounding error
                $correction_line = array(
                    'price'       => $diff,
                    'description' => __('Rounding error correction', 'woocommerce_moneybird'),
                    'tax_rate_id' => $no_tax_rate_id,
                    'row_order'   => 100000
                );
                if ($is_estimate) {
                    $edited_doc = $this->mb_api->updateEstimate(
                        $mb_doc->id, array('details_attributes' => array($correction_line)));
                } else {
                    $edited_doc = $this->mb_api->updateSalesInvoice(
                            $mb_doc->id, array('details_attributes' => array($correction_line)));
                }
                if ($edited_doc) {
                    $mb_doc = $edited_doc;
                }
            }
        }

        /******************************************************************************
         * STAGE 2: mark as sent & register payment
         ******************************************************************************/
        $sendmode = apply_filters('woocommerce_moneybird_sendmode', $gen_settings['sendmode'], $order, $mb_doc);
        if (!in_array($sendmode, array('default', 'Manual', 'Email', 'Simplerinvoicing'))) {
            $sendmode = 'draft';
        }

        $mb_doc_sent = ($mb_doc->state != 'draft');
        if (!$is_estimate && ($moneybird_state == 'saved') && ($sendmode != 'draft') && ($total_amount != 0.0)) {
            // Register invoice payment?
            if ($all_orders_paid && apply_filters('woocommerce_moneybird_register_payment', $gen_settings['register_payment'], $order)) {
                if (!$mb_doc_sent) {
                    // Mark invoice as sent manually
                    $this->log("Activating invoice");
                    if (!$this->mb_api->sendSalesInvoice($mb_doc->id, array('delivery_method' => 'Manual'))) {
                        if ($this->mb_api->request_limit_reached) {
                            $this->postpone_generation($doctype, $order, 'saved');
                            $this->release_generation_lock($order_id, $generation_lock);
                        } else {
                            $this->log("Error sending invoice: " . $this->mb_api->getLastError());
                            $this->add_admin_notice('Moneybird error (payment): ' . $this->mb_api->getLastError(), true);
                            do_action('woocommerce_moneybird_generate_invoice_error', $order, 'The generated invoice could not be marked as sent');
                            $this->release_generation_lock($order_id, $generation_lock);
                            $order->delete_meta_data($queue_meta_key);
                            if ($secondary_orders) {
                                $order->delete_meta_data('moneybird_combined_order_ids');
                            }
                            $order->save();
                        }
                        return false;
                    }
                    $mb_doc_sent = true;
                }

                // Register payment(s)
                // Note that any refunds are combined with the original payment so that the invoice is 
                // marked as paid through a single payment. Partial payments are registered separately.
                foreach ($this->get_invoice_payments($_order, $mb_doc) as $payment) {
                    $this->log("Registering payment: " . print_r($payment, true));
                    if (!$this->mb_api->createSalesInvoicePayment($mb_doc->id, $payment)) {
                        if ($this->mb_api->request_limit_reached) {
                            $this->postpone_generation($doctype, $order, 'saved');
                            $this->release_generation_lock($order_id, $generation_lock);
                        } else {
                            $this->log("Error registering payment: " . $this->mb_api->getLastError());
                            $this->add_admin_notice('Moneybird error (payment): ' . $this->mb_api->getLastError(), true);
                            do_action('woocommerce_moneybird_generate_invoice_error', $order, 'Could not register payment for the generated invoice');
                            $this->release_generation_lock($order_id, $generation_lock);
                            $order->delete_meta_data($queue_meta_key);
                            if ($secondary_orders) {
                                $order->delete_meta_data('moneybird_combined_order_ids');
                            }
                            $order->save();
                        }
                        return false;
                    }
                }
            }
        }
        if ($moneybird_state == 'saved') {
            $moneybird_state = 'paid';
        }

        // Maybe send the document
        if (($moneybird_state == 'paid') && ($sendmode != 'draft')) {
            if (($sendmode == 'Manual' && !$mb_doc_sent) ||
                ($sendmode != 'Manual')) {

                $this->log("Sending document: " . $sendmode);
                if ($sendmode != 'default') {
                    $send_params = array('delivery_method' => $sendmode);
                } else {
                    $send_params = array();
                }

                $send_params = apply_filters('woocommerce_moneybird_'.$doctype.'_send_parameters', $send_params, $order, $mb_doc);
                if ($is_estimate) {
                    $send_result = $this->mb_api->sendEstimate($mb_doc->id, $send_params);
                } else {
                    $send_result = $this->mb_api->sendSalesInvoice($mb_doc->id, $send_params);
                }
                if (!$send_result) {
                    if ($this->mb_api->request_limit_reached) {
                        $this->postpone_generation($doctype, $order, 'paid');
                        $this->release_generation_lock($order_id, $generation_lock);
                    } else {
                        $this->log("Error sending document: " . $this->mb_api->getLastError());
                        $this->add_admin_notice('Moneybird error (send): '.$this->mb_api->getLastError(), true);
                        $order->add_order_note(__('Moneybird document was not sent to customer due to error.', 'woocommerce_moneybird'));
                        do_action('woocommerce_moneybird_generate_'.$doctype.'_error', $order, 'Could not send the generated document.');
                        $this->release_generation_lock($order_id, $generation_lock);
                        $order->delete_meta_data($queue_meta_key);
                        if ($secondary_orders) {
                            $order->delete_meta_data('moneybird_combined_order_ids');
                        }
                        $order->save();
                    }
                    return false;
                }
            }
        }

        do_action('woocommerce_moneybird_after_'.$doctype.'_generate', $order, $mb_doc, $secondary_orders);

        // Add order note and admin notice
        $this->log("Document generated successfully");
        $this->release_generation_lock($order_id, $generation_lock);
        $order->delete_meta_data('moneybird_state');
        $order->delete_meta_data($queue_meta_key);
        if ($secondary_orders) {
            $order->delete_meta_data('moneybird_combined_order_ids');
        }
        $order->save_meta_data();
        if ($parent_order) {
            $parent_order->add_order_note(sprintf(__('Moneybird credit invoice generated for refund #%d.', 'woocommerce_moneybird'), $order_id));
        } else {
            $order->add_order_note(sprintf(
                    __('Moneybird %s generated.', 'woocommerce_moneybird'),
                    __($doctype, 'woocommerce_moneybird')
            ));
            foreach ($secondary_orders as $_order) {
                $_order->add_order_note(sprintf(
                        __('Moneybird %s generated.', 'woocommerce_moneybird'),
                        __($doctype, 'woocommerce_moneybird')
                ));
            }
            $this->add_admin_notice(sprintf(
                __('Moneybird %s generated successfully.', 'woocommerce_moneybird'),
                __($doctype, 'woocommerce_moneybird')
            ));
        }

        return true;
    }

    function acquire_generation_lock($order_id) {
        // Try to acquire an exclusive lock for document generation
        // Possible return values:
        //   - true if the locking mechanism is not available
        //   - false if the lock could not be acquired
        //   - a resource object if the lock could be acquired
        if (defined('INVOICES_DIR_MISSING')) {
            return true;
        }
        try {
            $filename = INVOICES_DIR . DIRECTORY_SEPARATOR . $order_id . '.lock';
            if (file_exists($filename)) {
                if (filemtime($filename) < (time() - 60)) {
                    unlink($filename);
                }
            }
            if (file_exists($filename)) {
                return false;
            }

            $fp = fopen($filename, "w");
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                fclose($fp);
                return false;
            }
            fwrite($fp, (string) time());
            fflush($fp);

            return $fp;
        } catch (Exception $e) {
            return true;
        }
    }

    function release_generation_lock($order_id, $fp) {
        try {
            if (($fp !== true) && ($fp !== false)) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            if (defined('INVOICES_DIR_MISSING')) {
                return;
            }
            $filename = INVOICES_DIR . DIRECTORY_SEPARATOR . $order_id . '.lock';
            if (file_exists($filename)) {
               unlink($filename);
            }
        } catch (Exception $e) {
            return;
        }
    }

    function get_payment_transaction_id($order) {
        // Try to get a payment transaction id
        // If $order is a refund, try to get the transaction id from the parent order
        $order_type = is_callable(array($order, 'get_type')) ? $order->get_type() : 'shop_order';
        if ($order_type == 'shop_order_refund') {
            $order = wc_get_order($order->get_parent_id());
        }
        if (!$order) {
            return false;
        }
        foreach (array('getter', 'get_meta', 'post_meta') as $method) {
            foreach (array('transaction_id', 'mollie_payment_id') as $field) {
                $transaction_id = false;
                $getter = 'get_' . $field;
                if (($method == 'getter') && method_exists($order, $getter)) {
                    $transaction_id = $order->{$getter}();
                } elseif (($method == 'get_meta') && !method_exists($order, $getter)) {
                    $transaction_id = $order->get_meta('_' . $field, true);
                } elseif ($method == 'post_meta') {
                    $transaction_id = get_post_meta($order->get_id(), '_' . $field, true);
                }

                if ($transaction_id && !empty($transaction_id)) {
                    if (strpos($transaction_id, 'tr_') === 0) {
                        return $transaction_id;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Build an array of Moneybird payments that need to be registered.
     * An order can have multiple payments in case of one-click upsells.
     * @param WC_Order $order
     * @param $mb_doc
     * @return array
     */
    function get_invoice_payments($order, $mb_doc) {
        $exclude_transaction_ids = array(); // Exclude transactions that are already registered
        if ($mb_doc) {
            $primary_amount = round(floatval($mb_doc->total_unpaid), 2);
            foreach ($mb_doc->payments as $payment) {
                if (isset($payment->transaction_identifier) && !empty($payment->transaction_identifier)) {
                    $exclude_transaction_ids[] = $payment->transaction_identifier;
                }
            }
        } else {
            $primary_amount = round(floatval($order->get_total()), 2);
        }
        $primary_amount = apply_filters('woocommerce_moneybird_payment_amount', $primary_amount, $order);

        $payments = array();
        $payment_date = date("Y-m-d");
        if ($this->settings['invoice_date'] == 'order_date') {
            $order_paid_date = $order->get_date_paid();
            if ($order_paid_date) {
                $payment_date = $order_paid_date->date_i18n();
            } else {
                $payment_date = $order->get_date_created()->date_i18n();
            }
        }

        // Upsell payments
        foreach (WCMoneyBird\get_upsell_transactions($order) as $transaction_id => $amount) {
            $amount = round($amount, 2);
            if ($amount >= 0.01 && $amount <= $primary_amount) {
                if (!in_array($transaction_id, $exclude_transaction_ids)) {
                    $amount = round($amount, 2);
                    $payments[] = array(
                        'payment_date'              => $payment_date,
                        'price'                     => sprintf('%.2f', $amount),
                        'transaction_identifier'    => $transaction_id
                    );
                    $primary_amount -= $amount;
                }
            }
        }

        // Primary payment
        if (($primary_amount > 0.0) || ($order->get_type() == 'shop_order_refund'))  {
            $payment = array(
                'payment_date'  => $payment_date,
                'price'         => sprintf('%.2f', $primary_amount)
            );
            $transaction_id = $this->get_payment_transaction_id($order);
            if ($transaction_id) {
                $payment['transaction_identifier'] = $transaction_id;
            }
            if (empty($transaction_id) || !in_array($transaction_id, $exclude_transaction_ids)) {
                $payments[] = $payment;
            }
        }

        // Maybe convert currencies
        if ($mb_doc && ($mb_doc->currency != 'EUR')) {
            $exchange_rate = $this->get_currency_rate($mb_doc->currency);
            foreach ($payments as $i => $payment) {
                $payments[$i]['price_base'] = round(floatval($payment['price']) * $exchange_rate, 2);
            }
        }

        return apply_filters('woocommerce_moneybird_invoice_payments', $payments, $order, $mb_doc);
    }

    function register_payment($order_id) {
        // Register a payment for an order that already has an invoice.
        // If payment registration is enabled, this function is called every time a payment is completed (for all orders)
        $order = wc_get_order($order_id);
        if (!$order) return;
        $order_type = is_callable(array($order, 'get_type')) ? $order->get_type() : 'shop_order';
        if (!in_array($order_type, array('shop_order', 'wcdp_payment'))) return;
        if (empty($order->get_date_paid())) return; // Order should be paid
        if ($order->get_meta('moneybird_invoice_id', true) == '') return; // Order should have Moneybird invoice
        if (!$this->load_api_connector()) return; // Moneybird API should be loaded

        $register_payment = apply_filters('woocommerce_moneybird_register_payment', true, $order);
        if (!$register_payment) {
            return;
        }
        $this->log("Registering invoice payment for order " . $order_id);

        // Get the Moneybird invoice
        $invoice = $this->mb_api->getSalesInvoice($order->get_meta('moneybird_invoice_id', true));
        $payment_amount = apply_filters('woocommerce_moneybird_payment_amount', $invoice->total_unpaid, $order);
        if ($invoice) {
            if ($invoice->state == 'open' || $invoice->state == 'late') {
                // Register payment
                $payment_date = date("Y-m-d");
                if ($this->settings['invoice_date'] == 'order_date') {
                    $order_paid_date = $order->get_date_paid();
                    if ($order_paid_date) {
                        $payment_date = $order_paid_date->date_i18n();
                    } else {
                        $payment_date = $order->get_date_created()->date_i18n();
                    }
                }
                $payment = array(
                    'payment_date'   => $payment_date,
                    'price'          => $payment_amount,
                );
                $transaction_id = $this->get_payment_transaction_id($order);
                if ($transaction_id) {
                    $payment['transaction_identifier'] = $transaction_id;
                }
                if ($invoice->currency != 'EUR') {
                    $exchange_rate = $this->get_currency_rate($invoice->currency);
                    $payment['price_base'] = round(floatval($payment['price']) * $exchange_rate, 2);
                }
                $this->log("Submitting payment object: \n" . print_r($payment, true));
                $this->mb_api->createSalesInvoicePayment($invoice->id, $payment);
            }
        }
    }

    function delete_invoice_payments($order_id) {
        // Delete any invoice payments of the invoice linked to $order_id.
        $order = wc_get_order($order_id);
        if ($order) {
            $invoice_id = $order->get_meta('moneybird_invoice_id', true);
            if ($invoice_id && $this->load_api_connector()) {
                $invoice = $this->mb_api->getSalesInvoice($invoice_id);
                if ($invoice) {
                    $this->log("Deleting invoice payments for failed order " . $order_id);
                    foreach ($invoice->payments as $payment) {
                        $this->mb_api->deleteSalesInvoicePayment($invoice_id, $payment->id);
                    }
                }
            }
        }
    }

    function clean($string) {
        // Remove all whitespace, convert to lower case
        return strtolower(str_replace(' ', '', $string));
    }


    function add_admin_notice($msg, $error=false) {
        // Add an admin notice that should be displayed.
        $notices = get_option('woocommerce_moneybird2_deferred_admin_notices', array('updated'=>'', 'error'=>''));
        if ($error) {
            $notices['error'] = $msg;
        } else {
            $notices['updated'] = $msg;
        }
        update_option('woocommerce_moneybird2_deferred_admin_notices', $notices);
    }

    function admin_notices() {
        // Display any admin notices saved in the option "woocommerce_moneybird2_deferred_admin_notices" and clear the option
        $notices = get_option('woocommerce_moneybird2_deferred_admin_notices', array('updated'=>'', 'error'=>''));

        if ($notices['error'] != '') { ?>
            <div class="error">
                <p><?php echo $notices['error']; ?></p>
            </div>
        <?php }
        if ($notices['updated'] != '') { ?>
            <div class="updated">
                <p><?php echo $notices['updated']; ?></p>
            </div>
        <?php }
        delete_option('woocommerce_moneybird2_deferred_admin_notices');
    }
}
