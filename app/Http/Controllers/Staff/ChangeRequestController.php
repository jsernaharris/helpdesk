<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ChangeApproval;
use App\Models\ChangeBlackoutPeriod;
use App\Models\ChangeCategory;
use App\Models\ChangeRequest;
use App\Models\ChangeReview;
use App\Models\Organization;
use App\Models\User;
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
        $query = ChangeRequest::with(['ticket', 'organization', 'category', 'requestedBy', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('change_number', 'like', "%{$search}%")
                    ->orWhereHas('ticket', fn ($t) => $t->where('subject', 'like', "%{$search}%"));
            });
        }

        $changes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('staff.changes.index', compact('changes', 'organizations'));
    }

    public function create(Request $request)
    {
        $organizations = Organization::where('is_active', true)->where('is_msp', false)->orderBy('name')->get();
        $categories = collect();

        if ($request->filled('organization_id')) {
            $categories = ChangeCategory::where('organization_id', $request->organization_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        return view('staff.changes.create', compact('organizations', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'organization_id' => 'required|exists:organizations,id',
            'change_category_id' => 'nullable|exists:change_categories,id',
            'type' => 'required|in:standard,normal,emergency',
            'risk_level' => 'required|in:low,medium,high,critical',
            'implementation_plan' => 'nullable|string',
            'rollback_plan' => 'nullable|string',
            'test_plan' => 'nullable|string',
            'business_justification' => 'nullable|string',
            'impact_assessment' => 'nullable|string',
            'communication_plan' => 'nullable|string',
            'scheduled_start_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date|after:scheduled_start_at',
        ]);

        $org = Organization::findOrFail($request->organization_id);
        $policy = $org->getOrCreateChangePolicy();

        // Determine CAB requirement from policy
        $cabRequired = $policy->cabRequiredForType($request->type);

        // Determine approval levels
        $approvalLevels = 1;
        if ($cabRequired) {
            $approvalLevels = max(1, $org->cabMembers()->where('is_active', true)->count() > 0 ? 2 : 1);
        }

        $ticket = $this->ticketService->create([
            'organization_id' => $request->organization_id,
            'requester_user_id' => $request->user()->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'type' => 'change',
            'priority' => 'medium',
            'source' => 'portal',
        ], $request->user());

        $change = ChangeRequest::create([
            'ticket_id' => $ticket->id,
            'organization_id' => $request->organization_id,
            'change_category_id' => $request->change_category_id,
            'requested_by_user_id' => $request->user()->id,
            'change_number' => $this->numberGenerator->generateChangeNumber(),
            'type' => $request->type,
            'risk_level' => $request->risk_level,
            'implementation_plan' => $request->implementation_plan,
            'rollback_plan' => $request->rollback_plan,
            'test_plan' => $request->test_plan,
            'business_justification' => $request->business_justification,
            'impact_assessment' => $request->impact_assessment,
            'communication_plan' => $request->communication_plan,
            'scheduled_start_at' => $request->scheduled_start_at,
            'scheduled_end_at' => $request->scheduled_end_at,
            'cab_required' => $cabRequired,
            'approval_level_required' => $approvalLevels,
            'status' => 'draft',
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', "Change request {$change->change_number} created.");
    }

    public function show(ChangeRequest $change)
    {
        $change->load([
            'ticket.organization', 'ticket.assignedTo', 'ticket.threads.user',
            'organization', 'category', 'requestedBy', 'approvedBy',
            'approvals.user', 'review.reviewer',
        ]);

        $blackoutWarning = $change->isInBlackout();
        $cabMembers = $change->organization
            ? $change->organization->cabMembers()->where('is_active', true)->with('user')->get()
            : collect();

        return view('staff.changes.show', compact('change', 'blackoutWarning', 'cabMembers'));
    }

    public function edit(ChangeRequest $change)
    {
        if (!in_array($change->status, ['draft', 'rejected'])) {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Only draft or rejected changes can be edited.');
        }

        $change->load(['organization', 'category']);
        $organizations = Organization::where('is_active', true)->where('is_msp', false)->orderBy('name')->get();
        $categories = ChangeCategory::where('organization_id', $change->organization_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('staff.changes.edit', compact('change', 'organizations', 'categories'));
    }

    public function update(Request $request, ChangeRequest $change)
    {
        if (!in_array($change->status, ['draft', 'rejected'])) {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Only draft or rejected changes can be edited.');
        }

        $request->validate([
            'type' => 'required|in:standard,normal,emergency',
            'risk_level' => 'required|in:low,medium,high,critical',
            'implementation_plan' => 'nullable|string',
            'rollback_plan' => 'nullable|string',
            'test_plan' => 'nullable|string',
            'business_justification' => 'nullable|string',
            'impact_assessment' => 'nullable|string',
            'communication_plan' => 'nullable|string',
            'scheduled_start_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date|after:scheduled_start_at',
        ]);

        $change->update($request->only([
            'type', 'risk_level', 'implementation_plan', 'rollback_plan', 'test_plan',
            'business_justification', 'impact_assessment', 'communication_plan',
            'scheduled_start_at', 'scheduled_end_at',
        ]));

        // Update ticket subject if provided
        if ($request->filled('subject')) {
            $change->ticket->update(['subject' => $request->subject]);
        }

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Change request updated.');
    }

    public function submit(ChangeRequest $change)
    {
        if ($change->status !== 'draft') {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Only draft changes can be submitted.');
        }

        // Validate policy requirements
        if ($change->organization) {
            $policy = $change->organization->getOrCreateChangePolicy();
            $errors = [];

            if ($policy->require_implementation_plan && empty($change->implementation_plan)) {
                $errors[] = 'Implementation plan is required by organization policy.';
            }
            if ($policy->require_rollback_plan && empty($change->rollback_plan)) {
                $errors[] = 'Rollback plan is required by organization policy.';
            }
            if ($policy->require_test_plan && empty($change->test_plan)) {
                $errors[] = 'Test plan is required by organization policy.';
            }
            if ($change->type !== 'emergency') {
                $minLead = $policy->min_lead_time_hours;
                if ($change->scheduled_start_at && $change->scheduled_start_at->diffInHours(now()) < $minLead) {
                    $errors[] = "Organization requires {$minLead} hours lead time for non-emergency changes.";
                }
            }
            if ($change->isInBlackout()) {
                $errors[] = 'Scheduled time falls within a change blackout period.';
            }

            if (!empty($errors)) {
                return redirect()->route('staff.changes.show', $change)
                    ->with('error', implode(' ', $errors));
            }
        }

        $change->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Change request submitted for review.');
    }

    public function approve(Request $request, ChangeRequest $change)
    {
        if (!in_array($change->status, ['submitted', 'under_review'])) {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Change is not pending approval.');
        }

        $request->validate(['comments' => 'nullable|string']);

        // Record the approval
        $nextLevel = $change->current_approval_level + 1;
        ChangeApproval::create([
            'change_request_id' => $change->id,
            'user_id' => $request->user()->id,
            'decision' => 'approved',
            'comments' => $request->comments,
            'approval_level' => $nextLevel,
        ]);

        $change->update([
            'current_approval_level' => $nextLevel,
            'status' => 'under_review',
        ]);

        // Check if fully approved
        if ($change->fresh()->isFullyApproved()) {
            $change->update([
                'status' => 'approved',
                'approved_by_user_id' => $request->user()->id,
                'approved_at' => now(),
            ]);

            return redirect()->route('staff.changes.show', $change)
                ->with('success', 'Change request fully approved.');
        }

        return redirect()->route('staff.changes.show', $change)
            ->with('success', "Approval recorded (level {$nextLevel}/{$change->approval_level_required}).");
    }

    public function reject(Request $request, ChangeRequest $change)
    {
        if (!in_array($change->status, ['submitted', 'under_review'])) {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Change is not pending approval.');
        }

        $request->validate(['reason' => 'required|string']);

        ChangeApproval::create([
            'change_request_id' => $change->id,
            'user_id' => $request->user()->id,
            'decision' => 'rejected',
            'comments' => $request->reason,
            'approval_level' => $change->current_approval_level + 1,
        ]);

        $change->update([
            'status' => 'rejected',
            'cab_notes' => $request->reason,
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Change request rejected.');
    }

    public function startImplementation(ChangeRequest $change)
    {
        if ($change->status !== 'approved') {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Only approved changes can begin implementation.');
        }

        $change->update([
            'status' => 'implementing',
            'actual_start_at' => now(),
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Implementation started.');
    }

    public function completeImplementation(Request $request, ChangeRequest $change)
    {
        if ($change->status !== 'implementing') {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Change is not currently being implemented.');
        }

        $request->validate([
            'outcome' => 'required|in:completed,failed',
            'post_implementation_notes' => 'nullable|string',
        ]);

        $change->update([
            'status' => $request->outcome,
            'actual_end_at' => now(),
            'post_implementation_notes' => $request->post_implementation_notes,
        ]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Implementation ' . $request->outcome . '.');
    }

    public function storeReview(Request $request, ChangeRequest $change)
    {
        if (!in_array($change->status, ['completed', 'failed'])) {
            return redirect()->route('staff.changes.show', $change)
                ->with('error', 'Post-implementation review requires completed or failed status.');
        }

        $request->validate([
            'objectives_met' => 'required|boolean',
            'on_schedule' => 'required|boolean',
            'within_budget' => 'required|boolean',
            'incidents_caused' => 'required|boolean',
            'incidents_description' => 'nullable|required_if:incidents_caused,1|string',
            'lessons_learned' => 'nullable|string',
            'improvement_actions' => 'nullable|string',
            'overall_rating' => 'required|in:successful,partially_successful,failed',
        ]);

        ChangeReview::updateOrCreate(
            ['change_request_id' => $change->id],
            array_merge($request->only([
                'objectives_met', 'on_schedule', 'within_budget',
                'incidents_caused', 'incidents_description',
                'lessons_learned', 'improvement_actions', 'overall_rating',
            ]), ['reviewer_id' => $request->user()->id])
        );

        $change->update(['review_completed_at' => now()]);

        return redirect()->route('staff.changes.show', $change)
            ->with('success', 'Post-implementation review saved.');
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $changes = ChangeRequest::with(['ticket', 'organization'])
            ->whereNotNull('scheduled_start_at')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_start_at', [$start, $end])
                    ->orWhereBetween('scheduled_end_at', [$start, $end]);
            })
            ->orderBy('scheduled_start_at')
            ->get();

        $blackouts = ChangeBlackoutPeriod::active()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('starts_at', [$start, $end])
                    ->orWhereBetween('ends_at', [$start, $end]);
            })
            ->with('organization')
            ->get();

        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('staff.changes.calendar', compact('changes', 'blackouts', 'month', 'start', 'end', 'organizations'));
    }
}
