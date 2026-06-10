# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A personal, single-couple **"Spotify Wrapped"** static site (no backend). One couple's
data and images are hardcoded; the page is a **faithful Spotify-app skin** (dark `#121212`,
green `#1DB954`) with a 3-screen flow: gift intro → music player (YouTube) → animated
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
  `tracks[{title, artist, youtube_url, photo}]`, `photos[{src, caption}]`, `slides[]`.
  Images are `import`ed at the top and referenced by `photo`/`src` so Vite bundles them.
- **`resources/js/App.vue`** — orchestrator (3-screen state machine: `GiftIntro` →
  `SpotifyPlayer` → overlay `WrappedStories`). Builds the `wrapped` object the components
  expect **from `data.js` at runtime**: computes `days_together` and `moon` from the date,
  extracts each track's `youtube_id`, and fills photo dominant colors. Owns the YouTube
  player (`useYouTubePlayer.js` + hidden `#yt-player`) and `trackIndex`; ⏮/⏭ swap track
  (`yt.load(videoId)`), cover, info, and background tint.
- **`resources/js/lib/`** — pure JS helpers: `youtube.js` (`idFrom`), `moon.js`
  (`moonForDate`, no API), `dominantColor.js` (canvas 1×1 average — runtime, same-origin
  images, falls back to the theme gradient if unavailable).
- **`resources/js/Components/Public/{GiftIntro,SpotifyPlayer,SpotifyNav,StarMap}.vue`** and
  **`Components/WrappedStories.vue`** — the UI. `themes.js` is the Spotify accent palette;
  `coverGradient(color, theme)` tints the player background from the current photo's
  dominant color, falling back to `playerGradient(theme)`.

## Conventions

- UI copy is in **Portuguese (pt-BR)**.
- Player audio uses the **YouTube IFrame API** (client-side). A video only plays if its
  owner allows embedding — unrelated to hosting; swap the link if one is blocked.
- Adding a `theme` requires updating `resources/js/themes.js` (accent palette).
