<?php
/**
 * Scripts for plugin activation hook
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/hooks
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Update Default opton when activate the plugin.
 */
register_activation_hook(CHALLAN_FREE_ROOT_FILE, 'challan_default_config');
function challan_default_config() {

    if ( ! get_option( 'challan_major_update' ) ) {
        update_option( 'challan_major_update', 1 );
    }

//    // Enable PDF Invoicing.
//    if ( '0' !== get_option( 'wpifw_invoicing' ) ) {
//        update_option( 'wpifw_invoicing', '1' );
//    }
//
//    // Default set Site Language.
//    if ( ! get_option( 'wpifw_pdf_document_language' ) ) {
//        update_option( 'wpifw_pdf_document_language', 'challan_pdf_site_language' );
//    }
//
//    // Default enable download button.
//    if ( '0' !== get_option( 'wpifw_download' ) ) {
//        update_option( 'wpifw_download', '1' );
//    }
//
//    // Default download button status.
//    if ( ! get_option( 'wpifw_invoice_download_check_list' ) ) {
//        update_option( 'wpifw_invoice_download_check_list', 'processing,completed,always_allow' );
//    }
//
//    // Default enable order email.
//    if ( '0' !== get_option( 'wpifw_order_email' ) ) {
//        update_option( 'wpifw_order_email', '1' );
//    }
//
//
//    // Default value for email status.
//    if ( ! get_option( 'wpifw_email_attach_check_list' ) ) {
//        update_option( 'wpifw_email_attach_check_list', 'customer_completed_order,customer_processing_order' );
//    }
//
//    // Default Enable Phone Number.
//    if ( ! get_option('wpifw_display_phone') ) {
//        update_option('wpifw_display_phone', '0');
//    }
//
//    // Default Enable Phone Number.
//    if ( ! get_option('wpifw_display_email') ) {
//        update_option('wpifw_display_email', '0');
//    }
//
//    // Default Company Logo.
//    if ( ! get_option( 'wpifw_logo_attachment_id' ) ) {
//        if ( has_custom_logo() ) {
//            $custom_logo_id  = get_theme_mod( 'custom_logo' );
//            $custom_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
//            update_option( 'wpifw_logo_attachment_id', $custom_logo_id );
//        }
//    }
//
//    // Default Set Company Name.
//    if ( ! get_option( 'wpifw_cname' ) ) {
//        update_option('wpifw_cname',get_bloginfo( 'name' ) );
//    }
//
//    /**
//     * Invoice Default Option.
//     */
//    // Default Set Next Invoice Number 1
//    if ( ! get_option( 'wpifw_invoice_no' ) ) {
//        update_option( 'wpifw_invoice_no', '1' );
//    }
//
//
//    // Default currency symbol enable.
//    if ( '1' !== get_option( 'wpifw_currency_code' ) ) {
//        update_option( 'wpifw_currency_code', '0' );
//    }
//
//    // Default Date Format.
//    if ( ! get_option( 'wpifw_date_format' ) ) {
//        update_option( 'wpifw_date_format', 'd/m/y' );
//    }
//
//    // Default set A4 paper size for invoice
//    if ( ! get_option( 'wpifw_invoice_paper_size' ) ) {
//        update_option( 'wpifw_invoice_paper_size', 'A4' );
//    }
//
//    // Default set Product Image.
//    if ( ! get_option( 'wpifw_product_image_show' ) ) {
//        update_option( 'wpifw_product_image_show', '1' );
//    }
//
//    // Default set product title length.
//    if ( ! get_option( 'wpifw_invoice_product_title_length' ) ) {
//        update_option( 'wpifw_invoice_product_title_length', '10' );
//    }
//
//    // Default set product description length.
//    if ( ! get_option( 'wpifw_invoice_description_limit' ) ) {
//        update_option( 'wpifw_invoice_description_limit', '20' );
//    }
//
//    /**----- Sorting------*/
//    // Default Header Sorting.
//     if ( ! get_option( 'wpifw_header_sort_index' ) ) {
//         update_option( 'wpifw_header_sort_index', 'ChallanLogo,BarCode,CompanyDetails' );
//     }
//
//     // Default Address Sorting.
//    if ( ! get_option( 'address_sort_index' ) ) {
//        update_option( 'address_sort_index', 'BillingAddress,ShippingAddress,OrderData' );
//    }
//
//    // Default Product Sorting.
//    if ( ! get_option( 'product_header_sort_index' ) ) {
//        update_option( 'product_header_sort_index', 'Item,Image,Cost,Qty,Total,Tax,TaxRate' );
//    }
//
//    // Default Product Sorting.
//    if ( ! get_option( 'order_total_sort_index' ) ) {
//        update_option( 'order_total_sort_index', 'subtotal,discount_total,tax_total,shipping_total,fees,total_without_tax,grand_total,total_refund,net_total' );
//    }
//
//    /**
//     * Packing slip default value set.
//     */
//
//    // Default set A4 paper size for packing slip.
//    if ( ! get_option( 'wpifw_pickingslip_paper_size' ) ) {
//        update_option( 'wpifw_pickingslip_paper_size', 'A4' );
//    }
//
//
//    // Default Set SKU for packing slip.
//    if ( ! get_option( 'wpifw_packingslip_disid' ) ) {
//        update_option( 'wpifw_packingslip_disid', 'SKU' );
//    }
//
//
//    // Default product title length for packing slip.
//    if ( ! get_option('wpifw_packingslip_product_title_length') ) {
//        update_option('wpifw_packingslip_product_title_length', '10');
//    }
//
//    // Default product description length for packing slip.
//    if ( ! get_option('wpifw_packingslip_description_limit') ) {
//        update_option('wpifw_packingslip_description_limit', '20');
//    }
//
//
//    /**----- Sorting------*/
//    // Default Packing Slip Header Sorting.
//    if ( ! get_option('wpifw_ps_header_sort_index') ) {
//        update_option('wpifw_ps_header_sort_index', 'ChallanLogo,BarCode,CompanyDetails');
//    }
//
//    // Default Packing Slip Address Sorting.
//    if ( ! get_option('wpifw_ps_address_sort_index') ) {
//        update_option('wpifw_ps_address_sort_index', 'BillingAddress,ShippingAddress,OrderData');
//    }
//
//    // Default Packing Product Sorting.
//    if ( ! get_option('wpifw_ps_product_header_sort_index') ) {
//        update_option('wpifw_ps_product_header_sort_index', 'Item,Image,Dimensions,Weight,Quantity');
//    }
//
//
//    /**
//     * Update Delivery Address Default Value.
//     *
//     */
//
//    // Default value for shipping label column.
//    if ( ! get_option('wpifw_delivery_address_num_col') ) {
//        update_option('wpifw_delivery_address_num_col', '3');
//    }
//
//    // Default value for shipping label row.
//    if ( ! get_option('wpifw_delivery_address_num_row') ) {
//        update_option('wpifw_delivery_address_num_row', '3');
//    }
//
//    // Default shipping label font size.
//    if ( ! get_option('wpifw_delivery_address_font_size') ) {
//        update_option('wpifw_delivery_address_font_size', '12');
//    }
//
//    // Default shipping label paper size.
//    if ( ! get_option('wpifw_delivery_address_paper') ) {
//        update_option('wpifw_delivery_address_paper', 'A4');
//    }
//
//    // Default shipping label paper size.
//    if ( ! get_option('wpifw_delivery_address_buyer') ) {
//        update_option('wpifw_delivery_address_buyer', '{{billing_company}}
//{{billing_address_1}}
//{{billing_first_name}}
//{{billing_last_name}}
//{{billing_address_2}}
//{{billing_city}}
//{{billing_state}}
//{{billing_postcode}}
//{{billing_country}}
//{{billing_phone}}
//{{billing_email}}');
//    }

}


function challan_update_old_option(){

    /*-------------------------------------------------------------
        Update Old Order meta.
    --------------------------------------------------------------*/

    // Update Order Meta Label.
    $challan_old_order_meta_label = get_option('_winvoice_order_meta_label');
    if ( ! empty($challan_old_order_meta_label) && is_array($challan_old_order_meta_label) ) {
        $new_value = implode(',', $challan_old_order_meta_label);
        update_option('_winvoice_order_meta_label', $new_value);
    }

    // Update Order Meta Name.
    $challan_old_order_meta_name = get_option('_winvoice_order_meta_name');
    if ( ! empty($challan_old_order_meta_name) && is_array($challan_old_order_meta_name) ) {
        $new_value = implode(',', $challan_old_order_meta_name);
        update_option('_winvoice_order_meta_name', $new_value);
    }


    // Update Order Meta Value.
    $challan_old_order_meta_position = get_option('_winvoice_order_meta_name_position');
    if ( ! empty($challan_old_order_meta_position) && is_array($challan_old_order_meta_position) ) {
        $new_value = implode(',', $challan_old_order_meta_position);
        update_option('_winvoice_order_meta_position', $new_value);
        delete_option('_winvoice_order_meta_name_position');
    }


    /*-------------------------------------------------------------
         Packing Slip Order Item Meta.
    --------------------------------------------------------------*/

    // Update Order Meta Label.
    $challan_old_order_meta_label = get_option('_winvoice_order_meta_label_ps');
    if ( ! empty($challan_old_order_meta_label) && is_array($challan_old_order_meta_label) ) {
        $new_value = implode(',', $challan_old_order_meta_label);
        update_option('_winvoice_order_meta_label_ps', $new_value);
    }

    // Update Order Meta Name.
    $challan_old_order_meta_name = get_option('_winvoice_order_meta_name_ps');
    if ( ! empty($challan_old_order_meta_name) && is_array($challan_old_order_meta_name) ) {
        $new_value = implode(',', $challan_old_order_meta_name);
        update_option('_winvoice_order_meta_name_ps', $new_value);
    }


//     // Update Order Meta Value.
    $challan_old_order_meta_position = get_option('_winvoice_order_meta_name_position_ps');
    if ( ! empty($challan_old_order_meta_position) && is_array($challan_old_order_meta_position) ) {
        $new_value = implode(',', $challan_old_order_meta_position);
        update_option('_winvoice_order_meta_position_ps', $new_value);
        delete_option('_winvoice_order_meta_name_position_ps');
    }

    $meta_arr = [
        'wpifw_custom_post_meta',
        'wpifw_order_item_meta',
        'wpifw_custom_post_meta_ps',
        'wpifw_order_item_meta_ps',
    ];

    foreach ( $meta_arr as $key => $val ) {
        $meta = get_option($val);
        if ( ! empty($meta) && is_array($meta) ) {
            $meta  = challan_get_product_meta_data($meta);
            update_option($val , $meta);
        }
    }
}

function challan_get_product_meta_data( $meta ) {
    $post_meta = [];
    foreach ( $meta as $key => $label ) {

        array_push($post_meta, [
            'meta_name'  => $key,
            'meta_label' => $label,
        ]);
    }
    return wp_json_encode($post_meta);
}

if ( ! get_option('challan_major_update') ) {
    challan_update_old_option();
}


/**
 * Load all the scripts those require on plugin update or upgrade
 */
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/activator/download-mpdf-lib-on-plugin-activate.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/activator/download-default-fonts-for-mpdf-on-plugin-activate.php';
