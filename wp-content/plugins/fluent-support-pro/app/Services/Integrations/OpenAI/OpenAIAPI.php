<?php

namespace FluentSupportPro\App\Services\Integrations\OpenAI;

class OpenAIAPI
{
    protected $apiKey;

    protected $model = 'gpt-3.5-turbo';

    protected $modelUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct($apiKey, $model = '')
    {
        $this->apiKey = $apiKey;

        if ($model) {
            $this->model = $model;
        }
    }

    public function makeRequest($prompt, $ticketId, $args = [])
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ];

        $bodyArgs = [
            "model"    => $this->model,
            "messages" => [
                $args ?: [
                    "role"    => "system",
                    "content" => "You are a helpful assistant."
                ]
            ]
        ];

        $timeout = apply_filters('fs_ai_request_timeout', 60);

        $request = wp_remote_post($this->modelUrl, [
            'headers' => $headers,
            'body'    => json_encode($bodyArgs),
            'timeout' => $timeout,
        ]);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            return new \WP_Error('chatGPT_error', $message);
        }

        $body = json_decode(wp_remote_retrieve_body($request), true);


        $code = wp_remote_retrieve_response_code($request);

        if (isset($body['error']) || !$body) {
            $message = $body['error']['message'] ?? 'Unknown error occurred';
            return new \WP_Error('chatGPT_error', $message);
        }

        if ($code !== 200) {
            $error = __('Something went wrong.', 'fluent-support-pro');
            if (isset($body['error']['message'])) {
                $error = __($body['error']['message'], 'fluent-support-pro');
            }
            return new \WP_Error(423, $error);
        }

        $usedTokens = $body['usage']['total_tokens'];

        $responseBody = $body['choices'][0]['message']['content'] ?? '';

        do_action('fluent_support/ai_response_success', $ticketId, $prompt, $usedTokens, $this->model);

        return $responseBody;
    }
}
