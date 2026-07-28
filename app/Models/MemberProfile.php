<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id', 'payment_proof_path', 'tshirt_size', 'validated_at', 'validated_by',
    ];

    protected $hidden = ['payment_proof_path'];

    protected $appends = ['payment_proof_available'];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    protected function paymentProofAvailable(): Attribute
    {
        return Attribute::get(fn () => ! empty($this->payment_proof_path));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
