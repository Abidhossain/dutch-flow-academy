<?php
/**
 * YITH Vendors Other Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$new_page_url = admin_url( 'post-new.php?post_type=page' );

return apply_filters( 'yith_wcmv_other_settings',  array(
	'other' => array(

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'Other Options', '[Admin]Option section title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_other_options_title',
		),

		array(
			'title'   => _x( 'Terms & Conditions page', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'id'      => 'yith_wpv_terms_and_conditions_page_id',
			'type'    => 'single_select_page',
			'default' => 0,
			'class'   => 'wc-enhanced-select-nostd',
			// translators: %s is the create new page link.
			'desc'    => sprintf( _x( 'Set the page for the Vendors Terms & Conditions or <a href="%s" target="_blank">create a new page</a>.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ), $new_page_url ),
		),

		array(
			'title'   => _x( 'Privacy Policy page', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'id'      => 'yith_wpv_privacy_page',
			'type'    => 'single_select_page',
			'default' => 0,
			'class'   => 'wc-enhanced-select-nostd',
			// translators: %s is the create new page link.
			'desc'    => sprintf( _x( 'Set the page for the Vendors Privacy Policy or <a href="%s" target="_blank">create a new page</a>.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ), $new_page_url ),
		),

		array(
			'title'     => _x( 'Ask vendors to accept the Terms & Conditions and Privacy Policy when updated', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'If enabled, every time the Terms & Conditions and/or the Privacy Policy are updated, vendors will have to read and accept them again.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_manage_terms_and_privacy_revision',
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
		),

		array(
			'title'     => _x( 'If a vendor doesn\'t accept the new policies', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'desc'      => _x( 'Choose how to handle a vendor that does not accept the changes made to the Terms & Conditions and/or to the Privacy Policy.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'no_action'     => _x( 'Allow the vendor to keep selling anyway', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'disable_now'   => _x( 'Disable the vendor’s selling feature immediately and until the policies are accepted', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'disable_after' => _x( 'Wait x days after the policies update and then disable the vendors’ selling feature until policies are accepted', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'id'        => 'yith_wpv_manage_terms_and_privacy_revision_actions',
			'default'   => 'no_action',
			'deps'      => array(
				'id'    => 'yith_wpv_manage_terms_and_privacy_revision',
				'value' => 'yes',
			),
		),

		array(
			'title'     => _x( 'Days to wait:', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'id'        => 'yith_wpv_manage_terms_and_privacy_revision_disable_after',
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'min'       => 1,
			'step'      => 1,
			'default'   => 3,
			'deps'      => array(
				'id'    => 'yith_wpv_manage_terms_and_privacy_revision_actions',
				'value' => 'disable_after',
			),
		),

		array(
			'type' => 'sectionend',
		),

		array(
			'type' => 'sectionstart',
		),

		array(
			'title' => _x( 'GDPR', '[Admin]Option section title', 'yith-woocommerce-product-vendors' ),
			'type'  => 'title',
			'id'    => 'yith_wcmv_gdpr_options_title',
		),

		array(
			'title'     => _x( 'When handling an account erasure request', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			'type'      => 'yith-field',
			'yith-type' => 'checkbox-array',
			// translators: %s is used to add link to erase account tool.
			'desc'      => sprintf( _x( 'Choose whether or not to remove the vendor\'s data when handling an <a href="%s" target="_blank">account erasure request</a>.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ), esc_url( admin_url( 'tools.php?page=remove_personal_data' ) ) ),
			'id'        => 'yith_wpv_vendor_data_to_delete',
			'options'   => array(
				'profile'             => _x( 'Remove the vendor profile information', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'commissions_user_id' => _x( 'Remove user ID in the vendor\'s commissions', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
				'media'               => _x( 'Remove the vendor\'s avatar and header image from media files', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			),
			'default'   => array(),
		),

		array(
			'title'     => _x( 'Export commissions in personal data export requests', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
			// translators: %s is used to add link to export account tool.
			'desc'      => sprintf( _x( 'Enable to include commissions in an <a href="%s" target="_blank">account\'s personal data export request</a>.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ), esc_url( admin_url( 'tools.php?page=export_personal_data' ) ) ),
			'id'        => 'yith_vendor_exports_commissions',
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
		),

		array(
			'type' => 'sectionend',
		),
	),
) );
