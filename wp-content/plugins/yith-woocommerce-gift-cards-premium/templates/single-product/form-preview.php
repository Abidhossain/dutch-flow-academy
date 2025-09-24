<?php
/**
 * Form preview on product page
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * APPLY_FILTERS: ywgc_form_preview_from_text
 *
 * Filter the "From" text in the gift card preview on the product page.
 *
 * @param string the text
 *
 * @return string
 */
/**
 * APPLY_FILTERS: ywgc_form_preview_to_text
 *
 * Filter the "To" text in the gift card preview on the product page.
 *
 * @param string the text
 *
 * @return string
 */

?>
<div class="ywgc-main-form-preview-container">
	<?php if ( $product->is_virtual() ) : ?>
		<div class="ywgc-form-preview-title-container clearfix">
			<span class="ywgc-form-preview-title"><?php echo wp_kses_post( $product->get_title() ); ?></span>
			<div class="ywgc-form-preview-amount"></div>
		</div>
		<hr style="margin-top: 20px;">
		<div class="ywgc-form-preview-from-to-container">
			<?php if ( 'yes' === get_option( 'ywgc_ask_sender_name', 'yes' ) ) : ?>
				<span class="ywgc-form-preview-from"><?php echo esc_html( apply_filters( 'ywgc_form_preview_from_text', __( 'From: ', 'yith-woocommerce-gift-cards' ) ) ); ?></span>
				<span class="ywgc-form-preview-from-content"></span>
			<?php endif; ?>
			<br>
			<span class="ywgc-form-preview-to"><?php echo esc_html( apply_filters( 'ywgc_form_preview_to_text', __( 'To: ', 'yith-woocommerce-gift-cards' ) ) ); ?></span>
			<span class="ywgc-form-preview-to-content"></span>
		</div>
		<div class="ywgc-form-preview-separator"></div>
		<div class="ywgc-form-preview-message-container">
			<p class="ywgc-form-preview-message"></p>
		</div>
	<?php endif ?>
	<?php do_action( 'ywgc_form_preview_end', $product ); ?>
</div>
