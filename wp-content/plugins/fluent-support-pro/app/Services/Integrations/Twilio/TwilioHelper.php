<?php

namespace FluentSupportPro\App\Services\Integrations\Twilio;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Services\Helper;

class TwilioHelper
{
    public static function getSettings()
    {
        $settings = Helper::getIntegrationOption('twilio_settings', []);

        $defaults = [
            'account_sid'         => '',
            'auth_token'          => '',
            'from_number'         => '',
            'notification_events' => [],
            'fallback_agent_id'   => '',
            'status'              => 'no',
            'reply_from_whatsapp' => 'no'
        ];


        if (!$settings) {
            return $defaults;
        }

        return wp_parse_args($settings, $defaults);
    }

    public static function getWebhookToken()
    {
        $token = Helper::getIntegrationOption('_twilio_webhook_token', false);

        if(!$token) {
            $token = substr(wp_generate_uuid4(), 0, 12);
            Helper::updateIntegrationOption('_twilio_webhook_token', $token);
        }

        return $token;
    }

    public static function getWebhookUrl()
    {
        $app = Helper::FluentSupport();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');
        return rest_url($ns . '/' . $v . '/public/twilio-response/'. static::getWebhookToken());
    }

    public static function resolveAgent($agentWhatsappNumber)
    {
        $personMeta = Meta::where('key','whatsapp_number')->where('value', $agentWhatsappNumber)->first();
        $agentId = false;

        if($personMeta) {
            $agentId = $personMeta->object_id;
        } else {
            $settings = self::getSettings();
            if(!empty($settings['fallback_agent_id'])) {
                $agentId = absint($settings['fallback_agent_id']);
            }
        }

        if(!$agentId) {
            return false;
        }

        return Agent::find($agentId);
    }
}