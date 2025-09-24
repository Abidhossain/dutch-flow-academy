<?php
/**
 * Plugin commission data store abstract
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

abstract class YITH_Vendors_Commission_Data_Store extends YITH_Vendors_Data_Store {

	/**
	 * Data type
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $object_type = 'commission';

	/**
	 * Object table columns type
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $columns = array(
		'order_id'         => '%d',
		'user_id'          => '%d',
		'vendor_id'        => '%d',
		'product_id'       => '%d',
		'line_item_id'     => '%d',
		'line_total'       => '%f',
		'rate'             => '%f',
		'amount'           => '%f',
		'amount_refunded'  => '%f',
		'status'           => '%s',
		'type'             => '%s',
		'created_date'     => '%s',
		'created_date_gmt' => '%s',
		'last_edit'        => '%s',
		'last_edit_gmt'    => '%s',
	);

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $data = array(
		'order_id'         => 0,
		'user_id'          => 0,
		'vendor_id'        => 0,
		'product_id'       => 0,
		'line_item_id'     => 0,
		'line_total'       => 0,
		'rate'             => 0,
		'amount'           => 0,
		'amount_refunded'  => 0,
		'status'           => 'pending',
		'type'             => 'product',
		'created_date'     => '0000-00-00 00:00:00',
		'created_date_gmt' => '0000-00-00 00:00:00',
		'last_edit'        => '0000-00-00 00:00:00',
		'last_edit_gmt'    => '0000-00-00 00:00:00',
	);

	/**
	 * Method to create a new record
	 *
	 * @throws Exception Exception on create process errors.
	 */
	protected function create() {
		global $wpdb;

		// Double check commission doesn't exist.
		if ( ! empty( $this->get_id() ) ) {
			// translators: %d stand for the commission ID.
			throw new Exception( sprintf( __( 'Create commission failed. Commission #%d already exists!', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		if ( empty( $this->changes ) ) {
			throw new Exception( __( 'Create commission failed. Empty commission data!', 'yith-woocommerce-product-vendors' ) );
		}

		// Set commissions times.
		$time     = current_time( 'mysql' );
		$time_gmt = current_time( 'mysql', 1 );
		$this->set_data( 'created_date', $time );
		$this->set_data( 'created_date_gmt', $time_gmt );
		$this->set_data( 'last_edit', $time );
		$this->set_data( 'last_edit_gmt', $time_gmt );

		// Format data.
		$data   = array_intersect_key( $this->changes, $this->data ); // Validate data columns.
		$data   = array_merge( $this->data, $data ); // Order data columns.
		$format = array_merge( $data, $this->columns ); // Format data columns.

		if ( ! $wpdb->insert( $wpdb->commissions, (array) $data, (array) $format ) ) { // phpcs:ignore
			// Translators: %s is an array of data useful for debug purpose.
			throw new Exception( sprintf( __( 'Create commission failed. Commission data: %s', 'yith-woocommerce-product-vendors' ), print_r( $this->data, true ) ) ); // phpcs:ignore
		}

		$this->set_id( $wpdb->insert_id );
		$this->apply_changes();
		$this->empty_cache();
	}

	/**
	 * Method to read a record.
	 *
	 * @return void
	 * @throws Exception Error if commission was not found.
	 */
	protected function read() {
		global $wpdb;

		if ( $this->get_id() ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->commissions WHERE ID = %d", $this->get_id() ), ARRAY_A ); // phpcs:ignore
			if ( empty( $data ) ) {
				throw new Exception( __( 'Invalid commission.', 'yith-woocommerce-product-vendors' ) );
			}

			unset( $data['ID'] );
			$this->data = array_merge( $this->data, $data );
		}
	}

	/**
	 * Updates a record in the database.
	 *
	 * @throws Exception Exception on update process errors.
	 */
	protected function update() {
		global $wpdb;

		// Double check commission doesn't exist.
		if ( empty( $this->get_id() ) ) {
			throw new Exception( __( 'Update commission failed. Commission does not exists!', 'yith-woocommerce-product-vendors' ) );
		}

		if ( ! empty( $this->changes ) ) {

			$this->set_data( 'last_edit', current_time( 'mysql' ) );
			$this->set_data( 'last_edit_gmt', current_time( 'mysql', 1 ) );

			$changes = array_intersect_key( $this->changes, $this->columns ); // Validate data columns.
			$format  = array_merge( $changes, array_intersect_key( $this->columns, $changes ) ); // Format data columns.

			if ( ! $wpdb->update( $wpdb->commissions, $changes, array( 'ID' => $this->get_id() ), $format, array( 'ID' => '%d' ) ) ) { // phpcs:ignore
				// translators: %d stand for the commission ID, %s is an array of data to update.
				throw new Exception( sprintf( __( 'Update commission #%d failed. Commission data: %s', 'yith-woocommerce-product-vendors' ), $this->get_id(), print_r( $changes, true ) ) ); // phpcs:ignore
			}

			$this->apply_changes();
			$this->empty_cache();
		}
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @throws Exception Exception on delete process errors.
	 */
	public function delete() {
		global $wpdb;

		if ( ! $this->get_id() ) {
			throw new Exception( __( 'Delete commission failed: no commission ID to delete.', 'yith-woocommerce-product-vendors' ) );
		}

		if ( ! $wpdb->delete( $wpdb->commissions, array( 'ID' => $this->get_id() ) ) ) { // phpcs:ignore
			// translators: %d stand for the commission ID.
			throw new Exception( sprintf( __( 'Delete commission failed: error deleting commission ID #%d.', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		$this->set_id( 0 );
		YITH_Vendors_Commissions_Factory::unset( $this->get_id() );

		$this->empty_cache();
	}

	/**
	 * Add a note for the commission
	 *
	 * @since  4.0.0
	 * @author YITH
	 * @param string $note (Optional) A note to add to this commission.
	 * @return void
	 */
	public function add_note( $note = '' ) {
		global $wpdb;

		if ( empty( $note ) || ! $this->id ) { // Avoid add note to empty commission.
			return;
		}

		$wpdb->insert(
			$wpdb->commissions_notes,
			array(
				'commission_id' => $this->id,
				'description'   => trim( $note ),
				'note_date'     => date( 'Y-m-d H:i:s' ),
			)
		);
	}

	/**
	 * Get all notes of commission
	 *
	 * @since  4.0.0
	 * @return array
	 */
	public function get_notes() {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->commissions_notes WHERE commission_id = %d ORDER BY note_date DESC", $this->get_id() ) ); // phpcs:ignore
	}

	/**
	 * Empty current object cache
	 *
	 * @since  4.0.0
	 * @return void
	 */
	public function empty_cache() {
		global $yith_wcmv_cache;
		$yith_wcmv_cache->flush( 'commissions' );
	}
}
