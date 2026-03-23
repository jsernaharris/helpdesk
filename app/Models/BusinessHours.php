<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessHours extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'timezone', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function periods(): HasMany
    {
        return $this->hasMany(BusinessHourPeriod::class, 'business_hours_id');
    }
}
