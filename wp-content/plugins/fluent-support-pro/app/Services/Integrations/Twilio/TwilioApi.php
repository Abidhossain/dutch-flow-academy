<?php

namespace FluentSupportPro\App\Services\Integrations\Twilio;

use FluentSupport\App\Models\Meta;

class TwilioApi
{
    public static function sendNotification($data)
    {
        $settings = TwilioHelper::getSettings();
        $url = 'https://api.twilio.com/2010-04-01/Accounts/'.$settings['account_sid'].'/Messages.json';
        $agentsNumbers = Meta::where('object_type', 'person_meta')->where('key','whatsapp_number')->select(['value'])->get();
        $response = '';

        $payload = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( $settings['account_sid'] . ':' . $settings['auth_token'] ),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'From' => 'whatsapp:'.$settings['from_number'],
                'Body' => $data,
            ],
        ];

        if(is_array($data)) {
            $payload['body']['To'] = 'whatsapp:'.$data['to'];
            $payload['body']['Body'] = $data['message'];
            $response = wp_remote_post( $url, $payload );

            if (is_wp_error($response)) {
                return new \WP_Error($response->get_error_code(), $response->get_error_message());
            }

            if (!$response) {
                return new \WP_Error('twilio_error', 'Twilio API Request Failed');
            }
        }

        if (!is_array($data)) {
            foreach ($agentsNumbers as $to) {
                $payload['body']['To'] = 'whatsapp:'.$to->value;

                $response = wp_remote_post( $url, $payload );

                if (is_wp_error($response)) {
                    return new \WP_Error($response->get_error_code(), $response->get_error_message());
                }

                if (!$response) {
                    return new \WP_Error('twilio_error', 'Twilio API Request Failed');
                }
            }
        }
        return $response;

    }
}