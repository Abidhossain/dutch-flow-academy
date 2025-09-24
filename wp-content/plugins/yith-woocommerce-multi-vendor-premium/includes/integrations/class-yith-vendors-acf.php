<?php
/**
 * Advanced Custom Fields compatibility class
 *
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

if ( ! class_exists( 'YITH_Vendors_ACF' ) ) {
	/**
	 * Handle support for Advanced Custom Fields plugin
	 */
	class YITH_Vendors_ACF {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * ACF fields for taxonomy
		 *
		 * @since 4.2.0
		 * @var array
		 */
		protected $fields = array();

		/**
		 * Construct
		 */
		private function __construct() {
			$this->init();
		}

		/**
		 * Init ACF integrations hooks and filters
		 *
		 * @since  4.2.0
		 * @return void
		 */
		protected function init() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( apply_filters( 'yith_wcmv_skip_acf_fields_integration', $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) ) {
				return;
			}

			// Add ACF fields to vendor modal.
			add_filter( 'yith_wcmv_vendor_admin_fields', array( $this, 'add_fields' ), 10, 1 );
			// Remove ACF field from standard dave process.
			add_filter( 'yith_wcmv_vendor_factory_validate_data', array( $this, 'exclude_fields_from_save' ), 10, 1 );
			// Save ACF field for vendor.
			add_action( 'yith_wcmv_after_vendor_object_save', array( $this, 'save_fields' ), 10, 1 );
			// Get ACF field value.
			add_filter( 'yith_wcmv_get_vendor_edit_data', array( $this, 'get_fields_value' ), 10, 4 );
		}

		/**
		 * Get an array of ACF fields
		 *
		 * @since  4.2.0
		 * @return array
		 */
		protected function get_fields() {
			if ( empty( $this->fields ) ) {
				$acf_groups = acf_get_field_groups( array( 'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME ) );
				foreach ( $acf_groups as $acf_group ) {
					$this->fields = array_merge( $this->fields, acf_get_fields( $acf_group ) );
				}
			}

			return $this->fields;
		}

		/**
		 * Get an array of ACF fields formatted to be used in vendor forms.
		 *
		 * @since  4.2.0
		 * @return array
		 */
		public function get_fields_formatted() {
			$fields = array();
			foreach ( $this->get_fields() as $acf_field ) {
				$key            = $acf_field['key'];
				$fields[ $key ] = array(
					'type'        => $acf_field['type'],
					'name'        => "acf[{$key}]",
					'label'       => $acf_field['label'],
					'default'     => $acf_field['default_value'] ?? '',
					'placeholder' => $acf_field['placeholder'] ?? '',
					'options'     => isset( $acf_field['choices'] ) ? $acf_field['choices'] : array(),
				);

				if ( 'number' === $acf_field['type'] ) {
					$fields[ $key ]['custom_attributes'] = array_filter(
						array(
							'min'  => ( isset( $acf_field['min'] ) && '' !== $acf_field['min'] ) ? $acf_field['min'] : null,
							'max'  => ( isset( $acf_field['max'] ) && '' !== $acf_field['max'] ) ? $acf_field['max'] : null,
							'step' => ( isset( $acf_field['step'] ) && '' !== $acf_field['step'] ) ? $acf_field['step'] : null,
						),
						'is_numeric'
					);
				}
			}

			return apply_filters( 'yith_wcmv_get_acf_fields', $fields );
		}

		/**
		 * Add ACF to vendor add/edit modal
		 *
		 * @since  4.2.0
		 * @param array $fields An array of modal fields.
		 * @return array
		 */
		public function add_fields( $fields ) {
			$acf_fields = $this->get_fields_formatted();
			if ( empty( $acf_fields ) ) {
				return $fields;
			}

			// Be sure that these fields are added before socials.
			$socials = isset( $fields['additional']['socials'] ) ? $fields['additional']['socials'] : array();
			unset( $fields['additional']['socials'] );
			$fields['additional'] = array_merge( $fields['additional'], $acf_fields, array( 'socials' => $socials ) );

			return $fields;
		}

		/**
		 * Exclude ACF fields from standard vendor save process.
		 *
		 * @since  4.2.0
		 * @param array $data The data to save.
		 * @return array
		 */
		public function exclude_fields_from_save( $data ) {
			$acf_fields = $this->get_fields_formatted();
			if ( ! empty( $acf_fields ) ) {
				$data = array_diff_key( $data, $acf_fields );
			}

			return $data;
		}

		/**
		 * Save ACF fields for vendor
		 *
		 * @since  4.2.0
		 * @param YITH_Vendor $vendor The vendor saved.
		 * @return void
		 */
		public function save_fields( $vendor ) {
			// trigger ACF save action.
			acf_save_post( 'term_' . $vendor->get_id() );
		}

		/**
		 * Get ACF fields value for given vendor
		 *
		 * @since  4.2.0
		 * @param array       $data   An array of vendor data.
		 * @param YITH_Vendor $vendor The vendor object.
		 * @param boolean     $modal  True if is modal, false otherwise.
		 * @param array       $fields An array of vendor fields to retrieve.
		 * @return mixed
		 */
		public function get_fields_value( $data, $vendor, $modal, $fields ) {
			foreach ( $this->get_fields() as $field ) {
				$key          = $field['key'];
				$value        = acf_get_value( 'term_' . $vendor->get_id(), $field );
				$data[ $key ] = $value;
			}
			return $data;
		}
	}
}

YITH_Vendors_ACF::instance();
