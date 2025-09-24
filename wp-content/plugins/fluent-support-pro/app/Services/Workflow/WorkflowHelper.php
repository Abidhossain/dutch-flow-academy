<?php

namespace FluentSupportPro\App\Services\Workflow;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\Product;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Models\Workflow;
use FluentSupportPro\App\Models\WorkflowAction;
use FluentSupportPro\App\Services\ProHelper;

class WorkflowHelper
{
    /**
     * This function will generate the list of trigger fields and return
     * @param false $workflow
     * @return array
     */
    public static function getTriggers($workflow = false)
    {
        $products = Product::select(['id', 'title'])->orderBy('title', 'ASC')->get();
        $mailboxes = MailBox::select(['id', 'name'])->orderBy('name', 'ASC')->get();
        $agents = Agent::select(['id', 'first_name', 'last_name'])->oldest()->get();

        $formattedProducts = [];
        $formattedMailboxes = [];
        $formattedAgents = [];

        foreach ($products as $product) {
            $formattedProducts[strval($product->id)] = $product->title;
        }

        foreach ($mailboxes as $mailbox) {
            $formattedMailboxes[strval($mailbox->id)] = $mailbox->name;
        }

        foreach ($agents as $agent) {
            $formattedAgents[strval($agent->id)] = $agent->first_name.' '.$agent->last_name;
        }

        $triggers = [
            'triggers'   => [
                'fluent_support/ticket_created'             => [
                    'title'                => 'On Ticket Creation',
                    'description'          => 'This workflow will be initiated on when a new ticket has been submitted by customer',
                    'supported_conditions' => apply_filters('fluent_support/workflow_ticket_created_supported_conditions', [
                        'customer.first_name',
                        'customer.last_name',
                        'customer.email',
                        'message.title',
                        'message.content',
                        'message.attachments',
                        'ticket.client_priority',
                        'ticket.mailbox_id',
                        'message.added_time_range',
                        'message.added_date_range',
                        'ticket.product_id',
                        'fluent_crm.tags',
                        'fluent_crm.lists',
                    ]),
                ],
                'fluent_support/response_added_by_customer' => [
                    'title'                => 'On Customer Response',
                    'description'          => 'This workflow will be initiated when customer add a response to an existing ticket',
                    'supported_conditions' => apply_filters('fluent_support/workflow_response_added_by_customer_supported_conditions', [
                            'customer.first_name',
                            'customer.last_name',
                            'customer.email',
                            'message.content',
                            'message.attachments',
                            'ticket.agent_id',
                            'ticket.mailbox_id',
                            'bookmarks.tag_id',
                            'message.added_time_range',
                            'message.added_date_range',
                            'ticket.product_id',
                            'fluent_crm.tags',
                            'fluent_crm.lists',
                        ]),
                ],
                'fluent_support/ticket_closed' => [
                    'title'                => 'On Ticket Closed',
                    'description'          => 'This workflow will be initiated when a ticket is closed',
                    'supported_conditions' => apply_filters('fluent_support/workflow_ticket_closed_supported_conditions', [
                            'customer.first_name',
                            'customer.last_name',
                            'customer.email',
                            'ticket.mailbox_id',
                            'ticket.product_id',
                            'fluent_crm.tags',
                            'fluent_crm.lists',
                        ]),
                ]
            ],
            'conditions' => static::getConditions(),
        ];

        if(defined('FLUENTCRM')) {
            return self::buildConditionCheckerForCRM($triggers);
        } else {
            return $triggers;
        }
    }
    private static function getConditions()
    {
        $products = Product::select(['id', 'title'])->oldest('title')->get();
        $mailboxes = MailBox::select(['id', 'name'])->oldest('name')->get();
        $agents = Agent::select(['id', 'first_name', 'last_name'])->oldest()->get();

        $formattedProducts = [];
        $formattedMailboxes = [];
        $formattedAgents = [];

        foreach ($products as $product) {
            $formattedProducts[strval($product->id)] = $product->title;
        }

        foreach ($mailboxes as $mailbox) {
            $formattedMailboxes[strval($mailbox->id)] = $mailbox->name;
        }

        foreach ($agents as $agent) {
            $formattedAgents[strval($agent->id)] = $agent->first_name.' '.$agent->last_name;
        }


        $conditions = [
            'customer.first_name'      => [
                'title'     => 'Customer First Name',
                'data_type' => 'string',
                'group'     => 'Customer'
            ],
            'customer.last_name'       => [
                'title'     => 'Customer Last Name',
                'data_type' => 'string',
                'group'     => 'Customer'
            ],
            'customer.email'           => [
                'title'     => 'Customer Email',
                'data_type' => 'string',
                'group'     => 'Customer'
            ],
            'message.title'            => [
                'title'     => 'Ticket Title',
                'data_type' => 'string',
                'group'     => 'Message'
            ],
            'message.content' => [
                'title' => 'Message Content',
                'data_type' => 'string',
                'group'     => 'Message'
            ],
            'message.attachments'      => [
                'title'     => 'Attachments',
                'data_type' => 'yes_no',
                'default'   => 'yes',
                'options'   => [
                    'yes' => 'Has an attachment',
                    'no'  => 'Does not have an attachment'
                ],
                'group'     => 'Message'
            ],
            'message.added_time_range' => [
                'title'     => 'Added Time Range',
                'data_type' => 'time_range',
                'group'     => 'Message'
            ],
            'message.added_date_range' => [
                'title'     => 'Added Date Range',
                'data_type' => 'date_range',
                'group'     => 'Message'
            ],
            'ticket.client_priority'   => [
                'title'     => 'Ticket Priority (Client)',
                'data_type' => 'single_dropdown',
                'options'   => Helper::customerTicketPriorities(),
                'group'     => 'Ticket'
            ],
            'ticket.product_id'        => [
                'title'     => 'Selected Product',
                'data_type' => 'single_dropdown',
                'options'   => $formattedProducts,
                'group'     => 'Ticket'
            ],
            'ticket.mailbox_id'        => [
                'title'     => 'Mailbox',
                'data_type' => 'single_dropdown',
                'options'   => $formattedMailboxes,
                'group'     => 'Ticket'
            ],
            'bookmarks.tag_id'        => [
                'title'     => 'Bookmarks',
                'data_type' => 'multiple_select',
                'options'   => $formattedAgents,
                'group'     => 'Ticket'
            ]
        ];

        return apply_filters('fluent_support/workflow_conditions', $conditions);
    }

    private static function buildConditionCheckerForCRM($condition)
    {
        if (defined('FLUENTCRM')) {
            $condition['conditions']['fluent_crm.tags'] = [
                'title' => 'Customer CRM Tag(s)',
                'data_type' => 'multiple_select',
                'options' => self::getFluentCrmTagsOrLists(),
                'group' => 'Fluent CRM'
            ];
            $condition['conditions']['fluent_crm.lists'] = [
                'title' => 'Customer CRM List(s)',
                'data_type' => 'multiple_select',
                'options' => self::getFluentCrmTagsOrLists('lists'),
                'group' => 'Fluent CRM'
            ];
            return $condition;
        }
    }

    public static function sortActionsBySequence($actions, $workflow_id)
    {
        $actionSequence = Meta::where([
            'object_type' => 'workflow_meta',
            'object_id' => $workflow_id,
            'key' => '_fs_workflow_action_sequence'
        ])->get('value')->first();

        if (!$actionSequence) {
            return $actions;
        }

        $actionSequence = maybe_unserialize($actionSequence->value);

        $sortedActions = $actions->sortBy(function ($action) use ($actionSequence) {
            return array_search($action->id, $actionSequence);
        })->values();

        return $sortedActions->all();
    }

    private static function getFluentCrmTagsOrLists($type = 'tags', $createOptions = false)
    {
        if (defined('FLUENTCRM')) {
            $tagsOrLists = [];
            $data = [];
            if ($type == 'lists') {
                $listApi = \FluentCrmApi('lists');
                $data = $listApi->all();
            } else {
                $tagApi = \FluentCrmApi('tags');
                $data = $tagApi->all();
            }

            if ($createOptions) {
                foreach ($data as $key => $value) {
                    $tagsOrLists[] = [
                        'id' => $value['id'],
                        'title' => $value['title']
                    ];
                }
            } else {
                foreach ($data as $key => $value) {
                    $tagsOrLists[$value['id']] = $value['title'];
                }
            }

            return $tagsOrLists;
        }
    }

    public static function getActions($workFlow)
    {
        $extraAgentOptions = [
            [
                'id'    => 'ticket_agent_id',
                'title' => 'Current Assignee'
            ],
            [
                'id'    => 'last_agent_id',
                'title' => 'Last Agent to reply'
            ]
        ];

        $actions = [
            'fs_action_create_response' => [
                'title'             => 'Add Response',
                'settings_defaults' => [
                    'response_body' => ''
                ],
                'fields'            => [
                    'response_body'  => [
                        'type'  => 'wp-editor',
                        'label' => 'Response Body',
                        'props'       => [
                            'show-shortcodes'   => true,
                            'show-saved-replies' => true
                        ]
                    ],
                    'agent_id'       => [
                        'type'          => 'agent-selectors',
                        'label'         => 'Response From',
                        'extra_options' => $extraAgentOptions,
                        'inline_help'   => 'Leave empty for manual workflow'
                    ],
                    'fallback_agent' => [
                        'type'        => 'agent-selectors',
                        'label'       => 'Fallback Agent',
                        'inline_help' => 'If agent could not be found from "Response From" selector then this agent will be used. Leave empty for manual Workflow'
                    ]
                ]
            ],
            'fs_action_assign_agent'    => [
                'title'             => 'Assign Agent',
                'settings_defaults' => [
                    'agent_id'      => '',
                    'skip_if_exist' => 'yes'
                ],
                'fields'            => [
                    'agent_id'      => [
                        'type'          => 'agent-selectors',
                        'label'         => 'Select Agent',
                        'extra_options' => [
                            [
                                'id'    => 'unassigned',
                                'title' => 'Unassigned'
                            ]
                        ]
                    ],
                    'skip_if_exist' => [
                        'type'           => 'inline-checkbox',
                        'true_label'     => 'yes',
                        'false_label'    => 'no',
                        'checkbox_label' => 'Skip if ticket already have a agent assigned'
                    ]
                ]
            ],
            'fs_action_create_note'     => [
                'title'             => 'Add Internal Note',
                'settings_defaults' => [
                    'response_body' => ''
                ],
                'fields'            => [
                    'response_body'  => [
                        'type'  => 'wp-editor',
                        'label' => 'Note Body',
                        'props'       => [
                            'show-shortcodes'   => true,
                            'show-saved-replies' => true,
                        ]
                    ],
                    'agent_id'       => [
                        'type'          => 'agent-selectors',
                        'label'         => 'Note From',
                        'extra_options' => $extraAgentOptions,
                        'inline_help'   => 'Leave empty for manual workflow'
                    ],
                    'fallback_agent' => [
                        'type'        => 'agent-selectors',
                        'label'       => 'Fallback Agent',
                        'inline_help' => 'If agent could not be found from "Note From" selector then this agent will be used. Leave empty for manual Workflow'
                    ]
                ]
            ],
            'fs_action_close_ticket'    => [
                'title'             => 'Close Ticket',
                'settings_defaults' => [
                    'agent_id'       => '',
                    'fallback_agent' => '',
                ],
                'fields'            => [
                    'agent_id'       => [
                        'type'          => 'agent-selectors',
                        'label'         => 'Response From',
                        'extra_options' => $extraAgentOptions,
                        'inline_help'   => 'Leave empty for manual workflow'
                    ],
                    'fallback_agent' => [
                        'type'        => 'agent-selectors',
                        'label'       => 'Fallback Agent',
                        'inline_help' => 'If agent could not be found from "Response From" selector then this agent will be used. Leave empty for manual Workflow'
                    ]
                ]
            ],
            'fs_action_add_tags'        => [
                'title'             => 'Add Tag(s)',
                'settings_defaults' => [
                    'tag_ids' => []
                ],
                'fields'            => [
                    'tag_ids' => [
                        'is_multiple' => true,
                        'label'       => 'Select Tags',
                        'type'        => 'tag-selectors',
                        'placeholder' => 'Select Tags'
                    ]
                ]
            ],
            'fs_action_remove_tags'     => [
                'title'             => 'Remove Tag(s)',
                'settings_defaults' => [
                    'tag_ids' => []
                ],
                'fields'            => [
                    'tag_ids' => [
                        'is_multiple' => true,
                        'label'       => 'Select Tags',
                        'type'        => 'tag-selectors',
                        'placeholder' => 'Select Tags'
                    ]
                ]
            ],
            'fs_delete_ticket'          => [
                'title'             => 'Delete Ticket',
                'settings_defaults' => [
                    'ticket_delete_html' => ''
                ],
                'fields'            => [
                    'ticket_delete_html' => [
                        'type' => 'html-viewer',
                        'html' => '<p><br />Ticket will be deleted permanently and no further action will run</p>'
                    ]
                ]
            ],
            'fs_block_customer'         => [
                'title'             => 'Block Ticket Submitter (Customer)',
                'settings_defaults' => [
                    'customer_block_html' => ''
                ],
                'fields'            => [
                    'customer_block_html' => [
                        'type' => 'html-viewer',
                        'html' => '<p><br />Customer will be blocked and can not create new ticket or access to previous tickets</p>'
                    ]
                ]
            ],
            'fs_trigger_webhook'        => [
                'title'             => 'Trigger Outgoing Webhook',
                'settings_defaults' => [
                    'content-type'  => 'application/json',
                    'included_data' => ['ticket', 'customer']
                ],
                'fields'            => [
                    'webhook_url'   => [
                        'type'        => 'input-text',
                        'label'       => 'Webhook URL',
                        'data-type'   => 'url',
                        'placeholder' => 'Your webhook URL'
                    ],
                    'content-type'  => [
                        'label'       => 'Content Type',
                        'type'        => 'input-options',
                        'placeholder' => 'Select Content Type',
                        'options'     => [
                            [
                                'id'    => 'application/x-www-form-urlencoded',
                                'title' => 'FORM',
                            ],
                            [
                                'id'    => 'application/json',
                                'title' => 'JSON',
                            ]
                        ]
                    ],
                    'included_data' => [
                        'type'    => 'checkbox-group',
                        'label'   => 'Included Data',
                        'options' => [
                            'ticket'                 => 'Ticket Information',
                            'customer'               => 'Customer Information',
                            'agent'                  => 'Agent Information',
                            'last_agent_response'    => 'Last Response Data From Agent',
                            'last_response'          => 'Last Response From Customer / Agent',
                            'custom_fields'          => 'Custom Fields'
                        ]
                    ]
                ]
            ],
            'fs_action_add_bookmarks'    => [
                'title'             => 'Add Bookmarks',
                'settings_defaults' => [
                    'bookmarks'      => [],
                ],
                'fields'            => [
                    'bookmarks'      => [
                        'type'          => 'agent-selectors',
                        'label'         => 'Select Agent(s)',
                        'is_multiple'      => true,
                    ],
                ],
            ],
            'fs_action_remove_bookmarks' => [
                'title'             => 'Remove Bookmarks',
                'settings_defaults' => [
                    'bookmarks'      => [],
                ],
                'fields'            => [
                    'bookmarks'      => [
                        'type'          => 'agent-selectors',
                        'label'         => 'Select Agent(s)',
                        'is_multiple'      => true,
                    ],
                ],
            ],
            'fs_action_change_mailbox' => [
                'title'             => 'Change Mailbox',
                'settings_defaults' => [
                    'mailbox_id'      => '',
                ],
                'fields'            => [
                    'mailbox_id'      => [
                        'type'          => 'input-options',
                        'label'         => 'Select Mailbox',
                        'options'       => self::getMailboxes(),
                    ],
                ],
            ],
            'fs_action_add_product' => [
                'title'             => 'Add Product',
                'settings_defaults' => [
                    'product_id'      => '',
                ],
                'fields'            => [
                    'product_id'      => [
                        'type'          => 'input-options',
                        'label'         => 'Select Product',
                        'options'       => ProHelper::getProducts(),
                    ],
                ],
            ],
        ];

        // FluentCRM Actions
        if (defined('FLUENTCRM')){
            $crmActions = [
                'fs_action_attach_crm_tags'        => [
                    'title'             => 'Add To FluentCRM Tag(s)',
                    'settings_defaults' => [
                        'tag_ids' => []
                    ],
                    'fields'            => [
                        'tag_ids' => [
                            'multiple' => true,
                            'label'       => 'Select FluentCRM Tags',
                            'type'        => 'input-options',
                            'placeholder' => 'Select FluentCRM Tags',
                            'options'     => self::getFluentCrmTagsOrLists('tags', true)
                        ]
                    ]
                ],
                'fs_action_attach_crm_lists'        => [
                    'title'             => 'Add To FluentCRM List(s)',
                    'settings_defaults' => [
                        'list_ids' => []
                    ],
                    'fields'            => [
                        'list_ids' => [
                            'multiple' => true,
                            'label'       => 'Select FluentCRM Lists',
                            'type'        => 'input-options',
                            'placeholder' => 'Select FluentCRM Lists',
                            'options'     => self::getFluentCrmTagsOrLists('lists', true)
                        ]
                    ]
                ],
                'fs_action_detach_crm_tags'        => [
                    'title'             => 'Remove From FluentCRM Tag(s)',
                    'settings_defaults' => [
                        'tag_ids' => []
                    ],
                    'fields'            => [
                        'tag_ids' => [
                            'multiple' => true,
                            'label'       => 'Select FluentCRM Tags',
                            'type'        => 'input-options',
                            'placeholder' => 'Select FluentCRM Tags',
                            'options'     => self::getFluentCrmTagsOrLists('tags', true)
                        ]
                    ]
                ],
                'fs_action_detach_crm_lists'        => [
                    'title'             => 'Remove From FluentCRM List(s)',
                    'settings_defaults' => [
                        'list_ids' => []
                    ],
                    'fields'            => [
                        'list_ids' => [
                            'multiple' => true,
                            'label'       => 'Select FluentCRM Lists',
                            'type'        => 'input-options',
                            'placeholder' => 'Select FluentCRM Lists',
                            'options'     => self::getFluentCrmTagsOrLists('lists', true)
                        ]
                    ]
                ],
                'fs_action_run_automation'     => [
                    'title'             => 'Run Automation',
                    'settings_defaults' => [
                        'automation_ids' => []
                    ],
                    'fields'            => [
                        'automation_id' => [
                            'is_multiple' => true,
                            'label'       => 'Select Automation',
                            'type'        => 'input-options',
                            'placeholder' => 'Select Automation',
                            'options'     => self::getFluentCrmAutomationLists()
                        ]
                    ]
                ],
            ];

            return $actions = array_merge($actions, $crmActions);
        }

        if ($workFlow->trigger_type == 'manual') {
            unset($actions['fs_action_create_response']['fields']['agent_id']);
            unset($actions['fs_action_create_response']['fields']['fallback_agent']);
            unset($actions['fs_action_create_note']['fields']['agent_id']);
            unset($actions['fs_action_create_note']['fields']['fallback_agent']);
            unset($actions['fs_action_close_ticket']['fields']['agent_id']);
            unset($actions['fs_action_close_ticket']['fields']['fallback_agent']);
        }

        return apply_filters('fluent_support/workflow_actions', $actions, $workFlow);
    }

    private static function getMailboxes()
    {
        $mailboxes = [];
        $mailboxes = MailBox::all();


        $mailboxes = $mailboxes->each(function ($mailbox) {
            $mailbox['id'] = $mailbox['id'];
            $mailbox['title'] = $mailbox['name'];
        });

        return $mailboxes;
    }

    private static function getFluentCrmAutomationLists()
    {
        $automations = [];
        if (defined('FLUENTCRM')) {
            $funnelModel = \FluentCrm\App\Models\Funnel::class;
            return $funnelModel::select(['id', 'title'])->where('status', 'published')->oldest('title')->get()->toArray();
        }

        return $automations;
    }

    public static function syncActions($workflowId, $actions)
    {
        $syncIds = [];

        foreach ($actions as $action) {

            $actionData = Arr::only($action, ['id', 'title', 'action_name', 'settings']);
            $actionData = array_filter($actionData);

            $actionData['workflow_id'] = $workflowId;

            // Check if we have $id
            if (!empty($actionData['id'])) {
                $actionId = absint($actionData['id']);
                unset($actionData['id']);

                WorkflowAction::find($actionId)->update($actionData);
                $syncIds[] = $actionId;
            } else {
                // It's a new
                $newAction = WorkflowAction::create($actionData);
                $syncIds[] = $newAction->id;
                WorkflowHelper::updateActionSequence($workflowId, $syncIds);
            }
        }

        WorkflowAction::where('workflow_id', $workflowId)->whereNotIn('id', $syncIds)->delete();

        return true;

    }

    public static function updateActionSequence($workflowId, $actionSequence)
    {
        if (empty($actionSequence)) {
            return;
        }

        $previousValue = Meta::where([
            'object_type' => 'workflow_meta',
            'object_id' => $workflowId,
            'key' => '_fs_workflow_action_sequence'
        ])->first();

        $serializedSequence = maybe_serialize($actionSequence);

        if ($previousValue) {
            $previousValue->update(['value' => $serializedSequence]);
        } else {
            Meta::create([
                'object_type' => 'workflow_meta',
                'object_id' => $workflowId,
                'key' => '_fs_workflow_action_sequence',
                'value' => $serializedSequence
            ]);
        }
    }

    public static function generateUniqueTitle($title)
    {
        $numericSuffix = '';

        // Check if the title already ends with "- Copy"
        if (preg_match('/^(.*) - Copy(?: \((\d+)\))?$/i', $title, $matches)) {
            $baseTitle = $matches[1];
            $numericSuffix = isset($matches[2]) ? (int)$matches[2] : 1;
        } else {
            $baseTitle = $title;
        }

        $newTitle = "{$baseTitle} - Copy";

        // Check if the new title already exists in the database
        while (self::titleExists($newTitle)) {
            // Increment the numeric suffix and generate a new title
            $numericSuffix++;
            $newTitle = "{$baseTitle} - Copy ({$numericSuffix})";
        }

        return $newTitle;
    }

    private static function titleExists($title)
    {
        return Workflow::where('title', $title)->exists();
    }


    public static function duplicateActions($sourceWorkflow, $targetWorkflow)
    {
        $actions = WorkflowAction::where('workflow_id', $sourceWorkflow->id)->get();

        foreach ($actions as $action) {
            $newAction = $action->replicate();
            $newAction->workflow_id = $targetWorkflow->id;
            $newAction->save();
        }
    }
}
