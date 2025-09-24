<?php

namespace FluentSupportPro\App\Services\FileUploadIntegration\Dropbox;


use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        add_action('fluent_support/attachment_uploaded_as_temp_dropbox', [$this, 'maybeUploadToDropbox'], 10, 2);
        add_action('fluent_support/finalize_file_upload_dropbox', [$this, 'maybeUploadToDropbox'], 10, 2);

        add_filter('fluent_support_pro/file_storage_integration_settings_dropbox', [$this, 'getSettingsAndFields']);
        add_filter('fluent_support_pro/file_storage_integration_settings_save_dropbox', [$this, 'saveOAuthSettings'], 10, 2);

        add_action('fluent_support_pro/verify_dropbox_code', [$this, 'verifyDropboxCode']);
    }

    public function maybeUploadToDropbox($attachment, $ticketId)
    {
        if ($attachment->driver != 'local' || !$ticketId) {
            return;
        }

        $file = $attachment->file_path;
        if (!file_exists($file)) {
            return;
        }

        $accessToken = DropboxHelper::getAccessToken();
        if (is_wp_error($accessToken)) {
            return $this->handleUploadError($accessToken, $attachment);
        }

        $fileContent = file_get_contents($file);
        if (!$fileContent) {
            return;
        }

        $prefix = 'fluent_support-' . md5(uniqid(mt_rand(10000, 99999), true)) . '___';
        $fileName = $prefix . $attachment->title;

        $path = '/ticket_' . $ticketId . '/' . $fileName;

        $data = [
            'headers' => [
                'Authorization'   => 'Bearer ' . $accessToken,
                'Content-Type'    => 'application/octet-stream',
                'Dropbox-API-Arg' => json_encode([
                    'path'       => $path,
                    'mode'       => 'add',
                    'autorename' => false,
                    'mute'       => false
                ])
            ],
            'body'    => $fileContent,
        ];

        $request = wp_remote_post('https://content.dropboxapi.com/2/files/upload', $data);

        $response = $this->verifyAndFetchResponse($request, 'path_display');

        if (is_wp_error($response)) {
            return $this->handleUploadError($response, $attachment);
        }

        // Let's make the file public
        $url = 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings';
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json'
        ];

        $data = [
            'path'     => $response['path_display'],
            'settings' => [
                'access'               => 'viewer',
                'allow_download'       => true,
                'audience'             => 'public',
                'requested_visibility' => 'public'
            ]
        ];

        $request = wp_remote_post($url, [
            'headers' => $headers,
            'body'    => json_encode($data)
        ]);

        $response = $this->verifyAndFetchResponse($request, 'url');

        if (is_wp_error($response)) {
            return $this->handleUploadError($response, $attachment);
        }

        $attachment->file_path = 'dropbox://' . $response['id'];
        $attachment->full_url = esc_url($response['url']);
        $attachment->driver = 'dropbox';

        $settings = $attachment['settings'];
        if (!$settings) {
            $settings = [];
        }

        $settings['dropbox_id'] = $response['id'];
        $attachment->settings = $settings;

        $attachment->save();
    }

    public function saveOAuthSettings($response, $settings)
    {
        if (empty($settings['client_id']) || empty($settings['client_secret'])) {
            $settings = [
                'client_id'     => '',
                'client_secret' => '',
                'status'        => 'no'
            ];

            Helper::updateIntegrationOption('dropbox_settings', $settings);

            return [
                'message'      => __('Dropbox settings has been disconnected', 'fluent-support-pro'),
                'is_discarted' => true
            ];
        }

        $oldSettings = DropboxHelper::getSettings();

        $isNewSettings = $oldSettings['client_id'] != $settings['client_id'] || $oldSettings['client_secret'] != $settings['client_secret'];

        $oldSettings['client_id'] = $settings['client_id'];
        $oldSettings['client_secret'] = $settings['client_secret'];

        if ($isNewSettings || !empty($settings['do_reconnect']) || $oldSettings['status'] != 'yes' || !empty($settings['last_error'])) {
            $oldSettings['status'] = 'no';
            $oldSettings['last_error'] = '';
            $oldSettings['oauth_start'] = 'yes';
            DropboxHelper::updateSettings($oldSettings);

            // Now let's redirect to dropbox auth page
            $redirect = 'https://www.dropbox.com/oauth2/authorize?response_type=code&token_access_type=offline&client_id=' . $oldSettings['client_id'] . '&redirect_uri=' . urlencode(rest_url('fluent-support/v2/public/dropbox_auth'));

            return [
                'message'  => __('Please authorize your Dropbox account', 'fluent-support-pro'),
                'redirect' => $redirect
            ];
        }

        return [
            'message' => 'Dropbox settings has been saved'
        ];
    }

    public function verifyDropboxCode($code)
    {
        // verify dropbox oauth code and get access token
        $settings = DropboxHelper::getSettings();

        if (empty($settings['oauth_start'])) {
            return;
        }

        $url = 'https://api.dropbox.com/oauth2/token';
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $body = [
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'client_id'     => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'redirect_uri'  => rest_url('fluent-support/v2/public/dropbox_auth')
        ];

        $request = wp_remote_post($url, [
            'headers' => $headers,
            'body'    => $body
        ]);

        $response = $this->verifyAndFetchResponse($request, 'access_token');

        if (is_wp_error($response)) {
            $settings['last_error'] = $response->get_error_message();
            DropboxHelper::updateSettings($settings);
            return;
        }

        $settings['access_token'] = $response['access_token'];
        $settings['status'] = 'yes';
        $settings['last_error'] = '';
        unset($settings['oauth_start']);
        $settings['expire_at'] = $response['expires_in'] + time();
        $settings['account_id'] = $response['account_id'];
        $settings['refresh_token'] = $response['refresh_token'];

        DropboxHelper::updateSettings($settings);
        return;
    }

    private function handleUploadError($error, $attachment)
    {
        // we will do it later
    }

    public function verifyAndFetchResponse($response, $verifyKey = '')
    {
        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code > 299) {
            $error = wp_remote_retrieve_response_message($response);
            $errorText = sprintf(__('Unable to upload file in Dropbox %s', 'fluent-support-pro'), $error);
            return new \WP_Error('dropbox_upload_failed', $errorText);
        }

        $responseBody = wp_remote_retrieve_body($response);
        $responseBody = json_decode($responseBody, true);

        if ($verifyKey) {
            if (!isset($responseBody[$verifyKey])) {
                $errorText = __('Unable to upload file in Dropbox', 'fluent-support-pro');
                return new \WP_Error('dropbox_upload_failed', $errorText);
            }
        }

        return $responseBody;
    }


    public function getSettingsAndFields($data)
    {
        $allSettings = DropboxHelper::getSettings();
        $settings = Arr::only($allSettings, [
            'client_id', 'client_secret', 'status'
        ]);

        $configured = $settings['status'] == 'yes' && Arr::get($allSettings, 'access_token') && Arr::get($allSettings, 'refresh_token');

        $fieldConfig = [
            'title'       => 'Dropbox File Upload Settings',
            'component'   => 'DropboxSettings',
            'description' => sprintf(__('Please %1sread the documentation%2s first to get ClientID and Client Secret'), '<a href="https://fluentsupport.com/docs/dropbox-integration/" rel="noopener" target="_blank">', '</a>'),
            'fields'      => [
                'outh_redirect' => [
                    'type' => 'html-viewer',
                    'html' => '<p style="padding: 10px;background: #e8e8e9;border-radius: 5px;">' . sprintf(__('oAuth2 Redirect URL for Your APP: %s', 'fluent-support-pro'), '<code>' . rest_url('fluent-support/v2/public/dropbox_auth')) . '</code></p>'
                ],
                'client_id'     => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Dropbox Client ID', 'fluent-support-pro'),
                    'placeholder' => __('Dropbox Client ID', 'fluent-support-pro'),
                    'help'        => __('Enter Dropbox Client ID Here', 'fluent-support-pro')
                ],
                'client_secret' => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Dropbox Client Secret', 'fluent-support-pro'),
                    'placeholder' => __('Dropbox Client Secret', 'fluent-support-pro'),
                    'help'        => __('Enter Dropbox Client Secret Here', 'fluent-support-pro')
                ],
            ],
            'button_text' => ($configured) ? __('Save Settings', 'fluent-support-pro') : __('Connect to Dropbox', 'fluent-support-pro'),
            'has_discard' => $configured
        ];

        return [
            'settings'     => $settings,
            'fieldsConfig' => $fieldConfig
        ];
    }
}
