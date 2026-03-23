<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MspOrganizationSeeder::class,
            DefaultSlaPlanSeeder::class,
            DefaultBusinessHoursSeeder::class,
            DemoDataSeeder::class,
            ChangeManagementSeeder::class,
        ]);
    }
}
