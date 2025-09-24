<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\App;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;

class IncomingWebhookController extends Controller
{
    public function index(Request $request)
    {
        $webhook = Meta::where('object_type', 'fs_incoming_webhook')->select(['value'])->get()->first();
        $defaultMailBox = Helper::getDefaultMailBox();
        if(!$webhook){
            //If webhook not found then create new webhook
            $webhook = $this->createWebhook();
        }
        $webhookData = maybe_unserialize($webhook->value);
        //If webhook data is array then get webhook uri from array else get webhook uri from webhook data
        $webhook_uri = (is_array($webhookData) && isset($webhookData['webhook'])) ? $webhookData['webhook'] : $webhookData;
        //If webhook data is array then get mailbox id from array else get default mailbox id
        $mailBox = (is_array($webhookData) && isset($webhookData['mailbox'])) ? $webhookData['mailbox'] : $defaultMailBox->id;

        return [
            'webhook' => $webhook_uri,
            'mailbox' => intval($mailBox),
            'mailboxes' => MailBox::get(),
        ];
    }

    private function createWebhook()
    {
        $token = wp_generate_uuid4();
        $app = App::getInstance();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');
        $webhook_uri = rest_url($ns . '/' . $v . '/public/incoming_webhook/' . $token);
        $data = [
            'webhook' => $webhook_uri,
            'mailbox' => Helper::getDefaultMailBox()->id,
        ];
        return Meta::create([
            'object_type' => 'fs_incoming_webhook',
            'key' => $token,
            'value' => maybe_serialize($data)
        ]);
    }

    public function updateWebhook()
    {
        $app = App::getInstance();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');
        $request = Helper::FluentSupport('request');
        //If request come to update webhook then update webhook
        if($mailboxId = $request->get('mailbox_id')){
            //Update mailbox id with the selected value
           return $this->updateMailBox($mailboxId);
        }else{
            $webhook = Meta::where('object_type', 'fs_incoming_webhook')->get()->first();
            $defaultMailBox = Helper::getDefaultMailBox();
            $existingData = maybe_unserialize($webhook->value);
            //If webhook data is array then get mailbox id from array else get default mailbox id
            $mailBox = (is_array($existingData) && isset($existingData['mailbox'])) ? $existingData['mailbox'] : $defaultMailBox->id;
            $token = wp_generate_uuid4();
            $data = [
                'webhook' => rest_url($ns . '/' . $v . '/public/incoming_webhook/' . $token),
                'mailbox' => $mailBox,
            ];

            $updateMeta = Meta::where('object_type', 'fs_incoming_webhook')->update([
                'key' => $token,
                'value' => maybe_serialize($data)
            ]);

            return [
                'updatedData' => $updateMeta,
                'message' => __('Webhook updated successfully', 'fluent-support-pro')
            ];
        }
    }

    private function updateMailBox($mailboxId){
        $webhook = Meta::where('object_type', 'fs_incoming_webhook')->select(['value'])->get()->first();
        if($webhook){
            $existingData = maybe_unserialize($webhook->value);
            $webhook_uri = (is_array($existingData) && isset($existingData['webhook'])) ? $existingData['webhook'] : $existingData;
            $data = [
                'webhook' => $webhook_uri,
                'mailbox' => $mailboxId,
            ];
            $updateMeta = Meta::where('object_type', 'fs_incoming_webhook')->update([
                'value' => maybe_serialize($data)
            ]);

            return [
                'updatedData' => $updateMeta,
                'message' => __('Mailbox updated successfully', 'fluent-support-pro')
            ];
        }

        return [
            'updatedData' => false,
            'message' => __('No incoming webhook found', 'fluent-support-pro')
        ];

    }

}
