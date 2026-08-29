# SEMZON — WordPress conversion kit

Everything needed to stand the SEMZON site up on WordPress + Elementor Pro +
ACF PRO, reproducing the existing coded design rather than redesigning it.

---

## Files you were sent

| File | What it is | Where it goes |
|---|---|---|
| `semzon-child.zip` | The theme — **permanent** | Appearance → Themes → Add New → Upload |
| `semzon-setup.zip` | The installer — **delete after setup** | Plugins → Add New → Upload |
| `elementor-templates.zip` | 7 Elementor templates | Elementor → Templates → Import |
| `docs.zip` | These guides | keep on your computer |

---

## The one rule that shaped this build

> *"1 bar agr cheezain setup ho gai to files ko remove krny sy website p koi
> effect ni ana chaiya"*

So the split is deliberate:

**`semzon-setup` plugin — a one-shot installer. Delete it when done.**
It imports content and images, configures Elementor, and creates pages.
It registers **nothing** the finished site depends on. It has no front-end
code at all — it only runs inside wp-admin. Deleting it changes nothing on
the website.

**`semzon-child` theme — permanent, and that is normal.**
Every WordPress site has an active theme; this is yours. It holds the design
system, the motion layer, the header/footer, and the registrations the site is
built on (post types, custom fields, settings). This is not "extra files" —
it *is* the website.

I verified this by simulation, not by assumption: the test harness runs the
full setup, then deletes the plugin and re-loads the theme alone. Products,
solutions, projects, all four field groups, the Plant Flow editor, the SEMZON
settings menu and every imported value are still there. 123 checks, all green.

---

## Install — 20 minutes

**1. Plugins.** Install and activate:

| Plugin | Why |
|---|---|
| Elementor (free) | page builder core |
| **Elementor Pro** | Theme Builder, dynamic tags, Loop Grid |
| **ACF PRO** | repeater fields — spec tables, feature lists, flow stages |

Also install **Hello Elementor** (theme) but leave it deactivated — a child
theme needs its parent present, not active.

Add Rank Math (SEO), caching and image optimisation at go-live, not now.

**2. Theme.** Appearance → Themes → Add New → Upload `semzon-child.zip` →
Install → **Activate**.

**3. Installer.** Plugins → Add New → Upload `semzon-setup.zip` → Install →
**Activate**.

**4. Run it.** wp-admin → **SEMZON → Setup Wizard** → *Run setup*.

Eight steps, each reporting its own result:

1. Check requirements
2. Write breakpoints, colours & fonts into Elementor
3. Create the unique pages
4. Import 22 products
5. Import 8 solutions
6. Import 6 project case studies
7. Link related machines & plant flow
8. Flush permalinks

Every step is safe to re-run — content is matched by slug and updated, images
matched by filename before being added. Running it twice gives you the same
site, not two of it.

**5. Delete the installer.** Plugins → SEMZON Setup → Deactivate → Delete.
The site does not change. (Keeping it is also fine — it just sits idle.)

**6. Templates.** Elementor → Templates → Import each file from
`elementor-templates.zip`, then set its condition. See
`02-elementor-build-guide.md`.

**7. Settings.** SEMZON → Settings — phone, email, address and the Google Maps
key. These feed the header, footer and mobile action bar everywhere, so you
change them once here rather than in any template.

---

## What the wizard produces

- **3 post types** (Product, Solution, Project) and **2 taxonomies**
  (Product Category with 11 terms, Industry)
- **All custom fields already registered** — no ACF import step; they appear
  the moment the theme is active
- **36 catalogue entries** fully populated: every heading, paragraph, feature
  list, spec-table row and tag pill taken verbatim from the existing site
- **36 images** added to the media library and attached to the right entries
- **The plant-flow section** seeded into an editable options page
  (SEMZON → Plant Flow) instead of being hardcoded in JavaScript
- **Elementor globals** written from the design tokens — 4 system colours,
  15 custom colours, 4 type styles, and the six required breakpoints

---

## Architecture, briefly

The original is a bespoke design: GSAP ScrollTrigger choreography, a pinned
scroll-scrubbed plant flow, `mix-blend-mode` image treatments, `mask-image`
atmospherics, conic-gradient glows, hover-intent mega menus. Elementor's widget
UI exposes none of that.

| Concern | Where it lives | Why |
|---|---|---|
| Visual design system | theme stylesheet | one source of truth; pixel-identical by construction |
| Motion & interaction | theme JS | the GSAP timelines *are* the design |
| Page structure | Elementor containers + widgets | editable by a normal administrator |
| Repeated content | CPT + ACF | one template renders all 22 products |
| Global colours / fonts / breakpoints | Elementor kit | editable through Elementor's own UI |

Elementor elements carry the design system's class names through Elementor's
native **CSS Classes** field — a first-class Elementor feature and the standard
way agencies ship pixel-accurate builds. There is no HTML-widget dump and no
custom Elementor widget anywhere in this kit.

---

## Two things only you can do

**1. Rotate the Google Maps API key — today.**
`AIzaSyD3gn…` is hardcoded in all 46 files of the original static site and is
in the public GitHub repo. Anyone can read it and spend your quota. In the
Google Cloud console: delete it, create a new one, **restrict it by HTTP
referrer** to `semzoneng.com/*`, then paste the new one into SEMZON → Settings.
The build stores it as a WordPress option so it is never committed again — but
the exposed one still has to be revoked by you.

**2. Replace 8 placeholder illustrations.**
These still use SVG line-art rather than photographs, per the original
`IMAGE-GUIDE.md`: drum cleaner, paddle mixer, oil adding, liquid adding,
conveyors, ancillary equipment, storage tanks, NPK granulator. Send photos at
1200×900 and they drop into the `hero_image` field — the CSS frame is fixed,
so the layout cannot break.

---

## What I could and could not verify

**Verified** — 123 automated checks, all passing:
PHP parses across every file; the theme registers all post types, taxonomies
and field groups; every field name the importer writes exists in a registered
group; the importer produces 22/8/6 entries with correct repeater shapes and
resolved relationships; re-running creates no duplicates; the plugin registers
nothing permanent and has no front-end hooks; **deleting the plugin leaves the
site fully intact**; the six breakpoints ascend in the order Elementor
requires. Plus CSS brace balance and a JS syntax check.

**Not verified** — this environment has no network access, so I could not
download WordPress, Elementor or ACF and run a real install. The checks above
run against a WordPress stub, which catches logic and contract errors but
cannot catch a genuine plugin-API incompatibility. Treat the first wizard run
as the real test — run it on the subdomain first, which is what you are
already doing. `04-qa-checklist.md` is the list to work through.

**Templates ship structurally complete, not pre-bound.** Elementor Pro
serialises ACF dynamic tags in an internal format I could not verify without a
live import. Rather than ship bindings that might silently fail, every dynamic
slot is labelled in-canvas (`{{ACF: hero_lead}}` and dashed note blocks) —
two clicks each to attach, guaranteed correct. Full map in `02`.

Next: `01-what-was-fixed.md`, then `02-elementor-build-guide.md`.
