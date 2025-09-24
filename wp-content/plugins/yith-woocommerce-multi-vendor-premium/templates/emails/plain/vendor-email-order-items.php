<?php
/**
 * Vendor Email Order Items template plain
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var WC_Order        $order                  Order object.
 * @var YITH_Vendor     $vendor                 Vendor object.
 * @var WC_Order_Item[] $items                  An array of order items object.
 * @var boolean         $show_download_links    True to show item download link, false otherwise.
 * @var boolean         $show_purchase_note     True to show order purchase note, false otherwise.
 * @var boolean         $show_sku               True to show item sku, false otherwise.
 * @var boolean         $show_image             True to show item image, false otherwise.
 * @var boolean         $tax_credited_to_vendor True if tax are credited to vendor, false otherwise.
 * @var boolean         $plain_text             True if is a plain email, false otherwise.
 * @var array           $image_size             The image size to use for item.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$total_commissions_amount = 0;
$vendor_products          = $vendor->get_products( array( 'fields' => 'ids' ) );

foreach ( $items as $item_id => $item ) :

	if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		continue;
	}

	$product       = $item->get_product();
	$product_id    = $product ? ( $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id() ) : 0;
	$commission_id = $item->get_meta( '_commission_id' );
	$commission    = $commission_id ? yith_wcmv_get_commission( $commission_id ) : false;

	if ( ! empty( $product_id ) && ! in_array( $product_id, $vendor_products ) ) {
		continue;
	}

	if ( $product instanceof WC_Product ) {
		$sku           = $show_sku ? $product->get_sku() : '';
		$purchase_note = $show_purchase_note ? $product->get_purchase_note() : '';
		$image_id      = $product->get_image_id();
	}

	echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false ) );

	// SKU.
	if ( ! empty( $sku ) ) {
		echo wp_kses_post( ' (#' . $sku . ')' );
	}

	// Allow other plugins to add additional product information here.
	do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

	// Commission.
	if ( $commission ) {
		echo "\n" . esc_html_x( 'Commission ID:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' ' . absint( $commission->get_id() ) . ' (' . esc_url( $commission->get_view_url() ) . ')';
		echo "\n" . esc_html_x( 'Commission rate:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' ' . esc_html( $commission->get_rate( 'display' ) );
		echo "\n" . esc_html_x( 'Tax:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' ' . esc_html( $order->get_item_tax( $item ) );
		echo "\n" . esc_html_x( 'Earnings:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' ' . esc_html( $commission->get_amount() );
	}

	// Quantity.
	// Translators: %s stand for the item quantity.
	echo "\n" . esc_html( sprintf( __( 'Quantity: %s', 'yith-woocommerce-product-vendors' ), $item->get_quantity() ) );

	// Cost.
	// Translators: %s stand for the item cost.
	echo "\n" . esc_html( sprintf( __( 'Cost: %s', 'yith-woocommerce-product-vendors' ), $order->get_formatted_line_subtotal( $item ) ) );

	$commission && $total_commissions_amount += $commission->get_amount();

	// Download URLs.
	if ( $show_download_links ) {
		$download_files = $order->get_item_downloads( $item );
		$i              = 0;

		foreach ( $download_files as $download_id => $file ) {
			$i++;

			if ( count( $download_files ) > 1 ) {
				// Translators: %d is the index of the download.
				$prefix = sprintf( __( 'Download %d', 'yith-woocommerce-product-vendors' ), $i );
			} elseif ( 1 === $i ) {
				$prefix = __( 'Download', 'yith-woocommerce-product-vendors' );
			}

			echo "\n" . esc_html( $prefix ) . '(' . esc_html( $file['name'] ) . '): ' . esc_url( $file['download_url'] );
		}
	}

	// Allow other plugins to add additional product information here.
	do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );

	// Note.
	if ( ! empty( $purchase_note ) ) {
		echo "\n" . do_shortcode( wp_kses_post( $purchase_note ) );
	}

	echo "\n\n";

endforeach;

// Cost.
// Translators: %s The commission cost.
echo "\n" . esc_html( sprintf( __( 'Total: %s', 'yith-woocommerce-product-vendors' ), wc_price( $total_commissions_amount, array( 'currency' => $order->get_currency() ) ) ) );
