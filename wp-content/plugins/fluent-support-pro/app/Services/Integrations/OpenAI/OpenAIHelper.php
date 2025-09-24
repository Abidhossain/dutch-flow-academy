<?php

namespace FluentSupportPro\App\Services\Integrations\OpenAI;

use FluentSupportPro\App\Services\Integrations\OpenAI\OpenAIAPI;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\AIActivityLogs;
use FluentSupport\Framework\Support\Arr;

class OpenAIHelper
{
    const BASE_PROMPT_TOKENS = 1000;

    public function modifyResponse($prompt, $selectedText, $ticketId)
    {
        $prompt = apply_filters('fluent_support/modify_selected_text', $prompt);
        $query = $prompt . " " . $selectedText;

        return  $this->generateAPIResponse($query, $prompt, $ticketId);
    }

    public function generateResponse($prompt, $ticket)
    {
        $filteredPrompt = apply_filters('fluent_support/generate_response', $prompt, $ticket);

        $ticketData = $this->preProcessTicket($filteredPrompt, $ticket);
        $query = $filteredPrompt . ' ' . $ticketData;

        return $this->generateAPIResponse($query, $filteredPrompt, $ticket->id);
    }


    public function generateTicketSummary($ticket)
    {
        $prompt = 'Provide a summary of the ticket from the customer\'s perspective. Each step should start with "-". Break it down into concise steps, with a maximum of 6 steps. Each step should be within 6 words per line. Use full stops for separation.';
        $prompt = apply_filters('fluent_support/generate_ticket_summary', $prompt);
        $ticketData = $this->preProcessTicket($prompt, $ticket);
        $query = $prompt . " " . $ticketData;

        return $this->generateAPIResponse($query, $prompt, $ticket->id);
    }

    public function generateTicketTone($ticket)
    {
        $prompt = 'What is the tone of this ticket? Is it positive, negative, or neutral? Provide a response with a single word.';
        $prompt = apply_filters('fluent_support/find_customer_sentiment', $prompt);
        $ticketData = $this->preProcessTicket($prompt, $ticket);
        $query = $prompt . " " . $ticketData;

        return $this->generateAPIResponse($query, $prompt, $ticket->id);
    }

    private function generateAPIResponse($query, $prompt = null, $ticketId = null)
    {
        $args = [
            "role" => 'system',
            "content" => $query,
        ];

        $data = $this->metaData();
        $chatAPI = new OpenAIAPI($data['api_key'], $data['model']);

        return $chatAPI->makeRequest($prompt, $ticketId, $args);
    }

    private function countTokens($string)
    {
        return strlen($string) / 4;
    }

    private function preProcessTicket($prompt, $ticket)
    {
        $ticketArray = $ticket->toArray();
        $formattedTicket = $this->formatTicket($ticketArray);

        $responseContentTokens = $this->countTokens($prompt);
        $ticketDataTokens = $this->countTokens(json_encode($formattedTicket));

        $availableTokens = self::BASE_PROMPT_TOKENS - $responseContentTokens - $ticketDataTokens;

        if ($availableTokens < 0) {
            $formattedTicket['responses'] = $this->truncateResponses($formattedTicket['responses'], abs($availableTokens));
        }

        return json_encode($formattedTicket);
    }

    private function formatTicket($ticketArray): array
    {
        return [
            'title'               => sanitize_text_field($ticketArray['title']),
            'content'             => wp_strip_all_tags($ticketArray['content']),
            'responses'           => is_array($ticketArray['responses']) ? array_map(function($response) {
                return [
                    'content'       => wp_strip_all_tags($response['content'] ?? ''),
                    'created_at'    => $response['created_at'] ?? '',
                    'person'        => [
                        'full_name'    => $response['person']['full_name'] ?? 'Unknown',
                        'email'        => $response['person']['email'] ?? 'Unknown',
                    ],
                ];
            }, $ticketArray['responses']) : [],
            'status'              => $ticketArray['status'],
            'priority'            => $ticketArray['priority'],
            'created_at'          => date('Y-m-d H:i:s', strtotime($ticketArray['created_at'])),
            'updated_at'          => date('Y-m-d H:i:s', strtotime($ticketArray['updated_at'])),
            'waiting_since'       => date('Y-m-d H:i:s', strtotime($ticketArray['waiting_since'])),
            'last_agent_response' => $ticketArray['last_agent_response'],
        ];
    }

    private function truncateResponses($responses, $availableTokens)
    {
        // Sort the responses by creation date in descending order to keep the most recent responses
        usort($responses, function($firstResponse, $secondResponse) {
            $firstTimestamp = strtotime($firstResponse['created_at']);
            $secondTimestamp = strtotime($secondResponse['created_at']);

            return $secondTimestamp - $firstTimestamp;
        });


        $truncatedResponses = [];
        $totalTokens = 0;

        foreach ($responses as $response) {
            $responseTokens = $this->countTokens($response['content']);
            if ($totalTokens + $responseTokens > $availableTokens) {
                break;
            }
            $truncatedResponses[] = $response;
            $totalTokens += $responseTokens;
        }

        return $truncatedResponses;
    }

    private function metaData()
    {
        $metaValue = Meta::where('object_type', '_fs_openai_settings')->value('value');
        return maybe_unserialize($metaValue);
    }

    /**
     * Get preset prompts based on the type.
     *
     * @param string $type The type of prompts to retrieve.
     * @return array An array of preset prompts.
     */
    public function getPresetPrompts(string $type): array
    {
        switch ($type) {
            case 'modifyResponse':
                return $this->getModifyResponsePresets();
            case 'createResponse':
                return $this->getCreateResponsePresets();
            default:
                return [];
        }
    }

    /**
     * Get preset prompts for modifying responses.
     *
     * @return array An array of modify response preset prompts.
     */
    private function getModifyResponsePresets(): array
    {
        $presetPrompts = [
            [
                'label' => 'Improve Writing',
                'text' => 'shorten',
                'description' => 'Use AI to refine the text by removing unnecessary words and making it more concise while retaining the original meaning and key information.'
            ],
            [
                'label' => 'Fix Spelling & Grammar',
                'text' => 'lengthen',
                'description' => 'Apply AI to correct any spelling and grammatical errors in the text, ensuring it is free of mistakes and reads professionally.'
            ],
            [
                'label' => 'Make Shorter',
                'text' => 'friendly',
                'description' => 'AI will modify the text to make it shorter and more casual, making it suitable for informal or friendly communication.'
            ],
            [
                'label' => 'Make Longer',
                'text' => 'professional',
                'description' => 'Enhance the text by adding more details and using refined language to make it more formal and detailed, appropriate for professional settings.'
            ],
            [
                'label' => 'Simplify Language',
                'text' => 'simplify',
                'description' => 'Utilize AI to simplify complex phrases and terminology, making the text easier to read and understand for a general audience.'
            ]
        ];

        return apply_filters('fluent_support/get_modify_response_preset_prompts', $presetPrompts);
    }

    /**
     * Get preset prompts for creating responses.
     *
     * @return array An array of create response preset prompts.
     */
    private function getCreateResponsePresets(): array
    {
        $presetPrompts = [
            [
                'label' => 'Request More Information',
                'text' => 'requestInfo',
                'description' => 'Ask the customer to provide additional details or clarification about the issue they reported. This helps in gathering more information to resolve the issue effectively.'
            ],
            [
                'label' => 'Acknowledge Issue',
                'text' => 'acknowledgeIssue',
                'description' => 'Confirm receipt of the customer’s issue and reassure them that it is being investigated. This demonstrates that their concern is being taken seriously.'
            ],
            [
                'label' => 'Provide Solution',
                'text' => 'provideSolution',
                'description' => 'Offer a comprehensive solution or resolution to the problem described by the customer. This should address their concerns and provide actionable steps to resolve the issue.'
            ],
            [
                'label' => 'Follow Up',
                'text' => 'followUp',
                'description' => 'Reach out to the customer after a solution has been provided to ensure that their issue has been resolved to their satisfaction. This helps in confirming the resolution and maintaining good customer relations.'
            ],
            [
                'label' => 'Close Ticket',
                'text' => 'closeTicket',
                'description' => 'Notify the customer that their ticket will be closed as the issue has been resolved. Ensure that all their concerns are addressed before closing the ticket.'
            ]
        ];

        return apply_filters('fluent_support/get_create_response_preset_prompts', $presetPrompts);
    }

}
