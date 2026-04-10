<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'title',
        'description',
        'reminder_time',
        'is_active',
        'is_task_reminder',
    ];

    protected $casts = [
        'reminder_time' => 'datetime',
        'is_active' => 'boolean',
        'is_task_reminder' => 'boolean',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Task (optional)
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Scope for active reminders
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for upcoming reminders
    public function scopeUpcoming($query)
    {
        return $query->where('reminder_time', '>', now())->orderBy('reminder_time', 'asc');
    }
}