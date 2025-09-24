<?php
/**
 * LifterLMS Assignments template functions, hooks, and filters
 *
 * @package LifterLMS_Assignments/Functions
 *
 * @since 1.0.0-beta.1
 * @version 1.1.12
 */

defined( 'ABSPATH' ) || exit;

add_action( 'llms_before_lesson_buttons', 'llms_assignments_template_take_assignment_button', 10, 2 );
add_action( 'lifterlms_after_start_quiz', 'llms_assignments_after_start_quiz_buttons' );

add_action( 'llms_single_assignment_before_summary', 'llms_assignments_template_return_to_lesson_link', 10 );
add_action( 'llms_single_assignment_before_summary', 'llms_assignments_template_results', 15 );
add_action( 'llms_single_assignment_before_summary', 'llms_assignments_template_video', 20 );
add_action( 'llms_single_assignment_before_summary', 'llms_assignments_template_audio', 25 );


add_action( 'llms_single_assignment_after_summary', 'llms_assignments_template_content', 10 );
add_action( 'llms_single_assignment_after_summary', 'llms_assignments_template_footer', 20 );

/**
 * Determine if the standard lesson mark complete button can be displayed
 *
 * If an assignment is attached, the button should not be displayed because assignments (and maybe quizzes) control the completion.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.2 Unknown.
 *
 * @param bool        $show   Default value.
 * @param LLMS_Lesson $lesson The lesson instance.
 * @return bool
 */
function llms_assignment_show_mark_complete_button( $show, $lesson ) {
	if ( llms_lesson_has_assignment( $lesson ) ) {
		return false;
	}
	return $show;
}
add_filter( 'llms_show_mark_complete_button', 'llms_assignment_show_mark_complete_button', 10, 2 );

/**
 * Add actions before and after single assignment content
 *
 * These actions will be used for attaching other assignment template parts.
 *
 * @since 1.0.0-beta.1
 *
 * @param string $content Default post content from the assignment.
 * @return string
 */
function llms_assignments_get_post_content( $content ) {

	if ( 'llms_assignment' !== get_post_type() ) {
		return $content;
	}

	$assignment = llms_get_post( get_the_id() );
	if ( ! llms_parse_bool( $assignment->get( 'has_description' ) ) ) {
		$content = '';
	}

	$template_before = llms_get_template_part_contents( 'content', 'single-assignment-before' );
	$template_after  = llms_get_template_part_contents( 'content', 'single-assignment-after' );

	ob_start();
	load_template( $template_before, false );
	$output_before = ob_get_clean();

	ob_start();
	load_template( $template_after, false );
	$output_after = ob_get_clean();

	return do_shortcode( $output_before . $content . $output_after );
}

if ( function_exists( 'llms_post_content_init' ) ) { // Defined in LifterLMS core since 4.17.0.
	llms_post_content_init( 'llms_assignments_get_post_content' );
} else {
	add_filter( 'the_content', 'llms_assignments_get_post_content' );
}

/**
 * Add template directory to LifterLMS template overrides
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.4
 *
 * @param array $dirs Existing template directories.
 * @return array
 */
function llms_assignments_template_overrides( $dirs ) {

	$dirs[] = LLMS_ASSIGNMENTS_PLUGIN_DIR . '/templates';
	return $dirs;
}
add_filter( 'lifterlms_theme_override_directories', 'llms_assignments_template_overrides', 10, 1 );

/*
	/$$$$$$$$ /$$   /$$ /$$   /$$  /$$$$$$  /$$$$$$$$ /$$$$$$  /$$$$$$  /$$   /$$  /$$$$$$
	| $$_____/| $$  | $$| $$$ |r $$ /$$__  $$|__  $$__/|_  $$_/ /$$__  $$| $$$ | $$ /$$__  $$
	| $$      | $$  | $$| $$$$| $$| $$  \__/   | $$     | $$  | $$  \ $$| $$$$| $$| $$  \__/
	| $$$$$   | $$  | $$| $$ $$ $$| $$         | $$     | $$  | $$  | $$| $$ $$ $$|  $$$$$$
	| $$__/   | $$  | $$| $$  $$$$| $$         | $$     | $$  | $$  | $$| $$  $$$$ \____  $$
	| $$      | $$  | $$| $$\  $$$| $$    $$   | $$     | $$  | $$  | $$| $$\  $$$ /$$  \ $$
	| $$      |  $$$$$$/| $$ \  $$|  $$$$$$/   | $$    /$$$$$$|  $$$$$$/| $$ \  $$|  $$$$$$/
	|__/       \______/ |__/  \__/ \______/    |__/   |______/ \______/ |__/  \__/ \______/
*/

/**
 * Internal template function used by most assignment template parts
 *
 * Relies on $post global and only outputs a template if assignment is found
 * automatically passes LLMS_Assignment object as $assignment to the specified template.
 *
 * @since 1.0.0-beta.1
 * @since 1.1.10 Pass the student instance to the templates.
 *               Added `$assignment` as second parameter.
 *
 * @param string               $template_path Path for the template.
 * @param LLMS_Assignment|null $assignment    Optional. The assignment instance. If not provided the function will try
 *                                            to retrieve it from the global `$post`.
 * @return void
 */
function _llms_assignments_template_handler( $template_path, $assignment = null ) {

	global $post;

	$assignment = $assignment ? $assignment : llms_get_post( $post );
	if ( ! $assignment ) {
		return;
	}

	$student_id = llms_filter_input( INPUT_GET, 'sid', FILTER_SANITIZE_NUMBER_INT );
	$student    = llms_get_student( $student_id );

	llms_get_template( $template_path, compact( 'assignment', 'student' ) );
}

/**
 * Output a "Start Assignment" on the quiz permalink
 *
 * Only if lesson is incomplete & the lesson has an assignment.
 *
 * @since 1.0.0-beta.1
 *
 * @return void
 */
function llms_assignments_after_start_quiz_buttons() {

	global $post;
	$quiz    = llms_get_post( $post );
	$lesson  = $quiz->get_lesson();
	$student = llms_get_student();

	if ( ! $student ) {
		return;
	}

	// Only output the assignment button if the lesson isn't complete.
	if ( ! llms_is_complete( $student->get_id(), $lesson->get( 'id' ), 'lesson' ) ) {
		llms_assignments_template_take_assignment_button( $lesson, $student );
	}
}

if ( ! function_exists( 'llms_assignments_template_audio' ) ) {
	/**
	 * Output the audio embed for an assignment
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @template templates/assignment/audio.php
	 * @hooked llms_single_assignment_before_summary - 25
	 *
	 * @return void
	 */
	function llms_assignments_template_audio() {
		_llms_assignments_template_handler( 'assignment/audio.php' );
	}
}

if ( ! function_exists( 'llms_assignments_template_content' ) ) {
	/**
	 * Output the main "content" (tasks, uploads, essay) template for the assignmet
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.10 Reduce redundancy code by leveraging `_llms_assignments_template_handler()`.
	 *
	 * @template templates/assignment/content.php
	 * @hooked llms_single_assignment_after_summary - 10
	 *
	 * @return void
	 */
	function llms_assignments_template_content() {

		global $post;

		$assignment = llms_get_post( $post );
		if ( ! $assignment ) {
			return;
		}

		$type = $assignment->get( 'assignment_type' );
		if ( ! $type ) {
			return;
		}

		_llms_assignments_template_handler( 'assignment/content-' . $type . '.php', $assignment );
	}
}

if ( ! function_exists( 'llms_assignments_template_footer' ) ) {
	/**
	 * Output the assignment footer template
	 *
	 * @template templates/assignment/footer.php
	 * @hooked llms_single_assignment_after_summary - 20
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	function llms_assignments_template_footer() {
		_llms_assignments_template_handler( 'assignment/footer.php' );
	}
}

if ( ! function_exists( 'llms_assignments_template_results' ) ) {
	/**
	 * Output assignment results template
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @template templates/assignment/results.php
	 * @hooked llms_single_assignment_before_summary - 15
	 *
	 * @return void
	 */
	function llms_assignments_template_results() {
		_llms_assignments_template_handler( 'assignment/results.php' );
	}
}

if ( ! function_exists( 'llms_assignments_template_return_to_lesson_link' ) ) {
	/**
	 * Output the "Return to Lesson" link template for an assignment
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @template templates/assignment/return-to-lesson-link.php
	 * @hooked llms_single_assignment_before_summary - 10
	 *
	 * @return void
	 */
	function llms_assignments_template_return_to_lesson_link() {
		_llms_assignments_template_handler( 'assignment/return-to-lesson-link.php' );
	}
}

if ( ! function_exists( 'llms_assignments_template_take_assignment_button' ) ) {
	/**
	 * Setup and maybe output the "Take Assignment Button" template
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @template templates/lesson/take-assignment-button.php
	 *
	 * @param LLMS_Lesson        $lesson  Instance of the LLMS_Lesson.
	 * @param LLMS_Student|false $student Instance of the LLMS_Student or false when no logged in user.
	 * @return void
	 */
	function llms_assignments_template_take_assignment_button( $lesson, $student ) {

		// Need a student to proceed.
		if ( ! $student ) {
			return;
		}

		// If there's no assignment attached to the lesson we don't want to proceed.
		$assignment = llms_lesson_get_assignment( $lesson );
		if ( ! $assignment ) {
			return;
		}

		// Include the template and pass the data we have.
		llms_get_template( 'lesson/take-assignment-button.php', compact( 'assignment', 'lesson', 'student' ) );
	}
}

if ( ! function_exists( 'llms_assignments_template_video' ) ) {
	/**
	 * Output the video embed for an assignment
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @template templates/assignment/video.php
	 * @hooked llms_single_assignment_before_summary - 20
	 *
	 * @return void
	 */
	function llms_assignments_template_video() {
		_llms_assignments_template_handler( 'assignment/video.php' );
	}
}

/**
 * Output HTML for a task's completed time
 *
 * @since 1.0.0-beta.2
 * @since 1.1.5 Use `gmdate()` in favor of `date()`.
 *
 * @param LLMS_Assignment_Submission $submission Assignment submission object.
 * @param string                     $task_id    Assignment Task ID.
 * @return void
 */
function llms_assignments_task_completed_time( $submission, $task_id ) {
	$date = $submission->get_task_completed_date( $task_id );
	if ( ! $date ) {
		return;
	}
	?>
	<div class="llms-task-time">
		<?php _e( 'Completed on', 'lifterlms-assignments' ); ?>
		<time datetime="<?php echo gmdate( 'Y-m-d H:i:s', strtotime( $date ) ); ?>">
			<?php echo $date; ?>
		</time>
	</div>
	<?php
}
