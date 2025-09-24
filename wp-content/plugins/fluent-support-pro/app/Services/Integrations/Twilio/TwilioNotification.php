<?php

namespace FluentSupportPro\App\Services\Integrations\Twilio;

use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Integrations\NotificationIntegrationBase;
use FluentSupport\Framework\Support\Arr;

class TwilioNotification extends NotificationIntegrationBase
{
    public $key = 'twilio_settings';

    public function __construct()
    {
        $this->registerActions();
    }

    public function registerActions()
    {
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
                $message = $this->sendNotification([
                    'ticket' => $ticket,
                    'person' => $person
                ], 'ticket_closed');

                if(!$message) {
                    return false;
                }

                return true;
            }
        }, 20, 2);

        add_action('fluent_support/response_added_by_customer', function ($response, $ticket, $customer) {
            if ($this->isNotificationActivated('response_added_by_customer')) {
                $this->sendNotification([
                    'ticket'   => $ticket,
                    'response' => $response,
                    ''
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

        add_filter('fluent_support/integration_drivers', function ($drivers) {
            $drivers[] = [
                'key'         => 'twilio_settings',
                'title'       => 'Twilio',
                'description' => __('Send notifications to your agents WhatsApp number using twilio and make them more active', 'fluent-support-pro').'<p><a href="https://fluentsupport.com/docs/whatsapp-integration-via-twilio/" rel="noopener" target="_blank">Please read the documentation before you get started</a></p>'
            ];
            return $drivers;
        }, 4, 1);
    }

    public function getSettings($withFields = false)
    {
        $data = [
            'settings' => TwilioHelper::getSettings()
        ];

        if ($withFields) {
            $data['fields'] = $this->getFields();
        }

        return $data;
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

    public function sendNotification($data, $type = false)
    {
        $message = '';

        if ($type == 'ticket_created') {
            $message = $this->ticketCreatedMessage($data['ticket'], $data['person']);
        } else if ($type == 'response_added_by_customer') {
            $message = $this->replyMessage($data['ticket'], $data['response']);
        } else if ($type == 'ticket_closed') {
            $message = $this->ticketClosed($data['ticket'], $data['person']);
        } else if ($type == 'ticket_assigned') {
            $message = $this->ticketAssigned($data['person'], $data['ticket'], $data['assigner']);
        }

        if ($message) {
            TwilioApi::sendNotification($message);
        }
    }

    private function ticketCreatedMessage($ticket, $person)
    {
        $message = '*New Ticket Created By '.sanitize_text_field($ticket->customer->full_name).'*'. "\n";
        $message .= '```'.$ticket->title.'#'. $ticket->id .'```'. "\n";
        $message .= strip_tags($ticket->content)."\n";
        $message .= '_'. esc_url_raw($this->getTicketEditLink($ticket)) .'_';
        return $message;

    }

    private function replyMessage($ticket, $response)
    {
        $message = '*' . sanitize_text_field($ticket->customer->full_name) .' Added a New Reply To Ticket No. #'. $ticket->id . '*'. "\n";
        $message .= strip_tags($response->content) ."\n";
        $message .= '_'. esc_url_raw($this->getTicketEditLink($ticket)) .'_';
        return $message;
    }

    private function ticketClosed($ticket, $person)
    {
        $message = '*'. $person->full_name . ' Closed Ticket No. #' . $ticket->id . '*';
        return $message;
    }

    private function ticketAssigned($person, $ticket, $assigner)
    {
        $message = '*('. $ticket->title .') has been assigned to '. $person->full_name . ' by ' . $assigner->full_name. '*' . "\n";
        if($assigner->user_id == $person->user_id) {
            $message = '*You assign ('. $ticket->title . ') to yourself*' . "\n";
        }
        $message .= '_'. esc_url_raw($this->getTicketEditLink($ticket)) .'_';

        $data = [
            'message' => $message,
            'to'      => $person->getMeta('whatsapp_number')
        ];

        return $data;
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

    public function getFields()
    {
        return [
            'title'       => 'Twilio Settings',
            'fields'      => [
                'account_sid'     => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Twilio Account SID', 'fluent-support-pro'),
                    'placeholder' => __('Twilio Account SID', 'fluent-support-pro'),
                    'help'        => __('Enter your Twilio Account SID Here', 'fluent-support-pro')
                ],
                'auth_token'      => [
                    'type'        => 'input-text',
                    'data_type'   => 'password',
                    'label'       => __('Twilio Auth Token', 'fluent-support-pro'),
                    'placeholder' => __('Twilio Auth Token', 'fluent-support-pro'),
                    'help'        => __('Enter your Twilio Auth Token Here', 'fluent-support-pro')
                ],
                'from_number'      => [
                    'type'        => 'input-text',
                    'data_type'   => 'number',
                    'label'       => __('Twilio WhatsApp Number', 'fluent-support-pro'),
                    'placeholder' => __('Twilio WhatsApp Number', 'fluent-support-pro'),
                    'help'        => __('Enter your Twilio WhatsApp Number Here', 'fluent-support-pro')
                ],
                'notification_events' => [
                    'type'    => 'checkbox-group',
                    'label'   => __('Notification Events', 'fluent-support-pro'),
                    'options' => [
                        'ticket_created'             => __('Ticket Created', 'fluent-support-pro'),
                        'ticket_closed'              => __('Ticket Closed', 'fluent-support-pro'),
                        'response_added_by_customer' => __('Replied By Customer', 'fluent-support-pro'),
                        'ticket_assigned'            => __('Ticket Assigned To Agent', 'fluent-support-pro'),
                    ]
                ],
                'status'             => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Twilio Integration', 'fluent-support-pro')
                ],
                'reply_from_whatsapp'   => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Reply From WhatsApp', 'fluent-support-pro'),
                    'dependency'    => [
                        'depends_on' => 'status',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ],
                'reply_from_html'      => [
                    'type'          => 'html-viewer',
                    'html'          => __('<strong><code>'. TwilioHelper::getWebhookUrl() .'</code></strong> Copy this URL to make your agent able to reply ticket from WhatsApp.', 'fluent-support-pro'),
                    'dependency'    => [
                        'depends_on' => 'reply_from_whatsapp',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ],
                'fallback_agent_id' => [
                    'type'        => 'agent-selectors',
                    'label'       => 'Fallback Agent for reply from WhatsApp message',
                    'placeholder' => 'Select Fallback Agent',
                    'dependency'  => [
                        'depends_on' => 'status',
                        'operator'   => '=',
                        'value'      => 'yes'
                    ]
                ]
            ],
            'button_text' => __('Save Twilio Settings', 'fluent-support-pro')
        ];
    }


    public function saveSettings($settings)
    {
        $prevSettings = TwilioHelper::getSettings();

        if ($prevSettings) {
            return $this->save($settings);
        }

        $this->save($settings);
    }
}
