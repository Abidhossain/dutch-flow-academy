<?php

defined( 'ABSPATH' ) || exit;

if (!class_exists('WCMB_Shortcodes')):

class WCMB_Shortcodes {

    function __construct() {
        add_shortcode('moneybird_estimate_pdf_link', array($this, 'moneybird_estimate_pdf_link'));
        add_shortcode('moneybird_estimate_pdf_url', array($this, 'moneybird_estimate_pdf_url'));
        add_shortcode('moneybird_invoice_pdf_link', array($this, 'moneybird_invoice_pdf_link'));
        add_shortcode('moneybird_invoice_pdf_url', array($this, 'moneybird_invoice_pdf_url'));
        add_shortcode('moneybird_packing_slip_pdf_link', array($this, 'moneybird_packing_slip_pdf_link'));
        add_shortcode('moneybird_packing_slip_pdf_url', array($this, 'moneybird_packing_slip_pdf_url'));
    }

    function get_order($atts, $content = '') {
        // Get WC_Order object from shortcode attribute order_id, content or GET parameter key.
        // Returns WC_Order object or false.
        $_atts = shortcode_atts(array(
            'order_id' => '',
        ), $atts);
        $order_id = $_atts['order_id'];
        if (empty($order_id) && !empty($content)) {
            $order_id = trim(do_shortcode($content));
        }
        if (empty($order_id) && !empty($_GET['key']) && strpos($_GET['key'], 'wc_order_') !== false) {
            $order_id = wc_get_order_id_by_order_key($_GET['key']);
        }
        if (!empty($order_id) && is_numeric($order_id)) {
            $order_id = intval($order_id);
        } else {
            return false;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        return $order;
    }

    function moneybird_estimate_pdf_url($atts, $content = '') {
        // Shortcode for publicly available url of the PDF estimate of a WC_Order.
        // If the order_id attribute is not set or passed through the content, the order_id will be retrieved from the GET parameter 'key' (e.g. ?key=wc_order_{HASH_CODE}).
        // Usage: [moneybird_estimate_pdf_url order_id="123" public="yes|no"] or [moneybird_estimate_pdf_url public="yes|no"] or [moneybird_estimate_pdf_url public="yes|no"]123[/moneybird_estimate_pdf_url]
        $order = $this->get_order($atts, $content);
        if (!$order) {
            return '';
        }
        $_atts = shortcode_atts(array(
            'public' => 'no',
        ), $atts);
        $url = wcmb_get_estimate_pdf_url($order, $_atts['public']=='yes');
        if ($url) {
            return $url;
        } else {
            return '';
        }
    }

    function moneybird_estimate_pdf_link($atts, $content = '') {
        // Shortcode for link to publicly available url of the PDF estimate of a WC_Order.
        // If the order_id attribute is not set or passed through the content, the order_id will be retrieved from the GET parameter 'key' (e.g. ?key=wc_order_{HASH_CODE}).
        // Usage: [moneybird_estimate_pdf_link order_id="123" text="Download offerte" public="yes|no"] or [moneybird_estimate_pdf_link text="Download offerte" public="yes|no"] or [moneybird_estimate_pdf_link text="Download offerte" public="yes|no"]123[/moneybird_estimate_pdf_link]
        // Returns HTML link or empty string in case no estimate is available.
        $url = $this->moneybird_estimate_pdf_url($atts, $content);
        if ($url) {
            $atts = shortcode_atts(array(
                'text' => 'Download PDF',
            ), $atts);
            return '<a class="wcmb-pdf-link" href="' . esc_url($url) . '">' . $atts['text'] . '</a>';
        } else {
            return '';
        }
    }

    function moneybird_invoice_pdf_link($atts, $content = '') {
        // Shortcode for link to publicly available url of the PDF invoice of a WC_Order.
        // Usage: [moneybird_invoice_pdf_link order_id="123" text="Download factuur" public="yes|no"] or [moneybird_invoice_pdf_link text="Download factuur" public="yes|no"] or [moneybird_invoice_pdf_link text="Download factuur" public="yes|no"]123[/moneybird_invoice_pdf_link]
        // Returns HTML link or empty string in case no invoice is available.
        $url = $this->moneybird_invoice_pdf_url($atts, $content);
        if ($url) {
            $atts = shortcode_atts(array(
                'text' => 'Download PDF',
            ), $atts);
            return '<a class="wcmb-pdf-link" href="' . esc_url($url) . '">' . $atts['text'] . '</a>';
        } else {
            return '';
        }
    }

    function moneybird_invoice_pdf_url($atts, $content = '') {
        // Shortcode for publicly available url of the PDF invoice of a WC_Order.
        // If the order_id attribute is not set or passed through the content, the order_id will be retrieved from the GET parameter 'key' (e.g. ?key=wc_order_{HASH_CODE}).
        // Usage: [moneybird_invoice_pdf_url order_id="123" public="yes|no"] or [moneybird_invoice_pdf_url public="yes|no"] or [moneybird_invoice_pdf_url public="yes|no"]123[/moneybird_invoice_pdf_url]
        $order = $this->get_order($atts, $content);
        if (!$order) {
            return '';
        }
        $_atts = shortcode_atts(array(
            'public' => 'no',
        ), $atts);
        $url = wcmb_get_invoice_pdf_url($order, $_atts['public']=='yes');
        if ($url) {
            return $url;
        } else {
            return '';
        }
    }

    function moneybird_packing_slip_pdf_link($atts, $content = '') {
        // Shortcode for link to publicly available url of the PDF packing slip of a WC_Order.
        // If the order_id attribute is not set or passed through the content, the order_id will be retrieved from the GET parameter 'key' (e.g. ?key=wc_order_{HASH_CODE}).
        // Usage: [moneybird_packing_slip_pdf_link order_id="123" text="Download pakbon" public="yes|no"] or [moneybird_packing_slip_pdf_link text="Download pakbon" public="yes|no"] or [moneybird_packing_slip_pdf_link text="Download pakbon" public="yes|no"]123[/moneybird_packing_slip_pdf_link]
        // Returns HTML link or empty string in case no packing slip is available.
        $url = $this->moneybird_packing_slip_pdf_url($atts, $content);
        if ($url) {
            $atts = shortcode_atts(array(
                'text' => 'Download PDF',
            ), $atts);
            return '<a class="wcmb-pdf-link" href="' . esc_url($url) . '">' . $atts['text'] . '</a>';
        } else {
            return '';
        }
    }

    function moneybird_packing_slip_pdf_url($atts, $content = '') {
        // Shortcode for publicly available url of the PDF packing slip of a WC_Order.
        // If the order_id attribute is not set or passed through the content, the order_id will be retrieved from the GET parameter 'key' (e.g. ?key=wc_order_{HASH_CODE}).
        // Usage: [moneybird_packing_slip_pdf_url order_id="123" public="yes|no"] or [moneybird_packing_slip_pdf_url public="yes|no"] or [moneybird_packing_slip_pdf_url public="yes|no"]123[/moneybird_packing_slip_pdf_url]
        $order = $this->get_order($atts, $content);
        if (!$order) {
            return '';
        }
        $_atts = shortcode_atts(array(
            'public' => 'no',
        ), $atts);
        $url = wcmb_get_packing_slip_pdf_url($order, $_atts['public']=='yes');
        if ($url) {
            return $url;
        } else {
            return '';
        }
    }


}

endif;

new WCMB_Shortcodes();
