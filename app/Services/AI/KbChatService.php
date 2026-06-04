<?php

namespace App\Services\AI;

use App\Models\KbArticle;
use App\Models\User;
use Illuminate\Support\Str;

class KbChatService
{
    public function __construct(private readonly AzureFoundryClient $client)
    {
    }

    /**
     * @return array{answer: string, citations: array<int, array{id: int, title: string, slug: string, category_slug: ?string}>}
     */
    public function answer(string $question, ?User $user = null): array
    {
        $articles = $this->findRelevantArticles($question, $user)->load('category');

        $context = $articles->isEmpty()
            ? '(no matching articles found)'
            : $articles->map(function (KbArticle $a, int $i) {
                $body = Str::limit(strip_tags((string) $a->content), 1200);
                $n = $i + 1;
                return "[{$n}] {$a->title}\n{$body}";
            })->values()->implode("\n\n");

        $system = <<<TXT
You are a customer-facing knowledge base assistant. Answer the user's question
using ONLY the knowledge base articles provided. If the answer is not in the
articles, say "I couldn't find that in our knowledge base — please submit a
ticket and our team will help." Keep answers concise (under 200 words). When
you use information from an article, cite it inline using [1], [2], etc.
matching the article numbers below. Never invent product behavior.
TXT;

        $userMsg = "Knowledge base articles:\n{$context}\n\nQuestion: {$question}";

        $answer = $this->client->chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $userMsg],
            ],
            [
                'temperature' => config('ai.kb_chat.temperature'),
                'max_tokens' => config('ai.kb_chat.max_tokens'),
            ],
        );

        $citations = $articles->map(fn (KbArticle $a) => [
            'id' => $a->id,
            'title' => $a->title,
            'slug' => $a->slug,
            'category_slug' => $a->category?->slug,
        ])->values()->all();

        return ['answer' => $answer, 'citations' => $citations];
    }

    /** @return \Illuminate\Support\Collection<int, KbArticle> */
    private function findRelevantArticles(string $question, ?User $user)
    {
        $keywords = collect(preg_split('/\W+/', $question))
            ->filter(fn ($w) => strlen((string) $w) >= 4)
            ->unique()
            ->take(8)
            ->values();

        $query = KbArticle::published()->visibleTo($user);

        if ($keywords->isNotEmpty()) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'like', "%{$kw}%")
                      ->orWhere('content', 'like', "%{$kw}%");
                }
            });
        }

        return $query->limit(config('ai.kb_chat.max_kb_articles'))->get();
    }
}
