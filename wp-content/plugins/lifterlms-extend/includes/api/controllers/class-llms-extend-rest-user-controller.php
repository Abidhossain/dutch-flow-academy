<?php
/**
 * Course Details REST API Controller
 */

defined('ABSPATH') || exit;

/**
 * Course Details API Controller class
 */
class LLMS_Extend_REST_User_Controller {
   
    private $user;

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function register_routes($namespace) {
        register_rest_route(
            $namespace,
            '/my-profile',
              array(
                  'methods' => WP_REST_Server::READABLE,
                  'callback' => array($this, 'get_my_profile'),
                  'permission_callback' => array($this, 'check_my_profile_permissions'),
              )
        );
      }


    public function get_my_profile() {
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        $student = llms_get_student( $user_id );
        return array_merge(
            array(
                'avatar' => get_avatar_url( $user->ID ),
                'first_name' => $student ? $student->get('first_name') : '',
                'last_name' => $student ? $student->get('last_name') : '',
                'display_name' => $student ? $student->get('display_name') : '',
                'username' => $student ? $student->get('user_login') : '',
                'email' => $student ? $student->get('email_address') : '',
                'address' => $student ? $student->get('llms_billing_address_1') : '',
                'address_2' => $student ? $student->get('llms_billing_address_2') : '',
                'country' => $student ? $student->get('llms_billing_country') : '',
                'city' => $student ? $student->get('llms_billing_city') : '',
                'state' => $student ? $student->get('llms_billing_state') : '',
                'zip' => $student ? $student->get('llms_billing_zip') : '',
                'zip' => $student ? $student->get('zip') : '',
                'phone' => $student ? $student->get('llms_phone') : '',
            )
        );
    }
    
    /**
     * Permission check for the "my profile" endpoint.
     * @return bool
     */
    public function check_my_profile_permissions() {
        return is_user_logged_in();
    }


}