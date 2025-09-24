<?php
/**
 * Single Assignment: Footer
 *
 * Progress actions & navigation.
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.1
 * @since 1.1.10 Unknown.
 * @since 2.1.0 Escaped localized strings.
 * @version 2.1.0
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;

// Nothing to show if the current user is not the assignment's student.
if ( ! empty( $student ) && get_current_user_id() !== absint( $student->get_id() ) ) {
	return;
}

$lesson     = $assignment->get_lesson();
$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );
?>

<footer class="llms-assignment-footer">

	<?php if ( $submission->is_complete() ) : ?>

		<?php if ( ! llms_is_complete( get_current_user_id(), $lesson->get( 'id' ), 'lesson' ) ) : ?>

			<?php if ( llms_parse_bool( $lesson->get( 'quiz_enabled' ) ) ) : ?>
				<a class="llms-button-primary" href="<?php echo esc_url( get_permalink( $lesson->get( 'quiz' ) ) ); ?>"><?php esc_html_e( 'Take Quiz', 'lifterlms-assignments' ); ?></a>
			<?php endif; ?>

		<?php elseif ( $lesson->get_next_lesson() ) : ?>

			<a href="<?php echo esc_url( get_permalink( $lesson->get_next_lesson() ) ); ?>" class="llms-button-primary llms-next-lesson"><?php esc_html_e( 'Next Lesson', 'lifterlms-assignments' ); ?></a>

		<?php endif; ?>

	<?php else : ?>

		<button class="llms-button-primary llms-assignment-submit" disabled="disabled" id="llms-assignment-submit">
			<?php esc_html_e( 'Submit Assignment', 'lifterlms-assignments' ); ?>
			<i class="fa fa-paper-plane" aria-hidden="true"></i>
		</button>

	<?php endif; ?>

	<i class="llms-assignments-save-indicator"></i>

	<input id="llms-assignment-id" type="hidden" value="<?php echo esc_attr( $assignment->get( 'id' ) ); ?>">

</footer>
