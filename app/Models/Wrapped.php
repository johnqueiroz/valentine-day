<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wrapped extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'couple_name_1',
        'couple_name_2',
        'gifter_name',
        'love_letter',
        'relationship_started_on',
        'theme',
        'cover_photo_path',
        'published_at',
    ];

    protected $casts = [
        'relationship_started_on' => 'date',
        'published_at' => 'datetime',
    ];

    /**
     * Gera automaticamente um slug único ao criar, caso não informado.
     */
    protected static function booted(): void
    {
        static::creating(function (Wrapped $wrapped) {
            if (empty($wrapped->slug)) {
                do {
                    $wrapped->slug = Str::lower(Str::random(8));
                } while (static::where('slug', $wrapped->slug)->exists());
            }
        });
    }

    public function slides(): HasMany
    {
        return $this->hasMany(WrappedSlide::class)->orderBy('position');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WrappedPhoto::class)->orderBy('position');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(WrappedTrack::class)->orderBy('position');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
