# Step 1 — Register post types & taxonomy (ACF PRO, no code)

ACF PRO 6.1+ has built-in screens for this: **Custom Fields → Post Types**
and **Custom Fields → Taxonomies**. Nothing here needs Custom Post Type UI
or any code — just type these values into the forms.

## Post Type 1 — Product

`Custom Fields → Post Types → Add New`

| Field | Value |
|---|---|
| Plural Label | Products |
| Singular Label | Product |
| Post Type Key | `product` |
| Public | Yes |
| Show in Menu | Yes |
| Has Archive | Yes — set archive slug to `products` |
| Rewrite → Permalink | `products/%product_category%/%postname%/` *(matches current URL depth — see note below)* |
| Supports | Title, Editor (off if you prefer ACF-only), Featured Image, Excerpt, Revisions |
| Show in REST API | Yes (needed for Elementor dynamic content + Gutenberg) |

> Note on the nested permalink: the live site nests products two levels deep
> (`products/grinding/hammer-mill/`). WordPress can't natively rewrite a
> custom taxonomy into the permalink without a small rewrite rule — if you
> want to preserve the exact old URLs for SEO, ask me to set up 301
> redirects from the old paths to the new `products/hammer-mill/` paths
> instead of fighting the permalink structure. Simpler and just as good for
> SEO (redirects pass ranking signal).

## Post Type 2 — Solution

| Field | Value |
|---|---|
| Plural Label | Solutions |
| Singular Label | Solution |
| Post Type Key | `solution` |
| Has Archive | Yes — slug `solutions` |
| Rewrite | `solutions/%postname%/` |
| Supports | Title, Featured Image, Excerpt, Revisions |
| Show in REST API | Yes |

## Post Type 3 — Project

| Field | Value |
|---|---|
| Plural Label | Projects |
| Singular Label | Project |
| Post Type Key | `project` |
| Has Archive | Yes — slug `projects` |
| Rewrite | `projects/%postname%/` |
| Supports | Title, Featured Image, Excerpt, Revisions |
| Show in REST API | Yes |

## Taxonomy — Product Category

`Custom Fields → Taxonomies → Add New`

| Field | Value |
|---|---|
| Plural Label | Product Categories |
| Singular Label | Product Category |
| Taxonomy Key | `product_category` |
| Hierarchical | Yes (behaves like categories, not tags) |
| Attach to Post Types | Product |

Create these 11 terms once the taxonomy exists (matches the current mega-menu groupings):

```
Grinding, Cleaning, Screening, Mixing, Pelleting, Extrusion,
Cooling & Drying, Conveying, Packing, Automation, Fabrication
```

That's it for this step — 3 post types + 1 taxonomy, zero code. Next: import
the ACF field groups (`../README.md` step 3).
