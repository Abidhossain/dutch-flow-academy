<?php
/**
 * YITH Vendors Vendors Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return apply_filters(
	'yith_wcmv_vendors_settings',
	array(
		'vendors' => array(
			'vendors-options' => array(
				'type'     => 'multi_tab',
				'sub-tabs' => array(
					'vendors-list'         => array(
						'title'       => _x( 'Vendors list', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'The complete list of vendor stores registered on your site.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
					'vendors-registration' => array(
						'title'       => _x( 'Vendors registration', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'General options for managing new vendor registration on your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
					'vendors-permissions'  => array(
						'title'       => _x( 'Vendors permissions', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'Set up vendor permissions for managing their store, products, and orders.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
				),
			),
		),
	)
);
