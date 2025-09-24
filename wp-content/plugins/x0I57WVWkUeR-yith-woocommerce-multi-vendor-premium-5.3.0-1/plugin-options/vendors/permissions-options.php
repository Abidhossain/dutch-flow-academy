<?php
/**
 * Vendors Permissions subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$sell_options  = YITH_Vendors_Integrations::instance()->prepare_options( array( 'name_your_price', 'subscription', 'membership', 'product_bundles', 'booking' ) );
$order_options = YITH_Vendors_Integrations::instance()->prepare_options( array( 'sms_notifications', 'order_tracking', 'pdf_invoice', 'request_quote' ) );
$store_options = YITH_Vendors_Integrations::instance()->prepare_options( array( 'catalog_mode', 'waiting_list', 'badge_management', 'advanced_product_options', 'size_charts', 'role_based_prices', 'bulk_product_editing', 'coupon_email_system', 'review_discounts' ) );

foreach ( array( 'store_options', 'order_options', 'sell_options' ) as $key ) {
	end( $$key );
	$$key[ key( $$key ) ]['checkboxgroup'] = 'end';
}


return array(
	'vendors-permissions' => array(

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => __( 'Vendors Permissions', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_vendors_permissions_options_title',
		),

		array(
			'name'          => _x( 'Vendor can sell:', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'yith_wpv_vendors_can_sell[simple]',
			'desc'          => _x( 'Simple products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'yes',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_can_sell[grouped]',
			'desc'          => _x( 'Grouped products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'yes',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_can_sell[variable]',
			'desc'          => _x( 'Variable products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'yes',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_can_sell[external]',
			'desc'          => _x( 'External products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'yes',
		),

		...$sell_options,

		array(
			'title'     => _x( 'Enable the advanced editor for the vendors\' descriptions', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable this option to allow vendors to use an advanced editor for their stores\' descriptions.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_option_editor_management',
			'default'   => 'no',
		),

		array(
			'title'     => _x( 'Enable the media button in the text editor', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable this option to allow vendors to use the media button in the advanced editor.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_option_editor_media',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'yith_wpv_vendors_option_editor_management',
				'value' => 'yes',
			),
		),

		array(
			'title'     => _x( 'Limit the number of products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Enable to set the maximum number of products a vendor can publish.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_enable_product_amount',
			'default'   => 'no',
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
		),

		array(
			'title'              => _x( 'Each vendor can sell a max. of:', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'               => 'yith-field',
			'yith-type'          => 'number',
			'default'            => 25,
			'desc'               => _x( 'Set the maximum number of products a vendor can publish.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
			'id'                 => 'yith_wpv_vendors_product_limit',
			'min'                => 0,
			'step'               => 1,
			'inline_description' => _x( 'products', '[Admin] Option inline description', 'yith-woocommerce-product-vendors' ),
			'deps'               => array(
				'id'    => 'yith_wpv_enable_product_amount',
				'value' => 'yes',
			),
		),

		array(
			'title'     => _x( 'When a vendor creates a product:', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Choose whether vendors can publish a product without the admin\'s review or not. Note: you can override this option for each specific vendor.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_option_skip_review',
			'options'   => array(
				'yes' => _x( 'It can be published directly, without the admin\'s review', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
				'no'  => _x( 'The product will remain pending until the admin approves it', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'no',
		),

		array(
			'title'     => __( 'Force the "Skip admin\'s review" option for all vendors', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'buttons',
			'desc'      => __( 'Force the option to skip the admin\'s review to publish products for all vendors. Note: this global rule can be overridden inside each vendor\'s profile.', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_skip_review_for_all',
			'buttons'   => array(
				array(
					'name'  => __( 'Force option', 'yith-woocommerce-product-vendors' ),
					'class' => 'button-primary yith_wpv_vendors_skip_review_for_all',
					'data'  => array(
						'action' => 'force_skip_review_option',
					),
				),
			),
		),

		array(
			'title'     => _x( 'When a vendor edits a product:', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => _x( 'Choose whether vendors can edit a product and update it without the admin\'s review or not. Note: you can override this option for each specific vendor.', '[Admin] Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_option_pending_post_status',
			'options'   => array(
				'no'  => _x( 'The product can be updated without the admin\'s review', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
				'yes' => _x( 'The product will remain pending until the admin approves it', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => 'no',
		),

		array(
			'name'          => _x( 'Regarding his/her store, a vendor can:', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'yith_wpv_vendors_option_coupon_management',
			'desc'          => _x( 'Create coupons to be used on his/her products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_edit_store_slug',
			'desc'          => _x( 'Edit the store slug', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'yes',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_product_tags_management',
			'desc'          => _x( 'Assign tags to products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'yes',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_featured_management',
			'desc'          => _x( 'Set products as "featured" to highlight them', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_product_import_management',
			'desc'          => _x( 'Import products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_product_export_management',
			'desc'          => _x( 'Export products', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_review_management',
			'desc'          => _x( 'Manage reviews from his/her customers', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		...$store_options,

		array(
			'name'          => _x( 'Regarding his/her orders, a vendor can:', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'yith_wpv_vendors_option_order_management',
			'desc'          => _x( 'Manage orders', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_order_refund_synchronization',
			'desc'          => _x( 'Manage refunds', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_order_resend_email',
			'desc'          => _x( 'Send emails to the customers', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_order_edit_custom_fields',
			'desc'          => _x( 'Edit custom fields', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_order_show_customer',
			'desc'          => _x( 'See customer info in the order details', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_order_show_payment',
			'desc'          => _x( 'See payment info in the order details', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'yith_wpv_vendors_option_order_show_billing_shipping',
			'desc'          => _x( 'See billing and shipping info in the order details', '[Admin] Option label', 'yith-woocommerce-product-vendors' ),
			'default'       => 'no',
		),

		...$order_options,

		array(
			'title'     => __( 'Parent order and suborder synchronization', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable this option to synchronize all of the changes made to parent orders with the vendors\' individual orders.', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_option_order_synchronization',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'yith_wpv_vendors_option_order_management',
				'value' => 'yes',
			),
		),

		array(
			'title'     => __( 'Suborder and parent order statuses synchronization', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'Enable this option to update the parent order status when editing the suborder status.', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_option_suborder_synchronization',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'yith_wpv_vendors_option_order_management',
				'value' => 'yes',
			),
		),

		array(
			'type' => 'sectionend',
		),
	),
);
