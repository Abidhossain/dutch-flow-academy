<?php
/**
 * New vendor staff member email template.
 *
 * @author  YITH
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

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer username */ ?>
	<p><?php printf( esc_html__( 'Hi %s,', 'yith-woocommerce-product-vendors' ), esc_html( $user_display_name ) ); ?></p>
<?php /* translators: %1$s: Vendor name, %2$s: Site title */ ?>
	<p><?php printf( wp_kses_post( __( 'Vendor <b>%1$s</b> on <b>%2$s</b> has added your email as staff member!', 'yith-woocommerce-product-vendors' ) ), esc_html( $vendor_name ), esc_html( $blogname ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<?php if ( $set_password_url ) : ?>
	<?php /* translators: %1$s: Username */ ?>
	<p><?php printf( wp_kses_post( 'Your username is <b>%1$s</b>. To complete the registration of your account and start manage the store you must set a password clicking on the link below', 'yith-woocommerce-product-vendors' ), esc_html( $user_login ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<p>
		<a href="<?php echo esc_attr( $set_password_url ); ?>"><?php printf( esc_html__( 'Click here to set your new password', 'yith-woocommerce-product-vendors' ) ); ?></a>
	</p>
<?php else : ?>
	<?php /* translators: %1$s: My account link */ ?>
	<p><?php printf( esc_html__( 'For more information and to start managing the store, go to your account: %1$s.', 'yith-woocommerce-product-vendors' ), make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<?php endif; ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
