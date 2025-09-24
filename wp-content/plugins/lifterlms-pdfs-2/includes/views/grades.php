<?php
/**
 * HTML View for the grades content type.
 *
 * @package LifterLMS_PDFS/Views
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$course_id = $course->get( 'id' );

$grade = $student->get_grade( $course->get( 'id' ) );
if ( is_numeric( $grade ) ) {
	$grade .= '%';
}

$last_activity      = $student->get_events(
	array(
		'per_page' => 1,
		'post_id'  => $course->get( 'id' ),
	)
);
$last_activity_date = $last_activity ? wp_date( get_option( 'date_format' ), strtotime( $last_activity[0]->get( 'updated_date' ) ) ) : '&ndash;';
?>
<h2><?php echo $course->get( 'title' ); ?></h2>

<table class="info-table">
	<tbody>
		<tr>
			<th><?php _e( 'Student Name', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $student->get_name(); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Progress', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $student->get_progress( $course_id ); ?>%</td>
		</tr>
		<tr>
			<th><?php _e( 'Grade', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $grade; ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Enrollment Status', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo llms_get_enrollment_status_name( $student->get_enrollment_status( $course_id ) ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Enrollment Date', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $student->get_enrollment_date( $course_id, 'enrolled' ); ?></td>
		</tr>
		<tr>
			<th><?php _e( 'Last Activity Date', 'lifterlms-pdfs' ); ?></th>
			<td><?php echo $last_activity_date; ?></td>
		</tr>
	</tbody>
</table>

<br><br>

<?php lifterlms_template_student_dashboard_my_grades_table( $course, $student ); ?>
