<?php
/**
 * Metabox additions related to assignments.
 *
 * @package  LifterLMS_Assignments/Admin/Classes
 * @since    1.1.0
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assignments_Metaboxes class..
 */
class LLMS_Assignments_Metaboxes {

	/**
	 * Constructor.
	 *
	 * @since    1.1.0
	 * @version  1.1.0
	 */
	public function __construct() {

		add_action( 'llms_builder_mb_after_lesson', array( $this, 'builder_mb_after_lesson' ), 10, 2 );

	}

	/**
	 * Add "deep" links to the course builder to the course builder metabox.
	 *
	 * @param   obj $lesson  LLMS_Lesson.
	 * @param   obj $metabox LLMS_Metabox_Course_Builder.
	 * @return  void
	 * @since   1.1.0
	 * @version 1.1.0
	 */
	public function builder_mb_after_lesson( $lesson, $metabox ) {

		$assignment = llms_lesson_get_assignment( $lesson );
		if ( $assignment ) {

			$url = $metabox->get_builder_url( $lesson->get( 'parent_course' ), sprintf( 'lesson:%d:assignment', $lesson->get( 'id' ) ) );
			echo '<br>';
			printf( '<span class="tip--top-right" data-tip="%1$s"><i class="fa fa-check-square-o"></i></span> %2$s', __( 'Assignment', 'lifterlms-assignments' ), $metabox->get_title_html( $assignment->get( 'title' ), $url ) );

		}

	}

}

return new LLMS_Assignments_Metaboxes();
