<?php
/**
 * Assignment Notification View: New Assignment Submission
 *
 * @package LifterLMS_Assignments/Classes/Notifications
 *
 * @since 1.0.0-beta.6
 * @version 1.1.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assignment Notification View: New Assignment Submission
 *
 * @since 1.0.0-beta.6
 * @since 1.1.3 Use `created` for start date and `submitted` for end date.
 * @since 1.1.7 Added `get_object()` method.
 *              In the `submission_summary_code()` method, bail if no assignment.
 */
class LLMS_Assignments_Notification_View_Assignment_Submitted extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 *
	 * @var string
	 */
	public $trigger_id = 'assignment_submitted';

	/**
	 * LLMS_Assignment
	 *
	 * @var obj
	 */
	protected $assignment = null;

	/**
	 * LLMS_Course
	 *
	 * @var obj
	 */
	protected $course = null;

	/**
	 * LLMS_Lesson
	 *
	 * @var obj
	 */
	protected $lesson = null;

	/**
	 * LLMS_Assignment_Submission
	 *
	 * @var obj
	 */
	protected $submission = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.6
	 * @since 1.1.7 Due to the changes to the LLMS_Abstract_Notification_View constructore now the `$submission` property is set as the `$post` property prior to unsetting it.
	 *
	 * @param mixed $notification Notification id, instance of LLMS_Notification or an object containing at least an 'id'.
	 * @return void
	 */
	public function __construct( $notification ) {

		parent::__construct( $notification );
		$this->submission = $this->post instanceof LLMS_Assignment_Submission ? $this->post : $this->get_object();
		unset( $this->post );
		if ( $this->submission && $this->submission->exists() ) {
			$this->assignment = $this->submission->get_assignment();

			if ( $this->assignment ) {
				$this->course = $this->assignment->get_course();
				$this->lesson = $this->assignment->get_lesson();
			}
		}
	}

	/**
	 * Get the assignment submission associated to the notification
	 *
	 * @since 1.1.7
	 *
	 * @return LLMS_Assignment_Submission
	 */
	protected function get_object() {
		return llms_get_assignment_submission( $this->notification->get( 'post_id' ) );
	}

	/**
	 * Setup body content for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	protected function set_body() {

		if ( 'email' === $this->notification->get( 'type' ) ) {
			return $this->set_body_email();
		}
		return $this->set_body_basic();
	}

	/**
	 * Setup basic body content for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	private function set_body_basic() {
		return '';
	}

	/**
	 * Setup email body content for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	private function set_body_email() {

		$mailer = LLMS()->mailer();

		$btn_style = $mailer->get_button_style();

		$table_style = sprintf(
			'border-collapse:collapse;color:%1$s;font-family:%2$s;font-size:%3$s;Margin-bottom:15px;text-align:left;width:100%%;',
			$mailer->get_css( 'font-color', false ),
			$mailer->get_css( 'font-family', false ),
			$mailer->get_css( 'font-size', false )
		);
		$tr_style    = 'color:inherit;font-family:inherit;font-size:inherit;';
		$td_style    = sprintf( 'border-bottom:1px solid %s;color:inherit;font-family:inherit;font-size:inherit;padding:10px;', $mailer->get_css( 'divider-color', false ) );

		$rows = array(
			'STUDENT_NAME'     => __( 'Student', 'lifterlms-assignments' ),
			'ASSIGNMENT_TITLE' => __( 'Assignment', 'lifterlms-assignments' ),
			'LESSON_TITLE'     => __( 'Lesson', 'lifterlms-assignments' ),
			'COURSE_TITLE'     => __( 'Course', 'lifterlms-assignments' ),
			'STATUS'           => __( 'Status', 'lifterlms-assignments' ),
			'START_DATE'       => __( 'Started', 'lifterlms-assignments' ),
			'END_DATE'         => __( 'Completed', 'lifterlms-assignments' ),
		);

		ob_start();
		?><table style="<?php echo $table_style; ?>">
		<?php foreach ( $rows as $code => $name ) : ?>
			<tr style="<?php echo $tr_style; ?>">
				<th style="<?php echo $td_style; ?>width:33.3333%;"><?php echo $name; ?></th>
				<td style="<?php echo $td_style; ?>">{{<?php echo $code; ?>}}</td>
			</tr>
		<?php endforeach; ?>
		</table>
		{{SUBMISSION_SUMMARY}}
		{{DIVIDER}}
		<p><a href="{{REVIEW_URL}}" style="<?php echo $btn_style; ?>"><?php _e( 'View the whole submission and leave remarks', 'lifterlms-assignments' ); ?></a></p>
		<p><small><?php _e( 'Trouble clicking? Copy and paste this URL into your browser:', 'lifterlms-assignments' ); ?><br><a href="{{REVIEW_URL}}">{{REVIEW_URL}}</a></small></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	protected function set_icon() {
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{ASSIGNMENT_TITLE}}'   => __( 'Assignment Title', 'lifterlms-assignments' ),
			'{{COURSE_TITLE}}'       => __( 'Course Title', 'lifterlms-assignments' ),
			'{{END_DATE}}'           => __( 'End Date', 'lifterlms-assignments' ),
			'{{LESSON_TITLE}}'       => __( 'Lesson Title', 'lifterlms-assignments' ),
			'{{REVIEW_URL}}'         => __( 'Review URL', 'lifterlms-assignments' ),
			'{{START_DATE}}'         => __( 'Start Date', 'lifterlms-assignments' ),
			'{{STATUS}}'             => __( 'Status', 'lifterlms-assignments' ),
			'{{STUDENT_NAME}}'       => __( 'Student Name', 'lifterlms-assignments' ),
			'{{SUBMISSION_SUMMARY}}' => __( 'Submission Summary', 'lifterlms-assignments' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 *
	 * @since 1.0.0-beta.6
	 * @since 1.1.3 Use `created` for start date and `submitted` for end date.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{ASSIGNMENT_TITLE}}':
				$code = '';
				if ( isset( $this->assignment ) ) {
					$code = $this->assignment->get( 'title' );
				}
				break;

			case '{{COURSE_TITLE}}':
				$code = '';
				if ( isset( $this->course ) ) {
					$code = $this->course->get( 'title' );
				}
				break;

			case '{{END_DATE}}':
				$code = $this->submission->get_date( 'submitted' );
				break;

			case '{{LESSON_TITLE}}':
				$code = '';
				if ( isset( $this->lesson ) ) {
					$code = $this->lesson->get( 'title' );
				}
				break;

			case '{{REVIEW_URL}}':
				$code = $this->submission->get_review_url();
				break;

			case '{{START_DATE}}':
				$code = $this->submission->get_date( 'created' );
				break;

			case '{{STATUS}}':
				$code = $this->submission->get_l10n_status();
				break;

			case '{{STUDENT_NAME}}':
				$code = $this->user->get_name();
				break;

			case '{{SUBMISSION_SUMMARY}}':
				$code = $this->submission_summary_code();
				break;

		}

		return $code;
	}

	/**
	 * Setup notification subject for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	protected function set_subject() {
		// Translators: %1$s = Student Name, %2$s = Assignment Title.
		return sprintf( __( '%1$s submitted the assignment "%2$s"', 'lifterlms-assignments' ), '{{STUDENT_NAME}}', '{{ASSIGNMENT_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Assignment Submission Details', 'lifterlms-assignments' );
	}

	/**
	 * Get the HTML for the submission summary merge code
	 *
	 * @since 1.0.0-beta.6
	 * @since 1.1.7 Bail if no assignment.
	 *
	 * @return string
	 */
	private function submission_summary_code() {

		if ( ! $this->assignment ) {
			return '';
		}

		ob_start();
		$assignment_type = $this->assignment->get( 'assignment_type' );
		if ( 'tasklist' === $assignment_type ) {
			echo '<p><strong>' . __( 'Tasks Summary', 'lifterlms-assignments' ) . '</strong></p>';
			foreach ( $this->assignment->get_tasks() as $i => $task ) {
				// only output the first 10 tasks in the email.
				if ( 10 === $i ) {
					break;
				}
				echo '<p>';
				echo '<strong>' . $task->get( 'marker' ) . '</strong>. ';
				echo $task->get( 'title' );
				echo $this->submission->get_task_status( $task->get( 'id' ) ) ? ' &#10004;' : ' &#10006;'; // heavy check / heavy multiplication.
				echo '</p>';
			}
		} elseif ( 'essay' === $assignment_type ) {
			echo '<p><strong>' . __( 'Essay Excerpt', 'lifterlms-assignments' ) . '</strong></p>';
			echo balanceTags( llms_trim_string( $this->submission->get_submission(), apply_filters( 'llms_assignments_submission_summary_essay_limit', '500', $this->assignment ) ), true );
		} else {
			echo '<p><strong>' . __( 'Submission', 'lifterlms-assignments' ) . '</strong></p>';
			llms_log( $this->submission->get_submission_upload_html() );
			echo $this->submission->get_submission_upload_html();
		}
		return ob_get_clean();
	}
}
