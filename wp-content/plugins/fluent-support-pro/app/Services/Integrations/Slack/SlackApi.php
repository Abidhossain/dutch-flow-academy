<?php

namespace FluentSupportPro\App\Services\Integrations\Slack;

use FluentSupportPro\App\Services\Integrations\Slack\SlackHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class SlackApi
{
    private static $apiUrl = 'https://slack.com/api/chat.postMessage';

    public static function send($message, $threadId = false)
    {

        $settings = SlackHelper::getSettings();

        $data = [
            'attachments' => json_encode($message),
            'channel'     => $settings['channel'],
            'token'       => $settings['bot_token']
        ];

        if ($threadId) {
            $data['thread_ts'] = $threadId;
        }

        $data = apply_filters('fluent_support/slack_api_data', $data, $message);

        $response = wp_remote_request(self::$apiUrl, [
            'method' => 'POST',
            'body'   => $data,
            'header' => [
                'content-type' => 'application/x-www-form-urlencoded'
            ]
        ]);


        if (is_wp_error($response)) {
            return new \WP_Error($response->get_error_code(), $response->get_error_message());
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (!$response) {
            return new \WP_Error('Slack_Error', 'Slack API Request Failed');
        }

        if (!empty($response['error'])) {
            return new \WP_Error('Slack_Error', $response['error']);
        }

        return $response;
    }
}
