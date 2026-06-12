<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTimeTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $msp;
    private Organization $client;
    private User $admin;
    private User $tech;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->msp = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);
        $this->client = Organization::create(['name' => 'Acme', 'slug' => 'acme', 'is_active' => true]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->msp->id, 'is_active' => true,
        ]);
        $this->admin->assignRole('msp_admin');

        $this->tech = User::create([
            'name' => 'Tech', 'email' => 'tech@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->msp->id, 'is_active' => true,
        ]);
        $this->tech->assignRole('msp_technician');
    }

    public function test_admin_creates_project_with_sequential_number_and_members(): void
    {
        $this->actingAs($this->admin)->post(route('staff.projects.store'), [
            'name' => 'Server Patching',
            'organization_id' => $this->client->id,
            'status' => 'active',
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@acme.test',
            'members' => [$this->tech->id],
        ])->assertRedirect();

        $project = Project::firstWhere('name', 'Server Patching');
        $this->assertNotNull($project);
        $this->assertSame('PRJ-000001', $project->project_number);
        $this->assertSame($this->client->id, $project->organization_id);
        $this->assertSame('Jane Doe', $project->customer_name);
        $this->assertSame('jane@acme.test', $project->customer_email);
        $this->assertTrue($project->members->contains($this->tech->id));
    }

    public function test_create_form_lists_msp_org_so_a_project_can_be_filed(): void
    {
        // The MSP org must be selectable too, otherwise an MSP-only environment
        // has an empty dropdown and can't create any project.
        $this->actingAs($this->admin)->get(route('staff.projects.create'))
            ->assertOk()
            ->assertSee('MSP');
    }

    public function test_logging_time_sums_hours_and_supports_ticket_link(): void
    {
        $project = Project::create([
            'project_number' => 'PRJ-000001', 'organization_id' => $this->client->id,
            'name' => 'Patching', 'status' => 'active', 'created_by_user_id' => $this->admin->id,
        ]);
        $ticket = Ticket::create([
            'ticket_number' => 'INC-1', 'organization_id' => $this->client->id,
            'subject' => 'x', 'description' => 'y', 'type' => 'incident',
            'status' => 'open', 'priority' => 'medium', 'source' => 'portal',
        ]);

        $this->actingAs($this->tech)->post(route('staff.projects.time.store', $project), [
            'user_id' => $this->tech->id, 'work_date' => '2026-06-10', 'hours' => '1.5',
        ])->assertRedirect();

        $this->actingAs($this->tech)->post(route('staff.projects.time.store', $project), [
            'user_id' => $this->tech->id, 'work_date' => '2026-06-11', 'hours' => '0.5',
            'ticket_id' => $ticket->id, 'notes' => 'wrap up',
        ])->assertRedirect();

        $this->assertSame(120, $project->fresh()->totalMinutes());
        $this->assertSame(2.0, $project->fresh()->totalHours());
        $this->assertDatabaseHas('project_time_entries', [
            'project_id' => $project->id, 'ticket_id' => $ticket->id, 'minutes' => 30,
            'organization_id' => $this->client->id,
        ]);
    }

    public function test_export_returns_csv(): void
    {
        $project = Project::create([
            'project_number' => 'PRJ-000001', 'organization_id' => $this->client->id,
            'name' => 'Patching', 'status' => 'active',
        ]);
        $project->timeEntries()->create([
            'organization_id' => $this->client->id, 'user_id' => $this->tech->id,
            'work_date' => '2026-06-10', 'minutes' => 90, 'notes' => 'work',
        ]);

        $response = $this->actingAs($this->admin)->get(route('staff.projects.time.export'));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString('PRJ-000001', $csv);
        $this->assertStringContainsString('Tech', $csv);
    }

    public function test_ledger_records_events_and_manual_notes(): void
    {
        // Create via the controller so the 'created' event is recorded.
        $this->actingAs($this->admin)->post(route('staff.projects.store'), [
            'name' => 'Patching', 'organization_id' => $this->client->id, 'status' => 'planned',
        ])->assertRedirect();
        $project = Project::firstWhere('name', 'Patching');

        // Status change -> auto event.
        $this->actingAs($this->admin)->put(route('staff.projects.update', $project), [
            'name' => 'Patching', 'organization_id' => $this->client->id, 'status' => 'active',
        ])->assertRedirect();

        // Logging time -> auto (internal) event.
        $this->actingAs($this->tech)->post(route('staff.projects.time.store', $project), [
            'user_id' => $this->tech->id, 'work_date' => '2026-06-10', 'hours' => '1',
        ])->assertRedirect();

        // Manual public note + internal note.
        $this->actingAs($this->admin)->post(route('staff.projects.ledger.store', $project), [
            'description' => 'Rebooted the web tier.',
        ])->assertRedirect();
        $this->actingAs($this->admin)->post(route('staff.projects.ledger.store', $project), [
            'description' => 'Client still owes access creds.', 'is_internal' => '1',
        ])->assertRedirect();

        $types = $project->ledgerEntries()->pluck('type')->all();
        $this->assertContains('created', $types);
        $this->assertContains('status_changed', $types);
        $this->assertContains('time_logged', $types);
        $this->assertContains('note', $types);

        // Customer-visible feed excludes internal rows (time_logged + internal note).
        $visible = $project->ledgerEntries()->visibleToCustomer()->pluck('type')->all();
        $this->assertContains('status_changed', $visible);
        $this->assertContains('note', $visible);
        $this->assertNotContains('time_logged', $visible);
        $this->assertSame(1, $project->ledgerEntries()->visibleToCustomer()->where('type', 'note')->count());
    }

    public function test_customer_sees_only_their_org_projects_read_only(): void
    {
        $ourProject = Project::create([
            'project_number' => 'PRJ-000001', 'organization_id' => $this->client->id,
            'name' => 'Ours', 'status' => 'active',
        ]);
        $otherOrg = Organization::create(['name' => 'Other', 'slug' => 'other', 'is_active' => true]);
        $otherProject = Project::create([
            'project_number' => 'PRJ-000002', 'organization_id' => $otherOrg->id,
            'name' => 'Theirs', 'status' => 'active',
        ]);

        $customer = User::create([
            'name' => 'Cust', 'email' => 'cust@acme.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->client->id, 'is_active' => true,
        ]);
        $customer->assignRole('customer_admin');

        $this->actingAs($customer)->get(route('portal.projects.show', $ourProject))
            ->assertOk()->assertSee('Ours');

        // Another org's project must be inaccessible — hidden by the tenant scope
        // (404) or rejected by the ownership guard (403); never visible.
        $status = $this->actingAs($customer)->get(route('portal.projects.show', $otherProject))->status();
        $this->assertContains($status, [403, 404]);
    }
}
