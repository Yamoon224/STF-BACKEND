<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'mentee_id', 'pairing_id', 'title', 'description', 'status', 'file_path',
    ];

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function pairing(): BelongsTo
    {
        return $this->belongsTo(MentorshipPairing::class, 'pairing_id');
    }
}
