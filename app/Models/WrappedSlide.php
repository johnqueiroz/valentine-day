<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WrappedSlide extends Model
{
    protected $fillable = [
        'wrapped_id',
        'type',
        'title',
        'body',
        'meta',
        'position',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function wrapped(): BelongsTo
    {
        return $this->belongsTo(Wrapped::class);
    }
}
