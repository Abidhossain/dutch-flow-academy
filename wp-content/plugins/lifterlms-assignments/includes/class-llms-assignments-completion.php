<?php
/**
 * Handle the completion of assignments & progression of associated lessons
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.1.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle the completion of assignments & progression of associated lessons.
 *
 * @since 1.0.0-beta.1
 * @since 1.1.5 Exit after redirecting on submission deletion.
 */
class LLMS_Assignments_Completion {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.6 Unknown.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'handle_review_submission' ) );

		add_action( 'llms_assignment_graded', array( $this, 'complete_lesson' ) );
		add_action( 'llms_assignment_submitted', array( $this, 'complete_lesson' ) );
		add_filter( 'llms_allow_lesson_completion', array( $this, 'maybe_prevent_lesson_completion' ), 10, 5 );

	}

	/**
	 * When assignment is submitted, triggers completion of the lesson.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj $submission Instance of the LLMS_Assignment_Submission.
	 * @return void
	 */
	public function complete_lesson( $submission ) {

		$assignment = $submission->get_assignment();

		do_action(
			'llms_trigger_lesson_completion',
			$submission->get( 'user_id' ),
			$assignment->get( 'lesson_id' ),
			'assignment_' . $assignment->get( 'id' ),
			array(
				'submission' => $submission,
			)
		);

	}

	/**
	 * Handle form submission on admin panel assignment reviews.
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.1 Unknown.
	 * @since 1.1.5 Exit after redirecting on submission deletion.
	 *
	 * @return void
	 */
	public function handle_review_submission() {

		if ( ! llms_verify_nonce( '_llms_assignment_submission_nonce', 'llms_assignment_submission_actions', 'POST' ) ) {
			return;
		}

		if ( ! isset( $_POST['llms_assignment_submission_action'] ) || ! isset( $_POST['llms_submission_id'] ) ) {
			return;
		}

		$submission_action = sanitize_text_field( wp_unslash( $_POST['llms_assignment_submission_action'] ) );

		$submission = new LLMS_Assignment_Submission( absint( $_POST['llms_submission_id'] ) );
		if ( ! $submission->exists() ) {
			return;
		}

		if ( 'llms_submission_grade' === $submission_action ) {

			$grade   = isset( $_POST['llms_assignment_submission_grade'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_assignment_submission_grade'] ) ) : 0;
			$remarks = isset( $_POST['llms_assignment_submission_remarks'] ) ? wp_kses_post( wp_unslash( $_POST['llms_assignment_submission_remarks'] ) ) : '';

			$submission->grade( $grade, $remarks );

			$assignment = $submission->get_assignment();
			if ( 'tasklist' === $assignment->get( 'assignment_type' ) ) {

				$submitted_tasks = filter_input( INPUT_POST, 'llms_assignment_task', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				if ( ! $submitted_tasks ) {
					$submitted_tasks = array();
				}

				$mark_incomplete = false;

				foreach ( $assignment->get_tasks() as $task ) {

					$status = ( in_array( $task->get( 'id' ), $submitted_tasks ) );
					if ( ! $status ) {
						$mark_incomplete = true;
					}

					if ( $status !== $submission->get_task_status( $task->get( 'id' ) ) ) {
						$submission->update_task( $task->get( 'id' ), $status );
					}
				}

				if ( $mark_incomplete ) {
					$submission->mark_incomplete();
				}
			}
		} elseif ( 'llms_submission_delete' === $submission_action ) {

			$url = add_query_arg(
				array(
					'page'          => 'llms-reporting',
					'tab'           => 'assignments',
					'stab'          => 'submissions',
					'assignment_id' => $submission->get( 'assignment_id' ),
				),
				admin_url( 'admin.php' )
			);

			$submission->delete();
			wp_safe_redirect( $url );
			exit();

		} elseif ( 'llms_submission_unlock' === $submission_action ) {

			$submission->mark_incomplete();

		}

	}

	/**
	 * Before a lesson is marked as complete, check if all the lesson's assignment requirements are met.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Unknown.
	 *
	 * @param bool   $allow_completion Whether or not to allow completion (true by default, false if something else has already prevented).
	 * @param int    $user_id          WP User ID of the student completing the lesson.
	 * @param int    $lesson_id        WP Post ID of the lesson to be completed.
	 * @param string $trigger          Text string to record the reason why the lesson is being completed.
	 * @param array  $args             Optional additional arguements from the triggering function.
	 * @return bool
	 */
	public function maybe_prevent_lesson_completion( $allow_completion, $user_id, $lesson_id, $trigger, $args ) {

		// if allow completion is already false, we don't need to run any assignment checks.
		if ( ! $allow_completion ) {
			return $allow_completion;
		}

		$lesson           = llms_get_post( $lesson_id );
		$passing_required = llms_parse_bool( $lesson->get( 'require_assignment_passing_grade' ) );

		// if the lesson is being completed by an assignment.
		if ( 0 === strpos( $trigger, 'assignment_' ) ) {

			// passing is required AND the attempt was a failure.
			if ( $passing_required && 'pass' !== $args['submission']->get( 'status' ) ) {
				$allow_completion = false;
			}

			// if the lesson has a quiz.
		} elseif ( llms_lesson_has_assignment( $lesson ) ) {

			$assignment = llms_lesson_get_assignment( $lesson );

			// $student = llms_get_student( $user_id );.
			$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $user_id );
			if ( $submission ) {

				// passing is not required but there's not submissions yet.
				// at least one attempt (passing or otherwise) is required!.
				if ( ! $passing_required && ! $submission->is_complete() ) {
					$allow_completion = false;

					// passing is required and there's no attempts or the best attempt is not passing.
				} elseif ( $passing_required && ! $submission->is_passing() ) {
					$allow_completion = false;
				}
			}
		}

		return $allow_completion;

	}

}

return new LLMS_Assignments_Completion();
