<?php
/**
 * Single Assignment: Results
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.2
 * @since 1.1.10 Unknown.
 * @since 2.1.0 Bail if there's no submission.
 * @version 2.1.0
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $student ) ) {
	$student = null;
}
$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );
if ( empty( $submission ) ) {
	return;
}
if ( 'incomplete' === $submission->get( 'status' ) && ! $submission->get( 'remarks' ) ) {
	return;
}
?>

<section class="llms-assignment-submission-results">

	<h3 class="llms-assignments-results-title">
		<?php echo esc_html( apply_filters( 'llms_assignments_results_title', __( 'Grade & Instructor Remarks', 'lifterlms-assignments' ) ) ); ?>
	</h3>

	<?php if ( 'incomplete' !== $submission->get( 'status' ) ) : ?>
		<?php echo llms_get_donut( $submission->get( 'grade' ), $submission->get_l10n_status(), 'default', array( $submission->get( 'status' ) ) ); ?>
	<?php endif; ?>

	<div class="llms-assignment-remarks"><?php echo wpautop( $submission->get( 'remarks' ) ); ?></div>

</section>
