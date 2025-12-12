<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'thread_participants')
                    ->withTimestamps()
                    ->withPivot('last_read_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    public function scopeWithUnreadCount($query, $userId)
    {
        return $query->withCount(['messages as unread_count' => function ($q) use ($userId) {
            $q->where('is_read', false)
              ->where('user_id', '!=', $userId);
        }]);
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('subject', 'like', '%' . $search . '%');
        }
        return $query;
    }
}
