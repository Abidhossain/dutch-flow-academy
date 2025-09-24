<?php
/**
 * New vendor registration email template plain
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string $email_heading The email heading
 * @var WC_Email $email The email object.
 * @var YITH_Vendor $vendor The vendor object.
 * @var bool $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool $plain_text True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

echo esc_html__( 'New vendor registered', 'yith-woocommerce-product-vendors' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_before_new_vendor_table', $vendor, $sent_to_admin, $plain_text );

echo esc_html( strtoupper( __( 'A new user has applied to become a vendor in your store.', 'yith-woocommerce-product-vendors' ) ) ) . "\n";

echo "\n";

yith_wcmv_get_template(
	'emails/plain/new-vendor-detail-table',
	array(
		'vendor' => $vendor,
		'owner'  => $vendor->get_owner(),
	)
);

do_action( 'woocommerce_email_after_new_vendor_table', $vendor, $sent_to_admin, $plain_text );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
