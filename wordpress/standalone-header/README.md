# SEMZON header — install in 5 minutes

Two files. **Both are required** — the JSON is the layout, the CSS is the look.
Importing only the JSON gives you a plain unstyled header.

```
semzon-header.json   → import into Elementor
semzon-header.css    → paste into Additional CSS
```

Works on a fresh site. No SEMZON theme, no plugin, no custom post types needed.

---

## Step 1 — paste the CSS first

**Appearance → Customize → Additional CSS** → paste the whole of
`semzon-header.css` → **Publish**.

Do this before importing so the header looks right the first time you see it.

Additional CSS works on any theme and needs no Elementor Pro. If you would
rather keep it in Elementor: **Elementor → Site Settings → Custom CSS** (Pro
only) works just as well. Do not use both.

---

## Step 2 — import the layout

**Elementor → Templates → Saved Templates → Import Templates** → upload
`semzon-header.json`.

It imports as a **section**, on purpose. Theme Builder document types only
validate while Elementor Pro's theme-builder module is loaded, so importing as
a "header" fails on some setups with no useful error. A section imports
everywhere.

---

## Step 3 — put it in your header

**With Ultimate Addons / Elementor Header & Footer Builder:**

1. **Appearance → Header Footer Builder → Add New**
2. Title it *SEMZON Header*, set **Type of Template: Header**,
   **Display On: Entire Website**
3. **Edit with Elementor**
4. Click the grey **folder icon** in the canvas → **My Templates** tab →
   find *SEMZON — Header* → **Insert**
5. Delete the empty starter section Elementor added → **Publish**

**With Elementor Pro Theme Builder:** Templates → Theme Builder → Header →
same folder-icon insert, condition *Entire Site*.

---

## Step 4 — your logo

The template ships an **Image widget** (as you asked), not text.

Select it → **Choose Image** → upload your logo → done. The CSS sizes it to
168px wide on desktop, 140px on tablet, 120px on phones, so any reasonably
proportioned logo fits without touching a setting.

A transparent PNG around 986×267 works best.

---

## Step 5 — the menu

The template has a dashed **MENU SLOT** block where the menu goes. It is empty
on purpose: hardcoding UAE's widget would break the import for anyone without
Ultimate Addons, and hardcoding Elementor Pro's would break it on free.

1. Delete the dashed block
2. Drag in your nav widget — **UAE Nav Menu** or **Elementor Pro Nav Menu**
3. Settings: **Layout: Horizontal**, **Breakpoint: 1024px** (or Tablet)
4. Pick your WordPress menu

The CSS styles UAE, Elementor Pro and a plain WordPress menu identically, so
whichever you use comes out the same.

---

## Your dropdown bug — what it was and what fixes it

Three things break dropdowns in an Elementor header. The CSS fixes all three
rather than hoping it was only one of them:

| Cause | Symptom | Fix in the CSS |
|---|---|---|
| `overflow: hidden` on the section, column or widget wrap | submenu is cut off or invisible | everything inside `.semzon-header` forced to `overflow: visible` |
| submenu z-index below page content | submenu opens *behind* the hero | header `z-index: 999`, submenu `z-index: 1000` |
| `position: sticky` makes a new stacking context | submenu lands in the wrong place | every `li` explicitly positioned so the submenu anchors to its item |

If dropdowns still misbehave after this, it is the nav widget's own setting —
check that the widget's **Submenu / Dropdown** option is enabled, not that the
menu items simply have no children assigned in **Appearance → Menus**.

---

## What it looks like

`preview-desktop.png`, `preview-laptop.png` and `preview-mobile.png` are real
screenshots of this exact CSS rendered against Elementor's real DOM structure,
not mockups. The desktop shot shows the dropdown open over page content.

Behaviour by width, matching the original site:

| Width | Utility bar | Menu | CTA button |
|---|---|---|---|
| 1024px and up | visible | horizontal pills | visible |
| below 1024px | hidden | hamburger | hidden (menu carries it) |

Verified: no horizontal scroll at 390 / 1280 / 1440px; header z-index 999;
submenu z-index 1000; widget wrap overflow visible; utility bar correctly
hidden on mobile.

---

## Honest note

I cannot reach a WordPress install from where I work, so this was verified by
rendering the shipped CSS against Elementor's DOM in a real browser — which is
how the three bugs above were caught before you saw them. What it cannot catch
is a conflict with your specific theme or another plugin's CSS. If something
looks off, send me a screenshot and I will target it.

The one thing that will differ: this is the **standard header**, not the
original's full mega-menu panels (the four-column grids with the feature
image). Those need custom markup and JavaScript that Elementor's nav widgets
cannot produce. Say the word if you want those back and I will explain the
options.
