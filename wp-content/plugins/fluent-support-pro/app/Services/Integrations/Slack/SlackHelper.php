<?php

namespace FluentSupportPro\App\Services\Integrations\Slack;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Services\Helper;

class SlackHelper
{

    public static function getSettings()
    {
        $settings = Helper::getIntegrationOption('slack_settings', []);

        $defaults = [
            'bot_token'           => '',
	        'channel'             => '',
	        'channel_id'          => '',
            'status'              => 'no',
            'ticket_assigned'     => 'no',
	        'reply_from_slack'    => 'no',
            'fallback_agent_id'   => ''
        ];


        if(!$settings) {
            return $defaults;
        }

        return wp_parse_args($settings, $defaults);
    }

    public static function getWebhookToken()
    {
        $token = Helper::getIntegrationOption('_slack_webhook_token', false);

        if(!$token) {
            $token = substr(wp_generate_uuid4(), 0, 12);
            Helper::updateIntegrationOption('_slack_webhook_token', $token);
        }

        return $token;
    }

    public static function resolveAgent($slackUserId)
    {
        $personMeta = Meta::where('object_type', 'person_meta')
            ->where('key', 'slack_user_id')
            ->where('value', $slackUserId)
            ->first();
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
