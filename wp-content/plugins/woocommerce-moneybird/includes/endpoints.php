<?php

namespace ExtensionTree\WCMoneyBird;


function get_moneybird_invoice_pdf_url($order_id, $packing_slip = false, $require_user_auth = true)
{
    // This function is only present for backwards compatibility.
    if ($packing_slip) {
        return get_moneybird_pdf_url($order_id, 'packing-slip', $require_user_auth);
    } else {
        return get_moneybird_pdf_url($order_id, 'invoice', $require_user_auth);
    }
}


function get_moneybird_pdf_url($order_id, $doctype, $require_user_auth = true)
{
    /**
     * Return Moneybird document PDF url for the specified order.
     * @param int $order_id
     * @param string $doctype 'estimate' or 'invoice' or 'packing-slip'
     * @param bool $require_user_auth
     * @return string|false
     */

    if (!in_array($doctype, array('estimate', 'invoice', 'packing-slip'))) {
        throw new \Exception('Invalid document type, must be "estimate" or "invoice" or "packing-slip".');
    }

    $order = wc_get_order($order_id);
    if (empty($order)) {
        throw new \Exception('Unknown order or invalid request (1).');
    }

    // Ensure Moneybird integration is available
    global $woocommerce;
    if (isset($woocommerce->integrations->integrations['moneybird2'])) {
        $moneybird = $woocommerce->integrations->integrations['moneybird2'];
        // Front-end invoice PDF button must be enabled unless user is admin
        if (!isset($moneybird->settings['frontend_button']) || ($moneybird->settings['frontend_button'] != 'yes')) {
            if (!current_user_can('edit_others_posts')) {
                throw new \Exception('Unknown order or invalid request (2).');
            }
        }
    } else {
        throw new \Exception('Unknown order or invalid request (3).');
    }
    
    // Ensure user is admin or creator of the specified order
    if ($require_user_auth && !current_user_can('edit_others_posts')) {
        $current_user = wp_get_current_user();
        $order_user = false;
        $order_type = is_callable(array($order, 'get_type')) ? $order->get_type() : 'shop_order';
        if ($order_type == 'shop_order_refund') {
            $parent_order_id = $order->get_parent_id();
            if ($parent_order_id) {
                $parent_order = wc_get_order($parent_order_id);
                if ($parent_order) {
                    $order_user = $parent_order->get_user();
                }
            }
        } else {
            $order_user = $order->get_user();
        }
        if (!$current_user || !$current_user->exists() || !$order_user || ($order_user->ID != $current_user->ID)) {
            throw new \Exception('Unknown order or invalid request (4).');
        }
    }

    // Redirect to PDF if order has a linked Moneybird document
    if ($doctype == 'estimate') {
        $document_id = $order->get_meta('moneybird_estimate_id', true);
    } else {
        $document_id = $order->get_meta('moneybird_invoice_id', true);
    }
    
    if (!empty($document_id)) {
        $transient_key = 'wcmb_pdf_url_' . $document_id . (($doctype == 'packing-slip') ? '_packing_slip' : '');
        $url = get_transient($transient_key);
        if ($url) {
            return $url;
        }
        $mb_api = $moneybird->load_api_connector();
        if ($mb_api) {
            // Make sure the invoice is available
            if ($doctype == 'estimate') {
                $mb_document = $mb_api->getEstimate($document_id);
            } else {
                $mb_document = $mb_api->getSalesInvoice($document_id);
            }
            if (empty($mb_document)) {
                throw new \Exception('Document is not available.');
            }
            // Only admin users can download draft documents
            if (($mb_document->state == 'draft') && !current_user_can('edit_others_posts')) {
                throw new \Exception('Document is not available.');
            }
            if ($doctype == 'estimate') {
                $url = $mb_api->getEstimatePdf($document_id);
            } else {
                if ($doctype == 'packing-slip') {
                    $url = $mb_api->getSalesInvoicePdf($document_id, true);
                } else {
                    $url = $mb_api->getSalesInvoicePdf($document_id);
                }
            }
            if ($url) {
                set_transient($transient_key, $url, 20); // Cache url for 20 seconds
                return $url;
            }
        }
    }

    return false; // PDF not available
}


function redirect_moneybird_estimate_pdf($data)
{
    /**
     * REST API request handler for wcmb/pdf-estimate/<order_id>
     */
    // Ensure authenticated user
    if (!is_user_logged_in()) {
        status_header(403);
        die('Login required.');
    }

    // Get url
    header('Content-type: text/html');
    $response = new \WP_REST_Response($data);
    $order_id = $data['order_id'];
    try {
        $url = get_moneybird_pdf_url($order_id, 'estimate');
    } catch (\Exception $e) {
        status_header(404);
        die($e->getMessage());
    }

    if ($url) {
        $response->set_status(302);
        $response->header('Location', $url);
        return $response;
    }

    // Return 404 in case we could not respond with a redirect to the PDF
    status_header(404);
    die(__('Estimate is not available.', 'woocommerce_moneybird'));
}


function redirect_moneybird_invoice_pdf($data)
{
    /**
     * REST API request handler for wcmb/pdf-invoice/<order_id>
     */
    // Ensure authenticated user
    if (!is_user_logged_in()) {
        status_header(403);
        die('Login required.');
    }

    // Get url
    header('Content-type: text/html');
    $response = new \WP_REST_Response($data);
    $order_id = $data['order_id'];
    try {
        $url = get_moneybird_pdf_url($order_id, 'invoice');
    } catch (\Exception $e) {
        status_header(404);
        die($e->getMessage());
    }

    if ($url) {
        $response->set_status(302);
        $response->header('Location', $url);
        return $response;
    }

    // Return 404 in case we could not respond with a redirect to the PDF
    status_header(404);
    die(__('Invoice is not available.', 'woocommerce_moneybird'));
}


function redirect_moneybird_packing_slip_pdf($data)
{
    /**
     * REST API request handler for wcmb/pdf-invoice/<order_id>
     */
    // Ensure authenticated admin user
    if (!is_user_logged_in() || !current_user_can('edit_others_posts')) {
        status_header(403);
        die('Admin login required.');
    }

    // Get url
    header('Content-type: text/html');
    $response = new \WP_REST_Response($data);
    $order_id = $data['order_id'];
    try {
        $url = get_moneybird_pdf_url($order_id, 'packing-slip');
    } catch (\Exception $e) {
        status_header(404);
        die($e->getMessage());
    }

    if ($url) {
        $response->set_status(302);
        $response->header('Location', $url);
        return $response;
    }

    // Return 404 in case we could not respond with a redirect to the PDF
    status_header(404);
    die(__('Invoice is not available.', 'woocommerce_moneybird'));
}


add_action('rest_api_init', function () {
    register_rest_route('wcmb', '/pdf-estimate/(?P<order_id>\d+)', array(
        'methods' => 'GET',
        'callback' => '\ExtensionTree\WCMoneyBird\redirect_moneybird_estimate_pdf',
        'permission_callback' => '__return_true'
    ));
});


add_action('rest_api_init', function () {
    register_rest_route('wcmb', '/pdf-invoice/(?P<order_id>\d+)', array(
        'methods' => 'GET',
        'callback' => '\ExtensionTree\WCMoneyBird\redirect_moneybird_invoice_pdf',
        'permission_callback' => '__return_true'
    ));
});


add_action('rest_api_init', function () {
    register_rest_route('wcmb', '/pdf-packing-slip/(?P<order_id>\d+)', array(
        'methods' => 'GET',
        'callback' => '\ExtensionTree\WCMoneyBird\redirect_moneybird_packing_slip_pdf',
        'permission_callback' => function () {
            return current_user_can('edit_others_posts');
        }
    ));
});

// Handle requests to public urls for PDFs
add_action('parse_request', function ($wp) {
    
    if (empty($wp->query_vars['name']) || $wp->query_vars['name'] != 'wcmb') {
        if (empty($wp->query_vars['pagename']) || $wp->query_vars['pagename'] != 'wcmb') {
            return;
        }
    }

    // Get parameters
    $doctype = (isset($_GET['doc'])) ? sanitize_text_field($_GET['doc']) : '';
    $order_id = (isset($_GET['order'])) ? sanitize_text_field($_GET['order']) : '';
    $hash = (isset($_GET['hash'])) ? sanitize_text_field($_GET['hash']) : '';
    if (
        empty($order_id) || !is_numeric($order_id) || empty($hash)
        || !in_array($doctype, array('estimate', 'invoice', 'packing-slip'))
    ) {
        status_header(404);
        get_template_part(404);
        exit();
    }

    // Verify hash
    if (wp_hash($doctype . $order_id) != $hash) {
        // Also try with 'invoice' prefix for backwards compatibility
        if (wp_hash('invoice' . $order_id) != $hash) {
            status_header(404);
            get_template_part(404);
            exit();
        }
    }

    // Get order
    $order = wc_get_order($order_id);
    if (empty($order)) {
        status_header(404);
        get_template_part(404);
        exit();
    }

    // Redirect to PDF if it exists
    try {
        $url = get_moneybird_pdf_url($order_id, $doctype, false);
    } catch (\Exception $e) {
        $url = '';
    }
    if ($url) {
        wp_redirect($url);
        exit();
    } else {
        status_header(404);
        get_template_part(404);
        exit();
    }
});
