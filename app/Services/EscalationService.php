<?php

namespace App\Services;

use App\Models\EscalationRule;
use App\Models\Ticket;
use App\Models\Team;
use App\Models\User;

class EscalationService
{
    public function __construct(
        private TicketService $ticketService,
    ) {}

    public function runEscalations(): int
    {
        $count = 0;
        $rules = EscalationRule::where('is_active', true)->orderBy('escalation_level')->get();

        foreach ($rules as $rule) {
            $tickets = $this->getTicketsForRule($rule);
            foreach ($tickets as $ticket) {
                $this->applyRule($rule, $ticket);
                $count++;
            }
        }

        return $count;
    }

    private function getTicketsForRule(EscalationRule $rule): \Illuminate\Support\Collection
    {
        $query = Ticket::whereIn('status', ['new', 'open', 'pending', 'on_hold']);

        if ($rule->organization_id) {
            $query->where('organization_id', $rule->organization_id);
        }

        if ($rule->sla_plan_id) {
            $query->where('sla_plan_id', $rule->sla_plan_id);
        }

        switch ($rule->trigger_type) {
            case 'sla_breach':
                $query->where(function ($q) {
                    $q->where('sla_response_breached', true)
                        ->orWhere('sla_resolution_breached', true);
                });
                break;

            case 'sla_warning':
                $minutesBefore = $rule->trigger_minutes_before ?? 30;
                $warningTime = now()->addMinutes($minutesBefore);
                $query->where(function ($q) use ($warningTime) {
                    $q->where(function ($inner) use ($warningTime) {
                        $inner->where('sla_response_breached', false)
                            ->whereNull('first_responded_at')
                            ->whereNotNull('sla_response_due_at')
                            ->where('sla_response_due_at', '<=', $warningTime);
                    })->orWhere(function ($inner) use ($warningTime) {
                        $inner->where('sla_resolution_breached', false)
                            ->whereNotNull('sla_resolution_due_at')
                            ->where('sla_resolution_due_at', '<=', $warningTime);
                    });
                });
                break;

            case 'no_response':
                $minutes = $rule->trigger_minutes_before ?? 60;
                $query->whereNull('first_responded_at')
                    ->where('created_at', '<=', now()->subMinutes($minutes));
                break;
        }

        // Only escalate tickets not already at this level or higher
        $query->where('escalation_level', '<', $rule->escalation_level);

        return $query->get();
    }

    private function applyRule(EscalationRule $rule, Ticket $ticket): void
    {
        $target = $rule->action_target;

        switch ($rule->action_type) {
            case 'assign_team':
                if (isset($target['team_id'])) {
                    $team = Team::find($target['team_id']);
                    if ($team) {
                        $ticket->update([
                            'assigned_to_team_id' => $team->id,
                            'is_escalated' => true,
                            'escalation_level' => $rule->escalation_level,
                        ]);
                    }
                }
                break;

            case 'assign_user':
                if (isset($target['user_id'])) {
                    $user = User::find($target['user_id']);
                    if ($user) {
                        $ticket->update([
                            'assigned_to_user_id' => $user->id,
                            'is_escalated' => true,
                            'escalation_level' => $rule->escalation_level,
                        ]);
                    }
                }
                break;

            case 'change_priority':
                if (isset($target['priority'])) {
                    $ticket->update([
                        'priority' => $target['priority'],
                        'is_escalated' => true,
                        'escalation_level' => $rule->escalation_level,
                    ]);
                }
                break;

            case 'notify_email':
                $ticket->update([
                    'is_escalated' => true,
                    'escalation_level' => $rule->escalation_level,
                ]);
                break;
        }
    }
}
