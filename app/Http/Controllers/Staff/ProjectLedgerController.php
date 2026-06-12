<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectLedgerEntry;
use Illuminate\Http\Request;

class ProjectLedgerController extends Controller
{
    public function store(Request $request, Project $project)
    {
        abort_unless($request->user()->can('projects.update'), 403);

        $data = $request->validate([
            'description' => 'required|string',
            'is_internal' => 'sometimes|boolean',
        ]);

        $project->logEntry('note', $data['description'], $request->user(), $request->boolean('is_internal'));

        return back()->with('success', 'Ledger note added.');
    }

    public function destroy(Request $request, Project $project, ProjectLedgerEntry $entry)
    {
        abort_unless($request->user()->can('projects.update'), 403);
        abort_if($entry->project_id !== $project->id, 404);
        // Only manual notes can be removed; recorded events are an audit trail.
        abort_unless($entry->type === 'note', 403);

        $entry->delete();

        return back()->with('success', 'Ledger note removed.');
    }
}
