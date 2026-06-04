<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private Organization $mspOrg;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->mspOrg = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->mspOrg->id, 'is_active' => true,
        ]);
        $this->admin->assignRole('msp_admin');
    }

    public function test_admin_can_create_custom_role_with_permissions(): void
    {
        $this->actingAs($this->admin)
            ->post(route('staff.roles.store'), [
                'name' => 'site_a_tech',
                'permissions' => ['tickets.view_all', 'tickets.update'],
            ])
            ->assertRedirect(route('staff.roles.index'));

        $role = Role::findByName('site_a_tech');
        $this->assertTrue($role->hasPermissionTo('tickets.view_all'));
        $this->assertTrue($role->hasPermissionTo('tickets.update'));
        $this->assertFalse($role->hasPermissionTo('tickets.delete'));
    }

    public function test_invalid_role_name_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('staff.roles.store'), ['name' => 'Site A Tech'])
            ->assertSessionHasErrors('name');
    }

    public function test_builtin_role_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('staff.roles.destroy', Role::findByName('msp_technician')))
            ->assertSessionHas('error');

        $this->assertTrue(Role::where('name', 'msp_technician')->exists());
    }

    public function test_protected_role_name_is_fixed_on_update_but_permissions_change(): void
    {
        $role = Role::findByName('customer_user');

        $this->actingAs($this->admin)
            ->put(route('staff.roles.update', $role), [
                'name' => 'renamed',
                'permissions' => ['tickets.view_own'],
            ])
            ->assertRedirect(route('staff.roles.index'));

        $this->assertTrue(Role::where('name', 'customer_user')->exists());
        $this->assertFalse(Role::where('name', 'renamed')->exists());
        $this->assertFalse($role->fresh()->hasPermissionTo('tickets.create'));
    }

    public function test_non_admin_technician_cannot_manage_roles(): void
    {
        $tech = User::create([
            'name' => 'Tech', 'email' => 'tech@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->mspOrg->id, 'is_active' => true,
        ]);
        $tech->assignRole('msp_technician');

        $this->actingAs($tech)->get(route('staff.roles.index'))->assertForbidden();
    }

    public function test_user_can_be_assigned_multiple_roles_with_scoped_orgs(): void
    {
        $siteA = Organization::create(['name' => 'Site A', 'slug' => 'site-a', 'is_msp' => false, 'is_active' => true]);
        $target = User::create([
            'name' => 'Multi', 'email' => 'multi@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $this->mspOrg->id, 'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('staff.users.update', $target), [
                'name' => 'Multi', 'email' => 'multi@msp.test',
                'organization_id' => $this->mspOrg->id,
                'roles' => ['msp_technician', 'customer_admin'],
                'access_mode' => 'specific',
                'accessible_orgs' => [$siteA->id],
            ])
            ->assertRedirect(route('staff.users.show', $target));

        $target->refresh();
        $this->assertTrue($target->hasRole('msp_technician'));
        $this->assertTrue($target->hasRole('customer_admin'));
        $this->assertSame([$siteA->id], $target->accessibleOrgIds());
    }
}
