<?php

namespace App\Services\AI;

use App\Models\Ticket;
use RuntimeException;

class TicketTriageService
{
    public function __construct(private readonly AzureFoundryClient $client)
    {
    }

    /**
     * @return array{priority: string, urgency: string, impact: string, type: string, summary: string, suggested_tags: array<int, string>, recommended_actions: array<int, string>}
     */
    public function triage(Ticket $ticket): array
    {
        $system = <<<TXT
You are an ITIL triage classifier for a managed service provider. Read the
ticket and return ONLY a JSON object with these exact keys:
  priority         one of: low, medium, high, critical
  urgency          one of: low, medium, high
  impact           one of: low, medium, high
  type             one of: incident, service_request, problem, change
  summary          1-2 sentence plain-English summary
  suggested_tags   array of up to 5 short tag strings
  recommended_actions  array of up to 4 short next-step recommendations
Do not include any commentary outside the JSON.
TXT;

        $user = "Subject: {$ticket->subject}\n\nDescription:\n{$ticket->description}";

        $raw = $this->client->chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            [
                'temperature' => config('ai.triage.temperature'),
                'max_tokens' => config('ai.triage.max_tokens'),
                'response_format' => ['type' => 'json_object'],
            ],
        );

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Triage response was not valid JSON: '.$raw);
        }

        return [
            'priority' => $this->pick($decoded, 'priority', ['low', 'medium', 'high', 'critical'], 'medium'),
            'urgency' => $this->pick($decoded, 'urgency', ['low', 'medium', 'high'], 'medium'),
            'impact' => $this->pick($decoded, 'impact', ['low', 'medium', 'high'], 'medium'),
            'type' => $this->pick($decoded, 'type', ['incident', 'service_request', 'problem', 'change'], 'incident'),
            'summary' => (string) ($decoded['summary'] ?? ''),
            'suggested_tags' => array_values(array_filter(array_map('strval', (array) ($decoded['suggested_tags'] ?? [])))),
            'recommended_actions' => array_values(array_filter(array_map('strval', (array) ($decoded['recommended_actions'] ?? [])))),
        ];
    }

    private function pick(array $data, string $key, array $allowed, string $default): string
    {
        $value = strtolower((string) ($data[$key] ?? ''));
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
