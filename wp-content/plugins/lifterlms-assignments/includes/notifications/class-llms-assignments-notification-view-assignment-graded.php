<?php
/**
 * Assignment Notification View: Assignment Graded
 *
 * @package LifterLMS_Assignments/Classes/Notifications
 *
 * @since 1.0.0-beta.6
 * @version 1.1.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assignment Notification View: Assignment Graded
 *
 * @since 1.0.0-beta.6
 * @since 1.1.3 Use `created` for start date and `submitted` for end date.
 * @since 1.1.7 Added `get_object()` method.
 */
class LLMS_Assignments_Notification_View_Assignment_Graded extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 *
	 * @var string
	 */
	public $trigger_id = 'assignment_graded';

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
	 * @since 1.1.7
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
			'ASSIGNMENT_TITLE' => __( 'Assignment', 'lifterlms-assignments' ),
			'LESSON_TITLE'     => __( 'Lesson', 'lifterlms-assignments' ),
			'COURSE_TITLE'     => __( 'Course', 'lifterlms-assignments' ),
			'GRADE'            => __( 'Grade', 'lifterlms-assignments' ),
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
		{{REMARKS}}
		{{DIVIDER}}
		<p><a href="{{REVIEW_URL}}" style="<?php echo $btn_style; ?>"><?php _e( 'Review your submission', 'lifterlms-assignments' ); ?></a></p>
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
			'{{ASSIGNMENT_TITLE}}' => __( 'Assignment Title', 'lifterlms-assignments' ),
			'{{COURSE_TITLE}}'     => __( 'Course Title', 'lifterlms-assignments' ),
			'{{END_DATE}}'         => __( 'End Date', 'lifterlms-assignments' ),
			'{{GRADE}}'            => __( 'Grade', 'lifterlms-assignments' ),
			'{{LESSON_TITLE}}'     => __( 'Lesson Title', 'lifterlms-assignments' ),
			'{{REMARKS}}'          => __( 'Instructor Remarks', 'lifterlms-assignments' ),
			'{{REVIEW_URL}}'       => __( 'Review URL', 'lifterlms-assignments' ),
			'{{START_DATE}}'       => __( 'Start Date', 'lifterlms-assignments' ),
			'{{STATUS}}'           => __( 'Status', 'lifterlms-assignments' ),
			'{{STUDENT_NAME}}'     => __( 'Student Name', 'lifterlms-assignments' ),
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

			case '{{GRADE}}':
				$code = $this->submission->get( 'grade' ) . '%';
				break;

			case '{{LESSON_TITLE}}':
				$code = '';
				if ( isset( $this->lesson ) ) {
					$code = $this->lesson->get( 'title' );
				}
				break;

			case '{{REMARKS}}':
				$code = wpautop( $this->submission->get( 'remarks' ) );
				if ( ! $code ) {
					$code = __( 'Your instructor did not leave any remarks.', 'lifterlms-assignments' );
				}
				break;

			case '{{REVIEW_URL}}':
				$code = '';
				if ( isset( $this->assignment ) ) {
					$code = get_permalink( $this->assignment->get( 'id' ) );
				}
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
		// Translators: %1$s = The assignment's title.
		return sprintf( __( 'Your assignment "%1$s" has been reviewed', 'lifterlms-assignments' ), '{{ASSIGNMENT_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Assignment Review Details', 'lifterlms-assignments' );
	}

}
