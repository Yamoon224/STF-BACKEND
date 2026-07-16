<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupFile extends Model
{
    protected $fillable = ['group_id', 'uploader_id', 'name', 'path', 'mime_type', 'size_bytes'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
