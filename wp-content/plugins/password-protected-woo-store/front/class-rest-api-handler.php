<?php
/** Get options of whole site settings */
$ppws_advanced_options = get_option('ppws_advanced_settings');
$rest_api_field_name = 'enable_rest_api_protection_checkbox';
if (isset($ppws_advanced_options[$rest_api_field_name]) && $ppws_advanced_options[$rest_api_field_name] == '') return;

/* Rest api handler class() for protected data */
if (!class_exists('ppws_wp_rest_api_handler')) {

    /** Get options of whole site settings */
    $ppws_whole_site_options = get_option('ppws_general_settings');

    /** Get options of page settings */
    $ppws_page_options = get_option('ppws_page_settings');

    /** Get options of product settings */
    $ppws_product_options = get_option('ppws_product_settings');

    /** Get options of product settings */
    $ppws_product_categories_options = get_option('ppws_product_categories_settings');

    class ppws_wp_rest_api_handler {

        public function __construct() {
            if (!is_admin()) {
                add_filter('rest_prepare_page', array($this, 'page_content_json'), 10, 3);
                add_filter('rest_prepare_product', array($this, 'product_content_json'), 10, 3);
                add_filter('rest_authentication_errors', array($this, 'global_protection_wp_rest_api'));
            }
        }

        public function page_content_json($data, $page, $request) {
            ppws_nocache_headers();

            global $ppws_page_options;
            $ppws_page_cookie = (ppws_get_cookie('ppws_page_cookie') != '') ? ppws_get_cookie('ppws_page_cookie') : '';
            $ppws_page_main_password = (isset($ppws_page_options['ppws_page_set_password_field_textbox'])) ? $ppws_page_options['ppws_page_set_password_field_textbox'] : '';

            // Allow access if the user is authenticated
            if (is_user_logged_in()) {
                return $data;
            }

            if (ppws_decrypted_password($ppws_page_cookie) != ppws_decrypted_password($ppws_page_main_password)) {
                $page_field_name = "ppws_page_list_of_page_field_checkbox";
                $selected_page = (isset($ppws_page_options[$page_field_name]) && !empty($ppws_page_options[$page_field_name])) ? explode(",", $ppws_page_options[$page_field_name]) : array();

                if (in_array($data->data['id'], $selected_page)) {
                    if (isset($data->data['content']['rendered'])) $data->data['content']['rendered'] = "";
                    if (isset($data->data['excerpt']['rendered'])) $data->data['excerpt']['rendered'] = "";
                }
            }

            return $data;
        }

        public function product_content_json($data, $product, $request) {
            ppws_nocache_headers();

            global $ppws_product_options, $ppws_product_categories_options;
            $ppws_categories_cookie = ppws_get_cookie('ppws_categories_cookie');
            $ppws_categories_main_password = (isset($ppws_product_categories_options['ppws_product_categories_password'])) ? $ppws_product_categories_options['ppws_product_categories_password'] : '';

            // Allow access if the user is authenticated
            if (is_user_logged_in()) {
                return $data;
            }

            if (ppws_decrypted_password($ppws_categories_cookie) != ppws_decrypted_password($ppws_categories_main_password)) {
                $selected_cat = "";
                $cat_field = "ppws_product_categories_all_categories_field_checkbox";

                $selected_cat = (isset($ppws_product_categories_options[$cat_field]) && !empty($ppws_product_categories_options[$cat_field])) ? $ppws_product_categories_options[$cat_field] : array();
                if (isset($ppws_product_categories_options[$cat_field]) && !empty($ppws_product_categories_options[$cat_field])) $selected_cat = explode(",", $ppws_product_categories_options[$cat_field]);

                if (isset($data->data['product_cat']) && !empty($data->data['product_cat'])) {
                    $jsonContainsCategory = !empty(array_intersect($data->data['product_cat'], $selected_cat));
                    if (!empty($jsonContainsCategory)) {
                        if (isset($data->data['content']['rendered'])) $data->data['content']['rendered'] = "";
                        if (isset($data->data['excerpt']['rendered'])) $data->data['excerpt']['rendered'] = "";
                    }
                }
            }

            $ppws_product_cookie = (ppws_get_cookie('ppws_product_cookie') != '') ? ppws_get_cookie('ppws_product_cookie') : '';
            $ppws_product_main_password = (isset($ppws_product_options['product_set_password_field_textbox'])) ? $ppws_product_options['product_set_password_field_textbox'] : '';

            if (ppws_decrypted_password($ppws_product_cookie) != ppws_decrypted_password($ppws_product_main_password)) {
                $product_field_name = "product_list_of_product_field_checkbox";
                $selected_product = (isset($ppws_product_options[$product_field_name]) && !empty($ppws_product_options[$product_field_name])) ? explode(",", $ppws_product_options[$product_field_name]) : array();

                if (in_array($data->data['id'], $selected_product)) {
                    if (isset($data->data['content']['rendered'])) $data->data['content']['rendered'] = "";
                    if (isset($data->data['excerpt']['rendered'])) $data->data['excerpt']['rendered'] = "";
                }
            }

            return $data;
        }


        public function global_protection_wp_rest_api($access) {
            ppws_nocache_headers();

            global $ppws_whole_site_options;

            // Check if the site is protected
            if (ppws_is_protected_whole_site()) {
                $ppws_cookie = ppws_get_cookie('ppws_cookie');
                $ppws_main_password = (isset($ppws_whole_site_options['ppws_set_password_field_textbox'])) ? $ppws_whole_site_options['ppws_set_password_field_textbox'] : '';

                // Allow access if the user is authenticated
                if (is_user_logged_in()) {
                    return $access;
                }

                // Allow access if the correct password is provided
                if (ppws_decrypted_password($ppws_cookie) != ppws_decrypted_password($ppws_main_password)) {
                    return new WP_Error('rest_cannot_access', __('Whole site is password protected so REST API cannot be accessed.', 'password-protected-store-for-woocommerce'), array('status' => rest_authorization_required_code()));
                } else {
                    return $access;
                }
            }

            return $access; // Default access
        }
    }
    new ppws_wp_rest_api_handler();
}
