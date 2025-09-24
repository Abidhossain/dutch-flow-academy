<?php
/**
 * Shipping extra costs subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'extra-cost' => array(
		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Shipping Extra Cost', '[Admin]Shipping module tab title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'shipping_extra_cost_title',
		),

		array(
			'title'     => _x( 'Set extra costs based on the number of products in the cart', '[Admin]Shipping module option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => '',
			'id'        => 'enable_shipping_extra_cost',
		),

		array(
			'title'     => _x( 'Extra costs rules', '[Admin]Shipping module option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'inline-fields',
			'default'   => array(
				'items'       => 1,
				'cost'        => 0,
				'applied_how' => 'per_product',
			),
			'fields'    => array(
				'html'        => array(
					'type' => 'html',
					'html' => _x( 'If the cart contains more than', '[Admin]Shipping module option label. Followed by an input quantity', 'yith-woocommerce-product-vendors' ),
				),
				'items'       => array(
					'type'    => 'number',
					'class'   => 'yith-wcmv-input-integer',
					'default' => 1,
					'min'     => 1,
					'step'    => 1,
				),
				'html1'       => array(
					'type' => 'html',
					// translators: %s is a placeholder for the currency symbol.
					'html' => sprintf( _x( 'item(s), add an extra cost of %s', '[Admin]Shipping module option label. Followed and preceded by an input quantity', 'yith-woocommerce-product-vendors' ), get_woocommerce_currency_symbol() ),
				),
				'cost'        => array(
					'type' => 'price',
				),
				'applied_how' => array(
					'type'    => 'select',
					'options' => array(
						'per_product' => _x( 'for each additional product', '[Admin]Shipping module option label.', 'yith-woocommerce-product-vendors' ),
						'fixed'       => _x( 'as a fixed cost', '[Admin]Shipping module option label.', 'yith-woocommerce-product-vendors' ),
					),
				),
			),
			'id'        => 'shipping_extra_cost_items',
			'deps'      => array(
				'id'    => 'enable_shipping_extra_cost',
				'value' => 'yes',
			),
		),

		array(
			'title'     => '',
			'type'      => 'yith-field',
			'yith-type' => 'inline-fields',
			'default'   => array(
				'items'       => 1,
				'cost'        => 0,
				'applied_how' => 'per_product',
			),
			'fields'    => array(
				'html'        => array(
					'type' => 'html',
					'html' => _x( 'If the cart contains more than', '[Admin]Shipping module option label. Followed by an input quantity', 'yith-woocommerce-product-vendors' ),
				),
				'items'       => array(
					'type'    => 'number',
					'default' => 1,
					'min'     => 1,
					'step'    => 1,
					'class'   => 'yith-wcmv-input-integer',
				),
				'html1'       => array(
					'type' => 'html',
					// translators: %s is a placeholder for the currency symbol.
					'html' => sprintf( _x( 'product(s) of the same type, add an extra cost of %s', '[Admin]Shipping module option label. Followed and preceded by an input quantity', 'yith-woocommerce-product-vendors' ), get_woocommerce_currency_symbol() ),
				),
				'cost'        => array(
					'type' => 'price',
				),
				'applied_how' => array(
					'type'    => 'select',
					'options' => array(
						'per_product' => _x( 'for each additional product', '[Admin]Shipping module option label.', 'yith-woocommerce-product-vendors' ),
						'fixed'       => _x( 'as a fixed cost', '[Admin]Shipping module option label.', 'yith-woocommerce-product-vendors' ),
					),
				),
			),
			'id'        => 'shipping_extra_cost_products',
			'deps'      => array(
				'id'    => 'enable_shipping_extra_cost',
				'value' => 'yes',
			),
		),

		array(
			'type' => 'sectionend',
		),
	),
);
