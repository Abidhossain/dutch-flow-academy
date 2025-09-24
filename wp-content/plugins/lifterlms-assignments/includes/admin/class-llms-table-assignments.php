<?php
/**
 * Assignments Reporting Table
 *
 * @package LifterLMS_Assignments/Admin/Classes
 *
 * @since 1.0.0-beta.2
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assignments Reporting Table
 *
 * @since 1.0.0-beta.2
 * @since 1.0.0-beta.5 Unknown.
 * @since 1.1.4 Added support for instructors to view assignment reporting.
 */
class LLMS_Table_Assignments extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'assignments';

	/**
	 * Is the Table Exportable?
	 *
	 * @var  boolean
	 */
	protected $is_exportable = true;

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
	protected $is_searchable = true;

	/**
	 * Results sort order
	 * 'ASC' or 'DESC'
	 * Only applicable of $orderby is not set
	 *
	 * @var  string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by
	 *
	 * @var  string
	 */
	protected $orderby = 'title';

	/**
	 * Retrieve data for a cell.
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.5 Unknown.
	 * @since 2.1.0 Added `round()` method for 'average' column values with precision from `llms_get_floats_rounding_precision()` helper.
	 *
	 * @param string $key  The column id / key.
	 * @param mixed  $data Object / array of data that the function can use to extract the data.
	 * @return mixed
	 */
	protected function get_data( $key, $data ) {

		$assignment = llms_get_post( $data );

		switch ( $key ) {

			case 'submissions':
				$a_data = new LLMS_Assignments_Data( $assignment->get( 'id' ) );
				$a_data->set_period( 'all_time' );
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'           => 'assignments',
						'stab'          => 'submissions',
						'assignment_id' => $assignment->get( 'id' ),
					)
				);
				$value = '<a href="' . $url . '">' . $a_data->get_submission_count() . '</a>';

				break;

			case 'average':
				$a_data = new LLMS_Assignments_Data( $assignment->get( 'id' ) );
				$a_data->set_period( 'all_time' );
				$avg   = $a_data->get_average_grade();
				$value = $avg ? round( $avg, llms_get_floats_rounding_precision() ) . '%' : '&ndash;';
				break;

			case 'course':
				$value = '&mdash;';

				if ( ! $assignment->is_orphan() ) {

					$course = $assignment->get_course();
					if ( $course ) {
						$url   = LLMS_Admin_Reporting::get_current_tab_url(
							array(
								'tab'       => 'courses',
								'course_id' => $course->get( 'id' ),
							)
						);
						$value = '<a href="' . esc_url( $url ) . '">' . $course->get( 'title' ) . '</a>';
					}
				}

				break;

			case 'id':
				$value = $assignment->get( 'id' );
				break;

			case 'lesson':
				$value = '&mdash;';

				if ( ! $assignment->is_orphan() ) {

					$lesson = $assignment->get_lesson();
					if ( $lesson ) {
						$value = $lesson->get( 'title' );
					}
				}
				break;

			case 'title':
				$value = $assignment->get( 'title' );
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'           => 'assignments',
						'assignment_id' => $assignment->get( 'id' ),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $assignment->get( 'title' ) . '</a>';
				break;

			case 'to_review':
				$a_data = new LLMS_Assignments_Data( $assignment->get( 'id' ) );
				$a_data->set_period( 'all_time' );
				$value = $a_data->get_pending_count();
				if ( $value > 0 ) {
					$url   = LLMS_Admin_Reporting::get_current_tab_url(
						array(
							'tab'           => 'assignments',
							'stab'          => 'submissions',
							'assignment_id' => $assignment->get( 'id' ),
						)
					);
					$value = '<a href="' . $url . '">' . $value . '</a>';
				}

				break;

			default:
				$value = $key;

		}// End switch().

		return $value;
	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.1.4 Added support for instructor roles.
	 *
	 * @param array $args Array of query args.
	 * @return WP_Post[]|false
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Assignments', 'lifterlms-assignments' );

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$query_args = array(
			'order'          => $this->order,
			'orderby'        => $this->orderby,
			'paged'          => $this->current_page,
			'post_status'    => array( 'publish', 'draft' ),
			'post_type'      => 'llms_assignment',
			'posts_per_page' => $per,
		);

		if ( isset( $args['search'] ) ) {
			$query_args['s'] = sanitize_text_field( $args['search'] );
		}

		// Filter for non-admins/manager.
		if ( ! current_user_can( 'view_others_lifterlms_reports' ) ) {

			// Instructors can only see assignments associated with their courses.
			if ( current_user_can( 'view_lifterlms_reports' ) ) {

				$assignment_ids = llms_instructor_get_assignments();

				if ( false === $assignment_ids ) {

					// No instructor found, this is an error, return false.
					return false;

				} elseif ( empty( $assignment_ids ) ) {

					// Instructor doesn't have any assignments so we shouldn't perform a query with an empty array or we'll expose the full assignment list!
					return array();

				}

				$query_args['post__in'] = $assignment_ids;

			} else {

				// Can't view this report.
				return false;

			}
		}

		// Perform the query.
		$query = new WP_Query( $query_args );

		$this->max_pages = $query->max_num_pages;

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $query->posts;

		return $this->tbody_data;

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 *
	 * @return   string
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search assignments...', 'lifterlms-assignments' ) );
	}

	/**
	 * Get a CSS class list (as a string) for each TR
	 *
	 * @param    mixed $row  object / array of row that the function can use to extract the row.
	 * @return   string
	 * @since    1.0.0-beta.5
	 * @version  1.0.0-beta.5
	 */
	protected function get_tr_classes( $row ) {
		$classes = parent::get_tr_classes( $row );
		if ( $this->get_data( 'to_review', $row ) ) {
			$classes .= ' llms-assignment-pending';
		}
		return $classes;
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.2
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 *
	 * @return   array
	 * @since    1.0.0-beta.2
	 * @version  1.0.0-beta.5
	 */
	protected function set_columns() {
		return array(
			'id'          => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms-assignments' ),
				'sortable'   => true,
			),
			'title'       => array(
				'exportable' => true,
				'title'      => __( 'Title', 'lifterlms-assignments' ),
				'sortable'   => true,
			),
			'course'      => array(
				'exportable' => true,
				'title'      => __( 'Course', 'lifterlms-assignments' ),
				'sortable'   => false,
			),
			'lesson'      => array(
				'exportable' => true,
				'title'      => __( 'Lesson', 'lifterlms-assignments' ),
				'sortable'   => false,
			),
			'to_review'   => array(
				'exportable' => true,
				'title'      => __( 'Awaiting Review', 'lifterlms-assignments' ),
				'sortable'   => false,
			),
			'submissions' => array(
				'exportable' => true,
				'title'      => __( 'Total Attempts', 'lifterlms-assignments' ),
				'sortable'   => false,
			),
			'average'     => array(
				'exportable' => true,
				'title'      => __( 'Average Grade', 'lifterlms-assignments' ),
				'sortable'   => false,
			),
		);
	}

}
