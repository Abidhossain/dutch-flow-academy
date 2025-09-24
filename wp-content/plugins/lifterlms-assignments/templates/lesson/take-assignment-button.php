<?php
/**
 * Lesson "Take Assignment" Button
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.6
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Lesson       $lesson     The assignment's lesson.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'llms_before_start_assignment_button' ); ?>

<a class="llms-button-action auto button llms-start-assignment-button" id="llms-start-assignment" href="<?php echo esc_url( get_permalink( $assignment->get( 'id' ) ) ); ?>">
	<?php echo esc_html( apply_filters( 'lifterlms_start_assignment_button_text', __( 'Start Assignment', 'lifterlms-assignments' ), $assignment->get( 'id' ), $lesson ) ); ?>
</a>

<?php do_action( 'llms_after_start_assignment_button' ); ?>
