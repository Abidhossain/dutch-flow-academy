<?php
/**
 * Settings options
 *
 * @package YITH\GiftCards\PluginOptions
 */

defined( 'ABSPATH' ) || exit();

$sub_tabs = array(
	'settings-general'              => array(
		'title'              => _x( 'General', 'Tab title in plugin settings panel', 'yith-woocommerce-gift-cards' ),
		'yith-ywgc-priority' => 20,
		'description'        => _x( 'Configure the plugin general settings.', 'Tab description in plugin settings panel', 'yith-woocommerce-gift-cards' ),
	),
	'settings-design'               => array(
		'title'              => _x( 'Style', 'Tab title in plugin settings panel', 'yith-woocommerce-gift-cards' ),
		'yith-ywgc-priority' => 20,
		'description'        => _x( 'Configure the plugin style settings.', 'Tab description in plugin settings panel', 'yith-woocommerce-gift-cards' ),
	),
	'settings-recipient_delivery'   => array(
		'title'              => _x( 'Recipient & delivery', 'Tab title in plugin settings panel', 'yith-woocommerce-gift-cards' ),
		'yith-ywgc-priority' => 20,
		'description'        => _x( 'Configure the plugin recipient & delivery settings.', 'Tab description in plugin settings panel', 'yith-woocommerce-gift-cards' ),
	),
	'settings-cart_checkout'        => array(
		'title'              => _x( 'Cart & checkout', 'Tab title in plugin settings panel', 'yith-woocommerce-gift-cards' ),
		'yith-ywgc-priority' => 20,
		'description'        => _x( 'Configure the plugin cart & checkout settings.', 'Tab description in plugin settings panel', 'yith-woocommerce-gift-cards' ),
	),
	'settings-gift-card-categories' => array(
		'title'              => _x( 'Gift card categories', 'Tab title in plugin settings panel', 'yith-woocommerce-gift-cards' ),
		'yith-ywgc-priority' => 20,
		'description'        => _x( 'Configure the gift card categories settings.', 'Tab description in plugin settings panel', 'yith-woocommerce-gift-cards' ),
	),
);
$sub_tabs = apply_filters( 'yith_ywgc_panel_settings_sub_tabs', $sub_tabs );
$sub_tabs = yith_ywgc_filter_options(
	$sub_tabs,
	array(
		'sort'             => true,
		'default_priority' => 10,
	)
);

$options = array(
	'settings' => array(
		'settings-tabs' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => $sub_tabs,
		),
	),
);

return apply_filters( 'yith_ywgc_panel_settings_options', $options );
