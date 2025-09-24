<?php
/**
 * Report abuse settings array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

return array(
	'report-abuse-settings' => array(

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => __( 'Report Abuse Settings', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_modules_report_abuse_options_title',
		),

		array(
			'title'     => _x( 'Show "Report abuse" link', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Enable to show a "Report abuse" link on all of the product pages.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_report_abuse_link',
			'options'   => array(
				'none'   => _x( 'Disable option', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'all'    => _x( 'On all products', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'vendor' => _x( 'Only for vendors\' products', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'none',
		),

		array(
			'title'     => _x( '"Report abuse" link label', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => _x( 'Enter the label for the "Report abuse" link text.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_report_abuse_link_text',
			'default'   => _x( 'Report abuse', '[Single Product Page]: link label', 'yith-woocommerce-product-vendors' ),
			'deps'      => array(
				'id'    => 'yith_wpv_report_abuse_link',
				'value' => 'all,vendor',
			),
		),

		array(
			'title'        => _x( '"Report abuse" link color', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'desc'         => _x( 'Set the color for textual content.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'           => 'yith_wpv_report_abuse_link_color',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Default', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'id'      => 'normal',
					'default' => '#af2323',
				),
				array(
					'name'    => _x( 'Hover', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'id'      => 'hover',
					'default' => '#af2323',
				),
			),
			'deps'         => array(
				'id'    => 'yith_wpv_report_abuse_link',
				'value' => 'all,vendor',
			),
		),

		array(
			'type' => 'sectionend',
		),
	),
);
