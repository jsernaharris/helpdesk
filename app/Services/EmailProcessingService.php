<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketThread;
use App\Models\User;
use App\Models\EmailMailbox;

class EmailProcessingService
{
    public function __construct(
        private TicketService $ticketService,
    ) {}

    public function process(array $emailData, EmailMailbox $mailbox): ?Ticket
    {
        $senderEmail = $this->extractEmail($emailData['from'] ?? '');
        $subject = $emailData['subject'] ?? 'No Subject';
        $body = $emailData['body'] ?? '';
        $messageId = $emailData['message_id'] ?? null;
        $inReplyTo = $emailData['in_reply_to'] ?? null;
        $references = $emailData['references'] ?? [];

        // Check if this is a reply to an existing ticket
        $ticket = $this->findExistingTicket($subject, $inReplyTo, $references);

        if ($ticket) {
            return $this->addReplyToTicket($ticket, $senderEmail, $body, $messageId);
        }

        // Create new ticket
        return $this->createTicketFromEmail($senderEmail, $subject, $body, $messageId, $mailbox);
    }

    private function findExistingTicket(string $subject, ?string $inReplyTo, array $references): ?Ticket
    {
        // Check subject for ticket number pattern
        if (preg_match('/\[(INC|SR|PRB|CHG)-(\d+)\]/', $subject, $matches)) {
            $ticketNumber = $matches[1] . '-' . $matches[2];
            $ticket = Ticket::where('ticket_number', $ticketNumber)->first();
            if ($ticket) {
                return $ticket;
            }
        }

        // Check In-Reply-To header against stored message IDs
        if ($inReplyTo) {
            $thread = TicketThread::where('email_message_id', $inReplyTo)->first();
            if ($thread) {
                return $thread->ticket;
            }
        }

        // Check References header
        if (!empty($references)) {
            $thread = TicketThread::whereIn('email_message_id', $references)
                ->latest()
                ->first();
            if ($thread) {
                return $thread->ticket;
            }
        }

        return null;
    }

    private function addReplyToTicket(Ticket $ticket, string $senderEmail, string $body, ?string $messageId): Ticket
    {
        $user = User::where('email', $senderEmail)->first();
        $contact = $user ? null : Contact::where('email', $senderEmail)->first();

        $thread = TicketThread::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'contact_id' => $contact?->id,
            'type' => 'email_inbound',
            'body' => $this->sanitizeHtml($body),
            'is_internal' => false,
            'email_message_id' => $messageId,
        ]);

        // Reopen if resolved/closed
        if (in_array($ticket->status, ['resolved', 'closed'])) {
            $ticket->update(['status' => 'open', 'resolved_at' => null, 'closed_at' => null]);
        }

        return $ticket->fresh();
    }

    private function createTicketFromEmail(string $senderEmail, string $subject, string $body, ?string $messageId, EmailMailbox $mailbox): Ticket
    {
        $user = User::where('email', $senderEmail)->first();
        $contact = null;
        $organizationId = null;

        if ($user) {
            $organizationId = $user->organization_id;
        } else {
            // Try to match by email domain
            $domain = substr(strrchr($senderEmail, '@'), 1);
            $org = Organization::where('email_domain', $domain)->first();
            $organizationId = $org?->id ?? $mailbox->organization_id;

            $contact = Contact::firstOrCreate(
                ['email' => $senderEmail],
                ['name' => $this->extractName($senderEmail), 'organization_id' => $organizationId]
            );
        }

        if (!$organizationId) {
            $organizationId = $mailbox->organization_id ?? Organization::where('is_msp', true)->value('id');
        }

        $ticket = $this->ticketService->create([
            'organization_id' => $organizationId,
            'requester_user_id' => $user?->id,
            'requester_contact_id' => $contact?->id,
            'subject' => $subject,
            'description' => $this->sanitizeHtml($body),
            'type' => $mailbox->default_type ?? 'incident',
            'priority' => $mailbox->default_priority ?? 'medium',
            'source' => 'email',
            'email_mailbox_id' => $mailbox->id,
        ], $user);

        // Store message ID on the initial thread for email threading
        if ($messageId) {
            $ticket->threads()->first()?->update(['email_message_id' => $messageId]);
        }

        return $ticket;
    }

    private function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $matches)) {
            return strtolower(trim($matches[1]));
        }
        return strtolower(trim($from));
    }

    private function extractName(string $email): string
    {
        $local = substr($email, 0, strpos($email, '@'));
        return ucwords(str_replace(['.', '_', '-'], ' ', $local));
    }

    private function sanitizeHtml(string $html): string
    {
        return strip_tags($html, '<p><br><strong><em><ul><ol><li><a><blockquote><pre><code>');
    }
}
