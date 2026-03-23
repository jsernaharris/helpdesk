<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Ticket;
use App\Services\TicketNumberGenerator;
use App\Services\TicketService;
use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    public function __construct(
        private TicketService $ticketService,
        private TicketNumberGenerator $numberGenerator,
    ) {}

    public function index(Request $request)
    {
        $query = ChangeRequest::with(['ticket.organization', 'ticket.assignedTo', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $changes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('staff.changes.index', compact('changes'));
    }

    public function create()
    {
        return view('staff.changes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'organization_id' => 'required|exists:organizations,id',
            'type' => 'required|in:standard,normal,emergency',
            'risk_level' => 'required|in:low,medium,high,critical',
            'implementation_plan' => 'nullable|string',
            'rollback_plan' => 'nullable|string',
            'test_plan' => 'nullable|string',
            'scheduled_start_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date|after:scheduled_start_at',
            'cab_required' => 'sometimes|boolean',
        ]);

        $ticket = $this->ticketService->create([
            'organization_id' => $request->organization_id,
            'requester_user_id' => $request->user()->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'type' => 'change',
            'priority' => 'medium',
            'source' => 'portal',
        ], $request->user());

        ChangeRequest::create([
            'ticket_id' => $ticket->id,
            'change_number' => $this->numberGenerator->generateChangeNumber(),
            'type' => $request->type,
            'risk_level' => $request->risk_level,
            'implementation_plan' => $request->implementation_plan,
            'rollback_plan' => $request->rollback_plan,
            'test_plan' => $request->test_plan,
            'scheduled_start_at' => $request->scheduled_start_at,
            'scheduled_end_at' => $request->scheduled_end_at,
            'cab_required' => $request->boolean('cab_required'),
            'status' => 'draft',
        ]);

        return redirect()->route('staff.changes.index')
            ->with('success', 'Change request created.');
    }

    public function show(ChangeRequest $change)
    {
        $change->load(['ticket.organization', 'ticket.assignedTo', 'ticket.threads.user', 'approvedBy']);
        return view('staff.changes.show', compact('change'));
    }

    public function approve(Request $request, ChangeRequest $change)
    {
        $change->update([
            'status' => 'approved',
            'approved_by_user_id' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Change request approved.');
    }

    public function reject(Request $request, ChangeRequest $change)
    {
        $request->validate(['reason' => 'required|string']);

        $change->update([
            'status' => 'rejected',
            'cab_notes' => $request->reason,
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Change request rejected.');
    }
}
