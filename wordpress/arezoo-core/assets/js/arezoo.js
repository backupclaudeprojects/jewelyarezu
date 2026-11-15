/* Arezoo Core — front-end behaviour of the Arezoo widgets and abilities. Plain JS, no dependencies.
   Every widget is initialised from its own markup (data attributes), so it also works when Elementor
   re-renders a widget in the editor. */
(function () {
  'use strict';
  var d = document, w = window;
  d.documentElement.classList.add('ay-js');
  var $ = function (s, r) { return (r || d).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
  var reduced = function () { return w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches; };
  var inEditor = function () { return !!(w.AREZOO && w.AREZOO.editor) || d.body.classList.contains('elementor-editor-active'); };
  function json(el, attr) { try { return JSON.parse(el.getAttribute(attr) || '{}') || {}; } catch (e) { return {}; } }
  function once(el, key) { if (el['_ay_' + key]) return false; el['_ay_' + key] = true; return true; }

  /* ----------------------------------------------------------- scroll engine */
  var updaters = [], ticking = false, lastScroll = 0, lastY = w.scrollY;
  function runAll() { ticking = false; for (var i = updaters.length - 1; i >= 0; i--) { if (!updaters[i].el.isConnected) updaters.splice(i, 1); else updaters[i].fn(); } }
  function onScroll(fn, el) { updaters.push({ fn: fn, el: el || d.body }); fn(); }
  function request() { if (!ticking) { ticking = true; requestAnimationFrame(runAll); } }
  w.addEventListener('scroll', function () { lastScroll = performance.now(); request(); }, { passive: true });
  w.addEventListener('resize', request);
  w.addEventListener('load', request);

  /* One observer for everything. Several watchers may share an element, and an element that hides itself
     with clip-path is watched through its parent (a fully clipped target never counts as intersecting). */
  var io = ('IntersectionObserver' in w) ? new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      var list = en.target._ayW || [];
      list.forEach(function (it) { if (it.vis) it.vis(en.isIntersecting); if (en.isIntersecting && it.once) { it.once(); it.once = null; } });
      en.target._ayW = list.filter(function (it) { return it.vis || it.once; });
      if (!en.target._ayW.length) io.unobserve(en.target);
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: .01 }) : null;
  function watch(el, vis, first, viaParent) {
    var t = (viaParent && el.parentElement) ? el.parentElement : el;
    if (!io) { if (first) first(); if (vis) vis(true); return; }
    (t._ayW = t._ayW || []).push({ vis: vis || null, once: first || null }); io.observe(t);
  }

  /* ------------------------------------------------------------------- hero */
  function heroPick(el, vs) {   /* the picture whose shape is closest to the frame */
    var a = el.clientWidth / (el.clientHeight || 1), best = 0, bd = 1e9;
    vs.forEach(function (v, i) { var q = Math.abs(Math.log(a / (v.w / v.h))) + (v.k === 'tablet' ? .15 : 0); if (q < bd) { bd = q; best = i; } });
    return best;
  }
  function heroSrc(el, v) {
    var need = Math.max(el.clientWidth / v.w, el.clientHeight / v.h) * v.w * Math.min(w.devicePixelRatio || 1, 2), i;
    for (i = 0; i < v.set.length; i++) if (v.set[i][1] >= need) return v.set[i][0];
    return v.set[v.set.length - 1][0];
  }
  function hero(el) {
    if (!w.AYHero) return;
    var cfg = json(el, 'data-ay-hero'), vs = cfg.variants;
    if (el._ayHero) { el._ayHero.destroy(); el._ayHero = null; }
    if (!vs || !vs.length) { el._ayHero = w.AYHero.mount(el, cfg); return; }
    var cur = -1, t = 0;
    function apply(i) {
      var v = vs[i], img = $('img.ay-hs-img', el), preset = el.getAttribute('data-v');
      if (el._ayHero) { el._ayHero.destroy(); el._ayHero = null; }
      if (cur < 0 && img) {   /* first time: keep the picture element that is already in the page */
        var want = heroSrc(el, v);
        if (!(img.getAttribute('src') && preset !== null && +preset === i) && img.getAttribute('src') !== want) img.src = want;
      } else {   /* the frame changed shape and another picture suits it: start from clean markup */
        el.removeAttribute('data-v'); el._ayTone = 0; el.style.backgroundColor = ''; el.classList.remove('is-live', 'is-letter-x', 'is-letter-y');
        el.innerHTML = '<img class="ay-hs-img ay-hero-media" alt="" decoding="async"><span class="ay-hero-veil" aria-hidden="true"></span><span class="ay-hero-fade" aria-hidden="true"></span>';
        img = $('img.ay-hs-img', el); img.alt = cfg.alt || ''; img.src = heroSrc(el, v);
      }
      cur = i;
      var c = {}, k; for (k in cfg) c[k] = cfg[k];
      c.img = img.getAttribute('src'); c.w = v.w; c.h = v.h; c.safe = v.safe; c.regions = v.regions; c.sparks = v.sparks; c.floats = v.k === 'wide' ? cfg.floats : [];
      el._ayHero = w.AYHero.mount(el, c);
    }
    apply(heroPick(el, vs));
    if (el._ayRO) el._ayRO.disconnect();
    if ('ResizeObserver' in w) { el._ayRO = new ResizeObserver(function () { clearTimeout(t); t = setTimeout(function () { if (!el.isConnected) return; var i = heroPick(el, vs); if (i !== cur) apply(i); }, 220); }); el._ayRO.observe(el); }
  }

  /* --------------------------------------------------- horizontal portfolio */
  function hz(el) {
    if (!once(el, 'hz')) return;
    var cfg = json(el, 'data-ay-hz'), rows = $$('.ay-hz-row', el), autoX = 0, visible = false, raf = 0, prev = 0, hover = false;
    function measure() { rows.forEach(function (r) { var n = parseInt(r.getAttribute('data-n'), 10) || 0, t = r.children; r._sw = t.length > n ? t[n].offsetLeft - t[0].offsetLeft : 0; }); }
    function update() {
      if (reduced()) { rows.forEach(function (r) { r.style.transform = ''; }); return; }
      var top = el.getBoundingClientRect().top + w.scrollY;
      var x = ((w.scrollY - top + w.innerHeight) * (cfg.mult || 0) + autoX - 200) * (cfg.dir || 1);
      rows.forEach(function (r, i) { var sw = r._sw; if (!sw) return; var v = ((((cfg.alt && i % 2) ? -x : x) % sw) + sw) % sw; r.style.transform = 'translate3d(' + (v - sw).toFixed(1) + 'px,0,0)'; });
    }
    /* after the visitor stops scrolling for the chosen number of seconds, the rows keep moving on their own */
    function loop() {
      if (raf || !cfg.auto || reduced()) return;
      prev = performance.now();
      var step = function (now) {
        raf = 0; if (!visible || !el.isConnected) return;
        var dt = Math.min(now - prev, 80) / 1000; prev = now;
        if (!(hover && cfg.hover) && now - lastScroll > (cfg.delay || 0) * 1000) { autoX += (cfg.speed || 60) * dt; update(); }
        raf = requestAnimationFrame(step);
      };
      raf = requestAnimationFrame(step);
    }
    el.addEventListener('pointerenter', function () { hover = true; }); el.addEventListener('pointerleave', function () { hover = false; });
    measure(); onScroll(function () { if (!rows[0]._sw) measure(); update(); }, el);
    w.addEventListener('resize', measure); w.addEventListener('load', measure);
    $$('img', el).forEach(function (im) { if (!im.complete) im.addEventListener('load', measure, { once: true }); });
    watch(el, function (v) { visible = v; if (v) loop(); });
  }

  /* ---------------------------------------------------------- stacked cards */
  function stack(el) {
    if (!once(el, 'stack')) return;
    var cfg = json(el, 'data-ay-stack'), cards = $$('.ay-st-card', el), wrap = el.closest('.elementor-widget') || el.parentNode;
    onScroll(function () {
      var on = wrap.classList.contains('ay-stack-on-yes') && !reduced();
      if (!on) { cards.forEach(function (c) { c.style.transform = ''; }); return; }
      var cs = getComputedStyle(wrap), base = parseFloat(cs.getPropertyValue('--ay-st-top')) || 128, gap = parseFloat(cs.getPropertyValue('--ay-st-gap')); if (isNaN(gap)) gap = 28;
      var span = w.innerHeight * (cfg.range || .85), tops = cards.map(function (c) { return c.getBoundingClientRect().top; });
      cards.forEach(function (c, i) {
        var k = 0, j;
        for (j = i + 1; j < cards.length; j++) k += clamp(1 - (tops[j] - (base + j * gap)) / span, 0, 1);
        c.style.transform = 'scale(' + Math.max(cfg.min || .5, 1 - k * (cfg.step || 0)).toFixed(4) + ')';   /* never below the minimum scale */
      });
    }, el);
  }

  /* ---------------------------------------------------------------- archive */
  function archive(el) {
    if (!once(el, 'archive')) return;
    var cfg = json(el, 'data-ay-archive'), state = { col: 'all', type: 'all' };
    $$('.ay-fi.is-on', el).forEach(function (b) { state[b.getAttribute('data-g')] = b.getAttribute('data-v'); });
    function apply() {
      var n = 0;
      $$('.ay-card', el).forEach(function (c) {
        var ok = ['col', 'type'].every(function (g) { return state[g] === 'all' || (' ' + (c.getAttribute('data-' + g) || '') + ' ').indexOf(' ' + state[g] + ' ') > -1; });
        c.hidden = !ok; if (ok) { c.style.setProperty('--n', n); n++; }
      });
      var cnt = $('.ay-ar-count', el); if (cnt) cnt.textContent = n + ' ' + (n === 1 ? (cfg.one || 'piece') : (cfg.many || 'pieces'));
      var none = $('.ay-ar-none', el); if (none) none.hidden = n > 0;
    }
    el.addEventListener('click', function (e) {
      var b = e.target.closest('.ay-fi'); if (!b || !el.contains(b)) return;
      var g = b.getAttribute('data-g'); state[g] = b.getAttribute('data-v');
      $$('.ay-fi[data-g="' + g + '"]', el).forEach(function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on); });
      $$('.ay-card', el).forEach(function (c) { c.style.animation = 'none'; void c.offsetWidth; c.style.animation = ''; });
      apply(); marks(true);
    });
    /* glass capsule / glass box: the marker glides to the chosen category */
    function marks(move) {
      $$('.ay-fl-items', el).forEach(function (box) {
        var ind = $('.ay-fl-ind', box), on = $('.ay-fi.is-on', box); if (!ind || !on) return;
        box.style.setProperty('--il', on.offsetLeft + 'px'); box.style.setProperty('--iw', on.offsetWidth + 'px');
        if (!box.classList.contains('is-ready')) { box.classList.add('is-ready', 'is-first'); setTimeout(function () { box.classList.remove('is-first'); }, 60); }
        if (move) { ind.classList.remove('is-move'); void ind.offsetWidth; ind.classList.add('is-move'); if (box.scrollWidth > box.clientWidth) box.scrollTo({ left: on.offsetLeft - (box.clientWidth - on.offsetWidth) / 2, behavior: reduced() ? 'auto' : 'smooth' }); }
      });
    }
    marks(false); w.addEventListener('resize', function () { marks(false); }); w.addEventListener('load', function () { marks(false); });
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(function () { marks(false); });
    apply();
  }

  /* ---------------------------------------------------------------- gallery */
  function gallery(el) {
    if (!once(el, 'gal')) return;
    el.addEventListener('click', function (e) {
      var b = e.target.closest('.ay-wg-thumb'); if (!b) return;
      var k = b.getAttribute('data-k');
      $$('.ay-wg-thumb', el).forEach(function (x) { x.classList.toggle('is-on', x === b); });
      $$('.ay-wg-slide', el).forEach(function (s) { s.classList.toggle('is-on', s.getAttribute('data-k') === k); });
    });
  }

  /* -------------------------------------------------------------------- FAQ */
  function faq(el) {
    if (!once(el, 'faq')) return;
    var single = el.getAttribute('data-ay-faq') === '1';
    el.addEventListener('click', function (e) {
      var b = e.target.closest('.ay-fq-topic'); if (!b) return;
      var g = b.getAttribute('aria-controls');
      $$('.ay-fq-topic', el).forEach(function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on); });
      $$('.ay-fq-group', el).forEach(function (x) { x.classList.toggle('is-on', x.id === g); });
    });
    el.addEventListener('toggle', function (e) {   /* one open answer at a time, also where <details name> is not supported */
      var t = e.target; if (!single || !t.open || !t.classList || !t.classList.contains('ay-fq-item')) return;
      $$('.ay-fq-item[open]', el).forEach(function (o) { if (o !== t) o.open = false; });
    }, true);
    var input = $('.ay-fq-search input', el);
    if (input) input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase(), any = false;
      el.classList.toggle('is-searching', !!q);
      $$('.ay-fq-group', el).forEach(function (g) {
        var hit = 0;
        $$('.ay-fq-item', g).forEach(function (it) { var m = !q || it.textContent.toLowerCase().indexOf(q) > -1; if (m) { it.removeAttribute('data-miss'); hit++; } else it.setAttribute('data-miss', ''); });
        if (hit) { g.removeAttribute('data-miss'); any = true; } else g.setAttribute('data-miss', '');
      });
      var none = $('.ay-fq-none', el); if (none) none.hidden = any;
    });
  }

  /* --------------------------------------------------------------- timeline */
  function timeline(el) {
    if (!once(el, 'tl')) return;
    var cfg = json(el, 'data-ay-timeline'), items = $$('.ay-tl-item', el);
    onScroll(function () {
      var r = el.getBoundingClientRect(), line = w.innerHeight * (cfg.at || .62), still = reduced() || !cfg.fill;
      el.style.setProperty('--ay-tl-p', still ? 1 : clamp((line - r.top - 8) / Math.max(r.height - 16, 1), 0, 1).toFixed(4));
      items.forEach(function (it) { it.classList.toggle('is-on', still || it.getBoundingClientRect().top + 16 < line); });
    }, el);
  }
  function timelineBox(el) {   /* any container marked as a timeline */
    if (!once(el, 'tlb')) return;
    onScroll(function () {
      var inner = $(':scope > .e-con-inner', el) || el, kids = $$(':scope > .e-con', inner);
      var at = parseFloat(getComputedStyle(el).getPropertyValue('--ay-tl-at')) || 62, r = el.getBoundingClientRect(), line = w.innerHeight * at / 100, still = reduced();
      el.style.setProperty('--ay-tl-p', still ? 1 : clamp((line - r.top - 10) / Math.max(r.height - 20, 1), 0, 1).toFixed(4));
      kids.forEach(function (k) { k.classList.toggle('ay-tl-on', still || k.getBoundingClientRect().top + 18 < line); });
    }, el);
  }

  /* ---------------------------------------------------------- design motion */
  function wire(canvas) {   /* a turning 3D solitaire: shaded band, prongs and a faceted stone on a studio floor, with a light circling it */
    if (!once(canvas, 'wire')) return;
    var ctx = canvas.getContext('2d'), host = canvas.closest('.ay-mo'), t = 0, raf = 0, last = 0, i, j;
    var R = 60, r = 8.5, N = 40, M = 10, CY = 20, ring = [], faces = [], TAU = 6.2832;
    for (i = 0; i < N; i++) { var row = []; for (j = 0; j < M; j++) { var a = i / N * TAU, b = j / M * TAU; row.push({ p: [(R + r * Math.cos(b)) * Math.cos(a), (R + r * Math.cos(b)) * Math.sin(a) + CY, r * 1.7 * Math.sin(b)], n: [Math.cos(b) * Math.cos(a), Math.cos(b) * Math.sin(a), Math.sin(b)] }); } ring.push(row); }
    for (i = 0; i < N; i++) for (j = 0; j < M; j++) faces.push([i, j]);
    /* stone: table, crown, girdle, pavilion */
    var GY = CY - R - 16, tab = [], gir = [], cul = [0, GY + 30, 0];
    for (i = 0; i < 8; i++) { var an = i / 8 * TAU + .39; tab.push([Math.cos(an) * 12, GY - 11, Math.sin(an) * 12]); gir.push([Math.cos(an - .39) * 23, GY, Math.sin(an - .39) * 23]); }
    var gem = [];
    for (i = 0; i < 8; i++) { var n8 = (i + 1) % 8; gem.push([tab[i], gir[n8], tab[n8]], [tab[i], gir[i], gir[n8]], [gir[i], cul, gir[n8]]); }
    var prongs = []; for (i = 0; i < 4; i++) { var pa = i / 4 * TAU + .785; prongs.push([[Math.cos(pa) * 9, CY - R + 4, Math.sin(pa) * 9], [Math.cos(pa) * 25, GY - 3, Math.sin(pa) * 25]]); }
    var ry = 0, rx = 0, cy_, sy_, cx_, sx_;
    function rot(p) { var x = p[0] * cy_ + p[2] * sy_, z = -p[0] * sy_ + p[2] * cy_, y = p[1] * cx_ - z * sx_; return [x, y, p[1] * sx_ + z * cx_]; }
    function scr(q) { var s = 420 / (420 + q[2]); return [200 + q[0] * s * 1.75, 196 + q[1] * s * 1.75, q[2]]; }
    function proj(p) { return scr(rot(p)); }
    function poly(pts) { ctx.beginPath(); ctx.moveTo(pts[0][0], pts[0][1]); for (var k = 1; k < pts.length; k++) ctx.lineTo(pts[k][0], pts[k][1]); ctx.closePath(); }
    function seg(a, b) { ctx.beginPath(); ctx.moveTo(a[0], a[1]); ctx.lineTo(b[0], b[1]); ctx.stroke(); }
    function frame(now) {
      raf = 0; if (!canvas.isConnected) return;
      var cs = getComputedStyle(host), spd = parseFloat(cs.getPropertyValue('--m-spd')) || 1, playing = host.classList.contains('is-play') && !reduced();
      var dt = Math.min(now - last, 80) / 1000; last = now; if (playing) t += dt * spd;
      var lineC = cs.getPropertyValue('--m-line').trim() || '#ddd', acc = cs.getPropertyValue('--m-accent').trim() || '#9be', fillC = cs.getPropertyValue('--m-fill').trim() || '#445';
      ry = t * .6; rx = .5 + Math.sin(t * .33) * .12; cy_ = Math.cos(ry); sy_ = Math.sin(ry); cx_ = Math.cos(rx); sx_ = Math.sin(rx);
      var solid = (Math.sin(t * .45 - 1.2) + 1) / 2, la = t * 1.3, L = [Math.cos(la) * .7, -.55, Math.sin(la) * .7 - .3], ll = Math.hypot(L[0], L[1], L[2]); L = [L[0] / ll, L[1] / ll, L[2] / ll];
      ctx.clearRect(0, 0, 400, 400); ctx.lineJoin = 'round'; ctx.lineCap = 'round';
      /* studio floor: rings and spokes fading with distance */
      var FY = CY + R + 22; ctx.strokeStyle = lineC; ctx.lineWidth = .8;
      [46, 86, 126].forEach(function (rad, k) { ctx.globalAlpha = .26 - k * .07; ctx.beginPath(); for (i = 0; i <= 48; i++) { var q = proj([Math.cos(i / 48 * TAU) * rad, FY, Math.sin(i / 48 * TAU) * rad]); if (i) ctx.lineTo(q[0], q[1]); else ctx.moveTo(q[0], q[1]); } ctx.stroke(); });
      ctx.globalAlpha = .12; for (i = 0; i < 12; i++) seg(proj([Math.cos(i / 12 * TAU) * 20, FY, Math.sin(i / 12 * TAU) * 20]), proj([Math.cos(i / 12 * TAU) * 126, FY, Math.sin(i / 12 * TAU) * 126]));
      /* soft shadow under the ring */
      var sh = proj([0, FY, 0]), g = ctx.createRadialGradient(sh[0], sh[1], 2, sh[0], sh[1], 90); g.addColorStop(0, 'rgba(0,0,0,.34)'); g.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.globalAlpha = .5 + solid * .5; ctx.fillStyle = g; ctx.save(); ctx.translate(sh[0], sh[1]); ctx.scale(1, Math.max(.18, Math.abs(sx_) * .8)); ctx.translate(-sh[0], -sh[1]); ctx.beginPath(); ctx.arc(sh[0], sh[1], 90, 0, TAU); ctx.fill(); ctx.restore();
      /* band: faces sorted back to front, lit by the moving light */
      var P = ring.map(function (row) { return row.map(function (v) { return { s: proj(v.p), n: rot(v.n) }; }); });
      var order = faces.map(function (f) { var v = P[f[0]][f[1]]; return [f[0], f[1], v.s[2]]; }).sort(function (a, b) { return b[2] - a[2]; });
      order.forEach(function (f) {
        var i0 = f[0], j0 = f[1], v = P[i0][j0], q = P[(i0 + 1) % N][j0].s, u = P[(i0 + 1) % N][(j0 + 1) % M].s, w2 = P[i0][(j0 + 1) % M].s;
        if (v.n[2] > .25) return;   /* facing away */
        var lit = clamp(v.n[0] * L[0] + v.n[1] * L[1] + v.n[2] * L[2], 0, 1), spec = Math.pow(lit, 14);
        poly([v.s, q, u, w2]);
        if (solid > .03) { ctx.globalAlpha = solid * (.28 + lit * .6); ctx.fillStyle = lit > .55 ? acc : fillC; ctx.fill(); if (spec > .05) { ctx.globalAlpha = solid * spec * .9; ctx.fillStyle = '#fff'; ctx.fill(); } }
        ctx.globalAlpha = (1 - solid * .85) * (.25 + lit * .55); ctx.strokeStyle = lineC; ctx.lineWidth = .7; ctx.stroke();
      });
      /* prongs */
      ctx.strokeStyle = lineC; ctx.lineWidth = 2.2; ctx.globalAlpha = .85; prongs.forEach(function (pr) { seg(proj(pr[0]), proj(pr[1])); });
      /* stone: every facet flashes as it turns towards the light */
      gem.map(function (tri) { var pp = tri.map(proj), z = (pp[0][2] + pp[1][2] + pp[2][2]) / 3; return { p: pp, z: z, t: tri }; }).sort(function (a, b) { return b.z - a.z; }).forEach(function (f) {
        var A = rot(f.t[0]), B = rot(f.t[1]), C = rot(f.t[2]), ux = B[0] - A[0], uy = B[1] - A[1], uz = B[2] - A[2], vx = C[0] - A[0], vy = C[1] - A[1], vz = C[2] - A[2];
        var nx = uy * vz - uz * vy, ny = uz * vx - ux * vz, nz = ux * vy - uy * vx, nl = Math.hypot(nx, ny, nz) || 1, dot = Math.abs((nx * L[0] + ny * L[1] + nz * L[2]) / nl), flash = Math.pow(dot, 5);
        poly(f.p); ctx.globalAlpha = .16 + dot * .34; ctx.fillStyle = acc; ctx.fill(); ctx.globalAlpha = flash * .95; ctx.fillStyle = '#fff'; ctx.fill();
        ctx.globalAlpha = .75; ctx.strokeStyle = lineC; ctx.lineWidth = .8; ctx.stroke();
      });
      /* the circling light and its glint on the stone */
      var lp = proj([Math.cos(la) * 118, GY - 20, Math.sin(la) * 118]), lg = ctx.createRadialGradient(lp[0], lp[1], 0, lp[0], lp[1], 26); lg.addColorStop(0, 'rgba(255,255,255,.95)'); lg.addColorStop(.25, acc); lg.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.globalAlpha = lp[2] > 60 ? .35 : .9; ctx.fillStyle = lg; ctx.beginPath(); ctx.arc(lp[0], lp[1], 26, 0, TAU); ctx.fill();
      var tw = (Math.sin(t * 2.4) + 1) / 2, c = proj([8, GY - 11, 6]); ctx.save(); ctx.translate(c[0], c[1]); ctx.rotate(t * .8); ctx.globalAlpha = tw; ctx.fillStyle = '#fff'; ctx.beginPath(); for (i = 0; i < 8; i++) { var rr = i % 2 ? 2.2 : 15 * tw + 3, aa = i / 8 * TAU; ctx.lineTo(Math.cos(aa) * rr, Math.sin(aa) * rr); } ctx.closePath(); ctx.fill(); ctx.restore();
      /* viewport marks: corner brackets and an axis gizmo */
      ctx.globalAlpha = .5; ctx.strokeStyle = lineC; ctx.lineWidth = 1.2;
      [[22, 22, 1, 1], [378, 22, -1, 1], [22, 378, 1, -1], [378, 378, -1, -1]].forEach(function (k) { ctx.beginPath(); ctx.moveTo(k[0] + 18 * k[2], k[1]); ctx.lineTo(k[0], k[1]); ctx.lineTo(k[0], k[1] + 18 * k[3]); ctx.stroke(); });
      var o = [52, 344]; [[1, 0, 0, acc], [0, -1, 0, lineC], [0, 0, 1, lineC]].forEach(function (ax, k) { var q = rot([ax[0] * 22, ax[1] * 22, ax[2] * 22]); ctx.globalAlpha = k ? .55 : .95; ctx.strokeStyle = ax[3]; ctx.lineWidth = 1.6; seg(o, [o[0] + q[0], o[1] + q[1]]); ctx.fillStyle = ax[3]; ctx.beginPath(); ctx.arc(o[0] + q[0], o[1] + q[1], 2.4, 0, TAU); ctx.fill(); });
      ctx.globalAlpha = 1;
      if (playing) raf = requestAnimationFrame(frame);
    }
    canvas._ayKick = function () { if (!raf) { last = performance.now(); raf = requestAnimationFrame(frame); } };
    canvas._ayKick();
  }
  function motion(el) {
    if (!once(el, 'mo')) return;
    var mode = el.getAttribute('data-ay-motion'), cv = $('.ay-mo-wire', el);
    if (cv) wire(cv);
    var kick = function () { if (cv && cv._ayKick) cv._ayKick(); };
    if (mode === 'always') { el.classList.add('is-play'); kick(); }
    else if (mode === 'hover') { el.addEventListener('pointerenter', function () { el.classList.add('is-play'); kick(); }); el.addEventListener('pointerleave', function () { el.classList.remove('is-play'); }); }
    else watch(el, function (v) { el.classList.toggle('is-play', v); if (v) kick(); });
  }

  /* Keep a block pinned while its column scrolls. CSS sticky does the work; if an ancestor blocks it
     (overflow hidden from another plugin or theme) the block is moved with a transform instead. */
  function pin(stick, media, on, varName, fallback) {
    if (!stick || !media) return;
    if (!on) { stick.classList.remove('is-js'); stick.style.transform = ''; return; }
    var top = parseFloat(getComputedStyle(stick).getPropertyValue(varName)) || fallback, mr = media.getBoundingClientRect(), want = clamp(top - mr.top, 0, Math.max(mr.height - stick.offsetHeight, 0));
    if (!stick.classList.contains('is-js') && want > 2 && Math.abs((stick.getBoundingClientRect().top - mr.top) - want) > 3) stick.classList.add('is-js');
    if (stick.classList.contains('is-js')) stick.style.transform = 'translate3d(0,' + want.toFixed(1) + 'px,0)';
  }

  /* --------------------------------------------------------- design process */
  function process(el) {
    if (!once(el, 'pr')) return;
    var cfg = json(el, 'data-ay-process'), steps = $$('.ay-pr-step', el), shots = $$('.ay-pr-shot', el), frame = $('.ay-pr-frame', el), pen = $('.ay-pr-pen', el), cur = -1;
    function go(i) {
      if (i === cur || i < 0) return;
      var prev = cur; cur = i;
      steps.forEach(function (s, k) { s.classList.toggle('is-on', k === i); });
      shots.forEach(function (s, k) { s.classList.toggle('was-on', k === prev); s.classList.toggle('is-on', k === i); });
      $$('.ay-pr-chip', el).forEach(function (c) { var k = +c.getAttribute('data-i'); c.classList.toggle('is-on', k === i); c.classList.toggle('is-done', k < i); });
      $$('.ay-pr-badge', el).forEach(function (b) { var on = +b.getAttribute('data-i') === i; b.classList.toggle('is-on', on); b.classList.toggle('is-play', on); var cv = $('.ay-mo-wire', b); if (cv) { wire(cv); if (on && cv._ayKick) cv._ayKick(); } });
      var num = $('.ay-pr-cur', el); if (num) num.textContent = ('0' + (i + 1)).slice(-2);
      el.style.setProperty('--ay-pr-p', steps.length > 1 ? (i / (steps.length - 1)).toFixed(3) : 1);
      if (pen && shots[i]) { var cs = getComputedStyle(shots[i]); pen.style.setProperty('--pen-x', cs.getPropertyValue('--px') || '60%'); pen.style.setProperty('--pen-y', cs.getPropertyValue('--py') || '50%'); }
    }
    go(0);
    onScroll(function () {
      var best = 0, bd = 1e9, media = $('.ay-pr-media', el), stacked = media && getComputedStyle(el).gridTemplateColumns.split(' ').length < 2;
      if (stacked) {   /* phones: the frame sits on top, so the active step is the one just under it */
        var line = media.getBoundingClientRect().bottom + 90;
        steps.forEach(function (s, k) { if (s.getBoundingClientRect().top <= line) best = k; });
      } else {
        var mid = w.innerHeight * .55;
        steps.forEach(function (s, k) { var r = s.getBoundingClientRect(), c = r.top + r.height / 2, dist = Math.abs(c - mid); if (dist < bd) { bd = dist; best = k; } });
      }
      go(best);
      pin($('.ay-pr-stick', el), media, !stacked && (el.closest('.elementor-widget') || el).classList.contains('ay-pr-sticky-yes'), '--ay-pr-top', 110);
      if (cfg.tilt && frame && !reduced()) { var r = el.getBoundingClientRect(), p = clamp((w.innerHeight / 2 - r.top) / Math.max(r.height, 1), 0, 1); frame.style.setProperty('--ay-pr-ry', ((p - .5) * -9).toFixed(2) + 'deg'); frame.style.setProperty('--ay-pr-rx', ((.5 - p) * 3).toFixed(2) + 'deg'); }
    }, el);
  }

  /* ----------------------------------------------------- sketch ↔ jewel */
  function compare(el) {
    if (!once(el, 'cp')) return;
    var cfg = json(el, 'data-ay-compare'), range = $('.ay-cp-range', el), auto = cfg.mode === 'auto' && !reduced(), raf = 0, t0 = 0, held = false, visible = false;
    function set(v) { v = clamp(v, 0, 100); el.style.setProperty('--cp', v + '%'); if (range && +range.value !== v) range.value = v; }
    set(cfg.start == null ? 50 : cfg.start);
    if (range) {
      range.addEventListener('input', function () { held = true; set(+range.value); });
      range.addEventListener('pointerdown', function () { held = true; });
      var release = function () { setTimeout(function () { held = false; t0 = 0; }, 2400); };
      range.addEventListener('pointerup', release); range.addEventListener('pointerleave', function () { if (held) release(); });
    }
    if (cfg.mode === 'scroll') { onScroll(function () { var r = el.getBoundingClientRect(); set(clamp((w.innerHeight - r.top) / (w.innerHeight + r.height), 0, 1) * 100); }, el); return; }
    if (!auto) return;
    var base = cfg.start == null ? 50 : cfg.start;
    function step(now) {
      raf = 0; if (!visible || !el.isConnected) return;
      if (!held) { if (!t0) t0 = now; set(50 + Math.sin((now - t0) / 1000 / (cfg.dur || 7) * 6.2832 + Math.asin(clamp((base - 50) / 38, -1, 1))) * 38); }
      raf = requestAnimationFrame(step);
    }
    watch(el, function (v) { visible = v; if (v && !raf) raf = requestAnimationFrame(step); });
  }

  /* ------------------------------------------------------- 3D tilt (optional) */
  function tilt(el) {
    if (!once(el, 'tilt')) return;
    var touchOk = el.classList.contains('ay-depth-touch-yes');
    el.addEventListener('pointermove', function (e) {
      if (reduced() || !el.classList.contains('ay-depth-tilt') || (e.pointerType === 'touch' && !touchOk)) return;
      var r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height, max = parseFloat(getComputedStyle(el).getPropertyValue('--ay-d-angle')); if (isNaN(max)) max = 12;
      el.classList.add('is-live');
      el.style.setProperty('--ry', ((x - .5) * max * 2).toFixed(2) + 'deg'); el.style.setProperty('--rx', ((.5 - y) * max * 2).toFixed(2) + 'deg');
      el.style.setProperty('--gx', (x * 100).toFixed(1) + '%'); el.style.setProperty('--gy', (y * 100).toFixed(1) + '%'); el.style.setProperty('--go', '1');
    });
    el.addEventListener('pointerleave', function () { el.classList.remove('is-live'); ['--rx', '--ry', '--gx', '--gy', '--go'].forEach(function (k) { el.style.removeProperty(k); }); });
  }

  /* -------------------------------------------------- entrance, dock, floats */
  function reveal(el) {
    if (!once(el, 'rv')) return;
    if (inEditor() || reduced()) { el.classList.add('ay-in'); return; }
    watch(el, null, function () { el.classList.add('ay-in'); }, /ay-rv-(wipe|ink)/.test(el.className));
  }
  function dock(el) {
    if (!once(el, 'dock')) return;
    var cfg = json(el, 'data-ay-dock'), body = d.body, phone = w.matchMedia('(max-width: 767px)');
    var clear = function () { body.classList.remove('ay-swap', 'ay-swap-morph', 'ay-swap-rise', 'ay-swap-blur', 'ay-swap-fade'); };
    onScroll(function () {
      if (inEditor()) { el.classList.remove('is-away', 'is-pre'); clear(); return; }
      var f = $('.ay-part-footer') || $('.elementor-location-footer') || $('footer'), away = !!(cfg.foot && f && f.getBoundingClientRect().top < w.innerHeight - 90);
      var menu = cfg.mm && phone.matches, pre = cfg.desk === 'scroll' && !menu && w.scrollY < (cfg.after || 0);
      el.classList.toggle('is-away', away); el.classList.toggle('is-pre', pre);
      var swap = cfg.swap && cfg.desk === 'scroll' && !menu && !pre && !away;
      if (swap) body.classList.add('ay-swap', 'ay-swap-' + (cfg.fx || 'morph')); else clear();
    }, el);
  }
  function floatBar(el) {
    if (!once(el, 'float')) return;
    onScroll(function () {
      if (inEditor()) { el.classList.remove('is-away'); return; }
      if (el.classList.contains('ay-float-hide-yes')) { var f = $('.ay-part-footer') || $('footer'); if (f) el.classList.toggle('is-away', f.getBoundingClientRect().top < w.innerHeight - 90); }
      if (el.classList.contains('ay-float-smart-yes')) { var y = w.scrollY; el.classList.toggle('is-away', y > lastY && y > 240); lastY = y; }
      el.classList.toggle('is-scrolled', w.scrollY > 40);
    }, el);
  }
  d.addEventListener('click', function (e) {
    var top = e.target.closest('.ay-totop'); if (top) { w.scrollTo({ top: 0, behavior: reduced() ? 'auto' : 'smooth' }); return; }
    var cp = e.target.closest('[data-ay-copy]'); if (!cp) return;
    var txt = cp.getAttribute('data-ay-copy'), label = cp.textContent, done = function () { cp.textContent = cp.getAttribute('data-done') || 'Copied'; setTimeout(function () { cp.textContent = label; }, 1800); };
    var fallback = function () { var val = cp.parentNode.querySelector('.ay-ct-val'); if (!val) return; var r = d.createRange(); r.selectNodeContents(val); var s = w.getSelection(); s.removeAllRanges(); s.addRange(r); };
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done, fallback); else fallback();
  });

  /* ------------------------------------------------- site-wide sketch wallpaper */
  function wallpaper() {
    var c = w.AREZOO && w.AREZOO.wallpaper; if (!c || !c.urls || !c.urls.length) return;
    if (!c.mobile && w.matchMedia('(max-width: 767px)').matches) return;
    var box = $('.ay-wall'); if (!box) { box = d.createElement('div'); box.className = 'ay-wall'; box.setAttribute('aria-hidden', 'true'); d.body.insertBefore(box, d.body.firstChild); d.body.classList.add('ay-has-wall'); }
    box.style.setProperty('--ay-wall-op', c.opacity); box.style.setProperty('--ay-wall-blur', (c.blur || 0) + 'px'); if (c.color) box.style.setProperty('--ay-wall-color', c.color);
    var seed = 7, rnd = function () { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; }, lastH = -1, lastW = -1;
    function layout() {
      var H = Math.max(d.documentElement.scrollHeight, d.body.scrollHeight), W = d.documentElement.clientWidth;
      if (Math.abs(H - lastH) < 60 && W === lastW) return;
      lastH = H; lastW = W; seed = 7; box.style.height = H + 'px';
      var phone = W < 768, base = phone ? Math.min(c.size * .62, W * .78) : Math.min(c.size, W * .5), gap = phone ? c.spacing * .8 : c.spacing, k = c.density || 1;
      var slots = k > 1.6 && !phone ? ['right', 'left', 'center'] : ['right', 'left'], big = 1, i, h = '';
      if (c.scales) c.scales.forEach(function (v) { if (v > big) big = v; });
      var per = phone || c.side !== 'alt' ? 1 : slots.length, cap = Math.max(1, Math.floor(H / (base * big * 1.25 * 1.12 / per)));   /* room for each design, so none lands on another */
      var n = Math.min(cap, Math.max(1, Math.round(H / gap * k))), rowH = H / n;
      for (i = 0; i < n; i++) {
        var size = base * ((c.scales && c.scales[i % c.urls.length]) || 1), side = c.side === 'alt' ? slots[i % slots.length] : c.side, x;
        if (side === 'left') x = -size * .22 + rnd() * W * .06; else if (side === 'right') x = W - size * .78 - rnd() * W * .06; else x = (W - size) / 2 + (rnd() - .5) * W * .2;
        var y = i * rowH + rnd() * Math.max(0, rowH - size * 1.25) * (per > 1 ? 1 : .6), rot = (rnd() - .5) * 2 * c.rotate;
        h += '<i data-y="' + y.toFixed(0) + '" style="left:' + x.toFixed(0) + 'px;top:' + y.toFixed(0) + 'px;width:' + size.toFixed(0) + 'px;height:' + (size * 1.25).toFixed(0) + 'px;--img:url(' + c.urls[i % c.urls.length] + ');--r:' + rot.toFixed(1) + 'deg;transform:rotate(' + rot.toFixed(1) + 'deg)"></i>';
      }
      box.innerHTML = h; drift();
    }
    function drift() {
      if (!c.parallax || reduced()) return;
      var y = w.scrollY, vh = w.innerHeight;
      $$('i', box).forEach(function (m) { var top = +m.getAttribute('data-y'); if (top > y - vh && top < y + vh * 2) m.style.transform = 'translate3d(0,' + ((y - top) * c.parallax).toFixed(1) + 'px,0) rotate(' + m.style.getPropertyValue('--r') + ')'; });
    }
    layout(); onScroll(drift, box);
    w.addEventListener('resize', layout); w.addEventListener('load', layout);
    if ('ResizeObserver' in w) new ResizeObserver(layout).observe(d.body);
  }

  /* ------------------------------------------ cards: tilt, depth, glare, glow */
  d.addEventListener('pointermove', function (e) {
    var pic = e.target && e.target.closest ? e.target.closest('.ay-card-pic') : null;
    if (cardLive && cardLive !== pic) cardOff();
    if (!pic || reduced()) return;
    var host = pic.closest('.ay-ct-yes, .ay-cd-yes, .ay-cg-yes, .ay-cgl-yes'); if (!host) return;
    if (e.pointerType === 'touch' && !host.classList.contains('ay-ct-touch-yes')) return;
    var r = pic.getBoundingClientRect(), x = clamp((e.clientX - r.left) / r.width, 0, 1), y = clamp((e.clientY - r.top) / r.height, 0, 1);
    if (cardLive !== pic) { cardLive = pic; cardMax = parseFloat(getComputedStyle(pic).getPropertyValue('--ay-ct-angle')); if (isNaN(cardMax)) cardMax = 10; }
    pic.classList.add('is-live');
    pic.style.setProperty('--ry', ((x - .5) * cardMax * 2).toFixed(2) + 'deg'); pic.style.setProperty('--rx', ((.5 - y) * cardMax * 2).toFixed(2) + 'deg');
    pic.style.setProperty('--gx', (x * 100).toFixed(1) + '%'); pic.style.setProperty('--gy', (y * 100).toFixed(1) + '%');
    pic.style.setProperty('--mx', ((x - .5) * 2).toFixed(3)); pic.style.setProperty('--my', ((y - .5) * 2).toFixed(3));
  }, { passive: true });
  var cardLive = null, cardMax = 10;
  function cardOff() { if (!cardLive) return; cardLive.classList.remove('is-live'); ['--rx', '--ry', '--mx', '--my'].forEach(function (k) { cardLive.style.removeProperty(k); }); cardLive = null; }
  d.addEventListener('pointerleave', cardOff); w.addEventListener('blur', cardOff);

  /* ------------------------------------------------ more abilities (Advanced) */
  function layerIn(el, cls) {   /* a helper layer as the first child; put back if Elementor re-rendered the element */
    var has = false, c = el.children, i; for (i = 0; i < c.length; i++) if (c[i].classList.contains(cls)) has = true;
    if (!has) { var n = d.createElement('i'); n.className = cls; n.setAttribute('aria-hidden', 'true'); el.insertBefore(n, el.firstChild); }
  }
  function halo(el) {
    layerIn(el, 'ay-halo-l');
    if (!once(el, 'halo')) return;
    el.addEventListener('pointermove', function (e) {
      if (!el.classList.contains('ay-halo-follow-yes') || reduced() || e.pointerType === 'touch') return;
      var r = el.getBoundingClientRect(); el.style.setProperty('--hx', (e.clientX - r.left - r.width / 2).toFixed(0) + 'px'); el.style.setProperty('--hy', (e.clientY - r.top - r.height / 2).toFixed(0) + 'px');
    }, { passive: true });
    el.addEventListener('pointerleave', function () { el.style.removeProperty('--hx'); el.style.removeProperty('--hy'); });
  }
  function shine(el) {
    layerIn(el, 'ay-shine-l');
    if (!once(el, 'shine')) return;
    if (el.classList.contains('ay-shine-view')) watch(el, null, function () { el.classList.add('ay-sh-go'); });
  }
  function progressLine(el) {
    layerIn(el, 'ay-pb-l');
    if (!once(el, 'pb')) return;
    onScroll(function () {
      var p;
      if (el.classList.contains('ay-pb-src-self')) { var r = el.getBoundingClientRect(); p = (w.innerHeight - r.top) / (w.innerHeight + r.height); }
      else { var max = d.documentElement.scrollHeight - w.innerHeight; p = max > 0 ? w.scrollY / max : 0; }
      el.style.setProperty('--pb', clamp(p, 0, 1).toFixed(4));
    }, el);
  }
  function pointerFx(el) {
    if (!once(el, 'ptr')) return;
    var area = el.classList.contains('ay-ptr-area-page') ? w : (el.classList.contains('ay-ptr-area-parent') && el.parentElement ? (el.parentElement.closest('.e-con') || el) : el);
    var off = function () { el.classList.remove('is-live'); el.style.removeProperty('--ptx'); el.style.removeProperty('--pty'); };
    area.addEventListener('pointermove', function (e) {
      if (reduced() || e.pointerType === 'touch') return;
      var magnet = el.classList.contains('ay-ptr-magnet'), shift = el.classList.contains('ay-ptr-shift'); if (!magnet && !shift) { off(); return; }
      var k = parseFloat(getComputedStyle(el).getPropertyValue('--ay-p-k')); if (isNaN(k)) k = 30;
      var r = el.getBoundingClientRect(), x, y;
      if (magnet) { x = (e.clientX - r.left - r.width / 2) * k / 100; y = (e.clientY - r.top - r.height / 2) * k / 100; }
      else { var a = area === w ? { left: 0, top: 0, width: w.innerWidth, height: w.innerHeight } : area.getBoundingClientRect(); x = ((e.clientX - a.left) / a.width - .5) * k; y = ((e.clientY - a.top) / a.height - .5) * k; }
      el.classList.add('is-live'); el.style.setProperty('--ptx', x.toFixed(1) + 'px'); el.style.setProperty('--pty', y.toFixed(1) + 'px');
    }, { passive: true });
    (area === w ? d : area).addEventListener('pointerleave', off);
  }
  function scrollPar(el) {
    if (!once(el, 'spar')) return;
    onScroll(function () {
      if (reduced() || inEditor() || !el.classList.contains('ay-spar-yes')) { el.style.removeProperty('--spy'); return; }
      var k = parseFloat(getComputedStyle(el).getPropertyValue('--ay-s-k')); if (isNaN(k)) k = 40;
      var r = el.getBoundingClientRect(), cur = parseFloat(el.style.getPropertyValue('--spy')) || 0, p = (r.top - cur + r.height / 2 - w.innerHeight / 2) / w.innerHeight;
      el.style.setProperty('--spy', (clamp(p, -1.2, 1.2) * -k).toFixed(1) + 'px');
    }, el);
  }
  function textFx(el) {
    if (el.classList.contains('ay-txt-shimmer')) return;
    var chars = /ay-txt-(letters|type)/.test(el.className), box = $('.elementor-heading-title', el) || $('.elementor-text-editor', el) || $('.elementor-widget-container', el) || el;
    if (!$('.ay-w', box)) {
      var tw = d.createTreeWalker(box, NodeFilter.SHOW_TEXT, null), nodes = [], n, wi = 0, ci = 0;
      while ((n = tw.nextNode())) if (n.nodeValue.trim() && !n.parentNode.closest('script,style,svg')) nodes.push(n);
      nodes.forEach(function (node) {
        var frag = d.createDocumentFragment();
        node.nodeValue.split(/(\s+)/).forEach(function (part) {
          if (!part) return;
          if (/^\s+$/.test(part)) { frag.appendChild(d.createTextNode(part)); return; }
          var o = d.createElement('span'), i = d.createElement('span'); o.className = 'ay-w'; i.className = 'ay-wi'; i.style.setProperty('--wi', wi++);
          if (chars) Array.from(part).forEach(function (ch) { var c = d.createElement('span'); c.className = 'ay-c'; c.style.setProperty('--ci', ci++); c.textContent = ch; i.appendChild(c); }); else i.textContent = part;
          o.appendChild(i); frag.appendChild(o);
        });
        node.parentNode.replaceChild(frag, node);
      });
    }
    if (!once(el, 'txt')) return;
    var go = function () { requestAnimationFrame(function () { requestAnimationFrame(function () { el.classList.add('ay-t-in'); }); }); };
    if (inEditor() || reduced()) el.classList.add('ay-t-in'); else if (el.classList.contains('ay-txt-start-load')) go(); else watch(el, null, go);
  }

  /* ------------------------------------ certificates: viewer with download */
  var crv = null, crvList = [], crvAt = 0, crvFrom = null;
  function crvBuild() {
    crv = d.createElement('div'); crv.className = 'ay-crv'; crv.setAttribute('role', 'dialog'); crv.setAttribute('aria-modal', 'true'); crv.setAttribute('data-no-curtain', '');
    crv.innerHTML = '<div class="ay-crv-box"><div class="ay-crv-stage"></div><div class="ay-crv-bar"><div class="ay-crv-cap"><strong></strong><span></span></div><div class="ay-crv-acts">' +
      '<button type="button" class="ay-crv-btn ay-crv-nav" data-go="-1" aria-label="Previous">‹</button><button type="button" class="ay-crv-btn ay-crv-nav" data-go="1" aria-label="Next">›</button>' +
      '<a class="ay-crv-btn is-main ay-crv-dl" download></a><button type="button" class="ay-crv-btn ay-crv-x"></button></div></div></div>';
    d.body.appendChild(crv);
    crv.addEventListener('click', function (e) {
      var go = e.target.closest('[data-go]');
      if (go) { crvShow(crvAt + (+go.getAttribute('data-go'))); return; }
      if (e.target === crv || e.target.closest('.ay-crv-x')) crvClose();
    });
    d.addEventListener('keydown', function (e) {
      if (!crv.classList.contains('is-open')) return;
      if (e.key === 'Escape') crvClose(); else if (e.key === 'ArrowRight') crvShow(crvAt + 1); else if (e.key === 'ArrowLeft') crvShow(crvAt - 1);
      else if (e.key === 'Tab') { var f = $$('button:not([hidden]), a[href]', crv), i = f.indexOf(d.activeElement); e.preventDefault(); (f[(i + (e.shiftKey ? -1 : 1) + f.length) % f.length] || f[0]).focus(); }
    });
  }
  function crvShow(i) {
    if (!crvList.length) return;
    crvAt = (i + crvList.length) % crvList.length;
    var b = crvList[crvAt], src = b.getAttribute('data-src'), stage = $('.ay-crv-stage', crv), dl = $('.ay-crv-dl', crv);
    stage.innerHTML = '';
    if (b.getAttribute('data-kind') === 'pdf') { var fr = d.createElement('iframe'); fr.title = b.getAttribute('data-title') || ''; fr.src = src; stage.appendChild(fr); }
    else { var im = d.createElement('img'); im.alt = b.getAttribute('data-title') || ''; im.src = src; stage.appendChild(im); }
    $('.ay-crv-cap strong', crv).textContent = b.getAttribute('data-title') || ''; $('.ay-crv-cap span', crv).textContent = b.getAttribute('data-meta') || '';
    dl.href = src; dl.setAttribute('download', (src.split('/').pop() || 'file').split('?')[0]);
    $$('.ay-crv-nav', crv).forEach(function (n) { n.hidden = crvList.length < 2; });
  }
  function crvClose() { crv.classList.remove('is-open'); d.documentElement.classList.remove('ay-lock'); setTimeout(function () { if (!crv.classList.contains('is-open')) $('.ay-crv-stage', crv).innerHTML = ''; }, 500); if (crvFrom) crvFrom.focus(); }
  function cred(el) {
    if (!once(el, 'cred')) return;
    var cfg = json(el, 'data-ay-cred');
    el.addEventListener('click', function (e) {
      var b = e.target.closest('button.ay-cr-item'); if (!b || inEditor()) return;
      if (!crv) crvBuild();
      crvList = $$('button.ay-cr-item', el); crvFrom = b;
      var cs = getComputedStyle(el); ['--ay-cr-veil', '--ay-cr-veil-op', '--ay-cr-veil-blur'].forEach(function (k) { var v = cs.getPropertyValue(k); if (v) crv.style.setProperty(k, v); else crv.style.removeProperty(k); });
      var dl = $('.ay-crv-dl', crv); dl.textContent = cfg.dl || 'Download'; dl.hidden = !cfg.download; dl.style.display = cfg.download ? '' : 'none'; $('.ay-crv-x', crv).textContent = cfg.close || 'Close';
      crvShow(crvList.indexOf(b)); d.documentElement.classList.add('ay-lock');
      requestAnimationFrame(function () { crv.classList.add('is-open'); $('.ay-crv-x', crv).focus(); });
    });
  }

  /* ------------------------------------------------ sticky portrait story */
  function story(el) {
    if (!once(el, 'story')) return;
    var chapters = $$('.ay-sy-ch', el), media = $('.ay-sy-media', el), stick = $('.ay-sy-stick', el), cur = -1;
    chapters.forEach(function (c) { if (inEditor() || reduced()) c.classList.add('is-in'); else watch(c, null, function () { c.classList.add('is-in'); }); });
    onScroll(function () {
      var stacked = getComputedStyle(el).gridTemplateColumns.split(' ').length < 2, r = el.getBoundingClientRect(), vh = w.innerHeight;
      var p = clamp((vh * .5 - r.top) / Math.max(r.height - vh * .4, 1), 0, 1), best = 0;
      chapters.forEach(function (c, k) { if (c.getBoundingClientRect().top < vh * .6) best = k; });
      el.style.setProperty('--sy-p', p.toFixed(4));
      if (best !== cur) { cur = best; chapters.forEach(function (c, k) { c.classList.toggle('is-on', k === best); }); var n = $('.ay-sy-cur', el); if (n) n.textContent = ('0' + (best + 1)).slice(-2); $$('.ay-sy-dot', el).forEach(function (dt, k) { dt.classList.toggle('is-on', k <= best); }); }
      pin(stick, media, !stacked && el.classList.contains('ay-sy-sticky'), '--ay-sy-top', 110);
    }, el);
  }

  /* --------------------------------------------------------------------- init */
  var MAP = [['[data-ay-hero]', hero], ['[data-ay-hz]', hz], ['[data-ay-stack]', stack], ['[data-ay-archive]', archive], ['[data-ay-gallery]', gallery], ['[data-ay-faq]', faq], ['[data-ay-timeline]', timeline],
    ['.e-con.ay-tl-yes', timelineBox], ['[data-ay-motion]', motion], ['[data-ay-process]', process], ['[data-ay-compare]', compare], ['.ay-depth-tilt', tilt], ['[class*="ay-rv-"]', reveal], ['.ay-skw-draw', reveal],
    ['[data-ay-dock]', dock], ['.e-con.ay-float-top, .e-con.ay-float-bottom', floatBar], ['[data-ay-story]', story], ['[data-ay-cred]', cred],
    ['.elementor-element.ay-halo-behind, .elementor-element.ay-halo-ring, .elementor-element.ay-halo-front', halo], ['.elementor-element.ay-shine-hover, .elementor-element.ay-shine-loop, .elementor-element.ay-shine-view', shine],
    ['.elementor-element.ay-pb-bottom, .elementor-element.ay-pb-top, .elementor-element.ay-pb-left, .elementor-element.ay-pb-right', progressLine],
    ['.ay-ptr-shift, .ay-ptr-magnet', pointerFx], ['.ay-spar-yes', scrollPar], ['.ay-txt-words, .ay-txt-letters, .ay-txt-blur, .ay-txt-lines, .ay-txt-type', textFx]];
  function init(root) {
    root = root || d;
    MAP.forEach(function (m) {
      if (root !== d && root.matches && root.matches(m[0])) m[1](root);
      $$(m[0], root).forEach(function (el) { m[1](el); });
    });
    request();
  }
  w.AYInit = init;
  function boot() {
    init(d); wallpaper();
    if (inEditor()) setInterval(function () { init(d); }, 1000);   /* abilities switched on in the panel appear without a reload */
    if (w.elementorFrontend && w.elementorFrontend.hooks) w.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) { init($scope && $scope[0] ? $scope[0] : d); });
  }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot); else boot();
  w.addEventListener('elementor/frontend/init', function () { if (w.elementorFrontend && w.elementorFrontend.hooks) w.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) { init($scope && $scope[0] ? $scope[0] : d); }); });
})();
