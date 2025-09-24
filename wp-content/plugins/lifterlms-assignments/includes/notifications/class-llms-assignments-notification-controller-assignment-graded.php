<?php
/**
 * Notification Controller: Assignment Graded
 *
 * @package LifterLMS_Assignments/Classes/Notifications
 * @since    1.0.0-beta.6
 * @version  1.0.0-beta.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Assignment Graded
 *
 * @since    1.0.0-beta.6
 * @version  1.0.0-beta.6
 */
class LLMS_Assignments_Notification_Controller_Assignment_Graded extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'assignment_graded';

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array(
		'llms_assignment_graded',
	);

	/**
	 * Determines if test notifications can be sent
	 *
	 * @var  bool
	 */
	protected $testable = array(
		'basic' => false,
		'email' => true,
	);

	/**
	 * Callback function called when a private post is updated
	 * Uses dupchecking to prevent duplicates
	 *
	 * @param    int $submission  LLMS_Assignment_Submission.
	 * @return   void
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function action_callback( $submission = null ) {

		$this->post_id = $submission->get_id();
		$this->user_id = $submission->get( 'user_id' );

		if ( $this->user_id && $this->post_id ) {
			$this->send();
		}

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @param    string $subscriber  subscriber type string.
	 * @return   int|false
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	protected function get_subscriber( $subscriber ) {

		$uid = false;

		if ( 'student' === $subscriber ) {

			$sub = llms_get_assignment_submission( $this->post_id );
			if ( $sub->exists() ) {
				$uid = $sub->get( 'user_id' );
			}
		}

		return $uid;

	}

	/**
	 * Get an array of LifterLMS Admin Page settings to send test notifications
	 *
	 * @param    string $type  notification type [basic|email].
	 * @return   array
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function get_test_settings( $type ) {

		$query = new LLMS_Query_Assignments_Submission(
			array(
				'per_page'       => 25,
				'status_exclude' => 'incomplete',
			)
		);

		$options = array(
			'' => '',
		);
		foreach ( $query->get_submissions() as $submission ) {
			$assignment = $submission->get_assignment();
			$student    = $submission->get_student();
			if ( $assignment && $student ) {
				$options[ $submission->get_id() ] = esc_attr(
					sprintf(
						// Translators: %1$s = Assignment Title; %2$s = Student Name; %3$s = Assignment Status.
						__( '"%1$s" by %2$s (%3$s)', 'lifterlms-assignments' ),
						$assignment->get( 'title' ),
						$student->get_name(),
						$submission->get_l10n_status()
					)
				);
			}
		}

		return array(
			array(
				'class'             => 'llms-select2',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-placeholder' => __( 'Select a submission', 'lifterlms-assignments' ),
				),
				'default'           => '',
				'id'                => 'submission_id',
				'desc'              => '<br/>' . __( 'Send yourself a test notification using information from the selected submission.', 'lifterlms-assignments' ),
				'options'           => $options,
				'title'             => __( 'Send a Test', 'lifterlms-assignments' ),
				'type'              => 'select',
			),
		);
	}

	/**
	 * Send a test notification to the currently logged in users
	 * Extending classes should redefine this in order to properly setup the controller with post_id and user_id data
	 *
	 * @param    string $type  notification type [basic|email].
	 * @param    array  $data  array of test notification data as specified by $this->get_test_data().
	 * @return   int|false
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function send_test( $type, $data = array() ) {

		if ( empty( $data['submission_id'] ) ) {
			return;
		}

		$submission    = llms_get_assignment_submission( $data['submission_id'] );
		$this->post_id = $submission->get_id();
		$this->user_id = $submission->get( 'user_id' );

		return parent::send_test( $type );

	}

	/**
	 * Determine what types are supported
	 * Extending classes can override this function in order to add or remove support
	 * 3rd parties should add support via filter on $this->get_supported_types()
	 *
	 * @return   array        associative array, keys are the ID/db type, values should be translated display types.
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	protected function set_supported_types() {
		return array(
			'email' => __( 'Email', 'lifterlms-assignments' ),
		);
	}

	/**
	 * Get the translateable title for the notification
	 * used on settings screens
	 *
	 * @return   string
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function get_title() {
		return __( 'Assignments: Assignment Graded', 'lifterlms-assignments' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @param    string $type  notification type id.
	 * @return   array
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
				break;

		}

		return $options;

	}

}

return LLMS_Assignments_Notification_Controller_Assignment_Graded::instance();
