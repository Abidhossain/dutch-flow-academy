<?php
/**
 * General options
 *
 * @package YITH\GiftCards\PluginOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$general_options = array(
	'settings-general' => array(
		/**
		 * General settings
		 */
		array(
			'name' => __( 'General settings', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_plugin_date_format_option'   => array(
			'name'      => __( 'Date format', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'date-format',
			'id'        => 'ywgc_plugin_date_format_option',
			'js'        => true,
			'desc'      => __( 'Choose the date format for the gift card expiry date, date of delivery and so on.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'MM d, yy',
		),
		'ywgc_apply_gc_code_on_gc_product' => array(
			'name'      => __( 'Prevent the use of a gift card to purchase another gift card', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_apply_gc_code_on_gc_product',
			'desc'      => __( 'If enabled, the use of gift cards codes to purchase a gift card product is not allowed. This option is only valid when using the default gift card fields to redeem a code.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_allow_shop_manager'          => array(
			'name'      => __( 'Enable Shop Managers', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_allow_shop_manager',
			'desc'      => __( 'If enabled, the user with role Shop Manager will be able to manage the plugin settings panel.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => __( 'Gift card code settings', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_code_pattern'                => array(
			'id'        => 'ywgc_code_pattern',
			'name'      => __( 'Gift card code pattern', 'yith-woocommerce-gift-cards' ),
			'desc'      => __( "Choose the pattern of new gift cards. Use '*' for a random numerical or alphabetical value and a 'D' for a random digit. For example, If you set ***-*** your cards will have a code like: 1ME-D28.", 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => '****-****-****-****',
		),
		'ywgc_generate_automatic_code'     => array(
			'name'      => __( 'Generate automatically a code when creating a gift card post', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_generate_automatic_code',
			'desc'      => __( 'Enable this option to generate a random code automatically when creating a new gift card code manually, based on the configured code pattern.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_enable_pre_printed_physical' => array(
			'name'      => __( 'Disable code generation in physical gift cards', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_enable_pre_printed_physical',
			'desc'      => __( 'Enable to create physical gift cards without any code.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_enable_pre_printed_virtual'  => array(
			'name'      => __( 'Disable code generation in virtual gift cards', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_enable_pre_printed_virtual',
			'desc'      => __( 'Enable to create virtual gift cards without any code.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * Global settings
		 */
		array(
			'name' => __( 'Global settings', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
			'desc' => __( 'These options will be applied to all your gift card products, but in the gift card product edit page you can override them if you want different values for specific gift cards.', 'yith-woocommerce-gift-cards' ),
		),
		'ywgc_permit_free_amount'          => array(
			'name'      => __( 'Allow custom amount', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_permit_free_amount',
			'desc'      => __( 'If enabled, customers can enter a custom amount when buying a gift card.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		'ywgc_minimal_amount_gift_card'    => array(
			'id'                => 'ywgc_minimal_amount_gift_card',
			'name'              => __( 'Minimum custom amount', 'yith-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			'desc'              => __( "Set a minimum value for the custom amount of your gift cards. Leave empty if you don't want to set a minimum amount.", 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => '0',
			'custom_attributes' => 'placeholder="' . __( 'write a minimal amount', 'yith-woocommerce-gift-cards' ) . '"',
			'min'               => 0,
			'deps'              => array(
				'id'    => 'ywgc_permit_free_amount',
				'value' => 'yes',
			),
		),
		'ywgc_maximum_amount_gift_card'    => array(
			'id'                => 'ywgc_maximum_amount_gift_card',
			'name'              => __( 'Maximum custom amount', 'yith-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			'desc'              => __( "Set a maximum value for the custom amount of your gift cards. Leave empty if you don't want to set a maximum amount.", 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => '0',
			'custom_attributes' => 'placeholder="' . __( 'write a maximal amount', 'yith-woocommerce-gift-cards' ) . '"',
			'min'               => 0,
			'deps'              => array(
				'id'    => 'ywgc_permit_free_amount',
				'value' => 'yes',
			),
		),
		'ywgc_usage_expiration'            => array(
			'id'                => 'ywgc_usage_expiration',
			'name'              => __( 'Gift card expiration date', 'yith-woocommerce-gift-cards' ),
			'desc'              => '<span class="ywgc_usage_expiration_months_span">' . __( 'months', 'yith-woocommerce-gift-cards' ) . '</span>' . __( 'Set a default expiration for gift cards in months. If the value is zero, your gift cards will never expire.', 'yith-woocommerce-gift-cards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 0,
			'custom_attributes' => 'placeholder="' . __( 'write expiration date in months', 'yith-woocommerce-gift-cards' ) . '"',
			'min'               => 0,
		),
		array(
			'type' => 'sectionend',
		),
		/**
		 * Gift card orders settings
		 */
		array(
			'name' => __( 'Gift card orders settings', 'yith-woocommerce-gift-cards' ),
			'type' => 'title',
		),
		'ywgc_order_cancelled_action'      => array(
			'name'      => __( 'When an order containing a gift card is cancelled', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'class'     => 'wc-enhanced-select',
			'id'        => 'ywgc_order_cancelled_action',
			'options'   => array(
				'nothing' => __( 'Do nothing', 'yith-woocommerce-gift-cards' ),
				'disable' => __( 'Disable the gift cards', 'yith-woocommerce-gift-cards' ),
				'dismiss' => __( 'Dismiss the gift cards', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'nothing',
		),
		'ywgc_order_refunded_action'       => array(
			'name'      => __( 'When an order containing a gift card is refunded', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'class'     => 'wc-enhanced-select',
			'id'        => 'ywgc_order_refunded_action',
			'options'   => array(
				'nothing' => __( 'Do nothing', 'yith-woocommerce-gift-cards' ),
				'disable' => __( 'Disable the gift cards', 'yith-woocommerce-gift-cards' ),
				'dismiss' => __( 'Dismiss the gift cards', 'yith-woocommerce-gift-cards' ),
			),
			'default'   => 'nothing',
		),
		'ywgc_autocomplete_orders'         => array(
			'name'      => __( 'Autocomplete gift card orders', 'yith-woocommerce-gift-cards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywgc_autocomplete_orders',
			'desc'      => __( 'Enable this option if you want to complete automatically the order which only contain gift card products.', 'yith-woocommerce-gift-cards' ),
			'default'   => 'no',
		),
		array(
			'type' => 'sectionend',
		),
	),
);

return apply_filters( 'yith_ywgc_general_options_array', $general_options );
