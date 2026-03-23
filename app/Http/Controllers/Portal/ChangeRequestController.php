<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ChangeCategory;
use App\Models\ChangeRequest;
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
        $user = $request->user();
        $query = ChangeRequest::where('organization_id', $user->organization_id)
            ->with(['ticket', 'category', 'requestedBy']);

        if ($user->isCustomerUser()) {
            $query->where('requested_by_user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $changes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('portal.changes.index', compact('changes'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $org = $user->organization;

        // Check if org allows customer-submitted changes
        $policy = $org->getOrCreateChangePolicy();
        if (!$policy->allow_customer_submit) {
            return redirect()->route('portal.changes.index')
                ->with('error', 'Your organization does not allow direct change request submission. Please contact your MSP.');
        }

        $categories = ChangeCategory::where('organization_id', $org->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('portal.changes.create', compact('categories', 'policy'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $org = $user->organization;
        $policy = $org->getOrCreateChangePolicy();

        if (!$policy->allow_customer_submit) {
            abort(403, 'Change submission not allowed for this organization.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'change_category_id' => 'nullable|exists:change_categories,id',
            'type' => 'required|in:standard,normal,emergency',
            'business_justification' => 'required|string',
            'impact_assessment' => 'nullable|string',
            'scheduled_start_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date|after:scheduled_start_at',
        ]);

        // Determine defaults from category if selected
        $category = $request->change_category_id
            ? ChangeCategory::find($request->change_category_id)
            : null;

        $riskLevel = $category?->default_risk_level ?? 'medium';
        $cabRequired = $category?->cab_required ?? $policy->cabRequiredForType($request->type);

        $ticket = $this->ticketService->create([
            'organization_id' => $org->id,
            'requester_user_id' => $user->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'type' => 'change',
            'priority' => 'medium',
            'source' => 'portal',
        ], $user);

        $change = ChangeRequest::create([
            'ticket_id' => $ticket->id,
            'organization_id' => $org->id,
            'change_category_id' => $request->change_category_id,
            'requested_by_user_id' => $user->id,
            'change_number' => $this->numberGenerator->generateChangeNumber(),
            'type' => $request->type,
            'risk_level' => $riskLevel,
            'implementation_plan' => $category?->template_implementation_plan,
            'rollback_plan' => $category?->template_rollback_plan,
            'test_plan' => $category?->template_test_plan,
            'business_justification' => $request->business_justification,
            'impact_assessment' => $request->impact_assessment,
            'scheduled_start_at' => $request->scheduled_start_at,
            'scheduled_end_at' => $request->scheduled_end_at,
            'cab_required' => $cabRequired,
            'approval_level_required' => $cabRequired ? 2 : 1,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('portal.changes.show', $change)
            ->with('success', "Change request {$change->change_number} submitted.");
    }

    public function show(Request $request, ChangeRequest $change)
    {
        $user = $request->user();

        if ($change->organization_id !== $user->organization_id) {
            abort(403);
        }
        if ($user->isCustomerUser() && $change->requested_by_user_id !== $user->id) {
            abort(403);
        }

        $change->load(['ticket', 'category', 'requestedBy', 'approvedBy', 'approvals.user', 'review']);

        return view('portal.changes.show', compact('change'));
    }
}
