<?php
/**
 * YITH Vendors Store & Product Pages Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return apply_filters(
	'yith_wcmv_frontend_pages_settings',
	array(
		'frontend-pages' => array(
			'frontend-pages-options' => array(
				'type'       => 'multi_tab',
				'nav-layout' => 'horizontal',
				'sub-tabs'   => array(
					'frontend-pages-general' => array(
						'title'       => _x( 'General', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'General options for vendor stores on your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
					'frontend-pages-product' => array(
						'title'       => _x( 'Product page', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( "Set up and customize your vendors' product pages", '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
					'frontend-pages-store'   => array(
						'title'       => _x( 'Store page', '[Admin]Sub-tab title.', 'yith-woocommerce-product-vendors' ),
						'description' => _x( "Set up and customize your vendors' store pages", '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
					),
				),
			),
		),
	)
);
