<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\TicketReplyRequest;
use App\Models\FormTemplate;
use App\Models\Organization;
use App\Models\ServiceCatalog;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Services\TicketStateMachine;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService,
        private TicketStateMachine $stateMachine,
    ) {}

    public function index(Request $request)
    {
        $query = Ticket::accessibleBy($request->user())
            ->with(['requester', 'requesterContact', 'assignedTo', 'assignedToTeam', 'organization']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_to_user_id', $request->user()->id);
            } elseif ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to_user_id');
            } else {
                $query->where('assigned_to_user_id', $request->assigned_to);
            }
        }
        if ($request->filled('sla_status')) {
            if ($request->sla_status === 'breached') {
                $query->where(function ($q) {
                    $q->where('sla_response_breached', true)
                        ->orWhere('sla_resolution_breached', true);
                });
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Default sort: priority weight then created_at
        $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->orderByDesc('created_at');

        $tickets = $query->paginate(25)->withQueryString();

        $orgQuery = Organization::where('is_active', true)->orderBy('name');
        $orgIds = $request->user()->accessibleOrgIds();
        if ($orgIds !== null) {
            $orgQuery->whereIn('id', $orgIds);
        }
        $organizations = $orgQuery->get();

        $technicians = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['msp_admin', 'msp_technician']);
        })->orderBy('name')->get();

        return view('staff.tickets.index', compact('tickets', 'organizations', 'technicians'));
    }

    public function create(Request $request)
    {
        $orgQuery = Organization::where('is_active', true)->where('is_msp', false)->orderBy('name');
        $orgIds = $request->user()->accessibleOrgIds();
        if ($orgIds !== null) {
            $orgQuery->whereIn('id', $orgIds);
        }
        $organizations = $orgQuery->get();
        $technicians = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['msp_admin', 'msp_technician']);
        })->orderBy('name')->get();
        $teams = Team::where('is_active', true)->orderBy('name')->get();
        $services = ServiceCatalog::where('is_active', true)->orderBy('name')->get();
        $formTemplates = FormTemplate::active()->orderBy('name')->get();

        return view('staff.tickets.create', compact('organizations', 'technicians', 'teams', 'services', 'formTemplates'));
    }

    public function store(StoreTicketRequest $request)
    {
        $data = $request->validated();

        // If organization set, look up requester by email if needed
        if (!isset($data['requester_user_id']) && $request->filled('requester_email')) {
            $user = User::where('email', $request->requester_email)->first();
            if ($user) {
                $data['requester_user_id'] = $user->id;
                $data['organization_id'] = $data['organization_id'] ?? $user->organization_id;
            }
        }

        // Handle custom form fields
        if ($request->filled('form_template_id')) {
            $data['form_template_id'] = $request->form_template_id;
            $data['custom_fields'] = $request->input('custom_fields', []);
        }

        $ticket = $this->ticketService->create($data, $request->user());

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/tickets/' . $ticket->id, 'local');
                $ticket->attachments()->create([
                    'user_id' => $request->user()->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} created successfully.");
    }

    public function show(Request $request, Ticket $ticket)
    {
        if (!$request->user()->canAccessOrganization($ticket->organization_id)) {
            abort(403, 'You do not have access to this organization\'s tickets.');
        }

        $ticket->load([
            'requester', 'requesterContact', 'assignedTo', 'assignedToTeam',
            'organization', 'serviceCatalog', 'slaPlan.targets', 'formTemplate',
            'threads.user', 'threads.contact', 'threads.attachments',
            'activities.user', 'attachments', 'tags',
            'problemRecord', 'changeRequest',
        ]);

        $allowedTransitions = $this->stateMachine->getAllowedTransitions($ticket);
        $technicians = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['msp_admin', 'msp_technician']);
        })->orderBy('name')->get();
        $teams = Team::where('is_active', true)->orderBy('name')->get();

        return view('staff.tickets.show', compact('ticket', 'allowedTransitions', 'technicians', 'teams'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        if (isset($data['status']) && $data['status'] !== $ticket->status) {
            $this->ticketService->changeStatus($ticket, $data['status'], $request->user());
            unset($data['status']);
        }

        if (!empty($data)) {
            $ticket->update($data);
        }

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function reply(TicketReplyRequest $request, Ticket $ticket)
    {
        $thread = $this->ticketService->addReply(
            $ticket,
            $request->user(),
            $request->body,
            $request->boolean('is_internal'),
        );

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/threads/' . $thread->id, 'local');
                $thread->attachments()->create([
                    'user_id' => $request->user()->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', 'Reply added successfully.');
    }

    public function addNote(TicketReplyRequest $request, Ticket $ticket)
    {
        $this->ticketService->addReply($ticket, $request->user(), $request->body, true);

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', 'Internal note added.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'assigned_to_team_id' => 'nullable|exists:teams,id',
        ]);

        $assignee = $request->assigned_to_user_id ? User::find($request->assigned_to_user_id) : null;
        $team = $request->assigned_to_team_id ? Team::find($request->assigned_to_team_id) : null;

        $this->ticketService->assign($ticket, $assignee, $team, $request->user());

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }

    public function escalate(Request $request, Ticket $ticket)
    {
        $request->validate(['level' => 'required|integer|min:1|max:5']);

        $this->ticketService->escalate($ticket, $request->level, $request->user());

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', 'Ticket escalated.');
    }

    public function merge(Request $request, Ticket $ticket)
    {
        $request->validate(['duplicate_ticket_id' => 'required|exists:tickets,id']);

        $duplicate = Ticket::findOrFail($request->duplicate_ticket_id);
        $this->ticketService->merge($ticket, $duplicate, $request->user());

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', "Ticket {$duplicate->ticket_number} merged into {$ticket->ticket_number}.");
    }
}
