<?php
/**
 * Shipping settings subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


return array(
	'settings' => array(
		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Shipping Options', '[Admin]Option section title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'shipping_settings_title',
		),

		array(
			'title'     => _x( 'Enable shipping', '[Admin]Shipping module option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'enable_shipping',
		),

		array(
			'title'     => _x( 'Default shipping cost', '[Admin]Shipping module option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Set the default shipping cost to be applied to the entire cart.', '[Admin]Shipping module option description', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'price',
			'default'   => 0,
			'id'        => 'shipping_default_price',
			'deps'      => array(
				'id'    => 'enable_shipping',
				'value' => 'yes',
			),
		),

		array(
			'title'       => _x( 'Processing time', '[Admin]Shipping module option label', 'yith-woocommerce-product-vendors' ),
			'desc'        => _x( 'The time required before shipping the product for delivery.', '[Admin]Shipping module option description', 'yith-woocommerce-product-vendors' ),
			'class'       => 'wc-enhanced-select',
			'type'        => 'yith-field',
			'yith-type'   => 'select',
			'options'     => YITH_Vendors_Shipping::get_shipping_processing_times(),
			'default'     => '',
			'id'          => 'shipping_processing_time',
			'placeholder' => __( 'Ready to ship in...', 'yith-woocommerce-product-vendors' ),
			'deps'        => array(
				'id'    => 'enable_shipping',
				'value' => 'yes',
			),
		),

		array(
			'title'       => _x( 'Shipping from', '[Admin]Shipping module option label', 'yith-woocommerce-product-vendors' ),
			'desc'        => _x( 'The location from where the products are shipped for delivery.', '[Admin]Shipping module option description', 'yith-woocommerce-product-vendors' ),
			'class'       => 'wc-enhanced-select',
			'type'        => 'yith-field',
			'yith-type'   => 'select',
			'options'     => YITH_Vendors_Shipping::get_shipping_countries( true ),
			'default'     => '',
			'id'          => 'shipping_location_from',
			'placeholder' => __( '- Select a location -', 'yith-woocommerce-product-vendors' ),
			'deps'        => array(
				'id'    => 'enable_shipping',
				'value' => 'yes',
			),
		),

		array(
			'type' => 'sectionend',
		),
	),
);
