<?php
/**
 * Assignment Submission Reporting Table
 *
 * @package LifterLMS_Assignments/Admin/Classes
 *
 * @since 1.0.0-beta.2
 * @version 1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Assignments_Submissions class.
 */
class LLMS_Table_Assignments_Submissions extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'assignment_submissions';

	/**
	 * ID of the displayed assignment
	 *
	 * @var  null
	 */
	protected $assignment_id = null;

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set
	 *
	 * @var  string
	 */
	protected $filter = 'any';

	/**
	 * Field results are filtered by
	 *
	 * @var  string
	 */
	protected $filterby = 'grade';

	/**
	 * Determine if the table is filterable
	 *
	 * @var  boolean
	 */
	protected $is_filterable = true;

	/**
	 * If true, tfoot will add ajax pagination links
	 *
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable
	 *
	 * @var  boolean
	 */
	protected $is_searchable = false;

	/**
	 * Results sort order
	 * 'ASC' or 'DESC'
	 * Only applicable of $orderby is not set
	 *
	 * @var  string
	 */
	protected $order = 'DESC';

	/**
	 * Field results are sorted by
	 *
	 * @var  string
	 */
	protected $orderby = 'id';

	/**
	 * Retrieve data for a cell
	 *
	 * @param    string $key         the column id / key.
	 * @param    obj    $submission  LLMS_Quiz_Attempt obj.
	 * @return   mixed
	 * @since    1.0.0-beta.2
	 * @version  1.1.0
	 */
	protected function get_data( $key, $submission ) {

		switch ( $key ) {

			case 'student':
				$student = $submission->get_student();
				$value   = $student ? $student->get_name() : __( '[Deleted]', 'lifterlms-assignments' );
				break;

			case 'grade':
				$value  = $submission->get( $key ) ? $submission->get( $key ) . '%' : '0%';
				$value .= ' (' . $submission->get_l10n_status() . ')';
				break;

			case 'created':
			case 'updated':
				$value = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission->get( $key ) ) );
				break;

			case 'id':
				$url   = $submission->get_review_url();
				$value = '<a href="' . esc_url( $url ) . '">' . $submission->get( 'id' ) . '</a>';

				break;

			default:
				$value = $key;

		}// End switch().

		return $value;
	}

	/**
	 * Retrieve a list of Instructors to be used for Filtering
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	private function get_instructor_filters() {

		$query = get_users(
			array(
				'fields'   => array( 'ID', 'display_name' ),
				'meta_key' => 'last_name',
				'orderby'  => 'meta_value',
				'role__in' => array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ),
			)
		);

		$instructors = wp_list_pluck( $query, 'display_name', 'ID' );

		return $instructors;

	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.4 Added support for instructor roles.
	 * @since 1.3.0 Replaced the use of the protected `LLMS_Query_Assignments_Submission::$max_pages` property
	 *              with `LLMS_Query_Assignments_Submission::get( 'max_pages' )`.
	 *
	 * @param array $args Array of query args.
	 * @return LLMS_Assignment_Submission[]|false
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Assignment Submissions', 'lifterlms-assignments' );

		$args = $this->clean_args( $args );

		$this->assignment_id = $args['assignment_id'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$query_args = array(
			'sort'          => array(
				$this->orderby => $this->order,
			),
			'page'          => $this->current_page,
			'per_page'      => $per,
			'assignment_id' => $args['assignment_id'],
			'student_id'    => isset( $args['student_id'] ) ? $args['student_id'] : null,
		);

		if ( 'any' !== $this->filter ) {
			$query_args['status'] = $this->filter;
		}

		// Filter for non-admins/manager.
		if ( ! current_user_can( 'view_others_lifterlms_reports' ) ) {

			// Instructors can only see assignments associated with their courses.
			if ( ! current_user_can( 'view_lifterlms_reports' ) || ! current_user_can( 'edit_post', $args['assignment_id'] ) ) {

				// Can't view this report.
				return false;

			}
		}

		$query = new LLMS_Query_Assignments_Submission( $query_args );

		$this->max_pages    = $query->get_max_pages();
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_submissions();

		return $this->tbody_data;

	}

	/**
	 * Get a CSS class list (as a string) for each TR
	 *
	 * @param    mixed $row  object / array of data that the function can use to extract the row.
	 * @return   string
	 * @since    1.0.0-beta.5
	 * @version  1.0.0-beta.5
	 */
	protected function get_tr_classes( $row ) {
		$classes = parent::get_tr_classes( $row );
		if ( 'pending' === $row->get( 'status' ) ) {
			$classes .= ' llms-assignment-pending';
		}
		return $classes;
	}


	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.6
	 */
	public function set_args() {
		return array(
			'assignment_id' => ! empty( $this->assignment_id ) ? $this->assignment_id : absint( filter_input( INPUT_GET, 'assignment_id', FILTER_SANITIZE_NUMBER_INT ) ),
			'student_id'    => 0,
		);
	}

	/**
	 * Define the structure of the table
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	protected function set_columns() {

		$cols = array(
			'id'      => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms-assignments' ),
				'sortable'   => true,
			),
			'student' => array(
				'exportable' => true,
				'title'      => __( 'Student', 'lifterlms-assignments' ),
				'sortable'   => false,
			),
			'grade'   => array(
				'filterable' => llms_get_assignment_submission_statuses(),
				'exportable' => true,
				'title'      => __( 'Grade', 'lifterlms-assignments' ),
				'sortable'   => true,
			),
			'created' => array(
				'exportable' => true,
				'title'      => __( 'Start Date', 'lifterlms-assignments' ),
				'sortable'   => true,
			),
			'updated' => array(
				'exportable' => true,
				'title'      => __( 'Last Updated', 'lifterlms-assignments' ),
				'sortable'   => true,
			),
		);

		return $cols;

	}

}
