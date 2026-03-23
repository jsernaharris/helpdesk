<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'asset_tag', 'type',
        'serial_number', 'status', 'assigned_to_user_id', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class)->withTimestamps();
    }
}
