# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A personal, single-couple **"Spotify Wrapped"** static site (no backend). One couple's
data and images are hardcoded; the page is a **faithful Spotify-app skin** (dark `#121212`,
green `#1DB954`) with a 3-screen flow: gift intro → music player (static MP3) → animated
"stories" retrospective. Pure client-side **Vue 3 + Vite + Tailwind 3** — no PHP, no
database, no admin. (A previous Laravel version lives in git history.)

## Commands

```bash
npm install
npm run dev        # vite dev server (HMR)
npm run build      # static build → dist/
npm run preview    # serve dist/ locally (do NOT open via file://)
```

Deploy: drag `dist/` into **Netlify Drop** (or GitHub Pages). Free, no server.

## Architecture

The whole site is driven by one data file + reusable Vue components. The `@/` alias maps
to `resources/js/` (see `jsconfig.json` / `vite.config.js`).

- **`resources/js/data.js`** — the only file to edit for content: couple names,
  `gifter_name`, `relationship_started_on` (`YYYY-MM-DD`), `theme`, `love_letter`,
  `couple_photo` (foto fixa do casal no card abaixo do player),
  `star_map[{city, lat, lng, datetime}]` (céu real da noite de início, cena da retrospectiva),
  `games[{question, answer, message}]` (mini-jogo Termo na retrospectiva; `answer` só A–Z),
  `tracks[{title, artist, audio, photo}]`, `photos[{src, caption}]`, `slides[]`,
  `highlights[{title, photos[]}]` (destaques estilo stories; capa = `photos[0]`).
  Images and MP3s are `import`ed at the top and referenced by `audio`/`photo`/`src` so
  Vite bundles them.
- **`resources/js/App.vue`** — orchestrator (3-screen state machine: `GiftIntro` →
  `SpotifyPlayer` → overlay `WrappedStories`). Builds the `wrapped` object the components
  expect **from `data.js` at runtime**: computes `days_together` and `moon` from the date,
  and fills photo dominant colors. Owns the audio player (`useAudioPlayer.js`, an HTML5
  `Audio`) and `trackIndex`; ⏮/⏭ swap track (`player.load(src)`), cover, info, and
  background tint; `play()` runs inside the "Ver Presente" tap so the browser allows sound.
- **`resources/js/lib/`** — pure JS helpers: `moon.js` (`moonForDate`, no API),
  `dominantColor.js` (canvas 1×1 average — runtime, same-origin images, falls back to the
  theme gradient if unavailable), `season.js` (`seasonForDate`, hemisfério sul), and
  `astro.js` (low-precision astronomy: `julianDate`/`lstDeg`/`eqToHorizontal`/
  `moonEquatorial`/`bvToColor`) + vendored star catalog `stars.6.json` (d3-celestial,
  GeoJSON `[RA°,Dec°]`+mag+bv) used by the real-sky scene.
- **`resources/js/Components/Public/{GiftIntro,SpotifyPlayer,SpotifyNav,StarMap}.vue`** and
  **`Components/{WrappedStories,HighlightStories}.vue`** — the UI. `WrappedStories` is the
  cinematic "Ver retrospectiva" Wrapped (auto-advancing scenes: intro+equalizer → days →
  **real sky** via `Public/RealSky.vue` → season → for each `games[]`: gameIntro + a
  Termo/Wordle round via `Public/WordleGame.vue` (auto-advance paused while playing;
  confetti burst + "Próxima Seção" on finish) → outro; music keeps playing with a mute
  toggle). `HighlightStories` is the full-screen photo-story viewer
  opened from the "Conheça {casal}" highlights card (App owns `activeHighlight`).
  `Public/RealSky.vue` draws an all-sky dome canvas (real star positions + real moon
  position for `star_map`'s date/lat/lng). `Public/StarMap.vue` is the older stylized
  starfield (no longer wired).
  `themes.js` is the Spotify accent palette;
  `coverGradient(color, theme)` tints the player background from the current photo's
  dominant color, falling back to `playerGradient(theme)`.

## Conventions

- UI copy is in **Portuguese (pt-BR)**.
- Player audio uses **static MP3 files** (HTML5 `Audio`) bundled by Vite from
  `resources/js/assets/`. Playback starts inside a user tap (autoplay-with-sound rule).
- Adding a `theme` requires updating `resources/js/themes.js` (accent palette).
