<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\App;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Customer;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\MailBox;
use FluentSupport\Framework\Request\Request;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\Integrations\FluentEmailPiping\Api;
use FluentSupportPro\App\Services\Integrations\FluentEmailPiping\ByMailHandler;
use FluentSupportPro\App\Services\ProHelper;

class EmailBoxController extends Controller
{
    public function getPipeStatus(Request $request, $box_id)
    {
        $box = MailBox::findOrFail($box_id);

        if ($box->box_type != 'email') {
            return $this->sendError([
                'message' => 'This is a web type business inbox. No email piping is available'
            ]);
        }

        if (!$box->mapped_email) {
            return [
                'email_pipe'          => [
                    'status' => 'not_issued',
                ],
                'is_custom_supported' => ByMailHandler::isCustomPipeSupported(),
                'webhook_url'         => $this->getWebhookUrl($box)
            ];
        }

        $status = 'unknown';
        $licenseKey = $this->getLicenseKey();

        $errorMessage = '';

        if ($licenseKey) {
            $remoteStatus = (new Api)->getPipeEmailStatus([
                'box_token'    => $this->getBoxSecret($box),
                'license'      => $licenseKey,
                'mapped_email' => $box->mapped_email
            ]);

            if (is_wp_error($remoteStatus)) {
                // handle error here
                $errorMessage = $remoteStatus->get_error_message();
            } else {
                $status = $remoteStatus['status'];
            }
        } else {
            $errorMessage = 'License Key could not be found. Please Activate Fluent Support License First';
        }

        return [
            'email_pipe'          => [
                'status'       => $status,
                'mapped_email' => $box->mapped_email,
            ],
            'is_custom_supported' => ByMailHandler::isCustomPipeSupported(),
            'webhook_url'         => $this->getWebhookUrl($box),
            'error_message'       => $errorMessage
        ];

    }

    public function issueMappedEmail(Request $request, $box_id)
    {
        $box = MailBox::findOrFail($box_id);

        if ($box->box_type != 'email') {
            return $this->sendError([
                'message' => 'This is a web type business inbox. No email piping is available'
            ]);
        }

        if ($box->mapped_email) {
            return $this->sendError([
                'message' => 'Mapped email has been already issued'
            ]);
        }

        $licenseKey = $this->getLicenseKey();


        if ($licenseKey) {
            // we don't have any mapped email yet. So let's get a new mapped email
            $data = [
                'license'     => $licenseKey,
                'email'       => $box->email,
                'site_url'    => site_url(),
                'webhook_url' => $this->getWebhookUrl($box),
                'box_token'   => $this->getBoxSecret($box)
            ];

            $response = (new Api())->issuePipeEmail($data);
            if (is_wp_error($response)) {
                return $this->sendError([
                    'message' => $response->get_error_message()
                ]);
            }
        } else {
            return $this->sendError([
                'message' => __('Please activate Fluent Support license', 'fluent-support-pro')
            ]);
        }

        if (isset($response['masked_email_id']) && is_email($response['masked_email_id'])) {
            $box->mapped_email = $response['masked_email_id'];
            $box->save();
            $response['mapped_email'] = $response['masked_email_id'];
        }

        return [
            'message'    => 'Mailbox mapped email has been generated',
            'email_pipe' => $response
        ];
    }

    public function pipePayload(Request $request, $box_id, $token)
    {
        $box = MailBox::find($box_id);

        if (!$box) {
            $box = MailBox::where('box_type', 'email')->first();
        }

        if ($this->getBoxSecret($box) != $token) {
            return $this->sendError([
                'message' => 'Token Mismatch'
            ]);
        }

        $data = $request->get('payload');
        $data = json_decode($data, true);
        $mimeId = Arr::get($data, 'mime_id');

        $data = apply_filters('fluent_support_pro/email_piping_raw_data', $data, $box);

        $response = ByMailHandler::processPayload($data, $box, $mimeId);

        if (is_wp_error($response)) {

            $code = $response->get_error_code();

            $internalErrorCodes = [
                'ticket_creation_error',
                'customer_inactive',
                'response_creation_error',
            ];

            if (in_array($code, $internalErrorCodes)) {
                return [
                    'type'    => 'confirmed',
                    'message' => $code
                ];
            }

            return $this->sendError([
                'message' => $response->get_error_message(),
                'type'    => 'error',
                'reason'  => $response->get_error_code()
            ]);
        }

        return $response;
    }


    protected function getWebhookUrl($mailBox)
    {
        $token = $this->getBoxSecret($mailBox);

        $app = App::getInstance();

        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');

        return rest_url($ns . '/' . $v . '/mail-piping/' . $mailBox->id . '/push/' . $token);
    }

    protected function getBoxSecret($mailBox)
    {
        if (!$token = $mailBox->getMeta('_webhook_token')) {
            $token = substr(md5(wp_generate_uuid4()) . '_' . $mailBox->id . '_' . mt_rand(100, 10000), 0, 16);
            $mailBox->saveMeta('_webhook_token', $token);
        }
        return $token;
    }

    protected function getLicenseKey()
    {
        $licenseData = get_option('__fluentsupport_pro_license');

        if ($licenseData && !empty($licenseData['license_key']) && $licenseData['status'] == 'valid') {
            return $licenseData['license_key'];
        }

        return false;
    }
}
