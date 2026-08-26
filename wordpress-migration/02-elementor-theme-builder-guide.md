# Step 4 — Build the Single templates in Elementor Pro (no HTML widget)

You build each layout ONCE. Every Product/Solution/Project entry then reuses
it automatically. This is what replaces "one hand-coded HTML page per
machine."

General steps (repeat per post type):

1. `Templates → Theme Builder → Single → Add New` → choose **Single Post
   Product/Solution/Project** as the condition (Elementor Pro auto-detects
   your CPTs once they're registered from Step 1).
2. Design the layout using normal Elementor widgets (Heading, Text Editor,
   Image, Icon List, Table if you have one, or a simple two-column Repeater
   loop via **Loop Grid** for the "related machines" bento).
3. On any text/image/heading widget, click the **dynamic content icon**
   (the little database/db symbol next to the field) instead of typing text
   → choose **ACF Field** → pick the field by name. This is the native,
   code-free way Elementor pulls in per-entry content.

## Field → widget mapping (Product template)

| Elementor widget | Dynamic tag source |
|---|---|
| Breadcrumb (Elementor Pro widget) | automatic, no binding needed |
| Heading (H1) | **Post Title** (dynamic tag, built-in — not an ACF field) |
| Text (lead) | ACF Field → `hero_lead` |
| Icon list / tag pills | ACF Field → `hero_tags` (repeater) — use a **Loop Grid** or the repeater's "Text" sub-fields bound via ACF's repeater dynamic tag |
| Text Editor | ACF Field → `body_intro` |
| Heading (h3) | ACF Field → `why_heading` |
| Icon List | ACF Field → `features` (repeater, one line per feature) |
| Image | ACF Field → `hero_image` |
| Table / repeater rows | ACF Field → `spec_table` (repeater with `label`, `value` sub-fields) |
| Related items row | ACF Field → `related_products` (relationship) — best done as an Elementor **Loop Grid** pointed at the relationship field |

## Field → widget mapping (Solution template)

Same pattern using: `hero_tags`, `hero_lead`, `body_intro`,
`secondary_heading`, `secondary_text`, `hero_image`, `flow_intro`, and the
`machine_flow` repeater (6 fixed rows — good candidate for 6 manually-bound
mini-columns, or a Loop Grid) for the "machines behind the line" bento.

## Field → widget mapping (Project template)

`eyebrow`, `hero_tags`, `hero_lead`, `body_intro`, `scope_heading`,
`scope_list` (repeater → Icon List), `gallery` (gallery field → Image
Carousel or Gallery widget).

## Nothing here uses the HTML widget

Every value above is either a built-in Elementor dynamic tag (Post Title,
Featured Image, Post URL, etc.) or an **ACF Field** dynamic tag — both are
native Elementor Pro features, not code, not a custom plugin. The repeaters
(`spec_table`, `features`, `machine_flow`, `scope_list`) are the only fields
that need Elementor's **Loop Grid**/**Loop Carousel** widgets (also native,
part of Elementor Pro's Theme Builder) instead of a plain text binding —
those widgets are built exactly for "repeat this mini-layout once per row
of a repeater/relationship field."

## Archive templates

Do the same thing once more for `Archive → Product/Solution/Project` —
this replaces the current `products/index.html`, `solutions/index.html`,
`projects/index.html` grid pages with one auto-updating loop each.
