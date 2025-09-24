<?php

namespace FluentSupportPro\App\Services\Integrations\Telegram;

use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Integrations\NotificationIntegrationBase;
use FluentSupport\Framework\Support\Arr;

class TelegramNotification extends NotificationIntegrationBase
{
    public $key = 'telegram_settings';

    public function __construct()
    {
        $this->registerActions();
    }

    public function registerActions()
    {
        add_filter('fluent_support/integration_drivers', function ($drivers) {
            $drivers[] = [
                'key'         => 'telegram_settings',
                'title'       => 'Telegram',
                'description' => __('Send Telegram notifications to Group, Channel or individual person inbox and reply from Telegram inbox', 'fluent-support-pro') . '<p><a href="https://fluentsupport.com/docs/managing-tickets-using-telegram/" rel="noopener" target="_blank">Please read the documentation before you get started</a></p>'
            ];
            return $drivers;
        }, 1, 1);

        add_action('fluent_support/ticket_created', function ($ticket, $customer) {
            if ($this->isNotificationActivated('ticket_created')) {
                $this->sendNotification([
                    'ticket' => $ticket,
                    'person' => $customer
                ], 'ticket_created');
            }
        }, 20, 2);

        add_action('fluent_support/ticket_closed', function ($ticket, $person) {
            if ($this->isNotificationActivated('ticket_closed')) {
                $this->sendNotification([
                    'ticket' => $ticket,
                    'person' => $person
                ], 'ticket_closed');
            }
        }, 20, 2);

        add_action('fluent_support/response_added_by_customer', function ($response, $ticket, $customer) {
            if ($this->isNotificationActivated('response_added_by_customer')) {
                $this->sendNotification([
                    'ticket'   => $ticket,
                    'response' => $response,
                    'person'   => $customer
                ], 'response_added_by_customer');
            }
        }, 20, 3);

        add_action('fluent_support/agent_assigned_to_ticket', function ($agent, $ticket, $assigner) {
            if ($this->isNotificationActivated('ticket_assigned')) {
                $this->sendNotification([
                    'person' => $agent,
                    'ticket' => $ticket,
                    'assigner' => $assigner
                ], 'ticket_assigned');
            }
        }, 20, 3);

    }

    public function getSettings($withFields = false)
    {
        $data = [
            'settings' => TelegramHelper::getSettings()
        ];

        if ($withFields) {
            $data['fields'] = $this->getFields();
        }

        return $data;
    }

    public function getFields()
    {
        return [
            'title'       => 'Telegram Notification Settings',
            'fields'      => [
                'bot_token'            => [
                    'type'        => 'input-text',
                    'data_type'   => 'password',
                    'label'       => __('Bot Token', 'fluent-support-pro'),
                    'placeholder' => __('Bot Token', 'fluent-support-pro'),
                    'help'        => __('Enter your Telegram Bot Token', 'fluent-support-pro')
                ],
                'chat_id'              => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'placeholder' => __('Chat ID', 'fluent-support-pro'),
                    'label'       => __('Default Group Chat ID', 'fluent-support-pro'),
                    'help'        => __('Enter your Telegram API channel user ID, You can also use message id. Please check documentation for more details.', 'fluent-support-pro')
                ],
                'notification_events'  => [
                    'type'    => 'checkbox-group',
                    'label'   => __('Notification Events', 'fluent-support-pro'),
                    'options' => [
                        'ticket_created'             => __('Ticket Created', 'fluent-support-pro'),
                        'ticket_closed'              => __('Ticket Closed', 'fluent-support-pro'),
                        'response_added_by_customer' => __('Replied By Customer', 'fluent-support-pro'),
                        'ticket_assigned'            => __('Agent Assigned To Ticket', 'fluent-support-pro')
                    ]
                ],
                'test_message'         => [
                    'placeholder' => __('Test Message to send right now', 'fluent-support-pro'),
                    'type'        => 'input-text',
                    'data_type'   => 'textarea',
                    'label'       => __('Test Message (Optional)', 'fluent-support-pro'),
                    'help'        => __('Enter message to send now as test', 'fluent-support-pro')
                ],
                'status'               => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Telegram Notifications', 'fluent-support-pro')
                ],
                'reply_from_telegram'  => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Reply From Telegram (Agent can directly reply from telegram)', 'fluent-support-pro'),
                    'dependency'     => [
                        'depends_on' => 'status',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ],
                'reply_from_html'      => [
                    'type'          => 'html-viewer',
                    'wrapper_class' => 'fs_highlight',
                    'html'          => __('Your support agents can easily reply from telegram by replying to telegram. </br>Please make sure support agent has telegram id set to the profile. <a href="https://fluentsupport.com/docs/managing-tickets-using-telegram" target="_blank">Learn More about this feature</a>', 'fluent-support-pro'),
                    'dependency'    => [
                        'depends_on' => 'reply_from_telegram',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ],
                'reply_webhook_status' => [
                    'type'       => 'html-viewer',
                    'html'       => __('Webhook is currently activated', 'fluent-support-pro'),
                    'dependency' => [
                        'depends_on' => 'webhook_activated',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ]
            ],
            'button_text' => __('Save Telegram Settings', 'fluent-support-pro')
        ];
    }

    public function saveSettings($settings)
    {
        $prevSettings = TelegramHelper::getSettings();

        if (empty($settings['chat_id']) || empty($settings['bot_token'])) {
            $settings['status'] = 'no';
            $this->maybeRemoveWebhook($prevSettings);
            $settings['webhook_activated'] = 'no';
            return $this->save($settings);
        }

        if ($prevSettings['bot_token'] != $settings['bot_token'] || $settings['status'] == 'no' || $settings['reply_from_telegram'] == 'no') { // bot token changed
            $response = $this->maybeRemoveWebhook($prevSettings);
            if (is_wp_error($response)) {
                $settings['webhook_activated'] = 'yes';
            } else {
                $isActive = is_array($response) ? $response['ok'] : $response->ok;
                $settings['webhook_activated'] = $isActive ? 'no' : 'yes';
            }
        }

        if ($settings['status'] === 'yes' && $settings['reply_from_telegram'] === 'yes' && $settings['webhook_activated'] === 'no') {
            $response = $this->maybeSetWebhook($settings);
            if (is_wp_error($response)) {
                $settings['webhook_activated'] = 'no';
            } else {
                $isActive = is_array($response) ? $response['ok'] : $response->ok;
                $settings['webhook_activated'] = $isActive ? 'yes' : 'no';
            }
        }

        $apiSettings = $settings;
        // Verify API key now
        try {

            $api = $this->getApiClient($settings);
            $apiStatus = $api->getMe();

            if (is_wp_error($apiStatus)) {
                throw new \Exception($apiStatus->get_error_message());
            }

            $message = Arr::get($settings, 'test_message', '');
            if ($message) {
                $api->setToken($apiSettings['bot_token']);
                $api->setChatId($apiSettings['chat_id']);
                $result = $api->sendMessage($message);
                if (is_wp_error($result)) {
                    $responseMessage = __('Your api key is right but, the message could not be sent to the provided chat id. Error: ' . $result->get_error_message(), 'fluent-support-pro');
                    throw new \Exception($responseMessage);
                }
            }

            if($settings['reply_from_telegram'] == 'yes') {
                $this->maybeSetWebhook($settings);
            }

            $apiSettings['test_message'] = '';

            return $this->save($apiSettings);

        } catch (\Exception $exception) {
            $apiSettings['status'] = 'no';
            $settings['webhook_activated'] = 'no';
            $this->save($apiSettings);
            return new \WP_Error($exception->getMessage());
        }
    }

    protected function getApiClient($settings = false, $ticket = false)
    {
        if (!$settings) {
            $settings = $this->get();
        }

        if ($ticket) {
            $agent = $ticket->agent;
            if ($agent && $chatId = $agent->getMeta('telegram_chat_id')) {
                $settings['chat_id'] = $chatId;
            }
        }

        return new TelegramApi(
            $settings['bot_token'],
            $settings['chat_id']
        );
    }

    public function sendNotification($data, $type = false)
    {
        $message = '';

        if ($type == 'ticket_created') {
            $message = $this->ticketCreatedMessage($data['ticket'], $data['person']);
        } else if ($type == 'response_added_by_customer') {
            $message = $this->replyMessage($data['ticket'], $data['response']);
        } else if ($type == 'ticket_closed') {
            $message = $this->ticketClosed($data['ticket'], $data['person']);
        } else if($type == 'ticket_assigned') {
            $message = $this->ticketAssigned($data['person'], $data['ticket'], $data['assigner']);
        }

        if ($message) {
            return $this->getApiClient(false, $data['ticket'])->sendMessage($message, 'html');
        }
    }

    public function isNotificationActivated($type)
    {
        $settings = $this->get();
        if (!$settings || $settings['status'] != 'yes') {
            return false;
        }
        $events = Arr::get($settings, 'notification_events', []);

        if(!$events || !is_array($events)) {
            return false;
        }

        return in_array($type, $events);
    }

    private function ticketCreatedMessage($ticket, $person)
    {
        $message = '<b>New Ticket Submitted by ' . $person->full_name . ' (#' . $ticket->id . ')</b>' . "\n";
        $message .= '<code>' . esc_html(\mb_substr($ticket->title, 0, 30)) . '</code>' . "\n";
        $message .= $this->clearText($ticket->content) . "\n";
        if ($ticket->product) {
            $message .= '<i>#' . preg_replace('~[^\pL\d]+~u', '_', $ticket->product->title) . '</i>' . "\n";
        }
        if ($ticket->custom_fields = $ticket->customData('public', true)){
            $customFields = apply_filters('fluent_support/ticket_custom_fields', []);
            foreach ($ticket->custom_fields as $customFieldKey=>$customFieldValue){
                if(is_array($customFieldValue)){
                    $customFieldValue = implode(',' , $customFieldValue);
                }
                $message .= '<i>' . str_replace('"',"",json_encode($customFields[$customFieldKey]['label'])) . ': ' . $customFieldValue . '</i>' . "\n";
            }
        }
        $message .= '<u><a href="' . $this->getTicketEditLink($ticket) . '">View Ticket</a></u>';
        return $message;
    }

    private function replyMessage($ticket, $response)
    {
        $message = '<b>Reply: ' . esc_html(\mb_substr($ticket->title, 0, 30)) . ' #' . $ticket->id . '</b>' . "\n";
        $message .= $this->clearText($response->content) . "\n";
        $message .= '<u><a href="' . $this->getTicketEditLink($ticket) . '">View Reply</a></u>';
        return $message;
    }

    private function ticketClosed($ticket, $person)
    {
        $message = '<b>Ticket  Closed by ' . $person->full_name . '</b>' . "\n";;
        $message .= '<pre>' . $ticket->title . '</pre> ';
        $message .= ' <a href="' . $this->getTicketEditLink($ticket) . '">(#' . $ticket->id . ')</a>';
        return $message;
    }

    private function ticketAssigned($person, $ticket, $assigner)
    {
        if ( !$assigner ) $assigner = $person;
        $text = '<b>Ticket Assigned to ' . $person->full_name . ' by '. $assigner->full_name.'</b>' . "\n";
        if($person->user_id == $assigner->user_id){
            $text = '<b>You assign a ticket to yourself</b>' . "\n";
        }

        $message = $text;
        $message .= '<pre>' . $ticket->title . '</pre> ';
        $message .= ' <a href="' . $this->getTicketEditLink($ticket) . '">(#' . $ticket->id . ')</a>';
        return $message;
    }

    private function clearText($html)
    {
        return preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))));
    }

    private function getTicketEditLink($ticket)
    {
        $adminUrl = Helper::getPortalAdminBaseUrl();
        return $adminUrl . 'tickets/' . $ticket->id . '/view';
    }

    protected function maybeRemoveWebhook($settings)
    {
        if (Arr::get($settings, 'webhook_activated') == 'yes' && Arr::get($settings, 'bot_token')) {
            return (new TelegramApi())->deleteBotWebhook(Arr::get($settings, 'bot_token'));
        }
    }

    protected function maybeSetWebhook($settings)
    {
        if (Arr::get($settings, 'webhook_activated') != 'yes' && Arr::get($settings, 'bot_token')) {
            return (new TelegramApi())->setBotWebhook(Arr::get($settings, 'bot_token'));
        }

        return false;
    }
}
