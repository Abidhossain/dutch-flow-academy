<?php
/**
 * Course Details REST API Controller
 */

defined('ABSPATH') || exit;

// Load services
require_once plugin_dir_path(__FILE__) . '../services/class-llms-extend-rest-course-lesson-service.php';

/**
 * Course Details API Controller class
 */
class LLMS_Extend_REST_Course_Lesson_Controller {
   
    private $service;
    private $course;
    private $student;
    private $lesson;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new LLMS_Extend_REST_Course_Lesson_Service();
    }
    /**
     * Register REST API routes
     *
     * @return void
     */
    public function register_routes($namespace) {
        register_rest_route(
            $namespace,
            '/lessons/(?P<lesson_id>\d+)/details',
              array(
                  'methods' => WP_REST_Server::READABLE,
                  'callback' => array($this, 'get_lesson_details'),
                  'permission_callback' => array($this, 'check_lesson_permissions'),
              )
        );
        register_rest_route(
            $namespace,
            '/lessons/(?P<lesson_id>\d+)/complete',
              array(
                  'methods' => WP_REST_Server::CREATABLE,
                  'callback' => array($this, 'mark_lesson_as_completed'),
                  'permission_callback' => array($this, 'check_lesson_permissions'),
              )
        );
        register_rest_route(
            $namespace,
            '/lessons/(?P<lesson_id>\d+)/incomplete',
              array(
                  'methods' => WP_REST_Server::CREATABLE,
                  'callback' => array($this, 'mark_lesson_as_incomplete'),
                  'permission_callback' => array($this, 'check_lesson_permissions'),
              )
        );
        register_rest_route(
            $namespace,
            '/lessons/(?P<lesson_id>\d+)/comments',
            array(
                // 🟢 GET: Fetch all comments with replies
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_lesson_comments'),
                    'permission_callback' => array($this, 'check_lesson_permissions'),
                    'args'                => array(
                        'lesson_id' => array(
                            'required'          => true,
                            'type'              => 'integer',
                            'sanitize_callback' => 'absint',
                        ),
                    ),
                ),

                // 🟡 POST: Add new comment or reply
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'add_lesson_comment'),
                    'permission_callback' => array($this, 'check_lesson_permissions'),
                    'args'                => array(
                        'lesson_id' => array(
                            'required' => true,
                            'type'     => 'integer',
                            'sanitize_callback' => 'absint',
                        ),
                        'content' => array(
                            'required' => true,
                            'type'     => 'string',
                        ),
                        'parent_id' => array(
                            'required' => false,
                            'type'     => 'integer',
                            'sanitize_callback' => 'absint',
                        ),
                    ),
                ),

                // 🔴 DELETE: Delete a comment
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array($this, 'delete_lesson_comment'),
                    'permission_callback' => array($this, 'check_lesson_delete_comment_permissions'),
                    'args'                => array(
                        'comment_id' => array(
                            'required'          => true,
                            'type'              => 'integer',
                            'sanitize_callback' => 'absint',
                        ),
                    ),
                ),
            )
        );

      }
    
    /**
     * Get lesson details including completion status
     *
     * @param WP_REST_Request $request The request object.
     * @return array Lesson details
     */
    public function get_lesson_details() {
        return $this->service->get_lesson_details($this->lesson);
    }

    public function mark_lesson_as_completed() {
        $this->service->mark_lesson_as_completed($this->student, $this->lesson);
        return array('success' => true, 'message' => __('Lesson marked as completed.', 'lifterlms-extend'));

    }

    public function mark_lesson_as_incomplete() {
        $this->service->mark_lesson_as_incomplete($this->student, $this->lesson);
        return array('success' => true, 'message' => __('Lesson marked as incomplete.', 'lifterlms-extend'));
    }

    public function get_lesson_comments( WP_REST_Request $request ) {
        $lesson_id = absint( $request->get_param('lesson_id') );
        return $this->service->get_lesson_comments( $lesson_id );
    }

    public function add_lesson_comment( WP_REST_Request $request ) {
        $lesson_id = absint( $request->get_param('lesson_id') );
        $content   = sanitize_text_field( $request->get_param('content') );
        $parent_id = $request->get_param('parent_id') ? absint( $request->get_param('parent_id') ) : 0;

        $this->service->add_lesson_comment( $lesson_id, $content, $parent_id );

        return [ 'success' => true, 'message' => 'Comment added successfully.' ];
    }

    public function delete_lesson_comment( WP_REST_Request $request ) {
        $this->service->delete_lesson_comment( $request->get_param('comment_id') );

        return [ 'success' => true, 'message' => 'Comment deleted successfully.' ];
    }
    /**
     * Check if user has permission to access the endpoint
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */    
    public function check_lesson_permissions($request) {
      // First check if the user is logged in
        if (!is_user_logged_in() ) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }

        $this->student = llms_get_student( get_current_user_id() );
        if ( !$this->student) {
          return new WP_Error(
              'llms_extend_not_enrolled',
              __('You must be a student to access this endpoint.', 'lifterlms-extend'),
              array('status' => 403)
          );
        }
          
        $lesson_id = $request->get_param('lesson_id');
        $this->lesson = llms_get_post($lesson_id);
        if (!$this->lesson || 'lesson' !== $this->lesson->get('type')) {
            return new WP_Error(
                'llms_extend_lesson_not_found',
                __('Lesson not found or invalid lesson ID.', 'lifterlms-extend'),
                array('status' => 404)
            );
        }

        $course_id = $this->lesson->get('parent_course');
        if ( ! $this->student->is_enrolled( $course_id ) ) {
            return new WP_Error(
                'llms_extend_not_enrolled',
                __('You must be enrolled in the course containing this lesson to access it.', 'lifterlms-extend'),
                array('status' => 403)
            );
          }
        return true;
    }

    /**
     * Permission check for deleting lesson comments.
     */
    public function check_lesson_delete_comment_permissions( WP_REST_Request $request ) {
        $comment_id = $request->get_param('comment_id');
        $comment    = get_comment($comment_id);
        $user       = wp_get_current_user();

        if (!$user || 0 === $user->ID) {
            return new WP_Error('rest_forbidden', 'You must be logged in to perform this action.', ['status' => 401]);
        }

        if (!$comment) {
            return new WP_Error('not_found', 'Comment not found.', ['status' => 404]);
        }

        // Allow admins or moderators
        if (current_user_can('moderate_comments')) {
            return true;
        }

        // Allow comment owner only
        if ((int) $comment->user_id === (int) $user->ID) {
            return true;
        }

        return new WP_Error('forbidden', 'You do not have permission to delete this comment.', ['status' => 403]);
    }

}