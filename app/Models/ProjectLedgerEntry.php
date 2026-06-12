<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLedgerEntry extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'project_id', 'organization_id', 'user_id',
        'type', 'description', 'is_internal', 'metadata',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'metadata' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleToCustomer($query)
    {
        return $query->where('is_internal', false);
    }
}
