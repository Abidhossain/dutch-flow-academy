<?php
/**
 * Vendor new account
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string   $email_heading The email heading.
 * @var string   $blogname      The blogname.
 * @var string   $user_display_name The user display name.
 * @var string   $admin_url     Admin url.
 * @var WC_Email $email         The email object.
 * @var bool     $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool     $plain_text    True if is plain email, false otherwise.
 * @var YITH_Vendor $vendor     The vendor object.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

// translators: %s: Customer username.
echo esc_html__( 'Hi %s,', 'yith-woocommerce-product-vendors' ), esc_html( $user_display_name );
echo esc_html__( 'Congratulations! You have been approved to join our marketplace!', 'yith-woocommerce-product-vendors' ) . "\n\n";

do_action( 'yith_wcmv_email_approved_vendor', $vendor, $plain_text, $email );

if ( $vendor->has_status( 'enabled' ) ) :
	echo esc_html__( 'Now you can customize your store, upload your products, and start selling.', 'yith-woocommerce-product-vendors' ) . "\n\n";
	// translators: %s is the vendor dashboard URL.
	echo esc_html( sprintf( __( 'Here\'s the link to access your dashboard: %s', 'yith-woocommerce-product-vendors' ), $admin_url ) ) . "\n\n";
endif;

echo esc_html__( 'Thank you for joining us, we look forward to working with you!', 'yith-woocommerce-product-vendors' ) . "\n\n";
echo esc_html__( 'Kind regards', 'yith-woocommerce-product-vendors' ) . "\n\n";
echo esc_html( $blogname ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
