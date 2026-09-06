# SEMZON contact page — install in 10 minutes

```
semzon-contact.json   → import into Elementor
semzon-contact.css    → paste into Additional CSS   (required)
semzon-map.png        → the multi-location map, upload as an image
semzon-map.svg        → same map as vector (see note below)
```

**Both the JSON and the CSS are required.** The JSON is the layout; the CSS is
the entire look. Importing only the JSON gives you an unstyled page.

---

## Step 1 — CSS first

**Appearance → Customize → Additional CSS** → paste all of
`semzon-contact.css` → **Publish**.

If you already pasted the header CSS, just append this below it. Both files
declare the same `:root` tokens; a duplicate is harmless.

## Step 2 — import the layout

**Elementor → Templates → Saved Templates → Import Templates** → upload
`semzon-contact.json`.

Then create/edit your Contact page → **Edit with Elementor** → grey **folder
icon** → **My Templates** → *SEMZON — Contact* → **Insert**.

## Step 3 — the map

Upload **`semzon-map.png`** to the Media Library, then select the Image widget
in the map section and choose it.

Use the PNG, not the SVG, unless you have SVG uploads enabled — WordPress
blocks `.svg` by default for security. If you want the sharper vector version,
install **Safe SVG** first, then upload `semzon-map.svg` instead.

## Step 4 — the form

The template has a dashed **FORM SLOT**. Delete it and drop in your form widget
— **Elementor Pro Form**, **WPForms** or **Contact Form 7**. The CSS styles all
three identically, so it does not matter which you pick.

Fields, in the original's order:

| Field | Type | Notes |
|---|---|---|
| Your name | Text | required |
| Phone / WhatsApp | Tel | |
| Interested in | Select | Poultry / livestock feed line · Aqua & pet feed line · Biomass pellet line · Fertilizer plant (SSP / BOP / compost) · Single machine · Automation / retrofit · Spares · Other |
| Target capacity (if known) | Text | placeholder `e.g. 20 t/h` |
| Your message | Textarea | placeholder `Tell us about the site, raw material, timeline…` |

Set the submit button label to **Send enquiry**, and in the form's Actions
After Submit set **Email** to `semzoneng@gmail.com`.

The two buttons below the form (**Send via WhatsApp**, **Send by email**) are
plain Button widgets and already work — no plugin needed. That is how the
original page behaved: it never stored anything, it handed off to WhatsApp or
the mail app. Adding a real form on top means enquiries also land in your
inbox, which the coded site could not do.

---

## About the map — read this

The original draws seven pins with the Google Maps JavaScript API, custom
styling and info windows. **Elementor's Google Maps widget only supports one
location**, and a styled multi-pin map genuinely needs JavaScript, so it cannot
be reproduced as an Elementor widget.

So the map here is the same information drawn as a graphic: all seven real
locations, each placed by an equirectangular projection of its true latitude
and longitude, in the original's visual language — blueprint grid, dashed
delivery arcs radiating from the Lahore hub, ringed HQ marker, mono labels.

| Location | Role |
|---|---|
| Lahore | HQ + works (ringed hub) |
| Gujranwala | Roshan Feeds — pellet line |
| Kasur | Subhan & Al-Itifaq — feed lines |
| Karachi | Auriga — BOP plant |
| Quetta | WMC — compost |
| Kabul | Albadar Feed — 20 t/h turnkey |
| Dubai | GCC export & support (hollow marker) |

What you gain over the coded version: no API key, no Google billing, no
JavaScript, nothing to break, and it stays sharp at any size. What you lose:
it does not pan or zoom, and clicking a pin does not open an info window.

**If you want the real interactive Google map instead**, it needs the Maps API
key and a small JS snippet — tell me and I will supply it. Note the old key
(`AIzaSyD3gn…`) is still public in the GitHub repo and must be rotated first.

On phones the map keeps a 660px minimum width and scrolls inside its own card,
because scaling it to 390px would shrink the labels to about 5px — present but
unreadable. The page itself never scrolls sideways.

---

## What was verified

`preview-contact.html` renders this exact CSS against Elementor's real DOM.
Screenshots `contact-desktop.png` and `contact-mobile.png` come from it.

Checked at 1440px and 390px: no page-level horizontal scroll; two-column grid
above 900px and single column below; form inputs 14px radius matching the
original; map holds its 900:520 ratio.

Three bugs were caught this way and fixed before delivery:

1. Inputs and buttons overflowed their column, because they were sized
   `width:100%` **plus** padding without `box-sizing: border-box`. Themes vary
   on whether they set that for form controls, so it is now set explicitly.
2. The WhatsApp and email buttons stacked vertically instead of sitting side
   by side — Elementor gives every widget `width:100%`.
3. Map labels were unreadable on phones (see above).

**What this cannot catch:** a conflict with your specific theme or another
plugin's CSS, since I have no way to reach a live WordPress install. If
something looks off, send a screenshot and I will target it.
