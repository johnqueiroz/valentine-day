<?php

namespace App\Http\Controllers;

use App\Models\Wrapped;
use App\Support\DominantColor;
use App\Support\MoonPhase;
use App\Support\YouTube;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicWrappedController extends Controller
{
    public function show(Wrapped $wrapped): Response
    {
        // Wrapped não publicado não fica acessível pelo link.
        abort_unless($wrapped->isPublished(), 404);

        $wrapped->load(['slides', 'photos']);

        $daysTogether = $wrapped->relationship_started_on
            ? (int) $wrapped->relationship_started_on->diffInDays(now())
            : null;

        // Capa do player: foto definida como capa ou a primeira foto enviada.
        $coverPath = $wrapped->cover_photo_path ?? $wrapped->photos->first()?->path;
        $coverUrl = $coverPath ? Storage::disk('public')->url($coverPath) : null;
        $coverColor = DominantColor::of($coverPath);

        $moon = $wrapped->relationship_started_on
            ? MoonPhase::forDate($wrapped->relationship_started_on)
            : null;

        return Inertia::render('Public/Wrapped', [
            'wrapped' => [
                'couple_name_1' => $wrapped->couple_name_1,
                'couple_name_2' => $wrapped->couple_name_2,
                'gifter_name' => $wrapped->gifter_name,
                'song_title' => $wrapped->song_title,
                'song_artist' => $wrapped->song_artist,
                'youtube_id' => YouTube::idFrom($wrapped->youtube_url),
                'love_letter' => $wrapped->love_letter,
                'cover_url' => $coverUrl,
                'cover_color' => $coverColor,
                'relationship_started_on' => $wrapped->relationship_started_on?->toDateString(),
                'days_together' => $daysTogether,
                'moon' => $moon,
                'theme' => $wrapped->theme,
                'slides' => $wrapped->slides->map(fn ($s) => [
                    'id' => $s->id,
                    'type' => $s->type,
                    'title' => $s->title,
                    'body' => $s->body,
                    'meta' => $s->meta,
                ]),
                'photos' => $wrapped->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => $p->url,
                    'caption' => $p->caption,
                ]),
            ],
        ]);
    }
}
