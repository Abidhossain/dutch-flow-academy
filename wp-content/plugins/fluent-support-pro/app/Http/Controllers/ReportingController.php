<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\Framework\Request\Request;
use FluentSupportPro\App\Services\ProHelper;

class ReportingController extends Controller
{
    public function getTimesheetByTickets(Request $request)
    {
        $mailBoxId = $request->get('mailbox_id');
        $dateRange = ProHelper::getValidatedDateRange($request->get('date_range', []));

        $tracks = ProHelper::getTracks($dateRange, 'mailbox_id', $mailBoxId, ['agent', 'ticket:id,title,slug']);

        $timeSheets = ProHelper::formatTimeSheets($tracks, 'ticket_id');
        $totalMinutes = ProHelper::calculateTotalMinutes($tracks);
        $dateLabels = ProHelper::generateDateLabels($dateRange);
        $mailBoxes = ProHelper::getMailBoxes();

        return [
            'tickets' => array_values($timeSheets['formattedItems']),
            'mail_boxes' => $mailBoxes,
            'date_labels' => $dateLabels,
            'total_minutes' => $totalMinutes,
            'time_sheets' => $timeSheets['sheets'],
            'date_range' => $dateRange
        ];
    }

    public function getTimesheetByAgents(Request $request)
    {
        $agentId = $request->get('agent_id');
        $dateRange = ProHelper::getValidatedDateRange($request->get('date_range', []));

        $tracks = ProHelper::getTracks($dateRange, 'agent_id', $agentId, ['agent', 'ticket:id,title,slug']);

        $timeSheets = ProHelper::formatTimeSheets($tracks, 'agent_id');
        $totalMinutes = ProHelper::calculateTotalMinutes($tracks);
        $dateLabels = ProHelper::generateDateLabels($dateRange);
        $allAgents = ProHelper::getAllAgents();

        return [
            'agents' => array_values($timeSheets['formattedItems']),
            'all_agents' => $allAgents,
            'date_labels' => $dateLabels,
            'total_minutes' => $totalMinutes,
            'time_sheets' => $timeSheets['sheets'],
            'date_range' => $dateRange
        ];
    }

    public function getTimesheetByCustomers(Request $request)
    {
        $customerId = $request->get('customer_id');
        $dateRange = ProHelper::getValidatedDateRange($request->get('date_range', []));

        $tracks = ProHelper::getTracks($dateRange, 'customer_id', $customerId, ['customer', 'ticket:id,title,slug']);

        $timeSheets = ProHelper::formatTimeSheets($tracks, 'customer_id');
        $totalMinutes = ProHelper::calculateTotalMinutes($tracks);
        $dateLabels = ProHelper::generateDateLabels($dateRange);
        $allCustomers = ProHelper::getAllCustomers();

        return [
            'customers' => array_values($timeSheets['formattedItems']),
            'all_customers' => $allCustomers,
            'date_labels' => $dateLabels,
            'total_minutes' => $totalMinutes,
            'time_sheets' => $timeSheets['sheets'],
            'date_range' => $dateRange
        ];
    }
}
