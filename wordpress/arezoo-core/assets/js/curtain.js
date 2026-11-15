/* Arezoo Core — frosted-glass page curtain around normal page loads, and the optional site-wide effects.
   The curtain closes on internal link clicks; the next page starts closed (flag in sessionStorage, read by
   an inline script in <head>) and opens once it has loaded. Fail-safes: a timer always opens it, pages
   restored from the back/forward cache are reset, and modified clicks, downloads and outside links are left alone. */
(function () {
  'use strict';
  var w = window, d = document, root = d.documentElement, A = w.AY_CURTAIN || {};
  var reduced = function () { return !!(w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches); };
  var fine = function () { return !!(w.matchMedia && w.matchMedia('(hover: hover) and (pointer: fine)').matches); };

  /* ------------------------------------------------------------------ curtain */
  function curtain(cfg) {
    var el = d.querySelector('body > .ay-cv') || d.querySelector('.ay-cv'), busy = false, KEY = 'ay-cv', t0 = Date.now();
    var dur = Math.max(200, Math.min(3000, +cfg.dur || 750)), hold = Math.max(0, +cfg.hold || 0);
    var skip = function () { return reduced() || (!cfg.mobile && w.matchMedia('(max-width: 767px)').matches); };
    function phase(p) {
      if (!el) return;
      if (p !== 'idle' && el.classList.contains('is-idle')) { el.classList.remove('is-idle'); void el.offsetWidth; }
      el.classList.remove('is-idle', 'is-cover', 'is-reveal'); el.classList.add('is-' + p);
    }
    function reveal() {
      if (!el) { root.classList.remove('ay-cv-arrive'); return; }
      phase('cover');
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          phase('reveal'); root.classList.remove('ay-cv-arrive');
          setTimeout(function () { phase('idle'); busy = false; }, Math.round(dur * 1.3) + 160);
        });
      });
    }
    var last = null;
    w.addEventListener('pointerdown', function (e) { last = { x: e.clientX, y: e.clientY, t: Date.now() }; }, { passive: true, capture: true });
    function origin() {
      var ok = last && Date.now() - last.t < 1500;
      el.style.setProperty('--cv-x', (ok ? last.x / w.innerWidth * 100 : 50).toFixed(1) + '%');
      el.style.setProperty('--cv-y', (ok ? last.y / w.innerHeight * 100 : 50).toFixed(1) + '%');
    }
    function prefetch(url) {
      try { if (d.querySelector('link[rel="prefetch"][href="' + url + '"]')) return; var l = d.createElement('link'); l.rel = 'prefetch'; l.href = url; l.as = 'document'; d.head.appendChild(l); } catch (e) { /* optional */ }
    }
    var bad = /\/wp-(admin|login)|\/feed\/?$|[?&](elementor-preview|preview|customize_changeset_uuid)=|\.(pdf|zip|rar|7z|jpe?g|png|webp|avif|gif|svg|mp4|webm|mp3|wav|docx?|xlsx?|pptx?|csv|txt)(\?|$)/i;
    function target(a) {
      if (!a || a.hasAttribute('download') || (a.target && a.target !== '_self') || a.closest('[data-no-curtain],#wpadminbar,.elementor-editor-active')) return null;
      var raw = a.getAttribute('href') || '';
      if (!raw || raw.charAt(0) === '#' || /^(mailto|tel|sms|javascript|whatsapp|skype|viber|tg):/i.test(raw)) return null;
      var u; try { u = new URL(a.href, w.location.href); } catch (e) { return null; }
      if (u.origin !== w.location.origin || bad.test(u.href)) return null;
      if (u.pathname === w.location.pathname && u.search === w.location.search) return null;
      if (a.hasAttribute('data-elementor-open-lightbox') && a.getAttribute('data-elementor-open-lightbox') !== 'no') return null;
      if (a.classList.contains('ay-wg-zoom') || /^#elementor-action/.test(u.hash)) return null;
      return u.href;
    }
    function leave(url) {
      if (busy) return; busy = true;
      origin(); prefetch(url); phase('cover');
      try { sessionStorage.setItem(KEY, String(Date.now())); } catch (e) { /* the next page simply opens without the curtain */ }
      setTimeout(function () { w.location.href = url; }, dur + 40);
      setTimeout(function () { if (d.visibilityState === 'visible') { try { sessionStorage.removeItem(KEY); } catch (e) { /* ignore */ } reveal(); } }, 7000);
    }
    d.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || !el || skip()) return;
      var url = target(e.target && e.target.closest ? e.target.closest('a[href]') : null);
      if (!url) return;
      e.preventDefault(); leave(url);
    });
    /* warm the next page while the pointer rests on a link, so the change feels immediate */
    var hoverT = 0;
    d.addEventListener('pointerover', function (e) {
      if (e.pointerType === 'touch') return;
      var a = e.target && e.target.closest ? e.target.closest('a[href]') : null, url = target(a);
      clearTimeout(hoverT); if (url) hoverT = setTimeout(function () { prefetch(url); }, 90);
    }, { passive: true });
    if (root.classList.contains('ay-cv-arrive')) {
      var go = function () { if (!root.classList.contains('ay-cv-arrive')) return; setTimeout(reveal, Math.max(60, hold - (Date.now() - t0))); };
      if (d.readyState === 'complete') go(); else { w.addEventListener('load', go, { once: true }); setTimeout(go, 2600); }
    }
    w.addEventListener('pageshow', function (e) { if (e.persisted) { busy = false; root.classList.remove('ay-cv-arrive'); phase('idle'); } });
  }

  /* ------------------------------------------------------- site-wide effects */
  function layer(cls) { var n = d.createElement('i'); n.className = 'ay-fxl ' + cls; n.setAttribute('aria-hidden', 'true'); d.body.appendChild(n); return n; }
  function effects(fx) {
    if (fx.progress) {
      var bar = layer('ay-fx-progress'); if (fx.progress.c) bar.style.setProperty('--c', fx.progress.c); bar.style.setProperty('--h', (fx.progress.h || 2) + 'px');
      var tick = false, upd = function () { tick = false; var max = root.scrollHeight - w.innerHeight; bar.style.setProperty('--p', max > 0 ? Math.min(1, w.scrollY / max).toFixed(4) : 0); };
      w.addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(upd); } }, { passive: true }); upd();
    }
    if (reduced()) return;
    if (fx.grain) { layer('ay-fx-grain').style.setProperty('--o', fx.grain); }
    if (fx.shine) d.body.classList.add('ay-fx-shine');
    if (fx.sparkle) {
      d.addEventListener('pointerdown', function (e) {
        if (e.button > 0) return;
        for (var i = 0; i < fx.sparkle.n; i++) {
          var s = layer('ay-fx-spark'), a = Math.random() * 6.2832, r = 26 + Math.random() * 60;
          s.style.left = e.clientX + 'px'; s.style.top = e.clientY + 'px'; if (fx.sparkle.c) s.style.setProperty('--c', fx.sparkle.c);
          s.style.setProperty('--dx', (Math.cos(a) * r).toFixed(0) + 'px'); s.style.setProperty('--dy', (Math.sin(a) * r).toFixed(0) + 'px'); s.style.setProperty('--sc', (.5 + Math.random()).toFixed(2));
          s.addEventListener('animationend', function () { this.remove(); });
        }
      }, { passive: true });
    }
    if (!fine()) return;
    if (fx.cursor) {
      var c = layer('ay-fx-cursor'), x = 0, y = 0, tx = 0, ty = 0, raf = 0;
      if (fx.cursor.c) c.style.setProperty('--c', fx.cursor.c); c.style.setProperty('--s', (fx.cursor.s || 420) + 'px'); c.style.setProperty('--o', fx.cursor.o);
      var step = function () { x += (tx - x) * .14; y += (ty - y) * .14; c.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)'; raf = (Math.abs(tx - x) + Math.abs(ty - y) > .4) ? requestAnimationFrame(step) : 0; };
      d.addEventListener('pointermove', function (e) { tx = e.clientX; ty = e.clientY; if (!c.classList.contains('is-on')) { x = tx; y = ty; c.classList.add('is-on'); } if (!raf) raf = requestAnimationFrame(step); }, { passive: true });
      d.addEventListener('pointerleave', function () { c.classList.remove('is-on'); });
    }
    if (fx.magnet) {
      d.addEventListener('pointermove', function (e) {
        var b = e.target && e.target.closest ? e.target.closest('.elementor-button, .ay-st-btn, .ay-totop') : null;
        if (magnetOn && magnetOn !== b) { magnetOn.style.translate = ''; magnetOn.classList.remove('is-pull'); magnetOn = null; }
        if (!b) return;
        var r = b.getBoundingClientRect(); b.classList.add('ay-magnet', 'is-pull'); magnetOn = b;
        b.style.translate = ((e.clientX - r.left - r.width / 2) * fx.magnet).toFixed(1) + 'px ' + ((e.clientY - r.top - r.height / 2) * fx.magnet).toFixed(1) + 'px';
      }, { passive: true });
    }
  }
  var magnetOn = null;

  function boot() { if (A.curtain) curtain(A.curtain); if (A.fx) effects(A.fx); }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot); else boot();
})();
