<?php

namespace FluentSupportPro\App\Services\Workflow;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Ticket;
use FluentSupportPro\App\Models\WorkflowAction;
use FluentSupport\App\Models\Meta;

class WorkflowRunner
{
    private $workflow;

    private $actions;

    private $ticket;

    public function __construct($workflow, $ticket, $actions = false)
    {
        $this->workflow = $workflow;

        if ($actions === false) {
            $actions = WorkflowAction::where('workflow_id', $workflow->id)->get();
        }

        $actions = WorkflowHelper::sortActionsBySequence($actions, $workflow->id);

        $this->actions = $actions;
        $this->ticket = $ticket;
    }

    public function runActions()
    {
        $ticket = $this->getTicket();
        if (!$ticket || !$this->actions) {
            return false;
        }

        (new ActionRunner($this->workflow, $ticket))->runActions($this->actions);

        return true;

    }

    public function getTicket()
    {
        if (is_numeric($this->ticket)) {
            $this->ticket = Ticket::find($this->ticket);
        } elseif (is_object($this->ticket)) {
            $this->ticket = isset($this->ticket->ticket_id) ? Ticket::find($this->ticket->ticket_id) : $this->ticket;
        }

        return $this->ticket;
    }

}
