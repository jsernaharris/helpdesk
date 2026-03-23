<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ChangePolicy extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'require_cab_for_normal', 'require_cab_for_standard',
        'require_cab_for_emergency', 'min_lead_time_hours', 'emergency_lead_time_hours',
        'require_rollback_plan', 'require_test_plan', 'require_implementation_plan',
        'allow_customer_submit', 'auto_approve_standard', 'change_window_notes', 'settings',
    ];

    protected $casts = [
        'require_cab_for_normal' => 'boolean',
        'require_cab_for_standard' => 'boolean',
        'require_cab_for_emergency' => 'boolean',
        'require_rollback_plan' => 'boolean',
        'require_test_plan' => 'boolean',
        'require_implementation_plan' => 'boolean',
        'allow_customer_submit' => 'boolean',
        'auto_approve_standard' => 'boolean',
        'settings' => 'array',
    ];

    public function cabRequiredForType(string $type): bool
    {
        return match ($type) {
            'standard' => $this->require_cab_for_standard,
            'normal' => $this->require_cab_for_normal,
            'emergency' => $this->require_cab_for_emergency,
            default => true,
        };
    }
}
