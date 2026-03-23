<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ProblemRecord;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    public function index(Request $request)
    {
        $query = ProblemRecord::with(['ticket.organization', 'ticket.assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $problems = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('staff.problems.index', compact('problems'));
    }

    public function create()
    {
        return view('staff.problems.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'organization_id' => 'required|exists:organizations,id',
            'incident_ids' => 'nullable|array',
            'incident_ids.*' => 'exists:tickets,id',
        ]);

        $ticket = $this->ticketService->create([
            'organization_id' => $request->organization_id,
            'requester_user_id' => $request->user()->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'type' => 'problem',
            'priority' => 'high',
            'source' => 'portal',
        ], $request->user());

        $problem = ProblemRecord::create([
            'ticket_id' => $ticket->id,
            'status' => 'open',
        ]);

        if ($request->filled('incident_ids')) {
            $problem->incidents()->attach($request->incident_ids);
        }

        return redirect()->route('staff.problems.show', $problem)
            ->with('success', 'Problem record created.');
    }

    public function show(ProblemRecord $problem)
    {
        $problem->load(['ticket.organization', 'ticket.assignedTo', 'ticket.threads.user', 'incidents']);
        return view('staff.problems.show', compact('problem'));
    }

    public function update(Request $request, ProblemRecord $problem)
    {
        $request->validate([
            'root_cause' => 'nullable|string',
            'workaround' => 'nullable|string',
            'known_error' => 'sometimes|boolean',
            'status' => 'required|in:open,investigating,root_cause_identified,resolved,closed',
        ]);

        $problem->update($request->only(['root_cause', 'workaround', 'known_error', 'status']));

        return redirect()->route('staff.problems.show', $problem)
            ->with('success', 'Problem record updated.');
    }

    public function linkIncident(Request $request, ProblemRecord $problem)
    {
        $request->validate(['ticket_id' => 'required|exists:tickets,id']);

        $problem->incidents()->syncWithoutDetaching([$request->ticket_id]);

        return redirect()->route('staff.problems.show', $problem)
            ->with('success', 'Incident linked.');
    }
}
