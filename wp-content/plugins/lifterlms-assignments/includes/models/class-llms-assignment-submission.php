<?php
/**
 * Assignment Submission Storage Model
 *
 * @package LifterLMS_Assignments/Models
 *
 * @since 1.0.0-beta.1
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assignment_Submission class.
 *
 * @since 1.0.0-beta.1
 * @since 1.1.0-beta.2 Unknown.
 * @since 1.1.0-beta.5 Unknown.
 * @since 1.1.0-beta.6 Unknown.
 * @since 1.1.0 Unknown.
 * @since 1.1.6 Added `upload()` method for uploading assignment files.
 *              The `delete()` method now deletes uploaded files (if they exist).
 *              Added `delete_upload()` method to delete uploaded files from the assignment.
 */
class LLMS_Assignment_Submission extends LLMS_Abstract_Database_Store {

	/**
	 * Array of table columns => format
	 *
	 * @var  array
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.2
	 */
	protected $columns = array(
		'created'       => '%s',
		'updated'       => '%s',
		'submitted'     => '%s',
		'user_id'       => '%d',
		'assignment_id' => '%d',
		'status'        => '%s',
		'grade'         => '%f',
		'submission'    => '%s',
		'remarks'       => '%s',
	);

	/**
	 * Database Table Name
	 *
	 * @var  string
	 */
	protected $table = 'submissions';

	/**
	 * Database Table Prefix
	 *
	 * @var  string
	 */
	protected $table_prefix = 'lifterlms_assignments_';

	/**
	 * The record type
	 * Used for filters/actions
	 * Should be defined by extending classes
	 *
	 * @var  string
	 */
	protected $type = 'assignment_submission';

	/**
	 * Constructor
	 *
	 * @param    mixed $item   (int)   Item ID.
	 *                         (array) Array of item data, useful when creating a item.
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function __construct( $item = null ) {

		if ( is_numeric( $item ) ) {

			$this->id = $item;
			$this->hydrate();

		} elseif ( is_array( $item ) ) {

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			$this->setup( $item );

		}

		parent::__construct();
	}

	/**
	 * Delete the object from the database.
	 *
	 * @since 1.1.6
	 * @since 2.0.0 Change the user's lesson status to 'incomplete'.
	 *
	 * @return bool True if this submission is successfully deleted from the database, else false.
	 */
	public function delete() {

		$this->hydrate();

		// Delete the upload.
		$this->delete_upload();

		$this->mark_lesson_incomplete();

		return parent::delete();
	}

	/**
	 * Delete uploaded attachments (if they exist.)
	 *
	 * @since 1.1.6
	 *
	 * @param boolean $force If `true` bypasses the trash.
	 * @return bool|null `null` if there's no upload on the submission or it wasn't an upload assignment type.
	 *                   `true` on successful deletion.
	 *                   `false` on error.
	 */
	public function delete_upload( $force = true ) {

		$submission = $this->get( 'submission' );

		// No upload or not an upload assignment type.
		if ( ! $submission || ! is_numeric( $submission ) || 'attachment' !== get_post_type( $submission ) ) {
			return null;
		}

		// Update the submission.
		$this->set( 'submission', '', true );

		// Delete it.
		return wp_delete_attachment( $submission, $force ) ? true : false;
	}

	/**
	 * Get assignment object for the item's assignment.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_Assignment|WP_Post|null|false
	 */
	public function get_assignment() {
		return llms_get_post( $this->get( 'assignment_id' ) );
	}

	/**
	 * Retrieve a formatted date
	 *
	 * @param    string $key     date field key: [created|updated].
	 * @param    string $format  output date format (PHP), uses WordPress format options if none provided.
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_date( $key, $format = null ) {

		$date   = strtotime( $this->get( $key ) );
		$format = ! $format ? get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) : $format;
		return date_i18n( $format, $date );
	}

	/**
	 * Retrieve the translated status name for the submission
	 *
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_l10n_status() {

		$statuses = llms_get_assignment_submission_statuses();
		$status   = $this->get( 'status' );
		if ( isset( $statuses[ $status ] ) ) {
			return $statuses[ $status ];
		}

		return $status;
	}

	/**
	 * Retrieve a URL to review the submission on the admin panel
	 *
	 * @return   string
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function get_review_url() {

		return add_query_arg(
			array(
				'tab'           => 'assignments',
				'stab'          => 'submissions',
				'assignment_id' => $this->get( 'assignment_id' ),
				'submission_id' => $this->get( 'id' ),
			),
			admin_url( 'admin.php?page=llms-reporting' )
		);
	}

	/**
	 * Get student object for the item's user
	 *
	 * @return   LLMS_Student
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_student() {
		return llms_get_student( $this->get( 'user_id' ) );
	}

	/**
	 * Retrieve the submitted data
	 * Handles unfurling tasks
	 *
	 * @return   mixed
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_submission() {

		$assignment = $this->get_assignment();
		if ( 'tasklist' === $assignment->get( 'assignment_type' ) ) {
			return $this->get_tasks();
		}

		return $this->get( 'submission' );
	}

	/**
	 * Retrieve the submission html for an upload assignment
	 * Either an <img> or download <a>
	 *
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.5
	 */
	public function get_submission_upload_html() {

		$submission = $this->get_submission();

		// if saved value isn't numeric something is weird.
		if ( ! is_numeric( $submission ) ) {
			return $submission;
		}

		// return <img> for an image.
		if ( wp_attachment_is_image( $submission ) ) {

			return wp_get_attachment_image( $submission, 'full' );

		}

		// otherwise return a download <a>.
		$upload_url = wp_get_attachment_url( $submission );
		$name       = basename( $upload_url );

		if ( class_exists( 'LLMS_Media_Protector' ) && preg_match( '/[?&]llms_media_id=(\d+)/', $name, $matches ) ) {
			$media_id        = $matches[1];
			$media_protector = new LLMS_Media_Protector();
			$name            = basename( $media_protector->get_media_path( $media_id ) );
		}

		return '<a href="' . esc_url( $upload_url ) . '" download="' . $name . '">' . $name . '</a>';
	}

	/**
	 * Get a single task by task id
	 *
	 * @param    string $id  task id.
	 * @return   array|false
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_task( $id ) {
		$tasks = $this->get_tasks();
		if ( isset( $tasks[ $id ] ) ) {
			return $tasks[ $id ];
		}
		return false;
	}

	/**
	 * Retrieve the status of a task by id
	 *
	 * @param    string $id  task id.
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_task_status( $id ) {
		$task = $this->get_task( $id );
		if ( ! $task || ! isset( $task['status'] ) ) {
			return false;
		}
		return $task['status'];
	}

	/**
	 * Retrieve the completed date of an individual task
	 *
	 * @param    string $id      task id.
	 * @param    string $format  (optional) date format.
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_task_completed_date( $id, $format = '' ) {

		$task = $this->get_task( $id );
		if ( ! $task || ! isset( $task['updated'] ) ) {
			return '';
		}

		$format = $format ? $format : get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		return date_i18n( $format, strtotime( $task['updated'] ) );
	}

	/**
	 * Retrieve all tasks
	 *
	 * @return   array
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_tasks() {
		$submission = $this->get( 'submission' );
		if ( ! $submission ) {
			return array();
		}
		return maybe_unserialize( $submission );
	}

	/**
	 * Retrieve a "title" for the submission
	 *
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.1.0
	 */
	public function get_title() {
		$name = $this->get_student() ? $this->get_student()->get_name() : __( '[Deleted]', 'lifterlms-assignments' );
		// Translators: %s = The name of the student.
		return sprintf( __( '%s\'s Assignment Submission', 'lifterlms-assignments' ), $name );
	}

	/**
	 * Save assignment grades / remarks
	 *
	 * @param    int|float $grade    grade.
	 * @param    string    $remarks  instructor remarks.
	 * @return   boolean
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.6
	 */
	public function grade( $grade, $remarks = '' ) {

		$assignment = $this->get_assignment();

		$status = ( $grade >= $assignment->get( 'passing_percentage' ) ) ? 'pass' : 'fail';

		$this->set( 'grade', $grade );
		$this->set( 'remarks', $remarks );
		$this->set( 'status', $status );
		$this->save();

		do_action( 'llms_assignment_graded', $this );

		return true;
	}

	/**
	 * Determine if an assignment is completed
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.6 Use strict comparison.
	 *
	 * @return bool
	 */
	public function is_complete() {

		return in_array( $this->get( 'status' ), array( 'pass', 'pending', 'fail' ), true );
	}

	/**
	 * Determine if an assignment has a passing grade.
	 *
	 * @since 1.0.0-beta.5
	 *
	 * @return bool
	 */
	public function is_passing() {
		return ( 'pass' === $this->get( 'status' ) );
	}

	/**
	 * Validates the submission to ensure it meets the criteria to allow an upload.
	 *
	 * 1) Must have an attached assignment.
	 * 2) Assignment must be an "upload" assignment type.
	 * 3) Must have an attached student.
	 *
	 * @since 1.1.6
	 *
	 * @return boolean|WP_Error WP_Error if upload is not permitted, otherwise `true`.
	 */
	protected function is_upload_permitted() {

		// No assignment.
		$assignment = $this->get_assignment();
		if ( ! $assignment ) {
			return new WP_Error( 'llms_assignment_sumbission_missing_assignment', __( 'No assignment found.', 'lifterlms-assignments' ) );
		}

		// Invalid assignment type.
		if ( 'upload' !== $assignment->get( 'assignment_type' ) ) {
			return new WP_Error( 'llms_assignment_sumbission_invalid_type', __( 'Uploads not permitted.', 'lifterlms-assignments' ) );
		}

		// No student.
		$student = $this->get_student();
		if ( ! $student ) {
			return new WP_Error( 'llms_assignment_sumbission_missing_student', __( 'No student found.', 'lifterlms-assignments' ) );
		}

		return true;
	}

	/**
	 * Marks a submission as incomplete
	 *
	 * @return   void
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function mark_incomplete() {
		$this->set( 'status', 'incomplete' );
		$this->set( 'submitted', null );
		$this->set( 'grade', null );
		$this->save();
	}

	/**
	 * Change the user's completion status of the assignment's lesson to 'incomplete'.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if successful, else false.
	 */
	private function mark_lesson_incomplete() {

		$assignment = $this->get_assignment();
		if ( ! is_a( $assignment, 'LLMS_Post_Model' ) ) {
			return false;
		}
		$trigger = 'assignment_' . $assignment->get( 'id' );

		$student = $this->get_student();
		if ( ! is_a( $student, 'LLMS_Student' ) ) {
			return false;
		}

		$lesson = $assignment->get_lesson();
		if ( ! is_a( $lesson, 'LLMS_Post_Model' ) ) {
			return false;
		}
		$lesson_id = $lesson->get( 'id' );
		$type      = $lesson->get( 'type' );

		return $student->mark_incomplete( $lesson_id, $type, $trigger );
	}

	/**
	 * Upload a file
	 *
	 * @since 1.1.6
	 *
	 * @param string $file_id Index/name of the file in the `$_FILES` array.
	 * @return WP_Error|int WP_Post ID of the attachment on success.
	 *                      WP_Error on failure.
	 */
	public function upload( $file_id ) {

		$valid = $this->is_upload_permitted();
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// Missing file.
		if ( empty( $_FILES[ $file_id ] ) ) {
			return new WP_Error( 'llms_assignment_sumbission_upload_no_file', __( 'Missing upload file.', 'lifterlms-assignments' ) );
		}

		$assignment = $this->get_assignment();

		// Validate filetype.
		if ( llms_parse_bool( $assignment->get( 'enable_allowed_mimes' ) ) ) {

			$ext = ! empty( $_FILES[ $file_id ]['name'] ) ? pathinfo( sanitize_text_field( wp_unslash( $_FILES[ $file_id ]['name'] ) ), PATHINFO_EXTENSION ) : '';

			$allowed = array();
			foreach ( $assignment->get( 'allowed_mimes' ) as $type_string ) {
				$allowed = array_merge( $allowed, explode( '|', $type_string ) );
			}

			// Invalid filetype.
			if ( ! in_array( $ext, $allowed, true ) ) {
				return new WP_Error( 'llms_assignment_sumbission_upload_invalid_file', __( 'Invalid upload.', 'lifterlms-assignments' ) );
			}
		}

		$student = $this->get_student();

		/**
		 * Filter overrides passed to `media_handle_upload()` during an assignment submission file upload.
		 *
		 * This filter is mainly used to disable some file tests (like `is_uploaded_file()`) encountered in `_wp_handle_upload()`
		 * which causes issues when faking an HTTP file upload by modifying the $_FILES global like we do during phpunit testing.
		 *
		 * @since 1.1.6
		 *
		 * @param array                      $overrides  Array of overrides.
		 * @param LLMS_Assignment_Submission $submission Submission object.
		 */
		$overrides = apply_filters(
			'llms_assignment_submission_media_handle_upload_overrides',
			array(
				'test_form' => false,
			),
			$this
		);

		/**
		 * Customize the name of the attachment upload file.
		 *
		 * @since 1.1.6
		 *
		 * @param string                     $title      Default upload file title.
		 * @param LLMS_Assignment_Submission $submission Submission object.
		 */
		$title = apply_filters(
			'llms_assignment_submission_upload_title',
			// Translators: %1$s = Assignment title; %2$s = Student name.
			sprintf( esc_html__( '%1$s assignment by %2$s', 'lifterlms-assignments' ), wp_strip_all_tags( $assignment->get( 'title' ) ), $student->get_name() ),
			$this
		);

		// Upload the situation.
		$attachment_id = media_handle_upload(
			$file_id,
			$assignment->get( 'id' ),
			array(
				'post_title' => $title,
			),
			$overrides
		);

		// Success!
		if ( ! is_wp_error( $attachment_id ) ) {
			$this->set( 'submission', $attachment_id, true );
		}

		// Attachment ID or WP_Error on failure.
		return $attachment_id;
	}

	/**
	 * Submit a review and trigger completion-related actions
	 *
	 * @param    string $submission  student submission contents.
	 * @return   boolean
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function submit( $submission = '' ) {

		$assignment = $this->get_assignment();

		$status = 'pending';
		$type   = $assignment->get( 'assignment_type' );

		if ( 'tasklist' === $type ) {
			$status = 'pass';
			$this->set( 'grade', 100 );
		} elseif ( 'essay' === $type ) {
			$status = 'pending';
			$this->set( 'submission', $submission );
		}

		$this->set( 'submitted', current_time( 'mysql' ) );
		$this->set( 'status', $status );
		$this->save();

		do_action( 'llms_assignment_submitted', $this );

		return true;
	}

	/**
	 * Update a task status by ID
	 *
	 * @param    string  $id      task ID.
	 * @param    boolean $status  task status (true = complete; false = incomplete).
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function update_task( $id, $status ) {

		$assignment = $this->get_assignment();
		if ( ! $assignment ) {
			return false;
		}

		$tasks                   = $this->get_tasks();
		$tasks[ $id ]['status']  = $status;
		$tasks[ $id ]['updated'] = current_time( 'mysql' );

		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$this->set( 'submission', serialize( $tasks ), true );

		return true;
	}
}
