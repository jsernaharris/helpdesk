<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketThread extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id', 'user_id', 'contact_id', 'type', 'body',
        'is_internal', 'email_message_id',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        if ($this->contact) {
            return $this->contact->name ?? $this->contact->email;
        }
        return 'System';
    }
}
