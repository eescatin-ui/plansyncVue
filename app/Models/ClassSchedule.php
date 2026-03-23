<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'time',
        'location',
        'day',
        'color',
        // Description removed
    ];
    
    /**
     * Get the user that owns the class schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}