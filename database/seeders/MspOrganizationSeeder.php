<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MspOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $msp = Organization::firstOrCreate(
            ['slug' => 'msp-helpdesk'],
            [
                'name' => 'MSP Helpdesk',
                'is_msp' => true,
                'email_domain' => 'msphelpdesk.com',
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@msphelpdesk.com'],
            [
                'name' => 'System Admin',
                'organization_id' => $msp->id,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('msp_admin');

        $tech = User::firstOrCreate(
            ['email' => 'tech@msphelpdesk.com'],
            [
                'name' => 'Support Technician',
                'organization_id' => $msp->id,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $tech->assignRole('msp_technician');
    }
}
