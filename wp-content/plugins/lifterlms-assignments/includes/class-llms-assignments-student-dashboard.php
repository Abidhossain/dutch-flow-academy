<?php
/**
 * Modify the student dashboard to add assignment-related information
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.6
 * @version 1.1.9
 */

defined( 'ABSPATH' ) || exit;

/**
 * Modify the student dashboard to add assignment-related information
 *
 * @since 1.0.0-beta.6
 * @since 1.1.9 Make sure we display the of the correct student by passing the `$student` object
 *              to the `llms_student_get_assignment_submission()` function as second param, instead of relying on
 *              the current logged in user.
 * @since 1.1.10 Improve the displaying of the assignment's submission link logic. Also make sure the link
 *               points to the correct user submission.
 */
class LLMS_Assignments_Student_Dashboard {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_student_dashboard_my_grades_table_headings', array( $this, 'add_my_grades_table_headings' ) );

		add_action( 'llms_sd_my_grades_table_content_assignment', array( $this, 'add_my_grades_table_assignment_content' ), 10, 3 );

		add_action( 'llms_sd_my_grades_table_content_associated_quiz_before', array( $this, 'add_my_grades_table_quiz_content' ), 10, 3 );

	}

	/**
	 * Add an "Assignment" column to single grade tables
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @param array $headings Associative array of headings.
	 * @return array
	 */
	public function add_my_grades_table_headings( $headings ) {
		return llms_assoc_array_insert( $headings, 'completion_date', 'assignment', __( 'Assignment', 'lifterlms-assignments' ) );
	}

	/**
	 * Output HTML for the assignment column on a single grade table
	 *
	 * @since 1.0.0-beta.6
	 * @since 1.1.9 Pass the `$student` object to the `llms_student_get_assignment_submission()` function as second param.
	 * @since 1.1.10 Improve the displaying of the assignment's submission link logic. Also make sure the link
	 *               points to the correct user submission.
	 *               Also turn `Review` into `View` to make it more generic now that this content is shown also to other users.
	 *
	 * @param LLMS_Lesson  $lesson       LLMS_Lesson object.
	 * @param LLMS_Student $student      LLMS_Student object.
	 * @param array        $restrictions Array of page restriction information from `llms_page_restricted()`.
	 * @return void
	 */
	public function add_my_grades_table_assignment_content( $lesson, $student, $restrictions ) {

		$has_assignment = llms_lesson_has_assignment( $lesson );

		if ( $has_assignment && $restrictions['is_restricted'] ) {
			echo '<i class="fa fa-lock" aria-hidden="true"></i>';
		} elseif ( $has_assignment ) {
			$assignment = llms_lesson_get_assignment( $lesson );
			$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );

			if ( ! $submission ) {
				echo '&ndash;';
				return;
			}

			$status = $submission->get( 'status' );
			$text   = '';
			$url    = get_permalink( $assignment->get( 'id' ) );

			if ( 'incomplete' !== $status ) {

				// If incomplete and the current user is not the student, add the student id query arg to the assignment url.
				$url = ( get_current_user_id() === absint( $student->get_id() ) ) ? $url : add_query_arg( 'sid', $student->get_id(), $url );

				$text  = __( 'View', 'lifterlms-assignments' );
				$grade = $submission->get( 'grade' );
				echo is_numeric( $grade ) ? llms_get_donut( $grade, '', 'mini' ) : '';
			} elseif ( get_current_user_id() === absint( $student->get_id() ) ) {

				// If not incomplete and the current user is the submission's student, set the link text to "Start".
				$text = __( 'Start', 'lifterlms-assignments' );
			}

			echo '<span class="llms-status llms-' . esc_attr( $status ) . '">' . $submission->get_l10n_status() . '</span>';

			// Display a link to the submission only if there's anything to do: Review or Start.
			if ( $text ) {
				echo '<a href="' . $url . '">' . $text . '</a>';
			}
		} else {
			echo '&ndash;';
		}

	}

	/**
	 * Add a donut for the quiz grade on a lesson with a quiz and an assignment
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @param LLMS_Lesson  $lesson       LLMS_Lesson object.
	 * @param LLMS_Student $student      LLMS_Student object.
	 * @param array        $restrictions Array of page restriction information from `llms_page_restricted()`.
	 * @return void
	 */
	public function add_my_grades_table_quiz_content( $lesson, $student, $restrictions ) {

		if ( $lesson->has_quiz() && ! $restrictions['is_restricted'] ) {
			$attempt = $student->quizzes()->get_last_attempt( $lesson->get( 'quiz' ) );
			if ( $attempt ) {
				$grade = $attempt->get( 'grade' );
				echo is_numeric( $grade ) ? llms_get_donut( $grade, '', 'mini' ) : '';
			}
		}

	}

}

return new LLMS_Assignments_Student_Dashboard();
