"""SEMZON contact page as an importable Elementor template.

Same rules as the header: classic section > column > widget, typed "section",
core widgets only, so it imports on any Elementor version, free or Pro, with or
without the Container experiment.

The form is a marked slot rather than a hardcoded widget. Elementor Pro's form,
WPForms and Contact Form 7 all emit different markup and none of them can be
embedded in a template that must import without them; the stylesheet styles all
three identically instead. The two WhatsApp/email buttons beside it are core
Button widgets, so the page is functional the moment it is imported even before
a form plugin is configured — which is exactly how the original behaved.
"""

import json, os, hashlib, itertools

OUT = os.path.dirname(os.path.abspath(__file__))
_c = itertools.count(1)

def eid():
    return hashlib.md5(("contact" + str(next(_c))).encode()).hexdigest()[:7]

def section(cols, classes="", extra=None):
    s = {"layout": "full_width", "gap": "no"}
    if classes:
        s["css_classes"] = classes
    if extra:
        s.update(extra)
    return {"id": eid(), "elType": "section", "settings": s, "elements": cols, "isInner": False}

def column(widgets, size, classes=""):
    s = {"_column_size": size, "_inline_size": size}
    if classes:
        s["css_classes"] = classes
    return {"id": eid(), "elType": "column", "settings": s, "elements": widgets, "isInner": False}

def widget(wtype, settings, classes=""):
    s = dict(settings)
    if classes:
        s["css_classes"] = classes
    return {"id": eid(), "elType": "widget", "settings": s, "elements": [], "widgetType": wtype}

def heading(t, tag="h2", classes=""):
    return widget("heading", {"title": t, "header_size": tag}, classes)

def text(html, classes=""):
    return widget("text-editor", {"editor": html}, classes)

def button(label, url, classes):
    return widget("button", {
        "text": label,
        "link": {"url": url, "is_external": "on" if url.startswith("http") else "", "nofollow": ""},
    }, classes)

def note(msg):
    return widget("heading", {"title": msg, "header_size": "div"}, "semzon-slot")


WA = ("https://wa.me/923238845411?text="
      "Salaam%2C%20I%27d%20like%20to%20discuss%20a%20plant.")

# ------------------------------------------------------------------ 1. hero
hero = section([column([
    text('<p><a href="/">Home</a> <i>›</i> <span>Contact</span></p>', "crumb"),
    heading("Start your project", "p", "eyebrow"),
    heading("Talk to an engineer, not a form.", "h1", "display-1"),
    heading(
        "Send drawings, capacity targets, or just an idea — a SEMZON engineer "
        "replies within 24 hours, usually sooner. Prefer instant? WhatsApp goes "
        "straight to the works.", "p", "lead"),
    text('<p><span class="tag">Reply &lt; 24 h</span>'
         '<span class="tag">Lahore, Pakistan</span></p>', "page-meta"),
], 100)], "page-hero")

# ------------------------------------------------ 2. form + contact details
form_col = column([
    note("FORM SLOT — delete this and drop in your form widget. "
         "Elementor Pro Form, WPForms or Contact Form 7; all three are styled "
         "identically by the stylesheet. Fields, in order: "
         "Your name (text, required) | Phone / WhatsApp (tel) | "
         "Interested in (select: Poultry / livestock feed line, Aqua & pet feed line, "
         "Biomass pellet line, Fertilizer plant (SSP / BOP / compost), Single machine, "
         "Automation / retrofit, Spares, Other) | "
         "Target capacity (text, placeholder 'e.g. 20 t/h') | "
         "Your message (textarea, placeholder 'Tell us about the site, raw material, timeline…'). "
         "Set the form's own submit button label to 'Send enquiry'."),
    widget("button", {
        "text": "Send via WhatsApp",
        "link": {"url": WA, "is_external": "on", "nofollow": ""},
    }, "semzon-btn semzon-btn--primary"),
    widget("button", {
        "text": "Send by email",
        "link": {"url": "mailto:semzoneng@gmail.com?subject=RFQ%20%E2%80%94%20SEMZON%20website", "is_external": "", "nofollow": ""},
    }, "semzon-btn semzon-btn--secondary"),
    text("<p>Your message opens in WhatsApp or your mail app addressed to "
         "SEMZON — nothing is stored on this website.</p>", "form-note"),
], 55, "semzon-form-col")

card_col = column([
    text(
        '<div><span class="mono-label">Phone &amp; WhatsApp</span>'
        '<p><b>+92 323 8845411</b></p></div>'
        '<div><span class="mono-label">Email</span>'
        '<p><b>semzoneng@gmail.com</b></p></div>'
        '<div><span class="mono-label">Works &amp; office</span>'
        '<p>2.5 KM Manga Raiwind Road,<br>Manga Mandi, Lahore, Pakistan</p></div>'
        '<div><span class="mono-label">Factory visits</span>'
        '<p>Any working day — message ahead and we&rsquo;ll schedule a workshop '
        'walkthrough.</p></div>',
        "contact-card"),
], 45, "semzon-card-col")

form_section = section([form_col, card_col], "section section--tight page-grid")

# ------------------------------------------------------------------ 3. map
map_section = section([column([
    heading("Find us", "p", "eyebrow"),
    heading("Lahore works, global reach.", "h2", "display-2"),
    widget("image", {
        "image": {"url": "", "id": ""},
        "image_size": "full",
        "align": "center",
    }, "semzon-map"),
], 100)], "section section--tight")

# ------------------------------------------------------------------ 4. CTA
cta = section([column([
    heading("Start a conversation", "p", "eyebrow eyebrow--plain"),
    heading("Discuss your plant with our engineers.", "h2", "display-2"),
    heading("Send drawings, capacity targets, or just an idea. A SEMZON engineer "
            "replies within 24 hours — usually sooner.", "p", "lead"),
    widget("button", {"text": "Start your project",
                      "link": {"url": "/contact/", "is_external": "", "nofollow": ""}},
           "semzon-btn semzon-btn--inverse"),
    widget("button", {"text": "WhatsApp +92 323 8845411",
                      "link": {"url": WA, "is_external": "on", "nofollow": ""}},
           "semzon-btn semzon-btn--ghost"),
], 100)], "band-wrap")

template = {
    "version": "0.4",
    "title": "SEMZON — Contact",
    "type": "section",
    "content": [hero, form_section, map_section, cta],
    "page_settings": [],
}

# ------------------------------------------------------------------- audit
CORE = {"heading", "text-editor", "image", "button", "icon-list", "divider", "spacer"}
problems, ids = [], set()

def audit(n, path=""):
    et = n.get("elType")
    if et == "section":
        total = sum(c["settings"]["_column_size"] for c in n["elements"]
                    if c.get("elType") == "column")
        if total != 100:
            problems.append(f"{path}: columns total {total}")
        for i, c in enumerate(n["elements"]):
            audit(c, f"{path}>c{i}")
    elif et == "column":
        for i, w in enumerate(n["elements"]):
            audit(w, f"{path}>w{i}")
    elif et == "widget":
        if n["widgetType"] not in CORE:
            problems.append(f"{path}: non-core widget {n['widgetType']}")
    else:
        problems.append(f"{path}: bad elType {et}")
    if n.get("id") in ids:
        problems.append(f"duplicate id {n['id']}")
    ids.add(n.get("id"))

for r in template["content"]:
    audit(r, "root")

with open(os.path.join(OUT, "semzon-contact.json"), "w", encoding="utf-8") as f:
    json.dump(template, f, indent=2, ensure_ascii=False)

if problems:
    print("PROBLEMS:", *problems, sep="\n  ")
    raise SystemExit(1)
print(f"semzon-contact.json — {len(template['content'])} sections, "
      f"{len(ids)} unique ids, core widgets only, type='section'")
