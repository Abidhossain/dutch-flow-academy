<?php
/**
 * Assignments Reporting
 *
 * @package LifterLMS_Assignments/Admin/Classes
 *
 * @since 1.0.0-beta.2
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assignments Reporting
 *
 * @since 1.0.0-beta.2
 * @since 1.0.0-beta.6 Unknown.
 * @since 1.1.4 Fix user permission checks to view single assignment.
 */
class LLMS_Assignments_Reporting {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.6 Unknown.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'lifterlms_reporting_tabs', array( $this, 'register_tab' ) );
		add_action( 'llms_reporting_content_assignments', array( $this, 'output' ) );
		add_action( 'llms_reporting_assignment_tab_breadcrumbs', array( $this, 'breadcrumbs' ) );

		add_action( 'llms_reporting_assignment_tab_overview_content', array( $this, 'output_single_overview' ), 10, 1 );
		add_action( 'llms_reporting_assignment_tab_submissions_content', array( $this, 'output_single_submissions' ), 10, 1 );

		add_action( 'llms_table_get_student-course_columns', array( $this, 'student_course_table_add_cols' ), 10, 2 );
		add_action( 'llms_table_get_data_student-course', array( $this, 'student_course_table_get_data' ), 10, 3 );

	}

	/**
	 * Output breadcrumbs
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.6 Unknown.
	 * @since 1.1.11 Do not try to print the submission title if no submission was found for the current assignment.
	 *
	 * @return void
	 */
	public function breadcrumbs() {

		$links = array();

		// Single assignment.
		if ( isset( $_GET['assignment_id'] ) ) {
			$assignment = llms_get_post( absint( filter_input( INPUT_GET, 'assignment_id', FILTER_SANITIZE_NUMBER_INT ) ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'overview' ) ] = $assignment->get( 'title' );
		}

		if ( isset( $_GET['submission_id'] ) ) {

			$submission = new LLMS_Assignment_Submission( absint( filter_input( INPUT_GET, 'submission_id', FILTER_SANITIZE_NUMBER_INT ) ) );
			if ( $submission->exists() ) {
				$links[ LLMS_Admin_Reporting::get_stab_url( 'submissions' ) ] = $submission->get_title();
			}
		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . $title . '</a>';

		}

	}

	/**
	 * Output assignment data on the student course table
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @param array  $cols    Existing cols.
	 * @param string $context Display context.
	 * @return array
	 */
	public function student_course_table_add_cols( $cols, $context ) {
		$cols = llms_assoc_array_insert(
			$cols,
			'name',
			'assignment',
			array(
				'title' => __( 'Assignment', 'lifterlms-assignments' ),
			)
		);
		return $cols;
	}

	/**
	 * Output assignment information on the student course table
	 *
	 * @version 1.0.0-beta.6
	 *
	 * @param string      $value  Default value.
	 * @param string      $key    Key name.
	 * @param LLMS_Lesson $lesson LLMS_Lesson.
	 * @return string
	 */
	public function student_course_table_get_data( $value, $key, $lesson ) {
		if ( 'assignment' === $key ) {

			$value = '&ndash;';

			if ( llms_lesson_has_assignment( $lesson ) ) {
				$assignment = llms_lesson_get_assignment( $lesson );
				if ( $assignment ) {
					$title = $assignment->get( 'title' );
					$sub   = llms_student_get_assignment_submission( $assignment->get( 'id' ), absint( filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT ) ) );
					if ( $sub && $sub->exists() ) {
						$value = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $sub->get_review_url() ), $title );
					} else {
						$value = $title;
					}
				}
			}
		}
		return $value;
	}

	/**
	 * Output HTML
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @return void
	 */
	public function output() {

		// Single assignment.
		if ( isset( $_GET['assignment_id'] ) ) {

			$this->output_single();

			// Table view.
		} else {

			$this->output_table();

		}

	}

	/**
	 * Single assignment output.
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.6 Unknown.
	 * @since 1.1.4 Fix user permission checks to view single assignment.
	 * @since 2.0.0 Replaced use of the deprecated `FILTER_SANITIZE_STRING` constant.
	 *
	 * @return void
	 */
	private function output_single() {

		$assignment_id = absint( filter_input( INPUT_GET, 'assignment_id', FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! current_user_can( 'view_lifterlms_reports' ) || ! current_user_can( 'edit_post', $assignment_id ) ) {
			wp_die( __( 'You do not have permission to access this content.', 'lifterlms-assignments' ) );
		}

		$tabs        = apply_filters(
			'llms_reporting_tab_assignment_single_tabs',
			array(
				'overview'    => __( 'Overview', 'lifterlms-assignments' ),
				'submissions' => __( 'Submissions', 'lifterlms-assignments' ),
			)
		);
		$current_tab = llms_filter_input_sanitize_string( INPUT_GET, 'stab' ) ?? 'overview';
		$assignment  = llms_get_post( $assignment_id );

		include 'views/reporting/single.php';

	}

	/**
	 * Output a single assignment overview.
	 *
	 * @since 1.0.0-beta.2
	 * @since 2.0.0 Replaced use of the deprecated `FILTER_SANITIZE_STRING` constant.
	 *
	 * @param LLMS_Assignment $assignment LLMS_Assignment instance.
	 * @return void
	 */
	public function output_single_overview( $assignment ) {

		$data   = new LLMS_Assignments_Data( $assignment->get( 'id' ) );
		$period = llms_filter_input_sanitize_string( INPUT_GET, 'period' ) ?? 'today';
		$data->set_period( $period );
		$periods     = LLMS_Admin_Reporting::get_period_filters();
		$period_text = strtolower( $periods[ $period ] );
		$now         = current_time( 'timestamp' );

		include 'views/reporting/single-overview.php';

	}

	/**
	 * Output a single assignment submission
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.11 Print a notice if no submission was found for the current assignment,
	 *              instead of loading the assignment's submission template.
	 *
	 * @param LLMS_Assignment $assignment LLMS_Assignment instance.
	 * @return void
	 */
	public function output_single_submissions( $assignment ) {

		if ( isset( $_GET['submission_id'] ) ) {

			$submission_id = absint( $_GET['submission_id'] );
			$submission    = new LLMS_Assignment_Submission( $submission_id );

			if ( $submission->exists() ) {
				include 'views/reporting/single-submission.php';
			} else {
				printf(
					// Translators: %1$d = submission id.
					__( 'Could not find any submission with id %1$d for this assignment', 'lifterlms-assignments' ),
					$submission_id
				);
			}
		} else {

			$table = new LLMS_Table_Assignments_Submissions();
			$table->get_results(
				array(
					'assignment_id' => $assignment->get( 'id' ),
				)
			);
			echo $table->get_table_html();

		}

	}

	/**
	 * Output the assignments table
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @return void
	 */
	private function output_table() {

		$table = new LLMS_Table_Assignments();
		$table->get_results();
		echo $table->get_table_html();

	}

	/**
	 * Output a single assignment event
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.0 Unknown.
	 *
	 * @param LLMS_Assignment_Submission $submission LLMS_Assignment_Submission instance.
	 * @return void
	 */
	public static function output_event( $submission ) {

		$url     = add_query_arg(
			array(
				'submission_id' => $submission->get( 'id' ),
				'assignment_id' => $submission->get( 'assignment_id' ),
			),
			LLMS_Admin_Reporting::get_stab_url( 'submissions' )
		);
		$student = $submission->get_student();
		$name    = $student ? $student->get_name() : __( '[Deleted]', 'lifterlms-assignments' );
		$date    = $submission->get( 'updated' );

		switch ( $submission->get( 'status' ) ) {

			case 'pass':
				$color = 'color--green';
				// Translators: %1$s = student name; %2$s = assignment grade.
				$desc = sprintf( __( '%1$s passed the assignment with a %2$d%%', 'lifterlms-assignments' ), $name, $submission->get( 'grade' ) );
				break;

			case 'fail':
				$color = 'color--red';
				// Translators: %1$s = student name; %2$s = assignment grade.
				$desc = sprintf( __( '%1$s failed the assignment with a %2$d%%', 'lifterlms-assignments' ), $name, $submission->get( 'grade' ) );
				break;

			case 'incomplete':
				$color = 'color--purple';
				// Translators: %s = student name.
				$desc = sprintf( __( '%s started the assignment', 'lifterlms-assignments' ), $name );
				$date = $submission->get( 'created' );
				break;

			case 'pending':
				$color = 'color--orange';
				// Translators: %s = student name.
				$desc = sprintf( __( '%s\'s assignment is awaiting review', 'lifterlms-assignments' ), $name );
				break;

		}

		?>
		<div class="llms-reporting-event <?php echo $color; ?>">
			<a href="<?php echo esc_url( $url ); ?>">
				<?php echo $student ? $student->get_avatar( 24 ) : ''; ?>
				<?php echo $desc; ?>
				<time datetime="<?php echo $date; ?>"><?php echo llms_get_date_diff( current_time( 'timestamp' ), $date, 1 ); ?></time>
			</a>
		</div>
		<?php
	}

	/**
	 * Add an Assignments reporting tab
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @param array $orig_tabs Original tabs.
	 * @return array
	 */
	public function register_tab( $orig_tabs ) {

		$index = array_search( 'quizzes', array_keys( $orig_tabs ) );

		$assignments = array(
			'assignments' => __( 'Assignments', 'lifterlms-assignments' ),
		);

		$tabs = array_merge(
			array_slice( $orig_tabs, 0, $index, true ),
			$assignments,
			array_slice( $orig_tabs, $index, count( $orig_tabs ), true )
		);

		return $tabs;

	}

}

return new LLMS_Assignments_Reporting();
