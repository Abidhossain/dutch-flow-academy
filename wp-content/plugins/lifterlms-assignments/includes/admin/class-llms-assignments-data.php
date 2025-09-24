<?php
/**
 * Query data about an assignment
 *
 * @package LifterLMS_Assignments/Admin/Classes
 *
 * @since 1.0.0-beta.2
 * @version 1.1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query data about an assignment
 *
 * @since 1.0.0-beta.2
 * @since 1.1.2 Use the LLMS_Abstract_Post_Data abstract.
 */
class LLMS_Assignments_Data extends LLMS_Abstract_Post_Data {

	/**
	 * Assignment object
	 *
	 * @since 1.0.0-beta.2
	 * @deprecated 1.1.2 Use $this->post instead.
	 *
	 * @var LLMS_Assignment
	 */
	public $assignment;

	/**
	 * WP_Post ID of the assignment
	 *
	 * @since 1.0.0-beta.2
	 * @deprecated 1.1.2 Use $this->post_id instead.
	 *
	 * @var int
	 */
	public $assignment_id;

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.2 Call parent constructor.
	 *
	 * @param int $assignment_id  WP Post ID of the Assignment.
	 */
	public function __construct( $assignment_id ) {

		parent::__construct( $assignment_id );
		$this->assignment_id = $this->post_id;
		$this->assignment    = $this->post;

	}

	/**
	 * Retrieve avg grade of assignment submissions within the period
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.2 Use $this->post_id instead of $this->assignment_id.
	 *
	 * @param    string $period  date period [current|previous].
	 * @return   int
	 */
	public function get_average_grade( $period = 'current' ) {

		global $wpdb;

		$grade = $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT ROUND( AVG( grade ), 3 )
			FROM {$wpdb->prefix}lifterlms_assignments_submissions
			WHERE assignment_id = %d
			  AND updated BETWEEN %s AND %s
			  AND status IN ( 'pass', 'fail' )
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

		return $grade ? $grade : 0;

	}

	/**
	 * Retrieve the number assignments with a given status
	 *
	 * @since 1.0.0-beta.5
	 * @since 1.1.2 Use $this->post_id instead of $this->assignment_id.
	 *
	 * @param    string $status  status name.
	 * @param    string $period  date period [current|previous].
	 * @return   int
	 */
	private function get_count_by_status( $status, $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( id )
			FROM {$wpdb->prefix}lifterlms_assignments_submissions
			WHERE assignment_id = %d
			  AND status = %s
			  AND updated BETWEEN %s AND %s
			",
				$this->post_id,
				$status,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

	/**
	 * Retrieve # of assignment fails within the period
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @param    string $period  date period [current|previous].
	 * @return   int
	 */
	public function get_fail_count( $period = 'current' ) {
		return $this->get_count_by_status( 'fail', $period );
	}

	/**
	 * Retrieve # of assignment passes within the period
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @param    string $period  date period [current|previous].
	 * @return   int
	 */
	public function get_pass_count( $period = 'current' ) {
		return $this->get_count_by_status( 'pass', $period );
	}

	/**
	 * Retrieve # of assignments which are pending within the period
	 *
	 * @since 1.0.0-beta.5
	 *
	 * @param    string $period  date period [current|previous].
	 * @return   int
	 */
	public function get_pending_count( $period = 'current' ) {
		return $this->get_count_by_status( 'pending', $period );
	}

	/**
	 * Retrieve recent LLMS_User_Postmeta for the assignment
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.2 Add $args parameter & use $this->post_id instead of $this->assignment_id
	 *
	 * @param array $args Optional arguments to pass to LLMS_Query_Assignments_Submission.
	 * @return   array
	 */
	public function recent_events( $args = array() ) {

		$query = new LLMS_Query_Assignments_Submission(
			wp_parse_args(
				$args,
				array(
					'per_page'      => 10,
					'assignment_id' => $this->post_id,
					'sort'          => array(
						'updated' => 'DESC',
						'id'      => 'ASC',
					),
				)
			)
		);

		return $query->get_submissions();

	}

	/**
	 * Retrieve # of assignment submissions within the period
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.2 Use $this->post_id instead of $this->assignment_id.
	 *
	 * @param    string $period  date period [current|previous].
	 * @return   int
	 */
	public function get_submission_count( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( id )
			FROM {$wpdb->prefix}lifterlms_assignments_submissions
			WHERE assignment_id = %d
			  AND updated BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

}
