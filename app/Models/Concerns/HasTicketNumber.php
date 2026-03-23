<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasTicketNumber
{
    public static function bootHasTicketNumber(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->ticket_number)) {
                $model->ticket_number = static::generateTicketNumber($model->type ?? 'incident');
            }
        });
    }

    public static function generateTicketNumber(string $type): string
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
}
