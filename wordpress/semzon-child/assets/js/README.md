# Self-hosting GSAP (recommended, 2 minutes)

The theme currently loads GSAP and ScrollTrigger from cdnjs, which is what the
original coded site did. That works, but self-hosting is better: one less
third-party connection, no dependency on a CDN staying up, no visitor IPs sent
to another host, and a small Core Web Vitals gain.

## How

1. Download these two files (GSAP 3.12.5):
   - <https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js>
   - <https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js>
2. Save both into this folder, keeping the exact filenames:
   - `gsap.min.js`
   - `ScrollTrigger.min.js`
3. Done. `functions.php` detects them and switches over automatically — no
   code change, no setting.

Both files must be present. If only one is there the theme keeps using the CDN,
because a half-local pair would load two different builds.

## Licence

GSAP core and ScrollTrigger are free to use on a standard commercial site
under GreenSock's no-charge licence. The paid Club licence is only needed for
the bonus plugins (SplitText, MorphSVG and similar), none of which this build
uses. See <https://gsap.com/licensing/>.

## If you remove GSAP entirely

The site still works. `semzon.js` checks for GSAP before touching it: with the
library absent, or when the visitor has "reduce motion" enabled, every section
renders in its final state, counters show their final numbers, and the plant
flow becomes a tap-through list. Nothing is hidden behind an animation that
never runs.
