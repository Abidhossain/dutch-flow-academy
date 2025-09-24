<?php
/**
 * Announcement object data store class abstract
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

abstract class YITH_Vendors_Announcement_Data_Store extends YITH_Vendors_Data_Store {

	/**
	 * Data type
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $object_type = YITH_Vendors_Announcements::POST_TYPE;

	/**
	 * Default vendor term data
	 *
	 * @var array
	 */
	protected $data = array(
		// Post data.
		'title'   => '',
		'content' => '',
		'date'    => '0000-00-00 00:00:00',
	);


	/**
	 * Method to create a new record
	 *
	 * @throws Exception Error on announcement creation.
	 */
	protected function create() {
		// Double check announcement doesn't exist.
		if ( ! empty( $this->get_id() ) ) {
			throw new Exception( __( 'Announcement already exists!', 'yith-woocommerce-product-vendors' ) );
		}

		$announcement_id = wp_insert_post(
			array(
				'post_title'   => $this->get_data( 'title' ),
				'post_content' => $this->get_data( 'content' ),
				'post_type'    => $this->object_type,
				'post_status'  => 'publish', // Force post status publish.
			)
		);

		if ( is_wp_error( $announcement_id ) ) {
			throw new Exception( sprintf( 'An error occurred create announcement. %s', $announcement_id->get_error_message() ) );
		}

		$this->set_id( $announcement_id );
		// Set active by default.
		$this->set_meta( 'active', 'yes' );
		// Save meta data!
		foreach ( $this->changes as $key => $value ) {
			if ( isset( $this->data[ $key ] ) || empty( $value ) ) {
				continue;
			}
			update_post_meta( $this->get_id(), '_' . $key, $value );
		}

		$this->apply_changes();
		$this->empty_cache();
	}

	/**
	 * Method to read a record.
	 *
	 * @return void
	 * @throws Exception Error reading announcement object data.
	 */
	protected function read() {
		$post = get_post( $this->get_id() );
		if ( empty( $post ) || $this->object_type !== $post->post_type ) {
			// translators: %s stand for the announcement ID.
			throw new Exception( sprintf( __( 'Invalid announcement ID: #%s', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		// Set term data for vendor.
		$this->data['title']   = $post->post_title;
		$this->data['content'] = $post->post_content;
		$this->data['date']    = $post->post_modified;

		// Set data and meta_data array.
		$post_meta = get_post_meta( $this->get_id() );
		if ( is_array( $post_meta ) ) {
			foreach ( $post_meta as $key => $value ) {
				// Remove _ from private meta.
				if ( 0 === strpos( $key, '_' ) ) {
					$key = substr( $key, 1 );
				}
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
		if ( ! $this->exists() ) {
			// translators: %d stand for the announcement ID.
			throw new Exception( sprintf( __( 'You\'re trying to update an announcement that doesn\'t exist! Announcement ID #%d.', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		if ( empty( $this->changes ) ) {
			// translators: %d stand for the announcement ID.
			throw new Exception( sprintf( __( 'No data to update for announcement ID #%d', 'yith-woocommerce-product-vendors' ), $this->get_id() ) );
		}

		// Maybe update post.
		if ( isset( $this->changes['title'] ) || isset( $this->changes['content'] ) ) {
			$res = wp_update_post(
				array(
					'ID'           => $this->get_id(),
					'post_title'   => $this->get_data( 'title' ),
					'post_content' => $this->get_data( 'content' ),
					'post_type'    => $this->object_type, // Always force post type.
					'post_status'  => 'publish', // Force post status publish.
				)
			);
		}

		if ( is_wp_error( $res ) ) {
			// translators: %1$s stand for the announcement ID, %2$s is the error message.
			throw new Exception( sprintf( __( 'An error occurred updating announcement ID #%1$s. %2$s.', 'yith-woocommerce-product-vendors' ), $this->get_id(), $res->get_error_message() ) );
		}

		// Save the property to change in the announcement.
		foreach ( $this->changes as $key => $value ) {
			if ( ! isset( $this->data[ $key ] ) ) {
				// Delete meta if value is empty string or null.
				if ( is_null( $value ) || '' === $value ) {
					delete_post_meta( $this->get_id(), $key );
				} else {
					update_post_meta( $this->get_id(), $key, $value );
				}
			}
		}

		$this->apply_changes();
		$this->empty_cache();
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @throws Exception Error deleting current object.
	 */
	public function delete() {}

	/**
	 * Duplicate a record from the database.
	 *
	 * @return integer The new announcement ID.
	 * @throws Exception Error deleting current object.
	 */
	public function duplicate() {

		if ( ! $this->exists() ) {
			throw new Exception( __( 'Not possible to duplicate announcement. No announcement found.', 'yith-woocommerce-product-vendors' ) );
		}

		$new_announcement_id = wp_insert_post(
			array(
				'post_type'    => $this->object_type, // Always force post type.
				'post_status'  => 'draft', // Force post status draft.
				'post_title'   => $this->get_data( 'title' ),
				'post_content' => $this->get_data( 'content' ),
			)
		);

		if ( is_wp_error( $new_announcement_id ) ) {
			// translators: %s is the error message.
			throw new Exception( sprintf( __( 'Error duplicating announcement. %s.', 'yith-woocommerce-product-vendors' ), $new_announcement_id->get_error_message() ) );
		}
		// Clone all meta data.
		foreach ( $this->meta_data as $key => $value ) {
			update_post_meta( $new_announcement_id, $key, $value );
		}

		return $new_announcement_id;
	}

	/**
	 * Check if given meta exists for current object
	 *
	 * @since  4.0.0
	 * @param string $key The meta key to check.
	 * @return boolean
	 */
	public function meta_exists( $key ) {
		return isset( $this->meta_data[ $key ] ) && metadata_exists( 'post', $this->get_id(), $key );
	}

	/**
	 * Empty current object cache
	 *
	 * @since  4.0.0
	 * @return void
	 */
	public function empty_cache() {
		global $yith_wcmv_cache;
		$yith_wcmv_cache->flush( 'announcements' );
	}
}
