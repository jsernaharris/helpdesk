<?php

namespace App\Services;

use App\Models\BusinessHours;
use App\Models\SlaBreachLog;
use App\Models\SlaPlan;
use App\Models\SlaTarget;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlaService
{
    public function calculateDueDates(Ticket $ticket): array
    {
        $plan = $this->resolvePlan($ticket);
        if (!$plan) {
            return ['response_due_at' => null, 'resolution_due_at' => null];
        }

        $target = $plan->getTargetForPriority($ticket->priority);
        if (!$target) {
            return ['response_due_at' => null, 'resolution_due_at' => null];
        }

        $now = now();

        if ($target->business_hours_only) {
            $businessHours = $this->resolveBusinessHours($ticket);
            $responseDue = $businessHours
                ? $this->addBusinessMinutes($now, $target->response_time_minutes, $businessHours)
                : $now->copy()->addMinutes($target->response_time_minutes);
            $resolutionDue = $businessHours
                ? $this->addBusinessMinutes($now, $target->resolution_time_minutes, $businessHours)
                : $now->copy()->addMinutes($target->resolution_time_minutes);
        } else {
            $responseDue = $now->copy()->addMinutes($target->response_time_minutes);
            $resolutionDue = $now->copy()->addMinutes($target->resolution_time_minutes);
        }

        return [
            'response_due_at' => $responseDue,
            'resolution_due_at' => $resolutionDue,
        ];
    }

    public function checkBreaches(): Collection
    {
        $now = now();
        $breached = collect();

        // Check response breaches
        $responseBreaches = Ticket::whereIn('status', ['new', 'open', 'pending', 'on_hold'])
            ->where('sla_response_breached', false)
            ->whereNotNull('sla_response_due_at')
            ->whereNull('first_responded_at')
            ->where('sla_response_due_at', '<=', $now)
            ->get();

        foreach ($responseBreaches as $ticket) {
            $ticket->update(['sla_response_breached' => true]);
            $target = $ticket->slaPlan?->getTargetForPriority($ticket->priority);
            SlaBreachLog::create([
                'ticket_id' => $ticket->id,
                'sla_plan_id' => $ticket->sla_plan_id,
                'breach_type' => 'response',
                'target_minutes' => $target?->response_time_minutes ?? 0,
                'actual_minutes' => (int) $ticket->created_at->diffInMinutes($now),
                'breached_at' => $now,
            ]);
            $breached->push($ticket);
        }

        // Check resolution breaches
        $resolutionBreaches = Ticket::whereIn('status', ['new', 'open', 'pending', 'on_hold'])
            ->where('sla_resolution_breached', false)
            ->whereNotNull('sla_resolution_due_at')
            ->where('sla_resolution_due_at', '<=', $now)
            ->get();

        foreach ($resolutionBreaches as $ticket) {
            $ticket->update(['sla_resolution_breached' => true]);
            $target = $ticket->slaPlan?->getTargetForPriority($ticket->priority);
            SlaBreachLog::create([
                'ticket_id' => $ticket->id,
                'sla_plan_id' => $ticket->sla_plan_id,
                'breach_type' => 'resolution',
                'target_minutes' => $target?->resolution_time_minutes ?? 0,
                'actual_minutes' => (int) $ticket->created_at->diffInMinutes($now),
                'breached_at' => $now,
            ]);
            $breached->push($ticket);
        }

        return $breached;
    }

    public function getWarnings(int $minutesBefore = 30): Collection
    {
        $now = now();
        $warningTime = $now->copy()->addMinutes($minutesBefore);

        return Ticket::whereIn('status', ['new', 'open', 'pending', 'on_hold'])
            ->where(function ($query) use ($now, $warningTime) {
                $query->where(function ($q) use ($now, $warningTime) {
                    $q->where('sla_response_breached', false)
                        ->whereNull('first_responded_at')
                        ->whereNotNull('sla_response_due_at')
                        ->where('sla_response_due_at', '>', $now)
                        ->where('sla_response_due_at', '<=', $warningTime);
                })->orWhere(function ($q) use ($now, $warningTime) {
                    $q->where('sla_resolution_breached', false)
                        ->whereNotNull('sla_resolution_due_at')
                        ->where('sla_resolution_due_at', '>', $now)
                        ->where('sla_resolution_due_at', '<=', $warningTime);
                });
            })
            ->get();
    }

    private function resolvePlan(Ticket $ticket): ?SlaPlan
    {
        if ($ticket->sla_plan_id) {
            return $ticket->slaPlan;
        }

        $orgPlan = SlaPlan::where('organization_id', $ticket->organization_id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($orgPlan) {
            return $orgPlan;
        }

        return SlaPlan::whereNull('organization_id')
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    private function resolveBusinessHours(Ticket $ticket): ?BusinessHours
    {
        return BusinessHours::where(function ($query) use ($ticket) {
            $query->where('organization_id', $ticket->organization_id)
                ->orWhereNull('organization_id');
        })
            ->where('is_default', true)
            ->orderByRaw('organization_id IS NULL ASC')
            ->first();
    }

    private function addBusinessMinutes(Carbon $start, int $minutes, BusinessHours $hours): Carbon
    {
        $current = $start->copy()->setTimezone($hours->timezone);
        $remaining = $minutes;
        $periods = $hours->periods->groupBy('day_of_week');

        $maxIterations = $minutes + (($minutes / 60) * 2 * 24);
        $iterations = 0;

        while ($remaining > 0 && $iterations < $maxIterations) {
            $iterations++;
            $dayOfWeek = $current->dayOfWeek;
            $dayPeriods = $periods->get($dayOfWeek, collect());

            foreach ($dayPeriods as $period) {
                $periodStart = $current->copy()->setTimeFromTimeString($period->start_time);
                $periodEnd = $current->copy()->setTimeFromTimeString($period->end_time);

                if ($current < $periodStart) {
                    $current = $periodStart;
                }

                if ($current >= $periodStart && $current < $periodEnd) {
                    $availableMinutes = (int) $current->diffInMinutes($periodEnd);
                    if ($availableMinutes >= $remaining) {
                        return $current->addMinutes($remaining)->setTimezone(config('app.timezone'));
                    }
                    $remaining -= $availableMinutes;
                    $current = $periodEnd;
                }
            }

            $current->addDay()->startOfDay();
        }

        return $current->setTimezone(config('app.timezone'));
    }
}
