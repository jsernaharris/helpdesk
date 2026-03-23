<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] {$this->ticket->subject}")
            ->greeting("Ticket {$this->ticket->ticket_number} Created")
            ->line("A new ticket has been created: {$this->ticket->subject}")
            ->line("Priority: " . ucfirst($this->ticket->priority))
            ->line("Status: " . ucfirst($this->ticket->status))
            ->action('View Ticket', url('/staff/tickets/' . $this->ticket->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'action' => 'created',
        ];
    }
}
