<?php
/**
 * Admin commission bulk actions mail.
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 * @var YITH_Vendors_Commission[] $commissions An array of commissions.
 * @var string                    $new_commission_status The new commission status.
 * @var boolean                   $show_note Show note or not.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$commissions_total  = 0;
$shipping_fee_total = 0;
$wc_price_args      = apply_filters( 'yith_wcmv_commissions_bulk_email_wc_price_args', array() );

if ( ! is_array( $commissions ) ) {
	$commissions = array( $commissions );
}

?>

<tr style="border: 1px solid #eee;">
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Commission ID', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Order ID', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:center; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'SKU', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Amount', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo '%' . esc_html_x( 'Rate', '[Email]: meanse commissions rate', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'New status', 'yith-woocommerce-product-vendors' ); ?></td>

	<?php if ( $show_note ) : ?>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Note', 'yith-woocommerce-product-vendors' ); ?></td>
	<?php endif; ?>

</tr>

<?php
foreach ( $commissions as $commission ) :
	$order_id        = $commission->get_order_id();
	$commission_type = $commission->get_type();
	?>
	<?php
	if ( 'shipping' === $commission_type ) {
		$shipping_fee_total = $shipping_fee_total + $commission->get_amount();
	} else {
		$commissions_total = $commissions_total + $commission->get_amount();
	}
	?>
	<tr style="border: 1px solid #eee;">
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<a href="<?php echo esc_url( $commission->get_view_url( 'admin' ) ); ?>">
				<?php echo '#' . esc_html( $commission->get_id() ); ?>
			</a>
		</td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<?php
			if ( ! empty( $order_id ) ) :
				$order_uri = apply_filters( 'yith_wcmv_commissions_list_table_order_url', admin_url( 'post.php?post=' . absint( $commission->get_order_id() ) . '&action=edit' ), $commission, $commission->get_order() );
				?>
				<a href="<?php echo esc_url( $order_uri ); ?>">
					<?php echo '#' . esc_html( $order_id ); ?>
				</a>
			<?php endif; ?>
		</td>
		<td style="text-align:center; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<?php
			if ( 'shipping' === $commission_type ) {
				$info = _x( 'Shipping', '[admin] part of shipping fee details', 'yith-woocommerce-product-vendors' );
			} else {
				$info = '-';
				$item = $commission->get_item();
				if ( $item instanceof WC_Order_Item ) {
					$product = $commission->get_product();

					if ( $product ) {
						$sku = $product->get_sku( 'view' );

						if ( ! empty( $sku ) ) {
							if ( apply_filters( 'yith_wcmv_show_product_uri_in_commissions_bulk_email', true ) ) {
								$product_url = apply_filters( 'yith_wcmv_commissions_list_table_product_url', get_edit_post_link( $product->get_id() ), $product, $commission );
								$info        = sprintf( '<a href="%s">%s</a>', $product_url, $sku );
							} else {
								$info = $sku;
							}
						}
					}
				}
			}

			echo wp_kses_post( $info );
			?>
		</td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo wp_kses_post( $commission->get_amount( 'display' ) ); ?></td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo wp_kses_post( $commission->get_rate( 'display' ) ); ?></td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $new_commission_status ); ?></td>

		<?php if ( $show_note ) : ?>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word; ">
				<?php
				$msg = '-';

				if ( $item instanceof WC_Order_Item_Product ) {
					// Check if the commissions included tax.
					$commission_included_tax = wc_get_order_item_meta( $item->get_id(), '_commission_included_tax' );
					// Check if the commissions included coupon.
					$commission_included_coupon = wc_get_order_item_meta( $item->get_id(), '_commission_included_coupon' );

					$msg = YITH_Vendors_Commissions::get_tax_and_coupon_management_message( $commission_included_tax, $commission_included_coupon );
				}

				echo wp_kses_post( $msg );

				?>
			</td>
		<?php endif; ?>

	</tr>
<?php endforeach; ?>

<?php if ( ! empty( $commissions_total ) ) : ?>
	<tr style="border: 1px solid #eee;">
		<td colspan="5" style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<strong>
				<?php echo esc_html_x( 'Total product commissions', '[Email commissions report]: Total commissions amount', 'yith-woocommerce-product-vendors' ); ?>
			</strong>
		</td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<?php echo wc_price( $commissions_total, $wc_price_args ); // phpcs:ignore ?>
		</td>
	</tr>
<?php endif; ?>

<?php if ( ! empty( $shipping_fee_total ) ) : ?>
	<tr style="border: 1px solid #eee;">
		<td colspan="5" style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<strong>
				<?php echo esc_html_x( 'Total shipping fee', '[Email commissions report]: Total commissions amount', 'yith-woocommerce-product-vendors' ); ?>
			</strong>
		</td>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<?php echo wc_price( $shipping_fee_total, $wc_price_args ); // phpcs:ignore ?>

		</td>
	</tr>
<?php endif; ?>
