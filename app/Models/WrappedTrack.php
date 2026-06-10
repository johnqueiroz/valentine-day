<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WrappedTrack extends Model
{
    protected $fillable = [
        'wrapped_id',
        'title',
        'artist',
        'youtube_url',
        'photo_path',
        'position',
    ];

    protected $appends = ['photo_url'];

    public function wrapped(): BelongsTo
    {
        return $this->belongsTo(Wrapped::class);
    }

    /**
     * URL pública da foto da faixa (disco "public"), ou null.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
