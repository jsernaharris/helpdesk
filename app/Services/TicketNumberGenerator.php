<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TicketNumberGenerator
{
    public function generate(string $type): string
    {
        $prefixes = config('helpdesk.ticket_prefixes', [
            'incident' => 'INC',
            'service_request' => 'SR',
            'problem' => 'PRB',
            'change' => 'CHG',
        ]);

        $prefix = $prefixes[$type] ?? 'TKT';

        $lastNumber = DB::table('tickets')
            ->where('ticket_number', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(ticket_number, ?) AS UNSIGNED) DESC', [strlen($prefix) + 2])
            ->value('ticket_number');

        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, strlen($prefix) + 1) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%06d', $prefix, $sequence);
    }

    public function generateChangeNumber(): string
    {
        $lastNumber = DB::table('change_requests')
            ->orderByRaw('CAST(SUBSTRING(change_number, 5) AS UNSIGNED) DESC')
            ->value('change_number');

        $sequence = $lastNumber ? (int) substr($lastNumber, 4) + 1 : 1;

        return sprintf('CHR-%06d', $sequence);
    }
}
