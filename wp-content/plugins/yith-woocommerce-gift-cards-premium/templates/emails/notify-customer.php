<?php
/**
 * Email for customer notification of gift card recevied
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p class="center-email">
	<?php echo wp_kses_post( nl2br( apply_filters( 'ywgc_gift_card_email_before_preview', $introductory_text, $gift_card ) ) ); ?>
</p>

<?php do_action( 'woocommerce_email_footer' ); ?>
