<?php
/**
 * Single Assignment: Task list
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.1
 * @version 1.1.10
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;

$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );
?>

<section class="llms-assignment-content type--tasklist status--<?php echo $submission->is_complete() ? 'complete' : 'incomplete'; ?>">

	<h3 class="llms-assignment-content-title">
		<?php echo apply_filters( 'llms_assignment_content_title', __( 'Assignment Tasks', 'lifterlms-assignments' ), $assignment ); ?>
	</h3>

	<ol class="llms-assignment-tasklist">
	<?php foreach ( $assignment->get_tasks() as $task ) : ?>

		<li class="llms-assignment-task" id="llms-assignment-task--<?php echo esc_attr( $task->get( 'id' ) ); ?>">

			<strong class="llms-task-marker"><?php echo $task->get( 'marker' ); ?></strong>

			<input id="llms_task_<?php echo esc_attr( $task->get( 'id' ) ); ?>" name="llms_assignment_task[]" type="checkbox" value="<?php echo esc_attr( $task->get( 'id' ) ); ?>"<?php checked( 1, $submission->get_task_status( $task->get( 'id' ) ) ); ?><?php echo $submission->is_complete() ? ' disabled="disabled"' : ''; ?>>
			<label class="llms-task-main" for="llms_task_<?php echo esc_attr( $task->get( 'id' ) ); ?>">
				<i class="fa fa-check llms-task-check" aria-hidden="true"></i>
				<span class="llms-task-title"><?php echo make_clickable( $task->get( 'title' ) ); ?></span>
				<?php if ( $submission->get_task_status( $task->get( 'id' ) ) ) : ?>
					<?php echo llms_assignments_task_completed_time( $submission, $task->get( 'id' ) ); ?>
				<?php endif; ?>
			</label>

		</li>

	<?php endforeach; ?>
	</ol>

</section>
