<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = ['page_key', 'section_key', 'type', 'payload', 'order'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
