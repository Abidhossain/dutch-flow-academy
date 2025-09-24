<?php
/**
 * Vendor new account
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string      $email_heading The email heading.
 * @var string      $blogname      The blogname.
 * @var string      $user_display_name The user display name.
 * @var string      $admin_url     Admin url.
 * @var YITH_Vendor $vendor        The vendor object.
 * @var bool        $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool        $plain_text    True if is plain email, false otherwise.
 * @var WC_Email    $email         The email object.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	// translators: %s: Customer username.
	printf( esc_html__( 'Hi %s,', 'yith-woocommerce-product-vendors' ), esc_html( $user_display_name ) );
	?>
</p>
<p><?php echo esc_html( sprintf( __( 'Congratulations! You have been approved to join our marketplace!', 'yith-woocommerce-product-vendors' ), $blogname ) ); ?></p>

<?php do_action( 'yith_wcmv_email_approved_vendor', $vendor, $plain_text, $email ); ?>

<?php if ( $vendor->has_status( 'enabled' ) ) : ?>
	<p><?php echo esc_html__( 'Now you can customize your store, upload your products, and start selling.', 'yith-woocommerce-product-vendors' ); ?></p>
	<p><?php echo esc_html__( 'Click on the button below to access your dashboard:', 'yith-woocommerce-product-vendors' ); ?></p>
	<a href="<?php echo esc_url( $admin_url ); ?>" class="yith-vendor-button-cta">
		<?php echo esc_html( __( 'Access to your vendor dashboard', 'yith-woocommerce-product-vendors' ) ); ?>
	</a>
<?php endif; ?>

<p><?php echo esc_html__( 'Thank you for joining us, we look forward to working with you!', 'yith-woocommerce-product-vendors' ); ?></p>
<p><?php echo esc_html__( 'Kind regards', 'yith-woocommerce-product-vendors' ); ?>,</p>
<p><?php echo esc_html( $blogname ); ?></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
