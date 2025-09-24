<?php
/**
 * New deposit created email
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $email_heading string
 * @var $email         WC_Email
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

{content_html}

<?php do_action( 'woocommerce_email_footer', $email ); ?>
