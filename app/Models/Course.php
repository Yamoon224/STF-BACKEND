<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['level_id', 'subject_id', 'title', 'description', 'order', 'status'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }

    public function experiments(): HasMany
    {
        return $this->hasMany(Experiment::class);
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class);
    }
}
