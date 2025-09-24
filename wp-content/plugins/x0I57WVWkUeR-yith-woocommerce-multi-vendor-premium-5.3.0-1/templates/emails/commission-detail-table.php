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

$item_name = ! empty( $item ) ? $item->get_name() : '';
if ( empty( $item_name ) ) {
	$item_name = _x( 'Shipping fee', '[admin]: commission type', 'yith-woocommerce-product-vendors' );
}
?>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Status', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $commission->get_status( 'display' ) ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Date', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $commission->get_date( 'display' ) ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Amount', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $commission->get_amount( 'display', array( 'currency' => $order->get_currency() ) ); // phpcs:ignore ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'PayPal email', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( ! empty( $vendor->get_meta( 'paypal_email' ) ) ? $vendor->get_meta( 'paypal_email' ) : '-' ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Vendor', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $vendor->get_name() ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Order number', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $order->get_order_number() ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Product', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $item_name ); ?></td>
</tr>
