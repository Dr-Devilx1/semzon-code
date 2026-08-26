# SEMZON Website — Image Guide (v1.5)

Every image slot has a **fixed CSS frame** (aspect-ratio + object-fit). Replace any image
with one of the same category size — same path, same filename — and the layout never breaks.
No code change needed. All images: **WebP preferred, ≤300 KB**, filename pattern
`category-name-model-NN.webp`.

## Quick dimension chart (category-wise)

| # | Category | Aspect | Export size | Background | Fit |
|---|----------|--------|------------|------------|-----|
| 1 | Header logo | — | 986×267 px (shown ~168 px wide) | Transparent PNG | — |
| 2 | Footer logo (white version) | — | 986×267 px | Transparent PNG | — |
| 3 | Favicon | 1:1 | 192×192 px (512 source ideal) | Transparent PNG | — |
| 4 | Hero machine cut-out | 4:5 | 1080×1350 px | Transparent | contain |
| 5 | Hero rail cards | 4:5 | 480×600 px | Photo | cover |
| 6 | Machine render (product pages + tiles) | 4:3 | 1200×900 px | White or transparent | contain |
| 7 | Machine cut-out (drop-shadow style) | 1:1 | 1000×1000 px | Transparent | contain |
| 8 | Project / facility photo | 16:10 | 1600×1000 px | Photo | cover |
| 9 | Portrait (CEO / team) | 3:2 | 900×600 px | Photo | cover |
| 10 | Mega-menu feature | 4:3 | 800×600 px | Either | contain/cover |

## Every image slot, file by file (all under `assets/`)

### Brand (`assets/brand/`)
- `semzon-logo.png` — header, all 41 pages — cat 1
- `semzon-logo-white.png` — footer, all pages (auto-generated white version; replace with official white logo when available) — cat 2
- `favicon.png` — browser tab, all pages — cat 3

### Machine renders — cat 6 (4:3, 1200×900)
- `grinding-hammer-mill-smsp-01.webp` — Hammer Mill page, homepage bento, related tiles
- `cleaning-maize-pre-cleaner-cp-01.webp` — Maize Pre-Cleaner page + tiles
- `cleaning-pellet-cleaner-01.webp` — Pellet Cleaner page + tiles
- `screening-vibration-screener-sfjh-01.webp` — Vibration Screener page + tiles
- `screening-crumbler-sslg-01.webp` — Crumbler page + tiles
- `extrusion-fish-feed-extruder-smhs-01.webp` — Fish Feed Extruder page, aqua line page
- `extrusion-dry-extruder-sphg-01.webp` — Dry Extruder page + tiles
- `cooling-dryer-shgw-01.webp` — Belt Dryer page, biomass line page
- `conveying-bucket-elevator-01.webp` — Bucket Elevator page + tiles
- `conveying-rotary-distributor-01.webp` — Rotary Distributor page + tiles
- `packing-packing-scales-01.webp` — Packing Scales page + tiles
- `pelleting-feed-pellet-mill-szlh-01.webp` — Products mega-menu feature (cat 10)
- `automation-plc-panel-01.webp` — PLC page (photo, cover-fit)

### SVG placeholders — replace with cat-6 renders (1200×900) when photos are available
Send the photo; the file gets swapped at the same slot:
- `illus-drum-cleaner.svg` — Drum Cleaner page
- `illus-paddle-mixer.svg` — Paddle Mixer page
- `illus-oil-adding.svg` — Oil Adding page
- `illus-liquid-adding.svg` — Liquid Adding page
- `illus-conveyors.svg` — Conveyors page
- `illus-ancillary.svg` — Ancillary Equipment page
- `illus-storage-tanks.svg` — Storage Tanks page
- `illus-npk-granulator.svg` — NPK solution page

### Machine cut-outs — cat 7 (1:1, 1000×1000, transparent)
- `pelleting-feed-pellet-mill-szlh-cut.webp` — homepage hero card + Feed Pellet Mill page
- `mixing-ribbon-mixer-smhy-cut.webp` — Ribbon Mixer page
- `cooling-counterflow-cooler-skln-cut.webp` — Counterflow Cooler page

### Project & facility photos — cat 8 (16:10, 1600×1000)
- `project-albadar-feed-kabul-01.webp` / `-02.webp` — Projects, Kabul case study, Solutions mega feature
- `project-roshan-feeds-gujranwala-01.webp` — Projects, poultry line page, hero rail (cat 5 crop)
- `project-auriga-bop-karachi-01.webp` — Projects, SSP + BOP pages
- `project-quetta-wmc-01.webp` — Projects, compost page
- `project-subhan-feeds-kasur-01.webp` — Projects
- `project-alitifaq-feeds-kasur-01.webp` — Projects
- `facility-facade-01.webp`, `facility-workshop-collage-01.webp` — About page

### Portrait — cat 9 (3:2, 900×600)
- `leadership-ceo-shoukat-ali-01.webp` — About page + homepage leadership band

### Documents (`assets/docs/`)
- `semzon-catalogue-part-1.pdf`, `semzon-catalogue-part-2.pdf` — Downloads page
