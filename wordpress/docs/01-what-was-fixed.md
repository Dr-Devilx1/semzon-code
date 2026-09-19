# Audit findings — the original coded site

Phase 1 of the conversion was a full audit of the existing repository. What
follows is what I actually found, tested and changed. The site was in better
shape than expected: the CSS and markup are genuinely well built.

---

## Fixed

### 1. The entire site was duplicated twice
`semzon-website-v2.0/` contained a byte-identical copy of the whole site, and
*that* folder contained another full copy nested inside itself. 138 dead files
— roughly two thirds of the repository. Verified identical with a recursive
diff before removing.

**Fixed** and pushed to the working branch.

### 2. Plant-flow content was hardcoded in JavaScript
The six-stage homepage flow — titles, descriptions and machine links — lived
in a `FLOW` array inside `main.js`. Editing it meant editing JavaScript.

**Fixed**: it is now an ACF options page (SEMZON → Plant Flow) with a repeater
of stages, each with its own machine list linked to real Product posts. The
motion layer reads it through `wp_localize_script`. Same behaviour, editable
by a non-developer, and the machine links now resolve to live permalinks
instead of hardcoded relative paths.

### 3. Map locations were hardcoded
Seven site locations were an array literal in `main.js`.

**Fixed**: seeded into the `semzon_map_locations` option by the wizard.

---

## Found but NOT fixed — needs your action

### Google Maps API key exposed in the page source

`AIzaSyD3gnFTkn5PgyWAqqDbNmKpZ_a2lqE9o4A` is hardcoded in all 46 HTML files
and is in the public GitHub repository. Anyone can read it from the page
source and spend the quota on your billing account.

I cannot fix this from code — the key has to be rotated in the Google Cloud
console:

1. Delete that key.
2. Create a new one.
3. **Restrict it by HTTP referrer** to `semzoneng.com/*` and
   `*.semzoneng.com/*`. An unrestricted key can be lifted from the page by
   anyone, so restriction is the control that actually matters, not secrecy.
4. Paste the new key into SEMZON → Settings.

The WordPress build stores it as an option, so it will not be committed to
source control again.

---

## Checked and clean

I ran an automated crawl of all 46 pages and a headless-browser render of the
10 key page types at mobile, tablet and desktop widths.

| Check | Result |
|---|---|
| Broken internal links | none across 46 pages |
| Missing images | none |
| `<title>` present and unique | all 46 unique |
| Meta description present and unique | all 46 unique |
| Canonical URL | present on all 46 |
| `<img alt>` | present on every image |
| Viewport meta | present on all 46 |
| Sitemap ↔ page count | 46 ↔ 46, consistent |
| robots.txt | valid, points at the sitemap |
| Horizontal overflow | none at 375 / 768 / 1440px |
| JavaScript console errors | none |
| Page-level crashes | none |

The stylesheet is mobile-first with a proper breakpoint ladder, uses
`prefers-reduced-motion` correctly, has a print stylesheet, and the JS
degrades to a fully readable page with GSAP blocked. Structured data
(Organization + Product JSON-LD) is already present on product pages.

Whoever built this did careful work. The conversion preserves it rather than
replacing it.

---

## Deliberately carried over unchanged

These are original design decisions, not defects — kept exactly as they were:

- The 4:3 / 1:1 / 16:10 fixed image frames from `IMAGE-GUIDE.md`
  (now registered as WordPress image sizes so crops are generated server-side)
- The three image treatments: `render` (multiply blend), `cut` (drop shadow),
  `photo` (fills frame)
- All GSAP timings, easings and ScrollTrigger thresholds
- The Aurora v2.0 visual pass — glass surfaces, gradient glows, conic
  atmospherics
- Copy: every heading, paragraph and spec value is verbatim from the original
- The contact form's WhatsApp/email hand-off behaviour (no data is stored on
  the site, which is what the form's own note promises)

## URL change — and why

The original nests products two levels deep:
`/products/grinding/hammer-mill/`.

Putting a taxonomy term inside a WordPress CPT permalink needs custom rewrite
rules that break the moment a machine is recategorised, and any such URL would
still need a redirect when it moved. The build uses flat
`/products/hammer-mill/` URLs with **301 redirects from all 22 old paths**
(including the `/index.html` variants), which preserves ranking signal and
stays stable. The redirect map is in
`semzon-child/inc/post-types.php` — in code, so it survives a
database restore and is reviewable.

If you would rather keep the nested URLs exactly, say so and I will implement
the rewrite rules instead — it is doable, just more fragile.
