# Header, homepage and the sections Elementor builds by hand

## The header ships working — you don't build it

`semzon-child/header.php` renders the full navigation: utility bar, brand,
both mega panels, the desktop CTA, the burger and the mobile drawer. It is
already wired to the design system's CSS and to the hover-intent JavaScript.

**It is content-managed, not hardcoded.** The panels are generated from real
content:

- **Products mega** — reads the `Product Category` taxonomy and lists the
  machines in each. Add a product in wp-admin, assign a category, and it
  appears in the menu. Nothing to edit.
- **Solutions mega** — reads the Solution post type, splitting feed lines from
  biomass/fertilizer lines.
- **Feature tiles** — the SZLH pellet mill and the Kabul project, pulled by
  slug with their featured images.
- **The four plain items** (Projects, Industries, Services, About) — assign a
  menu to the **Primary** location under Appearance → Menus to control these;
  without one, they fall back to the original four.
- **Phone, email, address, WhatsApp number** — read from `semzon_*` options so
  they are changed in one place, not in 46 files.

### Why not an Elementor Theme Builder header

The panels need `data-mega` attributes and the exact
`.mega → .mega-in → .mega-feat` structure that the CSS grid and the
hover-intent script both depend on. Elementor's Nav Menu and Mega Menu widgets
emit their own markup and cannot produce it. Building the header in Elementor
would mean redesigning the navigation, which the brief rules out. A theme
template part is standard WordPress architecture — it is not an HTML widget
and not a custom Elementor widget.

The **footer** has no bespoke JavaScript, so it is a good Theme Builder
candidate: import `footer.json` and `footer.php` steps aside automatically. If
you don't, the static footer in `footer.php` renders instead, so the site is
never left without contact details mid-build.

---

## The homepage — build in Elementor

The home page is genuinely unique, so it is a normal WordPress page built with
Elementor. Give each container the class listed here and the design system
styles it; the JavaScript picks up the IDs.

| Section | Container class | Required id | Notes |
|---|---|---|---|
| Hero | `hero` | — | inner grid `hero-grid`; copy column `hero-copy`; H1 needs id `hero-h1` |
| Hero product card | `hm-card` | `hm-card` | id drives the 3D pointer tilt |
| Hero rail | `hero-rail` | — | one `rail-card rail-card--lg` + a `rail-duo` of two |
| Facts strip | `facts` | — | inner `facts-card` |
| Marquee | `marquee` | — | track needs id `mq-track` |
| Production-line cards | `grid grid--3` | — | Loop Grid over Products, item class `card` |
| **Plant flow** | `section` | `flow` | see below |
| Turnkey steps | `steps-wrap` | — | line element needs id `steps-line` |
| Feature + stats | `feature` | — | figure `feature-fig`, stats `stats` |
| Industry pills | `pillbox` | — | each `pill` |
| Quality | `quality-grid` | — | `iso` + `qa-card` |
| Global presence | `globe` | — | map card `gmap-card`, inner div id `gmap` |
| Leadership | `lead-band` | — | inner `lead-grid` |
| FAQ | `faq` | — | each `faq-item` with `faq-q` / `faq-a` |
| CTA band | `band-wrap` | `contact-band` | id also hides the mobile action bar here |

### The plant-flow section

This is the one section with real behaviour: on desktop it pins and scrubs
through six stages as you scroll; on mobile it becomes a tap-through list.

Build the shell only — three empty containers with these ids:

```
section id="flow"
  div id="flow-rail"    ← stage buttons are injected here
  div class="flow-panel"
    div class="flow-desc"
      h3 id="flow-title"
      p  id="flow-text"
    div class="flow-mach"
      ul id="flow-mach"
```

The stage content comes from **SEMZON → Plant Flow** (already seeded with all
six stages and their thirteen machine links). Leave the containers empty —
the script fills them and rebuilds them as the user scrolls.

### Counters

Any element with a `data-count="94"` attribute animates from zero when it
scrolls into view, and shows the final number immediately if motion is
reduced. Set it via Elementor's **Advanced → Attributes** field.

---

## Reveal animations

Add the class `rv` to any container or widget and it fades and rises into view
on scroll, staggered with its siblings. Headings with `display-2 rv` get the
per-word reveal instead. This is how the original site animates — it is a
class, not a setting, so use it rather than Elementor's own entrance
animations. Mixing the two double-animates the element.

---

## What to leave alone

- Don't set per-device padding/margin in Elementor on elements that already
  have design-system classes. The stylesheet's `clamp()` values handle all six
  ranges; an Elementor override pins them and breaks the fluid scaling between
  breakpoints.
- Don't set container width on `.container` / `.container--wide` elements —
  they read `--container` and `--container-wide` tokens.
- Don't enable Elementor's own entrance animations on `.rv` elements.
