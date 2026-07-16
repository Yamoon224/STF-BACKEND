<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = ['title', 'description', 'icon', 'criteria'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->withPivot(['awarded_at', 'awarded_by'])
            ->withTimestamps();
    }
}
