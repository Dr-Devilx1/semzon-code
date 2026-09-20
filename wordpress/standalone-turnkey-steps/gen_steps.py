"""SEMZON turnkey steps (home section S6) as an Elementor template.

Same rules as the machinery and contact builds: every colour, size, spacing and
radius is stored as an Elementor setting inside the JSON. Nothing to paste, no
css_classes, no HTML widget.

Sizes are measured off the coded page rendered in a real browser at 1440 / 900
/ 390 rather than read from the source, because its type scales off a fluid
root font-size - that is why they look unround (19.25px, 16.1px, 1.96px).

The connecting rail. The coded page draws it as two absolutely positioned bars
inside .steps-wrap: a grey track, and an accent bar that GSAP scrubs from 0 to
full width as you scroll past. Elementor has no scroll-scrub, so the rail is a
Divider widget already drawn in full accent - which is the state the finished
animation lands on, and the state in the screenshot this was built from. Each
dot is an Icon widget positioned absolutely against its own card, 22px in and
26px up, exactly like the original's .step .dot.
"""

import json, os, hashlib, itertools

OUT = os.path.dirname(os.path.abspath(__file__))
_c = itertools.count(1)
def eid(): return hashlib.md5(("st" + str(next(_c))).encode()).hexdigest()[:7]

# --- design tokens, straight from assets/css/main.css ------------------------
BRAND600 = "#3A22A8"
ACCENT700 = "#840C0C"
ACCENT600 = "#9E1414"
TEXT900  = "#14152B"
TEXT500  = "#4C4D60"
LINE     = "#E4E6EB"
WHITE    = "#FFFFFF"

DISPLAY = "Bricolage Grotesque"
BODY    = "Inter"
MONO    = "JetBrains Mono"

def px(n):  return {"unit": "px", "size": n, "sizes": []}
def pc(n):  return {"unit": "%",  "size": n, "sizes": []}
def em(n):  return {"unit": "em", "size": n, "sizes": []}
def box(t, r, b, l, unit="px"):
    return {"unit": unit, "top": str(t), "right": str(r), "bottom": str(b),
            "left": str(l), "isLinked": False}
def radius(n):
    return {"unit": "px", "top": str(n), "right": str(n), "bottom": str(n),
            "left": str(n), "isLinked": True}

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

def heading(title, tag, color, typ, align=None, margin=None, extra=None):
    s = {"title": title, "header_size": tag, "title_color": color}
    s.update(typ)
    if align: s["align"] = align
    if margin: s["_margin"] = margin
    if extra: s.update(extra)
    return widget("heading", s)

def rich(html, color, typ, margin=None):
    s = {"editor": html, "text_color": color}
    s.update(typ)
    if margin: s["_margin"] = margin
    return widget("text-editor", s)

# ===================================================================== 1. HEAD
eyebrow = widget("icon-list", {
    "view": "inline",
    "icon_list": [{"_id": eid(), "text": "Turnkey delivery",
                   "selected_icon": {"value": "fas fa-minus",
                                     "library": "fa-solid"}}],
    "icon_color": ACCENT700,
    "icon_size": px(13),
    "text_color": BRAND600,
    "icon_align": "left",
    "space_between": px(0),
    "_margin": box(0, 0, 16, 0),
    **typo("icon_typography", MONO, 13.6, "500", ls=2.18,
           transform="uppercase", tablet=13, mobile=11.8,
           ls_t=2.07, ls_m=1.9),
})

head = section([
    column([
        eyebrow,
        # 50px rather than the coded page's 54.25px, and matching the machinery
        # template so the two H2s agree when they sit on the same page.
        heading("Five steps. One signature.", "h2", TEXT900,
                typo("typography", DISPLAY, 50, "700", lh=1.12, ls=-0.9,
                     tablet=42, mobile=27, ls_t=-0.78, ls_m=-0.49),
                margin=box(0, 0, 0, 0)),
    ], 100, {"padding": box(0, 14, 0, 14),
             "padding_tablet": box(0, 10, 0, 10),
             "padding_mobile": box(0, 0, 0, 0)}),
], {
    "padding": box(71, 0, 64, 0),
    "padding_tablet": box(45, 26, 45, 26),
    "padding_mobile": box(40, 20, 30, 20),
})

# ==================================================================== 2. STEPS
STEPS = [
    ("01", "Consult & design",
     "Capacity, layout and power planned around your shed and raw material."),
    ("02", "Engineer & manufacture",
     "Fabricated in our Lahore workshop to released drawings."),
    ("03", "Deliver & install",
     "Site teams erect and align — in Pakistan or across the border."),
    ("04", "Commission & train",
     "Trial runs to rated output; your operators trained on the panel."),
    ("05", "Support & spares",
     "Dies, rollers, screens and answers — from Lahore, for the life of the plant."),
]

def dot():
    """The original's .step .dot - 22px in from the card's left edge and 26px
    above its top, so it lands on the rail. Absolute offsets are measured from
    the card box itself, which is what Elementor positions against."""
    return widget("icon", {
        "selected_icon": {"value": "fas fa-circle", "library": "fa-solid"},
        "view": "default",
        "primary_color": ACCENT600,
        "size": px(12),
        "_position": "absolute",
        "_offset_orientation_h": "start",
        "_offset_x": px(22),
        "_offset_orientation_v": "start",
        "_offset_y": px(-28),
        "hide_tablet": "hidden-tablet",
        "hide_mobile": "hidden-mobile",
    })

def step_card(n, title, body):
    return column([
        dot(),
        heading(n, "div", ACCENT700,
                typo("typography", MONO, 14, "400", lh=1.4, ls=1.96,
                     tablet=13.3, mobile=12.15),
                margin=box(0, 0, 8, 0)),
        heading(title, "h4", TEXT900,
                typo("typography", DISPLAY, 19.25, "700", lh=1.25, ls=-0.29,
                     tablet=18.3, mobile=16.7),
                margin=box(0, 0, 8, 0)),
        # Bare text rather than a <p>: a paragraph inside a Text Editor picks
        # up whatever bottom margin the active theme gives it, which would add
        # unpredictable space inside the card. Without it the only spacing is
        # the heading's own 8px, on any theme.
        rich(body, TEXT500,
             typo("typography", BODY, 16.1, "400", lh=1.65,
                  tablet=15.3, mobile=14),
             margin=box(0, 0, 0, 0)),
    ], 20, {
        # 3-up between 768 and 1024. The coded page keeps all five in a row
        # from 768 up, which leaves 123px cards at that width - one or two
        # words per line, with the longer titles running past the card edge.
        "_inline_size_tablet": 33,
        "background_background": "classic",
        "background_color": WHITE,
        "border_border": "solid",
        "border_width": box(1, 1, 1, 1),
        "border_color": LINE,
        "border_radius": radius(20),
        "padding": box(22, 20, 22, 20),
        "margin": box(0, 14, 0, 14),
        "margin_tablet": box(0, 10, 20, 10),
        "margin_mobile": box(0, 0, 14, 0),
        "space_between_widgets": 0,
    })

rail = widget("divider", {
    "style": "solid",
    "weight": px(2),
    "color": ACCENT600,
    "width": pc(98),
    "align": "center",
    "gap": px(0),
    "_margin": box(0, 0, 20, 0),
    "hide_tablet": "hidden-tablet",
    "hide_mobile": "hidden-mobile",
})

steps = section([
    column([rail, section([step_card(*s) for s in STEPS], inner=True)], 100),
], {
    "padding": box(0, 0, 71, 0),
    "padding_tablet": box(6, 26, 25, 26),
    "padding_mobile": box(0, 20, 40, 20),
})

template = {
    "version": "0.4",
    "title": "SEMZON — Turnkey steps (home)",
    "type": "section",
    "content": [head, steps],
    "page_settings": [],
}

# --- self-audit --------------------------------------------------------------
ALLOWED = {"heading", "text-editor", "icon", "icon-list", "divider"}
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
        if wt == "text-editor" and "text_color" not in n["settings"]:
            problems.append(f"{path}: text-editor carries no styling")
    else:
        problems.append(f"{path}: bad elType {et}")
    if n.get("id") in ids: problems.append(f"duplicate id {n['id']}")
    ids.add(n.get("id"))

for r in template["content"]: audit(r, "root")

with open(os.path.join(OUT, "semzon-turnkey-steps.json"), "w", encoding="utf-8") as f:
    json.dump(template, f, indent=2, ensure_ascii=False)

if problems:
    print("PROBLEMS:", *problems, sep="\n  "); raise SystemExit(1)

widgets = {}
def count(n):
    if n["elType"] == "widget":
        widgets[n["widgetType"]] = widgets.get(n["widgetType"], 0) + 1
    for c in n.get("elements", []): count(c)
for r in template["content"]: count(r)

print(f"semzon-turnkey-steps.json — {len(template['content'])} sections, {len(ids)} ids")
print("widgets:", ", ".join(f"{k}x{v}" for k, v in sorted(widgets.items())))
print("no css_classes anywhere: every element carries its own styling")
