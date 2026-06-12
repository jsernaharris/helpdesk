<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketNumberGenerator;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private TicketNumberGenerator $numberGenerator) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('projects.view_all'), 403);

        $query = Project::accessibleBy($request->user())
            ->with(['organization', 'members'])
            ->withCount('timeEntries');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->assigned === 'me') {
            $query->whereHas('members', fn ($q) => $q->where('users.id', $request->user()->id));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('project_number', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }

        $projects = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $organizations = $this->accessibleOrganizations($request->user());

        return view('staff.projects.index', compact('projects', 'organizations'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('projects.create'), 403);

        return view('staff.projects.create', [
            'organizations' => $this->accessibleOrganizations($request->user()),
            'technicians' => $this->technicians(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('projects.create'), 403);

        $data = $this->validateProject($request);

        $project = Project::create([
            'project_number' => $this->numberGenerator->generateProjectNumber(),
            'organization_id' => $data['organization_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'status' => $data['status'] ?? 'planned',
            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'created_by_user_id' => $request->user()->id,
        ]);

        $project->members()->sync($this->memberPivot($request->input('members', [])));

        return redirect()->route('staff.projects.show', $project)
            ->with('success', "Project {$project->project_number} created.");
    }

    public function show(Request $request, Project $project)
    {
        abort_unless($request->user()->can('projects.view_all'), 403);

        $project->load([
            'organization', 'members', 'createdBy',
            'timeEntries' => fn ($q) => $q->with(['user', 'ticket'])->orderByDesc('work_date')->orderByDesc('id'),
        ]);

        return view('staff.projects.show', [
            'project' => $project,
            'technicians' => $this->technicians(),
            'tickets' => Ticket::where('organization_id', $project->organization_id)
                ->orderByDesc('id')->limit(100)->get(['id', 'ticket_number', 'subject']),
        ]);
    }

    public function edit(Request $request, Project $project)
    {
        abort_unless($request->user()->can('projects.update'), 403);

        return view('staff.projects.edit', [
            'project' => $project->load('members'),
            'organizations' => $this->accessibleOrganizations($request->user()),
            'technicians' => $this->technicians(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($request->user()->can('projects.update'), 403);

        $data = $this->validateProject($request);

        $project->update([
            'organization_id' => $data['organization_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'status' => $data['status'] ?? $project->status,
            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        $project->members()->sync($this->memberPivot($request->input('members', [])));

        return redirect()->route('staff.projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Request $request, Project $project)
    {
        abort_unless($request->user()->can('projects.delete'), 403);

        $project->delete();

        return redirect()->route('staff.projects.index')->with('success', 'Project deleted.');
    }

    public function addMember(Request $request, Project $project)
    {
        abort_unless($request->user()->can('projects.assign'), 403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_lead' => 'sometimes|boolean',
        ]);

        $project->members()->syncWithoutDetaching([
            $request->user_id => ['is_lead' => $request->boolean('is_lead')],
        ]);

        return back()->with('success', 'Member added.');
    }

    public function removeMember(Request $request, Project $project, User $user)
    {
        abort_unless($request->user()->can('projects.assign'), 403);

        $project->members()->detach($user->id);

        return back()->with('success', 'Member removed.');
    }

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'description' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'status' => 'nullable|in:planned,active,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'members' => 'sometimes|array',
            'members.*' => 'exists:users,id',
        ]);
    }

    private function memberPivot(array $userIds): array
    {
        return collect($userIds)->mapWithKeys(fn ($id) => [$id => ['is_lead' => false]])->all();
    }

    private function technicians()
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['msp_admin', 'msp_technician']))
            ->orderBy('name')->get();
    }

    private function accessibleOrganizations(User $user)
    {
        $query = Organization::where('is_active', true)->orderBy('name');
        $orgIds = $user->accessibleOrgIds();
        if ($orgIds !== null) {
            $query->whereIn('id', $orgIds);
        }
        return $query->get();
    }
}
