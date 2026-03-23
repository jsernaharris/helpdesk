<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SlaBreachLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('staff.reports.index');
    }

    public function slaCompliance(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $stats = Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN sla_response_breached = 1 THEN 1 ELSE 0 END) as response_breaches,
                SUM(CASE WHEN sla_resolution_breached = 1 THEN 1 ELSE 0 END) as resolution_breaches,
                priority
            ")
            ->groupBy('priority')
            ->get();

        $byOrg = Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->join('organizations', 'tickets.organization_id', '=', 'organizations.id')
            ->selectRaw("
                organizations.name as org_name,
                COUNT(*) as total,
                SUM(CASE WHEN sla_response_breached = 1 THEN 1 ELSE 0 END) as response_breaches,
                SUM(CASE WHEN sla_resolution_breached = 1 THEN 1 ELSE 0 END) as resolution_breaches
            ")
            ->groupBy('organizations.name')
            ->get();

        return view('staff.reports.sla-compliance', compact('stats', 'byOrg', 'dateFrom', 'dateTo'));
    }

    public function ticketVolume(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $daily = Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, type')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $bySource = Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get();

        $byPriority = Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();

        return view('staff.reports.ticket-volume', compact('daily', 'bySource', 'byPriority', 'dateFrom', 'dateTo'));
    }

    public function technicianPerformance(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $performance = Ticket::whereBetween('tickets.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereNotNull('assigned_to_user_id')
            ->join('users', 'tickets.assigned_to_user_id', '=', 'users.id')
            ->selectRaw("
                users.name as tech_name,
                COUNT(*) as total_assigned,
                SUM(CASE WHEN tickets.status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved,
                AVG(CASE WHEN tickets.resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at) END) as avg_resolution_minutes,
                SUM(CASE WHEN tickets.sla_response_breached = 1 OR tickets.sla_resolution_breached = 1 THEN 1 ELSE 0 END) as sla_breaches
            ")
            ->groupBy('users.name')
            ->get();

        return view('staff.reports.technician-performance', compact('performance', 'dateFrom', 'dateTo'));
    }
}
