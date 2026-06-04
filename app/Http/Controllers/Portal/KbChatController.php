<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\AI\AzureFoundryClient;
use App\Services\AI\KbChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class KbChatController extends Controller
{
    public function ask(
        Request $request,
        KbChatService $chat,
        AzureFoundryClient $client,
    ): JsonResponse {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if (! $client->isConfigured()) {
            return response()->json(['error' => 'AI is not configured.'], 503);
        }

        try {
            return response()->json($chat->answer($data['question'], $request->user()));
        } catch (Throwable $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
