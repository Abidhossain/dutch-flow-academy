<?php
/**
 * Review Submission REST API Controller
 */

defined('ABSPATH') || exit;

/**
 * Review Submission API Controller class
 */
class LLMS_Extend_REST_Review_Controller {
    /**
     * Register REST API routes
     */
    public function register_routes($namespace) {
        register_rest_route(
            $namespace,
            '/courses/(?P<course_id>\d+)/reviews',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_course_reviews'),
                    'permission_callback' => array($this, 'get_reviews_permissions_check'),
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
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_course_review'),
                    'permission_callback' => array($this, 'create_review_permissions_check'),
                    'args' => array(
                        'course_id' => array(
                            'required' => true,
                            'type' => 'integer',
                        ),
                        'title' => array(
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ),
                        'content' => array(
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_textarea_field',
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Check if user has permission to get reviews
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function get_reviews_permissions_check($request) {
        $course_id = $request->get_param('course_id');

        // Check if course exists
        $course = llms_get_post($course_id);
        if (!$course || 'course' !== $course->get('type')) {
            return new WP_Error(
                'llms_extend_course_not_found',
                __('Course not found or invalid course ID.', 'lifterlms-extend'),
                array('status' => 404)
            );
        }

        // Check if reviews are enabled for this course
        if (!get_post_meta($course_id, '_llms_display_reviews', true)) {
            return new WP_Error(
                'llms_extend_reviews_disabled',
                __('Reviews are disabled for this course.', 'lifterlms-extend'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Check if user has permission to create a review
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function create_review_permissions_check($request) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to submit a review.', 'lifterlms-extend'),
                array('status' => rest_authorization_required_code())
            );
        }

        $course_id = $request->get_param('course_id');
        
        // Check if course exists and reviews are enabled
        $course = llms_get_post($course_id);
        if (!$course || 'course' !== $course->get('type')) {
            return new WP_Error(
                'llms_extend_course_not_found',
                __('Course not found or invalid course ID.', 'lifterlms-extend'),
                array('status' => 404)
            );
        }

        // Check if reviews are enabled
        if (!get_post_meta($course_id, '_llms_reviews_enabled', true)) {
            return new WP_Error(
                'llms_extend_reviews_disabled',
                __('Reviews are disabled for this course.', 'lifterlms-extend'),
                array('status' => 403)
            );
        }

        // Check if user is enrolled in the course
        $student = llms_get_student(get_current_user_id());
        if (!$student->is_enrolled($course_id)) {
            return new WP_Error(
                'llms_extend_not_enrolled',
                __('You must be enrolled in this course to submit a review.', 'lifterlms-extend'),
                array('status' => 403)
            );
        }

        // Check if user can write a review
        if (!LLMS_Reviews::current_user_can_write_review($course_id)) {
            return new WP_Error(
                'llms_extend_review_limit',
                __('You have already submitted a review for this course.', 'lifterlms-extend'),
                array('status' => 403)
            );
        }

        return true;
    }

    /**
     * Get course reviews
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_course_reviews($request) {
        $course_id = $request->get_param('course_id');
        
        $args = array(
            'posts_per_page' => get_post_meta($course_id, '_llms_num_reviews', true),
            'post_type' => 'llms_review',
            'post_status' => 'publish',
            'post_parent' => $course_id,
            'suppress_filters' => true,
        );

        $reviews = get_posts($args);
        $formatted_reviews = array();

        foreach ($reviews as $review) {
            $author_id = get_post_field('post_author', $review->ID);
            $formatted_reviews[] = array(
                'id' => $review->ID,
                'title' => get_the_title($review->ID),
                'content' => get_post_field('post_content', $review->ID),
                'date' => get_the_date('c', $review->ID),
                'author' => array(
                    'id' => $author_id,
                    'name' => get_the_author_meta('display_name', $author_id),
                    'avatar_url' => get_avatar_url($author_id, array('size' => 96))
                )
            );
        }

        return rest_ensure_response($formatted_reviews);
    }

    /**
     * Create a course review
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function create_course_review($request) {
        $course_id = $request->get_param('course_id');
        $title = $request->get_param('title');
        $content = $request->get_param('content');

        $post_data = array(
            'post_content' => $content,
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => 'llms_review',
            'post_parent' => $course_id,
            'post_author' => get_current_user_id(),
        );

        $review_id = wp_insert_post($post_data, true);

        if (is_wp_error($review_id)) {
            return $review_id;
        }

        $response = array(
            'id' => $review_id,
            'title' => $title,
            'content' => $content,
            'date' => get_the_date('c', $review_id),
            'author' => array(
                'id' => get_current_user_id(),
                'name' => wp_get_current_user()->display_name,
                'avatar_url' => get_avatar_url(get_current_user_id(), array('size' => 96))
            )
        );

        return rest_ensure_response($response);
    }
}