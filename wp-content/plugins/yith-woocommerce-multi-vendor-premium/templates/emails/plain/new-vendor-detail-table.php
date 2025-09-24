<?php
/**
 * Admin new vendor details table plain
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var YITH_Vendor $vendor Vendor object.
 * @var WP_User $owner User object.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


echo esc_html__( 'Owner', 'yith-woocommerce-product-vendors' ) . ':';
echo esc_html( $owner->user_firstname . ' ' . $owner->user_lastname ) . " \n\n";

echo esc_html( apply_filters( 'yith_wcmv_vendor_admin_settings_store_name_label', __( 'Store name', 'yith-woocommerce-product-vendors' ) ) ) . ':';
echo esc_html( $vendor->get_name() ) . " \n\n";

echo esc_html__( 'Location', 'yith-woocommerce-product-vendors' ) . ':';
echo esc_html( $vendor->get_formatted_address() ) . " \n\n";

echo esc_html( apply_filters( 'yith_wcmv_vendor_admin_settings_store_email_label', __( 'Store email', 'yith-woocommerce-product-vendors' ) ) ) . ':';
echo esc_html( ! empty( $vendor->get_meta( 'store_email' ) ) ? $vendor->get_meta( 'store_email' ) : '-' ) . " \n\n";

echo esc_html__( 'Phone', 'yith-woocommerce-product-vendors' ) . ':';
echo esc_html( ! empty( $vendor->get_meta( 'telephone' ) ) ? $vendor->get_meta( 'telephone' ) : '-' ) . " \n\n";
