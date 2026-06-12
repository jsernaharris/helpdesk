<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTimeEntry;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectTimeController extends Controller
{
    public function store(Request $request, Project $project)
    {
        abort_unless($request->user()->can('time.log'), 403);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'work_date' => 'required|date',
            'hours' => 'required|numeric|min:0.01|max:24',
            'ticket_id' => 'nullable|exists:tickets,id',
            'notes' => 'nullable|string',
        ]);

        $project->timeEntries()->create([
            'organization_id' => $project->organization_id,
            'user_id' => $data['user_id'],
            'ticket_id' => $data['ticket_id'] ?? null,
            'work_date' => $data['work_date'],
            'minutes' => (int) round($data['hours'] * 60),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Time logged.');
    }

    public function destroy(Request $request, Project $project, ProjectTimeEntry $entry)
    {
        abort_if($entry->project_id !== $project->id, 404);

        // A user may delete their own entry; managers (time.view_all) may delete any.
        abort_unless(
            $request->user()->can('time.view_all') || $entry->user_id === $request->user()->id,
            403
        );

        $entry->delete();

        return back()->with('success', 'Time entry removed.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('time.view_all'), 403);

        $query = ProjectTimeEntry::with(['project', 'organization', 'user', 'ticket']);

        $orgIds = $request->user()->accessibleOrgIds();
        if ($orgIds !== null) {
            $query->whereIn('organization_id', $orgIds);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('work_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('work_date', '<=', $request->to);
        }

        $filename = 'project-time-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Project #', 'Project', 'Organization', 'Technician', 'Date', 'Hours', 'Ticket', 'Notes']);

            $query->orderBy('work_date')->chunk(500, function ($entries) use ($out) {
                foreach ($entries as $e) {
                    fputcsv($out, [
                        $e->project?->project_number,
                        $e->project?->name,
                        $e->organization?->name,
                        $e->user?->name,
                        $e->work_date?->format('Y-m-d'),
                        number_format($e->minutes / 60, 2),
                        $e->ticket?->ticket_number,
                        $e->notes,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
