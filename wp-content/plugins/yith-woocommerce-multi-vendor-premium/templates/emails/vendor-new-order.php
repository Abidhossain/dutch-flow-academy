<?php
/**
 * New Vendor Order Email template
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string      $email_heading The email heading.
 * @var WC_Order    $order         The order object.
 * @var string      $order_number  The order number.
 * @var YITH_Vendor $vendor        The vendor object.
 * @var string      $customer      The order associated customer name or email.
 * @var WC_Email    $email         The email object.
 * @var bool        $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool        $plain_text    True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


$tax_credited_to_vendor = 'vendor' === get_option( 'yith_wpv_commissions_tax_management', 'website' );
$currency               = array( 'currency' => $order->get_currency() );
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	// Translators: %s stand for the customer name.
	echo esc_html( sprintf( __( 'You have received an order from %s. The order is as follows:', 'yith-woocommerce-product-vendors' ), $customer ) );
	?>
</p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false, $email ); ?>

<h2>
	<?php
	// translators: %s is the order number.
	echo esc_html( sprintf( __( 'Order #%s', 'yith-woocommerce-product-vendors' ), $order_number ) );
	?>
	(
	<time datetime="<?php echo esc_attr( $order->get_date_created()->format( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
	)
</h2>

<style>
    #vendor-table, #vendor-table th, #vendor-table td {
        border: 2px solid #eee !important;
    }

    #vendor-table-shipping {
        margin-bottom: 15px;
    }
</style>

<table id="vendor-table" cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee; border-collapse: collapse" border="1" bordercolor="#eee">
	<thead>
	<tr>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'Product', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'SKU', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'Qty', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'Price', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Commission', 'Email: commission rate column', 'yith-woocommerce-product-vendors' ); ?></th>
		<?php if ( $tax_credited_to_vendor ) : ?>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Tax', 'Email: tax amount column', 'yith-woocommerce-product-vendors' ); ?></th>
		<?php endif; ?>
		<th scope="col" style="text-align:left; border: 1px solid #eee;">
			<?php $earnings_text = _x( 'Earnings', 'Email: commission amount column', 'yith-woocommerce-product-vendors' ); ?>
			<?php if ( $tax_credited_to_vendor ) : ?>
				<?php $earnings_text .= ' ' . _x( '(inc. taxes)', 'Email: commission amount column', 'yith-woocommerce-product-vendors' ); ?>
			<?php endif; ?>
			<?php echo esc_html( $earnings_text ); ?>
		</th>
	</tr>
	</thead>
	<?php do_action( 'yith_wcmv_email_order_items_table', $vendor, $order, false, true ); ?>
</table>

<?php
$shipping_fee_ids = yith_wcmv_get_commissions(
	array(
		'order_id' => $order->get_id(),
		'status'   => 'all',
		'type'     => 'shipping',
	)
);
?>

<?php if ( ! empty( $shipping_fee_ids ) ) : ?>
	<h3><?php echo esc_html_x( 'Shipping fee', 'Email: Title before the Shipping fee list', 'yith-woocommerce-product-vendors' ); ?></h3>
	<table id="vendor-table-shipping" cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee; border-collapse: collapse" border="1" bordercolor="#eee">
		<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Shipping method', 'Email: shipping method column', 'yith-woocommerce-product-vendors' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Rate', 'Email: commission rate column', 'yith-woocommerce-product-vendors' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Amount', 'Email: commission amount column', 'yith-woocommerce-product-vendors' ); ?></th>
		</tr>
		</thead>
		<?php
		$line_items_shipping = $order->get_items( 'shipping' );
		foreach ( $shipping_fee_ids as $shipping_fee_id ) :
			?>
			<tr>
				<?php
				$shipping_fee = yith_wcmv_get_commission( $shipping_fee_id );
				if ( ! empty( $shipping_fee ) ) :
					?>
					<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
						<?php
						$shipping_method = isset( $line_items_shipping[ $shipping_fee->get_line_item_id() ] ) ? $line_items_shipping[ $shipping_fee->get_line_item_id() ] : null;
						if ( ! empty( $shipping_method ) ) {
							echo wp_kses_post( $shipping_method->get_name() );
							echo '<br/><small>' . esc_html_x( 'Commission ID:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' <a href="' . esc_url( $shipping_fee->get_view_url() ) . '">' . esc_html( $shipping_fee->get_id() ) . '</a></small>';
						}
						?>
					</td>
				<?php endif; ?>
				<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
					<?php echo esc_html( $shipping_fee->get_rate( 'display' ) ); ?>
				</td>
				<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
					<?php echo $shipping_fee->get_amount( 'display', $currency ); //phpcs:ignore
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php
		if ( $order->get_customer_note() ) {
			?>
			<tr>
				<th class="td" scope="row" colspan="2"><?php esc_html_e( 'Note:', 'yith-woocommerce-product-vendors' ); ?></th>
				<td class="td"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
			</tr>
			<?php
		}
		?>
	</table>
<?php endif; ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false, $email ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
