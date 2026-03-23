<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ChangeBlackoutPeriod extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'reason', 'starts_at', 'ends_at',
        'allow_emergency', 'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'allow_emergency' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function isActiveNow(): bool
    {
        $now = now();
        return $this->is_active && $now->between($this->starts_at, $this->ends_at);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        $now = now();
        return $query->active()->where('starts_at', '<=', $now)->where('ends_at', '>=', $now);
    }

    public function scopeUpcoming($query)
    {
        return $query->active()->where('starts_at', '>', now())->orderBy('starts_at');
    }
}
