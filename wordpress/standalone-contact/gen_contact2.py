"""SEMZON contact page — styling baked into the Elementor JSON, no custom CSS.

The previous build put the layout in the template and the entire look in a
stylesheet the client had to paste separately. On the live site that stylesheet
never loaded, so the page rendered as unstyled theme defaults. Depending on a
manual paste step for 100% of the appearance was the wrong design.

Everything is now encoded as Elementor widget/section/column settings —
typography with responsive sizes, colours, padding, borders, radii, gradients.
Import it and it looks right, with nothing to paste and nothing to override.

Native widgets only: heading, text-editor, icon-list, button, google_maps,
divider, and Elementor Pro's form. The map is Elementor's own Google Maps
widget pointed at the Lahore works — one pin, no API key (it uses Google's
keyless embed endpoint).
"""

import json, os, hashlib, itertools

OUT = os.path.dirname(os.path.abspath(__file__))
_c = itertools.count(1)
def eid(): return hashlib.md5(("c2" + str(next(_c))).encode()).hexdigest()[:7]

# --- palette, straight from the original design tokens ----------------------
INK      = "#0D0333"
BRAND800 = "#1C0863"
BRAND600 = "#3A22A8"
BRAND400 = "#8A79CC"
BRAND050 = "#F5F4FB"
ACCENT   = "#840C0C"
TEXT900  = "#14152B"
TEXT700  = "#2B2C42"
TEXT500  = "#4C4D60"
INVSOFT  = "#C7C2E8"
LINE     = "#E4E6EB"
WHITE    = "#FFFFFF"

DISPLAY = "Bricolage Grotesque"
BODY    = "Inter"
MONO    = "JetBrains Mono"

def px(n):  return {"unit": "px", "size": n, "sizes": []}
def em(n):  return {"unit": "em", "size": n, "sizes": []}
def box(t, r, b, l, unit="px"):
    return {"unit": unit, "top": str(t), "right": str(r), "bottom": str(b),
            "left": str(l), "isLinked": False}
def radius(n):
    return {"unit": "px", "top": str(n), "right": str(n), "bottom": str(n),
            "left": str(n), "isLinked": True}

def typo(prefix, family, size, weight="400", lh=None, ls=None,
         transform=None, tablet=None, mobile=None):
    """Elementor typography group control, with responsive sizes."""
    d = {
        f"{prefix}_typography": "custom",
        f"{prefix}_font_family": family,
        f"{prefix}_font_size": px(size),
        f"{prefix}_font_weight": weight,
    }
    if tablet: d[f"{prefix}_font_size_tablet"] = px(tablet)
    if mobile: d[f"{prefix}_font_size_mobile"] = px(mobile)
    if lh is not None: d[f"{prefix}_line_height"] = em(lh)
    if ls is not None: d[f"{prefix}_letter_spacing"] = px(ls)
    if transform: d[f"{prefix}_text_transform"] = transform
    return d

# --- element builders -------------------------------------------------------
def section(cols, settings=None):
    s = {"layout": "boxed", "content_width": {"unit": "px", "size": 1200},
         "gap": "no"}
    if settings: s.update(settings)
    return {"id": eid(), "elType": "section", "settings": s,
            "elements": cols, "isInner": False}

def column(widgets, size, settings=None):
    s = {"_column_size": size, "_inline_size": size}
    if settings: s.update(settings)
    return {"id": eid(), "elType": "column", "settings": s,
            "elements": widgets, "isInner": False}

def widget(wtype, settings):
    return {"id": eid(), "elType": "widget", "settings": settings,
            "elements": [], "widgetType": wtype}

def heading(title, tag, color, typ, align=None, margin=None):
    s = {"title": title, "header_size": tag, "title_color": color}
    s.update(typ)
    if align: s["align"] = align
    if margin: s["_margin"] = margin
    return widget("heading", s)

def rich(html, color, typ, margin=None):
    s = {"editor": html, "text_color": color}
    s.update(typ)
    if margin: s["_margin"] = margin
    return widget("text-editor", s)

def gradient_button(text, url, external=False):
    """The original's .btn--primary: gradient pill with a glow."""
    return widget("button", {
        "text": text,
        "link": {"url": url, "is_external": "on" if external else "", "nofollow": ""},
        "align": "left",
        "background_color": BRAND600,
        "button_background_hover_color": BRAND800,
        "button_text_color": WHITE,
        "hover_color": WHITE,
        "border_radius": radius(999),
        "text_padding": box(18, 30, 18, 30),
        "button_box_shadow_box_shadow_type": "yes",
        "button_box_shadow_box_shadow": {
            "horizontal": 0, "vertical": 10, "blur": 30, "spread": -10,
            "color": "rgba(58,34,168,0.6)"},
        **typo("typography", BODY, 15, "600"),
    })

def outline_button(text, url, external=False):
    """The original's .btn--secondary: white pill with a brand outline."""
    return widget("button", {
        "text": text,
        "link": {"url": url, "is_external": "on" if external else "", "nofollow": ""},
        "align": "left",
        "background_color": "#FFFFFF00",
        "button_background_hover_color": BRAND050,
        "button_text_color": BRAND800,
        "hover_color": BRAND800,
        "border_border": "solid",
        "border_width": box(1.5, 1.5, 1.5, 1.5),
        "border_color": "#D5CFEE",
        "button_hover_border_color": BRAND600,
        "border_radius": radius(999),
        "text_padding": box(18, 30, 18, 30),
        **typo("typography", BODY, 15, "600"),
    })

def label_value(label, value):
    """One row of the contact card: mono label above a bold value."""
    return [
        heading(label, "div", TEXT500,
                typo("typography", MONO, 12, "500", ls=1.4, transform="uppercase"),
                margin=box(0, 0, 4, 0)),
        rich(value, TEXT700, typo("typography", BODY, 16, "400", lh=1.6),
             margin=box(0, 0, 20, 0)),
    ]

WA = ("https://wa.me/923238845411?text="
      "Salaam%2C%20I%27d%20like%20to%20discuss%20a%20plant.")

# ============================================================== 1. HERO
hero = section([column([
    heading("Home  ›  Contact", "div", TEXT500,
            typo("typography", MONO, 12, "400", ls=1.4, transform="uppercase"),
            margin=box(0, 0, 22, 0)),
    heading("Start your project", "div", BRAND600,
            typo("typography", MONO, 13, "500", ls=2.1, transform="uppercase"),
            margin=box(0, 0, 14, 0)),
    heading("Talk to an engineer, not a form.", "h1", TEXT900,
            typo("typography", DISPLAY, 58, "800", lh=1.06, ls=-1.3,
                 tablet=42, mobile=32),
            margin=box(0, 0, 18, 0)),
    rich("<p>Send drawings, capacity targets, or just an idea — a SEMZON "
         "engineer replies within 24 hours, usually sooner. Prefer instant? "
         "WhatsApp goes straight to the works.</p>",
         TEXT700, typo("typography", BODY, 18, "400", lh=1.7, tablet=17, mobile=16),
         margin=box(0, 0, 20, 0)),
    widget("icon-list", {
        "view": "inline",
        "icon_list": [
            {"_id": eid(), "text": "Reply under 24 hours",
             "selected_icon": {"value": "fas fa-circle", "library": "fa-solid"}},
            {"_id": eid(), "text": "Lahore, Pakistan",
             "selected_icon": {"value": "fas fa-circle", "library": "fa-solid"}},
        ],
        "icon_color": ACCENT,
        "icon_size": px(6),
        "text_color": TEXT700,
        "space_between": px(22),
        **typo("icon_typography", MONO, 12, "500", ls=1.2, transform="uppercase"),
    }),
], 100)], {"padding": box(64, 0, 40, 0), "padding_mobile": box(40, 0, 28, 0),
            "content_width": {"unit": "px", "size": 980}})

# ================================================= 2. FORM + CONTACT CARD
form_widget = widget("form", {
    "form_name": "SEMZON enquiry",
    "form_fields": [
        {"_id": "fname", "field_type": "text", "field_label": "Your name",
         "required": "true", "width": "50"},
        {"_id": "fphone", "field_type": "tel", "field_label": "Phone / WhatsApp",
         "width": "50"},
        {"_id": "fint", "field_type": "select", "field_label": "Interested in",
         "field_options": "Poultry / livestock feed line\nAqua & pet feed line\n"
                          "Biomass pellet line\nFertilizer plant (SSP / BOP / compost)\n"
                          "Single machine\nAutomation / retrofit\nSpares\nOther",
         "width": "100"},
        {"_id": "fcap", "field_type": "text", "field_label": "Target capacity (if known)",
         "placeholder": "e.g. 20 t/h", "width": "100"},
        {"_id": "fmsg", "field_type": "textarea", "field_label": "Your message",
         "placeholder": "Tell us about the site, raw material, timeline…",
         "rows": 5, "width": "100"},
    ],
    "button_text": "Send enquiry",
    "button_size": "md",
    "email_to": "semzoneng@gmail.com",
    "email_subject": "New enquiry from semzoneng.com",
    "label_typography_typography": "custom",
    "label_typography_font_family": MONO,
    "label_typography_font_size": px(12),
    "label_typography_font_weight": "500",
    "label_typography_letter_spacing": px(1.4),
    "label_typography_text_transform": "uppercase",
    "label_color": TEXT500,
    "field_typography_typography": "custom",
    "field_typography_font_family": BODY,
    "field_typography_font_size": px(16),
    "field_background_color": WHITE,
    "field_text_color": TEXT900,
    "field_border_color": LINE,
    "field_border_width": box(1.5, 1.5, 1.5, 1.5),
    "field_border_radius": radius(14),
    "row_gap": px(16),
    "button_background_color": BRAND600,
    "button_background_hover_color": BRAND800,
    "button_text_color": WHITE,
    "button_border_radius": radius(999),
    "button_text_padding": box(18, 32, 18, 32),
    "button_typography_typography": "custom",
    "button_typography_font_family": BODY,
    "button_typography_font_size": px(15),
    "button_typography_font_weight": "600",
})

form_col = column([
    form_widget,
    widget("divider", {"gap": px(18), "weight": px(0)}),
    gradient_button("Send via WhatsApp", WA, external=True),
    outline_button("Send by email",
                   "mailto:semzoneng@gmail.com?subject=RFQ%20from%20semzoneng.com"),
    rich("<p>Prefer to talk? WhatsApp goes straight to the works, and the "
         "email button opens your own mail app.</p>",
         TEXT500, typo("typography", BODY, 14, "400", lh=1.6),
         margin=box(14, 0, 0, 0)),
], 55)

card_widgets = []
for lbl, val in [
    ("Phone & WhatsApp", "<p><strong>+92 323 8845411</strong></p>"),
    ("Email", "<p><strong>semzoneng@gmail.com</strong></p>"),
    ("Works & office", "<p>2.5 KM Manga Raiwind Road,<br>Manga Mandi, Lahore, Pakistan</p>"),
    ("Factory visits", "<p>Any working day — message ahead and we&rsquo;ll schedule a workshop walkthrough.</p>"),
]:
    card_widgets += label_value(lbl, val)

card_col = column(card_widgets, 45, {
    "background_background": "classic",
    "background_color": BRAND050,
    "border_border": "solid",
    "border_width": box(1, 1, 1, 1),
    "border_color": LINE,
    "border_radius": radius(24),
    "padding": box(32, 30, 14, 30),
    "padding_mobile": box(26, 22, 8, 22),
})

form_section = section([form_col, card_col],
                       {"padding": box(20, 0, 70, 0), "gap": "extended"})

# ============================================================== 3. MAP
map_section = section([column([
    heading("Find us", "div", BRAND600,
            typo("typography", MONO, 13, "500", ls=2.1, transform="uppercase"),
            margin=box(0, 0, 12, 0)),
    heading("Lahore works, global reach.", "h2", TEXT900,
            typo("typography", DISPLAY, 34, "700", lh=1.12, ls=-.6,
                 tablet=28, mobile=24),
            margin=box(0, 0, 28, 0)),
    widget("google_maps", {
        "address": "2.5 KM Manga Raiwind Road, Manga Mandi, Lahore, Pakistan",
        "zoom": {"unit": "px", "size": 14},
        "height": {"unit": "px", "size": 460},
        "height_mobile": {"unit": "px", "size": 320},
        "css_filters_css_filter": "custom",
        "css_filters_saturate": {"unit": "%", "size": 55},
        "css_filters_contrast": {"unit": "%", "size": 105},
        "_border_radius": radius(24),
    }),
], 100)], {"padding": box(10, 0, 70, 0),
           "background_background": "classic", "background_color": WHITE})

# ============================================================== 4. CTA BAND
cta = section([column([
    heading("Start a conversation", "div", "#E08A8A",
            typo("typography", MONO, 13, "500", ls=2.1, transform="uppercase"),
            align="center", margin=box(0, 0, 14, 0)),
    heading("Discuss your plant with our engineers.", "h2", WHITE,
            typo("typography", DISPLAY, 38, "700", lh=1.14, ls=-.7,
                 tablet=30, mobile=25),
            align="center", margin=box(0, 0, 16, 0)),
    rich("<p style=\"text-align:center\">Send drawings, capacity targets, or "
         "just an idea. A SEMZON engineer replies within 24 hours — usually "
         "sooner.</p>",
         INVSOFT, typo("typography", BODY, 17, "400", lh=1.7, mobile=16),
         margin=box(0, 0, 28, 0)),
    widget("button", {
        "text": "Start your project",
        "link": {"url": "#", "is_external": "", "nofollow": ""},
        "align": "center",
        "background_color": WHITE,
        "button_background_hover_color": "#EBE8F7",
        "button_text_color": BRAND800,
        "hover_color": BRAND800,
        "border_radius": radius(999),
        "text_padding": box(18, 32, 18, 32),
        **typo("typography", BODY, 15, "600"),
    }),
], 100, {"padding": box(0, 40, 0, 40), "padding_mobile": box(0, 6, 0, 6)})],
{
    "padding": box(72, 0, 76, 0),
    "padding_mobile": box(52, 0, 56, 0),
    "margin": box(0, 0, 40, 0),
    "background_background": "gradient",
    "background_color": "#160A4A",
    "background_color_b": INK,
    "background_gradient_angle": {"unit": "deg", "size": 165},
    "border_radius": radius(32),
    "shape_divider_top": "",
})

template = {
    "version": "0.4",
    "title": "SEMZON — Contact",
    "type": "section",
    "content": [hero, form_section, map_section, cta],
    "page_settings": [],
}

# ------------------------------------------------------------------- audit
ALLOWED = {"heading", "text-editor", "icon-list", "button", "google_maps",
           "divider", "form"}
problems, ids = [], set()
styled = {"typography_typography", "title_color", "text_color",
          "background_color", "button_text_color", "border_radius"}

def audit(n, path=""):
    et = n.get("elType")
    if et == "section":
        tot = sum(c["settings"]["_column_size"] for c in n["elements"])
        if tot != 100: problems.append(f"{path}: columns total {tot}")
        for i, c in enumerate(n["elements"]): audit(c, f"{path}>c{i}")
    elif et == "column":
        for i, w in enumerate(n["elements"]): audit(w, f"{path}>w{i}")
    elif et == "widget":
        wt = n["widgetType"]
        if wt not in ALLOWED: problems.append(f"{path}: widget {wt} not allowed")
        if wt in {"heading", "text-editor"} and not (set(n["settings"]) & styled):
            problems.append(f"{path}: {wt} carries no styling")
        if "css_classes" in n["settings"]:
            problems.append(f"{path}: still relies on a CSS class")
    else:
        problems.append(f"{path}: bad elType {et}")
    if n.get("id") in ids: problems.append(f"duplicate id {n['id']}")
    ids.add(n.get("id"))

for r in template["content"]: audit(r, "root")

with open(os.path.join(OUT, "semzon-contact.json"), "w", encoding="utf-8") as f:
    json.dump(template, f, indent=2, ensure_ascii=False)

if problems:
    print("PROBLEMS:", *problems, sep="\n  "); raise SystemExit(1)

widgets = {}
def count(n):
    if n["elType"] == "widget": widgets[n["widgetType"]] = widgets.get(n["widgetType"], 0) + 1
    for c in n.get("elements", []): count(c)
for r in template["content"]: count(r)

print(f"semzon-contact.json — {len(template['content'])} sections, {len(ids)} ids")
print("widgets used:", ", ".join(f"{k}x{v}" for k, v in sorted(widgets.items())))
print("no css_classes anywhere: every element carries its own styling")
