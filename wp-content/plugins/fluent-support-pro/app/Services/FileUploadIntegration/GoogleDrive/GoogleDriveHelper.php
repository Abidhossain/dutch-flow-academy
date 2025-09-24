<?php

namespace FluentSupportPro\App\Services\FileUploadIntegration\GoogleDrive;

use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\ProHelper;

class GoogleDriveHelper
{
    public static function getSettings()
    {
        $settings = Helper::getIntegrationOption('google_drive_settings', []);
        $defaults = [
            'client_id'     => '',
            'client_secret' => '',
            'status'        => 'no',
            'access_token'  => '',
            'refresh_token' => '',
            'expire_at'     => '',
            'is_encrypted'  => 'no'
        ];

        if (!$settings) {
            return $defaults;
        }

        $settings = wp_parse_args($settings, $defaults);

        if ($settings['status'] != 'yes') {
            return $settings;
        }

        if ($settings['is_encrypted'] == 'no' && !empty($settings['access_token'])) {
            $settings['is_encrypted'] = 'yes';
            self::updateSettings($settings);
            return $settings;
        }

        if ($settings['is_encrypted'] == 'yes') {
            $settings['access_token'] = ProHelper::decryptKey($settings['access_token']);
            $settings['refresh_token'] = ProHelper::decryptKey($settings['refresh_token']);
        }

        return $settings;
    }

    public static function getAccessToken()
    {
        $settings = self::getSettings();

        if (empty($settings['access_token']) || Arr::get($settings, 'status') != 'yes') {
            return new \WP_Error('no_access_token', __('No access token found', 'fluent-support-pro'));
        }

        if ($settings['expire_at'] - time() < 30) {
            $settings = self::refreshAccessToken($settings);
            if (is_wp_error($settings)) {
                return $settings;
            }
        }

        if ($error = Arr::get($settings, 'last_error')) {
            return new \WP_Error('last_error', $error);
        }

        return $settings['access_token'];
    }

    public static function updateSettings($settings)
    {
        if (!empty($settings['access_token'])) {
            $settings['access_token'] = ProHelper::encryptKey($settings['access_token']);
        }

        if (!empty($settings['refresh_token'])) {
            $settings['refresh_token'] = ProHelper::encryptKey($settings['refresh_token']);
        }

        $settings['is_encrypted'] = 'yes';

        Helper::updateIntegrationOption('google_drive_settings', $settings);
    }

    public static function refreshAccessToken($settings)
    {
        $url = 'https://oauth2.googleapis.com/token';

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $body = [
            'refresh_token' => $settings['refresh_token'],
            'grant_type'    => 'refresh_token',
            'client_id'     => $settings['client_id'],
            'client_secret' => $settings['client_secret']
        ];

        $request = wp_remote_post($url, [
            'headers' => $headers,
            'body'    => $body
        ]);

        $response = (new GoogleDriveApi())->verifyAndFetchResponse($request, 'access_token');

        if (is_wp_error($response)) {
            $settings['last_error'] = $response->get_error_message();
            self::updateSettings($settings);
            return $response;
        }

        $settings['last_error'] = '';
        $settings['access_token'] = $response['access_token'];
        $settings['expire_at'] = time() + $response['expires_in'];

        self::updateSettings($settings);

        return $settings;
    }
}
