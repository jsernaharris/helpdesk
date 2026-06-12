<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Creates the first MSP staff admin without needing interactive tinker.
 *
 * Reads its values from environment variables so credentials never live in the
 * repo. Run after RolesAndPermissionsSeeder:
 *
 *   ADMIN_NAME="Your Name" ADMIN_EMAIL="you@your-bu.example.com" \
 *   ADMIN_PASSWORD="a-strong-secret" \
 *     php artisan db:seed --class=FirstAdminSeeder
 *
 * Optional overrides: ORG_NAME, ORG_SLUG, ORG_EMAIL_DOMAIN (default to an MSP
 * org named "MSP Helpdesk"). Inline env vars work even when config is cached —
 * they are real process variables, not read from the .env file.
 */
class FirstAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $name || ! $email || ! $password) {
            $this->command->error('FirstAdminSeeder needs ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD.');
            $this->command->line('Example:');
            $this->command->line('  ADMIN_NAME="Your Name" ADMIN_EMAIL="you@example.com" ADMIN_PASSWORD="strong-secret" \\');
            $this->command->line('    php artisan db:seed --class=FirstAdminSeeder');

            return;
        }

        if (! Role::where('name', 'msp_admin')->exists()) {
            $this->command->error("The 'msp_admin' role does not exist yet. Seed roles first:");
            $this->command->line('  php artisan db:seed --class=RolesAndPermissionsSeeder');

            return;
        }

        $org = Organization::firstOrCreate(
            ['slug' => env('ORG_SLUG', 'msp')],
            [
                'name' => env('ORG_NAME', 'MSP Helpdesk'),
                'is_msp' => true,
                'email_domain' => env('ORG_EMAIL_DOMAIN'),
                'is_active' => true,
            ]
        );

        // A staff admin must live in an MSP org — refuse to attach to a customer org.
        if (! $org->is_msp) {
            $this->command->error("Organization '{$org->name}' is not an MSP org (is_msp=false); refusing to create a staff admin in it.");

            return;
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'organization_id' => $org->id,
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );

        $admin->assignRole('msp_admin');

        $this->command->info("Admin ready: {$admin->email} (msp_admin) in org '{$org->name}'.");

        if (! $admin->wasRecentlyCreated) {
            $this->command->warn('That email already existed — ensured the msp_admin role is assigned, but the password was NOT changed.');
        }
    }
}
