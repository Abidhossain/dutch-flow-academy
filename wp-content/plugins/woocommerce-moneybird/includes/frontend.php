<?php

namespace ExtensionTree\WCMoneyBird;


function wcmb_order_invoice_button($order) {
    /**
     * Add a 'download invoice' button to the view order page.
     *
     * @param object $order Order.
     */

    // Ensure authenticated user
    if (!is_user_logged_in()) {
        return;
    }

    // Try to get invoice id
    $invoice_id = $order->get_meta('moneybird_invoice_id', true);

    // Try to get credit invoice ids
    $refunds = $order->get_refunds();
    $refunds_with_invoice = array();
    if (is_array($refunds) && !empty($refunds)) {
        foreach ($refunds as $refund) {
            $credit_invoice_id = $refund->get_meta('moneybird_invoice_id', true);
            if (!empty($credit_invoice_id)) {
                $refunds_with_invoice[] = $refund;
            }
        }
    }

    if (empty($invoice_id) && empty($refunds_with_invoice)) {
        return; // No invoice
    }

    // Check if front-end invoice button is activated
    global $woocommerce;
    if (isset($woocommerce->integrations->integrations['moneybird2'])) {
        $moneybird = $woocommerce->integrations->integrations['moneybird2'];
        if (!isset($moneybird->settings['frontend_button']) || ($moneybird->settings['frontend_button'] != 'yes')) {
            return; // Front-end button not enabled
        }
    } else {
        return; // Moneybird plugin not active
    }

    // Output
    $invoice_links = array();
    if ($invoice_id) {
        $invoice_links[] = array('type' => 'invoice', 'pdf_url' => wcmb_get_invoice_pdf_url($order));
    }
    foreach ($refunds_with_invoice as $refund) {
        $invoice_links[] = array('type' => 'credit_invoice', 'pdf_url' => wcmb_get_invoice_pdf_url($refund));
    }
    if (!empty($invoice_links)) {
        $credit_invoice_counter = 1;
        foreach ($invoice_links as $invoice_link) {
            if ($invoice_link['type'] == 'credit_invoice') {
				if (strpos(get_locale(), 'nl') === 0) {
					// This is needed since some plugins mess with the translations
					$button_text = 'Download creditfactuur';
				} else {
					$button_text = 'Download ' . __('credit invoice', 'woocommerce_moneybird');
				}
                if (count($refunds_with_invoice) > 1) {
                    $button_text .= ' ' . $credit_invoice_counter++;
                }
            } else {
				if (strpos(get_locale(), 'nl') === 0) {
					// This is needed since some plugins mess with the translations
					$button_text = 'Download factuur';
				} else {
					$button_text = 'Download ' . __('invoice', 'woocommerce_moneybird');
				}
            }
            echo '<p class="order-invoice"><a href="' . esc_url( $invoice_link['pdf_url'] ) . '" class="button">' . $button_text . '</a></p>';
        }
    }
}


add_action( 'woocommerce_order_details_after_order_table', '\ExtensionTree\WCMoneyBird\wcmb_order_invoice_button' );
