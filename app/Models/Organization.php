<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'domain', 'email_domain', 'is_msp',
        'address', 'city', 'state', 'zip', 'country', 'phone',
        'logo_path', 'settings', 'is_active',
    ];

    protected $casts = [
        'is_msp' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function serviceCatalogs(): HasMany
    {
        return $this->hasMany(ServiceCatalog::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function defaultSlaPlan(): HasOne
    {
        return $this->hasOne(SlaPlan::class)->where('is_default', true);
    }

    public function slaPlans(): HasMany
    {
        return $this->hasMany(SlaPlan::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHours::class);
    }

    public function emailMailboxes(): HasMany
    {
        return $this->hasMany(EmailMailbox::class);
    }

    public function changePolicy(): HasOne
    {
        return $this->hasOne(ChangePolicy::class);
    }

    public function changeCategories(): HasMany
    {
        return $this->hasMany(ChangeCategory::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function cabMembers(): HasMany
    {
        return $this->hasMany(CabMember::class);
    }

    public function changeBlackoutPeriods(): HasMany
    {
        return $this->hasMany(ChangeBlackoutPeriod::class);
    }

    public function getOrCreateChangePolicy(): ChangePolicy
    {
        return $this->changePolicy ?? ChangePolicy::create([
            'organization_id' => $this->id,
        ]);
    }
}
