<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorshipSession extends Model
{
    protected $table = 'mentorship_sessions';

    protected $fillable = [
        'pairing_id', 'scheduled_at', 'duration_minutes', 'status',
        'topic', 'location_or_link', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function pairing(): BelongsTo
    {
        return $this->belongsTo(MentorshipPairing::class, 'pairing_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SessionNote::class, 'session_id');
    }
}
