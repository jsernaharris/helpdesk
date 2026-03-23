<?php

namespace App\Console\Commands;

use App\Services\EscalationService;
use Illuminate\Console\Command;

class RunEscalations extends Command
{
    protected $signature = 'helpdesk:run-escalations';
    protected $description = 'Run escalation rules against open tickets';

    public function handle(EscalationService $escalationService): int
    {
        $count = $escalationService->runEscalations();
        $this->info("Escalations applied: {$count}");

        return self::SUCCESS;
    }
}
