<?php
/**
 * Fired during plugin activation
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice
 * @subpackage Woo_Invoice/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woo_Invoice
 * @subpackage Woo_Invoice/includes
 * @author     Md Ohidul Islam <wahid@webappick.com>
 */
class Woo_Invoice_Activator
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 1.0.0
     */
    public static function activate() {
        // Enable PDF Invoicing.
        if ( ! get_option('wpifw_invoicing') ) {
            update_option('wpifw_invoicing', '1');
        }

        if ( ! get_option('wpifw_order_email') ) {
            update_option('wpifw_order_email', '1');
        }

        if ( ! get_option('wpifw_email_attach_check_list') ) {
            update_option('wpifw_email_attach_check_list', array( 'new_order', 'customer_completed_order', 'customer_processing_order', 'customer_refunded_order', 'customer_invoice' ));
        }

        if ( ! get_option('wpifw_download') ) {
            update_option('wpifw_download', '1');
        }

        if ( ! get_option('wpifw_invoice_download_check_list') ) {
            update_option('wpifw_invoice_download_check_list', array( 'processing', 'completed', 'payment_complete', 'always_allow' ));
        }

        if ( ! get_option('wpifw_disid') ) {
            update_option('wpifw_disid', 'SKU');
        }

        if ( ! get_option('wpifw_invoice_no') ) {
            update_option('wpifw_invoice_no', '1');
        }

        if ( ! get_option('wpifw_templateid') ) {
            update_option('wpifw_templateid', 'invoice-1');
        }

        if ( ! get_option('wpifw_currency_code') ) {
            update_option('wpifw_currency_code', '0');
        }

        if ( ! get_option('wpifw_payment_method_show') ) {
            update_option('wpifw_payment_method_show', '1');
        }

        if ( ! get_option('wpifw_display_phone') ) {
            update_option('wpifw_display_phone', '0');
        }

        if ( ! get_option('wpifw_display_email') ) {
            update_option('wpifw_display_email', '0');
        }

        if ( ! get_option('wpifw_cdetails') ) {
            if ( class_exists('WooCommerce') ) {
                $store          = new WC_Countries();
                $address        = $store->get_base_address();
                $address_2      = $store->get_base_address_2();
                $country        = $store->get_base_country();
                $city           = $store->get_base_city();
                $postcode       = $store->get_base_postcode();
                $store_location = '';
                if ( ! empty($address) ) {
                    $store_location .= $address . "\n";
                }
                if ( ! empty($address_2) ) {
                    $store_location .= $address_2 . "\n";
                }
                if ( ! empty($city) ) {
                    $store_location .= $city;
                }
                if ( ! empty($city) && ! empty($postcode) ) {
                    $store_location .= '-' . $postcode . "\n";
                } else {
                    $store_location .= "\n";
                }
                if ( ! empty($country) ) {
                    $store_location .= $country;
                }
                update_option('wpifw_cdetails', $store_location);
            }
        }

        if ( ! get_option('wpifw_cname') ) {
            update_option('wpifw_cname', get_bloginfo('name'));
        }

        if ( ! get_option('wpifw_logo_attachment_id') ) {
            if ( has_custom_logo() ) {
                $custom_logo_id  = get_theme_mod('custom_logo');
                $custom_logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                update_option('wpifw_logo_attachment_id', $custom_logo_url);
            }
        }

	    $wpifw_delivery_address_buyer = <<<INFO
{{billing_first_name}}	{{billing_last_name}}
{{billing_company}}	{{billing_address_1}}
{{billing_address_2}}	{{billing_city}}
{{billing_state}}	{{billing_postcode}}
{{billing_country}}	{{billing_phone}}
{{billing_email}}	
{{shipping_first_name}}	{{shipping_last_name}}
{{shipping_company}}	{{shipping_address_1}}
{{shipping_address_2}}	{{shipping_city}}
{{shipping_state}}	{{shipping_postcode}}
{{shipping_country}}	{{shipping_phone}}
{{shipping_email}}
INFO;

	    if ( ! get_option('wpifw_delivery_address_buyer') ) {
		    update_option('wpifw_delivery_address_buyer', $wpifw_delivery_address_buyer);
	    }

        $customer_info = <<<INFO
{{billing_first_name}} {{billing_last_name}}
{{billing_company}}
{{billing_address_1}}
{{billing_address_2}}
{{billing_city}} - {{billing_postcode}}
{{billing_state}}
{{billing_country}}
P: {{billing_phone}}
E: {{billing_email}}
INFO;

        if ( ! get_option('wpifw_buyer') ) {
            update_option('wpifw_buyer', $customer_info);
        }

        update_option('woo-invoice-activation-time', time());

    }

}
