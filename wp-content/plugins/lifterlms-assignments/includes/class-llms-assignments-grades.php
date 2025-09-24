<?php
/**
 * Modify grading of LMS content based on presence of assignments
 *
 * @package LifterLMS_Assignments/Classes
 * @since    1.0.0-beta.6
 * @version  1.0.0-beta.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Modify grading of LMS content based on presence of assignments
 */
class LLMS_Assignments_Grades {

	/**
	 * Constructor
	 *
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function __construct() {

		add_filter( 'llms_calculate_lesson_grade', array( $this, 'calculate_lesson_grade' ), 10, 3 );

	}

	/**
	 * Handle modification of a lesson grade based on the presence of an assignment
	 *
	 * @param    float|null $grade    default grade (quiz grade).
	 * @param    obj        $lesson   LLMS_Lesson object.
	 * @param    obj        $student  LLMS_Student object.
	 * @return   float|null
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function calculate_lesson_grade( $grade, $lesson, $student ) {

		if ( llms_lesson_has_assignment( $lesson ) ) {

			$assignment = llms_lesson_get_assignment( $lesson );
			if ( $assignment ) {

				$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );
				if ( $submission ) {

					$assignment_grade = $submission->get( 'grade' );

					// use weights to determine the grade.
					if ( $lesson->is_quiz_enabled() ) {

						$quiz = $lesson->get_quiz();
						if ( $quiz ) {

							// both must be complete to calculate a grade.
							if ( ! is_numeric( $assignment_grade ) || ! is_numeric( $grade ) ) {

								$grade = null;

							} else {

								$grade = $this->calculate_grade( $assignment_grade, $assignment->get( 'points' ), $grade, $quiz->get( 'points' ) );

							}
						}
						// there's no.
					} else {
						$grade = $assignment_grade;
					}
				}
			}
		}
		return $grade;

	}

	/**
	 * Calculate the grade of a lesson based on the quiz and assignment grades
	 *
	 * @param    float $assignment_grade   assignment grade.
	 * @param    int   $assignment_points  assignment points.
	 * @param    float $quiz_grade         quiz grade.
	 * @param    int   $quiz_points        quiz points.
	 * @return   float
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function calculate_grade( $assignment_grade, $assignment_points, $quiz_grade, $quiz_points ) {

		$total_points     = $quiz_points + $assignment_points;
		$calculated_grade = $total_points ? ( ( $quiz_grade * $quiz_points ) + ( $assignment_grade * $assignment_points ) ) / $total_points : null;

		return apply_filters( 'llms_assignments_calculate_lesson_grade', LLMS()->grades()->round( $calculated_grade ) );

	}

}

return new LLMS_Assignments_Grades();
