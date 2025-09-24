<?php

namespace FluentSupportPro\App\Services\Integrations\Discord;

use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Integrations\NotificationIntegrationBase;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\Integrations\Discord\DiscordApi;

class DiscordNotification extends NotificationIntegrationBase
{
    public $key = 'discord_settings';

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
                'key'         => 'discord_settings',
                'title'       => 'Discord',
                'description' => __('Send notifications to Discord Channel and keep your support agents more active', 'fluent-support-pro').'<p><a href="https://fluentsupport.com/docs/managing-tickets-using-discord" rel="noopener" target="_blank">Please read the documentation before you get started</a></p>'
            ];
            return $drivers;
        }, 3, 1);

    }

    public function getSettings($withFields = false)
    {
        $data = [
            'settings' => DiscordHelper::getSettings()
        ];

        if ($withFields) {
            $data['fields'] = $this->getFields();
        }

        return $data;
    }

    public function getFields()
    {
        return [
            'title'       => 'Discord Notification Settings',
            'fields'      => [
                'webhook_url'          => [
                    'type'        => 'input-text',
                    'data_type'   => 'text',
                    'label'       => __('Discord Incoming Webhook URL', 'fluent-support-pro'),
                    'placeholder' => __('Discord Incoming Webhook URL', 'fluent-support-pro'),
                    'help'        => __('Enter your Discord Incoming Webhook URL Here', 'fluent-support-pro')
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
                'status'              => [
                    'type'           => 'inline-checkbox',
                    'true_label'     => 'yes',
                    'false_label'    => 'no',
                    'label'          => '',
                    'checkbox_label' => __('Enable Discord Notifications', 'fluent-support-pro')
                ]
            ],
            'button_text' => __('Save Discord Settings', 'fluent-support-pro')
        ];
    }


    public function saveSettings($settings)
    {
        $prevSettings = DiscordHelper::getSettings();

        if ($prevSettings) {
            return $this->save($settings);
        }

        $this->save($settings);
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
            return DiscordApi::send($message);
        }
    }


    private function ticketCreatedMessage($ticket, $person)
    {

        $fields = [
            0 => [
                'name' => 'Priority',
                'value' => ucfirst($ticket->client_priority),
                'inline' => true
            ]
        ];

        if ($ticket->product) {
            $fields[] = [
                'name' => 'Product',
                'value' =>  '#' . preg_replace('~[^\pL\d]+~u', '_', $ticket->product->title),
                'inline' => true
            ];
        }

        if($ticket->custom_fields = $ticket->customData('public', true)){
            $customFields = apply_filters('fluent_support/ticket_custom_fields', []);
            foreach ($ticket->custom_fields as $customFieldKey=>$customFieldValue){
                if(is_array($customFieldValue)){
                    $customFieldValue = implode(',' , $customFieldValue);
                }
                $fields[] = [
                    'name'   => str_replace('"',"",json_encode($customFields[$customFieldKey]['label'])),
                    'value'  => strip_tags($customFieldValue),
                    'inline' => true
                ];
            }
        }

		$message = [
			'embeds' => [
				0 => [
					'fields' => $fields,
					'title' => esc_html($ticket->title),
					'url' => esc_url_raw($this->getTicketEditLink($ticket)),
					'description' => sanitize_text_field($ticket->content),
					'color' => 6746192,
				],
			],
			'content' => '*New Ticket Submitted by ' . $person->full_name . ' (#' . $ticket->id . ')*',
		];
        return $message;

    }

    private function replyMessage($ticket, $response)
    {
        $fields = [
            0 => [
                'name' => 'Priority',
                'value' => ucfirst($ticket->client_priority),
                'inline' => true
            ]
        ];

        if ($ticket->product) {
            $fields[1] = [
                'name' => 'Product',
                'value' =>  '#' . preg_replace('~[^\pL\d]+~u', '_', $ticket->product->title),
                'inline' => true
            ];
        }

	    $message = [
		    'embeds' => [
			    0 => [
				    'fields' => $fields,
                    'title' => esc_html($ticket->title),
                    'url' => esc_url_raw($this->getTicketEditLink($ticket)),
                    'description' => sanitize_text_field($response->content),
				    'color' => 6746192,
			    ],
		    ],
		    'content' => '*New Reply Added To ' . $ticket->title . ' (#' . $ticket->id . ')*',
	    ];
        return $message;

    }

    private function ticketClosed($ticket, $person)
    {
	    $message = [
		    'content' => '*' . $person->full_name . ' Closed ' . $ticket->title . ' (#' . $ticket->id . '*)',
		    'embeds' => [
				0 => [
					'title' => esc_html(\mb_substr($ticket->title, 0, 30)),
					'url' => esc_url_raw($this->getTicketEditLink($ticket)),
					'color' => 6746192,
				]
		    ]
	    ];
        return $message;

    }

    private function ticketAssigned($person, $ticket, $assigner)
    {
        $text = '*' . $person->full_name . ' Assigned to ' . $ticket->title . ' (#' . $ticket->id . '*) by ' . $assigner->full_name;
        if ($assigner->user_id == $person->user_id) {
            $text = '*' . $person->full_name . ' Assign ' . $ticket->title . ' (#' . $ticket->id . '*) to self';
        }
        $message = [
            'content' => $text,
            'embeds' => [
                0 => [
                    'title' => esc_html(\mb_substr($ticket->title, 0, 30)),
                    'url' => esc_url_raw($this->getTicketEditLink($ticket)),
                    'color' => 6746192,
                ]
            ]
        ];
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

}
