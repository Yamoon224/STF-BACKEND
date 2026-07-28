<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorProfile extends Model
{
    protected $fillable = [
        'user_id', 'expertise', 'bio', 'capacity', 'validated_at', 'validated_by',
        'cv_document_path',
    ];

    protected $hidden = ['cv_document_path'];

    protected $appends = ['cv_document_available'];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
        ];
    }

    protected function cvDocumentAvailable(): Attribute
    {
        return Attribute::get(fn () => ! empty($this->cv_document_path));
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
