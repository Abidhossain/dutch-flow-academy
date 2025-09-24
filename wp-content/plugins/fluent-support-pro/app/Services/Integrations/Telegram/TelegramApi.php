<?php

namespace FluentSupportPro\App\Services\Integrations\Telegram;

use FluentSupport\App\Services\Helper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class TelegramApi
{
    private $token = '';
    private $chatId = '';
    private $parseMode = 'none';

    private $apiBase = 'https://api.telegram.org/bot';

    public function __construct($token = '', $chatId = '')
    {
        if(!$token) {
            $token = TelegramHelper::getBotToken();
        }

        $this->token = $token;
        $this->chatId = $chatId;
    }

    public function setChatId($chatId)
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function setParseMode($mode)
    {
        $this->parseMode = $mode;
        return $this;
    }

    public function sendMessage($message, $parseMode = '')
    {
        if (!$message) {
            return new \WP_Error(300, 'Message is required', []);
        }

        if(!$this->token) {
            return new \WP_Error(300, 'Token is required', []);
        }

        if (!$parseMode) {
            $parseMode = $this->parseMode;
        }

        if($parseMode == 'none') {
            $message = $this->clearText($message);
        }

        return $this->sendRequest('sendMessage', [
            'chat_id'    => $this->chatId,
            'parse_mode' => $parseMode,
            'text'       => urlencode($message)
        ]);
    }

    public function getMe()
    {
        return $this->sendRequest('getMe');
    }

    private function getBaseUrl()
    {
        return $this->apiBase . $this->token . '/';
    }

    private function clearText($html)
    {
        return preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))) );
    }

    public function sendRequest($endPont, $args = [])
    {
        if(!$this->token) {
            return new \WP_Error(300, 'Token is required', []);
        }

        $url = add_query_arg($args, $this->getBaseUrl() . $endPont);

        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = \json_decode($result, true);
        if (isset($result['ok'])) {
            if(!empty($result['ok'])) {
                return $result;
            }
            return new \WP_Error($result['error_code'], $result['description'], $result);
        }

        return new \WP_Error(300, 'Unknown API error from Telegram', $result);
    }

    public function setBotWebhook($botToken = '')
    {
        if(!$botToken) {
            $botToken = TelegramHelper::getBotToken();
        }

        if(!$botToken) {
            return new \WP_Error('not_found', 'Bot Token could not be found');
        }

        $app = Helper::FluentSupport();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');
        $restUrl = rest_url($ns . '/' . $v.'/public/telegram_bot_response/'.TelegramHelper::getWebhookToken());
        $fullUrl = "https://api.telegram.org/bot{$botToken}/setWebhook?url=".rawurlencode($restUrl);


        $response = wp_remote_get($fullUrl);

        if(is_wp_error($response)) {
            return new \WP_Error($response->get_error_code(), $response->get_error_message());
        }

        $returnData = json_decode(wp_remote_retrieve_body($response), true);

        if(empty($returnData['ok'])) {
            return new \WP_Error($returnData['error_code'], $returnData['description']);
        }

        return $returnData;

    }

    public function deleteBotWebhook($botToken = '')
    {
        if(!$botToken) {
            $botToken = TelegramHelper::getBotToken();
        }

        if(!$botToken) {
            return new \WP_Error('not_found', 'Bot Token could not be found');
        }

        $fullUrl = "https://api.telegram.org/bot{$botToken}/deleteWebhook?drop_pending_updates=true";

        $response = wp_remote_get($fullUrl);

        if(is_wp_error($response)) {
            return new \WP_Error($response->get_error_code(), $response->get_error_message());
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    public function getBotWebhookInfo()
    {
        $botToken = TelegramHelper::getBotToken();

        if(!$botToken) {
            return new \WP_Error('not_found', 'Bot Token could not be found');
        }

        $fullUrl = "https://api.telegram.org/bot{$botToken}/getwebhookinfo";

        $response = wp_remote_get($fullUrl);

        if(is_wp_error($response)) {
            return new \WP_Error($response->get_error_code(), $response->get_error_message());
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

}
