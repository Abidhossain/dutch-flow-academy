<?php
/**
 * YITH Vendors Commissions Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return apply_filters(
	'yith_wcmv_commissions_settings',
	array(
		'commissions' => array(
			'commissions-options' => array(
				'type'     => 'multi_tab',
				'sub-tabs' => array(
					'commissions-list'     => array(
						'title'       => _x( 'Commissions', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'An overview of sales and commissions generated in your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
					'commissions-gateways' => array(
						'title'       => _x( 'Gateways', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'Manage the available gateways to pay vendors\' commissions.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
					'commissions-settings' => array(
						'title'       => _x( 'Commissions settings', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'General options for commissions given to your vendors.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
				),
			),
		),
	)
);
