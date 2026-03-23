<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeReview extends Model
{
    protected $fillable = [
        'change_request_id', 'reviewer_id', 'objectives_met', 'on_schedule',
        'within_budget', 'incidents_caused', 'incidents_description',
        'lessons_learned', 'improvement_actions', 'overall_rating',
    ];

    protected $casts = [
        'objectives_met' => 'boolean',
        'on_schedule' => 'boolean',
        'within_budget' => 'boolean',
        'incidents_caused' => 'boolean',
    ];

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
