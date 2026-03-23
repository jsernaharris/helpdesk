<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class EmailMailbox extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'email_address',
        'imap_host', 'imap_port', 'imap_encryption', 'imap_username', 'imap_password',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
        'is_active', 'last_fetched_at', 'auto_create_tickets',
        'default_priority', 'default_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_create_tickets' => 'boolean',
        'last_fetched_at' => 'datetime',
        'imap_password' => 'encrypted',
        'smtp_password' => 'encrypted',
    ];
}
