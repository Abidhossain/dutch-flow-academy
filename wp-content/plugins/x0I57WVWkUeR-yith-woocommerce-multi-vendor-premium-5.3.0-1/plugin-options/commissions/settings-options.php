<?php
/**
 * Commissions settings array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

// Merge Unpaid with Processing.
$views            = array( 'all' => __( 'All', 'yith-woocommerce-product-vendors' ) ) + yith_wcmv_get_commission_statuses();
$views['unpaid'] .= '/' . $views['processing'];
unset( $views['processing'] );

return apply_filters(
	'yith_wcmv_admin_commissions_settings',
	array(

		'commissions-settings' => array(

			array(
				'type' => 'sectionstart',
			),

			array(
				'title' => __( 'Commissions Settings', 'yith-woocommerce-product-vendors' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wpv_commissions_options_title',
			),

			array(
				'title'              => _x( 'Default commission', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
				'type'               => 'yith-field',
				'yith-type'          => 'number',
				'default'            => 50,
				'desc'               => _x( 'Set the default commission percentage.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				'id'                 => 'yith_vendor_base_commission',
				'class'              => 'percentage-input',
				'inline_description' => '%',
				'custom_attributes'  => array(
					'min'  => 0,
					'max'  => 100,
					'step' => apply_filters( 'yith_wcmv_commissions_step', 0.1 ),
				),
			),

			array(
				'title'     => _x( 'Commissions page view', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'select',
				'class'     => 'wc-enhanced-select',
				'default'   => 'all',
				'desc'      => _x( 'Select the default view for the commissions page.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_commissions_default_table_view',
				'options'   => $views,
			),

			array(
				'title'     => _x( 'If a coupon was used, calculate the vendor\'s commission', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'default'   => 'yes',
				'options'   => array(
					'no'  => _x( 'Based on the original product price', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
					'yes' => _x( 'Based on the discounted price', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
				),
				'desc'      => _x( 'Decide whether to calculate the vendors\' commissions including the coupon value or not when one is used to place the order.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wpv_include_coupon',
			),

			wc_tax_enabled() ? array(
				'title'     => _x( 'Taxes receiver', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'default'   => 'website',
				'options'   => array(
					'website' => _x( 'Admin', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
					'vendor'  => _x( 'Vendor', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
					'split'   => _x( 'Split taxes between admin and vendor', '[Admin]: Option label', 'yith-woocommerce-product-vendors' ),
				),
				'desc'      => _x( 'Choose how to manage taxes during the payment.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wpv_commissions_tax_management',
			) : array(),

			array(
				'title'     => _x( 'Pay commissions to vendors automatically when the order status changes', '[Admin] Option name', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wcmv_checkout_commissions_payment',
				'desc'      => _x( 'Enable this option to pay commissions to vendors when the order has a specific status.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'default'   => 'yes',
			),
			array(
				'title'     => _x( 'Order status', '[Admin] Option name', 'yith-woocommerce-product-vendors' ),
				'id'        => 'yith_wcmv_checkout_commissions_order_status',
				'desc'      => _x( 'Set the order status used to pay the commissions', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
				'type'      => 'yith-field',
				'yith-type' => 'checkbox-array',
				'multiple'  => true,
				'options'   => array(
					'completed'  => _x( 'Completed', '[Admin] Option name', 'yith-woocommerce-product-vendors' ),
					'processing' => _x( 'Processing', '[Admin] Option name', 'yith-woocommerce-product-vendors' ),
				),
				'default'   => array( 'completed' ),
				'deps'      => array(
					'id'    => 'yith_wcmv_checkout_commissions_payment',
					'value' => 'yes',
				),
			),
			array(
				'title'       => _x( 'Gateway to use', '[Admin] Option name', 'yith-woocommerce-product-vendors' ),
				'id'          => 'yith_wcmv_checkout_gateway',
				'desc'        => sprintf(
				// translators: %s is the link to the settings gateways section.
					_x( 'Select the gateway to use. Check all the available gateways <a href="%s" target="_blank">here</a>.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
					yith_wcmv_get_admin_panel_url(
						array(
							'tab'     => 'commissions',
							'sub_tab' => 'commissions-gateways',
						)
					)
				),
				'type'        => 'yith-field',
				'yith-type'   => 'select',
				'options'     => array_merge( array( '' => '' ), YITH_Vendors_Gateways::get_available_gateways_on_checkout() ),
				'default'     => '',
				'class'       => 'wc-enhanced-select',
				'placeholder' => _x( '- No gateway available -', '[Admin] Select gateway empty value', 'yith-woocommerce-product-vendors' ),
				'deps'        => array(
					'id'    => 'yith_wcmv_checkout_commissions_payment',
					'value' => 'yes',
				),
			),

			array(
				'type' => 'sectionend',
			),
		),
	)
);
