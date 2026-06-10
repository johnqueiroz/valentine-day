<?php

use App\Http\Controllers\Admin\TrackPhotoController;
use App\Http\Controllers\Admin\WrappedController;
use App\Http\Controllers\Admin\WrappedPhotoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicWrappedController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', fn () => redirect()->route('admin.wrappeds.index'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Painel administrativo (gera os Wrappeds dos casais).
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('wrappeds', WrappedController::class)->except('show');
        Route::post('wrappeds/{wrapped}/photos', [WrappedPhotoController::class, 'store'])
            ->name('wrappeds.photos.store');
        Route::delete('wrappeds/{wrapped}/photos/{photo}', [WrappedPhotoController::class, 'destroy'])
            ->name('wrappeds.photos.destroy');
        Route::post('wrappeds/{wrapped}/tracks/{track}/photo', [TrackPhotoController::class, 'store'])
            ->name('wrappeds.tracks.photo');
    });
});

// Página pública compartilhável de cada casal (link único por slug).
Route::get('/w/{wrapped:slug}', [PublicWrappedController::class, 'show'])->name('wrapped.show');

require __DIR__.'/auth.php';
