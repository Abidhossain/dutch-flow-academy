<?php
/**
 * Deposit expiring email
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

{content_html}

<?php do_action( 'woocommerce_email_footer', $email ); ?>
