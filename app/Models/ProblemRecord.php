<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProblemRecord extends Model
{
    protected $fillable = [
        'ticket_id', 'root_cause', 'workaround', 'known_error', 'status',
    ];

    protected $casts = [
        'known_error' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function incidents(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'problem_incident')->withTimestamps();
    }
}
