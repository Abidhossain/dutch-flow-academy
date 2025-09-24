<?php
/**
 * Single Assignment Tab: Single submission Subtab
 *
 * @package LifterLMS_Assignments/Admin/Views
 *
 * @since 1.0.0-beta.2
 * @since 2.1.0 Escaped output.
 * @version 2.1.0
 *
 * @param LLMS_Assignment_Submission $submission Instance of the LLMS_Assignment_Submission.
 */

defined( 'ABSPATH' ) || exit;

$student         = $submission->get_student();
$content         = $submission->get_submission();
$assignment      = $submission->get_assignment();
$assignment_type = $assignment->get( 'assignment_type' );
?>

<section class="llms-reporting-tab-main llms-reporting-widgets">

	<header>
		<h3><?php echo $submission->get_title(); ?></h3>
	</header>
	<?php

	do_action( 'llms_reporting_single_assignment_submission_before_widgets', $submission );

	LLMS_Admin_Reporting::output_widget(
		array(
			'cols'      => 'd-1of4',
			'icon'      => 'graduation-cap',
			'id'        => 'llms-reporting-assignment-submission-grade',
			'data'      => $submission->get( 'grade' ) ? $submission->get( 'grade' ) : 0,
			'data_type' => 'percentage',
			'text'      => __( 'Grade', 'lifterlms-assignments' ),
		)
	);

	switch ( $submission->get( 'status' ) ) {
		case 'pass':
			$icon = 'star';
			break;
		case 'incomplete':
		case 'fail':
			$icon = 'times-circle';
			break;
		case 'pending':
			$icon = 'clock-o';
			break;
		default:
			$icon = 'question-circle';
	}

	LLMS_Admin_Reporting::output_widget(
		array(
			'cols'      => 'd-1of4',
			'icon'      => $icon,
			'id'        => 'llms-reporting-assignment-submission-status',
			'data'      => esc_attr( $submission->get_l10n_status() ),
			'data_type' => 'text',
			'text'      => esc_html__( 'Status', 'lifterlms-assignments' ),
		)
	);

	LLMS_Admin_Reporting::output_widget(
		array(
			'cols'      => 'd-1of4',
			'icon'      => 'sign-in',
			'id'        => 'llms-reporting-assignment-submission-start-date',
			'data'      => esc_attr( $submission->get_date( 'created' ) ),
			'data_type' => 'date',
			'text'      => esc_html__( 'Start Date', 'lifterlms-assignments' ),
		)
	);

	if ( 'incomplete' !== $submission->get( 'status' ) ) {

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of4',
				'icon'      => 'sign-out',
				'id'        => 'llms-reporting-assignment-submission-end-date',
				'data'      => esc_attr( $submission->get_date( 'updated' ) ),
				'data_type' => 'date',
				'text'      => esc_html__( 'End Date', 'lifterlms-assignments' ),
			)
		);

	}

	do_action( 'llms_reporting_single_assignment_submission_after_widgets', $submission );
	?>

	<div class="clear"></div>

	<form class="llms-assignment-review" action="" method="POST">

		<section class="llms-assignment-main">

			<?php if ( 'tasklist' === $assignment_type ) : ?>
				<?php llms_get_template( 'assignment/content-tasklist.php', compact( 'assignment', 'student' ) ); ?>
			<?php else : ?>
				<h3><?php esc_html_e( 'Submission', 'lifterlms-assignments' ); ?></h3>
				<div class="llms-assignment-submission"><?php echo 'essay' === $assignment_type ? $content : $submission->get_submission_upload_html(); ?></div>
			<?php endif; ?>

			<section class="llms-assignment-review-fields">

				<label class="llms-assignment-remarks" for="llms-assignment-submission-remarks">
					<strong><?php esc_html_e( 'Remarks', 'lifterlms-assignments' ); ?></strong>
					<textarea disabled="disabled" id="llms-assignment-submission-remarks" name="llms_assignment_submission_remarks"><?php echo str_replace( array( '<br />', '<br>', '<br/>' ), '', $submission->get( 'remarks' ) ); ?></textarea>
				</label>

				<label for="llms-assignment-submission-grade">
					<strong><?php esc_html_e( 'Grade', 'lifterlms-assignments' ); ?></strong>
					<input class="small-text" disabled="disabled" id="llms-assignment-submission-grade" min="0" max="100" name="llms_assignment_submission_grade" required="required" type="number" value="<?php echo esc_attr( $submission->get( 'grade' ) ); ?>">%
				</label>

			</section>

		</section>

		<footer class="llms-assignments-review-actions">

			<button class="llms-button-primary large llms-assignments-review-start" name="llms_assignment_submission_action" type="submit" value="llms_submission_grade">
				<span class="default">
					<i class="fa fa-check-square-o" aria-hidden="true"></i>
					<?php esc_html_e( 'Start a Review', 'lifterlms-assignments' ); ?>
				</span>
				<span class="save">
					<i class="fa fa-floppy-o" aria-hidden="true"></i>
					<?php esc_html_e( 'Save Review', 'lifterlms-assignments' ); ?>
				</span>
			</button>

			<button class="llms-button-secondary large llms-assignments-review-cancel" name="llms_assignment_submission_action" type="submit" value="llms_submission_cancel">
				<i class="fa fa-ban" aria-hidden="true"></i>
				<?php esc_html_e( 'Cancel Review', 'lifterlms-assignments' ); ?>
			</button>

			<button class="llms-button-secondary large" name="llms_assignment_submission_action" type="submit" value="llms_submission_unlock">
				<i class="fa fa-refresh" aria-hidden="true"></i>
				<?php esc_html_e( 'Mark Incomplete', 'lifterlms-assignments' ); ?>
			</button>

			<button class="llms-button-danger large" name="llms_assignment_submission_action" type="submit" value="llms_submission_delete">
				<i class="fa fa-trash-o" aria-hidden="true"></i>
				<?php esc_html_e( 'Delete submission', 'lifterlms-assignments' ); ?>
			</button>

		</footer>

		<input type="hidden" name="llms_submission_id" value="<?php echo esc_attr( $submission->get( 'id' ) ); ?>">

		<?php wp_nonce_field( 'llms_assignment_submission_actions', '_llms_assignment_submission_nonce' ); ?>

	</form>


</section>

