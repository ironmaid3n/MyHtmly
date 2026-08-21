# Editorial — an HTMLy theme

A fast, magazine-style theme built for `htmly.edil.pro`, following HTMLy's
official theming docs (structure, variables, advanced theming, widgets).

## Install

1. Upload the whole `editorial` folder to `themes/` on your server, so you get:
   `themes/editorial/layout.html.php`, etc.
2. In HTMLy admin → **Settings → Config**, set **Theme** to `editorial` and save.
3. Hard-refresh the site once (Ctrl/Cmd+Shift+R) so your browser picks up the
   new stylesheet.

That's it — no build step, no npm, no database. It's plain PHP + one CSS file
+ one tiny JS file, exactly like every other HTMLy theme.

## What's inside

```
editorial/
  css/style.css        one stylesheet, ~28KB (~6KB gzipped)
  js/main.js            ~4KB (<1KB gzipped) — dark mode toggle, back-to-top,
                         closes the mobile menu after a tap
  img/favicon.svg        placeholder monogram favicon — swap for your logo
  functions.php          theme helpers (excerpts, initials, cache-busting,
                          thumbnail fallback) — auto-loaded by HTMLy
  partials/
    post-card.html.php   the post card used on home/category/tag/profile
    sidebar.html.php     search + recent + popular + categories + tags + archive
  layout.html.php        header, nav, footer, dark-mode + all <head> tags
  main.html.php          post listing (home, category, tag, archive, search)
  post.html.php          single post
  static.html.php        static pages (+ sub-page grid for parent pages)
  substatic.html.php     nested static pages (e.g. /about/me)
  profile.html.php       author page
  no-posts.html.php / 404.html.php / 404-search.html.php
```

## Design notes

- **No web fonts, no icon fonts, no external CDN calls.** Every icon is
  inline SVG and typography uses the visitor's system font stack (with good
  Greek-glyph coverage). That means the browser makes exactly **one**
  request for CSS and **one** for JS — nothing else — which keeps things
  fast on top of HTMLy's own page cache.
- **Dark mode** follows the visitor's OS setting automatically, plus a
  manual toggle (top right) that's remembered via `localStorage`. A tiny
  inline script in `<head>` applies the saved preference before first paint,
  so there's no flash of the wrong theme — and because it runs entirely in
  the browser, the cached HTML HTMLy serves stays identical for every
  visitor either way.
- **Cache-friendly by design.** HTMLy caches whole rendered pages, so this
  theme never does anything that would make a cached page render
  differently per-visitor (no server-side sessions, no per-request
  randomness). The one place assets need to change without waiting for a
  cache purge — your CSS/JS files — uses `editorial_ver()` in
  `functions.php`, which appends the file's own last-modified time as a
  `?v=` query string. Edit `style.css`, reload, and visitors get the new
  styles immediately, while unrelated pages stay served straight from
  HTMLy's cache.
- **Mobile menu and search reveal are pure CSS** (checkbox toggles) — no
  JavaScript required for the site to be fully navigable. JS only adds the
  dark-mode toggle, the back-to-top button's show/hide, and closing the
  mobile drawer after tapping a link.
- **Images** use `loading="lazy"` (except the single-post cover, which is
  `eager`/`fetchpriority="high"` since it's above the fold) and posts
  without an image fall back to a CSS gradient monogram instead of a broken
  image or an external placeholder request.
- **Layout**: a two-column grid (content + sidebar) on desktop that
  collapses to one column on mobile; the very first post on the homepage's
  first page renders as a larger "featured" card.

## Customizing colors

Everything is driven by CSS variables at the top of `css/style.css`:

```css
:root {
  --accent: #a8452f;         /* your brand/accent color */
  --accent-strong: #8a3423;  /* hover/darker variant */
  --bg: #fbfaf8;             /* page background */
  ...
}
```

Change `--accent` and `--accent-strong` and the whole theme re-colors
(badges, links, buttons, drop-caps, hover states). Dark mode has its own
matching block right below, plus a `prefers-color-scheme` copy for visitors
who haven't toggled manually.

## Lighthouse fixes (performance / accessibility)

If you ran this theme through PageSpeed Insights / Lighthouse, here's
exactly what was found and what changed:

**Accessibility — failing color contrast.**
`--text-faint` (used for dates, read-time, view counts, footer credit)
was `#948d86`, which only hits a 3.1–3.3:1 contrast ratio against the
background — below the WCAG AA minimum of 4.5:1 for normal-size text.
The dark-mode value had the same problem (4.45:1, just under). Both are
now darkened/lightened to `#726a62` (light) and `#968c80` (dark), giving
a safe 5.0–5.5:1 ratio. The accent color was also darkened slightly
(`#a8452f` → `#903a26`) so category pills and links clear AA with margin
too — verified with an actual WCAG contrast calculation, not eyeballing.

**Performance — render-blocking request.**
The theme previously linked `css/style.css` and `js/main.js` as normal
`<link>`/`<script>` tags, each costing one extra network round trip
before the page could render. Both are now inlined directly into
`layout.html.php` (`file_get_contents()` into a `<style>`/`<script>`
block), with an automatic fallback to the old `<link>`/`<script src>`
tags if the file can't be read for any reason. A fully cached HTMLy page
now needs exactly one HTTP request total for CSS+JS+HTML combined.

  *Trade-off to know about:* because the CSS/JS are baked into HTMLy's
  cached HTML output, editing `style.css` or `main.js` won't show up on
  already-cached pages until you clear HTMLy's page cache (Settings →
  clear cache, or just wait for it to expire). Content edits (new posts,
  etc.) are unaffected — this only applies to theme file edits.

**Performance — Cumulative Layout Shift.**
Two images didn't reserve their box size before loading, so the page
shifted around as they loaded in:
  - the single-post cover image now has a fixed 16:9 box
    (`.post-cover--img`) with `object-fit: cover`, plus matching
    `width`/`height` attributes;
  - the homepage's featured (first) post card already had matching
    `width`/`height` attributes tied to its CSS `aspect-ratio`, and the
    featured image is loaded `eager`/`fetchpriority="high"` (it's the
    Largest Contentful Paint candidate) while every other card image
    stays `loading="lazy"`.

**What's outside the theme's control.**
Two Lighthouse notes can't be fixed from a theme alone:
  - *"Improve image delivery"* — this is about the resolution/weight of
    the actual photos you upload through HTMLy, not the theme markup.
    Compressing images before upload (e.g. with Squoosh) or keeping
    them close to their display size helps here.
  - *"Use efficient cache lifetimes"* — this is an HTTP response-header
    setting on your web server, not something PHP/CSS can set from
    inside the theme. See `htaccess-caching-snippet.txt` in this folder
    for a ready-to-paste Apache snippet that sets long `Cache-Control`
    headers for images/fonts on `htmly.edil.pro`.

## Notes

- Sidebar widgets (`recent_posts()`, `popular_posts()`, `category_list()`,
  `tag_cloud()`, `archive_list()`, `search()`) are all official HTMLy
  widget functions — nothing custom or fragile.
- `functions.php` only defines `editorial_*`-prefixed functions, so it
  won't collide with core HTMLy or any other plugin/theme.
- If you'd like a child-theme approach instead (e.g. only overriding
  colors), HTMLy supports that too — see "Child Theme" in the docs.
