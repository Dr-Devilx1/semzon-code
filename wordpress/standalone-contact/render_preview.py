"""Render the contact template's own settings to HTML.

This does not re-describe the design by hand — it reads semzon-contact.json and
turns each element's Elementor settings into the CSS Elementor would generate,
so the preview shows what those stored values actually produce. If a size,
colour or spacing value in the JSON is wrong, it is wrong here too.
"""

import json, os, html

OUT = os.path.dirname(os.path.abspath(__file__))
doc = json.load(open(os.path.join(OUT, "semzon-contact.json"), encoding="utf-8"))

def dim(v):
    if not isinstance(v, dict): return None
    u = v.get("unit", "px")
    if "size" in v and v["size"] not in (None, ""): return f'{v["size"]}{u}'
    if "top" in v:
        return " ".join(f'{v[k]}{u}' for k in ("top", "right", "bottom", "left"))
    return None

def typo_css(s, prefix="typography"):
    out = []
    fam = s.get(f"{prefix}_font_family")
    if fam: out.append(f"font-family:'{fam}',system-ui,sans-serif")
    for key, css in (("font_size", "font-size"), ("font_weight", "font-weight"),
                     ("line_height", "line-height"), ("letter_spacing", "letter-spacing"),
                     ("text_transform", "text-transform")):
        v = s.get(f"{prefix}_{key}")
        if v is None: continue
        out.append(f"{css}:{dim(v) if isinstance(v, dict) else v}")
    return out

def box_css(s):
    out = []
    for key, css in (("padding", "padding"), ("_padding", "padding"),
                     ("margin", "margin"), ("_margin", "margin")):
        v = dim(s.get(key))
        if v: out.append(f"{css}:{v}")
    for key in ("border_radius", "_border_radius"):
        v = dim(s.get(key))
        if v: out.append(f"border-radius:{v}")
    if s.get("border_border") == "solid":
        w = dim(s.get("border_width")) or "1px"
        out.append(f'border:{w.split()[0]} solid {s.get("border_color", "#ddd")}')
    bg = s.get("background_background")
    if bg == "classic" and s.get("background_color"):
        out.append(f'background:{s["background_color"]}')
    elif bg == "gradient":
        a = s.get("background_gradient_angle", {}).get("size", 180)
        out.append(f'background:linear-gradient({a}deg,{s.get("background_color","#000")},'
                   f'{s.get("background_color_b","#000")})')
    return out

def btn_css(s):
    css = typo_css(s)
    css.append(f'background:{s.get("background_color","#eee")}')
    css.append(f'color:{s.get("button_text_color","#000")}')
    r = dim(s.get("border_radius"))
    if r: css.append(f"border-radius:{r}")
    p = dim(s.get("text_padding"))
    if p: css.append(f"padding:{p}")
    if s.get("border_border") == "solid":
        w = dim(s.get("border_width")) or "1px"
        css.append(f'border:{w.split()[0]} solid {s.get("border_color","#ddd")}')
    else:
        css.append("border:0")
    sh = s.get("button_box_shadow_box_shadow")
    if sh: css.append(f'box-shadow:{sh["horizontal"]}px {sh["vertical"]}px '
                      f'{sh["blur"]}px {sh["spread"]}px {sh["color"]}')
    css += ["display:inline-flex", "align-items:center", "text-decoration:none",
            "cursor:pointer", "box-sizing:border-box"]
    return css

RESP = []   # collected media-query rules, keyed by a per-element class

def responsive(node_id, s):
    """Elementor stores *_tablet and *_mobile variants alongside each control.
    Without emitting them the preview silently shows desktop sizes at every
    width, which would hide a wrong responsive value."""
    cls = "e" + node_id
    for bp, mq in (("tablet", "max-width:1024px"), ("mobile", "max-width:767px")):
        decls = []
        for pre in ("typography", "icon_typography"):
            v = s.get(f"{pre}_font_size_{bp}")
            if v: decls.append(f"font-size:{dim(v)}")
        for key, css in (("padding", "padding"), ("_padding", "padding"),
                         ("margin", "margin"), ("_margin", "margin"),
                         ("height", "height")):
            v = s.get(f"{key}_{bp}")
            if v and dim(v): decls.append(f"{css}:{dim(v)}")
        if decls:
            RESP.append(f"@media({mq}){{.{cls}{{{';'.join(decls)}!important}}}}")
    return cls


def render(node):
    et = node["elType"]; s = node["settings"]
    if et == "section":
        cw = dim(s.get("content_width")) or "1200px"
        inner = "".join(render(c) for c in node["elements"])
        cls = responsive(node["id"], s)
        return (f'<section class="{cls}" style="{";".join(box_css(s))}">'
                f'<div class="cont" style="max-width:{cw}">{inner}</div></section>')
    if et == "column":
        w = s.get("_column_size", 100)
        return (f'<div class="col" style="flex:0 0 {w}%;max-width:{w}%;'
                f'{";".join(box_css(s))}">' + "".join(render(c) for c in node["elements"]) + "</div>")

    wt = node["widgetType"]
    if wt == "heading":
        css = typo_css(s) + box_css(s)
        css.append(f'color:{s.get("title_color","#000")}')
        if s.get("align"): css.append(f'text-align:{s["align"]}')
        tag = s.get("header_size", "h2")
        cls = responsive(node["id"], s)
        return f'<{tag} class="{cls}" style="{";".join(css)}">{html.escape(s["title"])}</{tag}>'
    if wt == "text-editor":
        css = typo_css(s) + box_css(s)
        css.append(f'color:{s.get("text_color","#000")}')
        cls = responsive(node["id"], s)
        return f'<div class="{cls}" style="{";".join(css)}">{s["editor"]}</div>'
    if wt == "button":
        al = s.get("align", "left")
        disp = "block" if al == "center" else "inline-block"
        return (f'<div style="text-align:{al};margin:6px 10px 6px 0;display:{disp}">'
                f'<a style="{";".join(btn_css(s))}">{html.escape(s["text"])}</a></div>')
    if wt == "icon-list":
        css = typo_css(s, "icon_typography")
        css.append(f'color:{s.get("text_color","#000")}')
        gap = dim(s.get("space_between")) or "12px"
        items = "".join(
            f'<li style="display:inline-flex;align-items:center;gap:8px;margin-right:{gap}">'
            f'<span style="width:{dim(s.get("icon_size")) or "6px"};'
            f'height:{dim(s.get("icon_size")) or "6px"};border-radius:50%;'
            f'background:{s.get("icon_color","#000")};display:inline-block"></span>'
            f'{html.escape(i["text"])}</li>' for i in s["icon_list"])
        return f'<ul style="list-style:none;padding:0;margin:14px 0 0;{";".join(css)}">{items}</ul>'
    if wt == "divider":
        return f'<div style="height:{dim(s.get("gap")) or "15px"}"></div>'
    if wt == "google_maps":
        h = dim(s.get("height")) or "400px"
        r = dim(s.get("_border_radius")) or "0"
        sat = s.get("css_filters_saturate", {}).get("size", 100)
        return (f'<div style="height:{h};border-radius:{r};overflow:hidden;'
                f'filter:saturate({sat}%);background:#E8E6F2;display:grid;'
                f'place-items:center;font-family:Inter,sans-serif;color:#4C4D60">'
                f'<div style="text-align:center"><div style="font-size:34px">📍</div>'
                f'<b style="color:#14152B">Elementor Google Maps widget</b><br>'
                f'<span style="font-size:14px">{html.escape(s["address"])}</span></div></div>')
    if wt == "form":
        fields = ""
        for f in s["form_fields"]:
            lbl = (f'<label style="font-family:{chr(39)}{s["label_typography_font_family"]}{chr(39)};'
                   f'font-size:{dim(s["label_typography_font_size"])};'
                   f'letter-spacing:{dim(s["label_typography_letter_spacing"])};'
                   f'text-transform:uppercase;color:{s["label_color"]};'
                   f'display:block;margin-bottom:6px">{html.escape(f["field_label"])}</label>')
            style = (f'width:100%;box-sizing:border-box;'
                     f"font-family:'{s['field_typography_font_family']}';"

                     f'font-size:{dim(s["field_typography_font_size"])};'
                     f'background:{s["field_background_color"]};color:{s["field_text_color"]};'
                     f'border:1.5px solid {s["field_border_color"]};'
                     f'border-radius:{dim(s["field_border_radius"])};padding:13px 16px')
            if f["field_type"] == "textarea":
                ctl = f'<textarea rows="5" placeholder="{html.escape(f.get("placeholder",""))}" style="{style};min-height:130px"></textarea>'
            elif f["field_type"] == "select":
                opts = "".join(f"<option>{html.escape(o)}</option>"
                               for o in f["field_options"].split("\n"))
                ctl = f'<select style="{style}">{opts}</select>'
            else:
                ctl = f'<input placeholder="{html.escape(f.get("placeholder",""))}" style="{style}">'
            w = f.get("width", "100")
            fields += f'<div style="flex:0 0 calc({w}% - 8px);margin-bottom:{dim(s["row_gap"])}">{lbl}{ctl}</div>'
        btn = (f'<a style="background:{s["button_background_color"]};'
               f'color:{s["button_text_color"]};border-radius:{dim(s["button_border_radius"])};'
               f'padding:{dim(s["button_text_padding"])};' + f"font-family:'{s['button_typography_font_family']}';" +
               f'font-size:{dim(s["button_typography_font_size"])};font-weight:600;'
               f'display:inline-block;text-decoration:none">{html.escape(s["button_text"])}</a>')
        return f'<div style="display:flex;flex-wrap:wrap;gap:0 16px">{fields}</div>{btn}'
    return ""

body = "".join(render(n) for n in doc["content"])
resp_css = "\n".join(RESP)
page = f"""<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{doc['title']}</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
 *,*::before,*::after{{box-sizing:border-box}}
 body{{margin:0;background:#fff;font-family:Inter,system-ui,sans-serif}}
 .cont{{margin-inline:auto;display:flex;flex-wrap:wrap;padding-inline:24px}}
 .col{{min-width:0}}
 p{{margin:0 0 10px}}
 @media(max-width:880px){{.col{{flex:0 0 100%!important;max-width:100%!important}}}}
{resp_css}
</style></head><body>{body}</body></html>"""

open(os.path.join(OUT, "preview-contact.html"), "w", encoding="utf-8").write(page)
print("preview-contact.html rendered from the template's own settings")
