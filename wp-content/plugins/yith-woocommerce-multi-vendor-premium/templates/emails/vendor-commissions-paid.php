<?php
/**
 * Commission paid successfully email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string                  $email_heading The email heading.
 * @var YITH_Vendors_Commission $commission    The commission object.
 * @var WC_Email                $email         The email object.
 * @var bool                    $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool                    $plain_text    True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'The commission has been credited successfully.', 'yith-woocommerce-product-vendors' ); ?></p>

<?php do_action( 'woocommerce_email_before_commission_table', $commission, $sent_to_admin, $plain_text ); ?>

<h2>
	<a href="<?php echo esc_url( $commission->get_view_url( 'admin' ) ); ?>">
		<?php
		// translators: %s is the commission ID.
		echo esc_html( sprintf( __( 'Commission #%s detail', 'yith-woocommerce-product-vendors' ), $commission->get_id() ) );
		?>
	</a>
</h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
	<?php do_action( 'yith_wcmv_email_commission_details_table', $commission, $plain_text ); ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_email_after_commission_table', $commission, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
