<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaTarget extends Model
{
    protected $fillable = [
        'sla_plan_id', 'priority', 'response_time_minutes',
        'resolution_time_minutes', 'business_hours_only',
    ];

    protected $casts = [
        'business_hours_only' => 'boolean',
    ];

    public function slaPlan(): BelongsTo
    {
        return $this->belongsTo(SlaPlan::class);
    }
}
