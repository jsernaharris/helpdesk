<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscalationRule extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'sla_plan_id', 'organization_id', 'name', 'trigger_type',
        'trigger_minutes_before', 'escalation_level', 'action_type',
        'action_target', 'is_active',
    ];

    protected $casts = [
        'action_target' => 'array',
        'is_active' => 'boolean',
    ];

    public function slaPlan(): BelongsTo
    {
        return $this->belongsTo(SlaPlan::class);
    }
}
