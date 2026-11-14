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
  function hero(el) {
    if (!w.AYHero) return;
    if (el._ayHero) { el._ayHero.destroy(); }
    el._ayHero = w.AYHero.mount(el, json(el, 'data-ay-hero'));
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
      apply();
    });
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
  function wire(canvas) {   /* a turning 3D ring: wireframe band, prongs and a brilliant */
    if (!once(canvas, 'wire')) return;
    var ctx = canvas.getContext('2d'), host = canvas.closest('.ay-mo'), t = 0, raf = 0, last = 0;
    var ring = [], gem = [], i, j, R = 62, r = 9, N = 28, M = 8;
    for (i = 0; i < N; i++) { var row = []; for (j = 0; j < M; j++) { var a = i / N * 6.2832, b = j / M * 6.2832; row.push([(R + r * Math.cos(b)) * Math.cos(a), (R + r * Math.cos(b)) * Math.sin(a) + 18, r * 1.5 * Math.sin(b)]); } ring.push(row); }
    for (i = 0; i < 8; i++) { var an = i / 8 * 6.2832; gem.push([[Math.cos(an) * 22, -64, Math.sin(an) * 22], [Math.cos(an) * 12, -76, Math.sin(an) * 12]]); }
    function proj(p, ry, rx) { var x = p[0] * Math.cos(ry) + p[2] * Math.sin(ry), z = -p[0] * Math.sin(ry) + p[2] * Math.cos(ry), y = p[1] * Math.cos(rx) - z * Math.sin(rx); z = p[1] * Math.sin(rx) + z * Math.cos(rx); var s = 360 / (360 + z); return [200 + x * s * 1.9, 208 + y * s * 1.9, z]; }
    function line(a, b) { ctx.beginPath(); ctx.moveTo(a[0], a[1]); ctx.lineTo(b[0], b[1]); ctx.stroke(); }
    function frame(now) {
      raf = 0; if (!canvas.isConnected) return;
      var cs = getComputedStyle(host), spd = parseFloat(cs.getPropertyValue('--m-spd')) || 1, playing = host.classList.contains('is-play') && !reduced();
      var dt = Math.min(now - last, 80) / 1000; last = now; if (playing) t += dt * spd;
      var lineC = cs.getPropertyValue('--m-line').trim() || '#ddd', acc = cs.getPropertyValue('--m-accent').trim() || '#9be', ry = t * .7, rx = .42 + Math.sin(t * .35) * .1, solid = (Math.sin(t * .5) + 1) / 2;
      ctx.clearRect(0, 0, 400, 400); ctx.lineWidth = 1.1; ctx.lineJoin = 'round';
      var P = ring.map(function (row) { return row.map(function (p) { return proj(p, ry, rx); }); });
      for (i = 0; i < N; i++) for (j = 0; j < M; j++) {
        var p = P[i][j], q = P[(i + 1) % N][j], s = P[i][(j + 1) % M], depth = clamp(.25 + (140 - p[2]) / 280, .15, 1);
        ctx.strokeStyle = lineC; ctx.globalAlpha = depth * (.35 + .65 * (1 - solid)); line(p, q); line(p, s);
        if (solid > .05) { ctx.globalAlpha = solid * depth * .5; ctx.fillStyle = acc; ctx.beginPath(); ctx.moveTo(p[0], p[1]); ctx.lineTo(q[0], q[1]); var u = P[(i + 1) % N][(j + 1) % M]; ctx.lineTo(u[0], u[1]); ctx.lineTo(s[0], s[1]); ctx.closePath(); ctx.fill(); }
      }
      ctx.globalAlpha = 1; ctx.strokeStyle = lineC;
      var G = gem.map(function (pair) { return [proj(pair[0], ry, rx), proj(pair[1], ry, rx)]; }), cu = proj([0, -40, 0], ry, rx);
      for (i = 0; i < 8; i++) { var n = (i + 1) % 8; ctx.globalAlpha = .9; line(G[i][0], G[n][0]); line(G[i][1], G[n][1]); line(G[i][0], G[i][1]); ctx.globalAlpha = .55; line(G[i][0], cu); if (i % 2 === 0) { ctx.globalAlpha = .8; line(proj([gem[i][0][0] * .9, -40, gem[i][0][2] * .9], ry, rx), G[i][0]); } }
      ctx.globalAlpha = 1; ctx.fillStyle = acc; var tw = (Math.sin(t * 2.2) + 1) / 2, c = proj([10, -74, 8], ry, rx);
      ctx.save(); ctx.translate(c[0], c[1]); ctx.rotate(t); ctx.globalAlpha = tw; ctx.beginPath(); for (i = 0; i < 8; i++) { var rr = i % 2 ? 2.4 : 13 * tw + 3, aa = i / 8 * 6.2832; ctx.lineTo(Math.cos(aa) * rr, Math.sin(aa) * rr); } ctx.closePath(); ctx.fill(); ctx.restore();
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
      /* keep the frame pinned while this section scrolls; if CSS sticky is blocked by an ancestor
         (overflow hidden from another plugin or theme), move it with a transform instead */
      var stick = $('.ay-pr-stick', el), wrap = el.closest('.elementor-widget') || el;
      if (stick && !stacked && wrap.classList.contains('ay-pr-sticky-yes')) {
        var top = parseFloat(getComputedStyle(wrap).getPropertyValue('--ay-pr-top')) || 110, mr = media.getBoundingClientRect(), want = clamp(top - mr.top, 0, Math.max(mr.height - stick.offsetHeight, 0));
        if (!stick.classList.contains('is-js') && want > 2 && Math.abs((stick.getBoundingClientRect().top - mr.top) - want) > 3) stick.classList.add('is-js');
        if (stick.classList.contains('is-js')) stick.style.transform = 'translate3d(0,' + want.toFixed(1) + 'px,0)';
      } else if (stick) { stick.classList.remove('is-js'); stick.style.transform = ''; }
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
    if (el.getAttribute('data-ay-dock') !== '1') return;
    onScroll(function () { var f = $('.ay-part-footer') || $('footer'); if (!f) return; el.classList.toggle('is-away', !inEditor() && f.getBoundingClientRect().top < w.innerHeight - 90); }, el);
  }
  function floatBar(el) {
    if (!once(el, 'float')) return;
    onScroll(function () {
      if (inEditor()) { el.classList.remove('is-away'); return; }
      if (el.classList.contains('ay-float-hide-yes')) { var f = $('.ay-part-footer') || $('footer'); if (f) el.classList.toggle('is-away', f.getBoundingClientRect().top < w.innerHeight - 90); }
      if (el.classList.contains('ay-float-smart-yes')) { var y = w.scrollY; el.classList.toggle('is-away', y > lastY && y > 240); lastY = y; }
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
      var phone = W < 768, size = phone ? Math.min(c.size * .62, W * .78) : Math.min(c.size, W * .5), gap = phone ? c.spacing * .8 : c.spacing, n = Math.max(1, Math.ceil(H / gap)), h = '', i;
      for (i = 0; i < n; i++) {
        var side = c.side === 'alt' ? (i % 2 ? 'left' : 'right') : c.side, x;
        if (side === 'left') x = -size * .22 + rnd() * W * .06; else if (side === 'right') x = W - size * .78 - rnd() * W * .06; else x = (W - size) / 2 + (rnd() - .5) * W * .2;
        var y = i * gap + gap * .18 + rnd() * gap * .3, rot = (rnd() - .5) * 2 * c.rotate;
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

  /* --------------------------------------------------------------------- init */
  var MAP = [['[data-ay-hero]', hero], ['[data-ay-hz]', hz], ['[data-ay-stack]', stack], ['[data-ay-archive]', archive], ['[data-ay-gallery]', gallery], ['[data-ay-faq]', faq], ['[data-ay-timeline]', timeline],
    ['.e-con.ay-tl-yes', timelineBox], ['[data-ay-motion]', motion], ['[data-ay-process]', process], ['[data-ay-compare]', compare], ['.ay-depth-tilt', tilt], ['[class*="ay-rv-"]', reveal], ['.ay-skw-draw', reveal],
    ['[data-ay-dock]', dock], ['.e-con.ay-float-top, .e-con.ay-float-bottom', floatBar]];
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
    if (w.elementorFrontend && w.elementorFrontend.hooks) w.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) { init($scope && $scope[0] ? $scope[0] : d); });
  }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot); else boot();
  w.addEventListener('elementor/frontend/init', function () { if (w.elementorFrontend && w.elementorFrontend.hooks) w.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) { init($scope && $scope[0] ? $scope[0] : d); }); });
})();
