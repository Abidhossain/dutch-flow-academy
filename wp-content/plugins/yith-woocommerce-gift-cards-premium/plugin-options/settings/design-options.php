<?php
/**
 * Design options
 *
 * @package YITH\GiftCards\PluginOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$design_options = array(
	'settings-design' => array(
		/**
		 * Shop logo options
		 */
		array(
			'name' => __( 'Shop logo options', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_shop_logo_on_gift_card'          => array(
			'name'      => __( 'Add your shop logo on gift cards', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_shop_logo_on_gift_card',
			'desc'      => __( 'Set if you want the shop logo to show up on the gift card template sent to the customers.', 'yith-woocommerce-gift-cards' ) . '<br>' . __( 'We suggest you keep it disabled if your gift card template image contains your shop logo.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_shop_logo_url'                   => array(
			'name'             => __( 'Upload your shop logo', 'yith-woocommerce-gift-cards' ),
			'type'             => 'yith-field',
			'yith-type'        => 'media',
			'id'               => 'ywgc_shop_logo_url',
			'allow_custom_url' => false,
			'desc'             => __( 'Upload the logo you want to show in the gift card sent to customers.', 'yith-woocommerce-gift-cards' ),
			// banner 850x300, logo, 100x60.
			'deps'             => array(
				'id'    => 'ywgc_shop_logo_on_gift_card',
				'value' => 'yes',
			),
		),
		'ywgc_shop_logo_on_gift_card_after'    => array(
			'name'      => __( 'Add your shop logo after the gift card image', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_shop_logo_on_gift_card_after',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card',
				'value' => 'yes',
			),
		),
		'ywgc_shop_logo_after_alignment'       => array(
			'name'      => __( 'Logo alignment', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'id'        => 'ywgc_shop_logo_after_alignment',
			'options'   => array(
				'left'   => __( 'Left', 'yith-woocommerce-gift-cards' ),
				'center' => __( 'Center', 'yith-woocommerce-gift-cards' ),
				'right'  => __( 'Right', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'left',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card_after',
				'value' => 'yes',
			),
		),
		'ywgc_shop_logo_on_gift_card_before'   => array(
			'name'      => __( 'Add your shop logo before the gift card image', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_shop_logo_on_gift_card_before',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card',
				'value' => 'yes',
			),
		),
		'ywgc_shop_logo_before_alignment'      => array(
			'name'      => __( 'Logo alignment', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'id'        => 'ywgc_shop_logo_before_alignment',
			'options'   => array(
				'left'   => __( 'Left', 'yith-woocommerce-gift-cards' ),
				'center' => __( 'Center', 'yith-woocommerce-gift-cards' ),
				'right'  => __( 'Right', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'left',
			'deps'      => array(
				'id'    => 'ywgc_shop_logo_on_gift_card_before',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * Gift Card page
		 */
		array(
			'name' => _x( 'Gift card page', 'Settings section title', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_page_layout_version'             => array(
			'id'        => 'ywgc_page_layout_version',
			'title'     => _x( 'Layout version', 'Option title to select the type of layout for the gift card product page', 'yith-woocommerce-gift-cards' ),
			'desc'      => _x( 'Choose the product page layout version.', 'Option description to select the type of layout for the gift card product page', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'v1' => __( 'Version 1.0', 'yith-woocommerce-gift-cards' ),
				'v2' => __( 'Version 2.0', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'v2',
		),
		'ywgc_display_price'                   => array(
			'name'      => _x( 'Show the gift card price', 'Option title', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_display_price',
			'desc'      => _x( 'Enable to show the gift card price added by default by WooCommerce in the product page.', 'Option description', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_select_amount_title'             => array(
			'name'              => __( 'Title for “Select amount” section', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywgc_select_amount_title',
			'desc'              => __( "Enter a title for the 'Set an amount' area on your gift card page. This area will include the preset gift card amounts and the custom amount if enabled.", 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write the set an amount area title', 'yith-woocommerce-gift-cards' ) . '"',
			'default'           => __( 'Set an amount', 'yith-woocommerce-gift-cards' ),
		),
		'yith_gift_card_header_url'            => array(
			'name'             => __( 'Default gift card image', 'yith-woocommerce-gift-cards' ),
			'type'             => 'yith-field',
			'yith-type'        => 'media',
			'id'               => 'ywgc_gift_card_header_url',
			'allow_custom_url' => false,
			'desc'             => __( 'Upload a image that will be used by default for all your gift cards. You can, however, override it when you create a new gift card product and leave empty if you don\'t want to apply a default image.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_template_design'                 => array(
			'name'      => _x( 'Enable the gallery', 'Option title', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_template_design',
			'desc'      => __( 'Enable to allow users to pick the gift card image from those available in the gallery.', 'yith-woocommerce-gift-cards' ) . '<br>' . __( 'Note: To show this section you need to upload at least 2 images in the gallery. For a better user experience, we also suggest you to group images into categories (e.g. Christmas, Easter, Birthday, etc.) through this link: ', 'yith-woocommerce-gift-cards' ) . ' <a href="' . admin_url( 'edit-tags.php?taxonomy=giftcard-category&post_type=attachment' ) . '" title="' . __( 'Set your gallery categories.', 'yith-woocommerce-gift-cards' ) . '">' . __( 'Set your image categories', 'yith-woocommerce-gift-cards' ) . '</a>',
			'default'   => 'yes',
		),
		'ywgc_gallery_in_physical'             => array(
			'name'      => _x( 'Show the gallery for physical gift cards', 'Option title', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_gallery_in_physical',
			'desc'      => __( 'Enable to show the gallery in physical gift cards, so the customer can choose a design to be printed in the gift card delivered at home.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'ywgc_template_design',
				'value' => 'yes',
			),
		),
		'ywgc_choose_design_title'             => array(
			'name'              => __( 'Title for “Choose your image” section', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywgc_choose_design_title',
			'desc'              => __( " Enter a title for the 'Choose your image' area on your gift card page.", 'yith-woocommerce-gift-cards' ),
			'custom_attributes' => 'placeholder="' . __( 'write the choose image area title', 'yith-woocommerce-gift-cards' ) . '"',
			'default'           => __( 'Choose your image', 'yith-woocommerce-gift-cards' ),
			'deps'              => array(
				'id'    => 'ywgc_template_design',
				'value' => 'yes',
			),
		),
		'ywgc_template_design_number_to_show'  => array(
			'id'        => 'ywgc_template_design_number_to_show',
			'name'      => __( 'How many images to show', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Set how many gift card images to show on the gift card page. Other designs will be shown when the customer clicks on "View all" button.', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'min'       => 0,
			'step'      => 1,
			'default'   => '3',
			'deps'      => array(
				'id'    => 'ywgc_template_design',
				'value' => 'yes',
			),
		),
		'ywgc_template_design_size'            => array(
			'id'         => 'ywgc_template_design_size',
			'name'       => _x( 'Image size', 'Option title', 'yith-woocommerce-gift-cards' ),
			'desc'       => __( 'Set the size of the images displayed on the design gallery.', 'yith-woocommerce-gift-cards' ),
			'type'       => 'yith-field',
			'yith-type'  => 'dimensions',
			'units'      => array( 'px' => 'px' ),
			'dimensions' => array(
				'width'  => 'width',
				'height' => 'height',
			),
			'default'    => array(
				'dimensions' => array(
					'width'  => 150,
					'height' => 150,
				),
				'linked'     => 'no',
			),
			'deps'       => array(
				'id'    => 'ywgc_template_design',
				'value' => 'yes',
			),
		),
		'ywgc_template_design_view_all_button' => array(
			'id'                => 'ywgc_template_design_view_all_button',
			'name'              => __( 'Text for "View all" button', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'custom_attributes' => 'placeholder="' . __( 'write the view all button text', 'yith-woocommerce-gift-cards' ) . '"',
			'default'           => __( 'VIEW ALL', 'yith-woocommerce-gift-cards' ),
			'deps'              => array(
				'id'    => 'ywgc_template_design',
				'value' => 'yes',
			),
		),
		'ywgc_custom_design'                   => array(
			'name'      => __( 'Custom Image upload', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_custom_design',
			'desc'      => __( 'Enable if the customer can upload a custom image/photo for the gift card.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_custom_design_suggested_size'    => array(
			'name'      => __( 'Enter a recommended image size for custom images', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'id'        => 'ywgc_custom_design_suggested_size',
			'desc'      => __( 'Enter a recommended size for the uploaded image that fits your gift card layout.', 'yith-woocommerce-gift-cards' ),
			'default'   => '1200x1600 px',
			'deps'      => array(
				'id'    => 'ywgc_custom_design',
				'value' => 'yes',
			),
		),
		'ywgc_custom_image_max_size'           => array(
			'name'      => __( 'Set a max size for custom images', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'id'        => 'ywgc_custom_image_max_size',
			'desc'      => __( 'Set up a maximum size in MB for the custom images uploaded by customers. Leave empty for no limit', 'yith-woocommerce-gift-cards' ),
			'min'       => 0,
			'max'       => round( wp_max_upload_size() / 1024 / 1024, 4 ),
			'step'      => 1,
			'required'  => 'required',
			'default'   => '',
			'deps'      => array(
				'id'    => 'ywgc_custom_design',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * Plugin color
		 */
		array(
			'name' => __( 'Colors', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_plugin_main_color'               => array(
			'name'      => __( 'Plugin main color', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Select the plugin main color. This color will be used to highlight some elements in the gift card product page, like the selected design, the gift card amounts, etc.', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'colorpicker',
			'default'   => '#000000',
			'id'        => 'ywgc_plugin_main_color',
		),
		array(
			'type' => 'sectionend',
		),
	),
);

return apply_filters( 'yith_ywgc_design_options_array', $design_options );
