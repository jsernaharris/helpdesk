<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KbArticle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'content', 'excerpt',
        'visibility', 'organization_id', 'status', 'view_count',
        'is_pinned', 'published_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        if (!$user || $user->isCustomerUser() || $user->isCustomerAdmin()) {
            return $query->where('visibility', 'public');
        }
        return $query;
    }
}
