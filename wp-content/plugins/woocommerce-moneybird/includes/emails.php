<?php

namespace ExtensionTree\WCMoneyBird;

define('INVOICES_DIR', wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'moneybird_invoices');

if (!is_dir(INVOICES_DIR)) {
    try {
        $dir_created = mkdir(INVOICES_DIR);
        if ($dir_created) {
            touch(INVOICES_DIR . DIRECTORY_SEPARATOR . 'index.html');
        } else {
            define('INVOICES_DIR_MISSING', true);
        }
    } catch (\Exception $e) {
        define('INVOICES_DIR_MISSING', true);
    }
}

// Garbage collection: unlink all invoices that are older than 15 minutes
function unlink_invoice_files() {
    if (defined('INVOICES_DIR_MISSING')) {
        return;
    }
    $pattern = INVOICES_DIR . DIRECTORY_SEPARATOR . '*';
    foreach (glob($pattern) as $dirname) {
        if (is_dir($dirname)) {
            $dir_empty = true;
            foreach (glob($dirname . DIRECTORY_SEPARATOR . '*') as $filename) {
                if (filemtime($filename) < (time() - 15 * 60)) {
                    try {
                        unlink($filename);
                    } catch (\Exception $e) {
                        $dir_empty = false;
                    }
                } else {
                    $dir_empty = false;
                }
            }
            if ($dir_empty) {
                try {
                    rmdir($dirname);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}

add_action('wc_mb_gc', '\ExtensionTree\WCMoneyBird\unlink_invoice_files');

function get_invoice_pdf_filename($invoice_id) {
    // Get absolute PDF filename for an invoice based on the invoice id.
    $filename = INVOICES_DIR . DIRECTORY_SEPARATOR . $invoice_id;
    $filename .= DIRECTORY_SEPARATOR . __('invoice', 'woocommerce_moneybird') . '.pdf';
    return $filename;
}


function get_packing_slip_pdf_filename($invoice_id) {
    // Get absolute PDF filename for a packing slip based on the invoice id.
    $filename = INVOICES_DIR . DIRECTORY_SEPARATOR . $invoice_id . DIRECTORY_SEPARATOR . 'pakbon.pdf';
    return $filename;
}


function download_invoice_pdf($invoice_id, $packing_slip = false) {
    // Try to download and save PDF invoice
    $wcmb = WCMB();
    if (!$wcmb) {
        return;
    }
    $mb_api = $wcmb->load_api_connector();
    if ($mb_api) {
        $url = $mb_api->getSalesInvoicePdf($invoice_id, $packing_slip);
        if ($url) {
            if ($packing_slip) {
                $filename = get_packing_slip_pdf_filename($invoice_id);
            } else {
                $filename = get_invoice_pdf_filename($invoice_id);
            }
            $dirname = pathinfo($filename)['dirname'];
            try {
                if (!is_dir($dirname)) {
                    mkdir($dirname);
                }
                wp_remote_get(
                    $url,
                    array(
                        'stream' => true,
                        'filename' => $filename,
                        'timeout' => 15
                    )
                );
                if (file_exists($filename) && (filesize($filename) < 10)) {
                    unlink($filename);  // Unlink download if file is empty
                }
            } catch (\Exception $e) {
                return;
            }
        }
    }
}


function get_invoice_pdf($invoice_id, $packing_slip = false) {
    // Return full path to invoice PDF if file exists or can be downloaded.
    // Return false if PDF cannot be obtained.
    if ($packing_slip) {
        $filename = get_packing_slip_pdf_filename($invoice_id);
    } else {
        $filename = get_invoice_pdf_filename($invoice_id);
    }
    if (is_file($filename)) {
        return $filename;
    }
    download_invoice_pdf($invoice_id, $packing_slip);
    if (is_file($filename)) {
        return $filename;
    }
    return false;
}


function maybe_attach_moneybird_invoice_pdf($attachments, $id, $object, $email = null) {
    // Maybe attach Moneybird invoice PDF to WooCommerce transactional email
    // based on plugin configuration and invoice availability.
    $wcmb = WCMB();
    if (!$wcmb || !is_a($object, 'WC_Order')) {
        return $attachments;
    }
    if (!isset($wcmb->settings['email_attachments']) || !is_array($wcmb->settings['email_attachments'])) {
        return $attachments;
    }

    if ($id == 'customer_partially_refunded_order') {
        $id = 'customer_refunded_order';
    }

    if (!in_array($id, $wcmb->settings['email_attachments'])) {
        return $attachments;
    }

    if ($id == 'customer_refunded_order') {
        // Get refund object instead of original order
        if ($email && isset($email->refund) && $email->refund) {
            $object = $email->refund;
        } else {
            // Cancel if we cannot get to the refund object
            return $attachments;
        }
    }

    $invoice_id = $object->get_meta('moneybird_invoice_id', true);
    if ($invoice_id) {
        $filename = get_invoice_pdf($invoice_id);
        if ($filename) {
            $attachments[] = $filename;
        }
    }

    return $attachments;
}

if (version_compare(WC()->version, '3.7.0', '>=')) {
    add_filter('woocommerce_email_attachments', '\ExtensionTree\WCMoneyBird\maybe_attach_moneybird_invoice_pdf', 10, 4);
} else {
    // Before WC v3.7.0, the woocommerce_email_attachments hook only has 3 arguments
    add_filter('woocommerce_email_attachments', '\ExtensionTree\WCMoneyBird\maybe_attach_moneybird_invoice_pdf', 10, 3);
}
