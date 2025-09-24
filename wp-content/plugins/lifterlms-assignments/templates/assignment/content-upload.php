<?php
/**
 * Single Assignment: Upload
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.2
 * @since 1.1.10 Unknown.
 * @since 2.1.0 Escaped localized strings.
 * @version 2.1.0
 *
 * @property LLMS_Assignment    $assignment The assignment instance.
 * @property LLMS_Student|false $student    The student for the current assignment. Can be `false`.
 */

defined( 'ABSPATH' ) || exit;

$submission = llms_student_get_assignment_submission( $assignment->get( 'id' ), $student );
$types      = llms_parse_bool( $assignment->get( 'enable_allowed_mimes' ) ) ? $assignment->get_allowed_mimes_string() : false;
?>

<section class="llms-assignment-content type--upload status--<?php echo $submission->is_complete() ? 'complete' : 'incomplete'; ?>">

	<?php if ( ! $submission->get_submission() ) : ?>
		<label class="llms-assignment-upload-field" id="llms-assignment-uploader-zone" for="llms-assignment-upload-file">

			<i class="fa fa-upload" aria-hidden="true"></i>
			<h2><?php esc_html_e( 'Select or drop a file...', 'lifterlms-assignments' ); ?></h2>
			<?php if ( $types ) : ?>
				<?php // Translators: %s = commas-separated list of allowed file types. ?>
				<em><?php printf( esc_html__( 'Allowed filetypes: %s', 'lifterlms-assignments' ), $types ); ?></em>
			<?php endif; ?>

			<input id="llms-assignment-upload-file" type="file"<?php echo $types ? ' accept="' . $types . '"' : ''; ?>>
			<?php wp_nonce_field( 'llms_assignment_upload', 'llms-assignment-upload-nonce' ); ?>

		</label>
	<?php else : ?>

		<div class="llms-assignment-upload-file">
			<?php if ( ! $submission->is_complete() ) : ?>
				<a href="#" id="llms-assignment-remove-file" title="<?php esc_attr_e( 'Remove file', 'lifterlms-assignments' ); ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
			<?php endif; ?>
			<?php echo wp_kses_post( $submission->get_submission_upload_html( 'submission' ) ); ?>
		</div>

	<?php endif; ?>

</section>



