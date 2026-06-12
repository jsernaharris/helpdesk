<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('organization_id', $request->user()->organization_id)
            ->withCount('timeEntries')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('portal.projects.index', compact('projects'));
    }

    public function show(Request $request, Project $project)
    {
        // Customers may only view their own organization's projects; no time detail.
        abort_unless($project->organization_id === $request->user()->organization_id, 403);

        $project->load(['members', 'organization']);

        return view('portal.projects.show', compact('project'));
    }
}
