<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $breachType = $this->ticket->sla_response_breached ? 'Response' : 'Resolution';

        return (new MailMessage)
            ->subject("SLA BREACH: [{$this->ticket->ticket_number}] {$this->ticket->subject}")
            ->greeting("SLA {$breachType} Breach")
            ->line("Ticket {$this->ticket->ticket_number} has breached its SLA {$breachType} target.")
            ->line("Organization: " . $this->ticket->organization?->name)
            ->line("Priority: " . ucfirst($this->ticket->priority))
            ->line("Assigned to: " . ($this->ticket->assignedTo?->name ?? 'Unassigned'))
            ->action('View Ticket', url('/staff/tickets/' . $this->ticket->id))
            ->salutation('This requires immediate attention.');
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'action' => 'sla_breach',
        ];
    }
}
