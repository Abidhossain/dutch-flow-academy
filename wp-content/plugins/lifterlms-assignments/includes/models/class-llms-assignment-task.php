<?php
/**
 * LifterLMS Assignment Task Model
 *
 * @package LifterLMS_Assignments/Models
 * @since    1.0.0-beta.1
 * @version  1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Assignment Task Model
 *
 * @since    1.0.0-beta.1
 * @version  1.0.0-beta.1
 */
class LLMS_Assignment_Task {

	/**
	 * Meta key prefix
	 *
	 * @var  string
	 */
	protected $prefix = '_llms_task_';

	/**
	 * Task ID
	 *
	 * @var  string
	 */
	private $id = null;

	/**
	 * Array of task data
	 *
	 * @var  array
	 */
	private $data = array();

	/**
	 * Instance of the LLMS_Assignment
	 *
	 * @var  obj
	 */
	private $assignment = null;

	/**
	 * ID of the LLMS_Assignment
	 *
	 * @var  null
	 */
	private $assignment_id = null;

	/**
	 * Constructor
	 *
	 * @param    int          $assignment_id  WP Post ID of the choice's parent LLMS_Assignment.
	 * @param    array|string $data_or_id   array of choice data or the choice ID string.
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function __construct( $assignment_id, $data_or_id = array() ) {

		// ensure the assignment is valid.
		if ( $this->set_assignment( $assignment_id ) ) {

			// if an ID is passed in, load the assignment data from post meta.
			if ( ! is_array( $data_or_id ) ) {
				$data_or_id = str_replace( $this->prefix, '', $data_or_id );
				$data_or_id = get_post_meta( $this->assignment_id, $this->prefix . $data_or_id, true );
			}

			// hydrate with postmeta data or array of data passed in.
			if ( is_array( $data_or_id ) && isset( $data_or_id['id'] ) ) {
				$this->hydrate( $data_or_id );
			}
		}

	}

	/**
	 * Creates a new assignment
	 *
	 * @param    array $data  assignment data array.
	 * @return   self
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function create( $data ) {

		$this->id = uniqid();
		return $this->update( $data )->save();

	}

	/**
	 * Delete a choice
	 *
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function delete() {
		return delete_post_meta( $this->assignment_id, $this->prefix . $this->id );
	}

	/**
	 * Determine if the choice that's been requested actually exists
	 *
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function exists() {
		return ( $this->id );
	}

	/**
	 * Retrieve a piece of choice data by key
	 *
	 * @param    string $key      name of the data to be retrieved.
	 * @param    mixed  $default  default value if key isn't set.
	 * @return   mixed
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get( $key, $default = '' ) {

		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		return $default;

	}

	/**
	 * Retrieve all of the choice data as an array
	 *
	 * @return   array
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Retrieve an instance of an LLMS_Assignment for assignments parent
	 *
	 * @return   obj
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_assignment() {
		return $this->assignment;
	}

	/**
	 * Retrieve the assignment ID for the given choice
	 *
	 * @return   int
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_assignment_id() {
		return $this->assignment_id;
	}

	/**
	 * Setup the id and data variables
	 *
	 * @param    array $data  array of assignment data.
	 * @return   void
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	private function hydrate( $data ) {
		$this->id         = $data['id'];
		$this->data['id'] = $this->id;
		$this->update( $data );
	}

	/**
	 * Save $this->data to the postmeta table
	 *
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function save() {

		$this->data['id'] = $this->id; // always ensure the ID is set when saving data.
		$update           = update_post_meta( $this->assignment_id, $this->prefix . $this->id, $this->data );

		return ( $update );

	}

	/**
	 * Set a piece of data by key
	 *
	 * @param    string $key  name of the key to set.
	 * @param    mixed  $val  value to set.
	 * @return   self
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function set( $key, $val ) {

		// dont set the ID.
		if ( 'id' === $key ) {
			return $this;
		}

		if ( is_array( $val ) ) {
			$val = array_map( 'sanitize_text_field', $val );
		} else {
			$val = wp_kses_post( $val );
		}

		$this->data[ $key ] = $val;
		return $this;

	}

	/**
	 * Sets assignment-related data from constructor
	 *
	 * @param    int $id  WP Post ID of the assignment's parent assignment.
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function set_assignment( $id ) {

		$assignment = llms_get_post( $id );
		if ( $assignment && is_a( $assignment, 'LLMS_Assignment' ) ) {
			$this->assignment    = $assignment;
			$this->assignment_id = $id;
			return true;
		}

		return false;

	}

	/**
	 * Update multiple data by key=>val pairs
	 *
	 * @param    array $data  array of data to set.
	 * @return   self
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function update( $data = array() ) {

		foreach ( $data as $key => $val ) {
			$this->set( $key, $val );
		}
		return $this;

	}

}
