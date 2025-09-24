<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice
 * @subpackage Woo_Invoice/includes
 */

/**
 * The core plugin class that bridge PDF Generator class and Hooks, to generate PDF.
 *
 * This is used to generate PDF Invoice
 *
 * @since      1.0.0
 * @package    Woo_Invoice
 * @subpackage Woo_Invoice/includes
 * @author     Md Ohidul Islam <wahid@webappick.com>
 */
class Woo_Invoice_Engine
{



    /**
     * Generate Bulk or Single Invoice
     *
     * @param  string $order_ids Order ids.
     * @return mixed
     * @throws \Mpdf\MpdfException Output PDF.
     */
    public function generate_invoice( $order_ids ) {
        $ids      = explode(',', $order_ids);
        $template = woo_invoice_template($ids, 'invoice');

        // parse url.
        if ( isset($_SERVER['REQUEST_URI']) ) {
            $url_components = wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));
            $url_components = parse_str($url_components['query'], $params);
            if ( isset($params['output']) && 'html' === $params['output'] ) {
                echo $template->get_invoice_template(); //phpcs:ignore
                die;
            }
        }

        // DEBUG HERE INVOICE.
        // echo $template->get_invoice_template(); die; !
        ob_start();
        echo $template->get_invoice_template(); //phpcs:ignore
        $html = ob_get_contents();
        ob_end_clean();
        $file_name = ( get_option('wpifw_INVOICE_TITLE') ) ? get_option('wpifw_INVOICE_TITLE') : 'Invoice';
        $pdf       = woo_invoice_pdf($html, $file_name, $order_ids, 'invoice');
        $pdf->generate_pdf();
        exit;
    }

    /**
     * Generate Bulk or Single Packing Slip
     *
     * @param  string $order_ids Order Ids.
     * @return mixed
     * @throws \Mpdf\MpdfException Output PDF.
     */
    public function generate_packing_slip( $order_ids ) {
        $ids      = explode(',', $order_ids);
        $template = woo_invoice_template($ids, 'packing_slip');

        // parse url.
        if ( isset($_SERVER['REQUEST_URI']) ) {
            $url_components = wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));
            $url_components = parse_str($url_components['query'], $params);
            if ( isset($params['output']) && 'html' === $params['output'] ) {
                echo $template->get_packing_template(); //phpcs:ignore
                die;
            }
        }

        // DEBUG? UNCOMMENT BELOW LINE.
        // echo $template->get_packing_template(); die(); !
        ob_start();
        echo $template->get_packing_template(); //phpcs:ignore
        $slip = ob_get_contents();
        ob_end_clean();
        $file_name = ( get_option('wpifw_PACKING_SLIP_TEXT') ) ? get_option('wpifw_PACKING_SLIP_TEXT') : 'Packing Slip';
        $pdf       = woo_invoice_pdf($slip, $file_name, $order_ids, 'packing_slip');
        $pdf->generate_pdf();
        exit;
    }


	/**
	 * Generate single shipping label.
	 *
	 * @param  string $order_ids Order Ids.
	 * @return mixed
	 * @throws \Mpdf\MpdfException Output PDF.
	 */
	public function generate_delivery_address( $order_ids ) {

		$ids      = explode(',', $order_ids);
		$template = woo_invoice_template($ids, 'label');

        // parse url.
        if ( isset($_SERVER['REQUEST_URI']) ) {
            $url_components = wp_parse_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));
            $url_components = parse_str($url_components['query'], $params);
            if ( isset($params['output']) && 'html' === $params['output'] ) {
                echo $template->get_delivery_address_template(); //phpcs:ignore
                die;
            }
        }


        ob_start();
		echo $template->get_delivery_address_template(); //phpcs:ignore
		$label = ob_get_contents();
		ob_end_clean();
		$file_name = ( get_option('wpifw_PACKING_SLIP_TEXT') ) ? get_option('wpifw_PACKING_SLIP_TEXT') : 'Delivery Address';
		$pdf       = woo_invoice_pdf($label, $file_name, $order_ids, 'label');
		$pdf->generate_pdf();
		exit;
	}

    /**
     * Save PDF Invoice to Upload directory
     *
     * @param  int $order_id Order Id.
     * @return mixed
     * @throws \Mpdf\MpdfException Save PDF.
     */
    public function save_invoice( $order_id ) {
        $template = woo_invoice_template(array( $order_id ), 'invoice');
        ob_start();
        echo $template->get_invoice_template();//phpcs:ignore
        $html = ob_get_contents();
        ob_end_clean();

        // DEBUG? UNCOMMENT BELOW LINE.
        // echo $template->get_invoice_template(); die(); !

        $file_name = ( get_option('wpifw_INVOICE_TITLE') ) ? get_option('wpifw_INVOICE_TITLE') : 'Invoice';
        $pdf       = woo_invoice_pdf($html, $file_name, $order_id, 'save');
        $pdf->generate_pdf();
    }
}

/**
 * Initialize Invoice Engine
 *
 * @return Woo_Invoice_Engine
 */
function woo_invoice_engine() {
    return new Woo_Invoice_Engine();
}
