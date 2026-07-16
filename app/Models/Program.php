<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'name', 'slug', 'audience', 'description', 'color', 'status', 'cycle_start', 'cycle_end',
    ];

    protected function casts(): array
    {
        return [
            'cycle_start' => 'date',
            'cycle_end' => 'date',
        ];
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function pairings(): HasMany
    {
        return $this->hasMany(MentorshipPairing::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
}
