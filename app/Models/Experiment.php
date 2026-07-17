<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experiment extends Model
{
    protected $fillable = ['subject_id', 'level_id', 'course_id', 'title', 'description', 'instructions', 'order', 'status'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
