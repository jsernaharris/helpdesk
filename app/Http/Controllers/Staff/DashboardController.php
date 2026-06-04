<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'open' => Ticket::accessibleBy($user)->whereIn('status', ['new', 'open'])->count(),
            'pending' => Ticket::accessibleBy($user)->where('status', 'pending')->count(),
            'on_hold' => Ticket::accessibleBy($user)->where('status', 'on_hold')->count(),
            'resolved_today' => Ticket::accessibleBy($user)->where('status', 'resolved')
                ->whereDate('resolved_at', today())->count(),
            'sla_breached' => Ticket::accessibleBy($user)->whereIn('status', ['new', 'open', 'pending', 'on_hold'])
                ->where(function ($q) {
                    $q->where('sla_response_breached', true)
                        ->orWhere('sla_resolution_breached', true);
                })->count(),
            'my_tickets' => Ticket::accessibleBy($user)->where('assigned_to_user_id', $user->id)
                ->whereIn('status', ['new', 'open', 'pending', 'on_hold'])->count(),
            'unassigned' => Ticket::accessibleBy($user)->whereNull('assigned_to_user_id')
                ->whereIn('status', ['new', 'open'])->count(),
        ];

        $recentTickets = Ticket::accessibleBy($user)
            ->with(['requester', 'assignedTo', 'organization'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $myTickets = Ticket::accessibleBy($user)
            ->with(['requester', 'organization'])
            ->where('assigned_to_user_id', $user->id)
            ->whereIn('status', ['new', 'open', 'pending', 'on_hold'])
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->limit(10)
            ->get();

        return view('staff.dashboard', compact('stats', 'recentTickets', 'myTickets'));
    }
}
