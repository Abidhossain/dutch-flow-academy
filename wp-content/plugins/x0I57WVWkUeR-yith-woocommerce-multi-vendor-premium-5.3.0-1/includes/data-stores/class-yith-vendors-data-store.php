<?php
/**
 * Plugin data store abstract
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

abstract class YITH_Vendors_Data_Store {

	/**
	 * ID for this object.
	 *
	 * @since 4.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Data type
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $object_type = '';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Meta data for this object. Name value pairs (name + default value).
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $meta_data = array();

	/**
	 * A collection of changed data keys for object.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $changes = array();

	/**
	 * Returns the unique ID for this object.
	 *
	 * @since  4.0.0
	 * @return integer
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Detect if object ID exists
	 *
	 * @since  4.0.0
	 * @return boolean
	 */
	public function exists() {
		return ! empty( $this->get_id() );
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @since  4.0.0
	 * @param string $key     Name of data to get.
	 * @param string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	protected function get_data( $key, $context = 'view' ) {
		if ( ! array_key_exists( $key, $this->data ) ) {
			return null;
		}

		if ( isset( $this->changes[ $key ] ) ) {
			$value = $this->changes[ $key ];
		} else {
			$value = $this->data[ $key ];
		}

		if ( 'view' === $context ) {
			$value = apply_filters( "yith_wcmv_get_{$this->object_type}_data", $value, $key, $this );
		}

		return $value;
	}

	/**
	 * Get a meta data for object.
	 *
	 * @since  4.0.0
	 * @param string $key     Name of data to set.
	 * @param string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	public function get_meta_data( $key, $context = 'view' ) {
		if ( isset( $this->changes[ $key ] ) ) {
			$value = $this->changes[ $key ];
		} else {
			$value = isset( $this->meta_data[ $key ] ) ? $this->meta_data[ $key ] : '';
		}

		if ( 'view' === $context ) {

            $value = apply_filters( "yith_wcmv_get_{$this->object_type}_meta_data", $value, $key, $this );
		}

		return $value;
	}

	/**
	 * Alias for get_meta_data.
	 *
	 * @since  4.0.0
	 * @param string $key     Name of data to set.
	 * @param string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	public function get_meta( $key, $context = 'view' ) {
		return $this->get_meta_data( $key, $context );
	}

	/**
	 * Set ID.
	 *
	 * @since 4.0.0
	 * @param integer $id ID.
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set a data for a setter method.
	 *
	 * @since  4.0.0
	 * @param string $key   Name of data to set.
	 * @param string $value Value of data to set.
	 * @return void
	 */
	protected function set_data( $key, $value ) {
		if ( array_key_exists( $key, $this->data ) ) {
			$value                 = $this->sanitize_data_value( $value, $key );
			$this->changes[ $key ] = $value;
		}
	}

	/**
	 * Set a meta data for object.
	 *
	 * @since  4.0.0
	 * @param string $key   Name of data to set.
	 * @param mixed  $value Value of data to set.
	 * @return void
	 */
	public function set_meta_data( $key, $value ) {
		// Prevent set internal data.
		if ( array_key_exists( $key, $this->data ) ) {
			return;
		}

		$value                 = $this->sanitize_data_value( $value, $key );
		$this->changes[ $key ] = $value;
	}

	/**
	 * Alias for set_meta_data
	 *
	 * @since  4.0.0
	 * @param string $key   Name of data to set.
	 * @param mixed  $value Value of data to set.
	 * @return void
	 */
	public function set_meta( $key, $value ) {
		$this->set_meta_data( $key, $value );
	}

	/**
	 * Sets a date prop whilst handling formatting and datetime objects.
	 *
	 * @since 3.0.0
	 * @param string         $prop  Name of prop to set.
	 * @param string|integer $value Value of the prop.
	 * @param boolean        $gmt   Optional. Whether to use GMT timezone. Default false.
	 */
	protected function set_date_prop( $prop, $value, $gmt = false ) {
		if ( empty( $value ) ) {
			$this->set_meta_data( $prop, null );
			return;
		}

		$timezone = $gmt ? new DateTimeZone( 'UTC' ) : wp_timezone();

		if ( is_a( $value, 'WC_DateTime' ) ) {
			$datetime = $value;
		} elseif ( is_numeric( $value ) ) {
			// Timestamps are handled as UTC timestamps in all cases.
			$datetime = new WC_DateTime( "@{$value}", $timezone );
		} else {
			// Strings are defined in local WP timezone. Convert to UTC.
			if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
				$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
				$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
			} else {
				$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $value ) ) ) );
			}
			$datetime = new WC_DateTime( "@{$timestamp}", $timezone );
		}

		$value = $datetime->format( 'Y-m-d H:i:s' );
		// Prevent set internal data.
		if ( array_key_exists( $prop, $this->data ) ) {
			$this->set_data( $prop, $value );
		} else {
			$this->set_meta_data( $prop, $value );
		}
	}

	/**
	 * Sanitize a value before set
	 *
	 * @since  4.0.0
	 * @param mixed  $value The value to sanitize.
	 * @param string $key   The data key.
	 * @return mixed
	 */
	protected function sanitize_data_value( $value, $key ) {
		// Handle boolean.
		if ( is_bool( $value ) ) {
			$value = $value ? 'yes' : 'no';
		}

		return $value;
	}

	/**
	 * Check if given meta exists for current object
	 *
	 * @since  4.0.0
	 * @param string $key The meta key to check.
	 * @return boolean
	 */
	public function meta_exists( $key ) {
		return isset( $this->meta_data[ $key ] );
	}

	/**
	 * Apply changes to current object data.
	 *
	 * @since  4.0.0
	 * @return void
	 */
	protected function apply_changes() {
		foreach ( $this->changes as $key => $value ) {
			if ( isset( $this->data[ $key ] ) ) {
				$this->data[ $key ] = $value;
			} else {
				$this->meta_data[ $key ] = $value;
			}
		}

		$this->changes = array();
	}

	/**
	 * Save should create or update based on object existence.
	 *
	 * @since  4.0.0
	 * @return integer
	 */
	public function save() {

		/**
		 * Trigger action before saving to the DB. Allows you to adjust object props before save.
		 *
		 * @param YITH_Vendors_Data_Store $this The object being saved.
		 */
		do_action( 'yith_wcmv_before_' . $this->object_type . '_object_save', $this );

		if ( $this->get_id() ) {
			$this->update();
		} else {
			$this->create();
		}

		/**
		 * Trigger action after saving to the DB.
		 *
		 * @param YITH_Vendors_Data_Store $this The object being saved.
		 */
		do_action( 'yith_wcmv_after_' . $this->object_type . '_object_save', $this );

		return $this->get_id();
	}

	/**
	 * Method to create a new record
	 */
	protected function create() {}

	/**
	 * Method to read a record.
	 */
	protected function read() {}

	/**
	 * Updates a record in the database.
	 */
	protected function update() {}

	/**
	 * Deletes a record from the database.
	 */
	public function delete() {}
}
