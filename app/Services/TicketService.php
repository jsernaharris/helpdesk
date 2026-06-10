<?php

namespace App\Services;

use App\Jobs\SendOutboundReply;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketThread;
use App\Models\User;
use App\Models\Contact;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        private PriorityMatrixService $priorityMatrix,
        private TicketStateMachine $stateMachine,
        private TicketNumberGenerator $numberGenerator,
        private SlaService $slaService,
    ) {}

    public function create(array $data, ?User $creator = null): Ticket
    {
        return DB::transaction(function () use ($data, $creator) {
            // Auto-calculate priority from impact + urgency if not explicitly set
            if (!isset($data['priority']) && isset($data['urgency'], $data['impact'])) {
                $data['priority'] = $this->priorityMatrix->calculate($data['urgency'], $data['impact']);
            }

            $data['priority'] = $data['priority'] ?? config('helpdesk.default_priority', 'medium');
            $data['type'] = $data['type'] ?? config('helpdesk.default_ticket_type', 'incident');
            $data['status'] = $data['status'] ?? 'new';
            $data['source'] = $data['source'] ?? 'portal';

            $ticket = Ticket::create($data);

            // Calculate SLA due dates
            $slaDates = $this->slaService->calculateDueDates($ticket);
            if ($slaDates['response_due_at'] || $slaDates['resolution_due_at']) {
                $ticket->update([
                    'sla_response_due_at' => $slaDates['response_due_at'],
                    'sla_resolution_due_at' => $slaDates['resolution_due_at'],
                ]);
            }

            // Create initial thread entry
            TicketThread::create([
                'ticket_id' => $ticket->id,
                'user_id' => $creator?->id ?? $data['requester_user_id'] ?? null,
                'contact_id' => $data['requester_contact_id'] ?? null,
                'type' => 'reply',
                'body' => $data['description'],
                'is_internal' => false,
            ]);

            // Log creation activity
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $creator?->id,
                'action' => 'created',
                'new_value' => $ticket->ticket_number,
            ]);

            return $ticket->fresh();
        });
    }

    public function addReply(Ticket $ticket, ?User $user, string $body, bool $internal = false, ?Contact $contact = null): TicketThread
    {
        return DB::transaction(function () use ($ticket, $user, $body, $internal, $contact) {
            $thread = TicketThread::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user?->id,
                'contact_id' => $contact?->id,
                'type' => $internal ? 'note' : 'reply',
                'body' => $body,
                'is_internal' => $internal,
            ]);

            // If staff is responding and no first response recorded, mark it
            if ($user && $user->isMspStaff() && !$internal && !$ticket->first_responded_at) {
                $ticket->update(['first_responded_at' => now()]);
            }

            // If ticket is new, auto-open it on first reply
            if ($ticket->status === 'new' && $user && $user->isMspStaff()) {
                $this->changeStatus($ticket, 'open', $user);
            }

            // If customer replies to resolved ticket, reopen it
            if (in_array($ticket->status, ['resolved', 'closed']) && $user && !$user->isMspStaff()) {
                $this->changeStatus($ticket, 'open', $user);
            }

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user?->id,
                'action' => $internal ? 'note_added' : 'reply_added',
            ]);

            // Email the reply back to the requester from the originating mailbox.
            // Internal notes and web-sourced tickets (no mailbox) never send.
            if (! $internal && $ticket->email_mailbox_id) {
                SendOutboundReply::dispatch($ticket, $thread)->afterCommit();
            }

            return $thread;
        });
    }

    public function changeStatus(Ticket $ticket, string $newStatus, ?User $user = null): Ticket
    {
        $oldStatus = $ticket->status;
        $this->stateMachine->transition($ticket, $newStatus);
        $ticket->save();

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'action' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
        ]);

        return $ticket;
    }

    public function assign(Ticket $ticket, ?User $assignee, ?Team $team, User $assignedBy): Ticket
    {
        return DB::transaction(function () use ($ticket, $assignee, $team, $assignedBy) {
            $oldAssignee = $ticket->assigned_to_user_id;
            $oldTeam = $ticket->assigned_to_team_id;

            $ticket->update([
                'assigned_to_user_id' => $assignee?->id,
                'assigned_to_team_id' => $team?->id,
            ]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $assignedBy->id,
                'action' => 'assigned',
                'old_value' => $oldAssignee ? User::find($oldAssignee)?->name : null,
                'new_value' => $assignee?->name ?? $team?->name ?? 'Unassigned',
            ]);

            // Auto-open new tickets on assignment
            if ($ticket->status === 'new') {
                $this->changeStatus($ticket, 'open', $assignedBy);
            }

            return $ticket->fresh();
        });
    }

    public function escalate(Ticket $ticket, int $level, User $user): Ticket
    {
        return DB::transaction(function () use ($ticket, $level, $user) {
            $ticket->update([
                'is_escalated' => true,
                'escalation_level' => $level,
            ]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'escalated',
                'new_value' => "Level {$level}",
            ]);

            return $ticket;
        });
    }

    public function merge(Ticket $primary, Ticket $duplicate, User $user): Ticket
    {
        return DB::transaction(function () use ($primary, $duplicate, $user) {
            // Move all threads from duplicate to primary
            $duplicate->threads()->update(['ticket_id' => $primary->id]);

            // Add merge note
            TicketThread::create([
                'ticket_id' => $primary->id,
                'user_id' => $user->id,
                'type' => 'system',
                'body' => "Ticket {$duplicate->ticket_number} was merged into this ticket.",
                'is_internal' => true,
            ]);

            // Close the duplicate
            $duplicate->update([
                'status' => 'closed',
                'closed_at' => now(),
                'resolution' => "Merged into {$primary->ticket_number}",
            ]);

            TicketActivity::create([
                'ticket_id' => $primary->id,
                'user_id' => $user->id,
                'action' => 'merged',
                'new_value' => $duplicate->ticket_number,
            ]);

            return $primary->fresh();
        });
    }

    public function updatePriority(Ticket $ticket, string $urgency, string $impact, User $user): Ticket
    {
        $oldPriority = $ticket->priority;
        $newPriority = $this->priorityMatrix->calculate($urgency, $impact);

        $ticket->update([
            'urgency' => $urgency,
            'impact' => $impact,
            'priority' => $newPriority,
        ]);

        if ($oldPriority !== $newPriority) {
            // Recalculate SLA
            $slaDates = $this->slaService->calculateDueDates($ticket);
            $ticket->update([
                'sla_response_due_at' => $slaDates['response_due_at'],
                'sla_resolution_due_at' => $slaDates['resolution_due_at'],
            ]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'priority_changed',
                'old_value' => $oldPriority,
                'new_value' => $newPriority,
            ]);
        }

        return $ticket;
    }
}
