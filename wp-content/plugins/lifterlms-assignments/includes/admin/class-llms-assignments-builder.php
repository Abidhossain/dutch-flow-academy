<?php
/**
 * Assignments Builder
 *
 * @package LifterLMS_Assignments/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.1.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assignments Builder
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.2 Added JS script localization.
 * @since 1.0.0-beta.4 Updated the `update()` method.
 * @since 1.1.6 Delete assignments instead of trashing them.
 */
class LLMS_Assignments_Builder {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.2 Added JS script localization.
	 * @since 1.1.6 Delete assignments instead of trashing them.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'llms_builder_assignment_settings', array( $this, 'settings_template' ) );

		add_action( 'llms_after_builder', array( $this, 'localize_scripts' ) );
		add_action( 'llms_after_builder', array( $this, 'output_templates' ) );

		add_action( 'llms_builder_detach_llms_assignment', array( $this, 'detach' ), 10, 1 );

		add_filter( 'llms_builder_update_lesson', array( $this, 'update' ), 10, 4 );

		add_filter( 'llms_builder_update_lesson_skip_props', array( $this, 'update_skip_props' ) );

		add_filter( 'llms_builder_detachable_post_types', array( $this, 'enable_trash_and_detach' ) );
		add_filter( 'llms_builder_trashable_post_types', array( $this, 'enable_trash_and_detach' ) );
		add_filter( 'llms_builder_trash_custom_item', array( $this, 'trash_task' ), 10, 3 );

		// Assignments are deleted (not trashed).
		add_filter( 'llms_builder_llms_assignment_force_delete', '__return_true' );

	}

	/**
	 * When an assignment is detached from a lesson, update lesson & assignment meta data
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj $assignment Instance of the LLMS_Assignment.
	 * @return void
	 */
	public function detach( $assignment ) {

		$parent = $assignment->get_lesson();
		if ( $parent ) {
			$parent->set( 'assignment_enabled', 'no' );
			$parent->set( 'assignment', '' );
			$assignment->set( 'lesson_id', 0 );
		}

	}

	/**
	 * Filter trashable post types.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string[] $types Post types list.
	 * @return string[]
	 */
	public function enable_trash_and_detach( $types ) {
		$types[] = 'llms_assignment';
		return $types;
	}

	/**
	 * Pass settings JS
	 *
	 * @since 1.0.0-beta.2
	 *
	 * @return void
	 */
	public function localize_scripts() {

		$data = apply_filters(
			'llms_assignments_builder_settings',
			array(
				'markers'    => range( 'A', 'Z' ),
				'mime_types' => get_allowed_mime_types(),
			)
		);

		echo '<script>window.llms_builder.assignments = ' . json_encode( $data ) . ';</script>';
	}

	/**
	 * Output builder JS templates
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function output_templates() {

		include LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/views/task.php';

	}

	/**
	 * Handle deleting assignment tasks
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param null  $null Null value.
	 * @param array $res  Default result array with error msg.
	 * @param mixed $id   Model ID.
	 * @return null|array
	 */
	public function trash_task( $null, $res, $id ) {

		if ( false === strpos( $id, ':' ) ) {
			return $null;
		}

		$parts      = explode( ':', $id );
		$assignment = llms_get_post( $parts[0] );
		if ( ! $assignment || ! is_a( $assignment, 'LLMS_Assignment' ) ) {
			return $null;
		}

		if ( $assignment->delete_task( $parts[1] ) ) {
			unset( $res['error'] );
		}

		return $res;

	}

	/**
	 * Output settings template on the builder
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function settings_template() {
		include LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/views/settings.php';
	}

	/**
	 * Save assignment data during builder saves
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Unknown.
	 *
	 * @param array $res         Result array.
	 * @param array $lesson_data Raw lesson data.
	 * @param obj   $lesson      Instance of the LLMS_Lesson.
	 * @param bool  $created     If true, newly created lesson, if false its an update.
	 * @return array
	 */
	public function update( $res, $lesson_data, $lesson, $created ) {

		// We're only interested in lessons with assignments.
		if ( empty( $lesson_data['assignment'] ) ) {
			return $res;
		}

		$assignment_res = array_merge(
			$res['assignment'],
			array(
				'orig_id' => $lesson_data['assignment']['id'],
			)
		);

		// Create a assignment.
		if ( LLMS_Admin_Builder::is_temp_id( $lesson_data['assignment']['id'] ) ) {

			$assignment = new LLMS_Assignment( 'new' );

			// Update existing assignment.
		} else {

			$assignment = llms_get_post( $lesson_data['assignment']['id'] );

		}

		$lesson->set( 'assignment', $assignment->get( 'id' ) );
		$lesson->set( 'assignment_enabled', 'yes' );

		// We don't have a proper assignment to work with.
		if ( empty( $assignment ) || ! is_a( $assignment, 'LLMS_Assignment' ) ) {

			// Translators: %s = Assignment ID.
			$assignment_res['error'] = sprintf( esc_html__( 'Unable to update assignment "%s". Invalid assignment ID.', 'lifterlms-assignments' ), $lesson_data['assignment']['id'] );

		} else {

			// return the real ID (important when creating a new assignment).
			$assignment_res['id'] = $assignment->get( 'id' );

			// If the parent lesson was just created the lesson will have a temp id.
			// Replace it with the newly created lessons's real ID.
			if ( ! isset( $lesson_data['assignment']['lesson_id'] ) || LLMS_Admin_Builder::is_temp_id( $lesson_data['assignment']['lesson_id'] ) ) {
				$lesson_data['assignment']['lesson_id'] = $lesson->get( 'id' );
			}

			// Remove tasks because we'll add them individually after creation.
			$tasks = ( isset( $lesson_data['assignment']['tasks'] ) && is_array( $lesson_data['assignment']['tasks'] ) ) ? $lesson_data['assignment']['tasks'] : false;
			unset( $lesson_data['assignment']['tasks'] );

			$properties = array_merge(
				array_keys( $assignment->get_properties() ),
				array(
					'status',
					'title',
				)
			);

			// Update all updateable properties.
			foreach ( $properties as $prop ) {
				if ( isset( $lesson_data['assignment'][ $prop ] ) ) {
					$assignment->set( $prop, $lesson_data['assignment'][ $prop ] );
				}
			}

			// Update all custom fields.
			LLMS_Admin_Builder::update_custom_schemas( 'assignment', $assignment, $lesson_data['assignment'] );

			if ( $tasks ) {

				$assignment_res['tasks'] = array();

				foreach ( $tasks as $t_data ) {

					$task_res = array_merge(
						$t_data,
						array(
							'orig_id' => $t_data['id'],
						)
					);

					unset( $t_data['assignment_id'] );

					// remove the temp ID so that we create it if it's new.
					if ( LLMS_Admin_Builder::is_temp_id( $t_data['id'] ) ) {
						unset( $t_data['id'] );
					}

					$task_id = $assignment->update_task( $t_data );
					if ( ! $task_id ) {
						// Translators: %s = Task ID.
						$task_res['error'] = sprintf( esc_html__( 'Unable to update task "%s". Invalid task ID.', 'lifterlms-assignments' ), $t_data['id'] );
					} else {
						$task_res['id']            = $task_id;
						$task_res['assignment_id'] = $assignment->get( 'id' );
					}

					array_push( $assignment_res['tasks'], $task_res );

				}
			}
		}

		$res['assignment'] = array_merge( $res['assignment'], $assignment_res );

		return $res;

	}

	/**
	 * Add assignments to the array of properties that shouldn't be updated during builder updates
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string[] $props Array of property keys that should be skipped.
	 * @return string[]
	 */
	public function update_skip_props( $props ) {
		$props[] = 'assignment';
		return $props;
	}

}

return new LLMS_Assignments_Builder();
