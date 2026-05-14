<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Remove: use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;  // Removed HasApiTokens

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'api_token',        // Add this
        'avatar_color',
        'profile_image',
        'is_admin',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',        // Hide api_token when serializing
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'preferences' => 'array',
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
    
    // Optional: Helper method to generate token
    public function generateToken()
    {
        $plainTextToken = \Illuminate\Support\Str::random(60);
        $this->api_token = hash('sha256', $plainTextToken);
        $this->save();
        
        return $plainTextToken;
    }
    
    // Optional: Helper method to revoke token
    public function revokeToken()
    {
        $this->api_token = null;
        $this->save();
    }
}