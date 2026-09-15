"""Render semzon-machinery.json to HTML using the template's own settings.

Nothing here re-describes the design by hand: every colour, size, radius and
gap below is read out of the JSON and turned into the CSS Elementor would
generate for that control. A wrong value in the template is a wrong value in
the preview, which is the point.

Images resolve to the local copies in assets/img so the preview renders
offline; the template itself points at the live site so Elementor can import
them into the media library.
"""

import json, os, html

OUT = os.path.dirname(os.path.abspath(__file__))
doc = json.load(open(os.path.join(OUT, "semzon-machinery.json"), encoding="utf-8"))
LOCAL = "img/"          # preview-local copies, keyed by the same basename

RESP = []               # collected media-query rules, one class per element

def dim(v):
    if not isinstance(v, dict): return None
    u = v.get("unit", "px")
    if v.get("size") not in (None, ""): return f'{v["size"]}{u}'
    if "top" in v: return " ".join(f'{v[k]}{u}' for k in ("top","right","bottom","left"))
    return None

def typo_css(s, prefix="typography"):
    out = []
    fam = s.get(f"{prefix}_font_family")
    if fam: out.append(f"font-family:'{fam}',system-ui,sans-serif")
    for key, css in (("font_size","font-size"), ("font_weight","font-weight"),
                     ("line_height","line-height"), ("letter_spacing","letter-spacing"),
                     ("text_transform","text-transform")):
        v = s.get(f"{prefix}_{key}")
        if v is None: continue
        out.append(f"{css}:{dim(v) if isinstance(v, dict) else v}")
    return out

def surface(s, pre=""):
    """Background / border / radius / padding / margin, for either a column
    (bare keys) or a widget (underscore-prefixed keys)."""
    out = []
    bg = s.get(f"{pre}background_background")
    if bg == "classic":
        if s.get(f"{pre}background_color"):
            out.append(f'background-color:{s[pre + "background_color"]}')
        bi = s.get(f"{pre}background_image")
        if isinstance(bi, dict) and bi.get("url"):
            out.append(f'background-image:url({LOCAL + bi["url"].rsplit("/",1)[-1]})')
            out.append(f'background-position:{s.get(pre+"background_position","center center")}')
            out.append(f'background-repeat:{s.get(pre+"background_repeat","no-repeat")}')
            out.append(f'background-size:{s.get(pre+"background_size","cover")}')
    elif bg == "gradient":
        a = s.get(f"{pre}background_gradient_angle", {}).get("size", 180)
        c1 = s.get(f"{pre}background_color", "#fff")
        c2 = s.get(f"{pre}background_color_b", "#fff")
        s1 = dim(s.get(f"{pre}background_color_stop")) or "0%"
        s2 = dim(s.get(f"{pre}background_color_b_stop")) or "100%"
        out.append(f"background-image:linear-gradient({a}deg,{c1} {s1},{c2} {s2})")
    if s.get(f"{pre}border_border") == "solid":
        w = dim(s.get(f"{pre}border_width")) or "1px"
        out.append(f'border:{w.split()[0]} solid {s.get(pre+"border_color","#ddd")}')
    r = dim(s.get(f"{pre}border_radius"))
    if r: out.append(f"border-radius:{r}")
    p = dim(s.get(f"{pre}padding"))
    if p: out.append(f"padding:{p}")
    m = dim(s.get(f"{pre}margin"))
    if m: out.append(f"margin:{m}")
    sh = s.get(f"{pre}box_shadow_box_shadow")
    if sh: out.append(f'box-shadow:{sh["horizontal"]}px {sh["vertical"]}px '
                      f'{sh["blur"]}px {sh["spread"]}px {sh["color"]}')
    return out

def absolute(s):
    """Elementor's Advanced > Position: absolute, with its four offsets."""
    if s.get("_position") != "absolute": return []
    out = ["position:absolute"]
    if s.get("_offset_orientation_h") == "end":
        out.append(f'right:{dim(s.get("_offset_x_end")) or "0px"}')
    else:
        out.append(f'left:{dim(s.get("_offset_x")) or "0px"}')
    if s.get("_offset_orientation_v") == "end":
        out.append(f'bottom:{dim(s.get("_offset_y_end")) or "0px"}')
    else:
        out.append(f'top:{dim(s.get("_offset_y")) or "0px"}')
    if s.get("_z_index") is not None: out.append(f'z-index:{s["_z_index"]}')
    out.append("width:auto")
    return out

def responsive(node_id, s, extra_keys=(), skip=()):
    """Emit the *_tablet / *_mobile variants the JSON stores. Without these the
    preview would show desktop values at every width and hide a bad one."""
    cls = "e" + node_id
    for bp, mq in (("tablet", "max-width:1024px"), ("mobile", "max-width:767px")):
        decls = []
        for pre in ("typography", "icon_typography"):
            v = s.get(f"{pre}_font_size_{bp}")
            if v: decls.append(f"font-size:{dim(v)}")
        for key, css in (("padding","padding"), ("_padding","padding"),
                         ("margin","margin"), ("_margin","margin"),
                         ("height","height"), ("space","height"),
                         ("_offset_x_end","right"), ("_offset_y_end","bottom")) + tuple(extra_keys):
            if key in skip: continue
            v = s.get(f"{key}_{bp}")
            if v and dim(v): decls.append(f"{css}:{dim(v)}")
        if s.get(f"hide_{bp}"): decls.append("display:none")
        al = s.get(f"align_{bp}")
        if al: decls.append(f"text-align:{al}")
        if decls:
            RESP.append(f"@media({mq}){{.{cls}{{{';'.join(decls)}!important}}}}")
    return cls

def col_width(s):
    """Column width per breakpoint. Elementor stacks to 100% at mobile unless
    _inline_size_mobile says otherwise."""
    return (s.get("_column_size", 100),
            s.get("_inline_size_tablet", s.get("_column_size", 100)),
            s.get("_inline_size_mobile", 100))


def render(node):
    et = node["elType"]; s = node["settings"]

    if et == "section":
        inner = "".join(render(c) for c in node["elements"])
        cls = responsive(node["id"], s)
        if node.get("isInner"):
            return f'<div class="row {cls}" style="{";".join(surface(s))}">{inner}</div>'
        cw = dim(s.get("content_width")) or "1384px"
        return (f'<section class="{cls}" style="{";".join(surface(s))}">'
                f'<div class="row" style="max-width:{cw};margin-inline:auto">{inner}</div></section>')

    if et == "column":
        d, t, m = col_width(s)
        cls = "e" + node["id"]
        RESP.append(f"@media(max-width:1024px){{.{cls}{{flex:0 0 {t}%!important;max-width:{t}%!important}}}}")
        RESP.append(f"@media(max-width:767px){{.{cls}{{flex:0 0 {m}%!important;max-width:{m}%!important}}}}")
        for bp, mq in (("tablet","max-width:1024px"), ("mobile","max-width:767px")):
            decls = []
            for key, css in (("padding","padding"), ("margin","margin")):
                v = dim(s.get(f"{key}_{bp}"))
                if v: decls.append(f"{css}:{v}")
            if decls:
                RESP.append(f"@media({mq}){{.{cls}>.pop{{{';'.join(decls)}!important}}}}")
        pop = ["position:relative", "display:flex", "flex-direction:column",
               "flex:1", "box-sizing:border-box"] + surface(s)
        if s.get("content_position") == "flex-end": pop.append("justify-content:flex-end")
        body = "".join(render(c) for c in node["elements"])
        return (f'<div class="col {cls}" style="flex:0 0 {d}%;max-width:{d}%">'
                f'<div class="pop" style="{";".join(pop)}">{body}</div></div>')

    wt = node["widgetType"]
    base = surface(s, "_") + absolute(s)

    if wt == "heading":
        css = typo_css(s) + base
        css.append(f'color:{s.get("title_color","#000")}')
        if s.get("align"): css.append(f'text-align:{s["align"]}')
        tag = s.get("header_size", "h2")
        if tag == "div": tag = "div"
        cls = responsive(node["id"], s)
        t = html.escape(s["title"])
        if s.get("link"): t = f'<a style="color:inherit;text-decoration:none">{t}</a>'
        return f'<{tag} class="{cls}" style="{";".join(css)}">{t}</{tag}>'

    if wt == "image":
        # Elementor puts width/height/object-fit on the <img> itself, so the
        # responsive height has to land there too - hanging it on the wrapper
        # instead lets the image overflow its panel at tablet and mobile.
        cls = responsive(node["id"], s, skip=("height",))
        icls = "i" + node["id"]
        for bp, mq in (("tablet","max-width:1024px"), ("mobile","max-width:767px")):
            v = s.get(f"height_{bp}")
            if v: RESP.append(f"@media({mq}){{.{icls}{{height:{dim(v)}!important}}}}")
        wrap = base + ["box-sizing:border-box", "line-height:0"]
        if s.get("align") == "center": wrap.append("text-align:center")
        img = [f'width:{dim(s.get("width")) or "100%"}',
               f'height:{dim(s.get("height")) or "auto"}',
               f'object-fit:{s.get("object-fit","fill")}']
        r = dim(s.get("image_border_radius"))
        if r: img.append(f"border-radius:{r}")
        src = LOCAL + s["image"]["url"].rsplit("/", 1)[-1]
        return (f'<div class="{cls}" style="{";".join(wrap)}">'
                f'<img class="{icls}" src="{src}" alt="" style="{";".join(img)}"></div>')

    if wt == "icon":
        cls = responsive(node["id"], s)
        pad = dim(s.get("icon_padding")) or "10px"
        bw = (dim(s.get("border_width")) or "1px").split()[0]
        css = base + [
            "display:inline-grid", "place-items:center", "border-radius:50%",
            f'border:{bw} solid {s.get("primary_color","#000")}',
            f'color:{s.get("secondary_color","#000")}',
            f'font-size:{dim(s.get("size")) or "16px"}',
            f"width:calc({pad}*2 + {dim(s.get('size')) or '16px'})",
            f"height:calc({pad}*2 + {dim(s.get('size')) or '16px'})"]
        return f'<span class="{cls}" style="{";".join(css)}">&#8594;</span>'

    if wt == "icon-list":
        cls = responsive(node["id"], s)
        css = typo_css(s, "icon_typography") + base
        css += [f'color:{s.get("text_color","#000")}', "display:inline-flex",
                "align-items:center", "gap:10px", "list-style:none",
                "box-sizing:border-box"]
        sz = dim(s.get("icon_size")) or "10px"
        glyph = s["icon_list"][0]["selected_icon"]["value"]
        # fa-minus is a rule, fa-circle is a dot - both drawn, not font-loaded
        if "minus" in glyph:
            mark = (f'<span style="width:26px;height:1px;flex:none;'
                    f'background:{s.get("icon_color","#000")}"></span>')
        else:
            mark = (f'<span style="width:{sz};height:{sz};flex:none;border-radius:50%;'
                    f'background:{s.get("icon_color","#000")}"></span>')
        items = "".join(mark + html.escape(i["text"]) for i in s["icon_list"])
        return f'<div class="{cls}" style="{";".join(css)}">{items}</div>'

    if wt == "button":
        cls = responsive(node["id"], s)
        css = typo_css(s) + surface(s, "_")
        css += [f'color:{s.get("button_text_color","#000")}',
                f'background:{s.get("background_color","#eee")}',
                f'padding:{dim(s.get("text_padding")) or "12px 24px"}',
                "border:0", "display:inline-flex", "align-items:center",
                f'gap:{dim(s.get("icon_indent")) or "8px"}',
                "text-decoration:none", "box-sizing:border-box"]
        wrapcss = [f'text-align:{s.get("align","left")}']
        m = dim(s.get("_margin"))
        if m: wrapcss.append(f"margin:{m}")
        return (f'<div class="{cls}" style="{";".join(wrapcss)}">'
                f'<a style="{";".join(css)}">{html.escape(s["text"])}'
                f'<span>&#8594;</span></a></div>')

    if wt == "spacer":
        cls = responsive(node["id"], s)
        return f'<div class="{cls}" style="height:{dim(s.get("space")) or "50px"}"></div>'
    return ""

body = "".join(render(n) for n in doc["content"])
page = f"""<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{html.escape(doc['title'])}</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
 *,*::before,*::after{{box-sizing:border-box}}
 body{{margin:0;background:#fff;font-family:Inter,system-ui,sans-serif;overflow-x:hidden}}
 section{{position:relative}}
 .row{{display:flex;flex-wrap:wrap;width:100%}}
 .col{{min-width:0;display:flex;position:relative}}
 h2,h3,div{{margin:0}}
{chr(10).join(RESP)}
</style></head><body>{body}</body></html>"""

open(os.path.join(OUT, "preview-machinery.html"), "w", encoding="utf-8").write(page)
print("preview-machinery.html rendered from the template's own settings")
