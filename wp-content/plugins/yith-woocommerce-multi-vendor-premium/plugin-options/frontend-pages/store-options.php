<?php
/**
 * Store & Product Pages 'Store Page' subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'frontend-pages-store' => array(

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Store Page', '[Admin]Option section title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_store_product_pages_store_options_title',
		),

		array(
			'id'        => 'yith_wpv_store_header_style',
			'type'      => 'yith-field',
			'yith-type' => 'select-images',
			'title'     => _x( 'Header style', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Choose the header style for the store page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'default'   => 'small-box',
			'options'   => array(
				'small-box'  => array(
					'label' => _x( 'Style 1', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'image' => YITH_WPV_ASSETS_URL . 'images/style1.svg',
				),
				'double-box' => array(
					'label' => _x( 'Style 2', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'image' => YITH_WPV_ASSETS_URL . 'images/style2.svg',
				),
				'avatar-box' => array(
					'label' => _x( 'Style 3', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'image' => YITH_WPV_ASSETS_URL . 'images/style3.svg',
				),
			),
		),

		array(
			'title'     => _x( 'Header size (px)', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'image-dimensions',
			'desc'      => _x( 'Set the size of the header image on the store page. Set it at zero to use the original image size (default: 0 px).', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_header_image_size',
			'default'   => array(
				'width'  => 1400,
				'height' => 460,
			),
		),

		array(
			'title'        => _x( 'Header colors', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'id'           => 'yith_wpv_header_color',
			'desc'         => _x( 'Set the colors for the header text and background.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'colorpickers' => array(
				array(
					'name'    => _x( 'Text', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'id'      => 'text',
					'default' => '#000000',
				),
				array(
					'name'    => _x( 'Background', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
					'id'      => 'background',
					'default' => 'rgba(255, 255, 255, 0.8)',
				),
			),
		),

		array(
			'title'     => _x( 'Show default header image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Choose where to show the default header image.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_header_use_default_image',
			'options'   => array(
				'all'      => _x( 'In all of the stores, vendors can\'t upload a custom header image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'no-image' => _x( 'Only in stores that do not have a header image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'none'     => _x( 'Never use the default header image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'no-image',
		),

		array(
			'title'     => _x( 'Upload default header image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'media',
			'store_as'  => 'id',
			'desc'      => _x( 'Upload the default header image.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_header_default_image',
			'default'   => get_option( 'yith_wpv_header_default_image_default', 0 ),
			'deps'      => array(
				'id'    => 'yith_wpv_header_use_default_image',
				'value' => 'all,no-image',
			),
		),

		array(
			'title'     => _x( 'Show default logo', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Choose where to show the default logo image.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_avatar_use_default_image',
			'options'   => array(
				'all'      => _x( 'In all of the stores, vendors can\'t upload a custom logo', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'no-image' => _x( 'Only in stores that do not have a logo image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'none'     => _x( 'Never use the default logo image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'no-image',
		),

		array(
			'title'         => _x( 'Upload default logo image', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'          => 'yith-field',
			'yith-type'     => 'media',
			'store_as'      => 'id',
			'desc'          => _x( 'Upload the default logo image.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'            => 'yith_wpv_avatar_default_image',
			'default'       => get_option( 'yith_wpv_avatar_default_image_default', 0 ),
			'preview_width' => 100,
			'deps'          => array(
				'id'    => 'yith_wpv_avatar_use_default_image',
				'value' => 'all,no-image',
			),
		),

		array(
			'title'     => _x( 'Logo size (px)', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'desc'      => _x( 'Set the vendors\' logo size on the store page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_vendors_gravatar_image_size',
			'default'   => 128,
			'min'       => 0,
			'step'      => 1,
		),

		array(
			'title'     => _x( 'Vendor info to show', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'checkbox-array',
			'desc'      => _x( 'Choose the vendor\'s info that will be shown on the store page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendor_info_to_show',
			'options'   => array(
				'description' => _x( 'Description', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'vat-ssn'     => _x( 'VAT/SSN', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'location'    => _x( 'Location', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'telephone'   => _x( 'Phone', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'store_email' => _x( 'Store email', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'sales'       => _x( 'Total sales', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'website'     => _x( 'Website', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'rating'      => _x( 'Reviews average rating', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'socials'     => _x( 'Social links', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'legal_notes' => _x( 'Store legal notes', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => array(
				'name',
				'location',
				'vat-ssn',
				'store_email',
				'rating',
				'socials',
				'telephone',
			),
		),

		array(
			'title'     => _x( 'VAT/SSN label', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Change the default VAT/SSN label according to your local standards.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vat_label',
		),

		array(
			'title'     => _x( 'Products list title', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'Our products', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Set a title to show before the products list on the store page.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_store_products_list_title',
		),

		array(
			'type' => 'sectionend',
		),

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Store Widgets', '[Admin]Option section title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_store_product_pages_store_options_title',
		),

		array(
			'title'     => _x( 'Google Map API key', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			// translators: %s is the link to google gmap API guide.
			'desc'      => sprintf( _x( 'Enter your API key. <a href="%s" target="_blank">Click here to learn where to find this info ></a>.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ), esc_url( '//developers.google.com/maps/documentation/javascript/get-api-key' ) ),
			'id'        => 'yith_wpv_frontpage_gmaps_key',
			'default'   => '',
		),

		array(
			'title'     => _x( 'Show Google Maps link', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Enable this option to add the "Show on Google Maps" link below the YITH Vendor Store Location widget.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'yith_wpv_frontpage_show_gmaps_link',
			'default'   => 'yes',
		),

		array(
			'type' => 'sectionend',
		),
	),
);
