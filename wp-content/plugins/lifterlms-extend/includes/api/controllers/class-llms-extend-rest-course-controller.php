<?php
/**
 * Course Details REST API Controller
 */

defined('ABSPATH') || exit;

// Load services
require_once plugin_dir_path(__FILE__) . '../services/class-llms-extend-rest-course-service.php';

/**
 * Course Details API Controller class
 */
class LLMS_Extend_REST_Course_Controller {
   
    private $service;
    private $course;
    private $student;
    private $lesson;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new LLMS_Extend_REST_Course_Service();
    }
    /**
     * Register REST API routes
     *
     * @return void
     */
    public function register_routes($namespace) {
        register_rest_route(
            $namespace,
            '/courses/(?P<course_id>\d+)/details',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_course_details'),
                'permission_callback' => array($this, 'check_course_details_permissions'),
                'args' => array(
                    'course_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        },
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/my-courses',
              array(
                  'methods' => WP_REST_Server::READABLE,
                  'callback' => array($this, 'get_my_courses'),
                  'permission_callback' => array($this, 'check_my_courses_permissions'),
              )
        );
        register_rest_route(
            $namespace,
            '/course-categories',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_course_categories'),
                'permission_callback' => array($this, 'check_course_categories_permissions'),
            )
        );
        register_rest_route(
            $namespace,
            '/courses/category/(?P<category_slug>[a-zA-Z0-9-_]+)',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_courses_by_category'),
                'permission_callback' => array($this, 'check_courses_by_category_permissions'),
                'args' => array(
                    'category_slug' => array(
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => function($param) {
                            return is_string($param) && !empty($param);
                        },
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/courses',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_all_courses'),
                'permission_callback' => array($this, 'check_all_courses_permissions'),
            )
        );
    }
    

    /**
     * Get course details including reviews
     *
     * @param WP_REST_Request $request The request object.
     * @return array Course details
     */
    public function get_course_details() {
        return $this->service->get_course_details($this->course, $this->student);

    }
    
    /**
     * Get course details including reviews
     *
     * @param WP_REST_Request $request The request object.
     * @return array Course details
     */
    public function get_my_courses() {
        return $this->service->get_my_courses($this->student);
    }

    /**
     * Check if user has permission to access the endpoint
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */
    public function check_course_details_permissions($request) {
      // First check if the user is logged in
        if (!is_user_logged_in() ) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }
        
        // Check if the course exists and is a LifterLMS course
        $course_id = $request->get_param('course_id');
        $this->course = llms_get_post($course_id);
        if (!$this->course || 'course' !== $this->course->get('type')) {
            return new WP_Error(
                'llms_extend_course_not_found',
                __('Course not found or invalid course ID.', 'lifterlms-extend'),
                array('status' => 404)
            );
        }

        $this->student = llms_get_student( get_current_user_id() );
        // Check if the user is enrolled in the course
        if ( ! $this->student->is_enrolled( $course_id ) ) {
            return new WP_Error(
                'llms_extend_not_enrolled',
                __('You must be enrolled in this course to access its details.', 'lifterlms-extend'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Check if user has permission to access the endpoint
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */
    public function check_my_courses_permissions($request) {
      // First check if the user is logged in
        if (!is_user_logged_in() ) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }

        $this->student = llms_get_student( get_current_user_id() );
        // Check if the user is enrolled in the course
        if ( !$this->student) {
            return new WP_Error(
                'llms_extend_not_enrolled',
                __('You must be a student to access this endpoint.', 'lifterlms-extend'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Get course categories
     *
     * @param WP_REST_Request $request The request object.
     * @return array|WP_Error Course categories or error
     */
    public function get_course_categories($request) {
        $terms = get_terms(array(
            'taxonomy' => 'course_cat',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms)) {
            return new WP_Error(
                'llms_extend_get_categories_failed',
                __('Failed to retrieve course categories.', 'lifterlms-extend'),
                array('status' => 500)
            );
        }

        return array_map(function($term) {
            return array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'count' => $term->count,
            );
        }, $terms);
    }

    /**
     * Get courses by category
     *
     * @param WP_REST_Request $request The request object.
     * @return array|WP_Error Courses in category or error
     */
    public function get_courses_by_category($request) {
        $category_slug = $request->get_param('category_slug');
        $term = get_term_by('slug', $category_slug, 'course_cat');

        if (!$term) {
            return new WP_Error(
                'llms_extend_category_not_found',
                __('Category not found.', 'lifterlms-extend'),
                array('status' => 404)
            );
        }

        $courses = get_posts(array(
            'post_type' => 'course',
            'tax_query' => array(
                array(
                    'taxonomy' => 'course_cat',
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
            'posts_per_page' => -1,
        ));

        return array_map(function($course) {
            $fetchedCourse = llms_get_post($course->ID);
            return $this->service->get_course_details($fetchedCourse, $this->student);
        }, $courses);
    }

    /**
     * Check permissions for course categories endpoint
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */
    public function check_course_categories_permissions($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }
        return true;
    }

    /**
     * Check permissions for courses by category endpoint
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */
    public function check_courses_by_category_permissions($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }

        $this->student = llms_get_student( get_current_user_id() );

        return true;
    }

    /**
     * Get all courses
     *
     * @param WP_REST_Request $request The request object.
     * @return array|WP_Error All courses or error
     */
    public function get_all_courses($request) {
        $courses = get_posts(array(
            'post_type' => 'course',
            'posts_per_page' => -1,
        ));

        return array_map(function($course) {
            $fetchedCourse = llms_get_post($course->ID);
            return $this->service->get_course_details($fetchedCourse, $this->student);
        }, $courses);
    }

    /**
     * Check permissions for all courses endpoint
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */
    public function check_all_courses_permissions($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }

        $this->student = llms_get_student( get_current_user_id() );

        return true;
    }

}