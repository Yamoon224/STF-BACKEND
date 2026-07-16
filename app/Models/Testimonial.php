<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = ['name', 'role', 'quote', 'program_id', 'order'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
