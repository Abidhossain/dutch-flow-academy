<?php
/**
 * New vendor registration email template
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

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'New vendor registered', 'yith-woocommerce-product-vendors' ); ?></p>

<p><?php esc_html_e( 'A new user has applied to become a vendor in your store.', 'yith-woocommerce-product-vendors' ); ?></p>

<?php do_action( 'woocommerce_email_before_commission_table', $vendor, $sent_to_admin, $plain_text ); ?>

<h2>
	<a href="<?php echo esc_url( $vendor->get_url( 'admin' ) ); ?>">
		<?php esc_html_e( 'Vendor detail', 'yith-woocommerce-product-vendors' ); ?>
	</a>
</h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<?php
		yith_wcmv_get_template(
			'emails/new-vendor-detail-table',
			array(
				'vendor' => $vendor,
				'owner'  => $vendor->get_owner( 'object' ),
			)
		);
		?>
	</tbody>
</table>

<?php do_action( 'woocommerce_email_after_commission_table', $vendor, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
