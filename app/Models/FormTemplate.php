<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'fields',
        'is_active',
        'organization_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableFor($query, ?int $organizationId)
    {
        return $query->active()->where(function ($q) use ($organizationId) {
            $q->whereNull('organization_id');
            if ($organizationId) {
                $q->orWhere('organization_id', $organizationId);
            }
        });
    }
}
