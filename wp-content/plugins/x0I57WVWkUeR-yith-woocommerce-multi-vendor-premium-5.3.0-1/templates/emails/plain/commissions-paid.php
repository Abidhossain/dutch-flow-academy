<?php
/**
 * Commission paid successfully email plain
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 2.0.0
 *
 * @var string $email_heading The email heading.
 * @var boolean $sent_to_admin True if is an admin email, false otherwise.
 * @var boolean $plain_text True if is plain text, false otherwise.
 * @var YITH_Vendors_Commission $commission Commission object.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

echo esc_html__( 'The commission has been credited successfully.', 'yith-woocommerce-product-vendors' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_before_commission_table', $commission, $sent_to_admin, $plain_text );
// translators: %s is the commission number.
echo esc_html( strtoupper( sprintf( __( 'Commission number: %s', 'yith-woocommerce-product-vendors' ), $commission->get_id() ) ) ) . "\n";
echo esc_html( date_i18n( 'jS F Y', strtotime( $commission->get_date() ) ) ) . "\n";

echo "\n";
do_action( 'yith_wcmv_email_commission_details_table', $commission, $plain_text );

do_action( 'woocommerce_email_after_commission_table', $commission, $sent_to_admin, $plain_text );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
