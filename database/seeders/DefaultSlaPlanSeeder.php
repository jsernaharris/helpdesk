<?php

namespace Database\Seeders;

use App\Models\SlaPlan;
use App\Models\SlaTarget;
use Illuminate\Database\Seeder;

class DefaultSlaPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plan = SlaPlan::firstOrCreate(
            ['name' => 'Default SLA'],
            [
                'description' => 'Default SLA plan for all organizations',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $targets = [
            ['priority' => 'critical', 'response_time_minutes' => 15, 'resolution_time_minutes' => 240],
            ['priority' => 'high', 'response_time_minutes' => 60, 'resolution_time_minutes' => 480],
            ['priority' => 'medium', 'response_time_minutes' => 240, 'resolution_time_minutes' => 1440],
            ['priority' => 'low', 'response_time_minutes' => 480, 'resolution_time_minutes' => 2880],
        ];

        foreach ($targets as $target) {
            SlaTarget::firstOrCreate(
                ['sla_plan_id' => $plan->id, 'priority' => $target['priority']],
                [
                    'response_time_minutes' => $target['response_time_minutes'],
                    'resolution_time_minutes' => $target['resolution_time_minutes'],
                    'business_hours_only' => true,
                ]
            );
        }
    }
}
