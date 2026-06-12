<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Services\TicketNumberGenerator;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $numbers = app(TicketNumberGenerator::class);
        $admin = User::where('email', 'admin@msphelpdesk.com')->first();
        $tech = User::where('email', 'tech@msphelpdesk.com')->first();

        $orgs = Organization::where('is_msp', false)->where('is_active', true)->get();

        foreach ($orgs as $org) {
            if (Project::where('organization_id', $org->id)->exists()) {
                continue;
            }

            $project = Project::create([
                'project_number' => $numbers->generateProjectNumber(),
                'organization_id' => $org->id,
                'name' => 'Server Patching',
                'description' => 'Quarterly OS patching and reboot of production servers.',
                'status' => 'active',
                'start_date' => now()->subDays(5),
                'due_date' => now()->addDays(10),
                'created_by_user_id' => $admin?->id,
            ]);

            $members = collect([$admin, $tech])->filter();
            $project->members()->sync(
                $members->mapWithKeys(fn ($u, $i) => [$u->id => ['is_lead' => $i === 0]])->all()
            );

            // A couple of sample time entries.
            if ($tech) {
                foreach ([['days' => 3, 'minutes' => 90], ['days' => 1, 'minutes' => 120]] as $entry) {
                    $project->timeEntries()->create([
                        'organization_id' => $org->id,
                        'user_id' => $tech->id,
                        'work_date' => now()->subDays($entry['days']),
                        'minutes' => $entry['minutes'],
                        'notes' => 'Patched and rebooted a batch of servers.',
                    ]);
                }
            }
        }
    }
}
