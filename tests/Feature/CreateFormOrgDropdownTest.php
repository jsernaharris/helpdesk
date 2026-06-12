<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateFormOrgDropdownTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        // An MSP-only environment: the only org is the MSP itself.
        $msp = Organization::create(['name' => 'Harris MSP', 'slug' => 'harris', 'is_msp' => true, 'is_active' => true]);
        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@harris.test', 'password' => bcrypt('secret'),
            'organization_id' => $msp->id, 'is_active' => true,
        ]);
        $this->admin->assignRole('msp_admin');
    }

    public function test_ticket_create_lists_org_even_when_only_msp_exists(): void
    {
        $this->actingAs($this->admin)->get(route('staff.tickets.create'))
            ->assertOk()
            ->assertSee('Harris MSP');
    }

    public function test_change_create_lists_org_even_when_only_msp_exists(): void
    {
        $this->actingAs($this->admin)->get(route('staff.changes.create'))
            ->assertOk()
            ->assertSee('Harris MSP');
    }
}
