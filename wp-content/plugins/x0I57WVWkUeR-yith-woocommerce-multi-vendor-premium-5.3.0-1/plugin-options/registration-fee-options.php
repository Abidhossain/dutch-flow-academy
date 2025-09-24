<?php
/**
 * Vendors Registration Fee Module sub-tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'registration-fee' => array(
		array(
			'type' => 'sectionstart',
		),
		array(
			'title' => __( 'Registration fee settings', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_modules_registration_fee_options_title',
		),
		array(
			'title'     => _x( 'Fee title', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => _x( 'Set the title of the fee to be displayed in the checkout process and in the order details.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wcmv_registration_fee_product_title',
			// translators: %s is the singular vendor tax label.
			'default'   => sprintf( _x( '%s registration fee', '[Admin]Option default. %s is the singular vendor tax label', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_singular_label( 'ucfirst' ) ),
		),
		array(
			'title'     => _x( 'Fee price', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'price',
			'desc'      => _x( 'Enter the fee amount.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wcmv_registration_fee_product_price',
			'default'   => 100,
		),
		array(
			'title'     => _x( 'Fee tax status', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Define whether the fee is taxable or not.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wcmv_registration_fee_product_tax_status',
			'options'   => array(
				'taxable' => _x( 'Taxable', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'none'    => _x( 'None', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'taxable',
		),
		array(
			'title'     => _x( 'Fee tax class', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Choose a tax class for this fee.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wcmv_registration_fee_product_tax_class',
			'options'   => wc_get_product_tax_class_options(),
			'default'   => '',
			'deps'      => array(
				'id'    => 'yith_wcmv_registration_fee_product_tax_status',
				'value' => 'taxable',
			),
		),
		array(
			'type' => 'sectionend',
		),
	),
);
