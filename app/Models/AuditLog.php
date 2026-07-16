<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['actor_id', 'action', 'target_type', 'target_id', 'meta', 'created_at'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(?User $actor, string $action, ?Model $target = null, array $meta = []): self
    {
        return self::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->getKey(),
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
