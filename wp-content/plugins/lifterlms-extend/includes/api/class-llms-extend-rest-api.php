<?php
/**
 * LifterLMS Extend REST API Controller
 */

defined('ABSPATH') || exit;

// Load controllers
require_once plugin_dir_path(__FILE__) . 'controllers/class-llms-extend-rest-course-controller.php';
require_once plugin_dir_path(__FILE__) . 'controllers/class-llms-extend-rest-review-controller.php';

/**
 * REST API Controller class
 */
class LLMS_Extend_REST_API {

    private $course;
    private $course_controller;
    private $review_controller;

    /**
     * API namespace
     *
     * @var string
     */
    private $namespace = 'llms-extend/v1';

    /**
     * Constructor
     */
    public function __construct() {
        $this->course_controller = new LLMS_Extend_REST_Course_Controller();
        $this->review_controller = new LLMS_Extend_REST_Review_Controller();

        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        $this->course_controller->register_routes($this->namespace);
        $this->review_controller->register_routes($this->namespace);
    }

}