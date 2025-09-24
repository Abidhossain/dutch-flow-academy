<?php

namespace ExtensionTree\WCMoneyBird;

// Number of seconds after which webhooks get deleted to prevent outdated ones from staying active
define('WC_MONEYBIRD_WEBHOOK_LIFETIME', 20 * 24 * 3600);

// Number of seconds after which a webhooks get deleted and recreated to refresh the creation timestamp
define('WC_MONEYBIRD_WEBHOOK_RENEW_AFTER', 10 * 24 * 3600);


function get_webhook_url($exclude_timestamp = false) {
    // Return a valid Moneybird webhook url or false if it is not available
    // A valid webhook url must use https
    if ($exclude_timestamp) {
        $url = get_rest_url(null, 'wcmb/webhook');
    } else {
        $url = get_rest_url(null, 'wcmb/webhook/' . time());
    }
    if (strpos($url, 'https://') === 0) {
        return $url;
    }
    return false;
}


function update_webhooks($delete = false, $force_create = false) {
    // Update Moneybird webhooks.
    // Outdated webhooks will be deleted.
    // If $delete is true, the webhook for the current site is deleted if it exists.
    // If $delete is false, the webhook for the current site is (re)created is needed.
    // If $force_create is true, the webhook for the current site is created even if it already exists.
    // Returns false in case of failure, true otherwise.
    $wcmb = WCMB();
    if (!$wcmb) {
        return false; // Plugin not available
    }
    $mb_api = $wcmb->load_api_connector();
    if (!$mb_api) {
        return false; // API not available
    }

    // Delete outdated webhooks
    $webhooks = $mb_api->request('webhooks', 'GET');
    if (!is_array($webhooks)) {
        return false; // Invalid response
    }
    $site_webhook_url = get_webhook_url(true);
    $site_webhook_exists = false;
    foreach ($webhooks as $webhook) {
        if (strpos($webhook->url, '/wcmb/webhook') === false) {
            continue; // Webhook is not related to this plugin
        }
        $created_on = intval(substr($webhook->url, strrpos($webhook->url, '/') + 1));
        if ($created_on <= 1) {
            continue; // Invalid timestamp
        }
        if ($site_webhook_url && (strpos($webhook->url, $site_webhook_url) !== false)) {
            // Webhook is for this site
            if ($delete || $force_create) {
                $mb_api->request('webhooks/' . $webhook->id, 'DELETE');
                $wcmb->log('Webhook deleted: ' . $webhook->id);
            } elseif ($created_on < time() - WC_MONEYBIRD_WEBHOOK_RENEW_AFTER) {
                $mb_api->request('webhooks/' . $webhook->id, 'DELETE');
                $wcmb->log('Webhook deleted (up for renewal): ' . $webhook->id);
            } else {
                $site_webhook_exists = true;
            }
        } else {
            // Webhook for another site
            if ($created_on < time() - WC_MONEYBIRD_WEBHOOK_LIFETIME) {
                $mb_api->request('webhooks/' . $webhook->id, 'DELETE');
                $wcmb->log('Webhook deleted (expired): ' . $webhook->id);
            }
        }
    }

    // Maybe create webhook for this site
    if (!$delete && !$site_webhook_exists && $site_webhook_url) {
        $params = array(
            'url' => get_webhook_url(),
            'enabled_events' => array(
                'administration_removed', 'administration_suspended',
                'document_style_destroyed',
                'estimate_mark_accepted', 'estimate_mark_billed', 'estimate_mark_rejected',
                'ledger_account_deactivated', 'ledger_account_destroyed',
                'project_archived', 'project_destroyed',
                'sales_invoice_destroyed', 'sales_invoice_merged',
                'sales_invoice_state_changed_to_paid',
                'tax_rate_deactivated', 'tax_rate_destroyed',
                'workflow_deactivated', 'workflow_destroyed'
            )
        );
        $response = $mb_api->request('webhooks', 'POST', $params);
        if ($response && is_object($response) && !empty($response->token)) {
            $wcmb->log('Webhook (re)created. Id: ' . $response->id . ', url: ' . $response->url);
            update_option('woocommerce_moneybird2_webhook_token', $response->token);
        }
    }
    return true;
}

add_action('wc_mb_update_webhooks', '\ExtensionTree\WCMoneyBird\update_webhooks');

function handle_webhook($request) {
    $payload = file_get_contents("php://input");
    if (!empty($payload)) {
        $payload = json_decode($payload);
    }
    $wcmb = WCMB();
    if (empty($wcmb)
        || empty($payload)
        || !is_object($payload)
        || empty($payload->administration_id)
        || empty($payload->action)) {

        return new \WP_REST_Response(null, empty($wcmb) ? 404 : 400);
    }
    if (empty($wcmb->settings['administration_id'])
        || ($wcmb->settings['administration_id'] != $payload->administration_id)) {

        return new \WP_REST_Response(null, 404);
    }
    if ($payload->action != 'test_webhook') {
        if (!empty($payload->webhook_token)) {
            $token = $payload->webhook_token;
        } elseif (!empty($payload->token)) {
            $token = $payload->token;
        } else {
            $token = 'invalid';
        }
        $saved_token = get_option('woocommerce_moneybird2_webhook_token', '');
        if (empty($saved_token) || trim($token) != trim($saved_token)) {
            // Invalid token, refuse webhook
            return new \WP_REST_Response(null, 403);
        }
    }

    do_action('woocommerce_moneybird_webhook', $payload, $wcmb);
    do_action('woocommerce_moneybird_webhook_' . $payload->action, $payload, $wcmb);

    return new \WP_REST_Response('Thanks!', 200);
}


add_action('rest_api_init', function ($wp_rest_server = null) {
    register_rest_route('wcmb', '/webhook/(?P<timestamp>\d+)', array(
        'methods' => array('GET', 'POST'),
        'callback' => '\ExtensionTree\WCMoneyBird\handle_webhook',
        'permission_callback' => '__return_true'
    ));
});


/****************************************************************
 * Below are the actual webhook handlers for specific events
 ****************************************************************/

function webhook_handle_entity_deletions($payload, $wcmb) {
    $action = $payload->action;
    $settings_updated = false;
    if ($action == 'administration_removed' || $action == 'administration_suspended') {
        // Unlink from Moneybird administration, remove all transients and settings
        $wcmb->log('Webhook: unlinking from Moneybird administration due to ' . $action);
        update_option('woocommerce_moneybird2_settings', array());
        $wcmb->settings = array();
        $wcmb->init_settings();
        $wcmb->delete_all_transients();
        return;
    } elseif ($action == 'document_style_destroyed') {
        foreach ($wcmb->settings as $key => $val) {
            if (strpos($key, 'document_style_id') !== false) {
                if (strpos($val, $payload->entity_id) !== false) {
                    $wcmb->log('Webhook: document style deleted, clearing setting: ' . $key);
                    $wcmb->settings[$key] = '';
                    $settings_updated = true;
                }
            }
        }
        $wcmb->log('Webhook: document style deleted, clearing transient');
        delete_transient('moneybird2_workflows');
    } elseif ($action == 'ledger_account_deactivated' || $action == 'ledger_account_destroyed') {
        foreach ($wcmb->settings as $key => $val) {
            if (strpos($key, 'ledger_account_id') !== false) {
                if (strpos($val, $payload->entity_id) !== false) {
                    $wcmb->log('Webhook: ledger account deleted, clearing setting: ' . $key);
                    $wcmb->settings[$key] = '';
                    $settings_updated = true;
                }
            }
        }
        $wcmb->log('Webhook: ledger account deactivated/deleted, clearing transient');
        delete_transient('moneybird2_revenue_ledger_accounts');
    } elseif ($action == 'project_archived' || $action == 'project_destroyed') {
        foreach ($wcmb->settings as $key => $val) {
            if (strpos($key, 'project_id') !== false) {
                if (strpos($val, $payload->entity_id) !== false) {
                    $wcmb->log('Webhook: project deleted, clearing setting: ' . $key);
                    $wcmb->settings[$key] = '';
                    $settings_updated = true;
                }
            }
        }
        $wcmb->log('Webhook: project deactivated/deleted, clearing transient');
        delete_transient('moneybird2_projects');
    } elseif ($action == 'tax_rate_deactivated' || $action == 'tax_rate_destroyed') {
        foreach ($wcmb->settings as $key => $val) {
            if (strpos($key, 'tax_rate_') !== false) {
                if (strpos($val, $payload->entity_id) !== false) {
                    $wcmb->log('Webhook: tax rate deleted, clearing setting: ' . $key);
                    $wcmb->settings[$key] = '';
                    $settings_updated = true;
                }
            }
        }
        $wcmb->log('Webhook: tax rate deactivated/deleted, clearing transient');
        delete_transient('moneybird2_mb_tax_rates');
    } elseif ($action == 'workflow_deactivated' || $action == 'workflow_destroyed') {
        foreach ($wcmb->settings as $key => $val) {
            if (strpos($key, 'workflow') !== false) {
                if (strpos($val, $payload->entity_id) !== false) {
                    $wcmb->log('Webhook: workflow deleted, clearing setting: ' . $key);
                    $wcmb->settings[$key] = '';
                    $settings_updated = true;
                }
            }
        }
        $wcmb->log('Webhook: workflow deactivated/deleted, clearing transient');
        delete_transient('moneybird2_workflows');
    }

    if ($settings_updated) {
        update_option('woocommerce_moneybird2_settings', $this->settings);
    }
}

add_action(
    'woocommerce_moneybird_webhook',
    '\ExtensionTree\WCMoneyBird\webhook_handle_entity_deletions',
    10, 2
);

function webhook_handle_sales_invoice_state_changed_to_paid($payload, $wcmb) {
    if (empty($wcmb->settings['register_payment_order']) || $wcmb->settings['register_payment_order'] != 'yes') {
        // Nothing to do
        return;
    }
    foreach (wcmb_get_order_ids_by_invoice_id($payload->entity_id) as $order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $log_msg = 'Webhook: invoice ' . $payload->entity_id . ' paid; linked to order ' . $order_id . '; ';
            if (!empty($wcmb->settings['register_payment_order_new_status'])) {
                // Maybe update order status to explicitly specified status
                if (empty($order->get_date_paid())) {
                    $wcmb->log($log_msg . 'registering payment complete on order');
                    $order->payment_complete();
                }
                $new_status = $wcmb->settings['register_payment_order_new_status'];
                if ($new_status != $order->get_status()) {
                    $wcmb->log(
                        'Update status of order ' . $order_id . ' because of invoice payment: ' .
                        $order->get_status() . ' -> ' . $new_status
                    );
                    $order->update_status($new_status, __('Paid through Moneybird invoice.', 'woocommerce_moneybird'));
                } else {
                    $order->add_order_note(__('Paid through Moneybird invoice.', 'woocommerce_moneybird'));
                }
            } elseif (empty($order->get_date_paid())) {
                // Mark order as paid, let WooCommerce handle the status
                $wcmb->log($log_msg . 'registering payment complete on order');
                $order->payment_complete();
                $order->add_order_note(
                    __('Paid through Moneybird invoice.', 'woocommerce_moneybird')
                );
            } else {
                // Order is already paid and no explicit status update is configured, do nothing
                $wcmb->log($log_msg . 'order already paid, nothing to do');
            }
        }
    }
}

add_action(
    'woocommerce_moneybird_webhook_sales_invoice_state_changed_to_paid',
    '\ExtensionTree\WCMoneyBird\webhook_handle_sales_invoice_state_changed_to_paid',
    10, 2
);

function webhook_handle_sales_invoice_destroyed($payload, $wcmb) {
    foreach (wcmb_get_order_ids_by_invoice_id($payload->entity_id) as $order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $wcmb->log('Webhook: invoice ' . $payload->entity_id . ' deleted; unlinking from order ' . $order_id);
            $order->add_order_note(
                __('Moneybird invoice deleted.', 'woocommerce_moneybird')
            );
            $order->delete_meta_data('moneybird_invoice_id');
            $order->save_meta_data();
        }
    }
}

add_action(
    'woocommerce_moneybird_webhook_sales_invoice_destroyed',
    '\ExtensionTree\WCMoneyBird\webhook_handle_sales_invoice_destroyed',
    10, 2
);

function maybe_update_order_status_from_estimate_update($webhook_payload, $wcmb) {
    if ($webhook_payload->action == 'estimate_mark_accepted') {
        $event = 'accepted';
    } elseif ($webhook_payload->action == 'estimate_mark_rejected') {
        $event = 'rejected';
    } elseif ($webhook_payload->action == 'estimate_mark_billed') {
        $event = 'billed';
    } else {
        return;
    }
    if (empty($wcmb->settings['estimate_' . $event . '_order_status_update'])) {
        return;
    }
    $new_status = $wcmb->settings['estimate_' . $event . '_order_status_update'];
    foreach (wcmb_get_order_ids_by_estimate_id($webhook_payload->entity_id) as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            continue;
        }
        if ($new_status != $order->get_status()) {
            $wcmb->log(
                'Webhook: estimate ' . $payload->entity_id . ' ' . $event . '; updating order ' . $order_id . ': ' .
                $order->get_status() . ' -> ' . $new_status
            );
            $order->update_status($new_status, __('Estimate', 'woocommerce_moneybird') . ' ' . __($event, 'woocommerce_moneybird') . '.');
        }
    }
}

add_action(
    'woocommerce_moneybird_webhook_estimate_mark_accepted',
    '\ExtensionTree\WCMoneyBird\maybe_update_order_status_from_estimate_update',
    10, 2
);

add_action(
    'woocommerce_moneybird_webhook_estimate_mark_rejected',
    '\ExtensionTree\WCMoneyBird\maybe_update_order_status_from_estimate_update',
    10, 2
);

add_action(
    'woocommerce_moneybird_webhook_estimate_mark_billed',
    '\ExtensionTree\WCMoneyBird\maybe_update_order_status_from_estimate_update',
    10, 2
);
