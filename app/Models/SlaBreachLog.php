<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaBreachLog extends Model
{
    protected $fillable = [
        'ticket_id', 'sla_plan_id', 'breach_type',
        'target_minutes', 'actual_minutes', 'breached_at',
    ];

    protected $casts = [
        'breached_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function slaPlan(): BelongsTo
    {
        return $this->belongsTo(SlaPlan::class);
    }
}
