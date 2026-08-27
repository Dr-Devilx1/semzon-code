# SEMZON — WordPress conversion kit

Everything needed to stand the SEMZON site up on WordPress + Elementor Pro +
ACF PRO, reproducing the existing coded design rather than redesigning it.

---

## What's in the box

```
semzon-child/          ← WordPress theme  (child of Hello Elementor)
semzon-setup/          ← WordPress plugin (installer + content architecture)
elementor-templates/   ← 7 importable Elementor .json templates
docs/                  ← these guides
```

## Install order — 20 minutes

**1. Plugins first.** Install and activate, in this order:

| Plugin | Why | Cost |
|---|---|---|
| Elementor (free) | page builder core | free |
| **Elementor Pro** | Theme Builder, dynamic tags, Loop Grid | you have it |
| **ACF PRO** | repeater fields — spec tables, feature lists, flow stages | you have it |
| Hello Elementor (theme) | the parent theme | free |

Nothing else is required to build the site. Add Rank Math (SEO), a caching
plugin and an image optimiser at go-live, not now.

**2. Theme.** Upload `semzon-child/` to `wp-content/themes/`, then
Appearance → Themes → activate **SEMZON Engineering**.
Hello Elementor must be installed but stays deactivated — a child theme needs
its parent present, not active.

**3. Plugin.** Upload `semzon-setup/` to `wp-content/plugins/`, activate
**SEMZON Setup**.

**4. Run the wizard.** wp-admin → **SEMZON → Setup Wizard** → *Run setup*.

It runs nine steps and reports each one:

1. Check requirements
2. Register post types & taxonomies
3. Configure Elementor breakpoints, colours & fonts
4. Create the unique pages
5. Import 22 products
6. Import 8 solutions
7. Import 6 project case studies
8. Link related machines & plant flow
9. Flush permalinks

Every step is safe to re-run — posts are matched by slug and updated, images
are matched by filename before being added. Running it twice gives you the
same site, not two of it.

**5. Templates.** Elementor → Templates → Import, upload each file from
`elementor-templates/`, then set its display condition (Theme Builder tells
you where). See `02-elementor-build-guide.md`.

**6. Settings.** SEMZON → Settings — paste your Google Maps key if you want
the live map. Leave empty and the styled fallback graphic stays.

---

## What the wizard gives you

- **3 post types**: Product, Solution, Project
- **2 taxonomies**: Product Category (11 terms), Industry
- **All custom fields registered** — no manual ACF import; they appear the
  moment the plugin is active
- **36 catalogue entries** fully populated: every heading, paragraph, feature
  list, spec-table row and tag pill lifted from the existing site
- **36 images** added to the media library and attached to the right entries
- **The plant-flow section** seeded into an editable options page
  (SEMZON → Plant Flow) instead of being hardcoded in JavaScript
- **Elementor globals** set from the design tokens — 4 system colours,
  15 custom colours, 4 type styles, and the six required breakpoints

---

## Architecture, briefly

The original build is a bespoke design: GSAP ScrollTrigger choreography, a
pinned scroll-scrubbed plant-flow, `mix-blend-mode` image treatments,
`mask-image` atmospherics, conic-gradient glows, hover-intent mega menus.
Elementor's widget UI does not expose any of that.

So the split is:

| Concern | Where it lives | Why |
|---|---|---|
| Visual design system | child theme stylesheet | one source of truth; pixel-identical to the original by construction |
| Motion & interaction | child theme JS | the GSAP timelines are the design |
| Page structure | Elementor containers + widgets | editable in wp-admin by a normal administrator |
| Repeated content | CPT + ACF | one template renders all 22 products |
| Global colours / fonts / breakpoints | Elementor kit | editable through Elementor's own UI |

Elementor elements carry the design system's class names through Elementor's
native **CSS Classes** field. That is a first-class Elementor feature and the
standard way agencies ship pixel-accurate builds — it is not an HTML-widget
dump, and there are no custom Elementor widgets anywhere in this kit.

---

## Two things you must do yourself

**1. Rotate the Google Maps API key.** The old key
(`AIzaSyD3gn…`) is committed in all 46 files of the original static site and
is in the public GitHub repo. Anyone can read it and spend your quota. In the
Google Cloud console: delete that key, create a new one, and restrict it by
HTTP referrer to `semzoneng.com/*` before pasting it into SEMZON → Settings.
This plugin stores the key as a WordPress option so it is never committed
again — but rotating the exposed one is on you, and it should be done today.

**2. Replace 8 placeholder illustrations.** These products still use SVG
line-art rather than photographs, per the original `IMAGE-GUIDE.md`:
drum cleaner, paddle mixer, oil adding, liquid adding, conveyors, ancillary
equipment, storage tanks, NPK granulator. Send real photos at 1200×900 and
they drop straight into the `hero_image` field — the CSS frame is fixed, so
the layout cannot break.

---

## Honest scope note

The templates in `elementor-templates/` ship with the **container structure,
CSS classes and section order complete**. The ACF dynamic bindings are
labelled in-canvas (`{{ACF: field_name}}` and dashed build-note blocks)
rather than pre-wired, because Elementor Pro serialises dynamic tags in an
internal format that cannot be verified without importing into a live
WordPress. Pre-wiring bindings I could not test would risk exactly the silent
breakage you asked to avoid; attaching them in the UI is two clicks per field
and is guaranteed correct. `02-elementor-build-guide.md` lists every binding.

Continue to `01-what-was-fixed.md` and `02-elementor-build-guide.md`.
