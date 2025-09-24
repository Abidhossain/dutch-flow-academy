<?php
/**
 * Store & Product Pages 'Product Page' subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'frontend-pages-product' => apply_filters(
		'yith_wcmv_frontend_pages_product_options',
		array(

			array(
				'type' => 'sectionstart',
			),

			array(
				'title' => __( 'Product Page', 'yith-woocommerce-product-vendors' ),
				'type'  => 'title',
				'id'    => 'yith_wcmv_store_product_pages_product_options_title',
			),

			array(
				'title'     => _x( 'Show the vendor\'s name on the product page', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => _x( 'Enable this option to show the vendor\'s name below the product\'s name on the single product page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wpv_vendor_name_in_single',
				'default'   => 'yes',
			),
			
			array(
				'title'     => _x( 'Show the number of items sold', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => _x( 'Enable this option to show the vendor\'s total number of sales on the product detail page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wpv_vendor_show_item_sold',
				'default'   => 'no',
				'deps'      => array(
					'id'    => 'yith_wpv_vendor_name_in_single',
					'value' => 'yes',
				),
			),

			array(
				'title'             => _x( 'Items sold label', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'              => 'yith-field',
				'yith-type'         => 'text',
				'desc'              => _x( 'Enter the label for the "items sold" text. Use the <code>%itemsold%</code> placeholder to show the number of items sold.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'                => 'yith_wpv_vendor_item_sold_label',
				// translators: %itemsold% is a placeholder for the number of items sold.
				'default'           => __( '%itemsold% orders', 'yith-woocommerce-product-vendors' ),
				'custom_attributes' => array(
					'data-deps'       => 'yith_wpv_vendor_name_in_single,yith_wpv_vendor_show_item_sold',
					'data-deps_value' => 'yes,yes',
				),
			),

			array(
				'title'     => _x( 'Show the vendor\'s tab with the WooCommerce tabs', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => _x( 'Enable this option to show the vendor\'s tab with the WooCommerce tabs on the single product page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wpv_show_vendor_tab_in_single',
				'default'   => 'yes',
			),

			array(
				'title'     => _x( 'Vendor\'s tab position', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'desc'      => _x( 'Choose the vendor\'s tab position.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_vendors_tab_position',
				'options'   => array(
					1  => _x( 'Show the vendor\'s tab as the first tab', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					99 => _x( 'Show the vendor\'s tab after the WooCommerce tabs', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				),
				'default'   => 99,
				'deps'      => array(
					'id'    => 'yith_wpv_show_vendor_tab_in_single',
					'value' => 'yes',
				),
			),

			array(
				'title'     => _x( 'Vendor\'s tab label', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => _x( 'Enter the label for the vendors\' tab.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wpv_vendor_tab_text_text',
				'default'   => YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ),
				'deps'      => array(
					'id'    => 'yith_wpv_show_vendor_tab_in_single',
					'value' => 'yes',
				),
			),

			array(
				'title'     => _x( 'Related products', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'desc'      => _x( 'Choose how to manage the related products section on each single product page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_vendors_related_products',
				'options'   => array(
					'disabled' => _x( 'Don\'t show the related products section', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'vendor'   => _x( 'Show related products from the same vendor', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'default'  => _x( 'Show related products from the whole shop', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				),
				'default'   => 'vendor',
			),

			array(
				'type' => 'sectionend',
			),
		)
	),
);
