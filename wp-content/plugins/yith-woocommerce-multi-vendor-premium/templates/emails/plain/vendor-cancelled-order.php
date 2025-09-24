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

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";
// translators: #%1$d is the order number, %2$s is the customer name.
echo esc_html( sprintf( __( 'The order #%1$d placed by %2$s has been cancelled. The order was as follows:', 'yith-woocommerce-product-vendors' ), $order_number, $billing_first_name . ' ' . $billing_last_name ) ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

// translators: %s is the order number.
echo esc_html( strtoupper( sprintf( __( 'Order number: %s', 'yith-woocommerce-product-vendors' ), $order_number ) ) ) . "\n";
echo esc_html( date_i18n( 'jS F Y', $order_date ) ) . "\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text );

do_action( 'yith_wcmv_email_order_items_table', $vendor, $order, false, true, false, false, array(), true );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
