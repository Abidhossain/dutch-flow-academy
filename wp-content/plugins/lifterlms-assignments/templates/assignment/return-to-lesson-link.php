<?php
/**
 * Single Assignment: Return to Lesson Link
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-return">
	<a href="<?php echo esc_url( get_permalink( $assignment->get( 'lesson_id' ) ) ); ?>">
		<?php esc_html_e( 'Return to Lesson', 'lifterlms-assignments' ); ?>
	</a>
</div>
