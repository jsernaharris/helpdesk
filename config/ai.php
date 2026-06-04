<?php

return [
    /*
     * Azure AI Foundry connection.
     *
     * Foundry deployments expose an OpenAI-compatible chat completions
     * endpoint of the form:
     *   {endpoint}/openai/deployments/{deployment}/chat/completions?api-version={api_version}
     *
     * For models served through Foundry's "Models as a Service" (e.g. Claude,
     * Llama) the path may differ — override `chat_path` if needed.
     */
    'foundry' => [
        'endpoint' => env('AZURE_AI_FOUNDRY_ENDPOINT'),
        'api_key' => env('AZURE_AI_FOUNDRY_KEY'),
        'deployment' => env('AZURE_AI_FOUNDRY_DEPLOYMENT'),
        'api_version' => env('AZURE_AI_FOUNDRY_API_VERSION', '2024-10-21'),
        'chat_path' => env('AZURE_AI_FOUNDRY_CHAT_PATH'),
        'timeout' => (int) env('AZURE_AI_FOUNDRY_TIMEOUT', 30),
    ],

    'reply_assistant' => [
        'max_kb_articles' => 3,
        'max_thread_messages' => 10,
        'temperature' => 0.4,
        'max_tokens' => 700,
    ],

    'triage' => [
        'temperature' => 0.1,
        'max_tokens' => 400,
    ],

    'kb_chat' => [
        'max_kb_articles' => 4,
        'temperature' => 0.3,
        'max_tokens' => 600,
    ],
];
