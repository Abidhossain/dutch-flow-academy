<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\Framework\Request\Request;
use FluentSupportPro\App\Services\ProTicketService;
use FluentSupportPro\App\Services\TicketBookmarkService;

class TicketController extends Controller
{
    /**
     * getCustomerTickets method will return customer tickets by customer id
     * @param Request $request
     * @param int $customer_id
     * @return array|array[]
     */
    public function getCustomerTickets(Request $request, $customer_id)
    {
        $tickets = Ticket::where('customer_id', $customer_id)
            ->where('id', '!=', $request->get('exclude_ticket_id'))
            ->latest()
            ->paginate();

        return [
            'tickets' => $tickets
        ];
    }


    /**
     * mergeCustomerTickets will merge tickets into one
     * @param Request $request
     * @param $ticket_id //ticket id where the tickets will be merged
     * @return array|array[]
     */
    public function mergeCustomerTickets(Request $request, $ticket_id)
    {
        if (!PermissionManager::currentUserCan('fst_merge_tickets')) {
            return $this->sendError([
                'message' => __('You do not have permission to merge tickets', 'fluent-support-pro')
            ]);
        }

        $ticketIDToMerge = $request->get('ticket_to_merge');
        return (new ProTicketService())->mergeCustomerTickets($ticketIDToMerge, $ticket_id);
    }

    public function syncTicketWatchers(Request $request, $ticket_id)
    {
        $watchers = $request->get('watchers', []);
        $agentIds = [];
        foreach($watchers as $watcher){
            is_array($watcher) ? $agentIds[] = $watcher['id'] : $agentIds[] = $watcher;
        }

        return (new ProTicketService())->syncTicketWatchers($agentIds, $ticket_id);
    }

    public function addTicketWatchers(Request $request, TicketBookmarkService $bookmarkService, $ticket_id)
    {
        $watchers = $request->get('watchers', []);

        if( ! $watchers ){
            return $this->sendError([
                'message' => __('Watchers is required', 'fluent-support-pro')
            ]);
        }

        $bookmarkService->addBookmarks( $watchers, $ticket_id );

        return [
            'message' => __('Watchers has been added to this ticket', 'fluent-support-pro'),
        ];
    }

    public function splitToNewTicket ( Request $request, ProTicketService $proTicketService, $ticket_id )
    {
        $newTicketData = $request->get('split_ticket');
        $sanitizeRules = [
            'conversation_id' => 'intval',
            'customer_id' => 'intval',
            'mailbox_id' => 'intval',
            'title' => 'sanitize_text_field',
            'content' => 'wp_kses_post',
            'client_priority' => 'sanitize_text_field',
            'create_customer' => 'sanitize_text_field',
            'create_wp_user' => 'sanitize_text_field',
        ];

        if( $newTicketData && is_array($newTicketData) ) {
            foreach ($newTicketData as $dataKey => $dataItem) {
                $sanitizeFunc = isset($sanitizeRules[$dataKey]) ? $sanitizeRules[$dataKey]: 'sanitize_text_field';
                if(is_array($dataItem)) {
                    $newTicketData[$dataKey] = map_deep($dataItem, $sanitizeFunc);
                } else {
                    $newTicketData[$dataKey] = $sanitizeFunc($dataItem);
                }
            }
        }

        try {
            return $proTicketService->splitToNewTicket( $ticket_id, $newTicketData );
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
        }
    }
}
