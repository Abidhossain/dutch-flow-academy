<?php
/**
 * HTML View for the order content type.
 *
 * @package LifterLMS_PDFS/Views
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

// Translators: %d = Order ID.
$view_title = sprintf( __( 'Order #%d', 'lifterlms-pdfs' ), $order->get( 'id' ) );
?>

<h2><?php echo $view_title; ?></h2>

<h5><?php _e( 'Customer Information', 'lifterlms-pdfs' ); ?></h5>

<table class="info-table">
	<tbody>
		<tr>
			<th><?php _e( 'Name', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $order->get_customer_name(); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Email', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $order->get( 'billing_email' ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Address', 'lifterlms-pdfs' ); ?></th>
			<td>
				<?php echo $order->get( 'billing_address_1' ); ?><br>
				<?php if ( isset( $order->billing_address_2 ) ) : ?>
					<?php echo $order->get( 'billing_address_2' ); ?><br>
				<?php endif; ?>
				<?php echo $order->get( 'billing_city' ); ?>,
				<?php echo $order->get( 'billing_state' ); ?>,
				<?php echo $order->get( 'billing_zip' ); ?><br>
				<?php echo llms_get_country_name( $order->get( 'billing_country' ) ); ?>
			</td>
		</tr>
		<?php if ( $order->get( 'billing_phone' ) ) : ?>
			<tr>
				<th><?php _e( 'Phone', 'lifterlms-pdfs' ); ?></th>
				<td><?php echo $order->get( 'billing_phone' ); ?></td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>

<br>

<h5><?php _e( 'Order Details', 'lifterlms-pdfs' ); ?></h5>
<?php llms_template_view_order_information( $order ); ?>

<br>

<h5><?php _e( 'Transactions', 'lifterlms-pdfs' ); ?></h5>
<?php llms_template_view_order_transactions( $order, null, -1, 1 ); ?>
