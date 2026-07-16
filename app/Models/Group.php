<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = ['name', 'type', 'program_id', 'status', 'created_by'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot(['role_in_group', 'joined_at'])
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(GroupPost::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(GroupFile::class);
    }
}
