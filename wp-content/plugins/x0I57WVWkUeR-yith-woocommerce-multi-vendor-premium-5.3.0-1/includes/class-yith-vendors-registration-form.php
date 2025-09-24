<?php
/**
 * This class is a method collection useful for registration form
 *
 * @class      YITH_Vendors_Registration_Form
 * @since      4.2.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Registration_Form', false ) ) {
	/**
	 * This class is a method collection useful for registration form
	 */
	class YITH_Vendors_Registration_Form {

		/**
		 * Fields DB option name
		 *
		 * @since 4.2.0
		 * @const string
		 */
		const OPTION_NAME = 'yith_wcmv_vendor_registration_form_fields';

		/**
		 * Get vendor registration default fields
		 *
		 * @since  4.2.0
		 * @return array
		 */
		public static function get_default_fields() {
			return apply_filters(
				'yith_wcmv_vendor_registration_default_fields',
				array(
					'vendor-name'         => array(
						'name'         => 'vendor-name',
						'label'        => apply_filters_deprecated( 'yith_wcmv_vendor_admin_settings_store_name_label', array( __( 'Store name', 'yith-woocommerce-product-vendors' ) ), '4.0.0' ),
						'type'         => 'text',
						'connected_to' => 'name',
						'required'     => 'yes',
						'position'     => 'form-row-wide',
						'active'       => 'yes',
					),
					'vendor-email'        => array(
						'name'         => 'vendor-email',
						'label'        => apply_filters_deprecated( 'yith_wcmv_vendor_admin_settings_store_email_label', array( __( 'Store email', 'yith-woocommerce-product-vendors' ) ), '4.0.0' ),
						'type'         => 'email',
						'connected_to' => 'store_email',
						'required'     => 'yes',
						'position'     => 'form-row-first',
						'active'       => 'yes',
					),
					'vendor-paypal-email' => array(
						'name'         => 'vendor-paypal-email',
						'label'        => __( 'PayPal email', 'yith-woocommerce-product-vendors' ),
						'type'         => 'email',
						'connected_to' => 'paypal_email',
						'required'     => 'yes' === get_option( 'yith_wpv_vendors_registration_required_paypal_email', 'no' ), // Deprecated. Leave for backward compatibility.
						'active'       => 'yes' === get_option( 'yith_wpv_vendors_registration_show_paypal_email', 'yes' ), // Deprecated. Leave for backward compatibility.
						'position'     => 'form-row-last',
					),
					'vendor-telephone'    => array(
						'name'         => 'vendor-telephone',
						'label'        => __( 'Phone', 'yith-woocommerce-product-vendors' ),
						'type'         => 'text',
						'connected_to' => 'telephone',
						'required'     => 'no',
						'active'       => 'yes',
						'position'     => 'form-row-first',
					),
					'vendor-vat'          => array(
						'name'         => 'vendor-vat',
						'label'        => get_option( 'yith_wpv_vat_label', __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ) ), // Deprecated. Leave for backward compatibility.
						'type'         => 'text',
						'connected_to' => 'vat',
						'required'     => 'yes' === get_option( 'yith_wpv_vendors_my_account_required_vat', 'no' ), // Deprecated. Leave for backward compatibility.
						'active'       => 'yes',
						'position'     => 'form-row-last',
					),
				)
			);
		}

		/**
		 * Get vendor registration form fields
		 *
		 * @since  4.2.0
		 * @return array
		 */
		public static function get_fields() {
			$fields = get_option( self::OPTION_NAME, self::get_default_fields() );
			return apply_filters( 'yith_wcmv_vendor_registration_form_fields', $fields );
		}

		/**
		 * Get vendor registration form fields frontend
		 *
		 * @since  4.2.0
		 * @return array
		 */
		public static function get_fields_frontend() {

			$frontend_fields = wp_cache_get( 'registration_fields', 'yith_wcmv' );
			if ( false === $frontend_fields ) {
				$frontend_fields = array();
				$fields          = self::get_fields();

				if ( 'yes' === get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' ) ) {
					$fields['vendor-terms'] = array(
						// translators: %s is the link to the vendor terms & conditions.
						'label'    => sprintf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank">Terms &amp; Conditions for Vendors</a>', 'yith-woocommerce-product-vendors' ), esc_url( get_permalink( get_option( 'yith_wpv_terms_and_conditions_page_id' ) ) ) ),
						'type'     => 'checkbox',
						'required' => 'yes',
					);
				}

				if ( 'yes' === get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' ) ) {
					$fields['vendor-privacy'] = array(
						// translators: %s is the link to the vendor privacy policy.
						'label'    => sprintf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank">Privacy Policy for Vendors</a>', 'yith-woocommerce-product-vendors' ), esc_url( get_permalink( get_option( 'yith_wpv_privacy_page' ) ) ) ),
						'type'     => 'checkbox',
						'required' => 'yes',
					);
				}

				foreach ( $fields as $key => $field ) {

					if ( isset( $field['active'] ) && ! yith_plugin_fw_is_true( $field['active'] ) ) {
						continue;
					}

					$key                     = isset( $field['name'] ) ? $field['name'] : $key; // Needed to use woocommerce_form_field functions.
					$frontend_fields[ $key ] = $field;

					// Handle special multiselect field.
					if ( 'multiselect' === $field['type'] ) {
						$frontend_fields[ $key ]['type']              = 'select';
						$frontend_fields[ $key ]['custom_attributes'] = array( 'multiple' => 'multiple' );
					}

					$position = ! empty( $field['position'] ) ? $field['position'] : 'form-row-wide';
					if ( ! empty( $field['class'] ) && is_array( $field['class'] ) ) {
						$frontend_fields[ $key ]['class'][] = $position;
					} else {
						$frontend_fields[ $key ]['class'] = array( $position );
					}

					$frontend_fields[ $key ]['required'] = ( isset( $frontend_fields[ $key ]['required'] ) && 'yes' === $frontend_fields[ $key ]['required'] );
				}

				wp_cache_set( 'registration_fields', $frontend_fields, 'yith_wcmv' );
			}

			return apply_filters( 'yith_wcmv_vendor_registration_form_fields_frontend', $frontend_fields );
		}

		/**
		 * Is a default field?
		 * Check if given field is a default one
		 *
		 * @since  4.2.0
		 * @param string $key The field key to check.
		 * @return boolean
		 */
		public static function is_default_field( $key ) {
			return array_key_exists( $key, self::get_default_fields() );
		}

		/**
		 * Return an array of fields for the registration table modal form
		 *
		 * @since  4.2.0
		 * @return array
		 */
		public static function get_admin_modal_fields() {
			return apply_filters(
				'yith_wcmv_registration_modal_form_fields',
				array(
					'name'         => array(
						'title'             => _x( 'Name', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'required'          => true,
						'class'             => array( 'ajax-check' ),
						'custom_attributes' => array(
							'data-error' => _x( 'This field is already defined', '[Admin]Vendor registration form field error', 'yith-woocommerce-product-vendors' ),
						),
					),

					'type'         => array(
						'title'   => _x( 'Type', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'default' => 'text',
						'type'    => 'select',
						'options' => array(
							'text'        => _x( 'Text', 'Text field', 'yith-woocommerce-product-vendors' ),
							'email'       => _x( 'Email', 'Email field', 'yith-woocommerce-product-vendors' ),
							'tel'         => _x( 'Phone', 'Phone number field', 'yith-woocommerce-product-vendors' ),
							'textarea'    => _x( 'Textarea', 'Textarea field', 'yith-woocommerce-product-vendors' ),
							'radio'       => _x( 'Radio', 'Radio button field', 'yith-woocommerce-product-vendors' ),
							'checkbox'    => _x( 'Checkbox', 'Checkbox field', 'yith-woocommerce-product-vendors' ),
							'select'      => _x( 'Select', 'Select field', 'yith-woocommerce-product-vendors' ),
							'multiselect' => _x( 'Multiselect', 'Select field', 'yith-woocommerce-product-vendors' ),
							'country'     => _x( 'Country', 'Select field for country', 'yith-woocommerce-product-vendors' ),
							'state'       => _x( 'State', 'Field for State', 'yith-woocommerce-product-vendors' ),
						),
					),

					'label'        => array(
						'title' => _x( 'Label', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
					),

					'class'        => array(
						'title' => _x( 'Class', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'desc'  => _x( 'Separate classes with commas.', '[Admin]Vendor registration form field description', 'yith-woocommerce-product-vendors' ),
					),

					'placeholder'  => array(
						'title' => _x( 'Placeholder', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'deps'  => array(
							'id'     => 'type',
							'values' => 'text,email,tel,textarea,select,multiselect',
						),
					),

					'connected_to' => array(
						'title'   => _x( 'Connect to', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'type'    => 'select',
						'options' => array(
							''             => '-',
							'location'     => _x( 'Store address', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'city'         => _x( 'Store city', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'zipcode'      => _x( 'Store postcode', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'country'      => _x( 'Store country', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'state'        => _x( 'Store state/province', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'vat'          => _x( 'Store CIF/VAT', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'telephone'    => _x( 'Store phone number', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'store_email'  => _x( 'Store email address', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'description'  => _x( 'Store description', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'legal_notes'  => _x( 'Company legal notes', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'bank_account' => _x( 'IBAN', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'paypal_email' => _x( 'PayPal email', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
						),
					),

					'options'      => array(
						'title' => _x( 'Options', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'type'  => 'options-table',
						'deps'  => array(
							'id'     => 'type',
							'values' => 'radio,select,multiselect',
						),
					),

					'position'     => array(
						'title'   => _x( 'Position', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'type'    => 'select',
						'options' => array(
							'form-row-first' => _x( 'First', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'form-row-last'  => _x( 'Last', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
							'form-row-wide'  => _x( 'Wide', '[Admin] Vendor registration form option label', 'yith-woocommerce-product-vendors' ),
						),
						'default' => 'form-row-wide',
					),

					'required'     => array(
						'title'   => _x( 'Required', '[Admin]Vendor registration form field label', 'yith-woocommerce-product-vendors' ),
						'type'    => 'onoff',
						'default' => 'no',
					),
				)
			);
		}

		/**
		 * Return an array of fields for the registration table modal form and it's default value
		 *
		 * @since  4.2.0
		 * @return array
		 */
		public static function get_admin_modal_fields_default() {
			$fields  = self::get_admin_modal_fields();
			$default = array();

			foreach ( $fields as $key => $field ) {
				$default[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
			}
			return $default;
		}
	}
}
