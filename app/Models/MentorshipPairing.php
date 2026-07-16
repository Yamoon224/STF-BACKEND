<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorshipPairing extends Model
{
    protected $fillable = [
        'mentee_id', 'mentor_id', 'program_id', 'cohort_id', 'status',
        'match_score', 'matched_at', 'ended_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MentorshipSession::class, 'pairing_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'pairing_id');
    }
}
