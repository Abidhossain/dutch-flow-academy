<?php
/**
 * New deposit created email
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<h2>
	<?php
	// translators: 1. Order number.
	echo esc_html( sprintf( __( 'Order #%s', 'yith-woocommerce-deposits-and-down-payments' ), '{order_number}' ) );
	?>
</h2>
<p>
	<?php
	// translators: 1. Customer full name.
	echo esc_html( sprintf( __( 'You have received an order with deposits from %s. Here you find the details of the full amount payments:', 'yith-woocommerce-deposits-and-down-payments' ), $parent_order->get_formatted_billing_full_name() ) );
	?>
</p>

{deposit_table}

<?php do_action( 'woocommerce_email_footer', $email ); ?>
