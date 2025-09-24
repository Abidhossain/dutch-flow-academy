<?php
/**
 * Manage custom LifterLMS post properites and Assignments custom post types
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.1
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage custom LifterLMS post properites and Assignments custom post types
 *
 * @since 1.0.0-beta.1
 * @since 1.1.1 Updated to handle the generation of an assignment during LifterLMS course clone and import operations.
 * @since 1.1.6 Add assignment to lesson and assignment to submission relationship actions.
 * @since 1.1.8 Register assignment-related meta data on the lesson post type for use in the block editor.
 */
class LLMS_Assignments_Posts {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.1 Added hooks & filters used during course import and duplications.
	 * @since 1.1.12 Added hook to handle redirection to the home page for orphaned assignments.
	 * @since 1.1.13 Added `maybe_restrict_assignment_access()` hook.
	 * @since 2.0.0 Added `save_parent_lesson()` hook.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );

		add_action( 'init', array( $this, 'register_lesson_meta' ) );

		add_filter( 'llms_get_lesson_properties', array( $this, 'add_lesson_props' ), 20, 2 );
		add_filter( 'llms_get_quiz_properties', array( $this, 'add_quiz_props' ), 20, 2 );

		add_filter( 'llms_get_quiz_get_property_defaults', array( $this, 'add_quiz_defaults' ), 20, 2 );

		add_filter( 'llms_lesson_to_array', array( $this, 'lesson_to_array' ), 20, 2 );

		add_filter( 'llms_get_post_relationships', array( $this, 'add_relationships' ) );

		add_filter( 'llms_builder_register_custom_fields', array( $this, 'allow_builder_custom_schemas' ), 1 );

		add_filter( 'llms_page_restricted_before_check_access', array( $this, 'before_check_access' ), 15, 2 );
		add_action( 'llms_content_restricted_by_orphaned_assignment', array( $this, 'restricted_by_orhpaned_assignment' ), 10, 1 );

		add_filter( 'is_lifterlms', array( $this, 'is_lifterlms' ) );

		add_filter( 'llms_course_children_post_types', array( $this, 'add_course_child' ) );

		add_filter( 'llms_generator_before_new_lesson', array( $this, 'generate_new_assignment' ), 10, 6 );
		add_action( 'llms_generator_new_lesson', array( $this, 'generate_new_assignment_relationships' ), 10, 3 );

		add_filter( 'llms_assignment_skip_custom_field', array( $this, 'maybe_skip_custom_field' ), 10, 3 );

		add_action( 'deleted_post', array( $this, 'maybe_delete_submissions' ) );

		add_action( 'wp', array( $this, 'maybe_restrict_assignment_access' ) );

		add_action( 'save_post_lesson', array( $this, 'save_parent_lesson' ), 100, 2 );

	}

	/**
	 * Add the assignment post type as a child type for courses
	 *
	 * Allows use llms_get_post_parent_course() with an assignment post.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param string[] $types Array of post types children of a course.
	 */
	public function add_course_child( $types ) {
		$types[] = 'llms_assignment';
		return $types;
	}

	/**
	 * Add lesson properties
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array       $props  Default lesson props.
	 * @param LLMS_Lesson $lesson Instance of the LLMS_Lesson.
	 * @return array
	 */
	public function add_lesson_props( $props, $lesson ) {

		$props['assignment']         = 'absint';
		$props['assignment_enabled'] = 'yesno';

		return $props;

	}

	/**
	 * Add quiz default property values
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @param array     $defaults Array of default property values.
	 * @param LLMS_Quiz $quiz     Instance of the LLMS_Quiz.
	 * @return array
	 */
	public function add_quiz_defaults( $defaults, $quiz ) {
		$defaults['points'] = 1;
		return $defaults;
	}

	/**
	 * Add quiz properties
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array     $props Array of quiz properties.
	 * @param LLMS_Quiz $quiz  Instance of the LLMS_Quiz.
	 * @return array
	 */
	public function add_quiz_props( $props, $quiz ) {

		$props['points'] = 'absint';

		return $props;
	}

	/**
	 * Allow 3rd parties to register custom assignment schemas on the builder
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $schemas Array of existing schemas.
	 * @return array
	 */
	public function allow_builder_custom_schemas( $schemas ) {
		$schemas['assignment'] = array();
		return $schemas;
	}

	/**
	 * Add assignment post relationships
	 *
	 * When a lesson is deleted: delete related assignments.
	 *
	 * When an assignment is deleted: unset metadata on related lessons.
	 *
	 * @since 1.0.0-beta.6
	 * @since 1.1.6 Add assignment to lesson and assignment to submission relationship actions.
	 *
	 * @param array $relationships Array of existing relationship data.
	 * @return array
	 */
	public function add_relationships( $relationships ) {

		if ( ! isset( $relationships['lesson'] ) ) {
			$relationships['lesson'] = array();
		}

		// When a lesson is deleted, also delete associated assignments.
		$relationships['lesson'][] = array(
			'action'    => 'delete',
			'meta_key'  => '_llms_lesson_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'post_type' => 'llms_assignment',
		);

		if ( ! isset( $relationships['llms_assignment'] ) ) {
			$relationships['llms_assignment'] = array();
		}

		// When an assignment is deleted, unset metadata on parent lessons.
		$relationships['llms_assignment'][] = array(
			'action'               => 'unset',
			'meta_key'             => '_llms_assignment', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_keys_additional' => array( '_llms_assignment_enabled' ),
			'post_type'            => 'lesson',
		);

		return $relationships;
	}

	/**
	 * Ensure that assignments obey all the restriction settings of the assignment's parent lesson
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.12 When orphaned return a proper restriction data instead of redirecting.
	 *                  Redirection will be handled at template include separately at 'template_include'.
	 *
	 * @param array $results Restriction data results.
	 * @param int   $post_id WP Post ID of the post being accessed.
	 * @return array
	 */
	public function before_check_access( $results, $post_id ) {

		// return early if it's not an assignment.
		if ( 'llms_assignment' !== get_post_type( $post_id ) ) {
			return $results;
		}

		// this shouldn't ever happen...
		$assignment = llms_get_post( $post_id );
		if ( ! $assignment ) {
			return $results;
		}

		// Orphaned assignments are not accessible.
		$lesson = $assignment->get_lesson();
		if ( ! $lesson ) {
			$results = array(
				'is_restricted'  => true,
				'reason'         => 'orphaned_assignment',
				'restriction_id' => $post_id,
				'content_id'     => $post_id,
			);

			/* This filter is documented in LifterLMS core. */
			return apply_filters( 'llms_page_restricted', $results, $post_id );

		}

		return llms_page_restricted( $lesson->get( 'id' ), get_current_user_id() );

	}

	/**
	 * Handle redirects and messages when a user attempts to access an orphaned assignment
	 *
	 * Redirects to home page.
	 *
	 * @since 1.1.12
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_orhpaned_assignment( $info ) {

		if ( 'llms_assignment' !== get_post_type( $info['content_id'] ) ) {
			return;
		}

		llms_redirect_and_exit( get_site_url() );
	}


	/**
	 * Create a new assignment when importing / cloning courses.
	 *
	 * Called via the `llms_generator_before_new_lesson` filter.
	 *
	 * @since 1.1.1
	 * @since 1.1.11 Remove usage of deprecated generator method.
	 *
	 * @param array          $raw Raw lesson data.
	 * @param int            $order Lesson order.
	 * @param int            $section_id LLMS_Section ID.
	 * @param int            $course_id LLMS_Course ID.
	 * @param int            $fallback_author_id WP_User ID to be used as a fallback if no author information found in the $raw data.
	 * @param LLMS_Generator $generator Generator instance.
	 * @return array
	 */
	public function generate_new_assignment( $raw, $order, $section_id, $course_id, $fallback_author_id, $generator ) {

		if ( empty( $raw['assignment'] ) || ! is_array( $raw['assignment'] ) ) {
			return $raw;
		}

		$raw_assignment = $raw['assignment'];

		$author_id = $generator->get_author_id_from_raw( $raw_assignment, $fallback_author_id );
		if ( isset( $raw_assignment['author'] ) ) {
			unset( $raw_assignment['author'] );
		}

		// Insert the new Assignment.
		$assignment = new LLMS_Assignment(
			'new',
			array(
				'post_author'   => $author_id,
				'post_content'  => isset( $raw_assignment['content'] ) ? $raw_assignment['content'] : null,
				'post_date'     => isset( $raw_assignment['date'] ) ? $generator->format_date( $raw_assignment['date'] ) : null,
				'post_modified' => isset( $raw_assignment['modified'] ) ? $generator->format_date( $raw_assignment['modified'] ) : null,
				'post_status'   => isset( $raw_assignment['status'] ) ? $raw_assignment['status'] : $generator->get_default_post_status(),
				'post_title'    => $raw_assignment['title'],
			)
		);

		if ( ! $assignment->get( 'id' ) ) {
			return $generator->error->add( 'assignment_creation', __( 'Error creating assignment', 'lifterlms-assignments' ) );
		}

		// set all metadata.
		foreach ( array_keys( $assignment->get_properties() ) as $key ) {
			if ( isset( $raw_assignment[ $key ] ) ) {
				$assignment->set( $key, $raw_assignment[ $key ] );
			}
		}

		// Add tasks.
		if ( isset( $raw_assignment['tasks'] ) ) {
			foreach ( $raw_assignment['tasks'] as $task ) {
				unset( $task['id'] );
				unset( $task['assignment_id'] );
				$assignment->create_task( $task );
			}
		}

		// add custom meta.
		$generator->add_custom_values( $assignment->get( 'id' ), $raw_assignment );

		// Run an action.
		do_action( 'llms_generator_new_assignment', $assignment, $raw_assignment, $generator );

		// Modify the raw data to have just the ID.
		$raw['assignment']         = $assignment->get( 'id' );
		$raw['assignment_enabled'] = 'yes';

		return $raw;

	}

	/**
	 * Update assignment data after a lesson is created via the LLMS_Generator
	 *
	 * @since 1.1.1
	 *
	 * @param LLMS_Lesson    $lesson    Lesson object.
	 * @param array          $raw       Raw lesson data.
	 * @param LLMS_Generator $generator Generator instance.
	 * @return void
	 */
	public function generate_new_assignment_relationships( $lesson, $raw, $generator ) {

		if ( ! empty( $raw['assignment'] ) && is_numeric( $raw['assignment'] ) ) {
			$assignment = llms_get_post( $raw['assignment'] );
			if ( $assignment ) {
				$assignment->set( 'lesson_id', $lesson->get( 'id' ) );
			}
		}

	}

	/**
	 * Ensure that is_lifterlms() returns true on assignments
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @param bool $boolean Default value (true/false).
	 * @return bool
	 */
	public function is_lifterlms( $boolean ) {

		if ( ! $boolean && is_singular( 'llms_assignment' ) ) {
			return true;
		}

		return $boolean;

	}

	/**
	 * Handle assignment unfurling during toArray calls on lessons
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Unknown.
	 *
	 * @param array       $arr    Lesson array data.
	 * @param LLMS_Lesson $lesson LLMS_Lesson instance.
	 * @return array
	 */
	public function lesson_to_array( $arr, $lesson ) {

		if ( llms_parse_bool( $lesson->get( 'assignment_enabled' ) ) ) {

			$id = $lesson->get( 'assignment' );

			if ( $id ) {

				$assignment = llms_get_post( $id );
				if ( $assignment ) {
					$arr['assignment'] = $assignment->toArray();
				}
			}
		}

		return $arr;
	}

	/**
	 * Delete submissions when an assignment is deleted.
	 *
	 * Hooked to `deleted_post` in the WP Core.
	 *
	 * @since 1.1.6
	 *
	 * @link https://developer.wordpress.org/reference/hooks/deleted_post/
	 *
	 * @param int $post_id WP_Post ID.
	 * @return bool
	 */
	public function maybe_delete_submissions( $post_id ) {

		// Only mess with assignments.
		if ( 'llms_assignment' !== get_post_type( $post_id ) ) {
			return false;
		}

		$is_last_page = false;
		while ( ! $is_last_page ) {

			$query = new LLMS_Query_Assignments_Submission(
				array(
					'assignment_id' => $post_id,
					'per_page'      => 50,
				)
			);
			foreach ( $query->get_submissions() as $submission ) {
				$submission->delete();
			}

			$is_last_page = $query->is_last_page();

		}

		return true;

	}

	/**
	 * Only allow users to access another user's assignment submission if they can `view_grades` for that student
	 *
	 * @since 1.1.13
	 *
	 * @return void
	 */
	public function maybe_restrict_assignment_access() {

		if ( 'llms_assignment' !== get_post_type() ) {
			return;
		}

		$assignment_id = get_the_ID();
		$sid           = llms_filter_input( INPUT_GET, 'sid', FILTER_SANITIZE_NUMBER_INT );

		// If an sid is provided and the current user can't view that user's grades, redirect them to the course's home page.
		if ( $sid && ! current_user_can( 'view_grades', absint( $sid ), $assignment_id ) ) {

			$assignment = llms_get_post( $assignment_id );
			$course     = $assignment ? $assignment->get_course() : false;
			$url        = $course ? get_permalink( $course->get( 'id' ) ) : home_url();

			llms_redirect_and_exit( $url );
		}

	}

	/**
	 * Prevent task metadata from being identified as "custom" fields by the LLMS_Post_Model `toArrayCustom` method
	 *
	 * @since 1.1.1
	 *
	 * @param bool            $skip       Whether or not to skip the field when creating the custom array.
	 * @param string          $key        Field meta key.
	 * @param LLMS_Assignment $assignment Assignment object.
	 * @return bool
	 */
	public function maybe_skip_custom_field( $skip, $key, $assignment ) {

		if ( 0 === strpos( $key, '_llms_task_' ) ) {
			$skip = true;
		}

		return $skip;
	}

	/**
	 * Registers lesson metadata related to assignments
	 *
	 * This is used in the block editor to determine whether or not an assignment
	 * exists for the given lesson.
	 *
	 * @since 1.1.8
	 *
	 * @return void
	 */
	public function register_lesson_meta() {

		register_meta(
			'post',
			'_llms_assignment',
			array(
				'object_subtype'    => 'lesson',
				'sanitize_callback' => 'absint',
				'auth_callback'     => '__return_true',
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
			)
		);

	}

	/**
	 * Register the assignment custom post type
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Unknown.
	 *
	 * @return void
	 */
	public static function register_post_type() {

		$permalinks = llms_get_assignments_permalink_structure();

		LLMS_Post_Types::register_post_type(
			'llms_assignment',
			array(
				'labels'              => array(
					'name'               => __( 'Assignments', 'lifterlms-assignments' ),
					'singular_name'      => __( 'Assignment', 'lifterlms-assignments' ),
					'menu_name'          => _x( 'Assignments', 'Admin menu name', 'lifterlms-assignments' ),
					'add_new'            => __( 'Add Assignment', 'lifterlms-assignments' ),
					'add_new_item'       => __( 'Add New Assignment', 'lifterlms-assignments' ),
					'edit'               => __( 'Edit', 'lifterlms-assignments' ),
					'edit_item'          => __( 'Edit Assignment', 'lifterlms-assignments' ),
					'new_item'           => __( 'New Assignment', 'lifterlms-assignments' ),
					'view'               => __( 'View Assignment', 'lifterlms-assignments' ),
					'view_item'          => __( 'View Assignment', 'lifterlms-assignments' ),
					'search_items'       => __( 'Search Assignments', 'lifterlms-assignments' ),
					'not_found'          => __( 'No Assignments found', 'lifterlms-assignments' ),
					'not_found_in_trash' => __( 'No Assignments found in trash', 'lifterlms-assignments' ),
					'parent'             => __( 'Parent Assignment', 'lifterlms-assignments' ),
				),
				'description'         => __( 'This is where you can add new assignments.', 'lifterlms-assignments' ),
				'public'              => true,
				'show_ui'             => false,
				'capabilities'        => LLMS_Post_Types::get_post_type_caps( 'assignment' ),
				'map_meta_cap'        => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => array(
					'slug'       => $permalinks['assignment_base'],
					'with_front' => false,
					'feeds'      => true,
				),
				'query_var'           => true,
				'supports'            => array( 'title', 'thumbnail' ),
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
			)
		);

	}

	/**
	 * Perform actions against an assignment when the assignment's parent lesson is updated.
	 *
	 * @since 2.0.0
	 *
	 * @param int     $lesson_id   WP_Post ID of the lesson post.
	 * @param WP_Post $lesson_post Lesson post object.
	 * @return void
	 */
	public function save_parent_lesson( $lesson_id, $lesson_post ) {

		$assignment = llms_lesson_get_assignment( $lesson_id );
		if ( ! $assignment ) {
			return;
		}

		// Sync the lesson author to the assignment.
		if ( $assignment->get( 'post_author' ) !== (int) $lesson_post->post_author ) {
			$assignment->set( 'post_author', $lesson_post->post_author );
		}

	}

}

return new LLMS_Assignments_Posts();
