<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KbCategory extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'parent_id', 'organization_id', 'name', 'slug',
        'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(KbCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KbArticle::class, 'category_id');
    }
}
