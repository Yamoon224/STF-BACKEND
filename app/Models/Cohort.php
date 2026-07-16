<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    protected $fillable = [
        'program_id', 'name', 'start_date', 'end_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function pairings(): HasMany
    {
        return $this->hasMany(MentorshipPairing::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_user')
            ->withPivot('role_in_cohort')
            ->withTimestamps();
    }
}
