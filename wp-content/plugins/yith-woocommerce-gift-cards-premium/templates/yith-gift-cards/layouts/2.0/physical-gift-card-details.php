<?php
/**
 * Gift Card product add to cart
 *
 * @author YITH
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<input type="hidden" name="ywgc-is-physical" value="1" />
<div class="gift-card-content-editor step-content" style="margin-top: 20px;">
	<?php if ( 'yes' === get_option( 'ywgc_ask_sender_name_physical', 'no' ) ) : ?>
		<div class="ywgc-recipient-sender-container">
			<div class="ywgc-recipient-name">
				<label for="ywgc-recipient-name"><?php echo esc_html( apply_filters( 'ywgc_recipient_name_label', _x( 'Name', 'Recipient name input title in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
				<input type="text" name="ywgc-recipient-name" id="ywgc-recipient-name" value="<?php echo esc_attr( apply_filters( 'ywgc_recipient_name_value', '' ) ); ?>">
			</div>
			<div class="ywgc-sender-name">
				<label for="ywgc-sender-name"><?php echo esc_html( apply_filters( 'ywgc_sender_name_label', _x( 'Your name', 'Sender name input title in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
				<input type="text" name="ywgc-sender-name" id="ywgc-sender-name" value="<?php echo esc_attr( apply_filters( 'ywgc_sender_name_value', '' ) ); ?>">
			</div>
		</div>
	<?php endif; ?>
	<?php if ( 'yes' === get_option( 'ywgc_allow_printed_message', 'no' ) ) : ?>
		<div class="ywgc-message">
			<label for="ywgc-edit-message"><?php echo esc_html( apply_filters( 'ywgc_edit_message_label', __( 'Message', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<textarea id="ywgc-edit-message" name="ywgc-edit-message" rows="5"></textarea>
		</div>
	<?php endif; ?>
</div>
