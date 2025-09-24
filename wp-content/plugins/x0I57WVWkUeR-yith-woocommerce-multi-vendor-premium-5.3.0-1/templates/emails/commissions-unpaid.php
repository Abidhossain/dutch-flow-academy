<?php
/**
 * Commission paid successfully email
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string $email_heading The email heading
 * @var WC_Email $email The email object.
 * @var YITH_Commission $commission The commission object.
 * @var bool $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool $plain_text True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Some commissions have not been credited properly.', 'yith-woocommerce-product-vendors' ); ?></p>

<?php do_action( 'woocommerce_email_before_commission_table', $commission, $sent_to_admin, $plain_text ); ?>

<h2><?php esc_html_e( 'Details of unpaid commissions', 'yith-woocommerce-product-vendors' ); ?></h2>

<h3>
	<a href="<?php echo esc_url( $commission->get_view_url( 'admin' ) ); ?>">
		<?php
		// translators: %s is the commission ID.
		echo esc_html( sprintf( __( 'Commission #%s', 'yith-woocommerce-product-vendors' ), $commission->get_id() ) );
		?>
	</a>
	(<?php echo sprintf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $commission->get_date() ) ), date_i18n( wc_date_format(), strtotime( $commission->get_date() ) ) ); // phpcs:ignore ?>)
</h3>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
	<?php do_action( 'yith_wcmv_email_commission_details_table', $commission ); ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_email_after_commission_table', $commission, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
