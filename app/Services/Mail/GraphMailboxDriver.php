<?php

namespace App\Services\Mail;

use App\Models\EmailMailbox;

/**
 * Reads and sends mail through Microsoft Graph using app-only auth. Suitable for
 * Microsoft 365 shared mailboxes, which have no password for IMAP/SMTP.
 *
 * Requires an Azure app registration with the Application permissions
 * Mail.ReadWrite and Mail.Send, admin-consented, ideally scoped to the target
 * mailbox via an Application Access Policy.
 */
class GraphMailboxDriver implements MailboxDriver
{
    public function fetchUnread(EmailMailbox $mailbox): iterable
    {
        $graph = new GraphClient($mailbox);

        $result = $graph->get($graph->userPath().'/mailFolders/inbox/messages', [
            '$filter' => 'isRead eq false',
            '$top' => 50,
            '$select' => 'id,subject,from,body,internetMessageId,hasAttachments,internetMessageHeaders',
        ]);

        foreach ($result['value'] ?? [] as $message) {
            $headers = collect($message['internetMessageHeaders'] ?? [])
                ->mapWithKeys(fn ($h) => [strtolower($h['name'] ?? '') => $h['value'] ?? null]);

            $emailData = [
                'from' => data_get($message, 'from.emailAddress.address', ''),
                'subject' => $message['subject'] ?? 'No Subject',
                'body' => data_get($message, 'body.content', ''),
                'message_id' => $message['internetMessageId'] ?? null,
                'in_reply_to' => $headers->get('in-reply-to'),
                'references' => $this->parseReferences($headers->get('references')),
                'attachments' => ($message['hasAttachments'] ?? false)
                    ? $this->fetchAttachments($graph, $message['id'])
                    : [],
            ];

            yield $emailData;

            // Mark read only after the consumer has handled (dispatched) the message,
            // mirroring the IMAP driver's "dispatch then flag seen" behavior.
            $graph->patch($graph->userPath()."/messages/{$message['id']}", ['isRead' => true]);
        }
    }

    public function send(EmailMailbox $mailbox, OutboundMessage $message): ?string
    {
        $graph = new GraphClient($mailbox);

        // Create a draft first so we can capture the internetMessageId for threading,
        // then send it. (sendMail alone returns no message id.)
        $draft = $graph->post($graph->userPath().'/messages', [
            'subject' => $message->subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $message->htmlBody,
            ],
            'toRecipients' => [[
                'emailAddress' => array_filter([
                    'address' => $message->toAddress,
                    'name' => $message->toName,
                ]),
            ]],
        ]);

        $draftId = $draft['id'] ?? null;
        if (! $draftId) {
            return null;
        }

        $graph->post($graph->userPath()."/messages/{$draftId}/send", []);

        return $draft['internetMessageId'] ?? null;
    }

    public function testConnection(EmailMailbox $mailbox): void
    {
        $graph = new GraphClient($mailbox);
        // Throws if the token request fails or the mailbox is inaccessible.
        $graph->get($graph->userPath(), ['$select' => 'id,mail']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchAttachments(GraphClient $graph, string $messageId): array
    {
        $result = $graph->get($graph->userPath()."/messages/{$messageId}/attachments");

        $attachments = [];
        foreach ($result['value'] ?? [] as $attachment) {
            // Only inline/file attachments carry contentBytes; skip item attachments.
            if (! isset($attachment['contentBytes'])) {
                continue;
            }

            $attachments[] = [
                'name' => $attachment['name'] ?? 'attachment',
                'content' => base64_decode($attachment['contentBytes']),
                'mime_type' => $attachment['contentType'] ?? 'application/octet-stream',
                'size' => $attachment['size'] ?? null,
            ];
        }

        return $attachments;
    }

    /**
     * @return array<int, string>
     */
    private function parseReferences(?string $references): array
    {
        if (! filled($references)) {
            return [];
        }

        preg_match_all('/<[^>]+>/', $references, $matches);

        return $matches[0] ?? [];
    }
}
