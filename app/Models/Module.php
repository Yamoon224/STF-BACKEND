<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = ['program_id', 'title', 'description', 'order', 'status'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
