# QA checklist — before go-live

Work top to bottom. Anything that fails, fix before moving on.

## 1. Setup verified

- [ ] SEMZON → Setup Wizard shows all eight steps green
- [ ] Products list shows **22**, Solutions **8**, Projects **6**
- [ ] Media library contains the **36** machine and project images
- [ ] Product Categories has **11** terms, each with products in it
- [ ] SEMZON → Plant Flow shows **6** stages, each with linked machines
- [ ] Settings → Reading shows *Home* as the front page
- [ ] Settings → Permalinks is on **Post name**

## 1b. Installer removal (do this once everything above passes)

- [ ] Plugins → **SEMZON Setup** → Deactivate → Delete
- [ ] Products / Solutions / Projects still listed in wp-admin
- [ ] Open a product — all custom fields still present with their values
- [ ] SEMZON → Settings still reachable, values intact
- [ ] SEMZON → Plant Flow still reachable, 6 stages intact
- [ ] Front end unchanged — home, a product, a solution, a project

If anything above fails, the theme is not active. Re-activate **SEMZON
Engineering** under Appearance → Themes; the plugin is not the fix.

## 2. Templates

- [ ] Each of the 7 templates imported and given its display condition
- [ ] Every `{{ACF: …}}` placeholder replaced with a real dynamic binding
- [ ] Every dashed build-note block replaced with its Loop Grid / widget
- [ ] Open 3 different products — each shows *its own* content, not the same
      values repeated (the classic sign of a hardcoded template)
- [ ] Same check on 2 solutions and 2 projects

## 3. Responsive — all six ranges

Test each at **375, 640, 900, 1150, 1400 and 1700px**. On every page type
(home, product, solution, project, archive, contact):

- [ ] No horizontal scrollbar anywhere
- [ ] `.page-grid` is one column below 900px, two above
- [ ] Mega menu appears at 1024px+, burger below it
- [ ] Mobile action bar visible below 1024px, hidden on desktop
- [ ] Action bar hides itself over the CTA band and footer
- [ ] Spec tables scroll inside their own box on narrow screens, never push
      the page sideways
- [ ] Tag pills wrap rather than overflow
- [ ] Hero card does not clip at any width

The design system covers all six ranges itself — these checks confirm nothing
in the Elementor layout is fighting it. If something is off, fix the Elementor
override, not the stylesheet.

## 4. Behaviour

- [ ] Mega menus open on hover (desktop) and close when the pointer leaves the
      header, not before — moving from the button into the panel must not close it
- [ ] Escape closes an open mega menu and returns focus to its button
- [ ] Drawer opens, traps Tab focus, closes on Escape and on any link click
- [ ] FAQ items open and close
- [ ] Plant flow: pins and scrubs through six stages on desktop; taps through
      on mobile; arrow keys move between stages
- [ ] Counters animate once on scroll
- [ ] Scroll progress bar fills across the page
- [ ] Marquee pauses on hover
- [ ] Nothing animates when the OS "reduce motion" setting is on — the page
      must still be fully readable and counters must show final values

## 5. Links & redirects

- [ ] Every mega-menu link resolves (no 404s)
- [ ] Old nested URLs 301 to the new ones — test at least:
      `/products/grinding/hammer-mill/` → `/products/hammer-mill/`
      `/products/mixing/ribbon-mixer/index.html` → `/products/ribbon-mixer/`
- [ ] Related-machine tiles on a product go to the right products
- [ ] Plant-flow machine links open the right product
- [ ] WhatsApp button opens a chat with the right number and message
- [ ] Contact form's WhatsApp and email hand-off both work

## 6. SEO

- [ ] Install Rank Math, run its setup, submit the sitemap
- [ ] Every product/solution/project has a unique title and meta description
      (the importer put the originals in the post excerpt — map that to Rank
      Math's description field, or write fresh ones)
- [ ] Product schema present on product pages
- [ ] Organization schema present sitewide
- [ ] `robots.txt` allows crawling and points at the new sitemap
- [ ] No `noindex` left on the site from the staging phase — **check this
      twice**, Settings → Reading → "Discourage search engines" must be OFF

## 7. Performance

- [ ] Run PageSpeed Insights on home and one product page, mobile and desktop
- [ ] LCP image on each template is *not* lazy-loaded (set the hero image's
      loading attribute to eager)
- [ ] Enable a caching plugin and re-test
- [ ] Confirm the registered image sizes are being served — a 1200×900 render
      should not be delivered as a full-size upload

## 8. Security & housekeeping

- [ ] **Google Maps key rotated** in Google Cloud and restricted by HTTP
      referrer to `semzoneng.com/*` — the old key is public in the GitHub repo
- [ ] New key pasted into SEMZON → Settings, map loads
- [ ] Delete or demote the temporary admin account created for the build
- [ ] Take a full backup (UpdraftPlus) before pointing the live domain
- [ ] 8 placeholder SVG illustrations replaced with real photos, or accepted
      as-is for launch
