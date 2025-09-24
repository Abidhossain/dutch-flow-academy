<?php
/**
 * Single Assignment Reporting View: Overview Tab
 *
 * @package LifterLMS_Assignments/Admin/Views
 *
 * @since 1.0.0-beta.2
 * @since 1.0.0-beta.6 Unknown.
 * @since 2.1.0 Escaped output.
 * @version 2.1.0
 *
 * @param LLMS_Assignment       $assignment  Instance of the current `LLMS_Assignment`.
 * @param LLMS_Assignments_Data $data        Instance of `LLMS_Assignment_Data`.
 * @param string                $period_text Period text. {@see values of `LLMS_Admin_Reporting::get_period_filters()`}.
 * @param int                   $now         Current time stamp.
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="llms-reporting-tab-main llms-reporting-widgets">

	<header>

		<?php
		LLMS_Admin_Reporting::output_widget_range_filter(
			$period,
			'assignments',
			array(
				'assignment_id' => $assignment->get( 'id' ),
			)
		);
		?>
		<h3><?php esc_html_e( 'Assignment Overview', 'lifterlms-assignments' ); ?></h3>

	</header>
	<?php

	$course = $assignment->get_course();
	if ( $course ) {
		$url = LLMS_Admin_Reporting::get_current_tab_url(
			array(
				'tab'       => 'courses',
				'course_id' => $course->get( 'id' ),
			)
		);
		_e( 'Course:', 'lifterlms-assignments' );
		printf( ' <a href="%1$s">%2$s</a>', esc_url( $url ), $course->get( 'title' ) );

	}

	$lesson = $assignment->get_lesson();
	if ( $lesson ) {
		echo '<br>';
		// Translators: %s = Lesson title.
		printf( esc_html__( 'Lesson: %s', 'lifterlms-assignments' ), $lesson->get( 'title' ) );
	}

	echo '<br class="clear"><br class="clear">';

	do_action( 'llms_reporting_single_assignment_overview_before_widgets', $assignment );

	LLMS_Admin_Reporting::output_widget(
		array(
			'cols'         => 'd-1of2',
			'icon'         => 'users',
			'id'           => 'llms-reporting-assignment-total-submissions',
			'data'         => esc_attr( $data->get_submission_count( 'current' ) ),
			'data_compare' => esc_attr( $data->get_submission_count( 'previous' ) ),
			'text'         => sprintf(
				// Translators: %s = text describing the current period.
				esc_html__( 'Submissions %s', 'lifterlms-assignments' ),
				esc_html( $period_text )
			),
		)
	);

	LLMS_Admin_Reporting::output_widget(
		array(
			'cols'         => 'd-1of2',
			'icon'         => 'graduation-cap',
			'id'           => 'llms-reporting-assignment-avg-grade',
			'data'         => esc_attr( $data->get_average_grade( 'current' ) ),
			'data_compare' => esc_attr( $data->get_average_grade( 'previous' ) ),
			'data_type'    => 'percentage',
			'text'         => sprintf(
				// Translators: %s = text describing the current period.
				esc_html__( 'Average grade %s', 'lifterlms-assignments' ),
				esc_html( $period_text )
			),
		)
	);

	LLMS_Admin_Reporting::output_widget(
		array(
			'icon'         => 'check-circle',
			'id'           => 'llms-reporting-assignment-passes',
			'data'         => esc_attr( $data->get_pass_count( 'current' ) ),
			'data_compare' => esc_attr( $data->get_pass_count( 'previous' ) ),
			'text'         => sprintf(
				// Translators: %s = text describing the current period.
				esc_html__( 'Passed attempts %s', 'lifterlms-assignments' ),
				esc_html( $period_text )
			),
		)
	);

	LLMS_Admin_Reporting::output_widget(
		array(
			'icon'         => 'times-circle',
			'id'           => 'llms-reporting-assignment-fails',
			'data'         => esc_attr( $data->get_fail_count( 'current' ) ),
			'data_compare' => esc_attr( $data->get_fail_count( 'previous' ) ),
			'text'         => sprintf(
				// Translators: %s = text describing the current period.
				esc_html__( 'Failed attempts %s', 'lifterlms-assignments' ),
				esc_html( $period_text )
			),
			'impact'       => 'negative',
		)
	);

	do_action( 'llms_reporting_single_assignment_overview_after_widgets', $assignment );
	?>

</section>

<aside class="llms-reporting-tab-side">

	<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php esc_html_e( 'Recent events', 'lifterlms-assignments' ); ?></h3>

	<?php foreach ( $data->recent_events() as $submission ) : ?>
		<?php LLMS_Assignments_Reporting::output_event( $submission ); ?>
	<?php endforeach; ?>

</aside>


