<?php
/**
 * Gift Card product add to cart
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$is_gift_this_product = ! ( $product instanceof WC_Product_Gift_Card );

?>
<h3 class="ywgc_delivery_info_title"><?php echo wp_kses_post( get_option( 'ywgc_delivery_info_title', __( 'Delivery info', 'yith-woocommerce-gift-cards' ) ) ); ?></h3>

<div class="gift-card-content-editor step-content clearfix">
	<?php if ( $allow_send_later ) : ?>
		<div class="ywgc-postdated">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_delivery_date_label
			 *
			 * Filter the "Delivery date:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			/**
			 * APPLY_FILTERS: ywgc_choose_delivery_date_placeholder
			 *
			 * Filter the "Delivery date" field placeholder in the gift card form in the product page.
			 *
			 * @param string the placeholder
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-delivery-date"><?php echo esc_html( apply_filters( 'ywgc_delivery_date_label', __( 'Delivery date: ', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<input type="text" id="ywgc-delivery-date" name="ywgc-delivery-date" placeholder="<?php echo esc_attr( apply_filters( 'ywgc_choose_delivery_date_placeholder', __( 'Now', 'yith-woocommerce-gift-cards' ) ) ); ?>" class="datepicker">
		</div>
	<?php endif; ?>

	<h5 class="ywgc_recipient_info_title">
		<?php echo wp_kses_post( get_option( 'ywgc_recipient_info_title', __( 'RECIPIENT INFO', 'yith-woocommerce-gift-cards' ) ) ); ?>
	</h5>
	<div class="ywgc-single-recipient">
		<div class="ywgc-recipient-name clearfix">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_recipient_name_label
			 *
			 * Filter the recipient "Name:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-recipient-name"><?php echo esc_html( apply_filters( 'ywgc_recipient_name_label', __( 'Name: ', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<input type="text" id="ywgc-recipient-name" name="ywgc-recipient-name[]" <?php echo ( $mandatory_recipient && ! $is_gift_this_product ) ? 'required' : ''; ?> class="yith_wc_gift_card_input_recipient_details">
		</div>
		<div class="ywgc-recipient-email clearfix">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_recipient_email_label
			 *
			 * Filter the "Email:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-recipient-email"><?php echo esc_html( apply_filters( 'ywgc_recipient_email_label', __( 'Email: ', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<input type="email" id="ywgc-recipient-email" name="ywgc-recipient-email[]" <?php echo ( $mandatory_recipient && ! $is_gift_this_product ) ? 'required' : ''; ?> class="ywgc-recipient yith_wc_gift_card_input_recipient_details"/>
		</div>
	</div>

	<?php if ( ! $mandatory_recipient ) : ?>
		<?php
		/**
		 * APPLY_FILTERS: ywgc_empty_recipient_note
		 *
		 * Filter the "If empty, will be sent to your email address" text in the gift card form in the product page.
		 *
		 * @param string the text
		 *
		 * @return string
		 */
		?>
		<span class="ywgc-empty-recipient-note"><?php echo esc_html( apply_filters( 'ywgc_empty_recipient_note', __( 'If empty, will be sent to your email address', 'yith-woocommerce-gift-cards' ) ) ); ?></span>
	<?php endif; ?>

	<?php if ( $allow_multiple_recipients && ! apply_filters( 'yith_ywgc_enable_ajax_add_to_cart_compatibility', false ) ) : ?>
		<a href="#" class="add-recipient" id="add_recipient"><?php echo esc_html__( '+ add another recipient', 'yith-woocommerce-gift-cards' ); ?></a>
	<?php endif; ?>

	<?php if ( 'yes' === get_option( 'ywgc_ask_sender_name', 'yes' ) ) : ?>
		<h5 class="ywgc-sender-info-title">
			<?php echo wp_kses_post( get_option( 'ywgc_sender_info_title', __( 'YOUR INFO', 'yith-woocommerce-gift-cards' ) ) ); ?>
		</h5>
		<div class="ywgc-sender-name clearfix">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_sender_name_label
			 *
			 * Filter the sender "Name:" label in the gift card form in the product page.
			 *
			 * @param string the label text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-sender-name"><?php echo esc_html( apply_filters( 'ywgc_sender_name_label', __( 'Name: ', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<input type="text" name="ywgc-sender-name" id="ywgc-sender-name" value="<?php echo esc_attr( apply_filters( 'ywgc_sender_name_value', '' ) ); ?>">
		</div>
	<?php endif; ?>

	<div class="ywgc-message clearfix">
		<?php
		/**
		 * APPLY_FILTERS: ywgc_edit_message_label
		 *
		 * Filter the "Message:" label in the gift card form in the product page.
		 *
		 * @param string the label text
		 *
		 * @return string
		 */
		?>
		<label for="ywgc-edit-message"><?php echo esc_html( apply_filters( 'ywgc_edit_message_label', __( 'Message: ', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
		<textarea id="ywgc-edit-message" name="ywgc-edit-message" rows="5"></textarea>
	</div>

	<?php
	$notify_delivered_email_settings = get_option( 'woocommerce_ywgc-email-delivered-gift-card_settings' );

	if ( isset( $notify_delivered_email_settings['enabled'] ) && 'yes' === $notify_delivered_email_settings['enabled'] && isset( $notify_delivered_email_settings['ywgc_delivery_notify_customer_checkbox'] ) && 'yes' === $notify_delivered_email_settings['ywgc_delivery_notify_customer_checkbox'] ) :
		?>
		<div class="ywgc-delivery-notification-checkbox-container">
			<input type="checkbox" id="ywgc-delivery-notification-checkbox" name="ywgc-delivery-notification-checkbox">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_edit_delivery_notification_label
			 *
			 * Filter the delivery notification option text.
			 *
			 * @param string the option text
			 *
			 * @return string
			 */
			?>
			<label for="ywgc-delivery-notification-checkbox"><?php echo esc_html( apply_filters( 'ywgc_edit_delivery_notification_label', __( 'Check to receive an email when the gift card has been sent to the recipient', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
		</div>
	<?php endif; ?>

	<?php do_action( 'ywgc_gift_card_details_end' ); ?>
</div>
