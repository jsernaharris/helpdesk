<?php

namespace App\Services\AI;

use App\Models\KbArticle;
use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketReplyAssistant
{
    public function __construct(private readonly AzureFoundryClient $client)
    {
    }

    public function draft(Ticket $ticket): string
    {
        $ticket->loadMissing(['threads.user', 'threads.contact', 'requester', 'organization']);

        $kbContext = $this->relevantKbContext($ticket);
        $thread = $this->renderThread($ticket);

        $system = <<<TXT
You are a helpdesk agent drafting replies on behalf of a support engineer.
Write a clear, professional reply addressed to the requester. Acknowledge the
issue, summarise the proposed next step, and ask for any missing information.
If the knowledge base context below contains a relevant answer, lean on it.
Do not invent product features or commitments. Output only the reply body —
no subject line, no greeting like "Hi {name}" unless natural, no sign-off
beyond "Thanks,\\n{Agent}".
TXT;

        $user = <<<TXT
Ticket #{$ticket->ticket_number}
Subject: {$ticket->subject}
Priority: {$ticket->priority}
Status: {$ticket->status}
Organization: {$ticket->organization?->name}
Requester: {$ticket->requester?->name}

Description:
{$ticket->description}

Conversation so far:
{$thread}

Knowledge base context:
{$kbContext}
TXT;

        return $this->client->chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            [
                'temperature' => config('ai.reply_assistant.temperature'),
                'max_tokens' => config('ai.reply_assistant.max_tokens'),
            ],
        );
    }

    private function renderThread(Ticket $ticket): string
    {
        $messages = $ticket->threads
            ->where('is_internal', false)
            ->take(-config('ai.reply_assistant.max_thread_messages'));

        if ($messages->isEmpty()) {
            return '(no replies yet)';
        }

        return $messages->map(function ($t) {
            $who = $t->user?->name ?? $t->contact?->name ?? 'System';
            $when = $t->created_at?->toDateTimeString();
            $body = Str::limit(strip_tags((string) $t->body), 800);
            return "[{$when}] {$who}:\n{$body}";
        })->implode("\n\n");
    }

    private function relevantKbContext(Ticket $ticket): string
    {
        $articles = $this->findRelevantArticles($ticket);

        if ($articles->isEmpty()) {
            return '(no matching articles)';
        }

        return $articles->map(function (KbArticle $a) {
            $body = Str::limit(strip_tags((string) $a->content), 700);
            return "### {$a->title}\n{$body}";
        })->implode("\n\n");
    }

    /** @return \Illuminate\Support\Collection<int, KbArticle> */
    private function findRelevantArticles(Ticket $ticket)
    {
        $keywords = collect(preg_split('/\W+/', $ticket->subject.' '.$ticket->description))
            ->filter(fn ($w) => strlen((string) $w) >= 4)
            ->unique()
            ->take(8)
            ->values();

        if ($keywords->isEmpty()) {
            return collect();
        }

        $query = KbArticle::published()
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'like', "%{$kw}%")
                      ->orWhere('content', 'like', "%{$kw}%");
                }
            });

        return $query->limit(config('ai.reply_assistant.max_kb_articles'))->get();
    }
}
