# Elementor build guide — binding map & template conditions

Import each template (Elementor → Templates → Import Templates), then set its
condition and attach the dynamic bindings listed here.

Attaching a binding: select the widget → click the **dynamic tag icon**
(the small database symbol beside the field) → **ACF Field** → pick the field
by the name given below. No code, no HTML widget.

---

## Template conditions

| File | Theme Builder type | Condition |
|---|---|---|
| `single-product.json` | Single | Posts → Product → All |
| `single-solution.json` | Single | Posts → Solution → All |
| `single-project.json` | Single | Posts → Project → All |
| `archive-products.json` | Archive | Archives → Product Archive |
| `archive-solutions.json` | Archive | Archives → Solution Archive |
| `archive-projects.json` | Archive | Archives → Project Archive |
| `footer.json` | Footer | Entire Site |

The **header** is not shipped as a template. Its mega-menu markup carries
`data-mega` attributes and hover-intent behaviour that the theme's JS binds to,
and Elementor's Nav Menu widget cannot emit that structure. Build it as a
Theme Builder header using the classes in `03-header-and-homepage.md`, or keep
the header in the child theme where the JS already drives it.

---

## Single Product — bindings

| Widget (in canvas) | Bind to | Type |
|---|---|---|
| `{{ACF: eyebrow}}` | `eyebrow` | ACF Text |
| `{{Post Title}}` | Post Title | built-in dynamic tag |
| `{{ACF: hero_lead}}` | `hero_lead` | ACF Text |
| `{{ACF: body_intro}}` | `body_intro` | ACF WYSIWYG |
| `{{ACF: why_heading}}` | `why_heading` | ACF Text |
| Image in `.pg-media` | `hero_image` | ACF Image |

### Repeater slots (replace the dashed build-note blocks)

Each of these is an Elementor **Loop Grid** whose query source is the ACF
repeater. Delete the note block, drop a Loop Grid in its place, and give the
wrapper the class named below.

| Build note | ACF repeater | Wrapper class | Item markup |
|---|---|---|---|
| Tag pills | `hero_tags` | `page-meta` | one element, class `tag`, text = `tag_text` |
| Feature list | `features` | `feat-list` | list item, text = `feature_text` |
| Spec table | `spec_table` | `spec-table` | two cells: `label` (th) + `value` (td) |
| Related tiles | `related_products` (relationship) | `bento` | link, class `bx`, showing thumbnail + title |

**Simpler alternative for the two short lists.** `hero_tags` and `features`
are plain string lists. Rather than a Loop Grid, you can use one **Icon List**
widget with the class `feat-list` and bind each row — fewer moving parts, same
output. The design system styles `.feat-list` whether the markup comes from a
Loop Grid or an Icon List (see section 26 of the stylesheet).

---

## Single Solution — bindings

| Widget | Bind to |
|---|---|
| `{{ACF: eyebrow}}` | `eyebrow` |
| `{{ACF: hero_lead}}` | `hero_lead` |
| `{{ACF: body_intro}}` | `body_intro` |
| `{{ACF: secondary_heading}}` | `secondary_heading` |
| `{{ACF: secondary_text}}` | `secondary_text` |
| `{{ACF: flow_intro}}` | `flow_intro` |
| Image in `.pg-media` | `hero_image` |

Repeater: `machine_flow` → Loop Grid, wrapper class `bento`, item class `bx`.
Each row has `step_label`, `machine_name` and a `linked_product` post object —
bind the item's link to the linked product's permalink.

---

## Single Project — bindings

| Widget | Bind to |
|---|---|
| `{{ACF: eyebrow}}` | `eyebrow` |
| `{{ACF: hero_lead}}` | `hero_lead` |
| `{{ACF: body_intro}}` | `body_intro` |
| `{{ACF: scope_heading}}` | `scope_heading` |

Repeaters: `scope_list` → `feat-list`; `gallery` → Elementor **Gallery**
widget bound to the ACF gallery field, each frame carrying `pg-media`.

---

## Image treatment

Every product and solution has an `image_treatment` field with three values
matching the original CSS: `render` (multiply blend, contained), `cut`
(drop shadow) and `photo` (fills the frame). Add it as a dynamic CSS class on
the image widget so the right treatment applies per entry — in the widget's
**Advanced → CSS Classes** field, set the dynamic tag to the
`image_treatment` field. The stylesheet already defines `.pg-media img.ph`
(photo) and `.pg-media img.cut`; `render` is the default with no extra class.

If you would rather keep it simple, leave the class off — every image then
renders with the default `render` treatment, which is correct for 20 of the
30 product and solution images.

---

## Responsive checks per template

Elementor's device switcher now shows the six required ranges. On each
template, check at every one:

| Range | Slot in Elementor | What to verify |
|---|---|---|
| < 576 | Mobile | `.page-grid` collapses to one column; buttons full-width; no sideways scroll |
| 576–767 | Mobile (landscape) | tag pills wrap rather than overflow |
| 768–1023 | Tablet | `.page-grid` still one column; spec table readable |
| 1024–1279 | Tablet (extra) | `.page-grid` becomes two columns at 900px+; mega menu appears |
| 1280–1535 | Laptop | container widens to 1240/1440 |
| 1536+ | Widescreen | container widens to 1440/1660; content not stranded |

The design system handles all six through its own media queries — these
checks confirm nothing in the Elementor layout fights them, they are not
places to add per-device overrides. Adding Elementor device-specific padding
on top of the stylesheet is the fastest way to break pixel accuracy.
