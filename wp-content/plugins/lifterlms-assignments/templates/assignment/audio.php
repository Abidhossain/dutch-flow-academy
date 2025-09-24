<?php
/**
 * Single Assignment: Featured Audio Embed
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

if ( ! $assignment->get( 'audio_embed' ) ) {
	return;
}
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php echo $assignment->get_audio(); ?>
	</div>
</div>
