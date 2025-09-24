<?php

namespace FluentSupportPro\App\Services\Integrations\Telegram;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Parser\Parsedown;
use FluentSupport\Framework\Support\Arr;

class TelegramHelper
{
    public static function getBotToken()
    {
        $settings = self::getSettings();

        return $settings['bot_token'];
    }

    /**
     * getWebhookToken method will return the telegram web hook token
     * @return false|string
     */
    public static function getWebhookToken()
    {
        //Get existing token
        $token = Helper::getIntegrationOption('_telegram_webhook_token', false);

        if(!$token) {
            //If token is not available, generate new and update record
            $token = substr(wp_generate_uuid4(), 0, 12);
            Helper::updateIntegrationOption('_telegram_webhook_token', $token);
        }

        return $token;
    }

    public static function getSettings()
    {
        $settings = Helper::getIntegrationOption('telegram_settings', []);

        $defaults = [
            'bot_token'           => '',
            'chat_id'             => '',
            'notification_events' => [],
            'test_message'        => '',
            'reply_from_telegram' => 'no',
            'status'              => 'no',
            'webhook_activated' => 'no'
        ];


        if(!$settings) {
            return $defaults;
        }

        return wp_parse_args($settings, $defaults);
    }

    /**
     * parseTelegramBotPayload method will read the request, generate ticket id, response text and agent id
     * @param $payload
     * @return array|\WP_Error
     */
    public static function parseTelegramBotPayload($payload)
    {

        if(!is_array($payload) || !Arr::get($payload, 'message.text')) {
            return new \WP_Error('invalid', 'Invalid Payload');
        }

        $replyTo = Arr::get($payload, 'message.reply_to_message');
        if(!$replyTo) {
            return new \WP_Error('no_reply_found', 'Not a reply to message');
        }

        $replyToText = Arr::get($replyTo, 'text');

        if(!$replyToText) {
            return new \WP_Error('no_reply_text', 'No reply text found');
        }

        $senderId = Arr::get($payload, 'message.from.id');

        if(!$senderId) {
            return new \WP_Error('no_agent', 'No Matched Agent found');
        }

        $personMeta = Meta::where('object_type', 'person_meta')
            ->where('key', 'telegram_chat_id')
            ->where('value', $senderId)
            ->first();

        if(!$personMeta) {

            // let's try with first name and last name
            $firstName = sanitize_text_field(Arr::get($payload, 'message.from.first_name'));
            $lastName = sanitize_text_field(Arr::get($payload, 'message.from.last_name'));

            $agent = Agent::where('first_name', $firstName)->where('last_name', $lastName)->orderBy('id', 'ASC')->first();

            if(!$agent) {
                $agent = Agent::where('first_name', $firstName)->orWhere('last_name', $lastName)->orderBy('id', 'ASC')->first();
            }

            if($agent) {
                $agent_id = $agent->id;
            } else {
                return new \WP_Error('no_agent', 'No Matched Agent found on database telegram settings');
            }

        } else {
            $agent_id = $personMeta->object_id;
        }

        preg_match('/#(.?[0-9)]*)\\n/', $replyToText, $matches);

        $ticketId = false;
        if(count($matches) >=2) {
            $ticketId = $matches[1];
            $ticketId = str_replace(')', '', $ticketId);
            $ticketId = absint($ticketId);
        }

        if(!$ticketId) {
            return new \WP_Error('no_ticket_id', 'No Ticket ID found from Payload. '.$ticketId);
        }

        $responseText = Arr::get($payload, 'message.text');
        $responseText = str_replace('\n', PHP_EOL, $responseText);

        $command = '';
        if(strpos($responseText, '[close]') !== false){
            $responseText = str_replace('[close]', '', $responseText);
            $command = 'close_ticket';
        }

        if($responseText) {
            if($parseText = (new Parsedown)->text($responseText)) {
                $responseText = $parseText;
            }
            $responseText = wpautop($responseText);
        }

        return [
            'ticket_id' => $ticketId,
            'response_text' => $responseText,
            'agent_id' => $agent_id,
            'command' => $command
        ];
    }
}
