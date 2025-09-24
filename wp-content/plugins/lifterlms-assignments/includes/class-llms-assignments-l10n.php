<?php
/**
 * Localize JS strings
 * This file should not be edited directly
 * It is compiled automatically via the gulp task `pot-js`
 * See the lifterlms-lib-tasks package for more information
 *
 * @package  lifterlms-assignments/Classes/Localization
 * @since    1.0.0-beta.2
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Localize JS strings
 */
class LLMS_Assignments_L10n {

	/**
	 * Constructor
	 *
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function __construct() {
		add_filter( 'lifterlms_js_l10n', array( $this, 'get_strings' ) );
	}

	/**
	 * Get strings to be passed to LifterLMS l10n class
	 *
	 * @param    array $strings existing strings from core / 3rd parties.
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.1.0
	 */
	public function get_strings( $strings ) {
		// phpcs:disable
		return array_merge(
			$strings,
			array(

				/**
				 * File: assets/js/llms-assignments-builder.js.
				 *
				 * @since    1.0.0-beta.1
				 * @version  1.1.0
				 */
				'General Settings'                       => esc_html__( 'General Settings', 'lifterlms-assignments' ),
				'Video Embed URL'                        => esc_html__( 'Video Embed URL', 'lifterlms-assignments' ),
				'Audio Embed URL'                        => esc_html__( 'Audio Embed URL', 'lifterlms-assignments' ),
				'Description'                            => esc_html__( 'Description', 'lifterlms-assignments' ),
				'Assignment Type'                        => esc_html__( 'Assignment Type', 'lifterlms-assignments' ),
				'Task List'                              => esc_html__( 'Task List', 'lifterlms-assignments' ),
				'Essay'                                  => esc_html__( 'Essay', 'lifterlms-assignments' ),
				'Upload'                                 => esc_html__( 'Upload', 'lifterlms-assignments' ),
				'Passing Percentage'                     => esc_html__( 'Passing Percentage', 'lifterlms-assignments' ),
				'Minimum grade required to pass the assignment.' => esc_html__( 'Minimum grade required to pass the assignment.', 'lifterlms-assignments' ),
				'Assignment Weight'                      => esc_html__( 'Assignment Weight', 'lifterlms-assignments' ),
				'POINTS'                                 => esc_html__( 'POINTS', 'lifterlms-assignments' ),
				'Determines the weight of the assignment when calculating the grade of the lesson.' => esc_html__( 'Determines the weight of the assignment when calculating the grade of the lesson.', 'lifterlms-assignments' ),
				'Minimum Word Count'                     => esc_html__( 'Minimum Word Count', 'lifterlms-assignments' ),
				'Maximum Word Count'                     => esc_html__( 'Maximum Word Count', 'lifterlms-assignments' ),
				'Restrict File Types'                    => esc_html__( 'Restrict File Types', 'lifterlms-assignments' ),
				'New Assignment'                         => esc_html__( 'New Assignment', 'lifterlms-assignments' ),
				'assignments'                            => esc_html__( 'assignments', 'lifterlms-assignments' ),
				'assignment'                             => esc_html__( 'assignment', 'lifterlms-assignments' ),
				'tasks'                                  => esc_html__( 'tasks', 'lifterlms-assignments' ),
				'task'                                   => esc_html__( 'task', 'lifterlms-assignments' ),
				'Click "Add Task" to get started'        => esc_html__( 'Click "Add Task" to get started', 'lifterlms-assignments' ),
				'Quiz Weight'                            => esc_html__( 'Quiz Weight', 'lifterlms-assignments' ),
				'Determines the weight of the quiz when calculating the grade of the lesson.' => esc_html__( 'Determines the weight of the quiz when calculating the grade of the lesson.', 'lifterlms-assignments' ),

				/**
				 * File: assets/js/llms-assignments.js.
				 *
				 * @since    1.0.0-beta.1
				 * @version  1.0.0-beta.2
				 */
				'word'                                   => esc_html__( 'word', 'lifterlms-assignments' ),
				'words'                                  => esc_html__( 'words', 'lifterlms-assignments' ),
				'Minimum'                                => esc_html__( 'Minimum', 'lifterlms-assignments' ),
				'Maximum'                                => esc_html__( 'Maximum', 'lifterlms-assignments' ),
				'Error Removing Upload'                  => esc_html__( 'Error Removing Upload', 'lifterlms-assignments' ),
				'Error submitting assignment, please try again.' => esc_html__( 'Error submitting assignment, please try again.', 'lifterlms-assignments' ),
				'Error saving assignment, please try again.' => esc_html__( 'Error saving assignment, please try again.', 'lifterlms-assignments' ),
				'Error updating task, please try again.' => esc_html__( 'Error updating task, please try again.', 'lifterlms-assignments' ),
				'Error Uploading File'                   => esc_html__( 'Error Uploading File', 'lifterlms-assignments' ),
				'Invalid filetype selected.'             => esc_html__( 'Invalid filetype selected.', 'lifterlms-assignments' ),

			)
		);
		// phpcs:enable
	}

}

return new LLMS_Assignments_L10n();
