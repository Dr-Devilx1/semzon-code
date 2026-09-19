/* ============================================================
   SEMZON ENGINEERING — motion & behaviour layer (WordPress)
   Base: nav · drawer · FAQ · plant-flow · toast (no dependencies)
   Motion: GSAP 3 + ScrollTrigger — every scroll choreographed.

   Ported from the original coded build. Two things changed for
   WordPress: the plant-flow stages now come from the ACF options
   page (SEMZON_DATA.flow) instead of a hardcoded array, and the
   Google Maps key comes from a WordPress option instead of being
   committed in the page source.

   Fully readable with JS off, GSAP blocked, or reduced motion.
   ============================================================ */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var DATA = window.SEMZON_DATA || {};

  /* ---------------- toast ---------------- */
  var toast = $('#toast'), toastT;
  function showToast(msg) {
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastT);
    toastT = setTimeout(function () { toast.classList.remove('show'); }, 2600);
  }
  window.semzonToast = showToast;

  /* ---------------- header condense ---------------- */
  var root = document.documentElement;
  function onScroll() { root.classList.toggle('is-condensed', window.scrollY > 40); }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------------- mega menus ---------------- */
  var megaBtns = $$('.nav-t[data-mega]');
  function closeMegas(exceptPanel) {
    megaBtns.forEach(function (b) {
      var p = document.getElementById(b.getAttribute('data-mega'));
      if (p && p !== exceptPanel) {
        p.classList.remove('open');
        b.setAttribute('aria-expanded', 'false');
      }
    });
  }
  var FINE = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  var hoverT = null;
  function openMega(btn, panel) {
    clearTimeout(hoverT);
    closeMegas(panel);
    panel.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
  }
  megaBtns.forEach(function (btn) {
    var panel = document.getElementById(btn.getAttribute('data-mega'));
    if (!panel) return;
    btn.addEventListener('click', function () {
      if (FINE) {
        /* desktop: click opens & keeps open; hover-out / Esc / outside click closes */
        if (!panel.classList.contains('open')) openMega(btn, panel);
        return;
      }
      var open = panel.classList.toggle('open');
      btn.setAttribute('aria-expanded', String(open));
      if (open) closeMegas(panel);
    });
    if (FINE) {
      btn.addEventListener('mouseenter', function () { openMega(btn, panel); });
      panel.addEventListener('mouseenter', function () { clearTimeout(hoverT); });
    }
  });
  if (FINE) {
    var headerEl = document.getElementById('header');
    if (headerEl) {
      /* close timer runs ONLY when the pointer leaves the whole header,
         so moving from the button down into the panel can never close it */
      headerEl.addEventListener('mouseenter', function () { clearTimeout(hoverT); });
      headerEl.addEventListener('mouseleave', function () {
        clearTimeout(hoverT);
        hoverT = setTimeout(function () { closeMegas(); }, 240);
      });
    }
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest || !e.target.closest('.header')) closeMegas();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var openBtn = null;
    megaBtns.forEach(function (b) { if (b.getAttribute('aria-expanded') === 'true') openBtn = b; });
    if (openBtn) { closeMegas(); openBtn.focus(); }
  });

  /* ---------------- mobile drawer ---------------- */
  var drawer = $('#drawer');
  var burgerOpen = $('.header .burger');
  function setDrawer(open) {
    if (!drawer || !burgerOpen) return;
    drawer.classList.toggle('open', open);
    drawer.setAttribute('aria-hidden', String(!open));
    burgerOpen.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
      var c = $('[data-close]', drawer);
      if (c) c.focus();
    } else {
      burgerOpen.focus();
    }
  }
  if (burgerOpen) burgerOpen.addEventListener('click', function () { setDrawer(true); });
  if (drawer) {
    drawer.addEventListener('click', function (e) {
      if (e.target.closest('[data-close]')) setDrawer(false);
      else if (e.target.closest('a')) setDrawer(false);
    });
    drawer.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab' || !drawer.classList.contains('open')) return;
      var f = $$('a, button', drawer).filter(function (el) { return el.offsetParent !== null; });
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
      else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('open')) setDrawer(false);
    });
  }
  $$('.dr-t').forEach(function (t) {
    t.addEventListener('click', function () {
      var open = t.getAttribute('aria-expanded') === 'true';
      t.setAttribute('aria-expanded', String(!open));
      var mark = t.querySelector('span');
      if (mark) mark.textContent = open ? '＋' : '－';
    });
  });

  /* ---------------- FAQ ---------------- */
  $$('.faq-q').forEach(function (q) {
    q.addEventListener('click', function () {
      var open = q.getAttribute('aria-expanded') !== 'true';
      q.setAttribute('aria-expanded', String(open));
      var item = q.closest('.faq-item');
      if (item) item.classList.toggle('open', open);
    });
  });

  /* ---------------- counters (shared) ---------------- */
  function animateCount(el) {
    var end = parseInt(el.getAttribute('data-count'), 10) || 0;
    var dur = 1200, t0 = performance.now();
    function tick(t) {
      var p = Math.min((t - t0) / dur, 1);
      el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  function setCountsImmediate() {
    $$('[data-count]').forEach(function (c) { c.textContent = c.getAttribute('data-count'); });
  }

  /* ---------------- INTERACTIVE PLANT FLOW ----------------
     Stages come from the "Plant Flow" ACF options page so an
     administrator can reorder, retitle or relink them without
     touching this file. */
  var FLOW = Array.isArray(DATA.flow) ? DATA.flow : [];
  var rail = $('#flow-rail'), ft = $('#flow-title'), fx = $('#flow-text'), fm = $('#flow-mach');
  var curFlow = -1, flowProg = null, flowScrollTo = null;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function selectStage(i) {
    if (i === curFlow || !rail || !FLOW[i]) return;
    curFlow = i;
    $$('.flow-stage', rail).forEach(function (b, j) {
      b.setAttribute('aria-selected', String(j === i));
    });
    var s = FLOW[i];
    ft.textContent = s.n + ' — ' + s.t;
    fx.textContent = s.d;
    fm.innerHTML = (s.m || []).map(function (row) {
      var open = row.url ? '<a href="' + esc(row.url) + '">' : '<span class="flow-mach-static">';
      var close = row.url ? '</a>' : '</span>';
      return '<li>' + open + '<span>' + esc(row.name) +
             '</span><span class="mono">' + esc(row.spec) + '</span>' + close + '</li>';
    }).join('');
  }
  function goStage(i) {
    if (flowScrollTo) flowScrollTo(i);
    else selectStage(i);
  }
  if (rail && ft && fx && fm && FLOW.length) {
    FLOW.forEach(function (s, i) {
      var b = document.createElement('button');
      b.className = 'flow-stage';
      b.type = 'button';
      b.setAttribute('role', 'tab');
      b.setAttribute('aria-selected', 'false');
      b.setAttribute('aria-controls', 'flow-desc');
      b.id = 'flow-tab-' + i;
      b.innerHTML = '<span class="n">' + esc(s.n) + '</span><span class="t">' + esc(s.t) + '</span>';
      b.addEventListener('click', function () { goStage(i); });
      rail.appendChild(b);
    });
    flowProg = document.createElement('span');
    flowProg.className = 'flow-progress';
    rail.appendChild(flowProg);
    rail.addEventListener('keydown', function (e) {
      var n = null;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') n = (curFlow + 1) % FLOW.length;
      if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') n = (curFlow - 1 + FLOW.length) % FLOW.length;
      if (e.key === 'Home') n = 0;
      if (e.key === 'End') n = FLOW.length - 1;
      if (n !== null) { e.preventDefault(); goStage(n); document.getElementById('flow-tab-' + n).focus(); }
    });
    selectStage(0);
  }

  /* ---------------- GOOGLE MAP — Global Presence ----------------
     The API key is stored as a WordPress option and injected here,
     so it is never committed to source control. With no key set the
     styled SVG fallback stays; auth failures restore it. */
  (function initGlobalMap() {
    var cardEl = $('.gmap-card');
    var mapEl = document.getElementById('gmap');
    var key = String(DATA.mapsKey || '').trim();
    if (!cardEl || !mapEl || !key) return; /* fallback graphic remains */

    var LOCATIONS = Array.isArray(DATA.mapPins) ? DATA.mapPins : [];
    if (!LOCATIONS.length) return;

    var STYLE = [
      { elementType: 'geometry', stylers: [{ color: '#f5f4fb' }] },
      { elementType: 'labels.text.fill', stylers: [{ color: '#5a5b6e' }] },
      { elementType: 'labels.text.stroke', stylers: [{ color: '#ffffff' }] },
      { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#d5cfee' }] },
      { featureType: 'administrative.country', elementType: 'geometry.stroke', stylers: [{ color: '#8a79cc' }, { weight: 1 }] },
      { featureType: 'poi', stylers: [{ visibility: 'off' }] },
      { featureType: 'transit', stylers: [{ visibility: 'off' }] },
      { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#e9eaef' }] },
      { featureType: 'road', elementType: 'labels', stylers: [{ visibility: 'off' }] },
      { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#dcd8f0' }] },
      { featureType: 'landscape.natural', elementType: 'geometry', stylers: [{ color: '#f6f7f9' }] }
    ];
    var PIN = 'M12 0C5.4 0 0 5.4 0 12c0 8.6 12 22 12 22s12-13.4 12-22C24 5.4 18.6 0 12 0z';

    window.__semzonMapInit = function () {
      var g = window.google;
      var map = new g.maps.Map(mapEl, {
        center: { lat: 29.6, lng: 69.5 }, zoom: 5, styles: STYLE,
        disableDefaultUI: true, zoomControl: true, minZoom: 3,
        gestureHandling: 'cooperative', backgroundColor: '#f5f4fb'
      });
      var bounds = new g.maps.LatLngBounds();
      var info = new g.maps.InfoWindow();
      LOCATIONS.forEach(function (l) {
        var pos = { lat: parseFloat(l.lat), lng: parseFloat(l.lng) };
        if (isNaN(pos.lat) || isNaN(pos.lng)) return;
        var hq = l.type === 'hq';
        var marker = new g.maps.Marker({
          position: pos, map: map, title: l.name,
          icon: { path: PIN, fillColor: hq ? '#840C0C' : '#1C0863', fillOpacity: 1,
                  strokeColor: '#ffffff', strokeWeight: 1.4,
                  scale: hq ? 1.25 : 0.95, anchor: new g.maps.Point(12, 34) },
          zIndex: hq ? 10 : 1
        });
        marker.addListener('click', function () {
          info.setContent('<div style="font-family:Inter,sans-serif;max-width:230px">' +
            '<strong style="display:block;color:#14152B;font-size:14px">' + esc(l.name) + '</strong>' +
            '<span style="font-family:monospace;font-size:10.5px;letter-spacing:.08em;color:#5A5B6E">' + esc(l.cap) + '</span></div>');
          info.open({ map: map, anchor: marker });
        });
        bounds.extend(pos);
      });
      map.fitBounds(bounds, 48);
      cardEl.classList.add('live');
    };
    window.gm_authFailure = function () {
      cardEl.classList.remove('live');
      if (window.console) console.warn('Google Maps auth failed — check the key under SEMZON → Settings. Fallback graphic restored.');
    };
    var s = document.createElement('script');
    s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key) + '&callback=__semzonMapInit&loading=async';
    s.async = true;
    s.onerror = window.gm_authFailure;
    document.head.appendChild(s);
  })();

  /* ================================================================
     MOTION — GSAP + ScrollTrigger (fallback below)
     ================================================================ */
  var hasGsap = !reduced && window.gsap && window.ScrollTrigger;

  if (hasGsap) {
    gsap.registerPlugin(ScrollTrigger);

    /* ---- scroll progress ---- */
    if ($('#scroll-progress')) {
      gsap.to('#scroll-progress', {
        scaleX: 1, ease: 'none',
        scrollTrigger: { start: 0, end: 'max', scrub: 0.3 }
      });
    }

    /* ---- hero intro ---- */
    var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
    if ($('.hero-copy')) {
      tl.from('.hero-copy .eyebrow', { y: 18, autoAlpha: 0, duration: .5 })
        .from('#hero-h1 .w', { yPercent: 118, duration: .75, stagger: .07 }, '-=.25')
        .from('.hero-sub', { y: 22, autoAlpha: 0, duration: .55 }, '-=.4')
        .from('.hero-ctas > *', { y: 18, autoAlpha: 0, duration: .45, stagger: .08 }, '-=.3')
        .from('.hstats > div', { y: 16, autoAlpha: 0, duration: .45, stagger: .08 }, '-=.28')
        .from('.hm-card', { scale: .94, autoAlpha: 0, duration: .9, ease: 'power2.out' }, '-=1.0')
        .from('.hm-card img', { y: 70, autoAlpha: 0, duration: .8 }, '-=.6')
        .from('.hm-card .chip, .hm-card .hm-spec', { autoAlpha: 0, y: 10, duration: .4, stagger: .08 }, '-=.35')
        .from('.hero-rail > *', { y: 26, autoAlpha: 0, duration: .55, stagger: .1 }, '-=.7')
        .from('.scroll-cue', { autoAlpha: 0, duration: .5 }, '-=.2');
    }
    if ($('.scroll-cue i')) {
      gsap.to('.scroll-cue i', { scaleY: .18, transformOrigin: 'top', repeat: -1, yoyo: true, duration: .9, ease: 'power1.inOut' });
    }
    if ($('.hm-card .chip')) {
      gsap.to('.hm-card .chip', { y: -7, duration: 1.9, yoyo: true, repeat: -1, ease: 'sine.inOut' });
    }

    /* ---- hero tilt (fine pointers only) ---- */
    if (window.matchMedia('(hover:hover) and (pointer:fine)').matches) {
      var card = $('#hm-card');
      if (card) {
        var rx = gsap.quickTo(card, 'rotationX', { duration: .55, ease: 'power3' });
        var ry = gsap.quickTo(card, 'rotationY', { duration: .55, ease: 'power3' });
        card.addEventListener('pointermove', function (e) {
          var r = card.getBoundingClientRect();
          var px = (e.clientX - r.left) / r.width - .5;
          var py = (e.clientY - r.top) / r.height - .5;
          ry(px * 7); rx(-py * 6);
        });
        card.addEventListener('pointerleave', function () { rx(0); ry(0); });
      }
    }

    /* ---- split-word reveals for section headings ---- */
    function splitWords(el) {
      var words = el.textContent.trim().split(/\s+/);
      el.textContent = '';
      words.forEach(function (w, i) {
        var wo = document.createElement('span'); wo.className = 'wo';
        var sp = document.createElement('span'); sp.className = 'w'; sp.textContent = w;
        wo.appendChild(sp); el.appendChild(wo);
        if (i < words.length - 1) el.appendChild(document.createTextNode(' '));
      });
    }
    $$('.display-2.rv').forEach(function (h) {
      splitWords(h);
      h.classList.remove('rv');
      gsap.from(h.querySelectorAll('.w'), {
        yPercent: 112, duration: .7, ease: 'power3.out', stagger: .05,
        scrollTrigger: { trigger: h, start: 'top 86%', once: true }
      });
    });

    /* ---- media images scale-in ---- */
    var mediaImgs = $$('.card-media img, .bx figure img, .bx--wide > img');
    gsap.set(mediaImgs, { scale: 1.12 });

    /* ---- generic reveals ---- */
    var rvEls = $$('.rv');
    gsap.set(rvEls, { y: 26, autoAlpha: 0 });
    ScrollTrigger.batch(rvEls, {
      start: 'top 88%',
      once: true,
      onEnter: function (els) {
        gsap.to(els, { y: 0, autoAlpha: 1, duration: .75, ease: 'power3.out', stagger: .09 });
        els.forEach(function (el) {
          var imgs = el.querySelectorAll('.card-media img, figure img, :scope > img');
          if (imgs.length) gsap.to(imgs, { scale: 1, duration: 1.1, ease: 'power3.out' });
        });
      }
    });

    /* ---- marquee ---- */
    var track = $('#mq-track');
    if (track) {
      var mq = gsap.to(track, { xPercent: -50, ease: 'none', duration: 26, repeat: -1 });
      track.addEventListener('pointerenter', function () { mq.pause(); });
      track.addEventListener('pointerleave', function () { mq.resume(); });
    }

    /* ---- parallax ---- */
    if ($('.hm-card') && $('.hero')) {
      gsap.to('.hm-card', {
        yPercent: -5, ease: 'none',
        scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
      });
    }
    if ($('.feature-fig img')) {
      gsap.fromTo('.feature-fig img', { yPercent: -4 }, {
        yPercent: 4, ease: 'none',
        scrollTrigger: { trigger: '.feature', start: 'top bottom', end: 'bottom top', scrub: true }
      });
    }
    if ($('.f-mark')) {
      gsap.fromTo('.f-mark', { yPercent: 26 }, {
        yPercent: 0, ease: 'none',
        scrollTrigger: { trigger: '.footer', start: 'top bottom', end: 'top 40%', scrub: true }
      });
    }

    /* ---- turnkey line + dots ---- */
    if ($('.steps-wrap')) {
      var stepsTl = gsap.timeline({
        scrollTrigger: { trigger: '.steps-wrap', start: 'top 80%', end: 'bottom 58%', scrub: true }
      });
      stepsTl.to('#steps-line', { scaleX: 1, ease: 'none' }, 0)
             .from('.step .dot', { scale: .25, stagger: .18, duration: .12, ease: 'back.out(3)' }, 0)
             .to('.step .dot', { backgroundColor: '#9E1414', borderColor: '#9E1414', stagger: .18, duration: .1 }, 0);
    }

    /* ---- network arcs + hub pulse ---- */
    if ($('#arc1')) {
      gsap.to('#arc1, #arc2', { strokeDashoffset: -36, duration: 2.6, ease: 'none', repeat: -1 });
      gsap.to('#hub', { attr: { r: 9.5 }, duration: 1.3, yoyo: true, repeat: -1, ease: 'sine.inOut' });
    }

    /* ---- counters ---- */
    $$('[data-count]').forEach(function (el) {
      ScrollTrigger.create({
        trigger: el, start: 'top 88%', once: true,
        onEnter: function () { animateCount(el); }
      });
    });

    /* ---- pinned Plant-Flow scrub (desktop) ---- */
    if ($('#flow') && FLOW.length) {
      var mm = gsap.matchMedia();
      mm.add('(min-width: 1024px)', function () {
        var st = ScrollTrigger.create({
          trigger: '#flow',
          start: 'top 64px',
          end: '+=1500',
          pin: true,
          scrub: 0.5,
          onUpdate: function (self) {
            var i = Math.min(FLOW.length - 1, Math.floor(self.progress * FLOW.length));
            selectStage(i);
            if (flowProg) flowProg.style.width = (self.progress * 100) + '%';
          }
        });
        flowScrollTo = function (i) {
          var y = st.start + ((i + 0.5) / FLOW.length) * (st.end - st.start);
          window.scrollTo({ top: y, behavior: 'smooth' });
        };
        return function () {
          flowScrollTo = null;
          if (flowProg) flowProg.style.width = '0';
        };
      });
    }

    window.addEventListener('load', function () { ScrollTrigger.refresh(); });

  } else {
    /* ---- fallback: static page, counters still count ---- */
    if (reduced || !('IntersectionObserver' in window)) {
      setCountsImmediate();
    } else {
      var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { animateCount(en.target); cio.unobserve(en.target); }
        });
      }, { threshold: 0.5 });
      $$('[data-count]').forEach(function (c) { cio.observe(c); });
    }
  }

  /* ---------------- action bar auto-hide ---------------- */
  var bar = $('#action-bar');
  var sentinels = [$('#contact-band'), $('.footer')].filter(Boolean);
  if (bar && sentinels.length && 'IntersectionObserver' in window) {
    var vis = new Map();
    var bio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { vis.set(en.target, en.isIntersecting); });
      var any = false;
      vis.forEach(function (v) { if (v) any = true; });
      bar.classList.toggle('hidden', any);
    }, { threshold: 0 });
    sentinels.forEach(function (el) { bio.observe(el); });
  }
})();
