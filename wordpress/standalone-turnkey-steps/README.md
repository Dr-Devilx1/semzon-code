# SEMZON turnkey steps — "Five steps. One signature."

```
semzon-turnkey-steps.json   → the only file you import
preview-turnkey-steps.html  → open in a browser to inspect it locally
s-desktop.png / s-tablet.png / s-t768.png / s-mobile.png
```

No stylesheet to paste. Every colour, font, size, padding, border and radius
lives inside the template as Elementor widget and column settings.

## Install — 3 steps

1. **Elementor → Templates → Saved Templates → Import Templates** → upload
   `semzon-turnkey-steps.json`
2. Open your page → **Edit with Elementor** → grey **folder icon** →
   **My Templates** → *SEMZON — Turnkey steps (home)* → **Insert**
3. **Publish**

It inserts as two ordinary sections (the heading, then the rail + cards), so
you can move it anywhere on the page.

## Widgets used — all standard

| Part | Widgets |
|---|---|
| Heading row | Icon List (the eyebrow), Heading |
| The rail | Divider |
| Each of the 5 cards | Icon (the dot), Heading ×2, Text Editor |

No HTML widget, no addon, no `css_classes`. Click any card and edit it in the
normal panel — the text is plain text, so changing "Consult & design" to
anything else takes one click.

## The rail and the dots

The coded page draws the rail as two absolutely positioned bars: a grey track,
and an accent bar that GSAP scrubs from zero to full width as you scroll past.
Elementor has no scroll-scrub, so the rail here is a **Divider** already drawn
in full accent — which is the state the animation finishes on, and the state in
the screenshot you sent.

Each dot is an **Icon** widget positioned against its own card, 22px in and
26px up, exactly as the original's `.step .dot` is. So if you drag a card to a
different position, its dot travels with it.

## Responsive

| Width | Layout |
|---|---|
| 1025px and up | 5 across, rail and dots visible |
| 768–1024px | 3 + 2, rail and dots hidden |
| below 768px | single column, rail and dots hidden |

Type scales with it, using the sizes the coded page actually resolves to:

| Element | Desktop | Tablet | Mobile |
|---|---|---|---|
| H2 | 50px | 42px | 27px |
| Step number | 14px | 13.3px | 12.15px |
| Step title | 19.25px | 18.3px | 16.7px |
| Body text | 16.1px | 15.3px | 14px |

## What was verified

`preview-turnkey-steps.html` is generated **from the template's own settings** —
it reads `semzon-turnkey-steps.json` and turns each stored value into the CSS
Elementor would produce. It is not a hand-drawn mock, so a wrong value in the
template is wrong in the preview too.

Measured against the coded page rendered at the same widths:

- cards **244×212** at 1440 (coded page: 243×212) and **350×146** at 390
  (350×146) — the same card, to the pixel
- 28px gutter, 20px corners, 22/20 padding, 1px `#E4E6EB` border
- the five dots land on the rail at y-centre 224 with the rail at 223–225
- no horizontal scroll at 360, 390, 575, 576, 767, 768, 900, 1023, 1024, 1280,
  1440, 1536 or 1920

Three things were caught this way and fixed before delivery:

1. The card titles printed a literal `&amp;` — an Elementor heading title is
   plain text, not HTML, so the entity was never decoded. (Same trap as the
   contact card labels.)
2. At 768px the coded page's 5-across grid leaves 123px cards: one or two words
   per line, with "manufacture" and "Commission" running past the card edge.
   Hence 3 + 2 on tablet.
3. The body text was wrapped in a `<p>`, which picks up whatever bottom margin
   the active theme gives paragraphs and pushed the cards 32px taller than the
   original. It is now bare text in the Text Editor, so the only spacing is the
   heading's own 8px — on any theme.

## Two deliberate differences from the coded page

1. **The rail is drawn, not animated.** No GSAP in Elementor. If you want the
   scroll-fill back it needs a small script — say the word.
2. **3 + 2 on tablet instead of 5 across**, for the reason above.

## What this cannot catch

A conflict with your specific theme or another plugin's CSS. I have no way to
reach a live WordPress install from here. If anything looks off after
importing, send a screenshot and I will target it.
