<?php
/**
 * AJAX Functios for the add-on
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.1
 * @version 2.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX Functios for the add-on
 */
class LLMS_Assignments_AJAX {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function __construct() {

		$actions = array(
			'submit_assignment' => false,
			'update_essay'      => false,
			'update_task'       => false,
			'upload_file'       => false,
			'upload_remove'     => false,
		);

		// Register all ajax functions.
		foreach ( $actions as $action => $nopriv ) {

			add_action( 'wp_ajax_llms_assignments_' . $action, array( $this, $action ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_llms_assignments_' . $action, array( $this, $action ) );
			}
		}
	}

	/**
	 * Verify the parameters for an ajax call
	 *
	 * Responds with an error if any errors are encountered.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.6 Unknown.
	 *
	 * @param array $params An array of param_key => validation function.
	 * @return void
	 */
	private function verify_parameters( $params ) {
		// phpcs:disable WordPress.Security.NonceVerification
		foreach ( $params as $var => $func ) {

			if ( ! isset( $_POST[ $var ] ) ) {
				// Translators: %s = variable name.
				$this->send_response( sprintf( esc_html__( 'Missing required paramater: "%s"', 'lifterlms-assignments' ), $var, false ) );
			}

			$val = sanitize_text_field( wp_unslash( $_POST[ $var ] ) );
			if ( false === $func( $val ) ) {
				// Translators: %s = variable name.
				$this->send_response( sprintf( esc_html__( 'Invalid value submitted for parameter "%s"', 'lifterlms-assignments' ), $var, false ) );
			}
		}
		// phpcs:enable
	}

	/**
	 * Handle assignment submission.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.2 Unknown.
	 * @since 2.0.1 Strip slashes prior to saving submissions.
	 *
	 * @return array
	 */
	public function submit_assignment() {

		check_ajax_referer( 'llms-ajax', '_ajax_nonce' );
		$this->verify_parameters(
			array(
				'assignment_id' => 'absint',
				'submission'    => 'is_string',
			)
		);

		$data = $_POST;

		$submission = llms_student_get_assignment_submission( $data['assignment_id'] );
		if ( $submission ) {

			if ( $submission->submit( wp_kses_post( stripslashes( $data['submission'] ) ) ) ) {
				return $this->send_response( 'success', true );
			}
		}

		return $this->send_response( __( 'Unable to submit assignment.', 'lifterlms-assignments' ), false );
	}

	/**
	 * Update an tasklist assignment task.
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.12 Remove reference to undefined variable `$date_html` and fix return message.
	 * @since 2.0.1 Strip slashes prior to saving submissions.
	 *
	 * @return array
	 */
	public function update_essay() {

		check_ajax_referer( 'llms-ajax', '_ajax_nonce' );
		$this->verify_parameters(
			array(
				'assignment_id' => 'absint',
				'content'       => 'is_string',
			)
		);

		$data = $_POST;

		$submission = llms_student_get_assignment_submission( $data['assignment_id'] );
		if ( $submission ) {
			if ( $submission->set( 'submission', wp_kses_post( stripslashes( $data['content'] ) ), true ) ) {
				return $this->send_response( 'success', true );
			}
		}

		return $this->send_response( __( 'Unable to update essay.', 'lifterlms-assignments' ), false );
	}

	/**
	 * Update an tasklist assignment task
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function update_task() {

		check_ajax_referer( 'llms-ajax', '_ajax_nonce' );
		$this->verify_parameters(
			array(
				'assignment_id' => 'absint',
				'task_id'       => 'sanitize_text_field',
				'status'        => 'absint',
			)
		);

		$data = $_POST;

		$submission = llms_student_get_assignment_submission( $data['assignment_id'] );
		if ( $submission ) {
			if ( $submission->update_task( $data['task_id'], $data['status'] ) ) {
				ob_start();
				llms_assignments_task_completed_time( $submission, $data['task_id'] );
				$date_html = ob_get_clean();
				return $this->send_response( 'success', true, compact( 'date_html' ) );
			}
		}

		return $this->send_response( __( 'Unable to update task.', 'lifterlms-assignments' ), false );
	}

	/**
	 * Upload a file to an upload assignment
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.6 Unknown.
	 *
	 * @return array
	 */
	public function upload_file() {

		check_ajax_referer( 'llms_assignment_upload', 'nonce' );

		$this->verify_parameters(
			array(
				'assignment_id' => 'is_numeric',
			)
		);

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$student = llms_get_student();
		if ( ! $student ) {
			return $this->send_response( __( 'Invalid user.', 'lifterlms-assignments' ), false );
		}

		$assignment_id = absint( filter_input( INPUT_POST, 'assignment_id', FILTER_SANITIZE_NUMBER_INT ) );
		$assignment    = llms_get_post( $assignment_id );
		if ( ! $assignment ) {
			return $this->send_response( __( 'Invalid assignment.', 'lifterlms-assignments' ), false );
		}

		$submission = llms_student_get_assignment_submission( $assignment_id, $student );
		if ( $submission->get_submission() ) {
			return $this->send_response( __( 'File already uploaded.', 'lifterlms-assignments' ), false );
		}

		// If filetype restrictions are enabled, ensure the file is allowed.
		if ( llms_parse_bool( $assignment->get( 'enable_allowed_mimes' ) ) ) {

			$ext = ! empty( $_FILES['file']['name'] ) ? pathinfo( sanitize_text_field( wp_unslash( $_FILES['file']['name'] ) ), PATHINFO_EXTENSION ) : '';

			$allowed = array();
			foreach ( $assignment->get( 'allowed_mimes' ) as $type_string ) {
				$allowed = array_merge( $allowed, explode( '|', $type_string ) );
			}

			if ( ! in_array( $ext, $allowed ) ) {
				return $this->send_response( __( 'Invalid filetype.', 'lifterlms-assignments' ), false );
			}
		}

		// Upload the situation.
		$post_data = array(
			// Translators: %1$s = Assignment title; %2$s = Student name.
			'post_title' => sprintf(
				esc_html__( '%1$s assignment by %2$s', 'lifterlms-assignments' ),
				strip_tags( $assignment->get( 'title' ) ),
				$student->get_name()
			),
		);

		if ( class_exists( 'LLMS_Media_Protector' ) ) {
			$protector = new LLMS_Media_Protector();
			$id        = $protector->handle_upload( 'file', $assignment_id, 'llms_assignment_authorize_media_view', $post_data );
		} else {
			$id = media_handle_upload(
				'file',
				$assignment_id,
				$post_data
			);
		}

		if ( is_wp_error( $id ) ) {
			return $this->send_response( $id->get_error_message(), false, $id );
		}

		$submission->set( 'submission', $id, true );

		return $this->send_response( __( 'Success', 'lifterlms-assignments' ), true );
	}

	/**
	 * Allow students to remove a file after uploading it
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.6 Unknown.
	 * @since 1.1.12 Fixed use of undefined `$force_delete` variable, and always force attachments deletion rather than trashing.
	 *
	 * @return array
	 */
	public function upload_remove() {

		check_ajax_referer( 'llms-ajax', '_ajax_nonce' );
		$this->verify_parameters(
			array(
				'assignment_id' => 'is_numeric',
			)
		);

		$student = llms_get_student();
		if ( ! $student ) {
			return $this->send_response( __( 'Invalid user.', 'lifterlms-assignments' ), false );
		}

		$assignment_id = absint( filter_input( INPUT_POST, 'assignment_id', FILTER_SANITIZE_NUMBER_INT ) );
		$assignment    = llms_get_post( $assignment_id );
		if ( ! $assignment ) {
			return $this->send_response( __( 'Invalid assignment.', 'lifterlms-assignments' ), false );
		}

		$submission = llms_student_get_assignment_submission( $assignment_id, $student );
		if ( $submission->is_complete() ) {
			return $this->send_response( __( 'Upload could not be deleted.', 'lifterlms-assignments' ), false );
		}

		if ( false === wp_delete_attachment( $submission->get_submission(), true ) ) {
			return $this->send_response( __( 'Upload could not be deleted.', 'lifterlms-assignments' ), false );
		}

		$submission->set( 'submission', '', true );

		return $this->send_response( __( 'Success', 'lifterlms-assignments' ), true );
	}

	/**
	 * Send a JSON response (&die)
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string  $message Message.
	 * @param boolean $success Success.
	 * @param array   $data    Data to send with the message.
	 * @return void
	 */
	private function send_response( $message, $success = true, $data = array() ) {

		wp_send_json(
			array(
				'data'    => $data,
				'message' => $message,
				'success' => $success,
			)
		);
	}
}

return new LLMS_Assignments_AJAX();
