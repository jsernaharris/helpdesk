<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id', 'change_number', 'type', 'risk_level',
        'implementation_plan', 'rollback_plan', 'test_plan',
        'scheduled_start_at', 'scheduled_end_at', 'actual_start_at', 'actual_end_at',
        'status', 'approved_by_user_id', 'approved_at',
        'cab_required', 'cab_notes',
    ];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'approved_at' => 'datetime',
        'cab_required' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
