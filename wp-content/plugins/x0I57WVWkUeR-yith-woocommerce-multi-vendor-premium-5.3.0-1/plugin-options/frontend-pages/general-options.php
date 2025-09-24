<?php
/**
 * Store & Product Pages General subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'frontend-pages-general' => array(

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => __( 'General', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_store_product_pages_general_options_title',
		),

		array(
			'title'     => _x( 'Stores\' URL slug', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => _x( 'Enter the slug for the stores\' URL (e.g. "stores" will appear as follows: http://yoursite.com/stores/vendor-name).', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendor_taxonomy_rewrite',
			'default'   => 'vendor',
		),

		array(
			'title'     => _x( 'Label for "vendor"', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendor_label_text',
			'type'      => 'yith-field',
			'yith-type' => 'text-array',
			'desc'      => _x( 'Enter the text labels for "vendor" and "vendors".', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'fields'    => array(
				'singular' => _x( 'Singular', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'plural'   => _x( 'Plural', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'inline'    => 'yes',
			'default'   => array(
				'singular' => _x( 'Vendor', 'default singular vendor label', 'yith-woocommerce-product-vendors' ),
				'plural'   => _x( 'Vendors', 'default plural vendor label', 'yith-woocommerce-product-vendors' ),
			),
		),

		array(
			'title'     => _x( 'Hide products of stores in vacation mode', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable this option to hide the products that belong to the stores in vacation mode from the shop pages.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_hide_vendors_products_on_vacation',
			'default'   => 'no',
		),

		array(
			'title'     => _x( 'Show the vendor\'s name on shop pages', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable this option to show the vendor\'s name below the products on the shop pages.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendor_name_in_loop',
			'default'   => 'yes',
		),

		array(
			'title'     => _x( 'Show the vendor\'s name on taxonomy pages', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable this option to show the vendor\'s name below the products on the taxonomy pages.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendor_name_in_categories',
			'default'   => 'yes',
		),

		array(
			'title'        => _x( 'Vendor name colors', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'desc'         => _x( 'Set the color for textual content.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'           => 'yith_wpv_vendor_color_name',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Default', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'id'      => 'normal',
					'default' => '#bc360a',
				),
				array(
					'name'    => _x( 'Hover', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'id'      => 'hover',
					'default' => '#ea9629',
				),
			),
		),

		array(
			'title'     => __( 'Product listings', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable this option to hide the products that belong to vendors from the store loop page. This way, vendor products will only be visible on the individual vendor pages.', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_hide_vendor_products',
			'default'   => 'no',
		),

		array(
			'type' => 'sectionend',
		),
	),
);
