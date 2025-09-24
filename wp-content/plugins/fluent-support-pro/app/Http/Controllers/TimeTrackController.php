<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Models\Agent;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\Framework\Request\Request;
use FluentSupportPro\App\Services\ProTicketService;
use FluentSupportPro\App\Models\TimeTrack;

class TimeTrackController extends Controller
{
    public function updateEstimatedTime(Request $request, ProTicketService $proTicketService, $ticket_id)
    {
        $estimatedTime = $request->getSafe('estimated_minutes', 'intval');
        $ticketID = $request->getSafe('ticket_id', 'intval');

        return $proTicketService->updateEstimatedTime($ticketID, $estimatedTime);
    }


    public function manualCommitTrack(Request $request, $ticketID)
    {
        $mailboxID = Ticket::where('id', $ticketID)->value('mailbox_id');

        if (!$mailboxID) {
            return $this->sendError([
                'message' => __('Ticket not found', 'fluent-support-pro')
            ]);
        }

        $agentID = Agent::where('user_id', get_current_user_id())->value('id');

        $data = [
            'status' => 'commited',
            'agent_id' => $agentID,
            'customer_id' => $request->getSafe('customer_id', 'intval'),
            'completed_at' => current_time('mysql'),
            'billable_minutes' => $request->getSafe('billable_minutes', 'intval'),
            'working_minutes' => $request->getSafe('billable_minutes', 'intval'),
            'message' => $request->getSafe('message', 'wp_kses_post'),
            'ticket_id' => (int) $ticketID,
            'mailbox_id' => $mailboxID,
            'is_manual' => 1,
        ];

        if ($data['billable_minutes'] <= 0) {
            return $this->sendError([
                'message' => __('Please provide valid hours and minutes', 'fluent-boards-pro')
            ]);
        }

        $track = TimeTrack::create($data);

        $track->load('agent');

        return [
            'track' => $track,
            'message' => __('You have successfully submitted your working time', 'fluent-boards-pro')
        ];
    }

    public function getTracks(Request $request, ProTicketService $proTicketService, $ticketID)
    {
        $tracks = TimeTrack::where('ticket_id', $ticketID)
            ->orderBy('updated_at', 'DESC')
            ->with(['agent'])
            ->get();

        return [
            'tracks'            => $tracks,
            'estimated_minutes' => $proTicketService->getEstimatedTime($ticketID),
        ];
    }

}
