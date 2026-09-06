"""Build the multi-location network map as a standalone SVG.

The original page draws this with the Google Maps JS API — seven custom pins,
a bespoke greyscale style and info windows. Elementor's Google Maps widget only
does a single location, and a styled multi-pin map genuinely needs JavaScript,
so a live map is not reproducible as an Elementor widget.

This is the same information as a self-contained SVG: every one of the seven
real locations, positioned by an equirectangular projection of its true
lat/lon, in the original's visual language (blueprint grid, dashed delivery
arcs from the Lahore hub, ringed HQ marker, mono labels). No API key, no
JavaScript, no billing, and it scales perfectly on every screen.
"""

import os

OUT = os.path.dirname(os.path.abspath(__file__))

# name, lat, lon, caption, kind
LOCATIONS = [
    ("SEMZON — HQ & Works", 31.291, 74.102, "LAHORE · HQ + WORKS",      "hq"),
    ("Albadar Feed",        34.555, 69.208, "KABUL · 20 T/H TURNKEY",   "project"),
    ("Roshan Feeds",        32.188, 74.195, "GUJRANWALA · PELLET LINE", "project"),
    ("Auriga",              24.861, 67.001, "KARACHI · BOP PLANT",      "project"),
    ("Subhan & Al-Itifaq",  31.119, 74.450, "KASUR · FEED LINES",       "project"),
    ("Quetta WMC",          30.180, 66.975, "QUETTA · COMPOST",         "project"),
    ("Export & Support",    25.205, 55.271, "DUBAI · GCC EXPORT",       "export"),
]

W, H = 900, 520
# Asymmetric horizontal padding on purpose: Lahore, Gujranwala and Kasur are
# the easternmost points, and their labels sit to the right of the marker. A
# symmetric pad put them past the canvas edge, so the plot area is pulled left
# to reserve a label gutter.
PAD_L, PAD_R, PAD_Y = 60, 235, 56
LON0, LON1 = 53.0, 77.5
LAT0, LAT1 = 23.5, 35.5

def project(lat, lon):
    x = (lon - LON0) / (LON1 - LON0) * (W - PAD_L - PAD_R) + PAD_L
    y = (LAT1 - lat) / (LAT1 - LAT0) * (H - 2 * PAD_Y) + PAD_Y
    return round(x, 1), round(y, 1)

pts = [(n, *project(la, lo), cap, kind) for n, la, lo, cap, kind in LOCATIONS]
hub = next(p for p in pts if p[4] == "hq")

# Label offsets, hand-tuned: the three Punjab sites sit within ~120km of each
# other, so their markers genuinely overlap at this scale. Leader lines fan the
# labels out instead of letting them collide.
OFFSETS = {
    "LAHORE · HQ + WORKS":      ( 34, -14, "start"),
    "GUJRANWALA · PELLET LINE": ( 34, -46, "start"),
    "KASUR · FEED LINES":       ( 34,  30, "start"),
    "KABUL · 20 T/H TURNKEY":   (-14, -26, "end"),
    "QUETTA · COMPOST":         (-14,  -8, "end"),
    "KARACHI · BOP PLANT":      ( 34,  26, "start"),
    "DUBAI · GCC EXPORT":       ( 16,  26, "start"),
}

arcs, markers, labels = [], [], []

for name, x, y, cap, kind in pts:
    if kind != "hq":
        # Curve bows away from the straight line, matching the original's arcs.
        mx, my = (hub[1] + x) / 2, (hub[2] + y) / 2
        dx, dy = x - hub[1], y - hub[2]
        cx, cy = mx - dy * 0.18, my + dx * 0.18
        arcs.append(
            f'  <path d="M{hub[1]} {hub[2]} Q {cx:.0f} {cy:.0f}, {x} {y}" fill="none" '
            f'stroke="#1C0863" stroke-width="1.4" stroke-dasharray="4 5" opacity=".55"/>'
        )

    if kind == "hq":
        markers.append(f'  <circle cx="{x}" cy="{y}" r="26" fill="none" stroke="#1C0863" stroke-width="1" opacity=".5"/>')
        markers.append(f'  <circle cx="{x}" cy="{y}" r="16" fill="none" stroke="#1C0863" stroke-width="1" opacity=".8"/>')
        markers.append(f'  <circle cx="{x}" cy="{y}" r="7.5" fill="#840C0C"/>')
    elif kind == "export":
        markers.append(f'  <circle cx="{x}" cy="{y}" r="5.5" fill="none" stroke="#1C0863" stroke-width="2"/>')
    else:
        markers.append(f'  <circle cx="{x}" cy="{y}" r="5" fill="#1C0863"/>')

    ox, oy, anchor = OFFSETS[cap]
    lx, ly = x + ox, y + oy
    # leader line from marker to label, only where the label is pushed away
    if abs(oy) > 20:
        elbow_x = x + (10 if ox > 0 else -10)
        labels.append(
            f'  <path d="M{x + (8 if ox > 0 else -8)} {y} L{elbow_x} {ly - 4} L{lx - (4 if ox > 0 else -4)} {ly - 4}" '
            f'fill="none" stroke="#8A79CC" stroke-width="1" opacity=".7"/>'
        )
    labels.append(f'  <text x="{lx}" y="{ly}" text-anchor="{anchor}">{cap}</text>')

svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" role="img"
     aria-label="SEMZON delivery network: Lahore headquarters and works, with installations in Gujranwala, Kasur, Karachi, Quetta and Kabul, and export support from Dubai.">
  <defs>
    <pattern id="szgrid" width="40" height="40" patternUnits="userSpaceOnUse">
      <path d="M40 0H0V40" fill="none" stroke="#D5CFEE" stroke-width="1"/>
    </pattern>
    <radialGradient id="szglow" cx="50%" cy="50%" r="50%">
      <stop offset="0%"   stop-color="#6C4FE0" stop-opacity=".18"/>
      <stop offset="100%" stop-color="#6C4FE0" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="{W}" height="{H}" fill="#F5F4FB"/>
  <rect width="{W}" height="{H}" fill="url(#szgrid)"/>
  <ellipse cx="{hub[1]}" cy="{hub[2]}" rx="300" ry="230" fill="url(#szglow)"/>

{chr(10).join(arcs)}

{chr(10).join(markers)}

  <g font-family="JetBrains Mono, ui-monospace, monospace" font-size="12"
     letter-spacing="1.2" fill="#33344A">
{chr(10).join(labels)}
  </g>
</svg>
'''

with open(os.path.join(OUT, "semzon-map.svg"), "w", encoding="utf-8") as f:
    f.write(svg)

print(f"semzon-map.svg written — {len(pts)} locations, {len(arcs)} delivery arcs")
for n, x, y, cap, kind in pts:
    print(f"  {kind:8s} {cap:26s} -> ({x}, {y})")
