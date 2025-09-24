<?php
/**
 * LifterLMS Assignments functions
 *
 * @package LifterLMS_Assignments/Functions
 *
 * @since 1.0.0-beta.1
 * @version 1.1.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve an instance of an assignment submission
 *
 * @since 1.0.0-beta.1
 *
 * @param int|array $submission Submission ID or array of submission data.
 * @return LLMS_Assignment_Submission
 */
function llms_get_assignment_submission( $submission ) {
	return new LLMS_Assignment_Submission( $submission );
}

/**
 * Retrieve an array of valid assignment submission statuses
 *
 * @since 1.0.0-beta.2
 *
 * @return array
 */
function llms_get_assignment_submission_statuses() {
	return apply_filters(
		'llms_get_assignment_submission_statuses',
		array(
			'fail'       => __( 'Fail', 'lifterlms-assignments' ),
			'incomplete' => __( 'Incomplete', 'lifterlms-assignments' ),
			'pass'       => __( 'Pass', 'lifterlms-assignments' ),
			'pending'    => __( 'Pending', 'lifterlms-assignments' ),
		)
	);
}

/**
 * Retrieve assignments available to a given instructor
 *
 * @since 1.1.4
 *
 * @param LLMS_Instructor|WP_User|int|null $instructor User reference. Uses current user if no reference is passed.
 * @return false|int[] `false` If no instructor is found, otherwise returns an array of LLMS_Assignment post IDs.
 */
function llms_instructor_get_assignments( $instructor = null ) {

	$instructor = ! is_a( $instructor, 'LLMS_Instructor' ) ? llms_get_instructor( $instructor ) : $instructor;

	// Instructor not found.
	if ( ! $instructor ) {
		return false;
	}

	$courses = $instructor->get_courses( array( 'posts_per_page' => -1 ), 'ids' );

	// Return early if the instructor doesn't have any courses.
	if ( ! $courses ) {
		return array();
	}

	global $wpdb;

	// Prepare the Query.
	return $wpdb->get_col(
		$wpdb->prepare(
			sprintf(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_llms_lesson_id' AND meta_value IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_llms_parent_course' AND meta_value IN ( %s ) )",
				implode( ', ', array_fill( 0, count( $courses ), '%d' ) )
			),
			$courses
		)
	);

}

/**
 * Retrieve the assignment object from a given LLMS_Lesson
 *
 * @since 1.0.0-beta.1
 * @since 1.1.4 Simplify logic.
 *
 * @param LLMS_Lesson|WP_Post|int $lesson Lesson reference.
 * @return LLMS_Assignment|false
 */
function llms_lesson_get_assignment( $lesson ) {

	$lesson = is_a( $lesson, 'LLMS_Lesson' ) ? $lesson : llms_get_post( $lesson );
	if ( ! $lesson ) {
		return false;
	}

	if ( ! llms_lesson_has_assignment( $lesson ) ) {
		return false;
	}

	return llms_get_post( $lesson->get( 'assignment' ) );

}

/**
 * Determine if a lesson has an assignment enabled
 *
 * @since 1.0.0-beta.1
 * @since 1.1.4 Simplify logic.
 *
 * @param LLMS_Lesson|WP_Post|int $lesson Lesson reference.
 * @return boolean
 */
function llms_lesson_has_assignment( $lesson ) {

	$lesson = is_a( $lesson, 'LLMS_Lesson' ) ? $lesson : llms_get_post( $lesson );
	if ( ! $lesson ) {
		return false;
	}

	if ( llms_parse_bool( $lesson->get( 'assignment_enabled' ) ) ) {

		$assignment_id = $lesson->get( 'assignment' );

		if ( $assignment_id && ( 'publish' === get_post_status( $assignment_id ) || current_user_can( 'edit_post', $assignment_id ) ) ) {
			return true;
		}
	}

	return false;

}

/**
 * Get an assignment submission for a student
 * Creates a new one if one doesn't exist
 *
 * @since 1.0.0-beta.1
 * @since 2.0.0 Added a check to ensure the assignment and student exist before attempting to retrieve a submission.
 *
 * @param WP_Post|int $assignment WP Post ID or WP Post of the assignment.
 * @param mixed       $student    WP User ID or WP_User of the student (uses current user if none supplied).
 * @return LLMS_Assignment_Submission|boolean Returns the submission object or `false` if the student or assignment don't exist.
 */
function llms_student_get_assignment_submission( $assignment, $student = null ) {

	$assignment = llms_get_post( $assignment );
	$student    = llms_get_student( $student );

	if ( ! $student || ! is_a( $assignment, 'LLMS_Assignment' ) ) {
		return false;
	}

	global $wpdb;
	$id = $wpdb->get_var(
		$wpdb->prepare(
			"
		SELECT id
		FROM {$wpdb->prefix}lifterlms_assignments_submissions
		WHERE user_id = %d AND assignment_id = %d LIMIT 1;",
			$student->get_id(),
			$assignment->get( 'id' )
		)
	);

	if ( $id ) {
		return llms_get_assignment_submission( $id );
	}

	return llms_student_get_new_assignment_submission( $assignment->get( 'id' ), $student->get_id() );

}

/**
 * Create and retrieve a new assignment submission for a student
 *
 * @since 1.0.0-beta.1
 *
 * @param int $assignment_id WP Post ID of the assignment.
 * @param int $student_id WP User ID of the student.
 * @return LLMS_Assignment_Submission
 */
function llms_student_get_new_assignment_submission( $assignment_id, $student_id ) {

	$submission = llms_get_assignment_submission(
		array(
			'assignment_id' => $assignment_id,
			'user_id'       => $student_id,
			'status'        => 'incomplete',
		)
	);

	$submission->save();
	return $submission;

}
