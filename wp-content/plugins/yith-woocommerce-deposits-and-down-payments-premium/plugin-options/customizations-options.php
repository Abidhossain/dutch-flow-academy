<?php
/**
 * Customizations settings page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

return apply_filters(
	'yith_wcdp_customizations_settings',
	array(
		'customizations' => array(
			'deposit-customizations'               => array(
				'title' => __( 'Customization Options', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'  => 'title',
				'id'    => 'yith_wcdp_deposit_customizations',
				'desc'  => '',
			),
			'deposit-hide-loop-button'             => array(
				'title'     => __( 'Hide "Pay deposit" button on shop pages', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => __( 'Enable to hide the "Pay Deposit" button on shop pages.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_hide_loop_button',
				'default'   => 'no',
			),
			'deposit-show-product-notices'         => array(
				'title'     => __( 'Show custom notices on product page', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => __( 'Enable to show a custom notice about the deposit option in all products.', 'yith-woocommerce-deposits-and-down-payments' ),
				'id'        => 'yith_wcdp_deposit_labels_show_product_notes',
				'default'   => 'yes',
			),
			'deposit-labels-product-note'          => array(
				'title'         => __( 'Notice to show on products with balance payment required online', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'          => 'yith-field',
				'yith-type'     => 'textarea-editor',
				'css'           => 'width: 100%; min-height: 150px;',
				'id'            => 'yith_wcdp_deposit_labels_product_note',
				'textarea_rows' => 5,
				'deps'          => array(
					'id'    => 'yith_wcdp_deposit_labels_show_product_notes',
					'value' => 'yes',
				),
			),
			'deposit-labels-pay-in-loco'           => array(
				'title'         => __( 'Notice to show on products with balance payment not managed online', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'          => 'yith-field',
				'yith-type'     => 'textarea-editor',
				'css'           => 'width: 100%; min-height: 150px;',
				'id'            => 'yith_wcdp_deposit_labels_pay_in_loco',
				'textarea_rows' => 5,
				'deps'          => array(
					'id'    => 'yith_wcdp_deposit_labels_show_product_notes',
					'value' => 'yes',
				),
			),
			'deposit-labels-product-note-position' => array(
				'title'     => __( 'Notice position on product page', 'yith-woocommerce-deposits-and-down-payments' ),
				'desc'      => __( 'Choose the position of the notice on the product page.', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'      => 'yith-field',
				'yith-type' => 'select',
				'class'     => 'wc-enhanced-select',
				'options'   => array(
					'none'                                => __( 'Do not show any note on product', 'yith-woocommerce-deposits-and-down-payments' ),
					'woocommerce_template_single_title'   => __( 'Below product title', 'yith-woocommerce-deposits-and-down-payments' ),
					'woocommerce_template_single_price'   => __( 'Below product price', 'yith-woocommerce-deposits-and-down-payments' ),
					'woocommerce_template_single_excerpt' => __( 'Below product excerpt', 'yith-woocommerce-deposits-and-down-payments' ),
					'woocommerce_template_single_add_to_cart' => __( 'Below single Add to Cart', 'yith-woocommerce-deposits-and-down-payments' ),
					'woocommerce_product_meta_end'        => __( 'Below product meta', 'yith-woocommerce-deposits-and-down-payments' ),
					'woocommerce_template_single_sharing' => __( 'Below product share', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'id'        => 'yith_wcdp_deposit_labels_product_note_position',
				'default'   => '',
				'deps'      => array(
					'id'    => 'yith_wcdp_deposit_labels_show_product_notes',
					'value' => 'yes',
				),
			),
			'deposit-customizations-end'           => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcdp_deposit_customizations',
				'desc' => '',
			),
			'deposit-labels-options'               => array(
				'title' => __( 'Labels & Messages', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith_wcdp_deposit_labels_options',
			),
			'deposit-labels-deposit'               => array(
				'title'   => __( 'Deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'    => 'text',
				'css'     => 'min-width: 300px;',
				'id'      => 'yith_wcdp_deposit_labels_deposit',
				'default' => __( 'Deposit', 'yith-wcdp' ),
			),
			'deposit-labels-pay-deposit'           => array(
				'title'   => __( 'Pay deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'    => 'text',
				'css'     => 'min-width: 300px;',
				'id'      => 'yith_wcdp_deposit_labels_pay_deposit',
				'default' => __( 'Pay deposit', 'yith-woocommerce-deposits-and-down-payments' ),
			),
			'deposit-labels-pay-full-amount'       => array(
				'title'   => __( 'Pay full amount', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'    => 'text',
				'css'     => 'min-width: 300px;',
				'id'      => 'yith_wcdp_deposit_labels_pay_full_amount',
				'default' => __( 'Pay full amount', 'yith-woocommerce-deposits-and-down-payments' ),
			),
			'deposit-labels-partially-paid-status' => array(
				'title'   => __( 'Partially paid', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'    => 'text',
				'css'     => 'min-width: 300px;',
				'id'      => 'yith_wcdp_deposit_labels_partially_paid_status',
				'default' => __( 'Partially paid', 'yith-woocommerce-deposits-and-down-payments' ),
			),
			'deposit-labels-full-price-label'      => array(
				'title'   => __( 'Full price label', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'    => 'text',
				'css'     => 'min-width: 300px;',
				'id'      => 'yith_wcdp_deposit_labels_full_price_label',
				'default' => __( 'Full price', 'yith-woocommerce-deposits-and-down-payments' ),
			),
			'deposit-labels-balance-label'         => array(
				'title'   => __( 'Balance label', 'yith-woocommerce-deposits-and-down-payments' ),
				'type'    => 'text',
				'css'     => 'min-width: 300px;',
				'id'      => 'yith_wcdp_deposit_labels_balance_label',
				'default' => __( 'Balance', 'yith-woocommerce-deposits-and-down-payments' ),
			),
			'deposit-labels-options-end'           => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcdp_deposit_labels_options',
				'desc' => '',
			),
		),
	)
);
