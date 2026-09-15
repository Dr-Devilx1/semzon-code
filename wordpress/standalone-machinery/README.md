# SEMZON machinery bento — home section 02

```
semzon-machinery.json   → the only file you import
images/                 → the 8 product shots, in case the auto-import misses one
preview-machinery.html  → open in a browser to inspect it locally
m-desktop.png / m-tablet.png / m-mobile.png
```

Same rule as the contact page: **there is no stylesheet to paste.** Every
colour, font, size, padding, border and radius lives inside the template as
Elementor widget and column settings. Import it and it looks right.

## Install — 3 steps

1. **Elementor → Templates → Saved Templates → Import Templates** → upload
   `semzon-machinery.json`
2. Open the page you want it on → **Edit with Elementor** → grey **folder
   icon** → **My Templates** → *SEMZON — Machinery bento (home 02)* → **Insert**
3. **Publish**

It inserts as three ordinary sections (heading, bento row 1, bento row 2), so
you can drop it anywhere on a page and move it up or down like any other
section.

## Widgets used — all standard

| Part | Widgets |
|---|---|
| Heading row | Icon List (the eyebrow), Heading ×2, Button |
| Big pellet-mill tile | Image, Heading ×2, Icon |
| 6 product tiles | Image, Heading ×2 each |
| PLC tile | Spacer, Icon List (the chip) |

No HTML widget, no third-party addon, no `css_classes`. Click any tile and the
settings are right there in the normal Elementor panel.

## About the images

The template points at the eight shots on your live site, e.g.
`https://www.semzoneng.com/assets/img/grinding-hammer-mill-smsp-01.webp`.
Elementor pulls remote images into your media library as part of the import,
so in the normal case they simply appear.

If any tile comes in empty, upload the matching file from `images/` (**Media →
Add New**), then click the tile and pick it. The filenames match the tile
order:

| Tile | File |
|---|---|
| Feed pellet mills | `pelleting-feed-pellet-mill-szlh-cut.webp` |
| Hammer mills | `grinding-hammer-mill-smsp-01.webp` |
| Mixers | `mixing-ribbon-mixer-smhy-01.webp` |
| Extruders | `extrusion-fish-feed-extruder-smhs-01.webp` |
| Coolers & dryers | `cooling-counterflow-cooler-skln-01.webp` |
| Crumblers & screeners | `screening-crumbler-sslg-01.webp` |
| Conveying & packing | `conveying-bucket-elevator-01.webp` |
| PLC & control panels | `automation-plc-panel-01.webp` (it is the PLC tile's **column background**, not an image widget) |

**One thing worth fixing separately:** five of these files have a solid black
background baked into them — hammer mill, mixer, extruder, cooler, crumbler.
That is not the template; your coded site shows the same black boxes today (I
rendered it side by side to check). Whenever you have cut-outs on white or
transparent, swap them in and the tiles will look considerably cleaner.

## How the layout was rebuilt

The coded page draws this with CSS Grid, where the pellet-mill tile spans two
columns **and two rows**. Elementor's classic sections cannot span rows, so it
is built as:

```
row 1 : [ 50% = the big tile ][ 50% = two inner sections, two tiles each ]
row 2 : [ 25% ][ 25% ][ 50% = the full-bleed PLC tile ]
```

which resolves to the identical picture. The tile surface (white, 1px line,
20px corners) sits on the **column**, and the 28px gutter comes from that
column's own margin — Elementor paints a column background inside the margin,
so the tiles keep clean gaps instead of touching.

The tiles are columns rather than Image Box widgets on purpose: an Image Box
scales its picture by width alone, and these eight shots run from 294×368 to
992×787, so a width-driven box would give every tile a different height. The
Image widget takes a fixed height plus object-fit — which is exactly what the
original's `figure` does — so the grid lines up.

## Responsive

| Width | Layout |
|---|---|
| 1025px and up | big tile + 2×2 block, then 2 tiles + wide PLC tile |
| 768–1024px | big tile full width, tiles 2-up, PLC full width |
| below 768px | everything single column |

Type scales with it, using the sizes the coded page actually resolves to:

| Element | Desktop | Tablet | Mobile |
|---|---|---|---|
| H2 | 50px | 42px | 27px |
| Tile title | 16.6px | 15.8px | 14.4px |
| Mono spec | 11.9px | 11.3px | 10.3px |
| Big tile title | 29.75px | 25.6px | 19.1px |

The big "02" numeral is hidden below 1025px, same as the original.

## What was verified

`preview-machinery.html` is generated **from the template's own settings** — it
reads `semzon-machinery.json` and turns each stored value into the CSS
Elementor would produce. It is not a hand-drawn mock, so a wrong value in the
template is wrong in the preview too. Screenshots come from rendering it in a
real browser.

Checked against the coded page rendered at the same widths:

- tile block 1332px wide at 1440 (coded page: 1325), 828px at 900 (828), 350px
  at 390 (350)
- gutter 28 / 20 / 12px — the values the coded page's `clamp()` resolves to
- big tile 652×620, the 2×2 block 296+28+296 = 620 — the two sides line up
  exactly, as they do in the grid
- no horizontal scroll at 360, 390, 575, 576, 767, 768, 900, 1023, 1024, 1280,
  1440, 1536 or 1920

Three things were caught this way and fixed before delivery: the headline
column was too narrow and pushed "All 20+ machines" into two lines under the
numeral; the section had no side padding below 1360px, so tiles ran 4px from
the screen edge on tablet; and the responsive image height was landing on the
wrapper instead of the image, which let the pellet-mill shot overflow its tile
on phones.

## Two deliberate differences from the coded page

1. **The small tiles are packed, not stretched.** In the CSS Grid the four
   top-right tiles are forced to 408px tall and the caption floats at the
   bottom with a large empty gap. That height is a side effect of the grid row
   sizing, and reproducing it in Elementor would mean hard-coding tile heights
   that break the moment the text wraps. The tiles here are as tall as their
   content (296px).
2. **Phones get one column, not two.** The coded page keeps a 2-column grid all
   the way down, which leaves 169px-wide tiles on a 390px screen. Single column
   below 768px reads properly on a phone.

Say the word if you want either of those changed back.

## What this cannot catch

A conflict with your specific theme or another plugin's CSS. Because the
styling is inline on each element it is much harder to override than a
stylesheet, but I have no way to reach a live WordPress install from here. If
anything looks off after importing, send a screenshot and I will target it.
