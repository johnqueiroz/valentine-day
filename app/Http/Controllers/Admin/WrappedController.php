<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wrapped;
use App\Models\WrappedTrack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WrappedController extends Controller
{
    public function index(): Response
    {
        $wrappeds = Wrapped::query()
            ->withCount(['slides', 'photos', 'tracks'])
            ->latest()
            ->get();

        return Inertia::render('Admin/Wrappeds/Index', [
            'wrappeds' => $wrappeds,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Wrappeds/Create', [
            'slideTypes' => $this->slideTypes(),
            'themes' => $this->themes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $wrapped = Wrapped::create([
            ...$this->wrappedAttributes($data),
            'published_at' => ($data['published'] ?? false) ? now() : null,
        ]);

        $this->syncSlides($wrapped, $data['slides'] ?? []);
        $this->syncTracks($wrapped, $data['tracks'] ?? []);

        return redirect()
            ->route('admin.wrappeds.edit', $wrapped)
            ->with('success', 'Wrapped criado! Agora adicione as fotos das faixas e a galeria.');
    }

    public function edit(Wrapped $wrapped): Response
    {
        $wrapped->load(['slides', 'photos', 'tracks']);

        return Inertia::render('Admin/Wrappeds/Edit', [
            'wrapped' => $wrapped,
            'slideTypes' => $this->slideTypes(),
            'themes' => $this->themes(),
            'publicUrl' => route('wrapped.show', $wrapped),
        ]);
    }

    public function update(Request $request, Wrapped $wrapped): RedirectResponse
    {
        $data = $this->validateData($request);

        $wrapped->update([
            ...$this->wrappedAttributes($data),
            'published_at' => ($data['published'] ?? false) ? ($wrapped->published_at ?? now()) : null,
        ]);

        $this->syncSlides($wrapped, $data['slides'] ?? []);
        $this->syncTracks($wrapped, $data['tracks'] ?? []);

        return back()->with('success', 'Wrapped atualizado.');
    }

    public function destroy(Wrapped $wrapped): RedirectResponse
    {
        // Remove os arquivos das fotos antes de apagar (cascade cuida das linhas).
        foreach ($wrapped->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }
        foreach ($wrapped->tracks as $track) {
            if ($track->photo_path) {
                Storage::disk('public')->delete($track->photo_path);
            }
        }

        $wrapped->delete();

        return redirect()
            ->route('admin.wrappeds.index')
            ->with('success', 'Wrapped removido.');
    }

    /**
     * Mapeia os dados validados para os atributos do Wrapped (sem published_at).
     */
    protected function wrappedAttributes(array $data): array
    {
        return [
            'couple_name_1' => $data['couple_name_1'],
            'couple_name_2' => $data['couple_name_2'],
            'gifter_name' => $data['gifter_name'] ?? null,
            'love_letter' => $data['love_letter'] ?? null,
            'relationship_started_on' => $data['relationship_started_on'] ?? null,
            'theme' => $data['theme'],
        ];
    }

    /**
     * Substitui os slides do wrapped pelos enviados no formulário.
     */
    protected function syncSlides(Wrapped $wrapped, array $slides): void
    {
        $wrapped->slides()->delete();

        foreach (array_values($slides) as $position => $slide) {
            $wrapped->slides()->create([
                'type' => $slide['type'],
                'title' => $slide['title'],
                'body' => $slide['body'] ?? null,
                'meta' => $slide['meta'] ?? null,
                'position' => $position,
            ]);
        }
    }

    /**
     * Upsert das faixas preservando ids e a foto (photo_path) das existentes.
     * Faixas ausentes na submissão são apagadas (com o arquivo da foto).
     */
    protected function syncTracks(Wrapped $wrapped, array $tracks): void
    {
        $keptIds = [];

        foreach (array_values($tracks) as $position => $track) {
            $attributes = [
                'title' => $track['title'],
                'artist' => $track['artist'] ?? null,
                'youtube_url' => $track['youtube_url'] ?? null,
                'position' => $position,
            ];

            $model = ! empty($track['id'])
                ? tap($wrapped->tracks()->whereKey($track['id'])->first())?->fill($attributes)
                : null;

            if ($model) {
                $model->save();
            } else {
                $model = $wrapped->tracks()->create($attributes);
            }

            $keptIds[] = $model->id;
        }

        // Apaga faixas removidas no formulário, junto do arquivo da foto.
        $wrapped->tracks()->whereKeyNot($keptIds)->get()->each(function (WrappedTrack $track) {
            if ($track->photo_path) {
                Storage::disk('public')->delete($track->photo_path);
            }
            $track->delete();
        });
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'couple_name_1' => ['required', 'string', 'max:100'],
            'couple_name_2' => ['required', 'string', 'max:100'],
            'gifter_name' => ['nullable', 'string', 'max:100'],
            'love_letter' => ['nullable', 'string', 'max:2000'],
            'relationship_started_on' => ['nullable', 'date'],
            'theme' => ['required', 'string', 'in:'.implode(',', array_keys($this->themes()))],
            'published' => ['boolean'],
            'slides' => ['array'],
            'slides.*.type' => ['required', 'string', 'in:'.implode(',', array_keys($this->slideTypes()))],
            'slides.*.title' => ['required', 'string', 'max:150'],
            'slides.*.body' => ['nullable', 'string', 'max:1000'],
            'slides.*.meta' => ['nullable', 'array'],
            'tracks' => ['array'],
            'tracks.*.id' => ['nullable', 'integer'],
            'tracks.*.title' => ['required', 'string', 'max:150'],
            'tracks.*.artist' => ['nullable', 'string', 'max:150'],
            'tracks.*.youtube_url' => ['nullable', 'url', 'max:255'],
        ]);
    }

    protected function slideTypes(): array
    {
        return [
            'stat' => 'Estatística',
            'music' => 'Música',
            'place' => 'Lugar',
            'milestone' => 'Marco',
            'message' => 'Mensagem',
        ];
    }

    /**
     * Cores de acento da skin Spotify (fundo sempre escuro).
     */
    protected function themes(): array
    {
        return [
            'green' => 'Verde Spotify',
            'blue' => 'Azul',
            'purple' => 'Roxo',
            'pink' => 'Rosa',
        ];
    }
}
