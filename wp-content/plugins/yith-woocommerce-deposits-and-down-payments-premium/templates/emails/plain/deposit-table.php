<?php
/**
 * Deposit table (plain)
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

$items        = $parent_order->get_items( 'line_item' );
$total_paid   = 0;
$total_to_pay = 0;

if ( ! empty( $items ) ) :
	foreach ( $items as $item_id => $item ) :
		if ( ! isset( $item['deposit'] ) || ! $item['deposit'] ) {
			continue;
		}

		$product  = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : $parent_order->get_product_from_item( $item );
		$suborder = wc_get_order( $item['full_payment_id'] );

		if ( ! $product || ! $suborder || $suborder->has_status( array( 'completed', 'processing', 'cancelled' ) ) ) {
			continue;
		}

		$paid   = $parent_order->get_item_total( $item, true );
		$paid  += $suborder->has_status( array( 'processing', 'completed' ) ) ? $suborder->get_total() : 0;
		$to_pay = $suborder->has_status( array( 'processing', 'completed' ) ) ? 0 : $suborder->get_total();

		$total_paid   += $paid;
		$total_to_pay += $to_pay;

		echo esc_html( sprintf( '%s #%d', __( 'Order', 'yith-woocommerce-deposits-and-down-payments' ), $suborder->get_order_number() ) );
		echo "\n" . esc_html( $item['name'] );
		echo "\n" . esc_html( wc_get_order_status_name( $suborder->get_status() ) );
		echo "\n" . esc_html( sprintf( '%s (of %s)', sprintf( get_woocommerce_price_format(), '', $paid ), sprintf( get_woocommerce_price_format(), '', $paid + $to_pay ) ) );
		echo "\n\n";
	endforeach;
endif;

echo "==========\n\n";

echo esc_html( sprintf( '%s: %s', __( 'Total paid', 'yith-woocommerce-deposits-and-down-payments' ), sprintf( get_woocommerce_price_format(), '', $total_paid ) ) ) . "\n";
echo esc_html( sprintf( '%s: %s', __( 'Total to pay', 'yith-woocommerce-deposits-and-down-payments' ), sprintf( get_woocommerce_price_format(), '', $total_to_pay ) ) ) . "\n";
