<?php

namespace FluentSupportPro\App\Services\FileUploadIntegration\GoogleDrive;

use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        add_action('fluent_support/attachment_uploaded_as_temp_google_drive', [$this, 'maybeUploadToDrive'], 10, 2);
        add_action('fluent_support/finalize_file_upload_google_drive', [$this, 'maybeUploadToDrive'], 10, 2);

        add_filter('fluent_support_pro/file_storage_integration_settings_google_drive', [$this, 'getSettingsAndFields']);
        add_filter('fluent_support_pro/file_storage_integration_settings_save_google_drive', [$this, 'saveOAuthSettings'], 10, 2);

    }

    public function getSettingsAndFields($data)
    {
        $allSettings = GoogleDriveHelper::getSettings();
        $settings = Arr::only($allSettings, [
            'client_id', 'client_secret', 'status'
        ]);

        $configured = $settings['status'] == 'yes';

        $fieldConfig = [
            'title'       => 'Google Drive File Upload Settings',
            'component'   => 'GoogleSettings',
            'description' => sprintf(__('Please %1sread the documentation%2s first to get ClientID and Client Secret'), '<a href="https://fluentsupport.com/docs/google-drive-integration/" rel="noopener" target="_blank">', '</a>'),
            'fields'      => [
                'outh_redirect' => [
                    'type' => 'html-viewer',
                    'html' => '<p style="padding: 10px;background: #e8e8e9;border-radius: 5px;">' . sprintf(__('oAuth2 Redirect URL for Your APP: %s', 'fluent-support-pro'), '<code>' . rest_url('fluent-support/v2/public/google_auth')) . '</code></p>'
                ],
                'client_id'     => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Google App Client ID', 'fluent-support-pro'),
                    'placeholder' => __('Google App ID', 'fluent-support-pro'),
                    'help'        => __('Enter Google App Client ID Here', 'fluent-support-pro')
                ],
                'client_secret' => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Google App Client Secret', 'fluent-support-pro'),
                    'placeholder' => __('Google App Client Secret', 'fluent-support-pro'),
                    'help'        => __('Enter Google App Client Secret Here', 'fluent-support-pro')
                ],
            ],
            'button_text' => ($configured) ? __('Save Settings', 'fluent-support-pro') : __('Connect to Google Drive', 'fluent-support-pro'),
            'has_discard' => $configured,
            'last_error'  => Arr::get($allSettings, 'last_error')
        ];

        $settings['redirect_uri'] = rest_url('fluent-support/v2/public/google_auth');

        return [
            'settings'     => $settings,
            'all_settings' => $allSettings,
            'fieldsConfig' => $fieldConfig
        ];
    }

    public function saveOAuthSettings($response, $settings)
    {
        if ((Arr::get($settings, 'is_oauth_flow') && !empty($settings['access_token'])) || !empty($settings['do_reconnect']) ) {

            $newSettings = Arr::only($settings, ['access_token', 'refresh_token', 'expires_in']);

            $oldSettings = GoogleDriveHelper::getSettings();

            $settings = wp_parse_args($newSettings, $oldSettings);

            $settings['status'] = 'yes';
            $settings['last_error'] = '';
            $settings['is_encrypted'] = 'yes';
            $settings['folder_id'] = '';
            $settings['folder_name'] = '';

            $settings['expire_at'] = (int)$settings['expires_in'] + time();
            unset($settings['expires_in']);
            unset($settings['oauth_start']);

            GoogleDriveHelper::updateSettings($settings);

            return [
                'message' => __('Google Drive settings has been saved', 'fluent-support-pro')
            ];
        }

        if (empty($settings['client_id']) || empty($settings['client_secret'])) {
            $settings = [
                'client_id'     => '',
                'client_secret' => '',
                'status'        => 'no'
            ];

            Helper::updateIntegrationOption('google_drive_settings', $settings);

            return [
                'message'      => __('Google Drive connection has been disconnected', 'fluent-support-pro'),
                'is_discarted' => true
            ];
        }

        $oldSettings = GoogleDriveHelper::getSettings();

        $isNewSettings = $oldSettings['client_id'] != $settings['client_id'] || $oldSettings['client_secret'] != $settings['client_secret'];

        $oldSettings['client_id'] = $settings['client_id'];
        $oldSettings['client_secret'] = $settings['client_secret'];

        if ($isNewSettings || $oldSettings['status'] != 'yes' || !empty($settings['last_error'])) {
            $oldSettings['status'] = 'no';
            $oldSettings['last_error'] = '';
            $oldSettings['oauth_start'] = 'yes';
            GoogleDriveHelper::updateSettings($oldSettings);

            $redirect = add_query_arg([
                'client_id'     => $oldSettings['client_id'],
                'redirect_uri'  => rest_url('fluent-support/v2/public/google_auth'),
                'response_type' => 'code',
                'access_type'   => 'offline',
                'scope'         => 'https://www.googleapis.com/auth/drive.file',
                'prompt'        => 'consent'
            ], 'https://accounts.google.com/o/oauth2/auth');

            return [
                'message'  => __('Please authorize your Google Drive API', 'fluent-support-pro'),
                'redirect' => $redirect
            ];
        }

        return [
            'message' => 'Google API settings has been saved'
        ];
    }

    public function maybeUploadToDrive($attachment, $ticketId)
    {
        if ($attachment->driver != 'local' || !$ticketId) {
            return;
        }

        $response = (new GoogleDriveApi())->uploadToDrive($attachment, $ticketId);

        if (is_wp_error($response)) {
            return;
        }

        $settings = $attachment->settings;

        if (!$settings) {
            $settings[] = [];
        }

        $settings['gdrive_id'] = $response['file_id'];
        $attachment->settings = $settings;
        $attachment->file_path = 'gdrive://' . $response['file_id'];
        $attachment->full_url = esc_url($response['full_url']);
        $attachment->driver = 'google_drive';
        $attachment->save();
    }
}
