<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'project_number', 'organization_id', 'name', 'description',
        'status', 'start_date', 'due_date', 'created_by_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('is_lead')
            ->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(ProjectTimeEntry::class);
    }

    public function totalMinutes(): int
    {
        return (int) $this->timeEntries()->sum('minutes');
    }

    public function totalHours(): float
    {
        return round($this->totalMinutes() / 60, 2);
    }

    /**
     * Scope projects to those the given user is allowed to see (mirrors Ticket).
     */
    public function scopeAccessibleBy($query, User $user)
    {
        $orgIds = $user->accessibleOrgIds();
        if ($orgIds !== null) {
            $query->whereIn('organization_id', $orgIds);
        }
        return $query;
    }
}
