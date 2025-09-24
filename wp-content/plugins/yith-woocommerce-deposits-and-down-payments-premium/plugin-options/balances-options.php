<?php
/**
 * Balances settings page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

return apply_filters(
	'yith_wcdp_balances_settings',
	array(
		'balances' => array(
			'balance-options'                             => array(
				'title' => __( 'Balance Options', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'  => 'title',
				'id'    => 'yith_wcdp_balance',
				'desc'  => '',
			),
			'balance-orders'                              => array(
				'title'     => __( 'Balance order creation', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'desc'      => __( 'Choose how to manage the balance orders. This option can be overridden for specific products.', 'yith-woocommerce-deposits-and-down-payments' ),
				'options'   => array(
					'pending' => __( 'Create balance orders with "Pending payment" status, and users will pay the balance online', 'yith-woocommerce-deposits-and-down-payments' ),
					'on-hold' => __( 'Create balance orders with "On hold" status, and manage payments manually (e.g.: users pay cash in your shop)', 'yith-woocommerce-deposits-and-down-payments' ),
					'none'    => __( 'Do not create balance orders', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'id'        => 'yith_wcdp_create_balance',
				'default'   => 'pending',
			),
			'balance-type'                                => array(
				'title'             => __( 'If an order includes multiple deposits, for the balance', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'              => 'yith-field',
				'yith-type'         => 'radio',
				'options'           => array(
					'single'   => __( 'Create a single balance order that includes all products', 'yith-woocommerce-deposits-and-down-payments' ),
					'multiple' => __( 'Create a specific balance order for each product', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'desc'              => __( 'Choose how to manage the balance orders for orders that include multiple deposits.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'                => 'yith_wcdp_balance_type',
				'default'           => 'multiple',
				'custom_attributes' => array(
					'data-dependencies' => wp_json_encode( array( 'yith_wcdp_create_balance' => '!none' ) ),
				),
			),
			'deposit-expiration-enable'                   => array(
				'title'             => __( 'Require balance payment to customers', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'              => 'yith-field',
				'yith-type'         => 'onoff',
				'desc'              => __( 'Enable to choose when and how to require the balance payment.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'                => 'yith_wcdp_deposit_expiration_enable',
				'default'           => 'no',
				'custom_attributes' => array(
					'data-dependencies' => wp_json_encode( array( 'yith_wcdp_create_balance' => '!none' ) ),
				),
			),
			'deposit-expiration-type'                     => array(
				'title'     => __( 'The balance payment will be required', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'select',
				'class'     => 'wc-enhanced-select',
				'id'        => 'yith_wcdp_deposits_expiration_type',
				'css'       => 'min-width: 100px;',
				'default'   => 'num_of_days',
				'options'   => apply_filters(
					'yith_wcdp_deposit_expiration_types',
					array(
						'specific_date' => __( 'On a specific date', 'yith-woocommerce-deposits-and-down-payments' ),
						'num_of_days'   => __( 'After a specific range of days from the deposit', 'yith-woocommerce-deposits-and-down-payments' ),
					)
				),
				'deps'      => array(
					'id'    => 'yith_wcdp_deposit_expiration_enable',
					'value' => 'yes',
				),
			),
			'deposit-expiration-duration'                 => array(
				'title'       => __( 'Require balance payment', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'        => 'yith-field',
				'yith-type'   => 'custom',
				'action'      => 'yith_wcdp_print_balance_expiration_days_field',
				'class'       => 'balance-expiration-days-field',
				'inline_desc' => __( 'days from the deposit.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'          => 'yith_wcdp_deposits_expiration_duration',
				'deps'        => array(
					'id'    => 'yith_wcdp_deposits_expiration_type',
					'value' => 'num_of_days',
				),
			),
			'deposit-expiration-date'                     => array(
				'title'     => __( 'Require balance payment on', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'datepicker',
				'desc'      => __( 'This option can be overridden for specific products.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_deposits_expiration_date',
				'default'   => '',
				'data'      => array(
					'date-format' => 'yy-mm-dd',
				),
				'deps'      => array(
					'id'    => 'yith_wcdp_deposits_expiration_type',
					'value' => 'specific_date',
				),
			),
			'notify-customer-deposit-expiring-days-limit' => array(
				'title'       => __( 'Send the email about the balance payment', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'        => 'yith-field',
				'yith-type'   => 'custom',
				'action'      => 'yith_wcdp_print_duration_field',
				'class'       => 'duration-field',
				'inline_desc' => __( 'before the payment due date.', 'yith-woocommerce-deposits-and-down-payments' ),
				'desc'        => __( 'Choose when to send the email to notify the balance payment to customers.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'          => 'yith_wcdp_notify_customer_deposit_expiring_days_limit',
				'default'     => 15,
				'deps'        => array(
					'id'    => 'yith_wcdp_deposit_expiration_enable',
					'value' => 'yes',
				),
			),
			'deposit-expiration-fallback'                 => array(
				'title'     => __( 'If balance is not paid', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'custom',
				'desc'      => __( 'Choose the action to execute when a balance is not paid.', 'yith-woocommerce-deposits-and-down-payments' ),
				'action'    => 'yith_wcdp_print_expiration_fallback_field',
				'id'        => 'yith_wcdp_deposit_expiration_fallback',
				'deps'      => array(
					'id'    => 'yith_wcdp_deposit_expiration_enable',
					'value' => 'yes',
				),
			),
			'deposit-expiration-product-fallback'         => array(
				'title'     => __( 'When balance is required on a specific date, and is overdue', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'select',
				'class'     => 'wc-enhanced-select',
				'id'        => 'yith_wcdp_deposits_expiration_product_fallback',
				'css'       => 'min-width: 100px;',
				'default'   => 'disable_deposit',
				'options'   => array(
					'do_nothing'           => __( 'Do nothing', 'yith-woocommerce-deposits-and-down-payments' ),
					'disable_deposit'      => __( 'Disable deposit for the product', 'yith-woocommerce-deposits-and-down-payments' ),
					'item_not_purchasable' => __( 'Make product no longer purchasable', 'yith-woocommerce-deposits-and-down-payments' ),
					'hide_item'            => __( 'Hide product from catalog', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'deps'      => array(
					'id'    => 'yith_wcdp_deposits_expiration_type',
					'value' => 'specific_date',
				),
			),
			'balance-options-end'                         => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcdp_balance',
				'desc' => '',
			),
		),
	)
);
