"""SEMZON machinery bento (home section 02) as an Elementor template.

Same rules as the contact build: every colour, size, spacing and radius is
stored as an Elementor setting inside the JSON, so importing it is the only
step. No stylesheet to paste, no css_classes, no HTML widget.

Every number below was measured off the coded page rendered in a real browser
at 1440 / 900 / 390, not estimated from the source - see measure.js. That is
why the sizes look unround (54px, 29.75px, 11.9px): the original scales its
type off a fluid root font-size, and these are the values it actually resolves
to at each of Elementor's three breakpoints.

Layout note. The coded page draws this with CSS Grid, where the big pellet-mill
tile spans two columns AND two rows. Elementor's classic sections cannot span
rows, so the same picture is built as:

    section 2 : [ column 50% = the XL tile ][ column 50% = two inner sections,
                                              two cards each -> the 2x2 block ]
    section 3 : [ 25% ][ 25% ][ 50% full-bleed PLC tile ]

which lands on the identical arrangement. The card surface (white, 1px line,
20px radius) sits on the COLUMN, and the gutter comes from the column's own
margin - Elementor paints a column background on .elementor-element-populated,
which is inside that margin, so the cards keep clean 28px gaps instead of
touching. That is also why the cards are columns and not image-box widgets: an
image-box scales its image by width alone, and these eight product shots run
from 294x368 to 992x787, so a width-driven box would give every tile a
different height. The image widget takes a fixed height plus object-fit, which
is what the original's figure does.
"""

import json, os, hashlib, itertools

OUT = os.path.dirname(os.path.abspath(__file__))
_c = itertools.count(1)
def eid(): return hashlib.md5(("mz" + str(next(_c))).encode()).hexdigest()[:7]

# --- design tokens, straight from assets/css/main.css ------------------------
BRAND800 = "#1C0863"
BRAND600 = "#3A22A8"
BRAND200 = "#D5CFEE"
ACCENT   = "#840C0C"
TEXT900  = "#14152B"
TEXT500  = "#4C4D60"
MIST     = "#F6F7F9"
LINE     = "#E4E6EB"
WHITE    = "#FFFFFF"
WATERMARK = "#F3F1FB"   # the coded page strokes #EBE8F7; a fill this light
                        # reads at the same weight as that outline

DISPLAY = "Bricolage Grotesque"
BODY    = "Inter"
MONO    = "JetBrains Mono"

# Remote source for the product shots. Elementor's importer downloads media it
# finds in template settings into the library; if a fetch fails the URL still
# renders, and the same eight files ship in the zip as a manual fallback.
IMG  = "https://www.semzoneng.com/assets/img/"
SITE = "https://www.semzoneng.com/"

def px(n):  return {"unit": "px", "size": n, "sizes": []}
def pc(n):  return {"unit": "%",  "size": n, "sizes": []}
def em(n):  return {"unit": "em", "size": n, "sizes": []}
def box(t, r, b, l, unit="px"):
    return {"unit": unit, "top": str(t), "right": str(r), "bottom": str(b),
            "left": str(l), "isLinked": False}
def radius(n):
    return {"unit": "px", "top": str(n), "right": str(n), "bottom": str(n),
            "left": str(n), "isLinked": True}
def media(fname):
    return {"url": IMG + fname, "id": "", "size": ""}

def typo(prefix, family, size, weight="400", lh=None, ls=None,
         transform=None, tablet=None, mobile=None, ls_t=None, ls_m=None):
    d = {f"{prefix}_typography": "custom",
         f"{prefix}_font_family": family,
         f"{prefix}_font_size": px(size),
         f"{prefix}_font_weight": weight}
    if tablet: d[f"{prefix}_font_size_tablet"] = px(tablet)
    if mobile: d[f"{prefix}_font_size_mobile"] = px(mobile)
    if lh is not None: d[f"{prefix}_line_height"] = em(lh)
    if ls is not None: d[f"{prefix}_letter_spacing"] = px(ls)
    if ls_t is not None: d[f"{prefix}_letter_spacing_tablet"] = px(ls_t)
    if ls_m is not None: d[f"{prefix}_letter_spacing_mobile"] = px(ls_m)
    if transform: d[f"{prefix}_text_transform"] = transform
    return d

# --- structural builders -----------------------------------------------------
def section(cols, settings=None, inner=False):
    s = {"gap": "no", "structure": str(len(cols)) + "0"}
    if not inner:
        s.update({"layout": "boxed",
                  "content_width": {"unit": "px", "size": 1360}})
    if settings: s.update(settings)
    return {"id": eid(), "elType": "section", "settings": s,
            "elements": cols, "isInner": inner}

def column(widgets, size, settings=None):
    s = {"_column_size": size, "_inline_size": size}
    if settings: s.update(settings)
    return {"id": eid(), "elType": "column", "settings": s,
            "elements": widgets, "isInner": False}

def widget(wtype, settings):
    return {"id": eid(), "elType": "widget", "settings": settings,
            "elements": [], "widgetType": wtype}

def heading(title, tag, color, typ, align=None, margin=None, link=None,
            extra=None):
    s = {"title": title, "header_size": tag, "title_color": color}
    s.update(typ)
    if align: s["align"] = align
    if margin: s["_margin"] = margin
    if link: s["link"] = {"url": link, "is_external": "", "nofollow": ""}
    if extra: s.update(extra)
    return widget("heading", s)

# --- the card surface --------------------------------------------------------
# White tile, hairline border, 20px radius, 16px inside. The 14px side margin on
# each card is what produces the 28px gutter between two neighbours - the same
# gutter the coded page resolves to at 1440.
GUTTER = {"margin":        box(0, 14, 28, 14),
          "margin_tablet": box(0, 10, 20, 10),
          "margin_mobile": box(0,  6, 12,  6)}

def card_column(widgets, size, size_tablet=None, pad=16, extra=None):
    s = {
        "background_background": "classic",
        "background_color": WHITE,
        "border_border": "solid",
        "border_width": box(1, 1, 1, 1),
        "border_color": LINE,
        "border_radius": radius(20),
        "padding": box(pad, pad, pad, pad),
        "padding_mobile": box(14, 14, 14, 14),
        "space_between_widgets": 0,
        **GUTTER,
    }
    if size_tablet: s["_inline_size_tablet"] = size_tablet
    if extra: s.update(extra)
    return column(widgets, size, s)

def product_image(fname, url, h, h_t, h_m, panel=True, margin_bottom=12):
    """The coded page's .bx figure: a mist panel at 4:3 with 12px corners, the
    product shot centred inside it at 80% width so every tile lines up."""
    s = {
        "image": media(fname),
        "image_size": "full",
        "align": "center",
        "width": pc(80),
        "height": px(h),
        "height_tablet": px(h_t),
        "height_mobile": px(h_m),
        "object-fit": "contain",
        "link_to": "custom",
        "link": {"url": url, "is_external": "", "nofollow": ""},
        "_margin": box(0, 0, margin_bottom, 0),
    }
    if panel:
        s.update({
            "_background_background": "classic",
            "_background_color": MIST,
            "_padding": box(8, 8, 8, 8),
            "_border_radius": radius(12),
        })
    return widget("image", s)

def small_card(fname, title, spec, url, size=50, size_tablet=None):
    return card_column([
        product_image(fname, url, 191, 254, 214),
        heading(title, "h3", TEXT900,
                typo("typography", BODY, 16.6, "700", lh=1.35,
                     tablet=15.8, mobile=14.4),
                margin=box(0, 0, 4, 0), link=url),
        heading(spec, "div", TEXT500,
                typo("typography", MONO, 11.9, "400", lh=1.4, ls=0.24,
                     tablet=11.3, mobile=10.3),
                margin=box(0, 0, 0, 0)),
    ], size, size_tablet)

# ===================================================================== 1. HEAD
eyebrow = widget("icon-list", {
    "view": "inline",
    "icon_list": [{"_id": eid(), "text": "Machinery",
                   "selected_icon": {"value": "fas fa-minus",
                                     "library": "fa-solid"}}],
    "icon_color": ACCENT,
    "icon_size": px(13),
    "text_color": BRAND600,
    "icon_align": "left",
    "space_between": px(0),
    "_margin": box(0, 0, 16, 0),
    **typo("icon_typography", MONO, 13.6, "500", ls=2.18,
           transform="uppercase", tablet=13, mobile=11.8,
           ls_t=2.07, ls_m=1.9),
})

watermark = heading("02", "div", WATERMARK,
                    typo("typography", DISPLAY, 187, "800", lh=1, ls=-3.7),
                    align="right",
                    extra={
                        "_position": "absolute",
                        "_offset_orientation_h": "end",
                        "_offset_x_end": px(0),
                        "_offset_orientation_v": "start",
                        "_offset_y": px(-101),
                        "_z_index": 0,
                        "hide_tablet": "hidden-tablet",
                        "hide_mobile": "hidden-mobile",
                    })

all_machines = widget("button", {
    "text": "All 20+ machines",
    "link": {"url": SITE + "products/", "is_external": "", "nofollow": ""},
    "align": "right",
    "align_mobile": "left",
    "selected_icon": {"value": "fas fa-arrow-right", "library": "fa-solid"},
    "icon_align": "right",
    "icon_indent": px(8),
    "background_color": "#FFFFFF00",
    "button_background_hover_color": "#FFFFFF00",
    "button_text_color": BRAND800,
    "hover_color": BRAND600,
    "text_padding": box(0, 0, 0, 0),
    "border_radius": radius(0),
    "_margin": box(0, 0, 6, 0),
    "_margin_mobile": box(16, 0, 0, 0),
    **typo("typography", BODY, 16.6, "600", tablet=15.8, mobile=14.4),
})

head = section([
    column([
        eyebrow,
        heading("Built in our workshop, proven in yours.", "h2", TEXT900,
                typo("typography", DISPLAY, 50, "700", lh=1.12, ls=-0.9,
                     tablet=42, mobile=27, ls_t=-0.78, ls_m=-0.49),
                margin=box(0, 0, 0, 0)),
    ], 80, {"_inline_size_tablet": 74, "padding": box(0, 14, 0, 14),
            "padding_tablet": box(0, 10, 0, 10),
            "padding_mobile": box(0, 6, 0, 6)}),
    column([watermark, all_machines], 20,
           {"_inline_size_tablet": 26, "content_position": "flex-end",
            "padding": box(0, 14, 0, 14),
            "padding_tablet": box(0, 10, 0, 10),
            "padding_mobile": box(0, 6, 0, 6)}),
], {
    "padding": box(115, 0, 64, 0),
    "padding_tablet": box(72, 26, 45, 26),
    "padding_mobile": box(64, 14, 30, 14),
})

# ============================================================== 2. BENTO ROW 1
# The XL tile - the coded page's .bx--xl. Its final visual pass paints every
# tile the same near-white glass, so this one is white too; what sets it apart
# is the scale of the shot and the round go button pinned bottom-right.
XL_URL = SITE + "products/pelleting/feed-pellet-mill/"
xl_tile = card_column([
    product_image("pelleting-feed-pellet-mill-szlh-cut.webp", XL_URL,
                  476, 520, 300, panel=False, margin_bottom=22),
    heading("Feed pellet mills", "h3", TEXT900,
            typo("typography", DISPLAY, 29.75, "700", lh=1.2, ls=-0.45,
                 tablet=25.6, mobile=19.1),
            margin=box(0, 62, 6, 0), link=XL_URL),
    heading("SZLH · Ø350–768 MM · 2–35 T/H", "div", TEXT500,
            typo("typography", MONO, 11.9, "400", lh=1.4, ls=0.24,
                 tablet=11.3, mobile=10.3),
            margin=box(0, 62, 0, 0)),
    widget("icon", {
        "selected_icon": {"value": "fas fa-arrow-right", "library": "fa-solid"},
        "view": "framed",
        "shape": "circle",
        "primary_color": BRAND200,
        "secondary_color": BRAND800,
        "size": px(15),
        "icon_padding": px(13),
        "border_width": box(1.5, 1.5, 1.5, 1.5),
        "link": {"url": XL_URL, "is_external": "", "nofollow": ""},
        "_position": "absolute",
        "_offset_orientation_h": "end",
        "_offset_x_end": px(44),
        "_offset_orientation_v": "end",
        "_offset_y_end": px(30),
        "_offset_x_end_tablet": px(32),
        "_offset_y_end_tablet": px(22),
        "_offset_x_end_mobile": px(26),
        "_offset_y_end_mobile": px(18),
    }),
], 50, size_tablet=100, pad=30, extra={
    "padding_tablet": box(22, 22, 22, 22),
    "padding_mobile": box(20, 20, 20, 20),
})

quad = column([
    section([
        small_card("grinding-hammer-mill-smsp-01.webp", "Hammer mills",
                   "SMSP · 2–35 T/H", SITE + "products/grinding/hammer-mill/"),
        small_card("mixing-ribbon-mixer-smhy-01.webp", "Mixers",
                   "SMHY · SMSJ · CV ≤ 5%", SITE + "products/mixing/ribbon-mixer/"),
    ], inner=True),
    section([
        small_card("extrusion-fish-feed-extruder-smhs-01.webp", "Extruders",
                   "SMHS · SPHG", SITE + "products/extrusion/fish-feed-extruder/"),
        small_card("cooling-counterflow-cooler-skln-01.webp", "Coolers & dryers",
                   "SKLN · SHGW", SITE + "products/cooling-drying/counterflow-cooler/"),
    ], inner=True),
], 50, {"_inline_size_tablet": 100})

row1 = section([xl_tile, quad], {
    "padding": box(0, 0, 0, 0),
    "padding_tablet": box(0, 26, 0, 26),
    "padding_mobile": box(0, 14, 0, 14),
})

# ============================================================== 3. BENTO ROW 2
PLC_URL = SITE + "products/automation/plc-control-panels/"
plc_tile = column([
    widget("spacer", {"space": px(190), "space_mobile": px(190)}),
    widget("icon-list", {
        "view": "inline",
        # The chip carries the link as well as the column: Elementor's column
        # link control is not present on every version, and a chip nobody can
        # click would be the one dead tile in the grid.
        "icon_list": [{"_id": eid(), "text": "PLC & control panels",
                       "selected_icon": {"value": "fas fa-circle",
                                         "library": "fa-solid"},
                       "link": {"url": PLC_URL, "is_external": "",
                                "nofollow": ""}}],
        "icon_color": ACCENT,
        "icon_size": px(6),
        "text_color": TEXT900,
        "space_between": px(0),
        "_background_background": "classic",
        "_background_color": "rgba(255,255,255,0.94)",
        "_padding": box(9, 15, 9, 15),
        "_border_radius": radius(999),
        "_box_shadow_box_shadow_type": "yes",
        "_box_shadow_box_shadow": {"horizontal": 0, "vertical": 4, "blur": 18,
                                   "spread": 0, "color": "rgba(13,3,51,0.18)"},
        "_position": "absolute",
        "_offset_orientation_h": "start",
        "_offset_x": px(28),
        "_offset_orientation_v": "end",
        "_offset_y_end": px(28),
        "_offset_x_mobile": px(20),
        "_offset_y_end_mobile": px(26),
        **typo("icon_typography", BODY, 14, "600", tablet=13.3, mobile=12.2),
    }),
], 50, {
    "_inline_size_tablet": 100,
    "background_background": "classic",
    "background_image": media("automation-plc-panel-01.webp"),
    "background_position": "center center",
    "background_repeat": "no-repeat",
    "background_size": "cover",
    "border_radius": radius(20),
    "link": {"url": PLC_URL, "is_external": "", "nofollow": ""},
    **GUTTER,
})

row2 = section([
    small_card("screening-crumbler-sslg-01.webp", "Crumblers & screeners",
               "SSLG · SFJH", SITE + "products/screening/crumbler/",
               size=25, size_tablet=50),
    small_card("conveying-bucket-elevator-01.webp", "Conveying & packing",
               "ELEVATORS · SCALES", SITE + "products/conveying/bucket-elevator/",
               size=25, size_tablet=50),
    plc_tile,
], {
    "padding": box(0, 0, 115, 0),
    "padding_tablet": box(0, 26, 72, 26),
    "padding_mobile": box(0, 14, 64, 14),
})

template = {
    "version": "0.4",
    "title": "SEMZON — Machinery bento (home 02)",
    "type": "section",
    "content": [head, row1, row2],
    "page_settings": [],
}

# --- self-audit --------------------------------------------------------------
ALLOWED = {"heading", "image", "icon", "icon-list", "button", "spacer"}
ids, problems = set(), []

def audit(n, path=""):
    et = n.get("elType")
    if et == "section":
        tot = sum(c["settings"]["_column_size"] for c in n["elements"])
        if tot != 100: problems.append(f"{path}: columns total {tot}")
        if n.get("isInner"):
            for c in n["elements"]:
                for w in c["elements"]:
                    if w.get("elType") == "section":
                        problems.append(f"{path}: inner section nested inside an "
                                        "inner section - Elementor rejects that")
        for i, c in enumerate(n["elements"]): audit(c, f"{path}>c{i}")
    elif et == "column":
        for i, w in enumerate(n["elements"]): audit(w, f"{path}>w{i}")
    elif et == "widget":
        wt = n["widgetType"]
        if wt not in ALLOWED: problems.append(f"{path}: widget {wt} not allowed")
        if "css_classes" in n["settings"]:
            problems.append(f"{path}: relies on a CSS class")
        if wt == "heading" and "title_color" not in n["settings"]:
            problems.append(f"{path}: heading carries no styling")
    else:
        problems.append(f"{path}: bad elType {et}")
    if n.get("id") in ids: problems.append(f"duplicate id {n['id']}")
    ids.add(n.get("id"))

for r in template["content"]: audit(r, "root")

with open(os.path.join(OUT, "semzon-machinery.json"), "w", encoding="utf-8") as f:
    json.dump(template, f, indent=2, ensure_ascii=False)

if problems:
    print("PROBLEMS:", *problems, sep="\n  "); raise SystemExit(1)

widgets, images = {}, []
def count(n):
    if n["elType"] == "widget":
        widgets[n["widgetType"]] = widgets.get(n["widgetType"], 0) + 1
    for key in ("image", "background_image"):
        v = n["settings"].get(key)
        if isinstance(v, dict) and v.get("url"):
            images.append(v["url"].rsplit("/", 1)[-1])
    for c in n.get("elements", []): count(c)
for r in template["content"]: count(r)

print(f"semzon-machinery.json — {len(template['content'])} sections, {len(ids)} ids")
print("widgets:", ", ".join(f"{k}x{v}" for k, v in sorted(widgets.items())))
print("images :", len(images))
print("no css_classes anywhere: every element carries its own styling")
