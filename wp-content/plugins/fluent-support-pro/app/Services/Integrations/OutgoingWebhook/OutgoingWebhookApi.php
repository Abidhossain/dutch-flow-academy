<?php

namespace FluentSupportPro\App\Services\Integrations\OutgoingWebhook;

class OutgoingWebhookApi
{
    public static function sendData($url, $data, $type = 'application/json', $method = 'POST')
    {
        if ($type == 'application/json') {
            $data = json_encode($data);
        }

        $payload = [
            'body'    => $data,
            'headers' => [
                'Content-Type' => $type
            ]
        ];
        $response = wp_remote_post($url, $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        return [
            'status'  => 'success',
            'message' => 'Webhook triggered successfully'
        ];
    }
}
