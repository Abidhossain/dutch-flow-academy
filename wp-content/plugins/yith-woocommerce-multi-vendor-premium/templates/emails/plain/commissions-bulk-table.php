<?php
/**
 * Plain admin commission bulk actions mail.
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 2.0.0
 * @var YITH_Vendors_Commission[] $commissions An array of commissions.
 * @var string                    $new_commission_status The new commission status.
 * @var boolean                   $show_note Show note or not.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$commissions_total  = 0;
$shipping_fee_total = 0;
$wc_price_args      = apply_filters( 'yith_wcmv_commissions_bulk_email_wc_price_args', array() );

echo esc_html__( 'Commission ID', 'yith-woocommerce-product-vendors' ) . ' || ' . "\t";
echo esc_html__( 'Order ID', 'yith-woocommerce-product-vendors' ) . ' || ' . "\t";
echo esc_html__( 'SKU', 'yith-woocommerce-product-vendors' ) . ' || ' . "\t";
echo esc_html__( 'Amount', 'yith-woocommerce-product-vendors' ) . ' || ' . "\t";
echo '%' . esc_html_x( 'Rate', '[Email]: meanse commissions rate', 'yith-woocommerce-product-vendors' ) . "\t";
echo esc_html__( 'New status', 'yith-woocommerce-product-vendors' ) . "\t";

if ( $show_note ) {
	echo ' || ' . esc_html__( 'Note', 'yith-woocommerce-product-vendors' ) . ' || ';
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";


foreach ( $commissions as $commission ) :
	if ( 'shipping' === $commission->get_type() ) {
		$shipping_fee_total = $shipping_fee_total + $commission->get_amount();
	} else {
		$commissions_total = $commissions_total + $commission->get_amount();
	}

	echo '#' . esc_html( $commission->get_id() );
	echo ' || ' . "\t";

	$order_id = $commission->get_order_id();

	if ( ! empty( $order_id ) ) :
		echo '#' . esc_html( $order_id );
	else :
		echo ' - ';

	endif;
	echo ' || ' . "\t";

	if ( 'shipping' === $commission->get_type() ) {
		$info = _x( 'Shipping', '[admin] part of shipping fee details', 'yith-woocommerce-product-vendors' );
	} else {
		$info = '-';
		$item = $commission->get_item();
		if ( $item instanceof WC_Order_Item ) {
			$product = $commission->get_product();

			if ( $product ) {
				$sku = $product->get_sku( 'view' );

				if ( ! empty( $sku ) ) {
					$info = $sku;
				}
			}
		}
	}

	echo esc_html( $info );
	echo ' || ' . "\t";
	echo wp_kses_post( $commission->get_amount( 'display' ) );
	echo ' || ' . "\t";
	echo wp_kses_post( $commission->get_rate( 'display' ) );
	echo ' || ' . "\t";
	echo esc_html( $new_commission_status );

	if ( $show_note ) :
		echo ' || ' . "\t";
		$msg = '-';

		if ( $item instanceof WC_Order_Item_Product ) {
			// Check if the commissions included tax.
			$commission_included_tax = wc_get_order_item_meta( $item->get_id(), '_commission_included_tax', true );
			// Check if the commissions included coupon.
			$commission_included_coupon = wc_get_order_item_meta( $item->get_id(), '_commission_included_coupon', true );

			$msg = YITH_Vendors_Commissions::get_tax_and_coupon_management_message( $commission_included_tax, $commission_included_coupon );
		}

		echo wp_kses_post( $msg );
	endif;
	echo "\n";
endforeach;

if ( ! empty( $commissions_total ) ) :
	echo esc_html_x( 'Total product commissions', '[Email commissions report]: Total commissions amount', 'yith-woocommerce-product-vendors' );
	echo ' : ';
	echo wc_price( $commissions_total, $wc_price_args ); // phpcs:ignore
	echo "\n\n";
endif;

if ( ! empty( $shipping_fee_total ) ) :
	echo esc_html_x( 'Total shipping fee', '[Email commissions report]: Total commissions amount', 'yith-woocommerce-product-vendors' );
	echo ' : ';
	echo wc_price( $shipping_fee_total, $wc_price_args ); // phpcs:ignore
	echo "\n\n";
endif;
