<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wrapped;
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
            ->withCount(['slides', 'photos'])
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

        return redirect()
            ->route('admin.wrappeds.edit', $wrapped)
            ->with('success', 'Wrapped criado! Agora adicione as fotos.');
    }

    public function edit(Wrapped $wrapped): Response
    {
        $wrapped->load(['slides', 'photos']);

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

        return back()->with('success', 'Wrapped atualizado.');
    }

    public function destroy(Wrapped $wrapped): RedirectResponse
    {
        // Remove os arquivos das fotos antes de apagar (cascade cuida das linhas).
        foreach ($wrapped->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }
        if ($wrapped->cover_photo_path) {
            Storage::disk('public')->delete($wrapped->cover_photo_path);
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
            'song_title' => $data['song_title'] ?? null,
            'song_artist' => $data['song_artist'] ?? null,
            'youtube_url' => $data['youtube_url'] ?? null,
            'love_letter' => $data['love_letter'] ?? null,
            'relationship_started_on' => $data['relationship_started_on'] ?? null,
            'theme' => $data['theme'],
            'cover_photo_path' => $data['cover_photo_path'] ?? null,
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

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'couple_name_1' => ['required', 'string', 'max:100'],
            'couple_name_2' => ['required', 'string', 'max:100'],
            'gifter_name' => ['nullable', 'string', 'max:100'],
            'song_title' => ['nullable', 'string', 'max:150'],
            'song_artist' => ['nullable', 'string', 'max:150'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'love_letter' => ['nullable', 'string', 'max:2000'],
            'cover_photo_path' => ['nullable', 'string', 'max:255'],
            'relationship_started_on' => ['nullable', 'date'],
            'theme' => ['required', 'string', 'in:'.implode(',', array_keys($this->themes()))],
            'published' => ['boolean'],
            'slides' => ['array'],
            'slides.*.type' => ['required', 'string', 'in:'.implode(',', array_keys($this->slideTypes()))],
            'slides.*.title' => ['required', 'string', 'max:150'],
            'slides.*.body' => ['nullable', 'string', 'max:1000'],
            'slides.*.meta' => ['nullable', 'array'],
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
