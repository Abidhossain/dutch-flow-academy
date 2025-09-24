<?php
/**
 * Shipping polices subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return apply_filters(
	'yith_wcmv_vendor_polices_options',
	array(
		'polices' => array(
			array(
				'type' => 'sectionstart',
			),

			array(
				'title' => _x(
					'Shipping Polices',
					'[Admin]Shipping module tab title',
					'yith-woocommerce-product-vendors'
				),
				'type'  => 'title',
				'id'    => 'shipping_polices_title',
			),

			array(
				'title'     => _x(
					'Shipping policy',
					'[Admin]Shipping module option label',
					'yith-woocommerce-product-vendors'
				),
				'desc'      => _x(
					'Your terms, conditions and instructions about shipping.',
					'[Admin]Shipping module option description',
					'yith-woocommerce-product-vendors'
				),
				'type'      => 'yith-field',
				'yith-type' => 'textarea',
				'default'   => '',
				'id'        => 'shipping_policy',
			),

			array(
				'title'     => _x(
					'Shipping refund policy',
					'[Admin]Shipping module option label',
					'yith-woocommerce-product-vendors'
				),
				'desc'      => _x(
					'Your terms, conditions and instructions about shipping refunds.',
					'[Admin]Shipping module option description',
					'yith-woocommerce-product-vendors'
				),
				'type'      => 'yith-field',
				'yith-type' => 'textarea',
				'default'   => '',
				'id'        => 'shipping_refund_policy',
			),

			array(
				'type' => 'sectionend',
			),
		),
	)
);
