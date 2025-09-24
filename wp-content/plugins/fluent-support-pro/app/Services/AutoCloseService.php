<?php

namespace FluentSupportPro\App\Services;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Tickets\ResponseService;
use FluentSupport\App\Services\Tickets\TicketService;
use FluentSupport\Framework\Support\Arr;

class AutoCloseService
{
    public static function getSettings()
    {
        $defaults = [
            'enabled'                     => 'no',
            'inactive_days'               => 30,
            'include_tags'                => [],
            'exclude_tags'                => [],
            'exclude_if_customer_waiting' => 'yes',
            'close_silently'              => 'yes',
            'add_close_response'          => 'no',
            'close_response_body'         => '',
            'closed_by_agent'             => '',
            'close_bookmarked_tickets'    => 'no',
        ];

        $settings = Helper::getOption('auto_close_settings', []);

        if (!$settings) {
            $settings = [];
        }

        $settings = wp_parse_args($settings, $defaults);

        if (empty($settings['close_response_body'])) {
            $settings['close_response_body'] = self::getDefaultBody();
        }

        return $settings;
    }

    public static function saveSettings($settings)
    {
        return Helper::updateOption('auto_close_settings', $settings);
    }

    public static function maybeCloseTickets()
    {
        $settings = Helper::getOption('auto_close_settings', []);
        if (!$settings || Arr::get($settings, 'enabled') != 'yes') {
            return false;
        }

        $disableEvents = Arr::get($settings, 'close_silently') == 'yes';
        $doNotCloseBookmarked = 'yes' == Arr::get($settings, 'close_bookmarked_tickets');

        $inactiveDays = absint(Arr::get($settings, 'inactive_days'));
        $dateTime = date('Y-m-d H:i:s', current_time('timestamp') - $inactiveDays * 86400);

        $ticketsQuery = Ticket::with(['agent'])->where('updated_at', '<', $dateTime)
            ->whereNotIn('status', ['closed', 'new']);

        $defaultAgent = false;

        if (Arr::get($settings, 'exclude_if_customer_waiting') == 'yes') {
            $ticketsQuery->where(function ($q) {
                $q->whereColumn('last_customer_response', '<', 'last_agent_response');
            });
        } else if ($fallBackAgentId = Arr::get($settings, 'closed_by_agent')) {
            $defaultAgent = Agent::where('id', $fallBackAgentId)->first();
            if (!$defaultAgent) {
                $defaultAgent = Agent::orderBy('id', 'ASC')->first();
            }
        }

        if ($includedTags = Arr::get($settings, 'include_tags')) {
            $ticketsQuery->whereHas('tags', function ($q) use ($includedTags) {
                $q->whereIn('fs_taggables.id', $includedTags);
            });
        }

        if ($excludedTags = Arr::get($settings, 'exclude_tags')) {
            $ticketsQuery->whereDoesntHave('tags', function ($q) use ($excludedTags) {
                $q->whereIn('fs_taggables.id', $excludedTags);
            });
        }


        $tickets = $ticketsQuery->limit(150)->get();

        if ($tickets->isEmpty()) {
            return false; // We don't have tickets
        }

        $defaultResponse = false;
        if (Arr::get($settings, 'add_close_response') == 'yes') {
            $defaultResponse = Arr::get($settings, 'close_response_body');
        }

        $internalNote = __('Ticket has been closed automatically by the system due to inactivity', 'fluent-support-pro');

        add_filter('fluent_support/ticket_close_internal_note', function ($note) use ($internalNote) {
            return $internalNote;
        });

        $responseService = new ResponseService();
        $ticketService = new TicketService();

        $startTime = time();

        foreach ($tickets as $ticket) {
            if ((time() - $startTime) > 45) {
                return false;
            }

            if( $doNotCloseBookmarked && !$ticket->watchers->isEmpty()){
                continue;
            }

            $agent = $ticket->agent;
            if (!$agent) {
                $agent = $defaultAgent;
            }
            if (!$agent) {
                continue;
            }

            if ($defaultResponse) {
                $responseService->createResponse([
                    'content' => $defaultResponse,
                    'close_ticket' => 'yes'
                ], $agent, $ticket, $disableEvents);
                continue;
            }

            // Just close the ticket
            $ticketService->close($ticket, $agent, $internalNote, $disableEvents);
        }

        return true;
    }

    public static function getSettingsFields()
    {
        return [
            'inactive_days'               => [
                'label'     => __('Inactive days for tickets that you want to close automatically', 'fluent-support-pro'),
                'type'      => 'input-text',
                'data_type' => 'number',
            ],
            'exclude_if_customer_waiting' => [
                'type'           => 'inline-checkbox',
                'true_label'     => 'yes',
                'false_label'    => 'no',
                'checkbox_label' => __('Do not close if customer is waiting for response', 'fluent-support-pro')
            ],
            'include_tags'                => [
                'wrapper_class' => 'fs_half_field',
                'label'         => __('Close only if tickets are in the following tags', 'fluent-support-pro'),
                'type'          => 'tag-selectors',
                'is_multiple'   => true
            ],
            'exclude_tags'                => [
                'wrapper_class' => 'fs_half_field',
                'label'         => __('Do Not close if tickets are in the following tags', 'fluent-support-pro'),
                'type'          => 'tag-selectors',
                'is_multiple'   => true
            ],
            'close_silently'              => [
                'type'           => 'inline-checkbox',
                'true_label'     => 'yes',
                'false_label'    => 'no',
                'checkbox_label' => __('Close tickets silently (no email or events will be fired)', 'fluent-support-pro')
            ],
            'close_bookmarked_tickets'     => [
                'type'           => 'inline-checkbox',
                'true_label'     => 'yes',
                'false_label'    => 'no',
                'checkbox_label' => __('Don\'t close bookmarked tickets', 'fluent-support-pro')
            ],
            'add_close_response'          => [
                'type'           => 'inline-checkbox',
                'true_label'     => 'yes',
                'false_label'    => 'no',
                'checkbox_label' => __('Add Custom Response when closing the ticket', 'fluent-support-pro')
            ],
            'close_response_body'         => [
                'type'       => 'wp-editor-field',
                'show_shortcodes' => true,
                'show_saved_replies' => true,
                'label'      => __('Custom Response Text that will be sent to customer when closing the ticket automatically', 'fluent-support-pro'),
                'dependency' => [
                    'depends_on' => 'add_close_response',
                    'operator'   => '=',
                    'value'      => 'yes'
                ]
            ],
            'closed_by_agent'             => [
                'type'       => 'agent-selectors',
                'label'      => __('Select default agent if the ticket has not assigned to any agent', 'fluent-support-pro'),
                'dependency' => [
                    'depends_on' => 'exclude_if_customer_waiting',
                    'operator'   => '=',
                    'value'      => 'no'
                ]
            ]
        ];
    }

    private static function getDefaultBody()
    {
        return '<p>Hello {{customer.first_name}},</p><p>It seems that you are away.Therefore, I will have to close the conversation for now. If you still need our help, you can contact us again anytime</p>';
    }
}
