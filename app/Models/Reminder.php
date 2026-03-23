<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'reminder_time',
        'type',
        'task_id',
        'class_id'
    ];

    protected $casts = [
        'reminder_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_id');
    }
}