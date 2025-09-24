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

do_action( 'woocommerce_email_header', $email_heading, $email );

?>
<p class="center-email">
	<?php
	/**
	 * APPLY_FILTERS: ywgc_gift_card_email_before_preview
	 *
	 * Filter the introductory text before the gift card preview.
	 *
	 * @param string true to display it, false to not. Default: true
	 * @param object $gift_card the gift card object
	 *
	 * @return string
	 */
	echo wp_kses_post( nl2br( apply_filters( 'ywgc_gift_card_email_before_preview', $introductory_text, $gift_card ) ) );
	?>
</p>
<div class="ywgc-delivered-gift-card-image-container">
	<img class="ywgc-delivered-gift-card-image" src="<?php echo esc_url( YITH_YWGC_ASSETS_IMAGES_URL . 'delivered-gift-card.png' ); ?>" alt="">
</div>

<?php do_action( 'woocommerce_email_footer' ); ?>
