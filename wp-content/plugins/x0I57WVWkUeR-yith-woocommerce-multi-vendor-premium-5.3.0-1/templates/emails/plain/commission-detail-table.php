<?php
/**
 * Commission detail table for email
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 2.0.0
 *
 * @var YITH_Vendors_Commission $commission The commission object.
 * @var WC_Order $order The order object related to commission.
 * @var YITH_Vendor $vendor The vendor object related to commission.
 * @var WC_Order_Item $item The order item object related to commission.
 * @var WC_Product $product The product object related to commission.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( is_null( $vendor ) || is_null( $commission ) || ! $order instanceof WC_Order ) {
	return false;
}

echo esc_html__( 'Status', 'yith-woocommerce-product-vendors' ) . ':';
echo wp_kses_post( $commission->get_status( 'display' ) ) . " \n\n";

echo esc_html__( 'Date', 'yith-woocommerce-product-vendors' ) . ':';
echo wp_kses_post( $commission->get_date( 'display' ) ) . " \n\n";

echo esc_html__( 'Amount', 'yith-woocommerce-product-vendors' ) . ':';
echo wp_kses_post( $commission->get_amount( 'display', array( 'currency' => $order->get_currency() ) ) ) . " \n\n";

echo esc_html__( 'PayPal email', 'yith-woocommerce-product-vendors' ) . ':';
echo esc_html( ! empty( $vendor->get_meta( 'paypal_email' ) ) ? $vendor->get_meta( 'paypal_email' ) : '-' );
echo " \n\n";

echo esc_html__( 'Vendor', 'yith-woocommerce-product-vendors' ) . ':';
echo esc_html( $vendor->get_name() ) . " \n\n";

echo esc_html__( 'Order number', 'yith-woocommerce-product-vendors' ) . ':';
echo esc_html( $order->get_order_number() ) . " \n\n";

echo esc_html__( 'Product', 'yith-woocommerce-product-vendors' ) . ':';
echo wp_kses_post( ! empty( $item['name'] ) ? $item['name'] : _x( 'Shipping fee', '[admin]: commission type', 'yith-woocommerce-product-vendors' ) );
echo " \n\n";
