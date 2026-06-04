<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasTicketNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, HasTicketNumber;

    protected $fillable = [
        'ticket_number', 'organization_id', 'requester_user_id', 'requester_contact_id',
        'assigned_to_user_id', 'assigned_to_team_id', 'service_catalog_id', 'sla_plan_id',
        'type', 'status', 'priority', 'impact', 'urgency', 'source',
        'subject', 'description', 'resolution',
        'sla_response_due_at', 'sla_resolution_due_at',
        'first_responded_at', 'resolved_at', 'closed_at',
        'sla_response_breached', 'sla_resolution_breached',
        'is_escalated', 'escalation_level', 'parent_ticket_id', 'custom_fields', 'form_template_id',
    ];

    protected $casts = [
        'sla_response_due_at' => 'datetime',
        'sla_resolution_due_at' => 'datetime',
        'first_responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_response_breached' => 'boolean',
        'sla_resolution_breached' => 'boolean',
        'is_escalated' => 'boolean',
        'custom_fields' => 'array',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function requesterContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'requester_contact_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedToTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_to_team_id');
    }

    public function serviceCatalog(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class);
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function slaPlan(): BelongsTo
    {
        return $this->belongsTo(SlaPlan::class);
    }

    public function parentTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'parent_ticket_id');
    }

    public function childTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'parent_ticket_id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(TicketThread::class)->orderBy('created_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->orderByDesc('created_at');
    }

    public function problemRecord(): HasOne
    {
        return $this->hasOne(ProblemRecord::class);
    }

    public function changeRequest(): HasOne
    {
        return $this->hasOne(ChangeRequest::class);
    }

    public function slaBreachLogs(): HasMany
    {
        return $this->hasMany(SlaBreachLog::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class)->withTimestamps();
    }

    public function getRequesterNameAttribute(): string
    {
        if ($this->requester) {
            return $this->requester->name;
        }
        if ($this->requesterContact) {
            return $this->requesterContact->name ?? $this->requesterContact->email;
        }
        return 'Unknown';
    }

    public function getRequesterEmailAttribute(): ?string
    {
        return $this->requester?->email ?? $this->requesterContact?->email;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['new', 'open', 'pending', 'on_hold']);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed', 'cancelled']);
    }

    public function isSlaBreached(): bool
    {
        return $this->sla_response_breached || $this->sla_resolution_breached;
    }

    /**
     * Scope tickets to those the given user is allowed to see.
     */
    public function scopeAccessibleBy($query, \App\Models\User $user)
    {
        $orgIds = $user->accessibleOrgIds();
        if ($orgIds !== null) {
            $query->whereIn('organization_id', $orgIds);
        }
        return $query;
    }
}
