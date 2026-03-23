<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::where('organization_id', $user->organization_id);

        if ($user->isCustomerUser()) {
            $query->where('requester_user_id', $user->id);
        }

        $stats = [
            'open' => (clone $query)->whereIn('status', ['new', 'open'])->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'total' => (clone $query)->count(),
        ];

        $recentTickets = (clone $query)
            ->with(['assignedTo'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('portal.dashboard', compact('stats', 'recentTickets'));
    }
}
