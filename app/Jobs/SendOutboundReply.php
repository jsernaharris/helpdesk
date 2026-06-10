<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketThread;
use App\Services\Mail\MailboxDriverManager;
use App\Services\Mail\OutboundMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Emails a staff reply back to the ticket requester through the mailbox the
 * ticket originated from. Only dispatched for email-sourced tickets.
 */
class SendOutboundReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Ticket $ticket,
        public TicketThread $thread,
    ) {}

    public function handle(MailboxDriverManager $drivers): void
    {
        $mailbox = $this->ticket->mailbox;
        $to = $this->ticket->requester_email;

        if (! $mailbox || ! $mailbox->is_active || ! $to) {
            return;
        }

        $message = new OutboundMessage(
            toAddress: $to,
            subject: "Re: [{$this->ticket->ticket_number}] {$this->ticket->subject}",
            htmlBody: $this->thread->body,
            toName: $this->ticket->requester_name,
            inReplyTo: $this->lastInboundMessageId(),
        );

        $sentMessageId = $drivers->for($mailbox)->send($mailbox, $message);

        // Record the provider message id on the reply so subsequent customer
        // replies can be threaded via In-Reply-To.
        if ($sentMessageId) {
            $this->thread->update(['email_message_id' => $sentMessageId]);
        }
    }

    private function lastInboundMessageId(): ?string
    {
        return $this->ticket->threads()
            ->whereNotNull('email_message_id')
            ->latest()
            ->value('email_message_id');
    }
}
