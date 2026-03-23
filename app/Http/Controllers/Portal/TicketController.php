<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TicketReplyRequest;
use App\Models\ServiceCatalog;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::where('organization_id', $user->organization_id);

        if ($user->isCustomerUser()) {
            $query->where('requester_user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->with(['assignedTo'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('portal.tickets.index', compact('tickets'));
    }

    public function create(Request $request)
    {
        $services = ServiceCatalog::where('organization_id', $request->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('portal.tickets.create', compact('services'));
    }

    public function store(StoreTicketRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $data['requester_user_id'] = $request->user()->id;
        $data['source'] = 'portal';
        $data['type'] = $data['type'] ?? 'incident';

        $ticket = $this->ticketService->create($data, $request->user());

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

        return redirect()->route('portal.tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} submitted successfully.");
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Authorization
        if ($ticket->organization_id !== $user->organization_id) {
            abort(403);
        }
        if ($user->isCustomerUser() && $ticket->requester_user_id !== $user->id) {
            abort(403);
        }

        $ticket->load([
            'assignedTo', 'serviceCatalog',
            'threads' => function ($query) {
                $query->where('is_internal', false)->with(['user', 'contact', 'attachments']);
            },
            'attachments',
        ]);

        return view('portal.tickets.show', compact('ticket'));
    }

    public function reply(TicketReplyRequest $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($ticket->organization_id !== $user->organization_id) {
            abort(403);
        }
        if ($user->isCustomerUser() && $ticket->requester_user_id !== $user->id) {
            abort(403);
        }

        $thread = $this->ticketService->addReply($ticket, $user, $request->body, false);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/threads/' . $thread->id, 'local');
                $thread->attachments()->create([
                    'user_id' => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('portal.tickets.show', $ticket)
            ->with('success', 'Reply added.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($ticket->organization_id !== $user->organization_id) {
            abort(403);
        }

        $this->ticketService->changeStatus($ticket, 'closed', $user);

        return redirect()->route('portal.tickets.show', $ticket)
            ->with('success', 'Ticket closed.');
    }
}
