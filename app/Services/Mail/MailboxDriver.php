<?php

namespace App\Services\Mail;

use App\Models\EmailMailbox;

/**
 * A transport for reading inbound mail from, and sending outbound mail through,
 * a configured {@see EmailMailbox}. Implementations: IMAP/SMTP and Microsoft Graph.
 */
interface MailboxDriver
{
    /**
     * Fetch unread messages, marking each as read/seen as it is yielded.
     *
     * Each item is normalized to the shape expected by
     * {@see \App\Jobs\ProcessInboundEmail}:
     *   from, subject, body, message_id, in_reply_to, references[], attachments[]
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function fetchUnread(EmailMailbox $mailbox): iterable;

    /**
     * Send an outbound message. Returns the provider message id when available
     * (best-effort, used for threading), or null.
     */
    public function send(EmailMailbox $mailbox, OutboundMessage $message): ?string;

    /**
     * Verify the mailbox credentials/connectivity. Throws on failure.
     */
    public function testConnection(EmailMailbox $mailbox): void;
}
