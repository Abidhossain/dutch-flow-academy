<?php
/**
 * Deposits settings page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

return apply_filters(
	'yith_wcdp_deposits_settings',
	array(
		'settings-deposits' => array(
			'general-options'                        => array(
				'title' => __( 'General Options', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'  => 'title',
				'id'    => 'yith_wcdp_general',
				'desc'  => '',
			),
			'general-enable-deposit'                 => array(
				'title'     => __( 'Enable deposit on all products', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => __( 'Enable to activate the deposit option for all products. This option can be overridden for specific products.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_general_deposit_enable',
				'default'   => 'no',
			),
			'general-force-deposit'                  => array(
				'title'     => __( 'Set the deposit payment as:', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'desc'      => __( 'Choose how to manage the deposit payment for all products. This option can be overridden for specific products.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_general_deposit_force',
				'options'   => array(
					'no'  => __( 'Optional: users can choose whether to pay full amount or just a deposit', 'yith-woocommerce-deposits-and-down-payments' ),
					'yes' => __( 'Forced: users can only pay the deposit amount', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'default'   => 'yes',
				'deps'      => array(
					'id'    => 'yith_wcdp_general_deposit_enable',
					'value' => 'yes',
				),
			),
			'general-deposit-default'                => array(
				'title'     => __( 'Show deposit option selected by default', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => __( 'Enable to show the deposit option selected by default.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_general_deposit_default',
				'default'   => 'yes',
				'deps'      => array(
					'id'    => 'yith_wcdp_general_deposit_force',
					'value' => 'no',
				),
			),
			'general-deposit-amount'                 => array(
				'title'     => __( 'Default deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'custom',
				'action'    => 'yith_wcdp_print_deposit_amount_field',
				'id'        => 'yith_wcdp_general_deposit_amount',
				'deps'      => array(
					'id'    => 'yith_wcdp_general_deposit_enable',
					'value' => 'yes',
				),
			),
			'general-deposit-virtual'                => array(
				'title'     => __( 'Shipping costs (if any) will be:', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'no'  => __( 'Applied to the deposit order', 'yith-woocommerce-deposits-and-down-payments' ),
					'yes' => __( 'Applied to the balance order', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'desc'      => __( 'Choose how to manage shipping costs on products with the deposit option.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_general_deposit_virtual',
				'default'   => 'yes',
			),
			'general-enable-ajax-variation-handling' => array(
				'title'     => __( 'Load deposit data dynamically', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => __( 'Enable this option to load via AJAX deposit preferences for product variations; this will speed up single product page loading.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_general_enable_ajax_variation',
				'default'   => 'no',
			),
			'general-options-end'                    => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcdp_general',
				'desc' => '',
			),
		),
	)
);
