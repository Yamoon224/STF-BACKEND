<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionNote extends Model
{
    protected $fillable = ['session_id', 'author_id', 'content', 'visibility'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(MentorshipSession::class, 'session_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
