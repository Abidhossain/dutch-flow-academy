<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;
use FluentSupportPro\App\Services\ProHelper;

class TicketFormController extends Controller
{

    /**
     * getSettings method will return the configuration for ticket form and fields property
     * @param Request $request
     * @return array
     */
    public function getSettings(Request $request)
    {
        $settings = ProHelper::getTicketFormConfig();

        return [
            'settings'        => $settings,
            'settings_fields' => $this->getSettingsFields()
        ];

    }


    /**
     * saveSettings method will store the settings related to ticket form configuration
     * @param Request $request
     * @return array
     */
    public function saveSettings(Request $request)
    {
        //get list of settings from request
        $settings = $request->get('settings', []);

        //Format data to insert/update in meta table
        foreach ($settings['field_labels'] as $fieldKey => $value) {
            $settings['field_labels'][$fieldKey] = sanitize_text_field($value);
        }

        //Store settings information into meta table
        Helper::updateOption('_ticket_form_settings', $settings);

        return [
            'message' => __('Settings has been updated', 'fluent-support-pro')
        ];

    }


    /**
     * getSettingsFields method will return the list of fields, and it's property for ticket form configuration
     * @return array
     */
    public function getSettingsFields()
    {
        $postTypes = apply_filters('fluent_support/all_doc_post_types', get_post_types([
            'public'             => true,
            'publicly_queryable' => true
        ]));


        if (!function_exists('get_editable_roles')) {
            require_once(ABSPATH . '/wp-admin/includes/user.php');
        }

        $roles = \get_editable_roles();

        $formattedRoles = [];

        foreach ($roles as $roleKey => $role) {
            $formattedRoles[$roleKey] = $role['name'];
        }

        unset($formattedRoles['administrator']);

       asort($formattedRoles);

        $fields = [
            'enable_docs'       => [
                'type'           => 'inline-checkbox',
                'checkbox_label' => 'Enable knowledge base suggestion on ticket creation form',
                'true_label'     => 'yes',
                'false_label'    => 'no'
            ],
            'docs_post_types'   => [
                'type'          => 'checkbox-group',
                'label'         => 'Knowledge Base post types',
                'options'       => $postTypes,
                'inline_help'   => 'Select the post types that you want to show articles from',
                'wrapper_class' => 'fs_half_field',
                'dependency'    => [
                    'depends_on' => 'enable_docs',
                    'operator'   => '=',
                    'value'      => 'yes'
                ]
            ],
            'post_limits'       => [
                'type'          => 'input-text',
                'data_type'     => 'number',
                'label'         => 'Suggested Articles Limit',
                'wrapper_class' => 'fs_half_field',
                'dependency'    => [
                    'depends_on' => 'enable_docs',
                    'operator'   => '=',
                    'value'      => 'yes'
                ]
            ],
            'disabled_fields'   => [
                'type'          => 'checkbox-group',
                'label'         => 'Disabled Default Fields',
                'wrapper_class' => 'fs_half_field',
                'options'       => [
                    'file_upload'      => 'File Upload',
                    'priority'         => 'Priority',
                    'product_services' => 'Product & Services'
                ],
                'inline_help'   => 'Checked fields will not be available on create ticket form',
            ],
            'disable_rich_text' => [
                'type'           => 'inline-checkbox',
                'checkbox_label' => 'Disable Rich Text Editor for Frontend',
                'true_label'     => 'yes',
                'wrapper_class'  => 'fs_half_field',
                'false_label'    => 'no'
            ],
            'product_required_field' => [
                'type'           => 'inline-checkbox',
                'checkbox_label' => 'Required fields for products and services.',
                'true_label'     => 'yes',
                'wrapper_class'  => 'fs_half_field',
                'false_label'    => 'no',
                'dependency'    => [
                    'depends_on' => 'disabled_fields',
                    'operator'   => 'not_contains',
                    'value'      => 'product_services'
                ],
            ],
            'submitter_type' => [
                'type' => 'input-radio',
                'label' => 'Who can access customer portal?',
                'options' => [
                    [
                        'id' => 'logged_in_users',
                        'label' => 'Any logged in users'
                    ],
                    [
                        'id' => 'allowed_user_roles',
                        'label' => 'Only selected user roles'
                    ]
                ]
            ],
            'allowed_user_roles' => [
                'type' => 'checkbox-group',
                'label' => 'Select Users Roles for Customer Portal',
                'options' => $formattedRoles,
                'dependency'    => [
                    'depends_on' => 'submitter_type',
                    'operator'   => '=',
                    'value'      => 'allowed_user_roles'
                ]
            ],
            'field_labels'      => [
                'label'        => 'Form Labels Customization',
                'source_label' => 'Field',
                'new_label'    => 'Input Label',
                'type'         => 'object-tabular-input',
                'options'      => [
                    'subject'           => __('Subject heading', 'fluent-support-pro'),
                    'ticket_details'    => __('Form content heading', 'fluent-support-pro'),
                    'details_help'      => __('Content help message', 'fluent-support-pro'),
                    'product_services'  => __('Product/Service heading', 'fluent-support-pro'),
                    'priority'          => __('Priority heading', 'fluent-support-pro'),
                    'btn_text'          => __('Create ticket button text', 'fluent-support-pro'),
                    'submit_heading'    => __('Create ticket page heading', 'fluent-support-pro'),
                    'create_ticket_cta' => __('Ticket Create Call to Action', 'fluent-support-pro')
                ]
            ]
        ];

        if (defined('WC_PLUGIN_FILE')) {
            $fields['enable_woo_menu'] = [
                'type'           => 'inline-checkbox',
                'checkbox_label' => 'Add support link to WooCommerce account navigation',
                'true_label'     => 'yes',
                'false_label'    => 'no'
            ];
        }

        return $fields;
    }


}
