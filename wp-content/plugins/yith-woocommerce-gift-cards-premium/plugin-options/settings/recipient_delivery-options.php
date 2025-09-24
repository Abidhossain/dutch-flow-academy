<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Recipient & delivery options
 *
 * @package YITH\GiftCards\PluginOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$recipient_delivery_options = array(
	'settings-recipient_delivery' => array(
		/**
		 * Recipient & Delivery settings for virtual Gift Cards
		 */
		array(
			'name' => __( 'Recipient & Delivery settings for virtual gift cards', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_delivery_info_title'                => array(
			'name'              => __( 'Title for “Delivery info” section', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywgc_delivery_info_title',
			'desc'              => __( 'Enter a title for the delivery info area on your gift card page. This area will include recipient and sender\'s info, date of delivery and so on.', 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write the delivery info title', 'yith-woocommerce-gift-cards' ) . '"',
			'default'           => __( 'Delivery info', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_enable_send_later'                  => array(
			'name'      => __( 'Allow the user to choose the delivery date and time', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_enable_send_later',
			'desc'      => __( 'Allow your customers to choose a delivery date and time for the virtual gift card (option not available for physical gift cards delivered at home).', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_recipient_info_title'               => array(
			'name'              => __( 'Title for the “Recipient info” section', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywgc_recipient_info_title',
			'desc'              => __( 'Enter a title for the section with the recipient\'s info.', 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write the recipient info title', 'yith-woocommerce-gift-cards' ) . '"',
			'default'           => __( 'RECIPIENT\'S INFO', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_recipient_mandatory'                => array(
			'name'      => __( 'Make recipient\'s info mandatory', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_recipient_mandatory',
			'desc'      => __( 'If enabled, the recipient\'s name and email fields will be mandatory.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_allow_multi_recipients'             => array(
			'name'      => __( 'Enable multiple recipients for virtual gift cards', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_allow_multi_recipients',
			'desc'      => __( 'If enabled, customers can set multiple recipients: one gift card for each of them will be generated.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_ask_sender_name'                    => array(
			'name'      => __( 'Ask sender\'s name', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_ask_sender_name',
			'desc'      => __( 'If enabled, the customers who purchase the gift card can add his name to the gift card.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_sender_info_title'                  => array(
			'name'              => __( 'Title for “Sender\'s info” section', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywgc_sender_info_title',
			'desc'              => __( 'Enter a title for the section with sender\'s info.', 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write the sender info title', 'yith-woocommerce-gift-cards' ) . '"',
			'default'           => __( 'YOUR INFO', 'yith-woocommerce-gift-cards' ),
			'deps'              => array(
				'id'    => 'ywgc_ask_sender_name',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * Recipient & Delivery settings for physical Gift Cards delivered at home
		 */
		array(
			'name' => __( 'Recipient & Delivery settings for physical gift cards delivered at home', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_allow_printed_message'              => array(
			'name'      => __( 'Allow customers to add a printed message to the gift card', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_allow_printed_message',
			'desc'      => __( 'If enabled, the customers who purchase the gift card can add custom message to the physical gift card.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_ask_sender_name_physical'           => array(
			'name'      => __( 'Ask sender\'s and recipient\'s name', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_ask_sender_name_physical',
			'desc'      => __( 'If enabled, the customers who purchase the gift card can add his name to the physical gift card.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * E-mail options & customization
		 */
		array(
			'name' => __( 'Email options & template customization', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_auto_discount_button_activation'    => array(
			'name'      => __( 'Show a button in the gift card email', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_auto_discount_button_activation',
			'desc'      => __( 'If enabled, the gift card dispatch email will contain a link to redirect your user to your site in one click.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_email_button_label'                 => array(
			'id'        => 'ywgc_email_button_label',
			'name'      => __( 'Button label', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'Apply your gift card code', 'yith-woocommerce-gift-cards' ),
			'deps'      => array(
				'id'    => 'ywgc_auto_discount_button_activation',
				'value' => 'yes',
			),
		),
		'ywgc_redirected_page'                    => array(
			'id'        => 'ywgc_redirected_page',
			'name'      => __( 'Button redirect to', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Select the page where the recipient will be redirected after clicking on the discount button.', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'ajax-posts',
			'data'      => array(
				'placeholder' => __( 'Search Pages', 'yith-woocommerce-gift-cards' ),
				'post_type'   => 'page',
			),
			'deps'      => array(
				'id'    => 'ywgc_auto_discount_button_activation',
				'value' => 'yes',
			),
		),
		'ywgc_auto_discount'                      => array(
			'name'      => __( 'Auto-apply the gift card code', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_auto_discount',
			'desc'      => __( 'If enabled, the gift card code will be automatically applied when the user clicks on the button.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
			'deps'      => array(
				'id'    => 'ywgc_auto_discount_button_activation',
				'value' => 'yes',
			),
		),
		'ywgc_display_description_template'       => array(
			'name'      => __( 'Enter a custom text in the email template', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_display_description_template',
			'default'   => 'no',
			'desc'      => __( 'This text will be displayed in the gift card email with the instructions about how to redeem the gift card. Leave this field empty if you do not want to display any message.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_description_template_email_text'    => array(
			'name'              => __( 'Custom text in the email', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'textarea',
			'id'                => 'ywgc_description_template_email_text',
			'default'           => __( 'To use this gift card, you can either enter the code in the gift card field on the cart page or click on the following link to automatically get the discount.', 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write a message with the instructions to show in the gift card email', 'yith-woocommerce-gift-cards' ) . '"',
			'deps'              => array(
				'id'    => 'ywgc_display_description_template',
				'value' => 'yes',
			),
		),
		'ywgc_description_template_text_pdf'      => array(
			'name'              => __( 'Custom text in the PDF', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'textarea',
			'id'                => 'ywgc_description_template_text_pdf',
			'default'           => __( 'You can automatically apply the gift card code by simply reading the QR code with your phone.', 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write a message with the instructions to show in the gift card PDF', 'yith-woocommerce-gift-cards' ) . '"',
			'deps'              => array(
				'id'    => 'ywgc_display_description_template',
				'value' => 'yes',
			),
		),
		'ywgc_display_price'                      => array(
			'name'      => _x( 'Show the gift card price in the email', 'Option title', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_display_price',
			'default'   => 'yes',
			'desc'      => __( 'If enabled, the gift card price will show up in the gift card template.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_display_expiration_date'            => array(
			'name'      => __( 'Show the gift card\'s expiration date', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_display_expiration_date',
			'default'   => 'no',
			'desc'      => __( 'If enabled, the gift card expiration date will show up in the gift card template, if available.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_display_qr_code'                    => array(
			'name'      => __( 'Show QR code', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_display_qr_code',
			'default'   => 'no',
			'desc'      => __( 'If enabled, the gift card template will show a QR code with the gift card code, so if customers read the code with their phone, they will be redirected to the Shop page and the gift card will be automatically applied to the cart.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_attach_pdf_to_gift_card_code_email' => array(
			'name'      => __( 'Attach PDF', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_attach_pdf_to_gift_card_code_email',
			'default'   => 'no',
			'desc'      => __( 'If enabled, a PDF with the gift card will be attached to the gift card email.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_pdf_file_name'                      => array(
			'name'      => __( 'PDF file name', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'id'        => 'ywgc_pdf_file_name',
			'default'   => 'yith-gift-card-[giftcardid]-[uniqid]',
			'desc'      => __( 'Write the gift card PDF file name. You can use the placeholders [giftcardid] to include the gift card ID and [uniqid] to include a unique and random ID. It is recommended to add these placeholders to avoid duplicate file names.', 'yith-woocommerce-gift-cards' ),
			'deps'      => array(
				'id'    => 'ywgc_attach_pdf_to_gift_card_code_email',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
	),
);

if ( 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) ) {
	unset( $recipient_delivery_options['settings-recipient_delivery']['ywgc_recipient_info_title'] );
}

return apply_filters( 'yith_ywgc_recipient_delivery_options_array', $recipient_delivery_options );
