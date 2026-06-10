<?php

namespace App\Services\Mail;

use App\Models\EmailMailbox;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin Microsoft Graph wrapper using the OAuth2 client-credentials (app-only)
 * flow. Credentials live per-mailbox in the DB vault; access tokens are cached
 * (never persisted) until shortly before they expire.
 */
class GraphClient
{
    public function __construct(private EmailMailbox $mailbox)
    {
        if (! filled($mailbox->graph_tenant_id) || ! filled($mailbox->graph_client_id)
            || ! filled($mailbox->graph_client_secret) || ! filled($mailbox->graph_user_id)) {
            throw new RuntimeException("Mailbox '{$mailbox->name}' is missing Microsoft Graph credentials.");
        }
    }

    /**
     * Acquire (or reuse a cached) app-only access token for this mailbox.
     */
    public function token(): string
    {
        return Cache::remember(
            "graph_token:mailbox:{$this->mailbox->id}",
            now()->addMinutes(50),
            fn () => $this->requestToken(),
        );
    }

    private function requestToken(): string
    {
        $authority = rtrim((string) config('services.microsoft_graph.authority', 'https://login.microsoftonline.com'), '/');
        $url = "{$authority}/{$this->mailbox->graph_tenant_id}/oauth2/v2.0/token";

        $response = Http::asForm()
            ->timeout($this->timeout())
            ->post($url, [
                'client_id' => $this->mailbox->graph_client_id,
                'client_secret' => $this->mailbox->graph_client_secret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Microsoft Graph token request failed ({$response->status()}): ".$response->body()
            );
        }

        $token = data_get($response->json(), 'access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Microsoft Graph returned an empty access token.');
        }

        return $token;
    }

    /**
     * GET a Graph resource path (relative to the Graph base URL) and return decoded JSON.
     *
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $response = $this->request()->get($this->base().$path, $query);

        if ($response->failed()) {
            throw new RuntimeException("Microsoft Graph GET {$path} failed ({$response->status()}): ".$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * PATCH a Graph resource path with a JSON body.
     */
    public function patch(string $path, array $body): void
    {
        $response = $this->request()->patch($this->base().$path, $body);

        if ($response->failed()) {
            throw new RuntimeException("Microsoft Graph PATCH {$path} failed ({$response->status()}): ".$response->body());
        }
    }

    /**
     * POST a Graph resource path with a JSON body. Returns the decoded response (may be empty).
     *
     * @return array<string, mixed>
     */
    public function post(string $path, array $body): array
    {
        $response = $this->request()->post($this->base().$path, $body);

        if ($response->failed()) {
            throw new RuntimeException("Microsoft Graph POST {$path} failed ({$response->status()}): ".$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * The /users/{id} segment for this mailbox's shared mailbox.
     */
    public function userPath(): string
    {
        return '/users/'.rawurlencode((string) $this->mailbox->graph_user_id);
    }

    private function request()
    {
        return Http::withToken($this->token())
            ->acceptJson()
            ->timeout($this->timeout());
    }

    private function base(): string
    {
        return rtrim((string) config('services.microsoft_graph.base_url', 'https://graph.microsoft.com/v1.0'), '/');
    }

    private function timeout(): int
    {
        return (int) config('services.microsoft_graph.timeout', 30);
    }
}
