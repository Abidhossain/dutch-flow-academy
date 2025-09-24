<?php
/**
 * New vendor staff member email plain template.
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string   $email_heading      The email heading.
 * @var WC_Email $email              The email object.
 * @var string   $set_password_url   The password set url.
 * @var string   $user_display_name  The user display name.
 * @var string   $user_login         The user username.
 * @var string   $blogname           The blogname.
 * @var string   $vendor_name        The Vendor name.
 * @var string   $set_password_url   The password set url.
 * @var bool     $sent_to_admin      True if it is an admin email, false otherwise.
 * @var bool     $plain_text         True if is plain email, false otherwise.
 * @var string   $additional_content The additional email content to add before footer.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Customer username */
echo sprintf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $user_display_name ) ) . "\n\n";
/* translators: %1$s: Vendor name, %2$s: Site title */
echo sprintf( esc_html__( 'Vendor %1$s on %2$s has added your email as staff member!', 'yith-woocommerce-product-vendors' ), esc_html( $vendor_name ), esc_html( $blogname ) ) . "\n\n";
if ( $set_password_url ) :
	/* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */
	echo sprintf( esc_html__( 'Your username is %1$s. To complete your account\'s registration and start managing the store, you must set a password by clicking on the link below.', 'yith-woocommerce-product-vendors' ), esc_html( $user_login ) ) . "\n\n";
	echo esc_html( $set_password_url ) . "\n\n";
else :
	/* translators: %1$s: My account link */
	echo sprintf( esc_html__( 'For more information and to start managing the store, go to your account: %1$s.', 'yith-woocommerce-product-vendors' ), esc_html( wc_get_page_permalink( 'myaccount' ) ) ) . "\n\n";
endif;

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
