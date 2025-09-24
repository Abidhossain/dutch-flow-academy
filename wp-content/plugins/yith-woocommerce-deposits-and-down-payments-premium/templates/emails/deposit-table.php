<?php
/**
 * Deposit table (HTML)
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $parent_order WC_Order
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin: 25px 0;" border="1">
	<thead>
	<tr>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Suborder', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Status', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Total', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$items        = $parent_order->get_items( 'line_item' );
	$total_paid   = 0;
	$total_to_pay = 0;

	if ( ! empty( $items ) ) :
		foreach ( $items as $item_id => $item ) :
			if ( ! isset( $item['deposit'] ) || ! $item['deposit'] ) {
				continue;
			}

			$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : $parent_order->get_product_from_item( $item );

			$suborder = wc_get_order( $item['full_payment_id'] );

			if ( ! $suborder || apply_filters( 'yith_wcdp_email_deposit_table_skip_suborder', false, $suborder, $item, $parent_order ) ) {
				continue;
			}

			$deposit_balance = $suborder->get_total();

			if ( ! $product || ! $suborder || $suborder->has_status( array( 'completed', 'processing', 'cancelled' ) ) ) {
				continue;
			}
			$paid  = $parent_order->get_line_total( $item, true );
			$paid += $suborder->has_status( array( 'processing', 'completed' ) ) ? $suborder->get_total() : 0;

			$total_paid   += $paid;
			$total_to_pay += $deposit_balance;
			?>
			<tr>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
					<?php printf( '#%d', esc_html( $suborder->get_order_number() ) ); ?>
				</td>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
					<?php printf( '<a href="%s">%s</a>', esc_url( $product->get_permalink() ), esc_html( $item['name'] ) ); ?>
				</td>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
					<?php echo esc_html( wc_get_order_status_name( $suborder->get_status() ) ); ?>
				</td>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
					<?php echo wp_kses_post( sprintf( '%s (of %s)', wc_price( $paid, array( 'currency' => $suborder->get_currency() ) ), wc_price( $paid + $deposit_balance, array( 'currency' => $suborder->get_currency() ) ) ) ); ?>
				</td>
			</tr>
			<?php
		endforeach;
	endif;
	?>
	</tbody>
	<tfoot>
	<tr>
		<th class="td" scope="col" colspan="3" style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; text-align:left; border-top-width: 4px;"><?php esc_html_e( 'Total paid:', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
		<td class="td" scope="col" style="text-align:left; border-top-width: 4px;"><?php echo wp_kses_post( wc_price( $total_paid, array( 'currency' => $parent_order->get_currency() ) ) ); ?></td>
	</tr>
	<tr>
		<th class="td" scope="col" colspan="3" style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; text-align:left;"><?php esc_html_e( 'Total to be paid:', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
		<td class="td" scope="col" style="text-align:left;"><?php echo wp_kses_post( wc_price( $total_to_pay, array( 'currency' => $parent_order->get_currency() ) ) ); ?></td>
	</tr>
	</tfoot>
</table>
