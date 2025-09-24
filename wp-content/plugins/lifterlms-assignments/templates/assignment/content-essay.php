<?php
/**
 * Single Assignment: Essay
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.2
 * @version 1.1.10
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;

$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );
$min        = llms_parse_bool( $assignment->get( 'enable_word_count_min' ) ) ? $assignment->get( 'word_count_min' ) : 1;
$max        = llms_parse_bool( $assignment->get( 'enable_word_count_max' ) ) ? $assignment->get( 'word_count_max' ) : false;
?>

<section class="llms-assignment-content type--essay status--<?php echo $submission->is_complete() ? 'complete' : 'incomplete'; ?>">
	<div class="llms-assignment-essay-field" data-words-max="<?php echo $max; ?>" data-words-min="<?php echo $min; ?>" required="required"><?php echo $submission->get( 'submission' ); ?></div>
</section>
