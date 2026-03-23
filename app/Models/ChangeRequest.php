<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeRequest extends Model
{
    use SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'ticket_id', 'organization_id', 'change_category_id', 'requested_by_user_id',
        'change_number', 'type', 'risk_level',
        'implementation_plan', 'rollback_plan', 'test_plan',
        'business_justification', 'impact_assessment', 'communication_plan',
        'scheduled_start_at', 'scheduled_end_at', 'actual_start_at', 'actual_end_at',
        'status', 'approved_by_user_id', 'approved_at',
        'cab_required', 'cab_notes',
        'approval_level_required', 'current_approval_level',
        'submitted_at', 'review_completed_at', 'post_implementation_notes',
    ];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'review_completed_at' => 'datetime',
        'cab_required' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ChangeCategory::class, 'change_category_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ChangeApproval::class)->orderBy('approval_level')->orderByDesc('created_at');
    }

    public function review(): HasOne
    {
        return $this->hasOne(ChangeReview::class);
    }

    public function isInBlackout(): bool
    {
        if (!$this->scheduled_start_at) {
            return false;
        }

        return ChangeBlackoutPeriod::where('organization_id', $this->organization_id)
            ->active()
            ->where('starts_at', '<=', $this->scheduled_start_at)
            ->where('ends_at', '>=', $this->scheduled_start_at)
            ->when($this->type === 'emergency', fn ($q) => $q->where('allow_emergency', false))
            ->exists();
    }

    public function isFullyApproved(): bool
    {
        if ($this->approval_level_required <= 0) {
            return true;
        }

        return $this->current_approval_level >= $this->approval_level_required;
    }

    public function canBeImplemented(): bool
    {
        return $this->status === 'approved' && !$this->isInBlackout();
    }

    public function getRequesterNameAttribute(): string
    {
        return $this->requestedBy?->name ?? $this->ticket?->requester?->name ?? 'Unknown';
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeScheduledBetween($query, $start, $end)
    {
        return $query->where('scheduled_start_at', '>=', $start)
            ->where('scheduled_start_at', '<=', $end);
    }
}
