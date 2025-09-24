<?php
/**
 * Manage user capabilities for the assignment post type.
 *
 * @package  LifterLMS_Assignments/Classes
 *
 * @since 1.1.4
 * @version 1.1.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assignments_Capabilities class..
 *
 * @since 1.1.4
 */
class LLMS_Assignments_Capabilities {

	/**
	 * Constructor.
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_get_administrator_post_type_caps', array( $this, 'add_assignment_caps' ) );
		add_filter( 'llms_get_lms_manager_post_type_caps', array( $this, 'add_assignment_caps' ) );
		add_filter( 'llms_get_instructor_post_type_caps', array( $this, 'add_assignment_caps' ) );
		add_filter( 'llms_get_instructors_assistant_post_type_caps', array( $this, 'add_assignment_caps' ) );

		add_filter( 'llms_user_caps_edit_others_posts_post_types', array( $this, 'filter_edit_others_posts_types' ) );

	}

	/**
	 * Add assignment post capabilities.
	 *
	 * @since 1.1.4
	 *
	 * @param array $caps Array of capabilities.
	 * @return array
	 */
	public function add_assignment_caps( $caps ) {

		$role = current_filter();
		if ( $role ) {

			$role     = str_replace( array( 'llms_get_', '_post_type_caps' ), '', $role );
			$add_caps = self::get_assignment_caps_for_role( $role );

			if ( $add_caps ) {
				$caps['llms_assignment'] = array_fill_keys( array_values( $add_caps ), true );
			}
		}

		return $caps;

	}

	/**
	 * Adds Assignments to the list of post types that should cascade up to determine if a user can edit another user's posts.
	 *
	 * @since 1.1.4
	 *
	 * @param string[] $types Array of unprefixed post type names.
	 * @return string[]
	 */
	public function filter_edit_others_posts_types( $types ) {

		$types[] = 'assignments';
		return $types;

	}

	/**
	 * Retrieve a list of assignment post capabilities for a given role.
	 *
	 * @since 1.1.4
	 *
	 * @param string $role Role name.
	 * @return array Associative array of meta-cap => capability. EG: edit_post => edit_assignment.
	 */
	public function get_assignment_caps_for_role( $role ) {

		$caps    = LLMS_Post_Types::get_post_type_caps( 'assignment' );
		$allowed = array();

		if ( in_array( $role, array( 'administrator', 'lms_manager' ), true ) ) {
			$allowed = array_keys( $caps );
		} elseif ( 'instructor' === $role ) {
			$allowed = array(
				'delete_posts',
				'delete_published_posts',
				'edit_post',
				'edit_posts',
				'edit_published_posts',
				'publish_posts',
				'create_posts',
			);
		} elseif ( 'instructors_assistant' === $role ) {
			$allowed = array(
				'edit_post',
				'edit_posts',
				'edit_published_posts',
			);
		}

		foreach ( $caps as $key => $val ) {

			if ( ! in_array( $key, $allowed, true ) ) {
				unset( $caps[ $key ] );
			}
		}

		return $caps;

	}

}

return new LLMS_Assignments_Capabilities();
