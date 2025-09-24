<?php
/**
 * Query LifterLMS Students for a given course / membership
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.2
 * @version 1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query LifterLMS Students for a given course / membership
 *
 * @arg  $assignment_id (int|array)  Query by Assignment WP post ID (assignments with an array of ids)
 * @arg  $user_id       (int|array)  Query by WP User ID (locate by multiple users with an array of ids)
 *
 * @arg  $page          (int)        Get results by page
 * @arg  $per_page      (int)        Number of results per page (default: 25)
 * @arg  $sort          (array)      Define query sorting options [id,user_id,assignment_id,created,updated,grade,status]
 *
 * @example
 *       $query = new LLMS_Query_Assignments_Submission( array(
 *           'user_id' => 1234,
 *           'assignment_id' => 5678,
 *       ) );
 */
class LLMS_Query_Assignments_Submission extends LLMS_Database_Query {

	/**
	 * Identify the extending query
	 *
	 * @var  string
	 */
	protected $id = 'assignment_submission';

	/**
	 * Retrieve default arguments for a student query
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	protected function get_default_args() {

		$args = array(
			'user_id'        => array(),
			'assignment_id'  => array(),
			'sort'           => array(
				'created' => 'DESC',
				'id'      => 'ASC',
			),
			'status'         => array(),
			'status_exclude' => array(),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		return apply_filters( $this->get_filter( 'default_args' ), $args );

	}

	/**
	 * Retrieve an array of LLMS_Assignment_Submission_Submissions for the given result set returned by the query
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_submissions() {

		$attempts = array();
		$results  = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$attempts[] = new LLMS_Assignment_Submission( $result->id );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $attempts;
		}

		return apply_filters( $this->get_filter( 'get_submissions' ), $attempts );

	}

	/**
	 * Parses data passed to $statuses
	 * Convert strings to array and ensure resulting array contains only valid statuses
	 * If no valid statuses, returns to the default
	 *
	 * @return   void
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	protected function parse_args() {

		// sanitize post & user ids.
		foreach ( array( 'user_id', 'assignment_id' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		// validate status args.
		$valid_statuses = array_keys( llms_get_assignment_submission_statuses() );
		foreach ( array( 'status', 'status_exclude' ) as $key ) {

			// allow single statuses to be passed in as a string.
			if ( is_string( $this->arguments[ $key ] ) ) {
				$this->arguments[ $key ] = array( $this->arguments[ $key ] );
			}

			// ensure submitted statuses are valid.
			if ( $this->arguments[ $key ] ) {
				$this->arguments[ $key ] = array_intersect( $valid_statuses, $this->arguments[ $key ] );
			}
		}

	}

	/**
	 * Prepare the SQL for the query.
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.3.0 Renamed preprare_query() to prepare_query().
	 *
	 * @return string
	 */
	protected function prepare_query() {

		global $wpdb;

		return "SELECT SQL_CALC_FOUND_ROWS id
				FROM {$wpdb->prefix}lifterlms_assignments_submissions
				{$this->sql_where()}
				{$this->sql_orderby()}
				{$this->sql_limit()};";

	}

	/**
	 * SQL "where" clause for the query
	 *
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.6
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1';

		foreach ( array( 'assignment_id', 'user_id' ) as $key ) {
			$ids = $this->get( $key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql     .= " AND {$key} IN ({$prepared})";
			}
		}

		// add numeric lookups.
		foreach ( array( 'attempt' ) as $key ) {

			$val = $this->get( $key );
			if ( '' !== $val ) {
				$sql .= " AND {$key} = {$val}";
			}
		}

		$status = $this->get( 'status' );
		if ( $status ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status ) );
			$sql     .= " AND status IN ({$prepared})";
		}

		$status_exclude = $this->get( 'status_exclude' );
		if ( $status_exclude ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status_exclude ) );
			$sql     .= " AND status NOT IN ({$prepared})";
		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
