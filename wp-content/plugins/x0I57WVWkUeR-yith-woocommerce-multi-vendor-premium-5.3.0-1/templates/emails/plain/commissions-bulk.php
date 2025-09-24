<?php
/**
 * Commission paid successfully email
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 2.0.0
 *
 * @var YITH_Vendors_Commission|YITH_Vendors_Commission[] $commissions An array of commissions, or a single commission object.
 * @var string $new_commission_status The new commission status.
 * @var YITH_Vendor|null $current_vendor Vendor object or null.
 * @var boolean $show_note True to show notice, false otherwise.
 * @var string $email_heading The email heading.
 * @var YITH_WC_Email_Vendor_Commissions_Bulk_Action $email The email class instance.
 * @var boolean $sent_to_admin True if is an admin email, false otherwise.
 * @var boolean $plain_text True if is plain text, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n"; ?>

<?php do_action( 'woocommerce_email_before_commissions_table', $commissions, $sent_to_admin, $plain_text ); ?>

<?php
if ( ! empty( $current_vendor ) ) :
	printf( '%s %s', esc_html_x( 'Commissions Report for', 'yith-woocommerce-product-vendors' ), esc_html( $current_vendor->get_name() ) );
endif;
?>

<?php echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n"; ?>

<?php echo $email->email_commission_bulk_table( $commissions, $new_commission_status, $show_note, true ); //phpcs:ignore ?>

<?php do_action( 'woocommerce_email_after_commission_table', $commissions, $sent_to_admin, $plain_text ); ?>

<?php
do_action( 'woocommerce_email_footer', $email );
