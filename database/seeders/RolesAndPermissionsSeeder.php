<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'tickets.view_own', 'tickets.view_org', 'tickets.view_all',
            'tickets.create', 'tickets.update', 'tickets.assign', 'tickets.delete',
            'tickets.merge', 'tickets.escalate', 'tickets.add_internal_note',
            'problems.view', 'problems.create', 'problems.update', 'problems.link_incidents',
            'changes.view', 'changes.create', 'changes.update', 'changes.approve', 'changes.reject',
            'kb.view_public', 'kb.view_internal', 'kb.create', 'kb.update', 'kb.delete',
            'organizations.view', 'organizations.create', 'organizations.update', 'organizations.delete',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'teams.manage', 'sla.manage', 'settings.manage', 'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // MSP Admin - full access
        $mspAdmin = Role::firstOrCreate(['name' => 'msp_admin']);
        $mspAdmin->givePermissionTo(Permission::all());

        // MSP Technician - everything except destructive admin actions
        $mspTech = Role::firstOrCreate(['name' => 'msp_technician']);
        $mspTech->givePermissionTo([
            'tickets.view_all', 'tickets.create', 'tickets.update', 'tickets.assign',
            'tickets.merge', 'tickets.escalate', 'tickets.add_internal_note',
            'problems.view', 'problems.create', 'problems.update', 'problems.link_incidents',
            'changes.view', 'changes.create', 'changes.update',
            'kb.view_public', 'kb.view_internal', 'kb.create', 'kb.update',
            'organizations.view', 'users.view', 'reports.view',
        ]);

        // Customer Admin - org-level visibility
        $customerAdmin = Role::firstOrCreate(['name' => 'customer_admin']);
        $customerAdmin->givePermissionTo([
            'tickets.view_org', 'tickets.create',
            'kb.view_public',
            'organizations.view', 'users.view',
        ]);

        // Customer User - own tickets only
        $customerUser = Role::firstOrCreate(['name' => 'customer_user']);
        $customerUser->givePermissionTo([
            'tickets.view_own', 'tickets.create',
            'kb.view_public',
        ]);
    }
}
