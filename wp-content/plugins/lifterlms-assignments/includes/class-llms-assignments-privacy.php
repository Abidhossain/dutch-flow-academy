<?php
/**
 * Privacy exporters and erasers for Assignment data
 *
 * @package LifterLMS_Assignments/Classes
 * @since    1.0.0-beta.6
 * @version  1.0.0-beta.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Privacy exporters and erasers for Assignment data
 */
class LLMS_Assignments_Privacy extends LLMS_Abstract_Privacy {

	/**
	 * Constructor
	 *
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function __construct() {

		parent::__construct( __( 'LifterLMS Social Learning', 'lifterlms-assignments' ) );

		$this->add_exporter( 'lifterlms-assignments-data', __( 'Assignment Data', 'lifterlms-assignments' ), array( __CLASS__, 'export_data' ) );

		$this->add_eraser( 'lifterlms-sl-story-data', __( 'Story Data', 'lifterlms-assignments' ), array( __CLASS__, 'erase_data' ) );
	}

	/**
	 * Erase student assignment submission data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for.
	 * @param    int    $page           process page number.
	 * @return   array
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public static function erase_data( $email_address, $page ) {

		$ret = array(
			'messages'       => array(),
			'done'           => true,
			'items_removed'  => false,
			'items_retained' => false,
		);

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$enabled = llms_parse_bool( get_option( 'llms_erasure_request_removes_lms_data', 'no' ) );
		$query   = self::get_submissions( $student, $page );

		foreach ( $query->get_submissions() as $submission ) {

			if ( apply_filters( 'llms_privacy_erase_assignment_data', $enabled, $submission ) ) {

				/* Translators: %d assignment submission id. */
				$ret['messages'][]    = sprintf( __( 'Assignment submission #%d removed.', 'lifterlms-assignments' ), $submission->get( 'id' ) );
				$ret['items_removed'] = true;

				$submission->delete();

			} else {

				/* Translators: %d assignment submission id. */
				$ret['messages'][]     = sprintf( __( 'Assignment submission #%d retained.', 'lifterlms-assignments' ), $submission->get( 'id' ) );
				$ret['items_retained'] = true;

			}
		}

		$ret['done'] = $query->has_results() ? $query->is_last_page() : true;

		return $ret;
	}

	/**
	 * Export assignment submission data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for.
	 * @param    int    $page           process page number.
	 * @return   array
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public static function export_data( $email_address, $page ) {

		$ret = array(
			'data' => array(),
			'done' => true,
		);

		$student = self::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$query = self::get_submissions( $student, $page );

		if ( $query->has_results() ) {

			$group_label = __( 'Assignment Submissions', 'lifterlms-assignments' );
			foreach ( $query->get_submissions() as $sub ) {

				$ret['data'][] = array(
					'group_id'    => 'lifterlms_assignments',
					'group_label' => $group_label,
					'item_id'     => sprintf( 'assignment-%d', $sub->get( 'id' ) ),
					'data'        => self::get_submission_data( $sub ),
				);

			}

			$ret['done'] = $query->is_last_page();

		}

		return $ret;
	}

	/**
	 * Compile an array of data passed to the exporter for a single submission
	 *
	 * @param    obj $submission  LLMS_Assignment_Submission.
	 * @return   array
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	private static function get_submission_data( $submission ) {

		$data = array();

		$data[] = array(
			'name'  => __( 'Submission ID', 'lifterlms-assignments' ),
			'value' => $submission->get( 'id' ),
		);

		$assignment = $submission->get_assignment();
		if ( $assignment ) {

			$data[] = array(
				'name'  => __( 'Assignment Title', 'lifterlms-assignments' ),
				'value' => $assignment->get( 'title' ),
			);

			$lesson = $assignment->get_lesson();
			if ( $lesson ) {

				$course = $lesson->get_course();
				if ( $course ) {
					$data[] = array(
						'name'  => __( 'Course Title', 'lifterlms-assignments' ),
						'value' => $course->get( 'title' ),
					);
				}

				$data[] = array(
					'name'  => __( 'Lesson Title', 'lifterlms-assignments' ),
					'value' => $lesson->get( 'title' ),
				);

			}
		}

		$submitted = $submission->get( 'submitted' );
		$data[]    = array(
			'name'  => __( 'Submitted Date', 'lifterlms-assignments' ),
			'value' => $submitted ? $submitted : '&ndash;',
		);

		$data[] = array(
			'name'  => __( 'Status', 'lifterlms-assignments' ),
			'value' => $submission->get_l10n_status(),
		);

		$grade  = $submission->get( 'grade' );
		$data[] = array(
			'name'  => __( 'Grade', 'lifterlms-assignments' ),
			'value' => is_numeric( $grade ) ? $grade . '%' : '&ndash;',
		);

		$remarks = $submission->get( 'remarks' );
		$data[]  = array(
			'name'  => __( 'Remarks', 'lifterlms-assignments' ),
			'value' => $remarks ? wpautop( $remarks ) : '&ndash;',
		);

		return $data;
	}

	/**
	 * Retrieve assignment submissions for a student
	 *
	 * @param    obj $student  LLMS_Student.
	 * @param    int $page     page number.
	 * @return   obj
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	private static function get_submissions( $student, $page ) {

		$query = new LLMS_Query_Assignments_Submission(
			array(
				'user_id'  => $student->get( 'id' ),
				'page'     => $page,
				'per_page' => 500,
			)
		);

		return $query;
	}
}

function llms_assignments_load_privacy() {
	return new LLMS_Assignments_Privacy();
}
add_action( 'init', 'llms_assignments_load_privacy' );
