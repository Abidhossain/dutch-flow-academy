<?php
/**
 * Gift this product options
 *
 * @package YITH\GiftCards\PluginOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$gift_this_product_options = array(
	'gift-this-product' => array(
		/**
		 * Product page options
		 */
		array(
			'name' => __( 'Product page options', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_gift_this_product_include_shipping'       => array(
			'name'      => __( 'Include shipping cost in the "Gift this product" option', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_gift_this_product_include_shipping',
			'desc'      => __( 'Enable to allow users to also pay the shipping cost for the product that has been selected as a gift.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_gift_this_product_include_shipping_fixed' => array(
			'name'      => __( 'Set a custom shipping cost', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_gift_this_product_include_shipping_fixed',
			'desc'      => __( 'Enable to add a custom shipping cost to the "Gift this product". If disabled, the plugin will take the shipping costs configured in WooCommerce.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'ywgc_gift_this_product_include_shipping',
				'value' => 'yes',
			),
		),
		'ywgc_gift_this_product_fixed_shipping_value'   => array(
			'id'        => 'ywgc_gift_this_product_fixed_shipping_value',
			'name'      => __( 'Custom shipping cost', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Enter here the custom shipping cost to be applied in the "Gift this product".', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => '0',
			'deps'      => array(
				'id'    => 'ywgc_gift_this_product_include_shipping_fixed',
				'value' => 'yes',
			),
		),
		'ywgc_permit_its_a_present_shop_page'           => array(
			'name'      => __( 'Show "Gift this product" option in shop page', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_permit_its_a_present_shop_page',
			'desc'      => __( 'Enable to show the "Gift this product" link also in shop pages.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_gift_this_product_icon'                   => array(
			'name'      => __( 'Gift this product icon', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_gift_this_product_icon',
			'desc'      => __( 'Enable a gift card icon in the Gift this product title.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_gift_this_product_button_style'           => array(
			'name'      => __( 'Label style', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'id'        => 'ywgc_gift_this_product_button_style',
			'options'   => array(
				'ywgc_gift_this_product_button_style_text' => __( 'Only text', 'yith-woocommerce-gift-cards' ),
				'ywgc_gift_this_product_button_style_button' => __( 'Button', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'ywgc_gift_this_product_button_style_text',
		),
		'ywgc_gift_this_product_label_description'      => array(
			'id'        => 'ywgc_gift_this_product_label_description',
			'name'      => __( 'Description to show before button', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'textarea',
			'default'   => __( 'Do you feel this product is perfect for a friend or a loved one? You can buy a gift card for this item!', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_gift_this_product_label'                  => array(
			'id'        => 'ywgc_gift_this_product_label',
			'name'      => __( 'Enter text for the button', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'Gift this product', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_gift_this_product_colors'                 => array(
			'name'         => __( 'Label colors', 'yith-woocommerce-gift-cards' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'id'           => 'ywgc_gift_this_product_colors',
			'colorpickers' => array(
				array(
					'name'    => __( 'background color', 'yith-woocommerce-gift-cards' ),
					'id'      => 'default',
					'default' => '#ffffff',
				),
				array(
					'name'    => __( 'background hover', 'yith-woocommerce-gift-cards' ),
					'id'      => 'hover',
					'default' => '#ffffff',
				),
				array(
					'name'    => __( 'text color', 'yith-woocommerce-gift-cards' ),
					'id'      => 'default_text',
					'default' => '#448A85',
				),
				array(
					'name'    => __( 'text color hover', 'yith-woocommerce-gift-cards' ),
					'id'      => 'hover_text',
					'default' => '#1A4E43',
				),
			),
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * Gift this product email options
		 */
		array(
			'name' => __( 'Gift this product email options', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_gift_this_product_email_button_label'     => array(
			'id'        => 'ywgc_gift_this_product_email_button_label',
			'name'      => __( 'Button text', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( 'Enter a text for the button displayed in the email sent to the gift card recipient.', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'Go to the product', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_gift_this_product_email_button_redirect'  => array(
			'name'      => __( 'Button redirect the user to', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'id'        => 'ywgc_gift_this_product_button_redirect',
			'options'   => array(
				'to_product_page'   => __( 'Product page', 'yith-woocommerce-gift-cards' ),
				'to_customize_page' => __( 'Another page', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'to_product_page',
		),
		'ywgc_gift_this_product_redirected_page'        => array(
			'name'      => '',
			'desc'      => __( 'Set the page you want the user to be redirected to when the button in the gift card email is clicked on.', 'yith-woocommerce-gift-cards' ),
			'id'        => 'ywgc_gift_this_product_redirected_page',
			'type'      => 'yith-field',
			'yith-type' => 'ajax-posts',
			'data'      => array(
				'placeholder' => __( 'Search Pages', 'yith-woocommerce-gift-cards' ),
				'post_type'   => 'page',
			),
			'deps'      => array(
				'id'    => 'ywgc_gift_this_product_button_redirect',
				'value' => 'to_customize_page',
			),
		),
		'ywgc_gift_this_product_add_to_cart'            => array(
			'name'      => __( 'Automatically add the suggested product to the cart', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_gift_this_product_add_to_cart',
			'desc'      => __( 'Add the recommended product to the recipient\'s cart', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		'ywgc_gift_this_product_apply_gift_card'        => array(
			'name'      => __( 'Automatically apply the gift card code to cart', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_gift_this_product_apply_gift_card',
			'desc'      => __( 'Automatically apply the gift voucher code to the cart, without the user having to enter it manually.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'yes',
		),
		array(
			'type' => 'sectionend',
		),
	),
);

return apply_filters( 'yith_ywgc_gift_this_product_options_array', $gift_this_product_options );
