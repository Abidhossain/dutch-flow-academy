<?php
/**
 * Vendor Email Order Items template
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

$vendor_products          = $vendor->get_products(
	array(
		'fields'      => 'ids',
		'post_status' => 'any',
	)
);
$total_commissions_amount = 0;

?>
	<tr>
<?php
foreach ( $items as $item_id => $item ) :

	if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		continue;
	}

	$product       = $item->get_product();
	$product_id    = $product ? $item->get_product_id() : 0;
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

	?>
	<tr class="<?php echo esc_attr( apply_filters( 'woocoomerce_order_item_class', 'order_item', $item, $order ) ); ?>">
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<?php

			if ( $show_image ) {
				echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', '<img src="' . ( ! empty( $image_id ) ? current( wp_get_attachment_image_src( $image_id, $image_size ) ) : wc_placeholder_img_src() ) . '" alt="' . __( 'Product image', 'yith-woocommerce-product-vendors' ) . '" height="' . esc_attr( $image_size[1] ) . '" width="' . esc_attr( $image_size[0] ) . '" style="vertical-align:middle; margin-right: 10px;" />', $item ) );
			}

			// Product name.
			echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false ) );

			// SKU.
			if ( ! empty( $sku ) ) {
				echo wp_kses_post( ' (#' . $sku . ')' );
			}

			// Allow other plugins to add additional product information here.
			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

			wc_display_item_meta( $item );

			if ( $commission ) {
				$commission_link = '<a href="' . esc_url( $commission->get_view_url( 'admin' ) ) . '">' . $commission->get_id() . '</a>';
				echo '<br/><small>' . esc_html_x( 'Commission ID:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' ' . $commission_link . '</small>'; // phpcs:ignore
			}

			// File URLs.
			if ( $show_download_links ) {

				$download_files = $item->get_item_downloads();
				$i              = 0;

				foreach ( $download_files as $download_id => $file ) {
					$i++;

					if ( count( $download_files ) > 1 ) {
						// Translators: %d is the index of the download.
						$prefix = sprintf( __( 'Download %d', 'yith-woocommerce-product-vendors' ), $i );
					} elseif ( 1 === $i ) {
						$prefix = __( 'Download', 'yith-woocommerce-product-vendors' );
					}

					?>
					<br/><small><?php echo esc_html( $prefix ); ?>:
						<a href="<?php echo esc_url( $file['download_url'] ); ?>" target="_blank"><?php echo esc_html( $file['name'] ); ?></a></small>
					<?php
				}
			}

			// Allow other plugins to add additional product information here.
			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );

			?>
		</td>
		<td style="text-align:center; vertical-align:middle; border: 1px solid #eee;"><?php echo esc_html( ! empty( $sku ) ? $sku : '-' ); ?></td>
		<td style="text-align:center; vertical-align:middle; border: 1px solid #eee;"><?php echo esc_html( $item->get_quantity() ); ?></td>
		<?php $total = 'split' === get_option( 'yith_wpv_commissions_tax_management', 'website' ) ? ( $item->get_total() + $item->get_total_tax() ) : $item->get_total(); ?>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
			<?php echo wc_price( $total, array( 'currency', $order->get_currency() ) ); // phpcs:ignore ?>
		</td>
		<td style="text-align:center; vertical-align:middle; border: 1px solid #eee;">
			<?php echo $commission ? $commission->get_rate( 'display' ) : '-'; // phpcs:ignore ?>
		</td>
		<?php if ( $tax_credited_to_vendor ) : ?>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo wc_price( $order->get_item_tax( $item ), array( 'currency', $order->get_currency() ) ); // phpcs:ignore ?></td>
		<?php endif; ?>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo $commission ? $commission->get_amount( 'display' ) : '-'; // phpcs:ignore
		?>
		</td>
	</tr>
	<?php
	$commission && $total_commissions_amount += $commission->get_amount();

	if ( ! empty( $purchase_note ) ) :
		?>
		<tr>
			<td colspan="3" style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
				<?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); // phpcs:ignore ?>
			</td>
		</tr>
		<?php
	endif;
endforeach;
?>
	<tr>
		<td colspan="<?php echo $tax_credited_to_vendor ? 5 : 4; ?>" style="font-weight: bold; text-align:left; vertical-align:middle; border: 1px solid #eee;">
			<strong><?php esc_html_e( 'Total', 'yith-woocommerce-product-vendors' ); ?></strong>
		</td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo wc_price( $total_commissions_amount, array( 'currency', $order->get_currency() ) ); // phpcs:ignore ?></td>
	</tr>
<?php
