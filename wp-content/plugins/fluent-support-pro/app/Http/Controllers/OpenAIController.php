<?php

namespace FluentSupportPro\App\Http\Controllers;


use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupportPro\App\Services\Integrations\OpenAI\OpenAIService;

class OpenAIController extends Controller
{
    public function getPresetPrompts(Request $request, OpenAIService $openAIService)
    {
        $type = $request->getSafe('type', 'sanitize_text_field');
        $result = $openAIService->getPresetPrompts($type);

        if (is_wp_error($result)) {
            return $this->sendError($result->get_error_message());
        }

        return $result;
    }

    public function generateResponse(Request $request, OpenAIService $openAIService)
    {
        $ticketId = $request->getSafe('id', 'intval');
        $prompt = $request->getSafe('content', 'sanitize_text_field');
        $selectedText = $request->getSafe('selectedText', 'sanitize_text_field', '');
        $type = $request->getSafe('type', 'sanitize_text_field', 'response');

        if ($type === 'modifyResponse') {
            $result = $openAIService->modifyResponse($prompt, $selectedText, $ticketId);
        } else {
            $ticket = Ticket::with('responses')->findOrFail($ticketId);
            $result = $openAIService->generateResponse($prompt, $ticket);
        }

        if (is_wp_error($result)) {
            return $this->sendError($result->get_error_message());
        }

        return $result;
    }

    public function getTicketSummary(Request $request, OpenAIService $openAIService)
    {
        $ticketId = $request->getSafe('id', 'intval');
        $ticket = Ticket::with('responses')->findOrFail($ticketId);
        $summary = $openAIService->getTicketSummary($ticket);

        if (is_wp_error($summary)) {
            return $this->sendError($summary->get_error_message());
        }

        return $summary;
    }

    public function getTicketTone(Request $request, OpenAIService $openAIService)
    {
        $ticketId = $request->getSafe('id', 'intval');
        $ticket = Ticket::with('responses')->findOrFail($ticketId);
        $tone = $openAIService->getTicketTone($ticket);

        if (is_wp_error($tone)) {
            return $this->sendError($tone->get_error_message());
        }

        return $tone;
    }

}
