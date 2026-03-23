<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('helpdesk:fetch-emails')->everyMinute()->withoutOverlapping();
Schedule::command('helpdesk:check-sla')->everyFiveMinutes();
Schedule::command('helpdesk:auto-close-resolved')->daily();
Schedule::command('helpdesk:run-escalations')->everyFiveMinutes();
