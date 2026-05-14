<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // Only use each trait ONCE
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'avatar_color',
        'profile_image',
        'is_admin',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'preferences' => 'array',
        // Remove 'avatar_color' => 'string' - string is default, no need to cast
    ];

    // Relationships
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('is_admin', false);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function getAvatarColorAttribute($value): string
    {
        return $value ?: '#4361ee';
    }
}