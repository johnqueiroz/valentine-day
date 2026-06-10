# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A "Spotify Wrapped for couples" app (modeled on lovepanda.com.br). An authenticated admin creates a **Wrapped** for a couple (manual fields + photo uploads + a YouTube song); each Wrapped gets a unique slug and a shareable public link `/w/{slug}`. The public experience is a **faithful Spotify-app skin** (dark `#121212`, green `#1DB954`) with a 3-screen flow: gift intro → music player → animated "stories" retrospective. There is no public sign-up — only the admin generates Wrappeds.

Stack: **Laravel 13 + Inertia.js + Vue 3** (Breeze scaffolding), Tailwind 3, SQLite, **YouTube IFrame Player API** for the player's audio.

## Environment / PATH gotcha

PHP 8.4 and Composer come from **Laravel Herd shims** at `C:\Users\Windows\.config\herd\bin` (e.g. `php.bat` → `php84\php.exe`). This directory is **not** on the PATH of a freshly spawned shell, so `php`/`composer`/`artisan` fail with "not recognized". Prefix every PHP-related command:

```powershell
$env:Path = 'C:\Users\Windows\.config\herd\bin;' + $env:Path
```

## Commands

```powershell
# Run everything at once (server + queue + logs + vite) — preferred for dev
composer dev

# Or individually
php artisan serve            # http://127.0.0.1:8000
npm run dev                  # vite dev server (HMR)
npm run build                # production assets

# Database
php artisan migrate:fresh --seed   # rebuilds SQLite + seeds the /w/demo Wrapped

# Tests (PHPUnit)
php artisan test
php artisan test --filter=PublicWrappedTest          # single test class
php artisan test --filter=test_unpublished_wrapped   # single test method

# Lint / format (Laravel Pint)
./vendor/bin/pint
```

Admin login after seeding: `admin@example.com` / `password`. Public demo: `/w/demo`.

## Architecture

**Data model** (`app/Models/`): a `Wrapped` (couple names + `gifter_name`, `song_title`/`song_artist`/`youtube_url`, `love_letter`, `cover_photo_path`, `relationship_started_on`, `theme`) `hasMany` `WrappedSlide` (the ordered story content, typed `stat|music|place|milestone|message` with a flexible JSON `meta` column) and `hasMany` `WrappedPhoto` (files on the `public` disk). Key behaviors:
- `Wrapped::booted()` auto-generates a unique 8-char `slug` on create. **Caveat:** `DatabaseSeeder` uses the `WithoutModelEvents` trait, which disables this hook — seeders must set `slug` explicitly.
- Only the **public** route binds by slug, via the explicit `/w/{wrapped:slug}` parameter. Admin routes use the default id binding — do **not** add a global `getRouteKeyName()` returning slug, or `/admin/wrappeds/{id}/edit` (which passes the id) will 404.
- `WrappedPhoto` exposes a `url` accessor (appended) built from `Storage::disk('public')`.

**Request flow** (`routes/web.php`):
- Admin CRUD lives under the `auth` middleware + `admin.` route-name prefix. `WrappedController` handles the Wrapped + its slides together — `syncSlides()` deletes and recreates all slides from the submitted array on every store/update (positions are array index). Photos are a separate multipart flow in `WrappedPhotoController`.
- The single public route `/w/{wrapped:slug}` → `PublicWrappedController@show` **aborts 404 unless `published_at` is set**, then hands a trimmed payload to the `Public/Wrapped` Inertia page. It computes `days_together`, resolves `cover_url` (the `cover_photo_path` photo, else the first photo) plus its `cover_color` (via `App\Support\DominantColor::of()`), extracts `youtube_id` via `App\Support\YouTube::idFrom()`, and computes `moon` (phase/emoji/illumination) via `App\Support\MoonPhase::forDate()` — all three are pure helpers with no external API.

**Frontend** (`resources/js/`):
- Inertia pages in `Pages/`, with the Ziggy plugin registered in `app.js`. The `@/` alias maps to `resources/js/` (see `jsconfig.json`); use the Ziggy `route()` helper for URLs.
- Admin: `Pages/Admin/Wrappeds/{Index,Create,Edit}.vue` share `Components/Admin/WrappedFormFields.vue` (couple/gift/song/letter fields + the slides repeater, whose per-type meta inputs are driven by the `metaFields` map). Edit hosts the photo uploader and the "set as cover" action (writes `cover_photo_path`).
- **Public experience** = `Pages/Public/Wrapped.vue`, a 3-screen state machine inside a mobile-width frame: `GiftIntro` → `SpotifyPlayer` → (overlay) `WrappedStories`. The orchestrator **owns the YouTube player** (`useYouTubePlayer.js` + a hidden `#yt-player` div) so audio persists across screens; playback is initialized on the "Ver Presente" tap (a user gesture, so autoplay-with-sound is allowed). Public components live in `Components/Public/` (`GiftIntro`, `SpotifyPlayer`, `SpotifyNav`, `StarMap`).
- `SpotifyPlayer.vue`'s prev/next buttons (⏮/⏭) are a **navigable cover gallery**: they cycle `wrapped.photos` (starting on the cover) and the player background re-tints per photo. This relies on each photo carrying a `color` (dominant color via `App\Support\DominantColor::of()` in the payload), fed to `coverGradient()`; buttons disable when there are fewer than 2 photos.
- `WrappedStories.vue` takes the whole `wrapped` prop and builds the sequence `intro → days-together → authored slides → moon/starmap → gallery → outro` (computed slides come from props, not slide rows); progress bars run on `requestAnimationFrame` with tap/click/keyboard nav.
- `StarMap.vue` renders a date-seeded `<canvas>` starfield (deterministic via a mulberry32 PRNG) plus the moon emoji — stylized, not astronomically accurate.
- `resources/js/themes.js` is the **Spotify accent palette** (dark base + `accent`/`tint` per `theme` key). `coverGradient(coverColor, theme)` tints the player background from the cover's dominant color (à la Spotify), falling back to `playerGradient(theme)` when there's no cover. Adding a theme requires updating this file **and** the `themes()` whitelist in `WrappedController`.

**Shared Inertia props**: `HandleInertiaRequests::share()` exposes `auth.user` and `flash.success`; controllers redirect with `->with('success', ...)` and pages read `usePage().props.flash.success`.

## Conventions

- UI copy and domain language are in **Portuguese (pt-BR)**.
- Slide `type` and `theme` values are validated against whitelists in `WrappedController` (`slideTypes()` / `themes()`) — keep new values in sync across the controller, `themes.js`, and `WrappedStories.vue`'s `typeLabel` map.
- Image work uses raw **GD** (no Intervention Image): seeders generate placeholder JPEGs (`imagecreatetruecolor`/`imagejpeg`), and `App\Support\DominantColor` averages a cover to one pixel for the player's background tint.
