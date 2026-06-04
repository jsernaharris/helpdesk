<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'name', 'email', 'password', 'phone',
        'job_title', 'timezone', 'is_active', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_user_id');
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_user_id');
    }

    public function ticketThreads(): HasMany
    {
        return $this->hasMany(TicketThread::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('is_lead')->withTimestamps();
    }

    /**
     * Organizations this MSP tech is scoped to.
     * Empty = unrestricted (sees all). Only applies to msp_technician role.
     */
    public function accessibleOrganizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withTimestamps();
    }

    public function isMspStaff(): bool
    {
        return $this->organization && $this->organization->is_msp;
    }

    public function isMspAdmin(): bool
    {
        return $this->hasRole('msp_admin');
    }

    public function isMspTechnician(): bool
    {
        return $this->hasRole('msp_technician');
    }

    public function isCustomerAdmin(): bool
    {
        return $this->hasRole('customer_admin');
    }

    public function isCustomerUser(): bool
    {
        return $this->hasRole('customer_user');
    }

    /**
     * Returns org IDs this user can access, or null if unrestricted.
     * - MSP admins: always unrestricted (null)
     * - MSP techs: restricted to assigned orgs, or unrestricted if none assigned
     * - Customers: their own org only
     */
    public function accessibleOrgIds(): ?array
    {
        if ($this->isMspAdmin()) {
            return null; // unrestricted
        }

        if ($this->isMspTechnician()) {
            $orgIds = $this->accessibleOrganizations()->pluck('organizations.id')->all();
            return empty($orgIds) ? null : $orgIds; // empty = unrestricted
        }

        // Customer users: own org only
        return [$this->organization_id];
    }

    /**
     * Check if user can access a specific organization.
     */
    public function canAccessOrganization(int $organizationId): bool
    {
        $allowed = $this->accessibleOrgIds();
        return $allowed === null || in_array($organizationId, $allowed);
    }
}
