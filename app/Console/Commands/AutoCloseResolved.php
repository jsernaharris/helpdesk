<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class AutoCloseResolved extends Command
{
    protected $signature = 'helpdesk:auto-close-resolved';
    protected $description = 'Auto-close tickets that have been in resolved state for N days';

    public function handle(): int
    {
        $days = config('helpdesk.auto_close_resolved_after_days', 3);

        $tickets = Ticket::where('status', 'resolved')
            ->where('resolved_at', '<=', now()->subDays($days))
            ->get();

        foreach ($tickets as $ticket) {
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);
        }

        $this->info("Auto-closed {$tickets->count()} resolved tickets.");

        return self::SUCCESS;
    }
}
