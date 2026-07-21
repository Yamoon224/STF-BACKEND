<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CmsPageImage extends Model
{
    protected $fillable = ['cms_page_id', 'image_path', 'order'];

    protected $appends = ['image_url'];

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image_path ? Storage::disk('public')->url($this->image_path) : null
        );
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }
}
