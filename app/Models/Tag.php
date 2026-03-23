<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'color'];

    public function tickets(): MorphToMany
    {
        return $this->morphedByMany(Ticket::class, 'taggable');
    }

    public function kbArticles(): MorphToMany
    {
        return $this->morphedByMany(KbArticle::class, 'taggable');
    }
}
