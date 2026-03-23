<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketThread $thread,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Re: [{$this->ticket->ticket_number}] {$this->ticket->subject}")
            ->greeting("New reply on {$this->ticket->ticket_number}")
            ->line($this->thread->author_name . " replied:")
            ->line(\Illuminate\Support\Str::limit(strip_tags($this->thread->body), 300));

        if ($notifiable->isMspStaff()) {
            $message->action('View Ticket', url('/staff/tickets/' . $this->ticket->id));
        } else {
            $message->action('View Ticket', url('/portal/tickets/' . $this->ticket->id));
        }

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'action' => 'reply',
            'author' => $this->thread->author_name,
        ];
    }
}
