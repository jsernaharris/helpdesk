<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'email', 'phone', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_contact_id');
    }

    public function ticketThreads(): HasMany
    {
        return $this->hasMany(TicketThread::class);
    }
}
