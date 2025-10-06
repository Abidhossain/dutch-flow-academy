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
      }
    

    /**
     * Get course details including reviews
     *
     * @param WP_REST_Request $request The request object.
     * @return array Course details
     */
    public function get_course_details() {
        return $this->service->get_course_details($this->course);

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

        $student = llms_get_student( get_current_user_id() );
        // Check if the user is enrolled in the course
        if ( ! $student->is_enrolled( $course_id ) ) {
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

}