<?php

namespace FluentSupportPro\App\Services\Integrations\FluentEmailPiping;

class Api
{
    protected $apiUrl = 'https://apiv2.wpmanageninja.com/email-piping/';

    public function issuePipeEmail($data)
    {
        return $this->request('issue', $data, 'POST');
    }

    public function getPipeEmailStatus($data)
    {
        return $this->request('status', $data, 'POST');
    }

    public function removeMailBox($data)
    {
        return $this->request('remove', $data, 'POST');
    }

    public function request($route, $data, $method = 'POST')
    {

        $response = wp_remote_request($this->apiUrl . $route, [
            'method'    => $method,
            'body'      => $data,
            'sslverify' => false,
            'timeout'     => 45,
            'redirection' => 5,
            'blocking'    => true,
            'headers'     => array(),
            'cookies'     => array()
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $responseCode = wp_remote_retrieve_response_code($response);

        $responseArray = [];

        if ($responseCode >= 300) {
            $responseBody = wp_remote_retrieve_body($response);
            $message = 'Failed to communicate with API server';

            if ($responseBody) {
                $responseArray = json_decode($responseBody, true);
                if (isset($responseArray['message'])) {
                    $message = $responseArray['message'];
                }
            }

            return new \WP_Error($responseCode, $message, $responseArray);
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

}
