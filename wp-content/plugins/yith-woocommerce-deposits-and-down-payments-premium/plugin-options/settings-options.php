<?php
/**
 * General settings page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

return apply_filters(
	'yith_wcdp_general_settings',
	array(
		'settings' => array(
			'general_options' => array(
				'type'       => 'multi_tab',
				'nav-layout' => 'horizontal',
				'sub-tabs'   => array(
					'settings-deposits' => array(
						'title'       => __( 'General settings', 'yith-woocommerce-deposits-and-down-payments' ),
						'description' => __( 'Set up deposit options for the products in your shop.', 'yith-woocommerce-deposits-and-down-payments' ),
					),
					'settings-rules'    => array(
						'title'       => __( 'Deposit rules', 'yith-woocommerce-deposits-and-down-payments' ),
						'description' => __( 'Create advanced deposit rules for your products. ', 'yith-woocommerce-deposits-and-down-payments' ) . '<br>' . __( 'The rules will override the default deposit amount (defined in General Options) for specific user roles, product categories, or products. ', 'yith-woocommerce-deposits-and-down-payments' ),
					),
				),
			),
		),
	)
);
