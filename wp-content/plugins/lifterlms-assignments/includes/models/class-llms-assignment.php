<?php
/**
 * LifterLMS Assignment Model
 *
 * @package LifterLMS_Assignments/Models
 * @since 1.0.0-beta.1
 * @version 1.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Assignment Model.
 *
 * @since 1.0.0-beta.1
 * @since 1.2.0 Replace audio and video embed methods with the use of `LLMS_Trait_Audio_Video_Embed`.
 *
 * @property array  $allowed_mimes         (upload type only) Allowed mimes types.
 * @property string $audio_embed           URL to an oEmbed enable audio URL.
 * @property string $assignment_type       Type of assignment [tasklist*|essay|upload].
 * @property string $enable_word_count_max (Essay type only) Enable a maximum word count [yes|no*].
 * @property string $enable_word_count_min (Essay type only) Enable a minimum word count [yes|no*].
 * @property string $has_description       Enable description (content) for the assignment [yes|no*].
 * @property int    $passing_percentage    Minimum required grade to pass the assignment.
 * @property int    $points                Grading weight of the assignment.
 * @property int    $lesson_id             WP Post ID of the assignment's parent lesson.
 * @property string $video_embed           URL to an oEmbed enable video URL.
 * @property int    $word_count_max        (Essay type only) Max allowed words.
 * @property int    $word_count_min        (Essay type only) Min required words.
 */
class LLMS_Assignment extends LLMS_Post_Model {

	use LLMS_Trait_Audio_Video_Embed;

	/**
	 * Database Post Type Name for the Model
	 *
	 * @var  string
	 */
	protected $db_post_type = 'llms_assignment';

	/**
	 * Post Type name of the model
	 *
	 * @var  string
	 */
	protected $model_post_type = 'assignment';

	/**
	 * Model Properties
	 *
	 * @var  array
	 */
	protected $properties = array(
		'audio_embed'           => 'string',
		'allowed_mimes'         => 'array',
		'assignment_type'       => 'string',
		'enable_allowed_mimes'  => 'yesno',
		'enable_word_count_max' => 'yesno',
		'enable_word_count_min' => 'yesno',
		'has_description'       => 'yesno',
		'lesson_id'             => 'absint',
		'passing_percentage'    => 'float',
		'points'                => 'absint',
		'video_embed'           => 'string',
		'word_count_max'        => 'absint',
		'word_count_min'        => 'absint',
	);

	/**
	 * Create a new assignment task
	 *
	 * @param    array $data  array of assignment task data.
	 * @return   string|boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function create_task( $data ) {

		$data = wp_parse_args(
			$data,
			array(
				'assignment_id' => $this->get( 'id' ),
				'marker'        => 'A',
				'title'         => '',
			)
		);

		$task = new LLMS_Assignment_Task( $this->get( 'id' ) );
		if ( $task->create( $data ) ) {
			return $task->get( 'id' );
		}

		return false;

	}

	/**
	 * Delete a task by ID
	 *
	 * @param    string $id  task ID.
	 * @return   boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function delete_task( $id ) {

		$task = $this->get_task( $id );
		if ( ! $task ) {
			return false;
		}
		return $task->delete();

	}

	/**
	 * Retrieve the allowed mime types as a string (for upload assignments)
	 *
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_allowed_mimes_string() {
		$mimes = array();
		$types = $this->get( 'allowed_mimes' );
		foreach ( $types as $type ) {
			$mimes = array_merge( $mimes, explode( '|', $type ) );
		}
		return implode( ', ', $mimes );
	}

	/**
	 * Retrieve the LLMS_Course for the assignment
	 *
	 * @return   obj
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_course() {
		$lesson = $this->get_lesson();
		if ( $lesson ) {
			return $lesson->get_course();
		}
		return false;
	}

	/**
	 * Retrieve LLMS_Lesson for the assignment's parent lesson
	 *
	 * @return   obj
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_lesson() {
		$id = $this->get( 'lesson_id' );
		if ( ! $id ) {
			return false;
		}
		return llms_get_post( $id );
	}

	/**
	 * Retrieve a task by id
	 *
	 * @param    string $id   task ID.
	 * @return   obj|false
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_task( $id ) {
		$task = new LLMS_Assignment_Task( $this->get( 'id' ), $id );
		if ( $task->exists() && $this->get( 'id' ) == $task->get_assignment_id() ) {
			return $task;
		}
		return false;
	}

	/**
	 * Retrieve the assignments's tasks
	 *
	 * @param    string $return  return type [tasks*|ids].
	 * @return   array
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function get_tasks( $return = 'tasks' ) {

		global $wpdb;
		$query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key AS id
				  , meta_value AS data
			 FROM {$wpdb->postmeta}
			 WHERE post_id = %d
			   AND meta_key LIKE %s
			;",
				$this->get( 'id' ),
				'_llms_task_%'
			)
		);

		usort(
			$query,
			function( $a, $b ) {
				$adata = unserialize( $a->data );
				$bdata = unserialize( $b->data );
				return strcmp( $adata['marker'], $bdata['marker'] );
			}
		);

		if ( 'ids' === $return ) {
			return wp_list_pluck( $query, 'id' );
		}

		$ret = array();
		foreach ( $query as $result ) {
			$ret[] = new LLMS_Assignment_Task( $this->get( 'id' ), unserialize( $result->data ) );
		}

		return $ret;

	}

	/**
	 * Determine if the assignment is an orphan
	 *
	 * @return   bool
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function is_orphan() {

		$parent_id = $this->get( 'lesson_id' );

		if ( ! $parent_id ) {
			return true;
		}

		return false;

	}

	/**
	 * Called before data is sorted and returned by $this->toArray()
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json
	 *
	 * @param    array $arr   array of data to be serialized.
	 * @return   array
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	protected function toArrayAfter( $arr ) {

		$tasks = array();
		foreach ( $this->get_tasks() as $task ) {
			$tasks[] = $task->get_data();
		}
		$arr['tasks'] = $tasks;
		return $arr;

	}

	/**
	 * Update a question task
	 * if no id is supplied will create a new task
	 *
	 * @param    array $data  array of task data (see $this->create_task()).
	 * @return   string|boolean
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function update_task( $data ) {

		// if there's no ID, we'll add a new task.
		if ( ! isset( $data['id'] ) ) {
			return $this->create_task( $data );
		}

		// get the question.
		$task = $this->get_task( $data['id'] );
		if ( ! $task ) {
			return false;
		}

		$task->update( $data )->save();

		// return task ID.
		return $task->get( 'id' );

	}

}
