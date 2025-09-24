<?php

namespace FluentSupport\App\Http\Controllers;


use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\Integrations\FluentBot\FluentBotService;

class FluentBotController extends Controller
{
    public function getPresetPrompts(Request $request)
    {
        $type = $request->getSafe('type', 'sanitize_text_field');

        try {
            return (new FluentBotService())->getPresetPrompts($type);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function generateResponse(Request $request)
    {
        $ticketId = $request->getSafe('id', 'intval');
        $productId = $request->getSafe('product_id', 'intval');
        $prompt = $request->getSafe('content', 'sanitize_text_field');
        $previousAIResponse = $request->getSafe('previous_response', 'sanitize_text_field', '');
        $selectedText = $request->getSafe('selectedText', 'sanitize_text_field', '');
        $type = $request->getSafe('type', 'sanitize_text_field', 'response');

        try {
            $customAI = new FluentBotService();

            if ($type === 'modifyResponse') {
                $result = $customAI->modifyResponse($prompt, $selectedText, $ticketId);
            } else {
                $ticket = Ticket::with('responses')->findOrFail($ticketId);
                $result = $customAI->generateResponse($prompt, $ticket, $productId, $previousAIResponse);
            }

            return $result;
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTicketSummary(Request $request)
    {
        $ticketId = $request->getSafe('id', 'intval');
        $ticket = Ticket::with('responses')->findOrFail($ticketId);

        try {
            return (new FluentBotService())->getTicketSummary($ticket);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTicketTone(Request $request)
    {
        $ticketId = $request->getSafe('id', 'intval');
        $ticket = Ticket::with('responses')->findOrFail($ticketId);

        try {
            return (new FluentBotService())->getTicketTone($ticket);
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
        }
    }

}
