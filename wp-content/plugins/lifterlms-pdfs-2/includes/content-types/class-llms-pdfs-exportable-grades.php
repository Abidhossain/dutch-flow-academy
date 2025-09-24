<?php
/**
 * LLMS_PDFS_Exportable_Grades class file.
 *
 * @package LifterLMS_PDFS/Classes
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gades data server-side PDF exporter.
 *
 * @since 2.0.0
 */
class LLMS_PDFS_Exportable_Grades extends LLMS_PDFS_Abstract_Server_Side_Exportable_Content {

	/**
	 * Unique content type ID.
	 *
	 * @var string
	 */
	protected $id = 'grades';

	/**
	 * Registration priority.
	 *
	 * Used to order the content type on the settings page.
	 *
	 * @var integer
	 */
	protected $priority = 15;

	/**
	 * List of hooks used to output the button.
	 *
	 * @var array[]
	 */
	protected $button_hooks = array(
		array( 'llms_my_grades_course_table', 50, null, 2 ),
		array( 'llms_reporting_single_student_course_actions', 50, null, 2 ),
	);

	/**
	 * Retrieves the settings used to output the download button.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of settings suitable to pass to {@see llms_form_field()}.
	 */
	protected function get_button_settings() {

		$settings = parent::get_button_settings();

		$settings['wrapper_classes'] = 'align-right';

		if ( is_admin() ) {
			$settings['classes'] .= ' small auto';
			return $settings;
		}

		$settings['attributes'] = array(
			'style' => 'margin-top: 20px',
		);
		$settings['classes']   .= ' auto';

		return $settings;

	}

	/**
	 * Retrieves the HTML for the export form & button.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return string
	 */
	protected function get_button_html( ...$args ) {

		$html = parent::get_button_html( ...$args );

		if ( ! is_admin() ) {
			return $html;
		}

		ob_start();
		?>
		<style type="text/css">
			.llms-pdfs-dl-form {
				display: inline-block;
				float:  right;
				margin-right: 20px;
				margin-top: -4px;
				width: 200px;
			}
		</style>
		<?php
		$styles = ob_get_clean();

		return $styles . $html;

	}

	/**
	 * Retrieves the ID of the current object.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return int
	 */
	protected function get_current_object_id( ...$args ) {

		$args = array(
			$args[0]->get( 'id' ), // Course ID.
			$args[1]->get( 'id' ), // Student ID.
		);

		// Admin hook is passed as student, course.
		if ( is_admin() ) {
			$args = array_reverse( $args );
		}

		return implode( '|', $args );

	}

	/**
	 * Retrieves the filename for the exported PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	protected function get_filename( $object_data ) {

		$parts = array();

		if ( is_admin() ) {
			$parts[] = llms_get_student( $object_data['user_id'] )->get_name();
		}

		$parts[] = llms_get_post( $object_data['course_id'] )->get( 'title' );

		return sanitize_file_name( strtolower( implode( '-', $parts ) ) );

	}

	/**
	 * Retrieves the HTML to write to the PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	protected function get_html( $object_data ) {

		ob_start();
		$course  = llms_get_post( $object_data['course_id'] );
		$student = llms_get_student( $object_data['user_id'] );

		add_filter(
			'llms_sd_my_grades_table_content',
			function( $html ) {

				$html = trim( preg_replace( '/(<div.*>|<a.*>|<\/a>|<\/div>|\t|\n)/', '', $html ) );

				$html = str_replace( '><', '> <', $html );

				return $html;
			}
		);

		include_once LLMS_PDFS_PLUGIN_DIR . 'includes/views/grades.php';

		return ob_get_clean();

	}

	/**
	 * Retrieves object data from form submission.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function parse_object_data() {

		list( $course_id, $user_id ) = parent::parse_object_data();
		return compact( 'course_id', 'user_id' );

	}

	/**
	 * Sets the content type's description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function set_description() {
		return __( 'Students can download their course grade and progress reports.', 'lifterlms-pdfs' );
	}

	/**
	 * Sets the content type's title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Grade and Progress Reports', 'lifterlms-pdfs' );
	}

}

return new LLMS_PDFS_Exportable_Grades();
