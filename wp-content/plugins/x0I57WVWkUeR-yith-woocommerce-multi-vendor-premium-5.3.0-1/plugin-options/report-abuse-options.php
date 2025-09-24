<?php
/**
 * Vendors Report Abuse sub-tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'report-abuse' => array(
		'report-abuse-options' => array(
			'type'       => 'multi_tab',
			'nav-layout' => 'horizontal',
			'sub-tabs'   => array(
				'report-abuse-list'     => array(
					'title'       => _x( 'Reports', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
					'description' => __( 'Monitor abuse reports submitted by users on your marketplace.', 'yith-woocommerce-product-vendors' ),
				),
				'report-abuse-settings' => array(
					'title'       => _x( 'Settings', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
					'description' => _x( 'General options for reporting abuse on your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
				),
			),
		),
	),
);
