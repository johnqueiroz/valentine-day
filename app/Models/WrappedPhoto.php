<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WrappedPhoto extends Model
{
    protected $fillable = [
        'wrapped_id',
        'path',
        'caption',
        'position',
    ];

    protected $appends = ['url'];

    public function wrapped(): BelongsTo
    {
        return $this->belongsTo(Wrapped::class);
    }

    /**
     * URL pública da foto (disco "public").
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
