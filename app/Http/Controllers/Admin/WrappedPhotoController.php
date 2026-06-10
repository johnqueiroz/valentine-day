<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wrapped;
use App\Models\WrappedPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WrappedPhotoController extends Controller
{
    public function store(Request $request, Wrapped $wrapped): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array'],
            'photos.*' => ['image', 'max:8192'], // até 8MB por imagem
        ]);

        $start = (int) $wrapped->photos()->max('position');

        foreach ($request->file('photos') as $i => $file) {
            $path = $file->store("wrappeds/{$wrapped->id}", 'public');

            $wrapped->photos()->create([
                'path' => $path,
                'position' => $start + $i + 1,
            ]);
        }

        return back()->with('success', 'Fotos enviadas.');
    }

    public function destroy(Wrapped $wrapped, WrappedPhoto $photo): RedirectResponse
    {
        abort_unless($photo->wrapped_id === $wrapped->id, 404);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Foto removida.');
    }
}
