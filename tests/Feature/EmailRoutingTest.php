<?php

namespace Tests\Feature;

use App\Models\EmailMailbox;
use App\Models\FormTemplate;
use App\Models\Organization;
use App\Models\Queue;
use App\Models\User;
use App\Services\EmailProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailRoutingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $msp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->msp = Organization::create([
            'name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true,
        ]);
    }

    private function mailbox(?int $queueId = null): EmailMailbox
    {
        return EmailMailbox::create([
            'organization_id' => $this->msp->id,
            'queue_id' => $queueId,
            'name' => 'Shared Support', 'email_address' => 'support@msp.test',
            'driver' => EmailMailbox::DRIVER_IMAP,
            'imap_host' => 'h', 'imap_username' => 'u', 'imap_password' => 'p',
            'smtp_host' => 'h', 'smtp_username' => 'u', 'smtp_password' => 'p',
        ]);
    }

    public function test_inbound_mail_routes_to_org_owning_any_of_its_domains(): void
    {
        $harris = Organization::create(['name' => 'Harris', 'slug' => 'harris', 'is_active' => true]);
        $harris->domains()->create(['domain' => 'harriscomputer.com']);
        $harris->domains()->create(['domain' => 'stchealth.com']);

        $service = app(EmailProcessingService::class);

        // A sibling domain (not the "primary") still resolves to the same org.
        $ticket = $service->process([
            'from' => 'jane@stchealth.com',
            'subject' => 'Printer down',
            'body' => 'help',
        ], $this->mailbox());

        $this->assertEquals($harris->id, $ticket->organization_id);
    }

    public function test_unmatched_domain_falls_back_to_mailbox_org(): void
    {
        $service = app(EmailProcessingService::class);

        $ticket = $service->process([
            'from' => 'someone@unknown.test',
            'subject' => 'Hello',
            'body' => 'help',
        ], $this->mailbox());

        $this->assertEquals($this->msp->id, $ticket->organization_id);
    }

    public function test_ticket_inherits_the_mailbox_default_queue(): void
    {
        $queue = Queue::create([
            'organization_id' => $this->msp->id, 'name' => 'Cybersecurity', 'is_active' => true,
        ]);

        $service = app(EmailProcessingService::class);

        $ticket = $service->process([
            'from' => 'someone@unknown.test',
            'subject' => 'Phishing report',
            'body' => 'help',
        ], $this->mailbox($queue->id));

        $this->assertEquals($queue->id, $ticket->queue_id);
    }

    public function test_portal_form_submission_routes_to_the_forms_queue(): void
    {
        $client = Organization::create(['name' => 'Client', 'slug' => 'client', 'is_active' => true]);
        $queue = Queue::create([
            'organization_id' => $client->id, 'name' => 'AI', 'is_active' => true,
        ]);
        $template = FormTemplate::create([
            'name' => 'AI Request', 'organization_id' => $client->id, 'queue_id' => $queue->id,
            'is_active' => true, 'fields' => [['key' => 'detail', 'label' => 'Detail', 'type' => 'text']],
        ]);
        $user = User::create([
            'name' => 'Cust', 'email' => 'cust@client.test', 'password' => bcrypt('secret'),
            'organization_id' => $client->id, 'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('portal.tickets.store'), [
            'subject' => 'Need an AI model',
            'description' => 'please',
            'form_template_id' => $template->id,
            'custom_fields' => ['detail' => 'gpt'],
        ]);

        $response->assertRedirect();
        $ticket = \App\Models\Ticket::where('requester_user_id', $user->id)->latest('id')->first();

        $this->assertNotNull($ticket);
        $this->assertEquals($queue->id, $ticket->queue_id);
        $this->assertEquals($template->id, $ticket->form_template_id);
    }
}
