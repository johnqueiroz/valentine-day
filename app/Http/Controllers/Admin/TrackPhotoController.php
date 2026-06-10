<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wrapped;
use App\Models\WrappedTrack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrackPhotoController extends Controller
{
    public function store(Request $request, Wrapped $wrapped, WrappedTrack $track): RedirectResponse
    {
        abort_unless($track->wrapped_id === $wrapped->id, 404);

        $request->validate([
            'photo' => ['required', 'image', 'max:8192'], // até 8MB
        ]);

        // Substitui a foto anterior, se houver.
        if ($track->photo_path) {
            Storage::disk('public')->delete($track->photo_path);
        }

        $track->update([
            'photo_path' => $request->file('photo')->store("wrappeds/{$wrapped->id}/tracks", 'public'),
        ]);

        return back()->with('success', 'Foto da faixa atualizada.');
    }
}
