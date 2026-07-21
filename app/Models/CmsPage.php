<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CmsPage extends Model
{
    protected $fillable = [
        'title', 'slug', 'type', 'body', 'excerpt', 'category', 'image_path',
        'status', 'author_id', 'published_at',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image_path ? Storage::disk('public')->url($this->image_path) : null
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CmsPageImage::class)->orderBy('order');
    }
}
