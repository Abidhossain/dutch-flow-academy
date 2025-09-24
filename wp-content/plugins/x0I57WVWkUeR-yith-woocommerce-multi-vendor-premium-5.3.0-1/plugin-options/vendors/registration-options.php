<?php
/**
 * Vendors Registration subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$wc_account_settings_uri = esc_url(
	add_query_arg(
		array(
			'page' => 'wc-settings',
			'tab'  => 'account',
		),
		admin_url( 'admin.php' )
	)
);

return array(
	'vendors-registration' => array(

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => __( 'Vendors Registration Page', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wpv_wc_registration_options_title',
		),

		array(
			'title'     => _x( 'Vendors registration page', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_become_a_vendor_page_id',
			'type'      => 'yith-field',
			'yith-type' => 'ajax-posts',
			'data'      => array(
				'placeholder' => __( 'Search for a page...', 'yith-woocommerce-product-vendors' ),
				'post_type'   => 'page',
			),
			'default'   => 0,
			'desc'      => _x( 'Select the page that will contain the vendor registration form. Please note: if you select any page different from the default one (Become a Vendor), you\'ll need to insert the following shortcode on that page: <b>[yith_wcmv_become_a_vendor]</b>.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
		),

		array(
			'id'               => 'yith_wcmv_vendor_registration_form_fields',
			'type'             => 'yith-field',
			'yith-type'        => 'vendor-registration-table',
			'yith-display-row' => false,
		),

		array(
			'title'     => _x( 'Vendor registration on My Account page', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			// translators: %s is used for add a link to WooCommerce settings page.
			'desc'      => sprintf( _x( 'Enable to allow users to sign up as vendors during the registration process in your site. Note: you have to allow registration on the My Account page from <a href="%s" target="_blank"> WooCommerce > Settings > Accounts & Privacy.</a>', '[Admin]Option description', 'yith-woocommerce-product-vendors' ), $wc_account_settings_uri ),
			'id'        => 'yith_wpv_vendors_my_account_registration',
			'default'   => 'no',
		),

		array(
			'title'     => __( 'Enable vendor accounts automatically', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => __( 'This option automatically enables vendors after registration. If you disable it, the administrator must enable each vendor account manually.', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_my_account_registration_auto_approve',
			'default'   => 'no',
		),

		array(
			'title'     => _x( 'Add the Terms & Conditions to the registration form', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable to show the Terms & Conditions checkbox in the registration form.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_registration_required_terms_and_conditions',
			'default'   => 'no',
		),

		array(
			'title'     => _x( 'Add the Privacy Policy to the registration form', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => _x( 'Enable to show the Privacy Policy checkbox in the registration form.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_vendors_registration_required_privacy_policy',
			'default'   => 'no',
		),

		array(
			'type' => 'sectionend',
		),
	),
);
