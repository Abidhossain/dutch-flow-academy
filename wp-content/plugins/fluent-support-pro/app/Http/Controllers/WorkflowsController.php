<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Models\Workflow;
use FluentSupportPro\App\Models\WorkflowAction;
use FluentSupportPro\App\Services\Workflow\WorkflowHelper;
use FluentSupportPro\App\Services\Workflow\WorkflowRunner;
use FluentSupport\App\Models\Meta;

class WorkflowsController extends Controller
{

    /**This method get the list of workflows from the fs_workflows table and return
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $workflows = Workflow::orderBy('id', 'DESC')
            ->paginate();

        $triggers = WorkflowHelper::getTriggers();

        foreach ($workflows as $workflow) {
            if ($workflow->trigger_type == 'automatic' && $workflow->trigger_key) {
                $workflow->trigger_human_name = Arr::get($triggers, 'triggers.' . $workflow->trigger_key . '.title');
            }
        }

        return [
            'workflows' => $workflows
        ];
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $this->validate($data, [
            'title' => 'required|unique:fs_workflows'
        ]);

        $workflow = Workflow::create($data);

        return [
            'workflow' => $workflow,
            'message'  => __('Workflow has been created', 'fluent-support-pro')
        ];
    }

    public function getWorkflow(Request $request, $workflow_id)
    {
        //get the list of workflows from fs_workflows by workflow id
        $workflow = Workflow::findOrFail($workflow_id);

        $actions = WorkflowAction::where('workflow_id', $workflow->id)->get();

        $data = [
            'actions' => WorkflowHelper::sortActionsBySequence($actions, $workflow_id)
        ];

        //Get list of action field if request comes with action_fields
        if (in_array('action_fields', $request->get('with', []))) {
            $data['action_fields'] = WorkflowHelper::getActions($workflow);
        }

        if (in_array('trigger_fields', $request->get('with', []))) {
            $data['trigger_fields'] = WorkflowHelper::getTriggers($workflow);
        }

        $workflow = $workflow->toArray();

        if ($workflow['trigger_type'] == 'automatic' && empty($workflow['settings']['conditions'])) {
            if (!is_array($workflow['settings'])) {
                $workflow['settings'] = [];
            }
            $workflow['settings']['conditions'] = [[]];
        }

        $data['workflow'] = $workflow;

        return $data;

    }

    public function updateWorkflow(Request $request, $workflow_id)
    {
        $workflow = Workflow::findOrFail($workflow_id);
        $workFlowData = $request->get('workflow', []);

        $this->validate($workFlowData, [
            'title' => 'required'
        ]);

        $title = sanitize_text_field($workFlowData['title']);

        if (Workflow::where('title', $title)->where('id', '!=', $workflow_id)->first()) {
            return $this->sendError([
                'message' => __('Workflow title needs to be unique', 'fluent-support-pro')
            ]);
        }

        $workFlowData['title'] = $title;
        $workflow->fill($workFlowData)->save();


        $actions = $request->get('actions', []);
        WorkflowHelper::syncActions($workflow_id, $actions);

        return [
            'message'  => __('Workflow has been updated', 'fluent-support-pro'),
            'workflow' => $workflow,
            'actions'  => WorkflowAction::where('workflow_id', $workflow->id)->get()
        ];
    }

    public function getOptions()
    {
        $manualWorkflows = Workflow::select(['id', 'title'])
            ->where('trigger_type', 'manual')
            ->where('status', 'published')
            ->get();

        return [
            'option_key' => 'manual_workflows',
            'options' => $manualWorkflows
        ];
    }

    public function getWorkflowActions(Request $request, $workflow_id)
    {
        $workflow = Workflow::findOrFail($workflow_id);

        $actions = WorkflowAction::select(['id', 'title', 'action_name'])->where('workflow_id', $workflow->id)->get();

        return [
            'actions' => $actions
        ];
    }

    public function runWorkFlow(Request $request, $workflow_id)
    {
        $ticketIds = array_filter(array_filter($request->get('ticket_ids', []), 'absint'));

        if (!$ticketIds) {
            return $this->sendError([
                'message' => 'No ticket found'
            ]);
        }

        $workflow = Workflow::findOrFail($workflow_id);

        if ($workflow->status != 'published' || $workflow->trigger_type != 'manual') {
            return $this->sendError([
                'message' => 'The selected workflow needs to be published and trigger type manual'
            ]);
        }

        $actions = WorkflowAction::where('workflow_id', $workflow->id)->get();

        if ($actions->isEmpty()) {
            return $this->sendError([
                'message' => 'No Actions found for this workflow'
            ]);
        }

        $ticketsQuery = Ticket::whereIn('id', $ticketIds);

        do_action_ref_array('fluent_support/tickets_query_by_permission_ref', [&$ticketsQuery, false]);

        $tickets = $ticketsQuery->get();

        if ($tickets->isEmpty()) {
            return $this->sendError([
                'message' => 'No tickets found based on your permission for this workflow'
            ]);
        }

        $didRun = false;
        foreach ($tickets as $ticket) {
            $result = (new WorkflowRunner($workflow, $ticket, $actions))->runActions();
            if ($result) {
                $didRun = true;
            }
        }

        if (!$didRun) {
            return $this->sendError([
                'message' => 'Actions could not be executed'
            ]);
        }

        return [
            'message' => 'Selected workflow actions has been successfully applied'
        ];

    }

    public function deleteWorkflow(Request $request, $workflow_id)
    {
        Workflow::where('id', $workflow_id)->delete();
        WorkflowAction::where('workflow_id', $workflow_id)->delete();

        return [
            'message' => __('Selected workflow has been deleted', 'fluent-support-pro')
        ];
    }

    public function duplicateWorkflow(Request $request)
    {
        $workflowId = $request->getSafe('workflow_id', 'intval');

        $workflow = Workflow::findOrFail($workflowId);

        $newTitle = WorkflowHelper::generateUniqueTitle($workflow->title);

        $newWorkflow = $workflow->replicate();
        $newWorkflow->title = $newTitle;
        $newWorkflow->status = 'draft';
        $newWorkflow->save();

        WorkflowHelper::duplicateActions($workflow, $newWorkflow);

        return __('Selected workflow has been duplicated');
    }

    public function updateActionSequence(Request $request)
    {
        $workflowId = $request->getSafe('workflow_id', 'intval');
        $actionSequence = $request->getSafe('action_sequence', []);

        WorkflowHelper::updateActionSequence($workflowId, $actionSequence);

        return [
            'message' => __('Action sequence has been updated', 'fluent-support-pro')
        ];

    }

}
