<?php

namespace Tests\Feature;

use App\Models\EmailMailbox;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailboxManagementTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->org = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);
        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->org->id, 'is_active' => true,
        ]);
        $this->admin->assignRole('msp_admin');
    }

    public function test_admin_can_create_graph_mailbox_with_encrypted_secret(): void
    {
        $this->actingAs($this->admin)
            ->post(route('staff.mailboxes.store'), [
                'name' => 'Shared Support',
                'email_address' => 'support@company.com',
                'organization_id' => $this->org->id,
                'driver' => 'microsoft_graph',
                'graph_tenant_id' => 'tenant-abc',
                'graph_client_id' => 'client-abc',
                'graph_client_secret' => 'super-secret',
                'graph_user_id' => 'support@company.com',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $mailbox = EmailMailbox::firstWhere('email_address', 'support@company.com');
        $this->assertNotNull($mailbox);
        $this->assertTrue($mailbox->isGraph());
        $this->assertSame('super-secret', $mailbox->graph_client_secret);

        // Stored ciphertext must not be the plaintext, and must decrypt back to it.
        $raw = DB::table('email_mailboxes')->where('id', $mailbox->id)->value('graph_client_secret');
        $this->assertNotSame('super-secret', $raw);
        $this->assertSame('super-secret', Crypt::decryptString($raw));
    }

    public function test_graph_mailbox_requires_graph_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('staff.mailboxes.store'), [
                'name' => 'Incomplete',
                'email_address' => 'x@company.com',
                'driver' => 'microsoft_graph',
            ])
            ->assertSessionHasErrors(['graph_tenant_id', 'graph_client_id', 'graph_client_secret', 'graph_user_id']);
    }

    public function test_update_without_secret_keeps_stored_value(): void
    {
        $mailbox = EmailMailbox::create([
            'organization_id' => $this->org->id,
            'name' => 'Shared', 'email_address' => 'support@company.com',
            'driver' => 'microsoft_graph',
            'graph_tenant_id' => 't', 'graph_client_id' => 'c',
            'graph_client_secret' => 'original-secret', 'graph_user_id' => 'support@company.com',
        ]);

        $this->actingAs($this->admin)
            ->put(route('staff.mailboxes.update', $mailbox), [
                'name' => 'Shared Renamed',
                'email_address' => 'support@company.com',
                'driver' => 'microsoft_graph',
                'graph_tenant_id' => 't', 'graph_client_id' => 'c',
                'graph_client_secret' => '', // left blank
                'graph_user_id' => 'support@company.com',
            ])
            ->assertRedirect();

        $mailbox->refresh();
        $this->assertSame('Shared Renamed', $mailbox->name);
        $this->assertSame('original-secret', $mailbox->graph_client_secret);
    }

    public function test_non_admin_cannot_access_mailboxes(): void
    {
        $tech = User::create([
            'name' => 'Tech', 'email' => 'tech@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->org->id, 'is_active' => true,
        ]);
        $tech->assignRole('msp_technician');

        $this->actingAs($tech)->get(route('staff.mailboxes.index'))->assertForbidden();
    }
}
