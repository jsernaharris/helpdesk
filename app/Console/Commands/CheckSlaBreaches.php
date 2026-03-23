<?php

namespace App\Console\Commands;

use App\Events\SlaBreached;
use App\Events\SlaWarning;
use App\Services\SlaService;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    protected $signature = 'helpdesk:check-sla';
    protected $description = 'Check for SLA breaches and warnings';

    public function handle(SlaService $slaService): int
    {
        $breached = $slaService->checkBreaches();
        foreach ($breached as $ticket) {
            event(new SlaBreached($ticket));
        }
        $this->info("SLA breaches found: {$breached->count()}");

        $warnings = $slaService->getWarnings();
        foreach ($warnings as $ticket) {
            event(new SlaWarning($ticket));
        }
        $this->info("SLA warnings found: {$warnings->count()}");

        return self::SUCCESS;
    }
}
