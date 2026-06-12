<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeEntry extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'project_id', 'organization_id', 'user_id', 'ticket_id',
        'work_date', 'minutes', 'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'minutes' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function getHoursAttribute(): float
    {
        return round($this->minutes / 60, 2);
    }
}
