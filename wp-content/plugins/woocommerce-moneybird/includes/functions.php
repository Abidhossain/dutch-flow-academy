<?php

function wcmb_get_estimate_pdf_url($order, $public=false) {
    // Get the PDF estimate download link.
    $estimate_id = $order->get_meta('moneybird_estimate_id', true);
    if (!empty($estimate_id)) {
        if ($public) {
            // Publicly available URL (for use in emails, etc.)
            $hash = wp_hash('estimate' . $order->get_id());
            $estimate_pdf_url = get_site_url(null, 'wcmb') . '?doc=estimate&order=' . $order->get_id() . '&hash=' . $hash;
        } else {
            // Admin URL tied to user and session
            $estimate_pdf_url = get_rest_url(null, 'wcmb/pdf-estimate/' . $order->get_id());
            $estimate_pdf_url .= '?_wpnonce=' . wp_create_nonce('wp_rest');
        }
        return $estimate_pdf_url;
    } else {
        return false;
    }
}

function wcmb_get_invoice_pdf_url($order, $public=false) {
    // Get the PDF invoice download link.
    $invoice_id = $order->get_meta('moneybird_invoice_id', true);
    if (!empty($invoice_id)) {
        if ($public) {
            // Publicly available URL (for use in emails, etc.)
            $hash = wp_hash('invoice' . $order->get_id());
            $invoice_pdf_url = get_site_url(null, 'wcmb') . '?doc=invoice&order=' . $order->get_id() . '&hash=' . $hash;
        } else {
            // Admin URL tied to user and session
            $invoice_pdf_url = get_rest_url(null, 'wcmb/pdf-invoice/' . $order->get_id());
            $invoice_pdf_url .= '?_wpnonce=' . wp_create_nonce('wp_rest');
        }
        return $invoice_pdf_url;
    } else {
        return false;
    }
}

function wcmb_get_packing_slip_pdf_url($order, $public=false) {
    // Get the PDF packing slip download link.
    $invoice_id = $order->get_meta('moneybird_invoice_id', true);
    if (!empty($invoice_id)) {
        if ($public) {
            // Publicly available URL (for use in emails, etc.)
            $hash = wp_hash('packing-slip' . $order->get_id());
            $packing_slip_pdf_url = get_site_url(null, 'wcmb') . '?doc=packing-slip&order=' . $order->get_id() . '&hash=' . $hash;
        } else {
            // Admin URL tied to user and session
            $packing_slip_pdf_url = get_rest_url(null, 'wcmb/pdf-packing-slip/' . $order->get_id());
            $packing_slip_pdf_url .= '?_wpnonce=' . wp_create_nonce('wp_rest');
        }
        return $packing_slip_pdf_url;
    } else {
        return false;
    }
}

function wcmb_get_order_ids_by_moneybird_id($document_id, $document_type='invoice') {
    // Get ids of all WC_Orders that are linked to a Moneybird document.

    // When HPOS is disabled, wc_get_orders() ignores meta_query and returns all orders that have the meta_key 'moneybird_{$document_type}_id'
    // So we need to use get_posts() instead when HPOS is disabled
    $meta_key = 'moneybird_' . $document_type . '_id';
    if (class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')) {
        if (wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()) {
            // HPOS is enabled, use wc_get_orders()
            $args = array(
                'return' => 'ids',
                'type' => 'shop_order',
                'limit' => -1,
                'meta_query' => array(
                    array(
                        'key' => $meta_key,
                        'value' => $document_id,
                        'compare' => '=',
                    ),
                ),
            );
            return wc_get_orders($args);
        }
    }

    // HPOS is disabled, use legacy get_posts()
    $args = array(
        'post_type' => 'shop_order',
        'fields' => 'ids',
        'post_status' => 'any',
        'meta_query' => array(
            array(
                'key' => $meta_key,
                'value' => $document_id,
                'compare' => '=',
            ),
        ),
    );
    return get_posts($args);
}

function wcmb_get_order_ids_by_invoice_id($invoice_id) {
    return wcmb_get_order_ids_by_moneybird_id($invoice_id, 'invoice');
}

function wcmb_get_order_ids_by_estimate_id($estimate_id) {
    return wcmb_get_order_ids_by_moneybird_id($estimate_id, 'estimate');
}

function wcmb_is_license_valid($key='', $force_refresh=false) {
    /*
    Check if a license key is valid. The key is read from wp-options if $key is empty.
    The result is cached for performance reasons.

    NOTE: if you want to be a crook and use this plugin without a valid license,
    this is the function you should modify. However, here are some reasons 
    why you shouldn't:
    1.   Using the plugin without a license is illegal. Do you really want to
         become a criminal to save a few bucks? Not cool, not cool at all.
    2.   You won't receive updates anyway; the license validation for updates
         is performed server-side. If you update manually, you'll have to 
         patch this file after every update. Is it worth the hassle?
    3.   The developer also needs to feed his family. If you can't afford a
         license, act like a man (or woman, whatever, you get the point) 
         and just email info@extensiontree.com to ask for a discount. 
         You might just get one.
    */
    if (empty($key)) {
        $settings = get_option('woocommerce_moneybird2_settings');
        if ($settings && isset($settings['licensekey']) && !empty($settings['licensekey'])) {
            $key = trim($settings['licensekey']);
        } elseif (function_exists('get_sites')) {
            // In case of a multi-site setup, look for a license key in all sites
            foreach (get_sites(array('fields' => 'ids')) as $site_id) {
                $settings = get_blog_option($site_id, 'woocommerce_moneybird2_settings');
                if ($settings && isset($settings['licensekey']) && !empty($settings['licensekey'])) {
                    $key = trim($settings['licensekey']);
                    break;
                }
            }
        }
    }
    if (empty($key)) {
        return false;
    }
    if (preg_match('/^[a-zA-Z0-9\-]{8,16}$/', $key) !== 1) {
        return false;
    }
    $url = trim(network_site_url('/'));
    if ($force_refresh) {
        $license_status = false;
    } else {
        $license_status = get_transient('wcmb_license_status_'.$key);
    }
    if (is_array($license_status)) {
        if ($license_status['status_horizon'] < time()) {
            $license_status = false;
        }
    }
    if (!is_array($license_status)) {
        $license_status = array(
            'status_horizon' => time() + 3*24*3600,
            'urls' => array(),

        );
    }
    if (array_key_exists($url, $license_status['urls'])) {
        return $license_status['urls'][$url];
    }
    $req_options = array(
        'timeout' => 10,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    );
    $check_url = 'https://my.extensiontree.com/licenses/QBBXQ84NURJVGTQR/' . $key . '/status';
    $check_url = add_query_arg('url', $url, $check_url);
    $response = wp_remote_get($check_url, $req_options);
    if (is_wp_error($response)) {
        return true;
    }
    $response_data = json_decode(wp_remote_retrieve_body($response));
    if (empty($response_data) || !isset($response_data->valid)) {
        return true;
    }
    $license_status['urls'][$url] = $response_data->valid;
    set_transient('wcmb_license_status_'.$key, $license_status, 3*24*3600);
    return $license_status['urls'][$url];
}
