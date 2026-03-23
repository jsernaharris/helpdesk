<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessHourPeriod extends Model
{
    protected $fillable = [
        'business_hours_id', 'day_of_week', 'start_time', 'end_time',
    ];

    public function businessHours(): BelongsTo
    {
        return $this->belongsTo(BusinessHours::class);
    }
}
