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

global $product;

$is_gift_this_product = ! ( $product instanceof WC_Product_Gift_Card );

?>
<h3 class="ywgc_delivery_info_title"><?php echo wp_kses_post( get_option( 'ywgc_delivery_info_title', __( 'Delivery info', 'yith-woocommerce-gift-cards' ) ) ); ?></h3>
<div class="gift-card-content-editor step-content clearfix">
	<div class="ywgc-single-recipient">
		<div class="ywgc-recipient-name ywgc-label-above-input clearfix">
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
			<label for="ywgc-recipient-name"><?php echo esc_html( apply_filters( 'ywgc_recipient_name_label', _x( 'Name', 'Recipient name input title in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<input type="text" id="ywgc-recipient-name" name="ywgc-recipient-name[]" <?php echo ( $mandatory_recipient && ! $is_gift_this_product ) ? 'required' : ''; ?> class="yith_wc_gift_card_input_recipient_details">
		</div>
		<div class="ywgc-recipient-email ywgc-label-above-input clearfix">
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
			<label for="ywgc-recipient-email"><?php echo esc_html( apply_filters( 'ywgc_recipient_email_label', _x( 'Email', 'Recipient email input title in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
			<input type="email" id="ywgc-recipient-email" name="ywgc-recipient-email[]" <?php echo ( $mandatory_recipient && ! $is_gift_this_product ) ? 'required' : ''; ?> class="ywgc-recipient yith_wc_gift_card_input_recipient_details"/>
		</div>
	</div>

	<?php if ( $allow_multiple_recipients && ! apply_filters( 'yith_ywgc_enable_ajax_add_to_cart_compatibility', false ) ) : ?>
		<a href="#" class="add-recipient" id="add_recipient"><?php echo esc_html_x( '+ add another recipient', 'Link to add another recipient in the gift card product page', 'yith-woocommerce-gift-cards' ); ?></a>
	<?php endif; ?>

	<div class="ywgc-message ywgc-label-above-input clearfix">
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
		<label for="ywgc-edit-message"><?php echo esc_html( apply_filters( 'ywgc_edit_message_label', _x( 'Message', 'Message to the recipient input title in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
		<textarea id="ywgc-edit-message" name="ywgc-edit-message" rows="5"></textarea>
	</div>
	<div class="ywgc-send-and-delivery-container">
		<?php if ( $allow_send_later ) : ?>
			<div class="ywgc-postdated ywgc-label-above-input">
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
				$date_format           = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );
				$search_format         = array( 'MM', 'yy', 'mm', 'dd' );
				$replace               = array( 'M', 'Y', 'm', 'd' );
				$date_format_formatted = str_replace( $search_format, $replace, $date_format );
				$current_date          = gmdate( $date_format_formatted );

				?>
				<label for="ywgc-delivery-date"><?php echo esc_html( apply_filters( 'ywgc_delivery_date_label', _x( 'Delivery on', 'field to select a delivery date for the gift card ', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
				<input type="text" id="ywgc-delivery-date" name="ywgc-delivery-date" placeholder="<?php echo esc_attr( apply_filters( 'ywgc_choose_delivery_date_placeholder', _x( 'Now', 'Placeholder of the delivery date field in the gift card product page, which indicates that the gift card will be sent now. It is followed by the actual date.', 'yith-woocommerce-gift-cards' ) . ' - ' . $current_date ) ); ?>" class="datepicker" >
			</div>
		<?php endif; ?>

		<?php if ( 'yes' === get_option( 'ywgc_ask_sender_name', 'yes' ) ) : ?>
			<div class="ywgc-sender-name ywgc-label-above-input clearfix">
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
				<label for="ywgc-sender-name"><?php echo esc_html( apply_filters( 'ywgc_sender_name_label', _x( 'Your name', 'Sender name input title in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
				<input type="text" name="ywgc-sender-name" id="ywgc-sender-name" value="<?php echo esc_attr( apply_filters( 'ywgc_sender_name_value', '' ) ); ?>">
			</div>
		<?php endif; ?>
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
			<label for="ywgc-delivery-notification-checkbox"><?php echo esc_html( apply_filters( 'ywgc_edit_delivery_notification_label', _x( 'Check to receive an email when the gift card has been sent to the recipient', 'Checkbox in the gift card product page', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
		</div>
	<?php endif; ?>

	<?php do_action( 'ywgc_gift_card_details_end' ); ?>
</div>
