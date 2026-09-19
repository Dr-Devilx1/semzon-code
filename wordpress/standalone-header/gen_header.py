"""Standalone SEMZON header as an importable Elementor template.

Built for a fresh site: no SEMZON theme, no CPTs, no ACF. Classic
section > column > widget with core widgets only, typed "section" so it
imports on any Elementor version, free or Pro, Container experiment on or off.

The menu itself is left as a marked slot rather than a hardcoded third-party
widget: hardcoding uael-nav-menu would break the import for anyone without
Ultimate Addons, and hardcoding Elementor Pro's nav-menu would break it on
free. The accompanying stylesheet styles all three (UAE, Elementor Pro, and a
plain WordPress menu) identically, so whichever widget is dropped into the slot
comes out looking the same.
"""

import json, os, hashlib, itertools

OUT = "/tmp/claude-0/-home-user-semzon-code/736ac3d6-c6ac-5de2-adab-dd7a10217ff5/scratchpad/header-out"
os.makedirs(OUT, exist_ok=True)
_c = itertools.count(1)

def eid():
    return hashlib.md5(("hdr" + str(next(_c))).encode()).hexdigest()[:7]

def section(cols, classes="", extra=None):
    s = {"layout": "full_width", "gap": "no", "css_classes": classes}
    if extra:
        s.update(extra)
    return {"id": eid(), "elType": "section", "settings": s,
            "elements": cols, "isInner": False}

def column(widgets, size, classes=""):
    s = {"_column_size": size, "_inline_size": size}
    if classes:
        s["css_classes"] = classes
    return {"id": eid(), "elType": "column", "settings": s,
            "elements": widgets, "isInner": False}

def widget(wtype, settings, classes=""):
    s = dict(settings)
    if classes:
        s["css_classes"] = classes
    return {"id": eid(), "elType": "widget", "settings": s,
            "elements": [], "widgetType": wtype}

def text(html, classes="", align="left"):
    return widget("text-editor", {"editor": html, "align": align}, classes)

def note(msg):
    return widget("heading", {"title": msg, "header_size": "div"}, "semzon-slot")


# ---------------------------------------------------------------- utility bar
util = section([
    column([
        text(
            '<p><a href="tel:+923238845411">+92 323 8845411</a>'
            '<a href="mailto:semzoneng@gmail.com">semzoneng@gmail.com</a></p>',
            "semzon-util-left"
        ),
    ], 50),
    column([
        text(
            '<p>ISO 9001:2015 &middot; MANGA RAIWIND ROAD, LAHORE</p>',
            "semzon-util-right", "right"
        ),
    ], 50),
], "semzon-util")


# --------------------------------------------------------------- main header
header = section([
    # Logo — image widget, as requested. Pick your logo in Elementor.
    column([
        widget("image", {
            "image": {"url": "", "id": ""},
            "image_size": "full",
            "align": "left",
            "width": {"unit": "px", "size": 168},
        }, "semzon-logo"),
    ], 22, "semzon-col-logo"),

    # Menu slot.
    column([
        note("MENU SLOT — delete this block and drop your nav widget here. "
             "UAE Nav Menu, or Elementor Pro Nav Menu. Layout: Horizontal. "
             "Breakpoint: Tablet (or 1024). The stylesheet styles all of them "
             "identically, including dropdowns."),
    ], 56, "semzon-col-nav"),

    # CTA.
    column([
        widget("button", {
            "text": "Start your project",
            "link": {"url": "/contact/", "is_external": "", "nofollow": ""},
            "align": "right",
            "size": "sm",
        }, "semzon-cta"),
    ], 22, "semzon-col-cta"),
], "semzon-header")


template = {
    "version": "0.4",
    "title": "SEMZON — Header",
    "type": "section",
    "content": [util, header],
    "page_settings": [],
}

# ---------------------------------------------------------------- self-audit
CORE = {"heading", "text-editor", "image", "button", "icon-list", "divider", "spacer"}
problems, ids = [], set()

def audit(n, path=""):
    et = n.get("elType")
    if et == "section":
        total = 0
        for i, c in enumerate(n["elements"]):
            if c.get("elType") != "column":
                problems.append(f"{path}: section child is {c.get('elType')}")
                continue
            total += c["settings"]["_column_size"]
            audit(c, f"{path}>col{i}")
        if total != 100:
            problems.append(f"{path}: columns total {total}, not 100")
    elif et == "column":
        for i, w in enumerate(n["elements"]):
            audit(w, f"{path}>w{i}")
    elif et == "widget":
        if n["widgetType"] not in CORE:
            problems.append(f"{path}: non-core widget {n['widgetType']}")
    else:
        problems.append(f"{path}: bad elType {et}")
    if n.get("id") in ids:
        problems.append(f"duplicate id {n.get('id')}")
    ids.add(n.get("id"))

for root in template["content"]:
    audit(root, "root")

with open(os.path.join(OUT, "semzon-header.json"), "w", encoding="utf-8") as f:
    json.dump(template, f, indent=2, ensure_ascii=False)

if problems:
    print("PROBLEMS:")
    for p in problems:
        print("  -", p)
    raise SystemExit(1)

print(f"semzon-header.json written — {len(template['content'])} sections, "
      f"{len(ids)} unique ids, core widgets only, type='section'.")
