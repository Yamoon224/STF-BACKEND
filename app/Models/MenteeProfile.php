<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenteeProfile extends Model
{
    protected $fillable = [
        'user_id', 'level', 'school', 'interests', 'guardian_name', 'guardian_contact',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
