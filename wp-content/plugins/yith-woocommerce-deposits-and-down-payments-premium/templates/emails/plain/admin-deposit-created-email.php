<?php
/**
 * New deposit created email (plain)
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

echo '= ' . esc_html( $email_heading ) . " =\n\n";

// translators: 1. Formatted customer name.
echo esc_html( sprintf( __( 'You have received an order with deposits from %s. Here you find the details of the full amount payments:', 'yith-woocommerce-deposits-and-down-payments' ), $parent_order->get_formatted_billing_full_name() ) ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

// translators: 1. Order number.
echo esc_html( strtoupper( sprintf( __( 'Order number: %s', 'woocommerce' ), '{order_number}' ) ) ) . "\n";
echo '{order_date}' . "\n\n";

echo esc_html( strtoupper( __( 'Deposits:', 'yith-woocommerce-deposits-and-down-payments' ) ) ) . "\n\n";

echo '{deposit_table}';

// translators: 1. View order url.
echo "\n" . esc_html( sprintf( __( 'View order: %s', 'woocommerce' ), admin_url( 'post.php?post=' . $parent_order->get_order_number() . '&action=edit' ) ) ) . "\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
