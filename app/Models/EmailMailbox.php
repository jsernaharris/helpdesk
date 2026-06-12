<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailMailbox extends Model
{
    use BelongsToOrganization;

    public const DRIVER_IMAP = 'imap';
    public const DRIVER_GRAPH = 'microsoft_graph';

    protected $fillable = [
        'organization_id', 'queue_id', 'name', 'email_address', 'driver',
        'imap_host', 'imap_port', 'imap_encryption', 'imap_username', 'imap_password',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
        'graph_tenant_id', 'graph_client_id', 'graph_client_secret', 'graph_user_id',
        'is_active', 'last_fetched_at', 'auto_create_tickets',
        'default_priority', 'default_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_create_tickets' => 'boolean',
        'last_fetched_at' => 'datetime',
        'imap_password' => 'encrypted',
        'smtp_password' => 'encrypted',
        'graph_client_secret' => 'encrypted',
    ];

    protected $hidden = [
        'imap_password', 'smtp_password', 'graph_client_secret',
    ];

    public function isGraph(): bool
    {
        return $this->driver === self::DRIVER_GRAPH;
    }

    public function isImap(): bool
    {
        return $this->driver === self::DRIVER_IMAP;
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }
}
