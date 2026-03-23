<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            $context = app(TenantContext::class);
            if ($context->id() && !$context->isMsp()) {
                $builder->where($builder->getModel()->getTable() . '.organization_id', $context->id());
            }
        });

        static::creating(function (Model $model) {
            if (!$model->organization_id) {
                $context = app(TenantContext::class);
                if ($context->id()) {
                    $model->organization_id = $context->id();
                }
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
