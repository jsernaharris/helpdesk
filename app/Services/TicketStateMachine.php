<?php

namespace App\Services;

use App\Models\Ticket;

class TicketStateMachine
{
    private const TRANSITIONS = [
        'incident' => [
            'new' => ['open', 'cancelled'],
            'open' => ['pending', 'on_hold', 'resolved', 'cancelled'],
            'pending' => ['open', 'resolved', 'cancelled'],
            'on_hold' => ['open', 'cancelled'],
            'resolved' => ['closed', 'open'],
            'closed' => ['open'],
            'cancelled' => [],
        ],
        'service_request' => [
            'new' => ['open', 'cancelled'],
            'open' => ['pending', 'on_hold', 'resolved', 'cancelled'],
            'pending' => ['open', 'resolved', 'cancelled'],
            'on_hold' => ['open', 'cancelled'],
            'resolved' => ['closed', 'open'],
            'closed' => ['open'],
            'cancelled' => [],
        ],
        'problem' => [
            'new' => ['open', 'cancelled'],
            'open' => ['pending', 'on_hold', 'resolved', 'cancelled'],
            'pending' => ['open', 'resolved', 'cancelled'],
            'on_hold' => ['open', 'cancelled'],
            'resolved' => ['closed', 'open'],
            'closed' => ['open'],
            'cancelled' => [],
        ],
        'change' => [
            'new' => ['open', 'cancelled'],
            'open' => ['pending', 'on_hold', 'resolved', 'cancelled'],
            'pending' => ['open', 'resolved', 'cancelled'],
            'on_hold' => ['open', 'cancelled'],
            'resolved' => ['closed', 'open'],
            'closed' => ['open'],
            'cancelled' => [],
        ],
    ];

    public function canTransition(Ticket $ticket, string $newStatus): bool
    {
        $type = $ticket->type;
        $currentStatus = $ticket->status;

        if (!isset(self::TRANSITIONS[$type][$currentStatus])) {
            return false;
        }

        return in_array($newStatus, self::TRANSITIONS[$type][$currentStatus]);
    }

    public function getAllowedTransitions(Ticket $ticket): array
    {
        $type = $ticket->type;
        $currentStatus = $ticket->status;

        return self::TRANSITIONS[$type][$currentStatus] ?? [];
    }

    public function transition(Ticket $ticket, string $newStatus): void
    {
        if (!$this->canTransition($ticket, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition {$ticket->type} ticket from '{$ticket->status}' to '{$newStatus}'"
            );
        }

        $ticket->status = $newStatus;

        if ($newStatus === 'resolved') {
            $ticket->resolved_at = now();
        } elseif ($newStatus === 'closed') {
            $ticket->closed_at = now();
        } elseif ($newStatus === 'open' && in_array($ticket->getOriginal('status'), ['resolved', 'closed'])) {
            $ticket->resolved_at = null;
            $ticket->closed_at = null;
        }
    }
}
