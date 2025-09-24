<?php

namespace FluentSupport\App\Services\Integrations\FluentBot;

use WP_Error;

class FluentBotAPI
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct(string $apiKey, string $apiUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    public function makeRequest(int $ticketId, array $args = [], $prompt)
    {
        $response = $this->sendRequest($args);

        if (is_wp_error($response)) {
            $message = $response->get_error_message();
            $code = $response->get_error_code();
            return new \WP_Error($code, $message);
        }

        $responseBody = json_decode(wp_remote_retrieve_body($response), true) ?? [];

        if (!$responseBody || !is_array($responseBody)) {
            return new \WP_Error('fluent_bot_error', __('Invalid or empty response from API', 'fluent-support'));
        }

        if (!empty($responseBody['error'])) {
            $message = $responseBody['error']['message'] ?? __('Unknown error occurred', 'fluent-support');
            return new \WP_Error('fluent_bot_error', $message);
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        if ($statusCode !== 200) {
            $error = $responseBody['message'] ?? __('Something went wrong.', 'fluent-support');
            return new \WP_Error($statusCode, __($error, 'fluent-support'));
        }

        $content = $responseBody['content'] ?? $responseBody['response'] ?? '';

        if (empty($content)) {
            return new \WP_Error('fluent_bot_error', __('No AI response found in the API response.', 'fluent-support'));
        }

        do_action('fluent_support/ai_response_success', $ticketId, $prompt, $responseBody['totalTokens'], "Fluent Bot");

        return $content;
    }

    protected function sendRequest(array $payload)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ];

        $timeout = apply_filters('fs_ai_request_timeout', 60);

        return wp_remote_post($this->apiUrl, [
            'headers' => $headers,
            'body'    => wp_json_encode($payload),
            'timeout' => $timeout,
        ]);
    }
}
