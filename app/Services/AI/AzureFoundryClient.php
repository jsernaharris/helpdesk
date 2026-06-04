<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AzureFoundryClient
{
    public function isConfigured(): bool
    {
        return filled(config('ai.foundry.endpoint'))
            && filled(config('ai.foundry.api_key'))
            && filled(config('ai.foundry.deployment'));
    }

    /**
     * Send a chat completion request to Azure AI Foundry.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options  temperature, max_tokens, response_format, etc.
     * @return string  assistant message content
     */
    public function chat(array $messages, array $options = []): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Azure AI Foundry is not configured. Set AZURE_AI_FOUNDRY_* env vars.');
        }

        $payload = array_merge([
            'messages' => $messages,
            'temperature' => 0.4,
            'max_tokens' => 600,
        ], $options);

        $response = Http::withHeaders([
                'api-key' => config('ai.foundry.api_key'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(config('ai.foundry.timeout', 30))
            ->post($this->url(), $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "Azure AI Foundry request failed ({$response->status()}): ".$response->body()
            );
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Azure AI Foundry returned an empty response.');
        }

        return $content;
    }

    private function url(): string
    {
        $endpoint = rtrim((string) config('ai.foundry.endpoint'), '/');
        $deployment = config('ai.foundry.deployment');
        $apiVersion = config('ai.foundry.api_version');
        $path = config('ai.foundry.chat_path')
            ?: "/openai/deployments/{$deployment}/chat/completions";

        return $endpoint.$path.'?api-version='.$apiVersion;
    }
}
