<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenteeProfile extends Model
{
    protected $fillable = [
        'user_id', 'level', 'school', 'interests', 'goals', 'guardian_name', 'guardian_contact',
        'diploma_document_path',
    ];

    protected $hidden = ['diploma_document_path'];

    protected $appends = ['diploma_document_available'];

    protected function diplomaDocumentAvailable(): Attribute
    {
        return Attribute::get(fn () => ! empty($this->diploma_document_path));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
