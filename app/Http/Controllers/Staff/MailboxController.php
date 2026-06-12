<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMailboxRequest;
use App\Http\Requests\UpdateMailboxRequest;
use App\Models\EmailMailbox;
use App\Models\Organization;
use App\Models\Queue;
use App\Services\Mail\MailboxDriverManager;

class MailboxController extends Controller
{
    public function index()
    {
        $mailboxes = EmailMailbox::with('organization')
            ->orderBy('name')
            ->paginate(25);

        return view('staff.mailboxes.index', compact('mailboxes'));
    }

    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $queues = $this->activeQueues();

        return view('staff.mailboxes.create', compact('organizations', 'queues'));
    }

    public function store(StoreMailboxRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['auto_create_tickets'] = $request->boolean('auto_create_tickets');

        $mailbox = EmailMailbox::create($data);

        return redirect()->route('staff.mailboxes.show', $mailbox)
            ->with('success', 'Mailbox created successfully.');
    }

    public function show(EmailMailbox $mailbox)
    {
        $mailbox->load('organization');

        return view('staff.mailboxes.show', compact('mailbox'));
    }

    public function edit(EmailMailbox $mailbox)
    {
        $organizations = Organization::orderBy('name')->get();
        $queues = $this->activeQueues();

        return view('staff.mailboxes.edit', compact('mailbox', 'organizations', 'queues'));
    }

    /**
     * Active queues grouped by organization id, for the mailbox queue picker.
     */
    private function activeQueues()
    {
        return Queue::where('is_active', true)
            ->with('organization:id,name')
            ->orderBy('name')
            ->get()
            ->groupBy('organization_id');
    }

    public function update(UpdateMailboxRequest $request, EmailMailbox $mailbox)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['auto_create_tickets'] = $request->boolean('auto_create_tickets');

        // Leave secrets untouched when the field is submitted blank.
        foreach (['imap_password', 'smtp_password', 'graph_client_secret'] as $secret) {
            if (blank($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }

        $mailbox->update($data);

        return redirect()->route('staff.mailboxes.show', $mailbox)
            ->with('success', 'Mailbox updated successfully.');
    }

    public function destroy(EmailMailbox $mailbox)
    {
        $mailbox->delete();

        return redirect()->route('staff.mailboxes.index')
            ->with('success', 'Mailbox deleted.');
    }

    public function test(EmailMailbox $mailbox, MailboxDriverManager $drivers)
    {
        try {
            $drivers->for($mailbox)->testConnection($mailbox);

            return back()->with('success', "Connection to '{$mailbox->name}' succeeded.");
        } catch (\Throwable $e) {
            return back()->with('error', "Connection failed: {$e->getMessage()}");
        }
    }
}
