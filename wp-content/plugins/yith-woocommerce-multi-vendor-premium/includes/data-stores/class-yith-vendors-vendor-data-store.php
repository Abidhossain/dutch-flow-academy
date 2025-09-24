<?php
/**
 * Vendor object data store class interface
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

abstract class YITH_Vendors_Vendor_Data_Store extends YITH_Vendors_Data_Store {

	/**
	 * Term related to this object.
	 * Set it for backward compatibility.
	 *
	 * @since 4.0.0
	 * @var null | WP_Term
	 */
	protected $term = null;

	/**
	 * Data type
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $object_type = 'vendor';

	/**
	 * Default vendor term data
	 *
	 * @var array
	 */
	protected $data = array(
		// Term data.
		'name'        => '',
		'slug'        => '',
		'description' => '',
		'count'       => 0,
	);

	/**
	 * Default vendor term meta data
	 *
	 * @var array
	 */
	protected $meta_data = array(
		// Term meta data.
		'status'          => 'disabled',
		'commission_type' => 'default',
		'commission'      => 0,
	);

	/**
	 * Sanitize a value before set
	 *
	 * @since  4.0.0
	 * @param mixed  $value The value to sanitize.
	 * @param string $key   The data key.
	 * @return mixed
	 */
	protected function sanitize_data_value( $value, $key ) {
		$value = parent::sanitize_data_value( $value, $key );

		$skip_wc_clean_for = apply_filters( 'yith_wcmv_skip_wc_clean_for_fields_array', array( 'description', 'shipping_policy', 'shipping_refund_policy' ) );
		if ( ! in_array( $key, $skip_wc_clean_for, true ) ) {
			$value = wc_clean( $value );
		}

		return $value;
	}

	/**
	 * Method to create a new record
	 *
	 * @throws Exception Error on vendor creation.
	 */
	protected function create() {

		// Double check vendor doesn't exist.
		if ( ! empty( $this->get_id() ) ) {
			throw new Exception( __( 'Vendor already exists!', 'yith-woocommerce-product-vendors' ) );
		}

		// Make sure at least vendor name data is set.
		$term_name = $this->get_data( 'name' );
		if ( empty( $term_name ) ) {
			throw new Exception( __( 'Impossible to create a vendor without a specified term name.', 'yith-woocommerce-product-vendors' ) );
		}

		// Create the term first!
		$term = wp_insert_term(
			$term_name,
			YITH_Vendors_Taxonomy::TAXONOMY_NAME,
			array(
				'slug'        => $this->get_data( 'slug' ),
				'description' => $this->get_data( 'description' ),
			)
		);

		if ( is_wp_error( $term ) ) {
			throw new Exception( $term->get_error_message() );
		}

		$this->set_id( $term['term_id'] );
		// Set registration date.
		$this->set_meta_data( 'registration_date', current_time( 'mysql' ) );
		$this->set_meta_data( 'registration_date_gmt', current_time( 'mysql', 1 ) );

		// Force status disabled if no owner is set.
		if ( empty( $this->get_meta( 'owner' ) ) ) {
			$this->set_meta( 'status', 'disabled' );
		}

		// Save meta data!
		foreach ( $this->changes as $key => $value ) {
			if ( isset( $this->term->$key ) || empty( $value ) ) {
				continue;
			}
			update_term_meta( $this->get_id(), $key, $value );
		}

		$this->apply_changes();
		YITH_Vendors_Capabilities::set_vendor_capabilities( $this );

		// Send email.
		WC()->mailer();
		do_action( 'yith_wcmv_vendor_created', $this, $this->get_owner() );

		// Send Email notification to new vendor if already enabled.
		if ( $this->has_status( 'enabled' ) ) {
			do_action( 'yith_wcmv_vendor_account_approved', $this->get_owner() );
		}
	}

	/**
	 * Method to read a record.
	 *
	 * @return void
	 * @throws Exception Error trading vendor object data.
	 */
	protected function read() {

		$this->term = get_term( $this->get_id(), YITH_Vendors_Taxonomy::TAXONOMY_NAME );
		if ( empty( $this->term ) || is_wp_error( $this->term ) ) {
			// translators: %s stand for the vendor ID.
			throw new Exception( sprintf( __( 'Invalid vendor ID: #%s', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		// Set term data for vendor.
		$this->data['name']        = $this->term->name;
		$this->data['slug']        = $this->term->slug;
		$this->data['description'] = $this->term->description;
		$this->data['count']       = $this->term->count;

		// Set data and meta_data array.
		$term_meta = get_term_meta( $this->id );
		if ( is_array( $term_meta ) ) {
			foreach ( $term_meta as $key => $value ) {
				$value                   = maybe_unserialize( array_shift( $value ) );
				$this->meta_data[ $key ] = $value;
			}
		}
	}

	/**
	 * Updates a record in the database.
	 *
	 * @throws Exception Error updating current object.
	 */
	protected function update() {

		if ( ! $this->is_valid() ) {
			// translators: %d stand for the vendor ID.
			throw new Exception( sprintf( __( 'You are trying to update a vendor that doesn\'t exists! Vendor ID #%d', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		if ( empty( $this->changes ) ) {
			// translators: %d stand for the vendor ID.
			throw new Exception( sprintf( __( 'No data to update for vendor ID #%d', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		// If an owner is not set, disable enable selling cap.
		if ( empty( $this->get_meta( 'owner' ) ) ) {
			$this->set_meta( 'status', 'disabled' );
		}

		// Check if we need to update vendor capabilities. User array_key_exists since the value could be also null.
		$update_vendor_capabilities = apply_filters( 'yith_wcmv_update_vendor_capabilities_on_save', ( array_key_exists( 'owner', $this->changes ) || array_key_exists( 'skip_review', $this->changes ) ), $this );
		// Handle owner caps.
		( array_key_exists( 'owner', $this->changes ) && isset( $this->meta_data['owner'] ) ) && YITH_Vendors_Capabilities::remove_vendor_capabilities_for_user( $this->meta_data['owner'] );

		// Save the property to change in the term.
		$term_properties = array();
		foreach ( $this->changes as $key => $value ) {
			if ( isset( $this->term->$key ) ) {
				$term_properties[ $key ] = $value;
			} else {
				// Delete meta if value is empty string or null.
				if ( is_null( $value ) || '' === $value ) {
					delete_term_meta( $this->get_id(), $key );
				} else {
					update_term_meta( $this->get_id(), $key, $value );
				}
			}
		}

		// Save the term data.
		if ( ! empty( $term_properties ) ) {
			$res = wp_update_term( $this->get_id(), YITH_Vendors_Taxonomy::TAXONOMY_NAME, $term_properties );
			if ( is_wp_error( $res ) ) {
				// translators: %d stand for the vendor ID.
				throw new Exception( sprintf( __( 'Error updating term object for vendor ID #%d', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
			}
		}

		$this->apply_changes();
		$this->empty_cache();
		// Update vendor capabilities conditionally.
		$update_vendor_capabilities && YITH_Vendors_Capabilities::set_vendor_capabilities( $this );

		do_action( 'yith_wcmv_vendor_updated', $this );
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @throws Exception Error deleting current object.
	 */
	public function delete() {

		if ( ! $this->is_valid() ) {
			// translators: %d stand for the vendor ID.
			throw new Exception( sprintf( __( 'You are trying to delete a vendor that doesn\'t exists! Vendor ID #%d', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		$this->empty_cache();

		// Set vendor's products to draft.
		if ( apply_filters( 'yith_wcmv_set_product_to_orphan_on_vendor_delete', true ) ) {
			foreach ( $this->get_products() as $product_id ) {
				wp_update_post(
					array(
						'ID'          => $product_id,
						'post_status' => 'draft',
					)
				);
			}
		}

		// Remove associated users capabilities.
		YITH_Vendors_Capabilities::remove_vendor_capabilities( $this );

		if ( ! wp_delete_term( $this->get_id(), YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ) {
			// translators: %d stand for the vendor ID.
			throw new Exception( sprintf( __( 'An error occurred trying to delete vendor ID #%d', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		$this->set_id( 0 );
		YITH_Vendors_Factory::unset( $this->get_id() );

		do_action( 'yith_wcmv_vendor_deleted', $this );
	}

	/**
	 * Check if given meta exists for current object
	 *
	 * @since  4.0.0
	 * @param string $key The meta key to check.
	 * @return boolean
	 */
	public function meta_exists( $key ) {
		return isset( $this->meta_data[ $key ] ) && metadata_exists( 'term', $this->get_id(), $key );
	}

	/**
	 * Empty current object cache
	 *
	 * @since  4.0.0
	 * @return void
	 */
	public function empty_cache() {
		global $yith_wcmv_cache;
		$yith_wcmv_cache->delete_vendor_cache( $this->get_id() );
		do_action( 'yith_wcmv_empty_vendor_object_cache', $this->get_id() );
	}
}
