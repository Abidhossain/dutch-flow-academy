<?php

namespace FluentSupportPro\App\Services\Workflow;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Product;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Person;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Tickets\ResponseService;
use FluentSupport\App\Services\Tickets\TicketService;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\Integrations\OutgoingWebhook\OutgoingWebhookApi;
use FluentSupportPro\App\Services\TicketBookmarkService;
use FluentSupport\App\Models\MailBox;
use FluentCrm\App\Services\Funnel\FunnelProcessor;
use FluentCrm\App\Models\Subscriber;
use FluentCrm\App\Models\Funnel;

class ActionRunner
{
    private $workflow;

    private $ticket;

    private $preCompleted = false;

    public $builtInActionMaps = [
        'fs_action_create_response'  => 'createResponse',
        'fs_action_assign_agent'     => 'assignAgent',
        'fs_action_create_note'      => 'createNote',
        'fs_action_close_ticket'     => 'closeTicket',
        'fs_action_add_tags'         => 'addTags',
        'fs_action_remove_tags'      => 'removeTags',
        'fs_delete_ticket'           => 'deleteTicket',
        'fs_block_customer'          => 'blockCustomer',
        'fs_trigger_webhook'         => 'triggerOutgoingWebhook',
        'fs_action_attach_crm_tags'  => 'triggerAttachCrmTags',
        'fs_action_attach_crm_lists' => 'triggerAttachCrmLists',
        'fs_action_detach_crm_tags'  => 'triggerDetachCrmTags',
        'fs_action_detach_crm_lists' => 'triggerDetachCrmLists',
        'fs_action_add_bookmarks'    => 'triggerAddBookmarks',
        'fs_action_remove_bookmarks' => 'triggerRemoveBookmarks',
        'fs_action_change_mailbox'   => 'triggerChangeMailbox',
        'fs_action_run_automation'   => 'triggerCrmAutomation',
        'fs_action_add_product'      => 'addProduct',
    ];

    public function __construct($workflow, $ticket)
    {
        $this->workflow = $workflow;
        $this->ticket = $ticket;
    }

    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }

    public function reFetchTicket()
    {
        $this->ticket = Ticket::find($this->ticket->id);
    }

    public function setWorkflow($workflow)
    {
        $this->workflow = $workflow;
    }

    public function runActions($actions)
    {
        foreach ($actions as $action) {
            $this->run($action);
        }
    }

    public function run($action)
    {

        if ($this->preCompleted) {
            return false;
        }

        if (isset($this->builtInActionMaps[$action->action_name])) {
            $this->{$this->builtInActionMaps[$action->action_name]}($action);
        } else {
            do_action('fluent_support/run_action_' . $action->action_name, $action, $this->workflow, $this->ticket);
        }
    }

    private function createResponse($action)
    {
        $agent = $this->resolveAgent($action);
        if (!$agent) {
            return false;
        }

        $responseText = Arr::get($action->settings, 'response_body');

        $data = [
            'content'           => $responseText,
            'conversation_type' => 'response',
            'source'            => 'automation'
        ];

        // Support for shortcode parsing
        $data = apply_filters('fluent_support/parse_smartcode_data', $data, [
            'customer'  => $this->ticket->customer,
            'agent'     => $agent
        ]);

        (new ResponseService())->createResponse($data, $agent, $this->ticket);
        $this->reFetchTicket();

    }

    private function createNote($action)
    {
        $agent = $this->resolveAgent($action);
        if (!$agent) {
            return false;
        }

        $responseText = Arr::get($action->settings, 'response_body');

        $data = [
            'content'           => $responseText,
            'conversation_type' => 'note',
            'source'            => 'automation'
        ];

        return (new ResponseService())->createResponse($data, $agent, $this->ticket);
    }

    private function assignAgent($action)
    {
        $agentId = Arr::get($action->settings, 'agent_id');

        if ($agentId == 'unassigned') {
            $this->ticket->agent_id = NULL;
            $this->ticket->save();
        } else {
            $agent = Agent::find($agentId);
            $assigner = Agent::find(intval($action->settings['agent_id']));
            if ($agent) {
                $this->ticket->agent_id = $agent->id;
                $this->ticket->save();
                do_action('fluent_support/agent_assigned_to_ticket', $agent, $this->ticket, $assigner);
            }
        }
    }

    private function closeTicket($action)
    {
        $agent = $this->resolveAgent($action);
        if ($agent) {
            (new TicketService())->close($this->ticket, $agent, 'Ticket has been closed from automation');
            $this->reFetchTicket();
        }
    }

    private function addTags($action)
    {
        if ($tagIds = Arr::get($action->settings, 'tag_ids', [])) {
            $result = $this->ticket->applyTags($tagIds);
            if ($result) {
                $this->reFetchTicket();
            }
        }

    }

    private function removeTags($action)
    {
        if ($tagIds = Arr::get($action->settings, 'tag_ids', [])) {
            $result = $this->ticket->detachTags($tagIds);
            if ($result) {
                $this->reFetchTicket();
            }
        }
    }

    private function runCrmActions($action)
    {
        $this->ticket->runCrmActions();
    }

    private function deleteTicket($action)
    {
        $ticket = Ticket::find($this->ticket->id);
        if ($ticket) {
            $ticket->deleteTicket();
            $this->preCompleted = true;
        }
    }

    private function blockCustomer($action)
    {
        $customer = $this->ticket->customer;
        $customer->status = 'inactive';
        $customer->save();
    }

    private function triggerOutgoingWebhook($action)
    {
        $settings = $action->settings;

        if (empty($settings['included_data']) || empty($settings['webhook_url'])) {
            return false;
        }

        $includedData = $settings['included_data'];
        $webhookUrl = $settings['webhook_url'];

        $data = [];

        if (in_array('ticket', $includedData)) {
            $ticketArray = $this->ticket->toArray();
            $keys = (new Ticket())->getFillable();
            $keys[] = 'id';
            $keys[] = 'created_at';
            $keys[] = 'updated_at';
            $data['ticket'] = Arr::only($ticketArray, $keys);
        }


        if (in_array('customer', $includedData)) {
            $customer = $this->ticket->customer;
            $customerArray = $customer->toArray();
            $data['customer'] = Arr::only($customerArray, (new Person())->getFillable());
        }

        if (in_array('agent', $includedData)) {
            if ($this->ticket->agent) {
                $agent = $this->ticket->agent;
                $customerArray = $agent->toArray();
                $data['agent'] = Arr::only($customerArray, (new Person())->getFillable());
            } else {
                $data['agent'] = [];
            }
        }

        if (in_array('last_agent_response', $includedData)) {
            $ticket = $this->ticket;
            $lastResponse = $ticket->getLastAgentResponse();

            if ($lastResponse) {
                $lastResponseArray = (array) $lastResponse;
                $keys = (new Conversation())->getFillable();
                $keys[] = 'created_at';
                $keys[] = 'updated_at';
                $data['last_agent_response'] = Arr::only($lastResponseArray, $keys);
            } else {
                $data['last_agent_response'] = [];
            }
        }

        if (in_array('last_response', $includedData)) {
            $lastResponse = $this->ticket->getLastResponse();
            if ($lastResponse) {
                $lastResponseArray = (array) $lastResponse;
                $keys = (new Conversation())->getFillable();
                $keys[] = 'id';
                $keys[] = 'created_at';
                $keys[] = 'updated_at';
                $data['last_response'] = Arr::only($lastResponseArray, $keys);
            } else {
                $data['last_response'] = [];
            }
        }

        if (in_array('custom_fields', $includedData)) {
            $customData = $this->ticket->customData('admin', false);
            $data['custom_fields'] = $customData ?: [];
        }

        if (!$data) {
            return false;
        }

        $data = apply_filters('fluent_support/outgoing_webhook_data', $data, $action, $this->ticket);

        $contentType = Arr::get($settings, 'content-type', 'application/json');

        OutgoingWebhookApi::sendData($webhookUrl, $data, $contentType, 'POST');
    }

    private function resolveAgent($action)
    {
        if ($this->workflow->trigger_type == 'manual') {
            return Helper::getCurrentAgent();
        }

        $givenAgentId = Arr::get($action->settings, 'agent_id');

        if ($givenAgentId) {
            if (is_numeric($givenAgentId)) {
                return Agent::where('id', $givenAgentId)->first();
            }

            if ($givenAgentId == 'ticket_agent_id') {
                if ($this->ticket->agent_id) {
                    return Agent::where('id', $this->ticket->agent_id)->first();
                }
            } else if ($givenAgentId == 'last_agent_id') {
                if ($lastAgentResponse = $this->ticket->getLastAgentResponse()) {
                    return Agent::where('id', $lastAgentResponse->person_id)->first();
                }
            }
        }

        // We have to check for the fallback agent now

        $fallbackAgent = Arr::get($action->settings, 'fallback_agent');

        if (!$fallbackAgent) {
            return false;
        }

        return Agent::where('id', $fallbackAgent)->first();

    }

    private function triggerAttachCrmTags($action)
    {
        if(defined('FLUENTCRM')) {
            $settings = $action->settings;
            $lists = Arr::get($settings, 'tag_ids');

            $customerEmail = $this->ticket->customer->email;

            $crmContact = \FluentCrmApi('contacts')->getContact($customerEmail);

            if($crmContact) {
                return $crmContact->attachTags($lists);
            }
        }
    }

    private function triggerAttachCrmLists($action)
    {
        if (defined('FLUENTCRM')) {
            $settings = $action->settings;
            $lists = Arr::get($settings, 'list_ids');

            $customerEmail = $this->ticket->customer->email;

            $crmContact = \FluentCrmApi('contacts')->getContact($customerEmail);

            if($crmContact) {
                return $crmContact->attachLists($lists);
            }
        }
    }

    private function triggerDetachCrmTags($action)
    {
        if (defined('FLUENTCRM')) {
            $settings = $action->settings;
            $lists = Arr::get($settings, 'tag_ids');

            $customerEmail = $this->ticket->customer->email;

            $crmContact = \FluentCrmApi('contacts')->getContact($customerEmail);

            if($crmContact) {
                return $crmContact->detachTags($lists);
            }
        }
    }

    private function triggerDetachCrmLists($action)
    {
        if(defined('FLUENTCRM')) {
            $settings = $action->settings;
            $lists = Arr::get($settings, 'list_ids');

            $customerEmail = $this->ticket->customer->email;

            $crmContact = \FluentCrmApi('contacts')->getContact($customerEmail);

            if($crmContact) {
                return $crmContact->detachLists($lists);
            }
        }
    }

    private function triggerAddBookmarks ( $action )
    {
        $settings = $action->settings;
        $bookmarks = Arr::get($settings, 'bookmarks');

        if( ! $bookmarks ) {
            return false;
        }

        $bookmarkService = new TicketBookmarkService;

        return $bookmarkService->addBookmarks( $bookmarks, $this->ticket->id );
    }

    private function triggerRemoveBookmarks ( $action )
    {
        $settings = $action->settings;
        $bookmarks = Arr::get($settings, 'bookmarks');

        if( ! $bookmarks ) {
            return false;
        }

        $bookmarkService = new TicketBookmarkService;

        return $bookmarkService->removeBookmarks( $bookmarks, $this->ticket->id );
    }

    private function triggerChangeMailbox($actions)
    {
        $mailboxId = Arr::get($actions->settings, 'mailbox_id');

        if (!$mailboxId) {
            return false;
        }

        $mailbox = Mailbox::findOrFail($mailboxId);

        if (!$mailbox) {
            return false;
        }

        $this->ticket->mailbox_id = $mailboxId;
        $this->ticket->save();

        return true;
    }

    private function triggerCrmAutomation ($actions)
    {
        if(defined('FLUENTCRM')) {
            $autmationId = Arr::get($actions->settings, 'automation_id');
            $automation = Funnel::find($autmationId);

            if (!$automation) {
                return false;
            }

            $customerEmail = $this->ticket->customer->email;
            $subscriber = Subscriber::where('email', $customerEmail)->first();

            if (!$subscriber->exists) {
                // Create subscriber if it doesn't exist
                $subscriberData = $this->ticket->customer->toArray();
                $subscriber = \FluentCrmApi('contacts')->createOrUpdate($subscriberData);
            }

            (new FunnelProcessor())->startFunnelSequence($automation, [], [
                'source_trigger_name' => 'fcrm_manual_attach'
            ], $subscriber);
        }
    }

    private function addProduct($action)
    {
        $productId = Arr::get($action->settings, 'product_id');

        if (!$productId) {
            return false;
        }

        // Verify the product exists using the Product model
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        $this->ticket->product_id = $productId;
        $this->ticket->save();

        $this->reFetchTicket();

        return true;
    }
}
