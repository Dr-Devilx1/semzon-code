# SEMZON contact page — v2, no custom CSS

```
semzon-contact.json     → the only file you import
c2-desktop.png          → what it looks like
c2-mobile.png
preview-contact.html    → open in a browser to inspect it locally
```

**There is no stylesheet to paste this time.** Every colour, font, size,
padding, border, radius and gradient is stored inside the template as Elementor
widget settings. Import it and it looks right.

## Why it was broken before

The previous version put the layout in the template and the entire look in a
separate CSS file. On your site that CSS never loaded — the giveaway in your
screenshot was the dashed "FORM SLOT" note showing on the live page, which the
stylesheet is what hides. With the CSS missing, every element fell back to your
theme's defaults, which is why the text was maroon and the pills and cards had
no styling at all.

Making 100% of the appearance depend on a manual paste step was the wrong
design. That is fixed: the styling now travels inside the template.

## Install — 3 steps

1. **Elementor → Templates → Saved Templates → Import Templates** → upload
   `semzon-contact.json`
2. Open your Contact page → **Edit with Elementor** → grey **folder icon** →
   **My Templates** → *SEMZON — Contact* → **Insert**
3. Delete the empty starter section Elementor added → **Publish**

That's it. Nothing to paste, nothing to configure.

## Widgets used — all standard

| Section | Widgets |
|---|---|
| Hero | Heading ×3, Text Editor, Icon List |
| Form + details | **Form** (Elementor Pro), Button ×2, Text Editor, Divider |
| Map | Heading ×2, **Google Maps** |
| CTA band | Heading ×2, Text Editor, Button |

Nothing custom, no HTML widget, no third-party addon. Every one of these is a
widget you already have, so you can click any element and restyle it in the
normal Elementor panel — the settings are all sitting there, not locked away in
a stylesheet.

## The form

It is a real **Elementor Pro Form**, already built with the original's fields:

Your name (required, half width) · Phone / WhatsApp (half width) ·
Interested in (all 8 options) · Target capacity · Your message

Submit button reads **Send enquiry**, and the form is pre-set to email
`semzoneng@gmail.com`. Open the widget → **Actions After Submit** to confirm
the email settings on your install.

Under it sit the **Send via WhatsApp** and **Send by email** buttons, which
work with no plugin at all — that is how the original page behaved. With the
Pro form on top, enquiries now also land in your inbox, which the coded site
could not do.

If the form area is empty after importing, Elementor **Pro** is not active —
that widget is Pro-only. Everything else on the page still works.

## The map — now a single Lahore pin, as you asked

It is Elementor's own **Google Maps** widget pointed at:

> 2.5 KM Manga Raiwind Road, Manga Mandi, Lahore, Pakistan

Zoom 14, 460px tall on desktop and 320px on phones, 24px rounded corners, with
a slight desaturation so it sits with the brand palette rather than shouting.

**No API key needed** — Elementor's Maps widget uses Google's keyless embed.
Nothing to configure, nothing to bill. To change the address, click the widget
and type a new one.

## Responsive

Every heading carries desktop, tablet and mobile sizes, so text scales properly
instead of staying huge on phones:

| Element | Desktop | Tablet | Mobile |
|---|---|---|---|
| H1 | 58px | 42px | 32px |
| Section H2 | 34–38px | 28–30px | 24–25px |
| Lead paragraph | 18px | 17px | 16px |

The two columns stack below Elementor's tablet breakpoint. Section padding also
has its own mobile values, so the page tightens up rather than keeping desktop
spacing.

## What was verified

`preview-contact.html` is generated **from the template's own settings** — it
reads `semzon-contact.json` and turns each stored value into CSS. It is not a
hand-drawn mock, so if a size or colour in the template were wrong, the preview
would be wrong in the same way. Screenshots come from rendering it in a real
browser at 1440px and 390px.

Checked: no horizontal scroll at either width; H1 resolves to 58px on desktop
and 32px on mobile from the stored responsive values; columns stack on mobile.

Three bugs were caught this way and fixed before delivery:

1. The contact-card labels printed a literal `&amp;` — an Elementor heading
   title is plain text, not HTML, so the entity was never decoded.
2. The eyebrow text was accent red; in the original's final visual pass it is
   brand purple.
3. The H1 ran to a single 1200px line instead of wrapping to the original's
   tighter measure, so the hero section now uses a 980px content width.

**What this cannot catch:** a conflict with your specific theme. Because the
styling is now inline on each widget it is far harder for a theme to override
than the previous stylesheet, but if anything still looks off, send a
screenshot and I will target it.
