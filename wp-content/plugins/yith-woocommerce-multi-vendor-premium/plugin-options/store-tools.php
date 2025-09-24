<?php
/**
 * A list of store tools
 *
 * @since   5.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

return apply_filters(
	'yith_wcmv_store_tools',
	array(

		'subscription'     => array(
			'name'        => 'Subscription',
			'full_name'   => 'YITH WooCommerce Subscription',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/subscription.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-subscription/',
			'description' => __( 'Sell subscription-based products or services (weekly, monthly, yearly, etc.) and create an effective passive revenue system in your store.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_YWSBS_VERSION' ),
		),

		'membership'       => array(
			'name'        => 'Membership',
			'full_name'   => 'YITH WooCommerce Membership',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/membership.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-membership/',
			'description' => __( 'Create membership plans to allow access to products, pages, articles, and restricted areas of your website to users registered as members.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_WCMBS_PREMIUM' ),
		),

		'request_a_quote'  => array(
			'name'        => 'Request a Quote',
			'full_name'   => 'YITH Request a Quote for WooCommerce',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/request-a-quote.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
			'description' => __( 'The #1 plugin for hiding prices, disabling shopping carts, and encouraging customers to request a quote for products in your store.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_YWRAQ_VERSION' ),
			'option_id'   => 'yith_wpv_vendors_enable_request_quote',
		),

		'catalog_mode'     => array(
			'name'        => 'Catalog Mode',
			'full_name'   => 'YITH WooCommerce Catalog Mode',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/catalog-mode.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-catalog-mode/',
			'description' => __( 'Hide prices and add-to-cart buttons and create advanced calls-to-action and custom messages to encourage customer interaction.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YWCTM_PREMIUM' ),
			'option_id'   => 'yith_wpv_vendors_enable_catalog_mode',
		),

		'booking'          => array(
			'name'        => 'Booking and Appointment',
			'full_name'   => 'YITH Booking and Appointment for WooCommerce',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/booking.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-booking/',
			'description' => __( 'Enable a booking/appointment system to manage renting or booking of services, rooms, houses, cars, accommodation facilities and so on.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_WCBK_VERSION' ),
		),

		'badge_management' => array(
			'name'        => 'Badge Management',
			'full_name'   => 'YITH WooCommerce Badge Management',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/badge-management.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-badge-management/',
			'description' => __( 'Apply graphic badges to highlight discounts, promotions, and key features of your products.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_WCBM_VERSION' ),
		),

		'pdf_invoice'      => array(
			'name'        => 'PDF Invoices & Packing Slips',
			'full_name'   => 'YITH WooCommerce PDF Invoices & Packing Slips',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/pdf-invoice.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/',
			'description' => __( 'The complete solution to automatically generate and manage your invoices and create packing slips to speed up your shipping process.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_YWPI_VERSION' ),
			'option_id'   => 'yith_wpv_vendors_enable_pdf_invoice',
		),

		'product_add_ons'  => array(
			'name'        => 'Product Add-Ons & Extra Options',
			'full_name'   => 'YITH WooCommerce Product Add-Ons & Extra Options',
			'icon_url'    => YITH_WPV_ASSETS_URL . '/images/plugins/product-add-ons.svg',
			'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
			'description' => __( 'Add paid or free advanced options to your product pages using fields like radio buttons, checkboxes, drop-downs, custom text inputs, and more.', 'yith-woocommerce-product-vendors' ),
			'is_active'   => defined( 'YITH_WAPO_PREMIUM' ),
		),
	)
);
