<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\AI\AzureFoundryClient;
use App\Services\AI\TicketReplyAssistant;
use App\Services\AI\TicketTriageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class TicketAiController extends Controller
{
    public function suggestReply(
        Ticket $ticket,
        TicketReplyAssistant $assistant,
        AzureFoundryClient $client,
    ): JsonResponse {
        if (! $client->isConfigured()) {
            return response()->json(['error' => 'AI is not configured.'], 503);
        }

        try {
            return response()->json(['reply' => $assistant->draft($ticket)]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function triage(
        Ticket $ticket,
        TicketTriageService $triage,
        AzureFoundryClient $client,
    ): JsonResponse {
        if (! $client->isConfigured()) {
            return response()->json(['error' => 'AI is not configured.'], 503);
        }

        try {
            return response()->json($triage->triage($ticket));
        } catch (Throwable $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
