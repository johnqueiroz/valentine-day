<?php

namespace App\Http\Controllers;

use App\Models\Wrapped;
use App\Support\DominantColor;
use App\Support\MoonPhase;
use App\Support\YouTube;
use Inertia\Inertia;
use Inertia\Response;

class PublicWrappedController extends Controller
{
    public function show(Wrapped $wrapped): Response
    {
        // Wrapped não publicado não fica acessível pelo link.
        abort_unless($wrapped->isPublished(), 404);

        $wrapped->load(['slides', 'photos', 'tracks']);

        $daysTogether = $wrapped->relationship_started_on
            ? (int) $wrapped->relationship_started_on->diffInDays(now())
            : null;

        $moon = $wrapped->relationship_started_on
            ? MoonPhase::forDate($wrapped->relationship_started_on)
            : null;

        return Inertia::render('Public/Wrapped', [
            'wrapped' => [
                'couple_name_1' => $wrapped->couple_name_1,
                'couple_name_2' => $wrapped->couple_name_2,
                'gifter_name' => $wrapped->gifter_name,
                'love_letter' => $wrapped->love_letter,
                'relationship_started_on' => $wrapped->relationship_started_on?->toDateString(),
                'days_together' => $daysTogether,
                'moon' => $moon,
                'theme' => $wrapped->theme,
                'tracks' => $wrapped->tracks->map(fn ($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'artist' => $t->artist,
                    'youtube_id' => YouTube::idFrom($t->youtube_url),
                    'photo_url' => $t->photo_url,
                    'photo_color' => DominantColor::of($t->photo_path),
                ]),
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
                    'color' => DominantColor::of($p->path),
                    'caption' => $p->caption,
                ]),
            ],
        ]);
    }
}
