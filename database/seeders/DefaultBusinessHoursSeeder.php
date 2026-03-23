<?php

namespace Database\Seeders;

use App\Models\BusinessHourPeriod;
use App\Models\BusinessHours;
use Illuminate\Database\Seeder;

class DefaultBusinessHoursSeeder extends Seeder
{
    public function run(): void
    {
        $hours = BusinessHours::firstOrCreate(
            ['name' => 'Default Business Hours'],
            [
                'timezone' => config('helpdesk.default_timezone', 'America/Chicago'),
                'is_default' => true,
            ]
        );

        // Mon-Fri 8am-6pm
        for ($day = 1; $day <= 5; $day++) {
            BusinessHourPeriod::firstOrCreate(
                ['business_hours_id' => $hours->id, 'day_of_week' => $day],
                ['start_time' => '08:00', 'end_time' => '18:00']
            );
        }
    }
}
