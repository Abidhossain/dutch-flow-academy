<?php
/**
 * A list of available modules
 *
 * @since   5.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

return apply_filters(
	'yith_wcmv_integrations',
	array(

		'order_tracking'              => array(
			'name'        => 'YITH WooCommerce Order & Shipment Tracking',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-order-tracking/',
			'option_desc' => _x( 'Manage order tracking', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_YWOT_PREMIUM' ) && defined( 'YITH_YWOT_VERSION' ) && YITH_YWOT_PREMIUM && version_compare( YITH_YWOT_VERSION, '1.1.9', '>=' ),
		),

		'subscription'                => array(
			'name'         => 'YITH WooCommerce Subscription',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-woocommerce-subscription/',
			'option_desc'  => _x( 'Subscription products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'ywsbs_subscription',
			'capabilities' => yith_wcmv_create_capabilities( 'ywsbs_sub' ),
			'available'    => defined( 'YITH_YWSBS_PREMIUM' ) && YITH_YWSBS_PREMIUM,
		),

		'name_your_price'             => array(
			'name'        => 'YITH WooCommerce Name Your Price',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-name-your-price/',
			'option_desc' => _x( '"Name your price" products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YWCNP_PREMIUM' ) && YWCNP_PREMIUM,
		),

		'size_charts'                 => array(
			'name'         => 'YITH Product Size Charts for WooCommerce',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-product-size-charts-for-woocommerce/',
			'option_desc'  => _x( 'Add size charts for their products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'yith-wcpsc-wc-chart',
			'capabilities' => yith_wcmv_create_capabilities( array( 'size_chart', 'size_charts' ) ),
			'available'    => defined( 'YITH_WCPSC_PREMIUM' ) && defined( 'YITH_WCPSC_VERSION' ) && YITH_WCPSC_PREMIUM && version_compare( YITH_WCPSC_VERSION, '1.0.6', '>=' ),
		),

		'membership'                  => array(
			'name'         => 'YITH WooCommerce Membership',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-woocommerce-membership/',
			'option_desc'  => _x( 'Membership products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'yith-wcmbs-plan',
			'capabilities' => yith_wcmv_create_capabilities( array( 'plan', 'plans' ) ),
			'available'    => defined( 'YITH_WCMBS_PREMIUM' ) && defined( 'YITH_WCMBS_VERSION' ) && YITH_WCMBS_PREMIUM && version_compare( YITH_WCMBS_VERSION, '1.0.4', '>=' ),
		),

		'waiting_list'                => array(
			'name'        => 'YITH WooCommerce Waiting List',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-waiting-list/',
			'option_desc' => _x( 'Manage waiting lists for their products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_WCWTL_PREMIUM' ) && defined( 'YITH_WCWTL_VERSION' ) && YITH_WCWTL_PREMIUM && version_compare( YITH_WCWTL_VERSION, '1.0.6', '>=' ),
		),

		'badge_management'            => array(
			'name'         => 'YITH WooCommerce Badge Management',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-woocommerce-badge-management/',
			'option_desc'  => _x( 'Create and manage badges for their products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'yith-wcbm-badge',
			'capabilities' => yith_wcmv_create_capabilities( array( 'badge', 'badges' ) ),
			'available'    => defined( 'YITH_WCBM_PREMIUM' ) && defined( 'YITH_WCBM_VERSION' ) && YITH_WCBM_PREMIUM && version_compare( YITH_WCBM_VERSION, '1.2.3', '>=' ),
		),

		'review_discounts'            => array(
			'name'         => 'YITH WooCommerce Review For Discounts',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-woocommerce-review-for-discounts/',
			'option_desc'  => __( 'Create and manage discounts for their customers', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'ywrfd-discount',
			'capabilities' => yith_wcmv_create_capabilities( array( 'ywrfd-discount', 'ywrfd-discounts' ) ),
			'available'    => defined( 'YWRFD_PREMIUM' ) && YWRFD_PREMIUM,
		),

		'coupon_email_system'         => array(
			'name'        => 'YITH WooCommerce Coupon Email System',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-coupon-email-system/',
			'option_desc' => _x( 'Create custom coupons and email them to your customers', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YWCES_PREMIUM' ) && defined( 'YWCES_VERSION' ) && YWCES_PREMIUM && version_compare( YWCES_VERSION, '1.0.5', '>=' ),
		),

		'pdf_invoice'                 => array(
			'name'        => 'YITH WooCommerce PDF Invoices & Packing Slips',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/',
			'option_desc' => _x( 'Create invoices for their orders', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_YWPI_PREMIUM' ) && defined( 'YITH_YWPI_VERSION' ) && YITH_YWPI_PREMIUM && version_compare( YITH_YWPI_VERSION, '1.3.0', '>=' ),
			'option_name' => 'yith_wpv_vendors_enable_pdf_invoice',
		),

		'request_quote'               => array(
			'name'        => 'YITH WooCommerce Request a quote',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
			'option_desc' => _x( 'Receive and manage their own quote requests', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_YWRAQ_PREMIUM' ) && defined( 'YITH_YWRAQ_VERSION' ) && YITH_YWRAQ_PREMIUM && version_compare( YITH_YWRAQ_VERSION, '1.4.0', '>=' ),
			'option_name' => 'yith_wpv_vendors_enable_request_quote',
			'includes'    => array(
				'common' => 'class-yith-vendors-request-quote.php',
			),
		),

		'catalog_mode'                => array(
			'name'         => 'YITH WooCommerce Catalog Mode',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-woocommerce-catalog-mode/',
			'option_desc'  => _x( 'Set store in catalog mode', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'ywctm-button-label',
			'capabilities' => yith_wcmv_create_capabilities( array( 'ywctm-button-label', 'ywctm-button-labels' ) ),
			'available'    => defined( 'YWCTM_PREMIUM' ) && defined( 'YWCTM_VERSION' ) && YWCTM_PREMIUM && version_compare( YWCTM_VERSION, '1.3.0', '>=' ),
			'option_name'  => 'yith_wpv_vendors_enable_catalog_mode',
		),

		'role_based_prices'           => array(
			'name'         => 'YITH WooCommerce Role Based Prices',
			'landing_uri'  => '//yithemes.com/themes/plugins/yith-woocommerce-role-based-prices/',
			'option_desc'  => _x( 'Create custom price rules for their products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'   => 'yith_price_rule',
			'capabilities' => yith_wcmv_create_capabilities( array( 'price_rule', 'price_rules' ) ),
			'available'    => defined( 'YWCRBP_PREMIUM' ) && YWCRBP_PREMIUM,
		),

		'advanced_product_options'    => array(
			'name'        => 'YITH WooCommerce Product Add-ons',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
			'option_desc' => _x( 'Create advanced options for their products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_WAPO_PREMIUM' ) && YITH_WAPO_PREMIUM,
		),

		'sms_notifications'           => array(
			'name'        => 'YITH WooCommerce SMS Notifications',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-sms-notifications/',
			'option_desc' => _x( 'Receive SMS notifications about their orders', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YWSN_PREMIUM' ) && defined( 'YWSN_VERSION' ) && YWSN_PREMIUM && version_compare( YWSN_VERSION, '1.0.3', '>=' ),
			'option_name' => 'yith_wpv_vendors_enable_sms',
		),

		'bulk_product_editing'        => array(
			'name'        => 'YITH WooCommerce Bulk Product Editing',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-bulk-product-editing/',
			'option_desc' => _x( 'Bulk edit their products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_WCBEP_PREMIUM' ) && defined( 'YITH_WCBEP_VERSION' ) && YITH_WCBEP_PREMIUM && version_compare( YITH_WCBEP_VERSION, '1.1.23', '>=' ),
			'option_name' => 'yith_wpv_vendors_option_bulk_product_editing_options_management',
		),

		'product_bundles'             => array(
			'name'        => 'YITH WooCommerce Product Bundles',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-product-bundles/',
			'option_desc' => _x( 'Bundle products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'available'   => defined( 'YITH_WCPB_PREMIUM' ) && defined( 'YITH_WCPB_VERSION' ) && YITH_WCPB_PREMIUM && version_compare( YITH_WCPB_VERSION, '1.1.3', '>=' ),
			'compare'     => '>=',
		),

		'booking'                     => array(
			'name'        => 'YITH Booking and Appointment for WooCommerce Premium',
			'landing_uri' => '//yithemes.com/themes/plugins/yith-woocommerce-booking/',
			'option_desc' => _x( 'Bookable products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'post_types'  => 'yith_booking',
			'available'   => defined( 'YITH_WCBK_PREMIUM' ) && defined( 'YITH_WCBK_VERSION' ) && YITH_WCBK_PREMIUM && version_compare( YITH_WCBK_VERSION, '1.0.7', '>=' ),
		),

		'woocommmerce_points_rewards' => array(
			'autoload'  => true,
			'available' => class_exists( 'WC_Points_Rewards' ),
			'includes'  => array(
				'common' => 'class-yith-wc-points-and-rewards.php',
			),
		),

		'woocommmerce_cost_goods'     => array(
			'autoload'  => true,
			'available' => class_exists( 'WC_COG' ),
			'includes'  => array(
				'common' => 'class-yith-wc-cog.php',
			),
		),

		'acf'                         => array(
			'autoload'  => true,
			'available' => defined( 'ACF' ),
			'includes'  => array(
				'common' => 'class-yith-vendors-acf.php',
			),
		),

		'yoast'                       => array(
			'autoload'  => true,
			'available' => defined( 'WPSEO_VERSION' ),
			'includes'  => array(
				'common' => 'class-yith-yoast-seo.php',
			),
		),

		'cost_goods'                  => array(
			'autoload'  => true,
			'available' => class_exists( 'YITH_COG' ),
			'includes'  => array(
				'common' => 'class-yith-cost-of-goods-support.php',
			),
		),

		'autoptimize'                 => array(
			'autoload'  => true,
			'available' => class_exists( 'YITH_COG' ),
			'includes'  => array(
				'common' => 'class-yith-wp-autoptimize.php',
			),
		),
	)
);
