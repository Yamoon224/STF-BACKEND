<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Scholarship extends Model
{
    protected $fillable = [
        'title', 'provider', 'description', 'amount', 'audience',
        'deadline', 'application_url', 'image_path', 'status', 'order',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image_path ? Storage::disk('public')->url($this->image_path) : null
        );
    }
}
