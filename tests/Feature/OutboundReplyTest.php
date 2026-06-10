<?php

namespace Tests\Feature;

use App\Jobs\SendOutboundReply;
use App\Models\Contact;
use App\Models\EmailMailbox;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketThread;
use App\Models\User;
use App\Services\Mail\MailboxDriverManager;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutboundReplyTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);
        $this->staff = User::create([
            'name' => 'Tech', 'email' => 'tech@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->org->id, 'is_active' => true,
        ]);
    }

    private function graphMailbox(): EmailMailbox
    {
        return EmailMailbox::create([
            'organization_id' => $this->org->id,
            'name' => 'Shared Support', 'email_address' => 'support@company.com',
            'driver' => EmailMailbox::DRIVER_GRAPH,
            'graph_tenant_id' => 't', 'graph_client_id' => 'c',
            'graph_client_secret' => 's', 'graph_user_id' => 'support@company.com',
        ]);
    }

    private function emailTicket(?EmailMailbox $mailbox): Ticket
    {
        $contact = Contact::create([
            'organization_id' => $this->org->id, 'name' => 'Cust', 'email' => 'user@client.com',
        ]);

        return Ticket::create([
            'ticket_number' => 'INC-1', 'organization_id' => $this->org->id,
            'requester_contact_id' => $contact->id, 'subject' => 'Need help',
            'description' => 'help', 'type' => 'incident', 'status' => 'open',
            'priority' => 'medium', 'source' => 'email',
            'email_mailbox_id' => $mailbox?->id,
        ]);
    }

    public function test_reply_on_email_ticket_dispatches_outbound_send(): void
    {
        Bus::fake();
        $ticket = $this->emailTicket($this->graphMailbox());

        app(TicketService::class)->addReply($ticket, $this->staff, 'Here is the fix', false);

        Bus::assertDispatched(SendOutboundReply::class, fn ($job) => $job->ticket->is($ticket));
    }

    public function test_internal_note_does_not_dispatch_send(): void
    {
        Bus::fake();
        $ticket = $this->emailTicket($this->graphMailbox());

        app(TicketService::class)->addReply($ticket, $this->staff, 'private note', true);

        Bus::assertNotDispatched(SendOutboundReply::class);
    }

    public function test_web_ticket_does_not_dispatch_send(): void
    {
        Bus::fake();
        $ticket = $this->emailTicket(null); // no mailbox

        app(TicketService::class)->addReply($ticket, $this->staff, 'reply', false);

        Bus::assertNotDispatched(SendOutboundReply::class);
    }

    public function test_job_sends_via_graph_and_records_message_id(): void
    {
        Http::fake([
            '*oauth2/v2.0/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/messages/*/send' => Http::response([], 202),
            '*/messages' => Http::response(['id' => 'draft-9', 'internetMessageId' => '<out-9@company.com>'], 201),
        ]);

        $mailbox = $this->graphMailbox();
        $ticket = $this->emailTicket($mailbox);
        $thread = TicketThread::create([
            'ticket_id' => $ticket->id, 'user_id' => $this->staff->id,
            'type' => 'reply', 'body' => '<p>fixed</p>', 'is_internal' => false,
        ]);

        (new SendOutboundReply($ticket, $thread))->handle(app(MailboxDriverManager::class));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/messages/draft-9/send'));
        $this->assertSame('<out-9@company.com>', $thread->fresh()->email_message_id);
    }
}
