<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queue extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function mailboxes(): HasMany
    {
        return $this->hasMany(EmailMailbox::class);
    }
}
