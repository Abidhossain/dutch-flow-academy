<?php

namespace FluentSupportPro\App\Services\Integrations\Slack;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Integrations\NotificationIntegrationBase;
use FluentSupport\App\Services\Parser\Parsedown;
use FluentSupport\App\Services\Tickets\ResponseService;
use FluentSupport\App\Services\Tickets\TicketService;
use FluentSupport\Framework\Support\Arr;

class SlackNotification extends NotificationIntegrationBase
{
    public $key = 'slack_settings';

    public function __construct()
    {
        $this->registerActions();
    }

    /**
     * registerActions method will register all action hooks related to slack integration
     */
    public function registerActions()
    {
        //Create ticket
        add_action('fluent_support/ticket_created', function ($ticket, $customer) {
            if ($this->isNotificationActivated('ticket_created')) {
                $result = $this->sendNotification([
                    'ticket' => $ticket,
                    'person' => $customer
                ], 'ticket_created');

                if (!$result || is_wp_error($result)) {
                    return false;
                } else {
                    $threadId = Arr::get($result, 'ts');
                    if (!$threadId) {
                        return false;
                    }

                    Helper::updateTicketMeta($ticket->id, '_slack_thread_id', $threadId);
                    return true;
                }
            }
        }, 20, 2);

        //Close ticket
        add_action('fluent_support/ticket_closed', function ($ticket, $person) {
            if ($this->isNotificationActivated('ticket_closed')) {

                $threadId = Helper::getTicketMeta($ticket->id, '_slack_thread_id');
                if (!$threadId) {
                    return false;
                }

                $message = $this->ticketClosed($ticket, $person);;
                if (!$message) {
                    return false;
                }

                $response = SlackApi::send($message, $threadId);

            }
        }, 20, 2);

        //response by customer
        add_action('fluent_support/response_added_by_customer', function ($response, $ticket, $customer) {
            if ($this->isNotificationActivated('response_added_by_customer')) {
                $threadId = Helper::getTicketMeta($ticket->id, '_slack_thread_id');
                if (!$threadId) {
                    return false;
                }

                $message = $this->replyMessage($ticket, $response);;
                if (!$message) {
                    return false;
                }

                $response = SlackApi::send($message, $threadId);
            }
        }, 20, 3);

        //Agent assigned to ticket
        add_action('fluent_support/agent_assigned_to_ticket', function ($agent, $ticket, $assigner) {
            if ($this->isNotificationActivated('ticket_assigned')) {
                $threadId = Helper::getTicketMeta($ticket->id, '_slack_thread_id');
                if (!$threadId) {
                    return false;
                }

                $message = $this->agentAssigned($agent, $ticket, $assigner);
                if (!$message) {
                    return false;
                }

                $response = SlackApi::send($message, $threadId);
            }
        }, 20, 3);

        add_filter('fluent_support/integration_drivers', function ($drivers) {
            $drivers[] = [
                'key'         => 'slack_settings',
                'title'       => 'Slack',
                'description' => __('Send notifications to Slack Channel and keep your support agents more active', 'fluent-support-pro') . '<p><a href="https://fluentsupport.com/docs/managing-tickets-using-slack/" rel="noopener" target="_blank">Please read the documentation before you get started</a></p>'
            ];
            return $drivers;
        }, 2, 1);

    }

    /**
     * getSettings will fetch all settings related to slack and return
     * @param false $withFields
     * @return array
     */
    public function getSettings($withFields = false)
    {
        $data = [
            'settings' => SlackHelper::getSettings()
        ];

        if ($withFields) {
            $data['fields'] = $this->getFields();
        }

        return $data;
    }

    /**
     * getFields method will return the field configuration for slack integration
     * @return array
     */
    public function getFields()
    {

        $slackEventUrl = $this->getWebhookUrl();

        return [
            'title'       => 'Slack Notification Settings',
            'fields'      => [
                'bot_token'         => [
                    'type'        => 'input-text',
                    'data_type'   => 'password',
                    'label'       => __('Slack Bot User OAuth Token', 'fluent-support-pro'),
                    'placeholder' => __('Slack Bot User OAuth Token', 'fluent-support-pro'),
                    'help'        => __('Enter your Slack Bot User OAuth Token Here', 'fluent-support-pro')
                ],
                'channel'           => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Slack Channel Name', 'fluent-support-pro'),
                    'placeholder' => __('Slack Channel Name', 'fluent-support-pro'),
                    'help'        => __('Input Channel Name', 'fluent-support-pro')
                ],
                'channel_id'        => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Slack Channel ID', 'fluent-support-pro'),
                    'placeholder' => __('Slack Channel ID', 'fluent-support-pro'),
                    'help'        => __('Input Channel ID', 'fluent-support-pro')
                ],
                'status'            => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Slack Notifications', 'fluent-support-pro')
                ],
                'ticket_assigned'   => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Ticket Assigned Notification', 'fluent-support-pro')
                ],
                'reply_from_slack'  => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Reply Ticket From Slack', 'fluent-support-pro')
                ],
                'reply_from_html'   => [
                    'type'          => 'html-viewer',
                    'wrapper_class' => 'fs_highlight',
                    'html'          => '<p>Your support agents can easily reply from slack by replying to slack. </br>Please make sure support agent has slack id set to the profile. <a href="https://fluentsupport.com/docs/managing-tickets-using-slack" target="_blank">Learn More about this feature</a></p>' . sprintf(__('Please copy this URL %1s%2s%3s to verify your Event Subscriptions in Slack', 'fluent-support-pro'), '<code>', $slackEventUrl, '</code>'),
                    'dependency'    => [
                        'depends_on' => 'reply_from_slack',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ],
                'fallback_agent_id' => [
                    'type'        => 'agent-selectors',
                    'label'       => 'Fallback Agent for reply from slack thread',
                    'placeholder' => 'Select Fallback Agent',
                    'dependency'  => [
                        'depends_on' => 'reply_from_slack',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ]
            ],
            'button_text' => __('Save Slack Settings', 'fluent-support-pro')
        ];
    }

    /**
     * saveSettings settings will save settings for slack integration
     * @param $settings
     * @return \FluentSupport\App\Models\Meta
     */
    public function saveSettings($settings)
    {
        $prevSettings = SlackHelper::getSettings();

        if ($prevSettings) {
            return $this->save($settings);
        }

        return $this->save($settings);
    }

    /**
     * isNotificationActivated method will return whether notification for Slack is enabled or not
     * @param false $type - type of notification
     * @return bool
     */
    public function isNotificationActivated($type = false)
    {
        $settings = $this->get();
        if (!$settings || Arr::get($settings, 'status') != 'yes') {
            return false;
        }
        if ($type == 'ticket_assigned' && !$settings && Arr::get($settings, $type) != 'yes') {
            return false;
        }

        return true;
    }

    /**
     * sendNotification will send notification
     * @param $data
     * @param false $type
     * @return mixed
     * @throws \WP_Error
     */
    public function sendNotification($data, $type = false)
    {
        $message = false;

        if ($type == 'ticket_created') {
            $message = $this->ticketCreatedMessage($data['ticket'], $data['person']);
        } else if ($type == 'response_added_by_customer') {
            $message = $this->replyMessage($data['ticket'], $data['response']);
        } else if ($type == 'ticket_closed') {
            $message = $this->ticketClosed($data['ticket'], $data['person']);
        } else if ($type == 'ticket_assigned') {
            $message = $this->ticketAssigned($data['person'], $data['ticket'], $data['assigned_agent']);
        }

        if ($message) {
            return SlackApi::send($message);
        }

        return false;
    }

    /**
     * ticketCreatedMessage method will generate Slack message for create ticket
     * @param $ticket
     * @param $person
     * @return array
     */
    private function ticketCreatedMessage($ticket, $person)
    {

        $fields = [];

        if ($ticket->product) {
            $fields[] = [
                'title' => 'Product',
                'value' => $ticket->product->title,
                'short' => true,
            ];
        }

        $fields[] = [
            'title' => 'Priority',
            'value' => ucfirst($ticket->client_priority),
            'short' => true,
        ];

        if ($ticket->custom_fields = $ticket->customData('public', true)) {
            $customFields = apply_filters('fluent_support/ticket_custom_fields', []);
            foreach ($ticket->custom_fields as $customFieldKey => $customFieldValue) {
                if (is_array($customFieldValue)) {
                    $customFieldValue = implode(',', $customFieldValue);
                }
                $fields[] = [
                    'title' => str_replace('"', "", json_encode($customFields[$customFieldKey]['label'])),
                    'value' => strip_tags($customFieldValue),
                    'short' => true
                ];
            }
        }

        $message = [
            0 => [
                'mrkdwn_in' => ['text', 'pretext'],
                'fallback'  => '*New Ticket Submitted by ' . $person->full_name . ' (#' . $ticket->id . ')*',
                'color'     => '#36a64f',
                'pretext'   => '*New Ticket Submitted by ' . $person->full_name . '  <' . esc_url_raw($this->getTicketEditLink($ticket)) . '|#' . $ticket->id . '>*',
                'title'     => esc_html($ticket->title),
                'text'      => $this->clearText($ticket->content),
                'fields'    => $fields,
                'ts'        => time(),
            ],
        ];
        return $message;

    }

    /**
     * replyMessage method will generate Slack message for reply regarding reply in ticket
     * @param $ticket
     * @param $response
     * @return array
     */
    private function replyMessage($ticket, $response)
    {
        $content = $this->clearText($response->content);

        if ($ticket->agent) {
            if ($slackUserId = $ticket->agent->getMeta('slack_user_id')) {
                $content = trim($content);
                $content .= PHP_EOL . '<@' . $slackUserId . '>';
            }
        }

        $message = [
            0 => [
                'mrkdwn_in'  => ['text'],
                'fallback'   => 'Replied by Customer: ' . $ticket->customer->full_name,
                'color'      => '#36a64f',
                'title'      => 'Replied by Customer: ' . $ticket->customer->full_name,
                'text'       => $content,
                'title_link' => esc_url_raw($this->getTicketEditLink($ticket)),
                'ts'         => time(),
            ],
        ];

        return $message;

    }

    /**
     * ticketClosed method will generate Slack message for reply regarding ticket is closed
     * @param $ticket
     * @param $person
     * @return array
     */
    private function ticketClosed($ticket, $person)
    {
        $message = [
            0 => [
                'fallback'   => 'Ticket  Closed by Customer: ' . $person->full_name,
                'color'      => '#36a64f',
                'title'      => 'Ticket  Closed by Customer: ' . $person->full_name,
                'title_link' => esc_url_raw($this->getTicketEditLink($ticket)),
                'ts'         => time(),
            ],
        ];
        return $message;

    }

    /**
     * agentAssigned method will generate Slack message for reply regarding ticket assigned to agent
     * @param $agent
     * @param $ticket
     * @param $assigner
     * @return array
     */
    private function agentAssigned($agent, $ticket, $assigner)
    {
        if(!$agent || !$ticket || !$assigner){
            return null;
        }
        $agentFullName = $this->getFullName($agent);
        $assignerFullName = $this->getFullName($assigner);

        if(!$agentFullName || !$assignerFullName){
            return null;
        }

        $text = '(' . $ticket->title . ')' . ' has been assigned to ' . $agentFullName . ' by ' . $assignerFullName;
        if (isset($agent->user_id) && isset($assigner->user_id) && $agent->user_id == $assigner->user_id) {
            $text = $assigner->full_name . ' assigned ' . $ticket->title . ' to self';
        }

        $message = [
            0 => [
                'fallback'   => 'Ticket  Assigned to Agent: ' . $agentFullName,
                'color'      => '#36a64f',
                'title'      => $text,
                'title_link' => esc_url_raw($this->getTicketEditLink($ticket)),
                'ts'         => time(),
            ],
        ];
        return $message;

    }

    private function getFullName($person){
        if($person->full_name) {
            $full_name = $person->full_name;
        } else if($person->first_name && $person->last_name) {
            $full_name = $person->first_name . ' ' . $person->last_name;
        } else if($person->first_name) {
            $full_name = $person->first_name;
        } else if($person->last_name) {
            $full_name = $person->last_name;
        }else {
            return null;
        }

        return $full_name;
    }

    private function clearText($html)
    {
        return preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))));
    }

    /**
     * getTicketEditLink method will generate hyper link for edit ticket
     * @param $ticket
     * @return string ticket link
     */
    private function getTicketEditLink($ticket)
    {
        $adminUrl = Helper::getPortalAdminBaseUrl();
        return $adminUrl . 'tickets/' . $ticket->id . '/view';
    }

    /**
     * getWebhookUrl method will generate web hook link for slack
     * @return string generated webhook link
     */
    private function getWebhookUrl()
    {
        $app = Helper::FluentSupport();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');
        return rest_url($ns . '/' . $v . '/public/slack_response/' . SlackHelper::getWebhookToken());
    }

    /**
     * processSlackEvent method will create response in a ticket from Slack
     * @param $event
     * @return bool
     */
    public function processSlackEvent($event)
    {

        if (!$event || $event['type'] != 'message') {
            return false;
        }


        $ticketThreadId = (string)Arr::get($event, 'thread_ts');

        if (!$ticketThreadId) {
            return false;
        }

        $thread = Meta::where('object_type', 'ticket_meta')
            ->where('key', '_slack_thread_id')
            ->where('value', $ticketThreadId)
            ->first();

        if (!$thread || !$thread->object_id) {
            return false;
        }

        $ticket = Ticket::find($thread->object_id);

        if (!$ticket) {
            return false;
        }

        $messageId = Arr::get($event, 'client_msg_id');

        if ($messageId && Conversation::where('ticket_id', $ticket->id)->where('message_id', $messageId)->first()) {
            return false;
        }

        $slackUserId = Arr::get($event, 'user');
        $channelId = Arr::get($event, 'channel');

        if (!$slackUserId || !$channelId) {
            return false;
        }

        $slackSettings = SlackHelper::getSettings();

        if ($slackSettings['channel_id'] != $channelId) {
            return false;
        }

        $agent = SlackHelper::resolveAgent($slackUserId);

        if (!$agent) {
            return false;
        }

        $conversionType = 'response';

        $text = Arr::get($event, 'text');
        if (strpos($text, '#note') !== false || strpos($text, '#internal') !== false) {
            $conversionType = 'note';
            $text = str_replace(['#note', '#internal'], '', $text);
        }

        $command = false;
        if (strpos($text, '[close]') !== false) {
            $command = 'close_ticket';
            $text = str_replace('[close]', '', $text);
        }

        if ($text) {
            if ($html = (new Parsedown)->text($text)) {
                $text = $html;
            }
        }

        if ($text) {
            $data = [
                'person_id'         => $agent->user_id,
                'ticket_id'         => $ticket->id,
                'conversation_type' => $conversionType,
                'content'           => $text,
                'source'            => 'slack',
                'message_id'        => $messageId
            ];

            (new ResponseService)->createResponse($data, $agent, $ticket);
        }

        if ($command == 'close_ticket') {
            (new TicketService())->close( $ticket, $agent );
        }


        return true;
    }

}
