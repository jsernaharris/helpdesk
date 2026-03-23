<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChangeCategory extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'description', 'default_type', 'default_risk_level',
        'template_implementation_plan', 'template_rollback_plan', 'template_test_plan',
        'cab_required', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'cab_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }
}
