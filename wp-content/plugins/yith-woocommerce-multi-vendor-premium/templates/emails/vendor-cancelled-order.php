<?php
/**
 * Cancelled order email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string      $email_heading The email heading.
 * @var WC_Order    $order         The order object.
 * @var string      $order_number  The order number.
 * @var YITH_Vendor $vendor        The vendor object.
 * @var WC_Email    $email         The email object.
 * @var bool        $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool        $plain_text    True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$billing_first_name = $order->get_billing_first_name();
$billing_last_name  = $order->get_billing_last_name();
$order_date         = $order->get_date_created()->getTimestamp();
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	// translators: #%1$d is the order number, %2$s is the customer name.
	echo esc_html( printf( __( 'The order #%1$d placed by %2$s has been cancelled. The order was as follows:', 'yith-woocommerce-product-vendors' ), $order_number, $billing_first_name . ' ' . $billing_last_name ) );
	?>
</p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false, $email ); ?>

<h2>
	<?php
	// translators: %s is the order number.
	echo esc_html( sprintf( __( 'Order #%s', 'yith-woocommerce-product-vendors' ), $order_number ) );
	?>
	(<time datetime="<?php echo esc_attr( date_i18n( 'c', $order_date ) ); ?>"><?php echo esc_html( date_i18n( wc_date_format(), $order_date ) ); ?></time>)
</h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
	<tr>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'Product', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'Qty', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html__( 'Price', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Commission', 'Email: commission rate column', 'yith-woocommerce-product-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html_x( 'Earnings', 'Email: commission amount column', 'yith-woocommerce-product-vendors' ); ?></th>
	</tr>
	</thead>
	<?php do_action( 'yith_wcmv_email_order_items_table', $vendor, $order, false, true ); ?>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false, $email, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false, $email ); ?>

<?php do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
