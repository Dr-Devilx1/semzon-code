# SEMZON — Custom HTML → WordPress (Elementor Pro) migration kit

This folder is the complete, no-code migration package for turning the 22
one-off Product pages, 8 Solution pages and 6 Project pages into three
WordPress Custom Post Types driven by ACF PRO + Elementor Pro's Theme
Builder. No custom plugin, no HTML/code widgets — everything below is a
value you type into a WordPress admin screen, or a file you import through
a plugin's own "Import" button.

## Files in this folder

| File | What it is | Where it goes |
|---|---|---|
| `01-post-types-and-taxonomies.md` | Exact labels/settings to type into ACF PRO's **Custom Fields → Post Types** and **→ Taxonomies** screens | You type these into WP admin |
| `acf-field-group-product.json` | ACF field group for the Product post type | **Custom Fields → Tools → Import Field Groups** |
| `acf-field-group-solution.json` | ACF field group for the Solution post type | same Import screen |
| `acf-field-group-project.json` | ACF field group for the Project post type | same Import screen |
| `content-export-products.json` / `.csv` | All 22 products, every field extracted from the live HTML | Reference while creating each Product entry, or feed to WP All Import |
| `content-export-solutions.json` / `.csv` | All 8 solutions | same |
| `content-export-projects.json` / `.csv` | All 6 projects | same |
| `02-elementor-theme-builder-guide.md` | How to build ONE Single-template per post type in Elementor Pro and bind every ACF field via Dynamic Tags (zero HTML widgets) | Elementor Pro → Theme Builder |

## Order of operations

1. **Plugins active:** Elementor Pro, Hello Elementor theme, ACF PRO. (Rank Math / WPForms / image-compression / caching can come later — they don't block this step.)
2. **Register the 3 post types + taxonomy** — follow `01-post-types-and-taxonomies.md` (5 minutes, all clicks, no code).
3. **Import the 3 ACF field groups** — `Custom Fields → Tools → Import Field Groups`, upload each `.json` in turn. This instantly gives every Product/Solution/Project entry its full custom-field form — nothing to build by hand.
4. **Build the Single templates in Elementor** — follow `02-elementor-theme-builder-guide.md`. You build the Hammer Mill-style layout ONCE per post type; every entry reuses it automatically via Dynamic Tags.
5. **Create the entries** — for each row in the `content-export-*.csv`, create a new Product/Solution/Project post and paste in the corresponding fields. The CSV/JSON already has every sentence, spec-table row, and feature list extracted from the current site — no rewriting needed, only copy-paste.
6. **Re-upload images** — the `hero_image` / `gallery` columns point at the existing file under `assets/img/...` on the current static site; grab those same files and upload to the WordPress Media Library, then attach them to the matching field.

## Why this structure (recap)

- 22 Products + 8 Solutions + 6 Projects = 36 near-identical page layouts today. Turning each into one CPT entry means editing a spec table or swapping a photo takes 30 seconds in wp-admin instead of hand-editing raw HTML.
- The **Product category** (Grinding, Cleaning, Mixing, Pelleting, Extrusion, Cooling & Drying, Conveying, Packing, Automation, Fabrication) becomes a taxonomy — this replaces the current mega-menu's hand-written groupings and can drive an automatic mega-menu in Elementor later.
- **Industries** (6 items) stayed as a single static Elementor page for now — it's presented as one page with anchors today, not 6 separate URLs, so a CPT isn't needed unless you want individual industry pages later.
