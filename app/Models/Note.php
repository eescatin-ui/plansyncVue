<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'tags',
        'color',
        'is_pinned',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Map camelCase (React Native) to snake_case (database)
    protected $appends = ['createdAt', 'updatedAt'];

    public function getCreatedAtAttribute(): ?string
    {
        if (!isset($this->attributes['created_at'])) return null;
        $val = $this->attributes['created_at'];
        return $val instanceof \DateTime ? $val->toISOString() : $val;
    }

    public function getUpdatedAtAttribute(): ?string
    {
        if (!isset($this->attributes['updated_at'])) return null;
        $val = $this->attributes['updated_at'];
        return $val instanceof \DateTime ? $val->toISOString() : $val;
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for pinned notes
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}