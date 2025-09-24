<?php
/**
 * Deposit list - used in plugin emails
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $email           YITH_WCDP_Email
 * @var $parent_order    WC_Order
 * @var $suborders       array
 * @var $hide_pay_button bool
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<table style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin: 25px 0; border-top: 1px solid #ebebeb; border-bottom: 1px solid #ebebeb;">
	<tbody>
	<?php
	$suborders = ! empty( $suborder ) ? (array) $suborder : YITH_WCDP_Suborders()->get_suborders( $parent_order->get_id() );

	if ( ! empty( $suborders ) ) :
		foreach ( $suborders as $suborder ) :
			if ( ! $suborder instanceof WC_Order ) {
				$suborder = wc_get_order( $suborder );
			}

			if ( ! $suborder || $suborder->has_status( array( 'completed', 'processing' ) ) ) {
				continue;
			}

			$suborder_items = $suborder->get_items();
			$first_item     = true;

			foreach ( $suborder_items as $item_id => $item ) {
				if ( $item->get_total() && ! apply_filters( 'yith_wcdp_email_deposit_list_skip_item', false, $item, $suborder, $parent_order ) ) {
					continue;
				}

				unset( $suborder_items[ $item_id ] );
			}

			foreach ( $suborder_items as $item ) :
				$product = $item->get_product();
				$image   = $product->get_image( array( 100, 100 ) );
				$total   = $item->get_quantity() * $suborder->get_item_total( $item, true );
				?>
				<tr>
					<td style="padding-left: 0;width: 20%;">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
					</td>
					<td style="width: 55%;">
						<span class="item-name">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ); ?>
						</span><br/>
						<span class="item-price">
							<?php echo wp_kses_post( wc_price( $total, array( 'currency' => $suborder->get_currency() ) ) ); ?></span>
					</td>

					<?php if ( $first_item ) : ?>
					<td style="padding-right: 0width: 25%;text-align: right;" rowspan="<?php echo count( $suborder_items ); ?>">
						<?php if ( $suborder->needs_payment() && empty( $hide_pay_button ) ) : ?>
							<a href="<?php echo esc_url( $suborder->get_checkout_payment_url() ); ?>" style="<?php echo esc_attr( $email->get_button_inline_style() ); ?>">
								<?php esc_html_e( 'Pay now', 'yith-woocommerce-deposits-and-down-payments' ); ?>
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url( $suborder->get_view_order_url() ); ?>" style="<?php echo esc_attr( $email->get_button_inline_style() ); ?>">
								<?php esc_html_e( 'View details', 'yith-woocommerce-deposits-and-down-payments' ); ?>
							</a>
						<?php endif; ?>
					</td>
					<?php endif; ?>
				</tr>
				<?php
				$first_item = false;
			endforeach;
		endforeach;
	endif;
	?>
	</tbody>
</table>
