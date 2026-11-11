/* Arezoo Yousefi — main demo preview. Plain JS, no dependencies.
   This is a visual / motion preview only; it is not the WordPress theme and proves nothing about Elementor. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var IMG = 'assets/img/works/';
  function rng(seed) { var a = seed >>> 0; return function () { a |= 0; a = a + 0x6D2B79F5 | 0; var t = Math.imul(a ^ a >>> 15, 1 | a); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  /* ------------------------------------------------------------------ data
     Sample entries only: the three sample sets shipped with Sogand, cut into single pieces. */
  var COLLECTIONS = [
    { id: 'kite', name: 'Kite', tall: 'emerald-kite-earrings', small: ['emerald-kite-drop', 'emerald-kite-drop'] },
    { id: 'ribbon', name: 'Ribbon', tall: 'ruby-ribbon-necklace', small: ['ruby-blossom-earrings', 'ruby-ribbon-bracelet'] },
    { id: 'bow', name: 'Bow', tall: 'blue-bow-necklace', small: ['blue-bow-studs', 'blue-bow-bracelet'] }
  ];
  var TYPES = ['Earrings', 'Necklace', 'Bracelet', 'Set'];
  var WORKS = [
    { slug: 'emerald-kite-earrings', name: 'Emerald Kite Earrings', col: 'kite', type: 'Earrings', year: '2025', materials: 'White gold', gemstones: 'Kite-cut green stones, emerald-cut white stones', technique: 'Digital rendering', extra: [['Pieces', 'Pair']], desc: 'A pair of drop earrings: an emerald-cut white stone above a long kite-cut green stone.' },
    { slug: 'emerald-kite-drop', name: 'Emerald Kite Drop', col: 'kite', type: 'Earrings', year: '2025', materials: 'White gold', gemstones: 'Kite-cut green stone, emerald-cut white stone', technique: 'Digital rendering', extra: [['Pieces', 'Single earring']], desc: 'The single drop from the Kite pair, shown on its own to read the cut and the setting.' },
    { slug: 'ruby-ribbon-set', name: 'Ruby Ribbon Set', col: 'ribbon', type: 'Set', year: '2025', materials: 'White gold, satin ribbon', gemstones: 'Red stones, white stones', technique: 'Digital rendering', extra: [['Pieces', 'Necklace, earrings, bracelet']], desc: 'The full set: a necklace tied with a satin ribbon, blossom earrings and a matching bracelet.' },
    { slug: 'ruby-ribbon-necklace', name: 'Ruby Ribbon Necklace', col: 'ribbon', type: 'Necklace', year: '2025', materials: 'White gold, satin ribbon', gemstones: 'Red stones, white stones', technique: 'Digital rendering', extra: [['Closure', 'Ribbon bow']], desc: 'A graduated line of white stones closing in a red blossom, finished with a ribbon bow.' },
    { slug: 'ruby-blossom-earrings', name: 'Ruby Blossom Earrings', col: 'ribbon', type: 'Earrings', year: '2025', materials: 'White gold', gemstones: 'Red stones, white stones', technique: 'Digital rendering', extra: [['Pieces', 'Pair']], desc: 'Clustered red blossoms with falling lines of white stones.' },
    { slug: 'ruby-ribbon-bracelet', name: 'Ruby Ribbon Bracelet', col: 'ribbon', type: 'Bracelet', year: '2025', materials: 'White gold', gemstones: 'Red stones, white stones', technique: 'Digital rendering', extra: [['Motif', 'Single blossom']], desc: 'A line bracelet of white stones with one red blossom at its centre.' },
    { slug: 'blue-bow-set', name: 'Blue Bow Set', col: 'bow', type: 'Set', year: '2024', materials: 'Yellow gold, white gold', gemstones: 'Blue stones', technique: 'Digital rendering', extra: [['Pieces', 'Necklace, studs, bracelet']], desc: 'The full set: a fine collar, a pair of studs and a bracelet, each tied with a two-tone bow.' },
    { slug: 'blue-bow-necklace', name: 'Blue Bow Necklace', col: 'bow', type: 'Necklace', year: '2024', materials: 'Yellow gold, white gold', gemstones: 'Blue stone', technique: 'Digital rendering', extra: [['Form', 'Rigid collar']], desc: 'A fine gold collar carrying a two-tone bow and a single blue stone.' },
    { slug: 'blue-bow-studs', name: 'Blue Bow Studs', col: 'bow', type: 'Earrings', year: '2024', materials: 'Yellow gold, white gold', gemstones: 'Blue stones', technique: 'Digital rendering', extra: [['Pieces', 'Pair']], desc: 'Stud earrings: a round blue stone above a bow in yellow and white gold.' },
    { slug: 'blue-bow-bracelet', name: 'Blue Bow Bracelet', col: 'bow', type: 'Bracelet', year: '2024', materials: 'Yellow gold, white gold', gemstones: 'Blue stones', technique: 'Digital rendering', extra: [['Motif', 'Centre bow']], desc: 'A beaded gold bracelet with a centre bow and three blue stones.' }
  ];
  var bySlug = {}; WORKS.forEach(function (w) { bySlug[w.slug] = w; });
  var colName = function (id) { var c = COLLECTIONS.filter(function (x) { return x.id === id; })[0]; return c ? c.name : id; };

  /* ----------------------------------------------------------------- icons */
  var ICONS = {
    home: '<path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9.5V20h13V9.5"/>',
    gem: '<path d="M6 4h12l4 5-10 11L2 9z"/><path d="M2 9h20M9 4 7.5 9 12 20l4.5-11L15 4"/>',
    user: '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-4.5 4-6.5 8-6.5s7 2 8 6.5"/>',
    mail: '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="m3.5 7 8.5 6.5L20.5 7"/>',
    instagram: '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r=".6" fill="currentColor"/>',
    telegram: '<path d="M21.5 3.5 2.5 11l6 2.2 2.3 6.3 3.2-4 4.6 3.4z"/><path d="m8.5 13.2 8.5-6-5.2 7"/>',
    up: '<path d="M12 20V5M5.5 11 12 4.5 18.5 11"/>',
    search: '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/>'
  };
  function paintIcons(root) { $$('[data-ic]', root).forEach(function (el) { if (!el.firstChild) el.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true">' + (ICONS[el.getAttribute('data-ic')] || '') + '</svg>'; }); }

  /* ------------------------------------------------- 96 colour themes
     Two palettes x 4 families (by page background) x 12 role combinations (3 accents x 2 button styles x 2 surface
     treatments) = 96 themes. Only each palette's own colours and their tints / shades are used. */
  var PA = { R: '#32292F', T: '#99E1D9', M: '#F0F7F4', W: '#705D56' };                 /* first palette */
  var PB = { K: '#0B1421', P: '#272737', S: '#B5B9BC', N: '#283041', L: '#DFDFDF' };   /* second palette (night) */
  function h2r(h) { return [parseInt(h.slice(1, 3), 16), parseInt(h.slice(3, 5), 16), parseInt(h.slice(5, 7), 16)]; }
  function r2h(r) { return '#' + r.map(function (v) { return ('0' + Math.round(clamp(v, 0, 255)).toString(16)).slice(-2); }).join('').toUpperCase(); }
  function mix(a, b, t) { var x = h2r(a), y = h2r(b); return r2h([x[0] + (y[0] - x[0]) * t, x[1] + (y[1] - x[1]) * t, x[2] + (y[2] - x[2]) * t]); }
  function lum(h) { var c = h2r(h).map(function (v) { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); }); return .2126 * c[0] + .7152 * c[1] + .0722 * c[2]; }
  function contrast(a, b) { var x = lum(a), y = lum(b); return (Math.max(x, y) + .05) / (Math.min(x, y) + .05); }
  var NAMES = {}; NAMES[PA.R] = 'ریزن بلک'; NAMES[PA.T] = 'آبی تیفانی'; NAMES[PA.M] = 'مینت کرم'; NAMES[PA.W] = 'ونگه';
  NAMES[PB.K] = 'سرمه‌ای شب'; NAMES[PB.P] = 'بادمجانی دودی'; NAMES[PB.S] = 'نقره‌ای'; NAMES[PB.N] = 'سرمه‌ای دودی'; NAMES[PB.L] = 'خاکستری روشن';
  var SETS = [{ id: 'b', fa: 'پالت دوم · شب (۴۸ تم)' }, { id: 'a', fa: 'پالت اول · مینت (۴۸ تم)' }];
  var FAMILIES = [
    { id: 'midnight', set: 'b', fa: 'سرمه‌ای شب', bg: PB.K, ink: PB.L, muted: mix(PB.S, PB.K, .12), accents: [PB.S, PB.L, PB.P], bands: [mix(PB.K, '#000000', .4), PB.P], dk: PB.K, lt: PB.L },
    { id: 'plum', set: 'b', fa: 'بادمجانی دودی', bg: PB.P, ink: PB.L, muted: mix(PB.S, PB.P, .14), accents: [PB.S, PB.L, PB.K], bands: [PB.K, PB.N], dk: PB.K, lt: PB.L },
    { id: 'slate', set: 'b', fa: 'سرمه‌ای دودی', bg: PB.N, ink: PB.L, muted: mix(PB.S, PB.N, .14), accents: [PB.S, PB.L, PB.K], bands: [PB.K, PB.P], dk: PB.K, lt: PB.L },
    { id: 'silver', set: 'b', fa: 'خاکستری روشن', bg: PB.L, ink: PB.K, muted: mix(PB.N, PB.L, .22), accents: [PB.N, PB.P, PB.S], bands: [PB.K, PB.N], dk: PB.K, lt: PB.L },
    { id: 'mint', set: 'a', fa: 'مینت کرم (روشن)', bg: PA.M, ink: PA.R, muted: PA.W, accents: [PA.T, PA.W, PA.R], bands: [PA.R, PA.W], dk: PA.R, lt: PA.M },
    { id: 'tiffany', set: 'a', fa: 'آبی تیفانی', bg: PA.T, ink: PA.R, muted: mix(PA.R, PA.T, .22), accents: [PA.R, PA.W, PA.M], bands: [PA.R, PA.W], dk: PA.R, lt: PA.M },
    { id: 'wenge', set: 'a', fa: 'ونگه', bg: PA.W, ink: PA.M, muted: mix(PA.M, PA.W, .28), accents: [PA.T, PA.M, PA.R], bands: [PA.R, mix(PA.W, PA.R, .55)], dk: PA.R, lt: PA.M },
    { id: 'raisin', set: 'a', fa: 'ریزن بلک (تیره)', bg: PA.R, ink: PA.M, muted: mix(PA.M, PA.R, .34), accents: [PA.T, PA.W, PA.M], bands: [mix(PA.R, '#000000', .32), PA.W], dk: PA.R, lt: PA.M }
  ];
  var THEMES = [];
  FAMILIES.forEach(function (f) {
    var dark = lum(f.bg) < .3, n = 0;
    var readable = function (bg) { return contrast(bg, f.dk) >= contrast(bg, f.lt) ? f.dk : f.lt; };
    f.accents.forEach(function (acc) {
      ['ink', 'accent'].forEach(function (btn) {
        ['soft', 'tint'].forEach(function (surf, si) {
          n++;
          var surface = surf === 'soft' ? (dark ? mix(f.bg, f.ink, .07) : mix(f.bg, '#FFFFFF', .55)) : mix(f.bg, acc, dark ? .2 : .14);
          var surface2 = mix(f.bg, acc, surf === 'soft' ? .24 : .36);
          var btnBg = btn === 'ink' ? f.ink : acc;
          var at = acc; if (contrast(at, f.bg) < 3) at = mix(acc, f.ink, .58); if (contrast(at, f.bg) < 3) at = f.ink;
          var band = f.bands[si], bandInk = readable(band);
          THEMES.push({
            id: f.id + '-' + ('0' + n).slice(-2), fam: f.id, set: f.set, famFa: f.fa, n: n,
            fa: f.fa + ' ' + ('0' + n).slice(-2) + ' · تأکید: ' + NAMES[acc] + ' · دکمه: ' + (btn === 'ink' ? 'رنگ متن' : 'رنگ تأکید') + ' · سطح: ' + (surf === 'soft' ? 'ملایم' : 'رنگی'),
            dark: dark,
            vars: { '--bg': f.bg, '--surface': surface, '--surface-2': surface2, '--ink': f.ink, '--muted': f.muted, '--accent': acc, '--accent-text': at,
              '--btn-bg': btnBg, '--btn-ink': readable(btnBg), '--btn-line': contrast(btnBg, f.bg) < 1.6 ? mix(btnBg, f.ink, .4) : btnBg,
              '--band': band, '--band-ink': bandInk, '--band-muted': mix(bandInk, band, .38) }
          });
        });
      });
    });
  });

  /* ----------------------------------------------------------------- state */
  var DEFAULTS = { theme: 'midnight-01', heroDemo: 'photo', spd: 1, amp: 1, kDandelion: 1, kBranch: 1, kNecklace: 1, sparks: true, floats: true, mouse: true, outline: false,
    hdrBlur: 16, hdrOp: .62, dockBlur: 16, dockOp: .76,
    hsMult: .3, hsAuto: true, hsDelay: 3, hsSpeed: 60,
    stackOn: true, stackStep: .03, stackGap: 28, stackMin: .8,
    fx: 'line', faqLayout: 'sidebar', faqSearch: true, wcOn: true, wcColor: '#B5B9BC', wcOp: .28 };
  var state = {}; Object.keys(DEFAULTS).forEach(function (k) { state[k] = DEFAULTS[k]; });
  try { var saved = JSON.parse(localStorage.getItem('ay-preview-v2') || '{}'); Object.keys(DEFAULTS).forEach(function (k) { if (typeof saved[k] === typeof DEFAULTS[k]) state[k] = saved[k]; }); } catch (e) { /* storage unavailable: defaults */ }
  function save() { try { localStorage.setItem('ay-preview-v2', JSON.stringify(state)); } catch (e) { /* ignore */ } }

  var root = document.documentElement;
  function applyTheme() {
    var t = THEMES.filter(function (x) { return x.id === state.theme; })[0] || THEMES[0];
    Object.keys(t.vars).forEach(function (k) { root.style.setProperty(k, t.vars[k]); });
    root.style.colorScheme = t.dark ? 'dark' : 'light';
    document.body.classList.toggle('is-dark', t.dark);
    $$('.pv-th').forEach(function (b) { b.classList.toggle('is-on', b.getAttribute('data-id') === t.id); b.setAttribute('aria-pressed', b.getAttribute('data-id') === t.id); });
    var cur = $('#pvCur'); if (cur) cur.textContent = t.fa;
  }
  function applyState() {
    var s = root.style;
    s.setProperty('--spd', Math.max(state.spd, .05));
    var hero = $('#hero'); if (hero) { hero.classList.toggle('hero--photo', state.heroDemo === 'photo'); hero.classList.toggle('hero--paint', state.heroDemo !== 'photo'); }
    if (photo) photo.update({ speed: state.spd, amp: state.amp, mouse: state.mouse, outline: state.outline,
      groups: { dandelion: { k: state.kDandelion }, seeds: { k: state.kDandelion, on: true }, branch: { k: state.kBranch }, leaves: { k: state.kBranch }, necklace: { k: state.kNecklace }, sparks: { on: state.sparks }, floaters: { on: state.floats } } });
    s.setProperty('--hdr-blur', state.hdrBlur + 'px'); s.setProperty('--hdr-op', state.hdrOp);
    s.setProperty('--dock-blur', state.dockBlur + 'px'); s.setProperty('--dock-op', state.dockOp);
    s.setProperty('--stack-gap', state.stackGap + 'px');
    s.setProperty('--wc-color', state.wcColor); s.setProperty('--wc-op', state.wcOp);
    document.body.classList.toggle('no-wc', !state.wcOn);
    var st = $('#stack'); if (st) st.classList.toggle('is-off', !state.stackOn);
    var f = $('#filters'); if (f) f.className = 'filters fx-' + state.fx;
    var fq = $('#faq'); if (fq) { fq.className = 'faq l-' + state.faqLayout + (fq.classList.contains('is-searching') ? ' is-searching' : ''); var sb = $('.faq-search', fq); if (sb) sb.hidden = !state.faqSearch; if (!state.faqSearch) faqFilter(''); }
    heroStill();
    onScroll();
  }

  /* ============================================================ hero artwork
     TEMPORARY vector stand-ins, drawn in code, layer by layer. The final watercolour layers (bird, branch,
     blossoms, jewel) will be generated by the client and dropped into the same slots. The pencil motifs stay vector. */
  function taperPath(pts, w0, w1) {
    var c = [], i, t;
    for (i = 0; i < pts.length - 1; i++) {
      var p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2;
      for (t = 0; t < 12; t++) {
        var s = t / 12, s2 = s * s, s3 = s2 * s;
        c.push([.5 * (2 * p1[0] + (-p0[0] + p2[0]) * s + (2 * p0[0] - 5 * p1[0] + 4 * p2[0] - p3[0]) * s2 + (-p0[0] + 3 * p1[0] - 3 * p2[0] + p3[0]) * s3),
          .5 * (2 * p1[1] + (-p0[1] + p2[1]) * s + (2 * p0[1] - 5 * p1[1] + 4 * p2[1] - p3[1]) * s2 + (-p0[1] + 3 * p1[1] - 3 * p2[1] + p3[1]) * s3)]);
      }
    }
    c.push(pts[pts.length - 1]);
    var L = [], R = [];
    c.forEach(function (p, k) {
      var a = c[Math.max(k - 1, 0)], b = c[Math.min(k + 1, c.length - 1)], dx = b[0] - a[0], dy = b[1] - a[1], l = Math.hypot(dx, dy) || 1, w = (w0 + (w1 - w0) * (k / (c.length - 1))) / 2;
      dx /= l; dy /= l; L.push([p[0] - dy * w, p[1] + dx * w]); R.push([p[0] + dy * w, p[1] - dx * w]);
    });
    return 'M' + L.concat(R.reverse()).map(function (p) { return p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join('L') + 'Z';
  }
  function leaf(x, y, len, wid, ang, grad, op) {
    return '<g transform="translate(' + x + ' ' + y + ') rotate(' + ang + ')"><path d="M0 0C' + len * .28 + ' ' + -wid + ',' + len * .72 + ' ' + -wid * .85 + ',' + len + ' 0C' + len * .72 + ' ' + wid * .75 + ',' + len * .28 + ' ' + wid + ',0 0Z" fill="url(#' + grad + ')" fill-opacity="' + (op || .8) + '" stroke="#3F6B58" stroke-opacity=".28" stroke-width="1.4"/><path d="M5 0L' + (len - 8) + ' 0" stroke="#34584A" stroke-opacity=".3" stroke-width="1" fill="none"/></g>';
  }
  function blossomSVG(seed, pale) {
    var r = rng(seed), s = '', i;
    for (i = 0; i < 5; i++) {
      var a = i * 72 + (r() - .5) * 16, L = 84 + r() * 14, W = 44 + r() * 9;
      s += '<g transform="rotate(' + a.toFixed(1) + ')"><path d="M0 -6C' + W * .8 + ' ' + -L * .18 + ',' + W + ' ' + -L * .8 + ',' + W * .2 + ' ' + -L + 'C' + W * .08 + ' ' + -L * .9 + ',' + -W * .08 + ' ' + -L * .9 + ',' + -W * .2 + ' ' + -L + 'C' + -W + ' ' + -L * .8 + ',' + -W * .8 + ' ' + -L * .18 + ',0 -6Z" fill="url(#' + (pale ? 'gPetalPale' : 'gPetal') + ')" fill-opacity=".9" stroke="#D2708C" stroke-opacity=".3" stroke-width="1.6"/><path d="M0 -16L0 ' + -L * .6 + '" stroke="#CF6585" stroke-opacity=".25" stroke-width="1.2" fill="none"/></g>';
    }
    for (i = 0; i < 12; i++) { var an = r() * 6.283, d = 15 + r() * 19, x = Math.cos(an) * d, y = Math.sin(an) * d; s += '<path d="M0 0L' + x.toFixed(1) + ' ' + y.toFixed(1) + '" stroke="#B9822B" stroke-opacity=".75" stroke-width="1.1" fill="none"/><circle cx="' + x.toFixed(1) + '" cy="' + y.toFixed(1) + '" r="' + (2 + r() * 1.6).toFixed(1) + '" fill="#E3A93A"/>'; }
    s += '<circle r="8" fill="#F0CF73" fill-opacity=".85"/>';
    return '<g filter="url(#wc)">' + s + '</g>';
  }
  function budSVG() {
    return '<g filter="url(#wc)"><path d="M0 30C-26 10,-24 -30,0 -46C24 -30,26 10,0 30Z" fill="url(#gPetal)" fill-opacity=".92"/><path d="M0 34C-14 26,-20 12,-18 2C-8 10,8 10,18 2C20 12,14 26,0 34Z" fill="#6E9A78" fill-opacity=".85"/></g>';
  }
  function feather(len, w, ang, grad, op) {
    return '<path transform="rotate(' + ang + ')" d="M0 0C' + len * .3 + ' ' + -w + ',' + len * .82 + ' ' + -w * .85 + ',' + len + ' 0C' + len * .82 + ' ' + w * .45 + ',' + len * .3 + ' ' + w * .55 + ',0 0Z" fill="url(#' + grad + ')" fill-opacity="' + op + '" stroke="#1F5F66" stroke-opacity=".22" stroke-width="1"/>';
  }
  function wingSVG(far) {
    var s = '', i, n = 9;
    for (i = 0; i < n; i++) { var t = i / (n - 1); s += feather(118 + 92 * t, 15 - 3 * t, -176 + 70 * t + (far ? 14 : 0), far ? 'gFeatherFar' : 'gFeather', far ? .55 : .8); }
    if (!far) {
      for (i = 0; i < 7; i++) { var u = i / 6; s += feather(64 + 34 * u, 13, -172 + 64 * u, 'gTail', .62); }
      s += '<ellipse cx="-34" cy="-22" rx="40" ry="17" transform="rotate(28 -34 -22)" fill="#B0602C" fill-opacity=".72"/>';
    }
    return '<g filter="url(#wc)">' + s + '</g>';
  }
  function birdBodySVG() {
    var tail = '', i;
    for (i = 0; i < 4; i++) tail += '<g transform="translate(150 228)">' + feather(96 + i * 11, 11, 150 + i * 9, 'gTail', .82) + '</g>';
    return '<g filter="url(#wc)">' + tail +
      '<path d="M332 150C328 127,297 119,278 133C261 146,241 151,222 161C190 179,160 205,138 227C160 236,197 233,227 221C263 207,293 191,307 173C319 167,332 161,332 150Z" fill="url(#gBody)" fill-opacity=".95" stroke="#1B5560" stroke-opacity=".3" stroke-width="1.5"/>' +
      '<path d="M331 149C327 128,298 121,281 134C290 139,304 139,318 143Z" fill="#1B5F6A" fill-opacity=".72"/>' +
      '<path d="M318 166C306 172,292 174,282 168C292 160,306 156,318 158Z" fill="#C2577A" fill-opacity=".55"/>' +
      '</g><path d="M329 144L430 134L330 153Z" fill="#3E3331" fill-opacity=".95" filter="url(#wcSoft)"/>' +
      '<circle cx="313" cy="146" r="4.6" fill="#241C20"/><circle cx="314.4" cy="144.6" r="1.3" fill="#fff"/>';
  }
  function seedSVG() {
    var s = '<path d="M20 58L20 26" />', i;
    for (i = 0; i < 9; i++) { var a = (-150 + i * 15) * Math.PI / 180; s += '<path d="M20 26L' + (20 + Math.cos(a) * 17).toFixed(1) + ' ' + (26 + Math.sin(a) * 17).toFixed(1) + '"/>'; }
    return '<g fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round">' + s + '</g><circle cx="20" cy="58" r="1.8" fill="currentColor"/>';
  }
  var STAR = '<path d="M0 -12C1.2 -3.4,3.4 -1.2,12 0C3.4 1.2,1.2 3.4,0 12C-1.2 3.4,-3.4 1.2,-12 0C-3.4 -1.2,-1.2 -3.4,0 -12Z" fill="#fff"/>';

  /* ---- pencil motifs (final: these stay vector, low opacity) ---- */
  function sFlower(cx, cy, R, rot, seed) {
    var r = rng(seed), s = '', i;
    for (i = 0; i < 6; i++) {
      var a = rot + i * 60 + (r() - .5) * 12, L = R * (.9 + r() * .2), W = R * (.4 + r() * .1);
      var p = 'M0 ' + -R * .14 + 'C' + W + ' ' + -L * .3 + ',' + W * .95 + ' ' + -L * .84 + ',0 ' + -L + 'C' + -W * .95 + ' ' + -L * .84 + ',' + -W + ' ' + -L * .3 + ',0 ' + -R * .14;
      s += '<g transform="translate(' + cx + ' ' + cy + ') rotate(' + a.toFixed(1) + ')"><path d="' + p + '"/><path d="' + p + '" transform="translate(2 1.4) rotate(1.6)" opacity=".45"/><path d="M0 ' + -R * .2 + 'L0 ' + -L * .76 + 'M0 ' + -R * .24 + 'Q' + W * .42 + ' ' + -L * .5 + ' ' + W * .4 + ' ' + -L * .74 + 'M0 ' + -R * .24 + 'Q' + -W * .42 + ' ' + -L * .5 + ' ' + -W * .4 + ' ' + -L * .74 + '" opacity=".55"/></g>';
    }
    s += '<circle cx="' + cx + '" cy="' + cy + '" r="' + R * .12 + '"/>';
    for (i = 0; i < 12; i++) { var an = i * .5236; s += '<circle cx="' + (cx + Math.cos(an) * R * .2).toFixed(1) + '" cy="' + (cy + Math.sin(an) * R * .2).toFixed(1) + '" r="1.5" fill="currentColor" stroke="none"/>'; }
    return s;
  }
  function sLeaf(x, y, len, wid, ang) {
    var s = '<path d="M0 0C' + len * .3 + ' ' + -wid + ',' + len * .72 + ' ' + -wid * .8 + ',' + len + ' 0C' + len * .72 + ' ' + wid * .8 + ',' + len * .3 + ' ' + wid + ',0 0"/><path d="M0 0L' + len + ' 0" opacity=".7"/>';
    [.22, .4, .58, .74].forEach(function (t) { s += '<path d="M' + len * t + ' 0L' + len * (t + .13) + ' ' + -wid * .55 + 'M' + len * t + ' 0L' + len * (t + .13) + ' ' + wid * .55 + '" opacity=".45"/>'; });
    return '<g transform="translate(' + x + ' ' + y + ') rotate(' + ang + ')">' + s + '</g>';
  }
  function sSwallow(x, y, sc, rot) { return '<g transform="translate(' + x + ' ' + y + ') rotate(' + rot + ') scale(' + sc + ')"><path d="M-46 -4Q-22 -26 0 0Q24 -28 50 -12"/><path d="M-30 -9Q-16 -14 0 0Q16 -16 34 -13" opacity=".5"/><path d="M0 0Q3 9 -2 18M-2 18L-8 29M-2 18L3 30"/></g>'; }
  function sRing(x, y, sc) {
    return '<g transform="translate(' + x + ' ' + y + ') scale(' + sc + ')"><ellipse cx="0" cy="44" rx="44" ry="50"/><ellipse cx="0" cy="44" rx="36" ry="42"/>' +
      '<path d="M-18 -14L-10 -27L10 -27L18 -14L0 8ZM-18 -14L18 -14M-10 -27L-6 -14L0 8L6 -14L10 -27M-6 -14L0 -27L6 -14"/><path d="M-15 -6L-19 -18M15 -6L19 -18" opacity=".7"/>' +
      '<path d="M0 -46L0 112M-64 44L64 44" stroke-dasharray="7 5" opacity=".5"/><path d="M-44 112L44 112M-44 106L-44 118M44 106L44 118" opacity=".7"/></g>';
  }
  function sGem(x, y, r) {
    var s = '<circle r="' + r + '"/>', i, k = r * .5, pts = [];
    for (i = 0; i < 8; i++) { var a = i * Math.PI / 4 + Math.PI / 8; pts.push([Math.cos(a) * k, Math.sin(a) * k, a]); }
    s += '<path d="M' + pts.map(function (p) { return p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join('L') + 'Z"/>';
    pts.forEach(function (p) {
      var a1 = p[2] - Math.PI / 8, a2 = p[2] + Math.PI / 8;
      s += '<path d="M' + p[0].toFixed(1) + ' ' + p[1].toFixed(1) + 'L' + (Math.cos(a1) * r).toFixed(1) + ' ' + (Math.sin(a1) * r).toFixed(1) + 'M' + p[0].toFixed(1) + ' ' + p[1].toFixed(1) + 'L' + (Math.cos(a2) * r).toFixed(1) + ' ' + (Math.sin(a2) * r).toFixed(1) + '" opacity=".6"/>';
    });
    return '<g transform="translate(' + x + ' ' + y + ')">' + s + '</g>';
  }
  function sHatch(x, y, len, ang, n, gap) { var s = '', i; for (i = 0; i < n; i++) s += '<path d="M' + (i * gap) + ' ' + (i % 2 ? 3 : 0) + 'l' + (len * (.7 + (i % 3) * .15)) + ' 0"/>'; return '<g transform="translate(' + x + ' ' + y + ') rotate(' + ang + ')" opacity=".45">' + s + '</g>'; }
  function sDandelion(x, y, R, seed) {
    var r = rng(seed), s = '', i;
    for (i = 0; i < 34; i++) {
      var a = i / 34 * 6.283 + r() * .1, l = R * (.82 + r() * .26), ex = Math.cos(a) * l, ey = Math.sin(a) * l;
      s += '<path d="M' + (Math.cos(a) * 5).toFixed(1) + ' ' + (Math.sin(a) * 5).toFixed(1) + 'L' + ex.toFixed(1) + ' ' + ey.toFixed(1) + '" opacity=".7"/>';
      [-.5, 0, .5].forEach(function (o) { s += '<path d="M' + ex.toFixed(1) + ' ' + ey.toFixed(1) + 'l' + (Math.cos(a + o) * 7).toFixed(1) + ' ' + (Math.sin(a + o) * 7).toFixed(1) + '" opacity=".5"/>'; });
    }
    return '<g transform="translate(' + x + ' ' + y + ')">' + s + '<circle r="4"/></g>';
  }
  function sketchWrap(inner, vb) { return '<svg viewBox="' + vb + '" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><g filter="url(#pencil)">' + inner + '</g></svg>'; }

  function art(cls, x, y, w, h, vb, inner, style) {
    return '<svg class="art ' + cls + '" viewBox="' + vb + '" style="left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h + 'px;' + (style || '') + '" aria-hidden="true">' + inner + '</svg>';
  }
  /* Main hero: the client's picture, unchanged, with motion regions, sparkle points and drifting seeds cut from the same picture. */
  var photo = null;
  var HERO = { img: 'assets/img/hero/night-garden.jpg', w: 1672, h: 941, focus: [.68, .5], mouseAmt: .006,
    regions: [
      { g: 'dandelion', x: .215, y: .56, rx: .17, ry: .40, f: .55, type: 'sway', amp: .014, speed: .55, px: .135, py: .93, depth: .6 },
      { g: 'dandelion', x: .245, y: .42, rx: .12, ry: .20, f: .8, type: 'wave', amp: .0016, speed: 1.1, depth: .3 },
      { g: 'seeds', x: .100, y: .31, rx: .05, ry: .085, f: .7, type: 'wave', amp: .003, speed: .8, depth: 1.2 },
      { g: 'seeds', x: .045, y: .42, rx: .05, ry: .09, f: .7, type: 'wave', amp: .003, speed: .7, phase: 1.4, depth: 1.2 },
      { g: 'seeds', x: .390, y: .405, rx: .04, ry: .06, f: .7, type: 'wave', amp: .003, speed: .9, phase: 2.6, depth: 1.2 },
      { g: 'seeds', x: .375, y: .585, rx: .045, ry: .055, f: .7, type: 'wave', amp: .003, speed: .75, phase: 4, depth: 1.2 },
      { g: 'branch', x: .335, y: .165, rx: .15, ry: .16, f: .6, type: 'sway', amp: .012, speed: .45, px: .5, py: .12, phase: 1, depth: .4 },
      { g: 'branch', x: .445, y: .05, rx: .05, ry: .08, f: .6, type: 'sway', amp: .010, speed: .5, px: .49, py: .1, phase: 2.2, depth: .4 },
      { g: 'branch', x: .9, y: .2, rx: .07, ry: .14, f: .6, type: 'sway', amp: .014, speed: .5, px: .88, py: .11, phase: 2, depth: .4 },
      { g: 'branch', x: .9, y: .05, rx: .05, ry: .06, f: .7, type: 'wave', amp: .0015, speed: .9, depth: .3 },
      { g: 'leaves', x: .24, y: .74, rx: .11, ry: .14, f: .6, type: 'sway', amp: .012, speed: .5, px: .225, py: .9, phase: .6, depth: .8 },
      { g: 'leaves', x: .39, y: .84, rx: .10, ry: .13, f: .6, type: 'sway', amp: .010, speed: .4, px: .31, py: .93, phase: 3, depth: .9 },
      { g: 'leaves', x: .81, y: .84, rx: .075, ry: .13, f: .6, type: 'sway', amp: .010, speed: .42, px: .86, py: .93, phase: 1.5, depth: .9 },
      { g: 'leaves', x: .925, y: .72, rx: .075, ry: .15, f: .6, type: 'sway', amp: .012, speed: .48, px: .93, py: .9, phase: 2.2, depth: .8 },
      { g: 'necklace', x: .62, y: .5, rx: .19, ry: .46, f: .35, type: 'sway', amp: .0045, speed: .38, px: .63, py: .08, depth: .9 }
    ],
    sparks: [[1010, 585, 1.4, 4.2, 0], [892, 738, 1.1, 5, 1.7], [1000, 745, 1, 4.6, 3.1], [1190, 765, 1.3, 5.4, .8], [805, 470, .9, 6, 2.4], [1075, 735, 1.2, 4.4, 3.8], [955, 672, .9, 5.2, 1.1], [1188, 660, .8, 6.2, 4.4], [1262, 500, .8, 5.6, 2.9], [760, 318, .6, 6.6, .4], [1345, 318, .6, 7, 3.5]]
      .map(function (p) { return { g: 'sparks', x: p[0] / 1672, y: p[1] / 941, s: p[2], dur: p[3], dl: p[4] }; }),
    floats: [['a', 24, 30, 6.5, 38, -22, 50, 34, 0], ['c', 30, 40, 5.5, 45, -8, -40, 42, 12], ['d', 22, 48, 7, 52, 10, 70, 38, 22], ['b', 18, 24, 7.5, 30, -30, -60, 46, 30], ['c', 28, 36, 4.5, 60, -16, 90, 52, 40]]
      .map(function (f) { return { g: 'floaters', src: 'assets/img/hero/seed-' + f[0] + '.png', x: f[1], y: f[2], w: f[3], dx: f[4], dy: f[5], rot: f[6], dur: f[7], dl: f[8], o: .85 }; })
  };
  function buildHero() {
    var ph = $('#heroPhoto'); if (ph && window.AYHero) photo = window.AYHero.mount(ph, HERO);
    var scene = $('#scene'); if (!scene) return;
    var h = '';
    /* 1 · pencil sketch layer */
    var sk = sFlower(196, 176, 150, 8, 3) + sFlower(404, 84, 62, 30, 9) +
      '<path d="M196 318C182 430,128 540,40 660M196 318C230 400,240 470,236 540M404 142C380 200,330 250,262 286"/>' +
      sLeaf(168, 420, 120, 34, 196) + sLeaf(214, 470, 104, 30, -22) + sLeaf(118, 560, 110, 32, 168) +
      sLeaf(36, 884, 196, 50, -62) + sLeaf(60, 892, 170, 44, -34) + sLeaf(24, 872, 150, 40, -92) + sLeaf(120, 896, 120, 30, -12) +
      sGem(560, 86, 46) + sRing(700, 792, 1.15) + sDandelion(862, 566, 50, 4) + '<path d="M862 618C868 720,850 820,838 900"/>' +
      sSwallow(1250, 84, .95, -8) + sSwallow(1368, 52, .62, 6) + sSwallow(1452, 122, .5, -14) + sSwallow(92, 596, .6, 10) +
      sHatch(470, 196, 44, -32, 7, 7) + sHatch(1480, 836, 50, -28, 8, 7) + sHatch(300, 770, 40, 24, 6, 7) + sGem(1548, 842, 34);
    h += '<div class="dp" style="--d:6"><svg class="art art-sketch" viewBox="0 0 1600 900" style="left:0;top:0;width:1600px;height:900px;color:var(--muted);opacity:.36" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><g filter="url(#pencil)">' + sk + '</g></svg></div>';

    /* 2 · branch group (boughs + leaves + blossoms + hanging jewel), swaying from the right edge */
    var boughA = [[1650, 302], [1520, 262], [1400, 250], [1270, 222], [1140, 190], [1010, 172], [900, 138]];
    var boughB = [[1650, 372], [1560, 430], [1470, 540], [1400, 668], [1320, 790], [1240, 912]];
    var twigA = [[1150, 192], [1080, 262], [990, 330], [880, 398], [770, 452], [700, 486]];
    var twigB = [[1470, 540], [1400, 520], [1330, 470]];
    var twigC = [[1400, 668], [1470, 700], [1530, 690]];
    var br = '<g filter="url(#wc)">' +
      leaf(1010, 170, 150, 40, -128, 'gLeafA') + leaf(1090, 180, 170, 44, -72, 'gLeafA') + leaf(1215, 208, 150, 40, -112, 'gLeafA', .7) + leaf(1290, 226, 180, 46, -58, 'gLeafB', .78) + leaf(950, 150, 120, 30, -160, 'gLeafD', .6) +
      leaf(1000, 322, 120, 34, 150, 'gLeafC', .7) + leaf(900, 386, 110, 30, 100, 'gLeafC', .65) + leaf(1070, 268, 100, 28, 60, 'gLeafC', .6) + leaf(1560, 428, 130, 36, -150, 'gLeafB', .7) + leaf(1380, 700, 120, 32, 170, 'gLeafA', .7) +
      '<path d="' + taperPath(boughB, 26, 9) + '" fill="#705D56" fill-opacity=".86"/><path d="' + taperPath(twigA, 9, 2.5) + '" fill="#705D56" fill-opacity=".8"/><path d="' + taperPath(twigB, 8, 3) + '" fill="#705D56" fill-opacity=".8"/><path d="' + taperPath(twigC, 7, 3) + '" fill="#705D56" fill-opacity=".8"/>' +
      '<path d="' + taperPath(boughA, 32, 6) + '" fill="#705D56" fill-opacity=".9"/><path d="' + taperPath(boughA.map(function (p) { return [p[0], p[1] - 4]; }), 8, 1) + '" fill="#C9B8AE" fill-opacity=".35"/></g>';
    var g = art('art-branch', 0, 0, 1600, 900, '0 0 1600 900', br, '');
    var blossoms = [[1492, 214, 300, 11, 0, 3.2, 7.5], [1384, 446, 280, 23, 0, 2.6, 9], [1528, 668, 230, 31, 1, 3, 8.2], [1296, 792, 170, 44, 1, 3.4, 6.6], [806, 446, 96, 52, 1, 4, 5.8]];
    blossoms.forEach(function (b, i) { g += art('m-sway', b[0] - b[2] / 2, b[1] - b[2] / 2, b[2], b[2], '-112 -112 224 224', blossomSVG(b[3], b[4]), '--a:' + b[5] + 'deg;--dur:' + b[6] + 's;--dl:-' + (i * 1.3) + 's'); });
    [[742, 470, 62, 30], [700, 492, 50, -12], [884, 400, 58, 18], [1330, 462, 70, -40], [1534, 688, 60, 70]].forEach(function (b, i) {
      g += art('m-sway', b[0] - b[2] / 2, b[1] - b[2] / 2, b[2], b[2], '-50 -50 100 100', '<g transform="rotate(' + b[3] + ')">' + budSVG() + '</g>', '--a:4deg;--dur:' + (5 + i * .7) + 's;--dl:-' + i + 's');
    });
    /* hanging jewel: sample image until the client supplies the final layer */
    var hang = function (x, y, w, a, d, dl) { return '<div class="art m-swing" style="left:' + (x - w / 2) + 'px;top:' + y + 'px;width:' + w + 'px;--ox:50%;--oy:0%;--a:' + a + 'deg;--dur:' + d + 's;--dl:' + dl + 's"><span class="thread"></span><img src="' + IMG + 'emerald-kite-drop.webp" alt=""></div>'; };
    g += hang(1010, 176, 150, 1.6, 5.4, 0) + hang(1196, 204, 150, 1.3, 6.3, -2);
    [[1006, 262, 1, 0], [1012, 452, 1.3, 1.4], [1192, 292, .9, 2.6], [1200, 482, 1.3, .7], [972, 560, .8, 3.3], [1236, 420, .7, 2]].forEach(function (p) {
      g += art('m-twinkle', p[0] - 16, p[1] - 16, 32, 32, '-16 -16 32 32', '<g transform="scale(' + p[2] + ')">' + STAR + '</g>', '--dur:' + (3.6 + p[3]) + 's;--dl:-' + p[3] + 's;filter:drop-shadow(0 0 4px #fff)');
    });
    h += '<div class="dp" style="--d:14"><div class="art m-sway" style="inset:0;--ox:100%;--oy:34%;--a:.7deg;--dur:9s">' + g + '</div></div>';

    /* 3 · hummingbird: body bobs, each wing flutters on its own */
    var bird = '<div class="art art-bird m-bob" style="left:440px;top:36px;width:440px;height:330px;--dur:5.5s">' +
      art('m-flutter', 268 - 230, 158 - 230, 260, 260, '-230 -230 260 260', wingSVG(true), '--ox:88.46%;--oy:88.46%;--a:7deg;--dur:1.5s;--dl:-.4s') +
      art('', 0, 0, 440, 330, '0 0 440 330', birdBodySVG(), '') +
      art('m-flutter', 252 - 230, 172 - 230, 260, 260, '-230 -230 260 260', wingSVG(false), '--ox:88.46%;--oy:88.46%;--a:6deg;--dur:1.5s') +
      '</div>';
    h += '<div class="dp" style="--d:24">' + bird + '</div>';

    /* 4 · drifting dandelion seeds and falling petals */
    var fl = '';
    [[820, 560, 26, 0, 520, -260, 60, .55], [860, 540, 34, 9, 460, -330, -40, .5], [300, 700, 30, 4, 620, -200, 50, .45], [880, 590, 40, 17, 380, -140, 80, .5], [560, 820, 28, 12, 700, -420, -30, .4]].forEach(function (s) {
      fl += art('m-drift', s[0], s[1], 40, 60, '0 0 40 60', seedSVG(), 'color:var(--muted);--dur:' + s[2] + 's;--dl:-' + s[3] + 's;--dx:' + s[4] + 'px;--dy:' + s[5] + 'px;--r:' + s[6] + 'deg;--o:' + s[7]);
    });
    [[1420, 300, 21, 0, -150, 540, 260], [1300, 540, 26, 8, -110, 380, -200], [1500, 420, 24, 15, -220, 470, 320], [1180, 250, 30, 4, -90, 600, 180]].forEach(function (p) {
      fl += art('m-fall', p[0], p[1], 30, 30, '-30 -34 60 60', '<path d="M0 20C-20 6,-18 -22,0 -30C18 -22,20 6,0 20Z" fill="url(#gPetalPale)" fill-opacity=".9" filter="url(#wcSoft)"/>', '--dur:' + p[2] + 's;--dl:-' + p[3] + 's;--dx:' + p[4] + 'px;--dy:' + p[5] + 'px;--r:' + p[6] + 'deg');
    });
    h += '<div class="dp" style="--d:34">' + fl + '</div>';
    scene.innerHTML = h;

    /* fit the 1600x900 scene to the hero: cover, anchored to the right edge */
    var box = scene.parentNode;
    var fit = function () { var w = box.clientWidth, hh = box.clientHeight; scene.style.setProperty('--s', Math.max(w / 1600, hh / 900).toFixed(4)); };
    fit(); if ('ResizeObserver' in window) new ResizeObserver(fit).observe(box); else window.addEventListener('resize', fit);

    /* pointer reaction: layers shift by their depth */
    var tx = 0, ty = 0, cx = 0, cy = 0, raf = 0;
    var tick = function () { cx += (tx - cx) * .06; cy += (ty - cy) * .06; scene.style.setProperty('--mx', cx.toFixed(4)); scene.style.setProperty('--my', cy.toFixed(4)); raf = (Math.abs(tx - cx) > .002 || Math.abs(ty - cy) > .002) ? requestAnimationFrame(tick) : 0; };
    var hero = $('#hero');
    hero.addEventListener('pointermove', function (e) { if (!state.mouse || reduced || e.pointerType === 'touch' || state.heroDemo === 'photo') return; var r = hero.getBoundingClientRect(); tx = ((e.clientX - r.left) / r.width - .5) * -1; ty = ((e.clientY - r.top) / r.height - .5) * -1; if (!raf) raf = requestAnimationFrame(tick); });
    hero.addEventListener('pointerleave', function () { tx = 0; ty = 0; if (!raf) raf = requestAnimationFrame(tick); });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { heroVisible = en[0].isIntersecting; heroStill(); }).observe(hero);
  }
  var heroVisible = true;
  function heroStill() { var sc = $('#scene'); if (sc) sc.classList.toggle('is-still', reduced || state.spd <= 0 || !heroVisible || state.heroDemo === 'photo'); }

  /* ====================================================== horizontal portfolio */
  var hsRows = [], hsAutoX = 0, hsLastScroll = 0, hsVisible = false, hsRaf = 0, hsPrev = 0;
  function tile(w) { return '<a class="hs-tile" href="#work-' + w.slug + '"><img src="' + IMG + w.slug + '.webp" alt="' + esc(w.name) + '" loading="lazy"><span class="hs-cap"><strong>' + esc(w.name) + '</strong><span>' + esc(colName(w.col)) + '</span></span></a>'; }
  function buildHscroll() {
    var box = $('#hsRows'); if (!box) return;
    var a = WORKS.concat([WORKS[3]]);                                 /* 11 source tiles */
    var b = WORKS.slice().reverse();                                  /* 10 source tiles */
    [a, b].forEach(function (set) {
      var one = set.map(tile).join('');
      var row = document.createElement('div'); row.className = 'hs-row'; row.innerHTML = one + one + one; row._n = set.length;   /* repeated three times for a seamless loop */
      $$('.hs-tile', row).forEach(function (t, i) { if (i >= set.length) { t.setAttribute('tabindex', '-1'); t.setAttribute('aria-hidden', 'true'); } });
      box.appendChild(row); hsRows.push(row);
    });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { hsVisible = en[0].isIntersecting; if (hsVisible) hsLoop(); }, { rootMargin: '80px' }).observe(box);
  }
  function hsMeasure() { hsRows.forEach(function (row) { var t = row.children; row._sw = t.length > row._n ? t[row._n].offsetLeft - t[0].offsetLeft : 0; }); }
  function hsUpdate() {
    var sec = $('#hscroll'); if (!sec || !hsRows.length || reduced) return;
    var top = sec.getBoundingClientRect().top + window.scrollY;
    var x = (window.scrollY - top + window.innerHeight) * state.hsMult + hsAutoX - 200;
    hsRows.forEach(function (row, i) {
      var sw = row._sw; if (!sw) return;
      var v = (((i % 2 ? -x : x) % sw) + sw) % sw;
      row.style.transform = 'translate3d(' + (v - sw).toFixed(1) + 'px,0,0)';
    });
  }
  /* after the visitor stops scrolling for the chosen number of seconds, the rows keep moving on their own */
  function hsLoop() {
    if (hsRaf) return;
    hsPrev = performance.now();
    var step = function (now) {
      hsRaf = 0;
      if (!hsVisible || reduced || !pageIs('home')) return;
      var dt = Math.min(now - hsPrev, 80) / 1000; hsPrev = now;
      if (state.hsAuto && now - hsLastScroll > state.hsDelay * 1000) { hsAutoX += state.hsSpeed * dt; hsUpdate(); }
      hsRaf = requestAnimationFrame(step);
    };
    hsRaf = requestAnimationFrame(step);
  }

  /* ============================================================ stacked cards */
  function buildStack() {
    var box = $('#stack'); if (!box) return;
    box.innerHTML = COLLECTIONS.map(function (c, i) {
      var count = WORKS.filter(function (w) { return w.col === c.id; }).length;
      var pic = function (slug, cls, flip) { return '<a class="stack-pic ' + cls + '" href="#work-' + slug + '"><img src="' + IMG + slug + '.webp" alt="' + esc(bySlug[slug].name) + '" loading="lazy"' + (flip ? ' style="transform:scaleX(-1)"' : '') + '></a>'; };
      return '<article class="stack-card" style="--i:' + i + '"><header class="stack-head"><span class="stack-num">' + ('0' + (i + 1)).slice(-2) + '</span><h3>' + esc(c.name) + '</h3><span class="stack-meta">Collection · ' + count + ' pieces</span>' +
        '<a class="tlink" href="#collections" data-col="' + c.id + '">View collection <span class="arr" aria-hidden="true"></span></a></header>' +
        '<div class="stack-media"><div class="stack-small">' + pic(c.small[0], '') + pic(c.small[1], '', c.small[0] === c.small[1]) + '</div>' + pic(c.tall, 'stack-tall') + '</div></article>';
    }).join('');
    box.addEventListener('click', function (e) { var a = e.target.closest('[data-col]'); if (a) { fState.col = a.getAttribute('data-col'); fState.type = 'all'; renderFilters(); renderGrid(); } });
  }
  function stackUpdate() {
    var box = $('#stack'); if (!box || reduced) return;
    var cards = $$('.stack-card', box);
    if (!state.stackOn) { cards.forEach(function (c) { c.style.transform = ''; }); return; }
    var base = parseFloat(getComputedStyle(root).getPropertyValue('--stack-top')) || 128, span = window.innerHeight * .85;
    var tops = cards.map(function (c) { return c.getBoundingClientRect().top; });
    cards.forEach(function (c, i) {
      var k = 0, j;
      for (j = i + 1; j < cards.length; j++) k += clamp(1 - (tops[j] - (base + j * state.stackGap)) / span, 0, 1);
      c.style.transform = 'scale(' + Math.max(state.stackMin, 1 - k * state.stackStep).toFixed(4) + ')';   /* never below the minimum scale */
    });
  }

  /* ================================================================ timeline */
  function tlUpdate() {
    var tl = $('#tl'); if (!tl || !pageIs('about')) return;
    var r = tl.getBoundingClientRect(), line = window.innerHeight * .62;
    tl.style.setProperty('--tl', reduced ? 1 : clamp((line - r.top - 8) / (r.height - 16), 0, 1).toFixed(4));
    $$('.tl-item', tl).forEach(function (it) { it.classList.toggle('is-on', reduced || it.getBoundingClientRect().top + 16 < line); });
  }

  /* ============================================================== collections */
  var fState = { col: 'all', type: 'all' };
  function fiBtn(group, val, label) { var on = fState[group] === val; return '<button type="button" class="fi' + (on ? ' is-on' : '') + '" data-g="' + group + '" data-v="' + esc(val) + '" aria-pressed="' + on + '"><span class="fi-t" data-t="' + esc(label) + '">' + esc(label) + '</span></button>'; }
  function renderFilters() {
    var c = $('#fCollection'), t = $('#fType'); if (!c) return;
    c.innerHTML = fiBtn('col', 'all', 'All') + COLLECTIONS.map(function (x) { return fiBtn('col', x.id, x.name); }).join('');
    t.innerHTML = fiBtn('type', 'all', 'All') + TYPES.map(function (x) { return fiBtn('type', x, x); }).join('');
  }
  function card(w, n) { return '<a class="card" style="--n:' + n + '" href="#work-' + w.slug + '"><span class="card-pic"><img src="' + IMG + w.slug + '.webp" alt="' + esc(w.name) + '" loading="lazy"></span><span class="card-txt"><span class="card-name">' + esc(w.name) + '</span><span class="card-meta">' + esc(colName(w.col)) + ' · ' + esc(w.type) + '</span></span></a>'; }
  function renderGrid() {
    var g = $('#grid'); if (!g) return;
    var list = WORKS.filter(function (w) { return (fState.col === 'all' || w.col === fState.col) && (fState.type === 'all' || w.type === fState.type); });
    g.innerHTML = list.map(card).join('');
    $('#gridEmpty').hidden = list.length > 0;
    $('#count').textContent = list.length + (list.length === 1 ? ' piece' : ' pieces');
  }

  /* ============================================================== single work */
  function renderWork(w) {
    var box = $('#work'); if (!box) return;
    var sibs = WORKS.filter(function (x) { return x.col === w.col && x.slug !== w.slug; });
    var gallery = [w.slug].concat(sibs.slice(0, 3).map(function (x) { return x.slug; }));
    var i = WORKS.indexOf(w), prev = WORKS[(i - 1 + WORKS.length) % WORKS.length], next = WORKS[(i + 1) % WORKS.length];
    var specs = [['Collection', colName(w.col)], ['Type', w.type], ['Materials', w.materials], ['Gemstones', w.gemstones], ['Technique', w.technique], ['Year', w.year]].concat(w.extra || []);
    box.innerHTML =
      '<div class="work-main"><div class="gal"><div class="gal-stage"><img id="galMain" src="' + IMG + w.slug + '.webp" alt="' + esc(w.name) + '"></div>' +
      '<div class="gal-thumbs">' + gallery.map(function (s, k) { return '<button type="button" class="gal-thumb' + (k ? '' : ' is-on') + '" data-src="' + s + '" aria-label="Show image ' + (k + 1) + '"><img src="' + IMG + s + '.webp" alt=""></button>'; }).join('') + '</div></div>' +
      '<div class="work-info"><p class="crumbs"><a href="#collections">Collections</a> / ' + esc(colName(w.col)) + '</p><h1>' + esc(w.name) + '</h1><p class="work-desc">' + esc(w.desc) + '</p>' +
      '<ul class="specs">' + specs.map(function (s) { return '<li class="spec"><span class="k">' + esc(s[0]) + '</span><span class="v">' + esc(s[1]) + '</span></li>'; }).join('') + '</ul>' +
      '<p class="sample" dir="rtl" lang="fa">ورودی نمونه: عکس از بستهٔ سوگند است و متن و مشخصات موقت‌اند. گالری تا رسیدن عکس‌های واقعی، قطعه‌های هم‌کالکشن را نشان می‌دهد.</p>' +
      '<nav class="work-nav" aria-label="Other pieces"><a href="#work-' + prev.slug + '"><span class="eyebrow">Previous</span><strong>' + esc(prev.name) + '</strong></a><a href="#work-' + next.slug + '"><span class="eyebrow">Next</span><strong>' + esc(next.name) + '</strong></a></nav></div></div>' +
      '<section class="related" aria-labelledby="relT"><div class="sec-head"><p class="eyebrow">' + esc(colName(w.col)) + ' collection</p><h2 id="relT">More from this collection</h2></div><div class="grid">' + (sibs.length ? sibs : WORKS.filter(function (x) { return x.slug !== w.slug; }).slice(0, 3)).slice(0, 3).map(card).join('') + '</div></section>';
  }

  /* ===================================================================== FAQ */
  function buildFaq() {
    var box = $('#faq'), data = window.AY_FAQ || []; if (!box) return;
    var groups = [], idx = {};
    data.forEach(function (it) { if (!(it.group in idx)) { idx[it.group] = groups.length; groups.push({ title: it.group, items: [] }); } groups[idx[it.group]].items.push(it); });
    var h = '<div class="faq-search"><span class="ic" data-ic="search" aria-hidden="true"></span><label class="sr" for="faqQ">Search questions</label><input id="faqQ" type="search" placeholder="Search questions" autocomplete="off"></div>';
    h += '<div class="faq-nav" role="group" aria-label="Topics">' + groups.map(function (g, i) { return '<button type="button" class="faq-topic' + (i ? '' : ' is-on') + '" data-g="' + i + '" aria-pressed="' + (!i) + '" aria-controls="faqG' + i + '"><span>' + esc(g.title) + '</span><em>' + g.items.length + '</em></button>'; }).join('') + '</div>';
    h += '<div class="faq-groups">' + groups.map(function (g, i) {
      return '<div class="faq-group' + (i ? '' : ' is-on') + '" id="faqG' + i + '"><h3 class="faq-gt">' + esc(g.title) + '</h3>' + g.items.map(function (it, k) {
        return '<details class="faq-item" name="ay-faq"' + (!i && !k ? ' open' : '') + '><summary><span class="faq-q">' + esc(it.q) + '</span><span class="faq-ic" aria-hidden="true"></span></summary><div class="faq-a"><p>' + esc(it.a) + '</p></div></details>';
      }).join('') + '</div>';
    }).join('') + '</div><p class="faq-none" hidden>No questions match your search.</p>';
    box.innerHTML = h;
    box.addEventListener('click', function (e) {
      var b = e.target.closest('.faq-topic'); if (!b) return;
      var g = b.getAttribute('data-g');
      $$('.faq-topic', box).forEach(function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on); });
      $$('.faq-group', box).forEach(function (x) { x.classList.toggle('is-on', x.id === 'faqG' + g); });
    });
    box.addEventListener('toggle', function (e) {   /* only one answer open at a time (also where <details name> is not supported) */
      var d = e.target; if (!d.open || !d.classList || !d.classList.contains('faq-item')) return;
      $$('.faq-item[open]', box).forEach(function (o) { if (o !== d) o.open = false; });
    }, true);
    $('#faqQ').addEventListener('input', function (e) { faqFilter(e.target.value); });
  }
  function faqFilter(q) {
    var box = $('#faq'); if (!box) return;
    q = (q || '').trim().toLowerCase();
    box.classList.toggle('is-searching', !!q);
    var any = false;
    $$('.faq-group', box).forEach(function (g) {
      var hit = 0;
      $$('.faq-item', g).forEach(function (it) { var m = !q || it.textContent.toLowerCase().indexOf(q) > -1; if (m) { it.removeAttribute('data-miss'); hit++; } else it.setAttribute('data-miss', ''); });
      if (hit) { g.removeAttribute('data-miss'); any = true; } else g.setAttribute('data-miss', '');
    });
    var none = $('.faq-none', box); if (none) none.hidden = any;
  }

  /* ==================================================================== router */
  var PAGES = ['home', 'collections', 'about', 'contact'];
  var current = 'home';
  function pageIs(p) { return current === p; }
  function route(first) {
    var h = (location.hash || '').replace(/^#/, ''), page = h || 'home', w = null;
    if (h.indexOf('work-') === 0) { w = bySlug[h.slice(5)]; page = w ? 'work' : 'notfound'; }
    else if (PAGES.indexOf(page) < 0) page = 'notfound';
    current = page;
    $$('.page').forEach(function (p) { p.hidden = p.getAttribute('data-page') !== page; });
    $$('[data-nav]').forEach(function (a) { var on = a.getAttribute('data-nav') === page || (page === 'work' && a.getAttribute('data-nav') === 'collections'); a.classList.toggle('is-on', on); if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current'); });
    if (w) renderWork(w);
    document.title = 'Arezoo Yousefi Preview';
    if (!first) { window.scrollTo(0, 0); var m = $('#main'); if (m) m.focus({ preventScroll: true }); }
    requestAnimationFrame(function () { hsMeasure(); onScroll(); if (page === 'home' && hsVisible) hsLoop(); });
  }

  /* ============================================================ scroll engine */
  var ticking = false;
  function onScroll() {
    hsUpdate(); stackUpdate(); tlUpdate();
    var f = $('.ftr-card'), d = $('#dock');
    if (f && d) d.classList.toggle('is-away', f.getBoundingClientRect().top < window.innerHeight - 90);
  }
  window.addEventListener('scroll', function () { hsLastScroll = performance.now(); if (!ticking) { ticking = true; requestAnimationFrame(function () { ticking = false; onScroll(); }); } }, { passive: true });
  window.addEventListener('resize', function () { hsMeasure(); onScroll(); });

  /* ================================================================= 3D demos */
  function bindTilt(el, max) {
    if (!el) return;
    el.addEventListener('pointermove', function (e) {
      if (reduced) return;
      var r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
      el.classList.add('is-live');
      el.style.setProperty('--ry', ((x - .5) * max).toFixed(2) + 'deg'); el.style.setProperty('--rx', ((.5 - y) * max).toFixed(2) + 'deg');
      el.style.setProperty('--gx', (x * 100).toFixed(1) + '%'); el.style.setProperty('--gy', (y * 100).toFixed(1) + '%'); el.style.setProperty('--go', '.75');
    });
    var reset = function () { el.classList.remove('is-live'); ['--rx', '--ry', '--gx', '--gy', '--go'].forEach(function (k) { el.style.removeProperty(k); }); };
    el.addEventListener('pointerleave', reset); el.addEventListener('blur', reset);
    el.addEventListener('focus', function () { if (!reduced) { el.style.setProperty('--ry', '-9deg'); el.style.setProperty('--rx', '6deg'); } });
  }

  /* ========================================================== preview controls */
  var SECTIONS = [
    { t: 'تم رنگی (۹۶ تم)', themes: true },
    { t: 'هیرو', go: ['home', 'hero'], rows: [
      ['select', 'heroDemo', 'دموی هیرو', [['photo', '۱ · عکس شما (پیش‌فرض)'], ['paint', '۲ · هیروی روشن آبرنگی']]],
      ['range', 'spd', 'سرعت کلی حرکت', 0, 2, .05, '×'], ['range', 'amp', 'شدت کلی حرکت', 0, 3, .1, '×'],
      ['range', 'kDandelion', 'قاصدک و دانه‌ها', 0, 3, .1, '×'], ['range', 'kBranch', 'شاخه، برگ‌ها و گل‌ها', 0, 3, .1, '×'], ['range', 'kNecklace', 'گردنبند', 0, 3, .1, '×'],
      ['switch', 'sparks', 'برق نگین‌ها'], ['switch', 'floats', 'دانه‌های شناور'], ['switch', 'mouse', 'واکنش به حرکت موس'], ['switch', 'outline', 'نمایش ناحیه‌های حرکت']],
      hint: 'سرعت کلی صفر یعنی همان عکس، بدون هیچ حرکتی. «نمایش ناحیه‌ها» بیضی‌هایی را نشان می‌دهد که در ویجت هیرو جابه‌جا و تنظیم می‌شوند. کنترل‌های شدت فقط برای دموی ۱ هستند.' },
    { t: 'هدر و نوار پایین: شیشه', rows: [
      ['range', 'hdrBlur', 'تاری هدر', 0, 30, 1, 'px'], ['range', 'hdrOp', 'پوشش رنگ هدر', 0, 1, .02, ''], ['range', 'dockBlur', 'تاری نوار پایین', 0, 30, 1, 'px'], ['range', 'dockOp', 'پوشش رنگ نوار پایین', 0, 1, .02, '']] },
    { t: 'پورتفولیوی افقی', go: ['home', 'hscroll'], rows: [
      ['range', 'hsMult', 'ضریب حرکت با اسکرول', .05, .8, .05, '×'], ['switch', 'hsAuto', 'حرکت خودکار پس از توقف اسکرول'], ['range', 'hsDelay', 'تأخیر شروع خودکار', 0, 10, .5, 's'], ['range', 'hsSpeed', 'سرعت حرکت خودکار', 10, 200, 5, 'px/s']],
      hint: 'اسکرول کنید و بعد دست نگه دارید: پس از چند ثانیه‌ای که این‌جا تعیین می‌کنید ردیف‌ها خودشان حرکت می‌کنند.' },
    { t: 'کارت‌های روی‌هم', go: ['home', 'stack'], rows: [
      ['switch', 'stackOn', 'فعال'], ['range', 'stackStep', 'کوچک شدن هر لایه', 0, .08, .005, ''], ['range', 'stackGap', 'فاصلهٔ لایه‌ها', 0, 60, 2, 'px'], ['range', 'stackMin', 'کمترین مقیاس', .6, 1, .01, '']] },
    { t: 'دسته‌بندی‌ها: موشن متن', go: ['collections'], rows: [
      ['select', 'fx', 'نوع موشن', [['line', 'خط از چپ'], ['ink', 'خط موج‌دار دستی'], ['space', 'باز شدن حروف و نقطه'], ['shimmer', 'عبور نور از حروف'], ['roll', 'غلتیدن واژه']]]],
      hint: 'دسته‌بندی‌ها فقط متن‌اند و باکس پشتی ندارند.' },
    { t: 'سوالات متداول', go: ['contact', 'faqSec'], rows: [
      ['select', 'faqLayout', 'چیدمان (۵ دمو)', [['accordion', '۱ · آکاردئون ساده'], ['sidebar', '۲ · فهرست موضوع کنار پاسخ‌ها'], ['columns', '۳ · دو ستونه'], ['cards', '۴ · کارت‌های شیشه‌ای'], ['tabs', '۵ · تب‌های افقی']]], ['switch', 'faqSearch', 'کادر جستجو']] },
    { t: 'صفحهٔ تک‌اثر: باکس مشخصات', go: ['work-ruby-ribbon-necklace'], rows: [
      ['switch', 'wcOn', 'پترن آبرنگی کمرنگ'], ['color', 'wcColor', 'رنگ پترن'], ['range', 'wcOp', 'شدت پترن', .05, .9, .01, '']] }
  ];
  function buildPanel() {
    var body = $('#pvBody'); if (!body) return;
    var h = '';
    SECTIONS.forEach(function (sec, si) {
      h += '<section class="pv-sec"><h4><span>' + sec.t + '</span>' + (sec.go ? '<button type="button" class="pv-go" data-go="' + sec.go.join(',') + '">نمایش در صفحه</button>' : '') + '</h4>';
      if (sec.themes) {
        h += '<p class="pv-cur" id="pvCur"></p>';
        SETS.forEach(function (set) {
          h += '<p class="pv-set">' + set.fa + '</p>';
          FAMILIES.filter(function (f) { return f.set === set.id; }).forEach(function (f) {
            h += '<p class="pv-fam">' + f.fa + '</p><div class="pv-themes">' + THEMES.filter(function (t) { return t.fam === f.id; }).map(function (t) {
              return '<button type="button" class="pv-th" data-id="' + t.id + '" title="' + t.fa + '" aria-label="' + t.fa + '" style="--b:' + t.vars['--bg'] + ';--s:' + t.vars['--surface-2'] + ';--bt:' + t.vars['--btn-bg'] + ';--a:' + t.vars['--accent'] + '"><i></i></button>';
            }).join('') + '</div>';
          });
        });
        h += '<p class="pv-hint">هر مربع یک تم است: زمینه، سطح، دکمه و رنگ تأکید. تم پیش‌فرض دموی اصلی «سرمه‌ای شب ۰۱» است.</p>';
      }
      (sec.rows || []).forEach(function (r) {
        var id = 'pv_' + r[1];
        if (r[0] === 'range') h += '<div class="pv-row"><label for="' + id + '">' + r[2] + '<output id="' + id + '_o"></output></label><input type="range" id="' + id + '" data-k="' + r[1] + '" data-u="' + r[6] + '" min="' + r[3] + '" max="' + r[4] + '" step="' + r[5] + '"></div>';
        if (r[0] === 'switch') h += '<div class="pv-row"><label for="' + id + '">' + r[2] + '</label><span class="pv-switch"><input type="checkbox" id="' + id + '" data-k="' + r[1] + '"></span></div>';
        if (r[0] === 'select') h += '<div class="pv-row"><label for="' + id + '">' + r[2] + '</label><select id="' + id + '" data-k="' + r[1] + '">' + r[3].map(function (o) { return '<option value="' + o[0] + '">' + o[1] + '</option>'; }).join('') + '</select></div>';
        if (r[0] === 'color') h += '<div class="pv-row"><label for="' + id + '">' + r[2] + '</label><input type="color" id="' + id + '" data-k="' + r[1] + '"></div>';
      });
      if (sec.hint) h += '<p class="pv-hint">' + sec.hint + '</p>';
      h += '</section>';
    });
    h += '<button type="button" class="pv-reset" id="pvReset">بازگشت همه به پیش‌فرض</button>';
    body.innerHTML = h;
    var sync = function () {
      $$('[data-k]', body).forEach(function (el) {
        var k = el.getAttribute('data-k');
        if (el.type === 'checkbox') el.checked = !!state[k]; else el.value = state[k];
        var o = $('#' + el.id + '_o'); if (o) o.textContent = state[k] + (el.getAttribute('data-u') || '');
      });
    };
    sync();
    body.addEventListener('input', function (e) {
      var el = e.target, k = el.getAttribute && el.getAttribute('data-k'); if (!k) return;
      state[k] = el.type === 'checkbox' ? el.checked : (el.type === 'range' ? parseFloat(el.value) : el.value);
      sync(); applyState(); save();
    });
    body.addEventListener('click', function (e) {
      var th = e.target.closest('.pv-th'); if (th) { state.theme = th.getAttribute('data-id'); applyTheme(); save(); return; }
      var go = e.target.closest('.pv-go');
      if (go) {
        var a = go.getAttribute('data-go').split(',');
        if (location.hash.replace('#', '') !== a[0] && !(a[0] === 'home' && !location.hash)) location.hash = a[0];
        if (window.matchMedia('(max-width:760px)').matches) togglePanel(false);
        setTimeout(function () { var t = a[1] && document.getElementById(a[1]); if (t) window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - 90, behavior: reduced ? 'auto' : 'smooth' }); }, 120);
        return;
      }
      if (e.target.id === 'pvReset') { Object.keys(DEFAULTS).forEach(function (k) { state[k] = DEFAULTS[k]; }); sync(); applyTheme(); applyState(); save(); }
    });
  }
  function togglePanel(open) {
    var p = $('#pvPanel'), t = $('#pvTab'); if (!p) return;
    if (open === undefined) open = p.hidden;
    p.hidden = !open; t.setAttribute('aria-expanded', open); t.hidden = open;
    if (open) $('#pvClose').focus(); else t.focus();
  }

  /* ====================================================================== init */
  function init() {
    paintIcons(document);
    buildHero(); buildHscroll(); buildStack(); renderFilters(); renderGrid(); buildFaq(); buildPanel();
    paintIcons(document);
    var pa = $('#portraitArt'); if (pa) pa.innerHTML = sketchWrap(sFlower(92, 120, 74, 12, 6) + sLeaf(150, 360, 120, 32, -60) + sLeaf(120, 380, 100, 28, -120) + sSwallow(300, 96, .7, -8) + sSwallow(340, 150, .45, 8) + sDandelion(318, 420, 44, 3) + '<path d="M318 466C324 500,316 520,310 540M92 196C100 280,120 330,136 372"/>' + sGem(64, 470, 30), '0 0 400 500');
    var nf = $('#nfArt'); if (nf) nf.innerHTML = sketchWrap(sRing(120, 70, 1) + sGem(300, 90, 46) + sSwallow(230, 30, .6, -10) + sHatch(250, 170, 40, -30, 7, 7), '0 0 400 200');
    applyTheme(); applyState();

    $('#filters').addEventListener('click', function (e) { var b = e.target.closest('.fi'); if (!b) return; fState[b.getAttribute('data-g')] = b.getAttribute('data-v'); renderFilters(); renderGrid(); });
    $('#work').addEventListener('click', function (e) { var b = e.target.closest('.gal-thumb'); if (!b) return; var m = $('#galMain'); m.src = IMG + b.getAttribute('data-src') + '.webp'; m.style.animation = 'none'; void m.offsetWidth; m.style.animation = ''; $$('.gal-thumb').forEach(function (x) { x.classList.toggle('is-on', x === b); }); });
    $('#toTop').addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' }); });
    $('#skipLink').addEventListener('click', function (e) { e.preventDefault(); $('#main').focus(); });
    $('#copyMail').addEventListener('click', function () {
      var b = this, txt = b.getAttribute('data-copy'), done = function () { b.textContent = 'Copied'; setTimeout(function () { b.textContent = 'Copy'; }, 1800); };
      var fallback = function () { var r = document.createRange(); r.selectNodeContents($('#mailVal')); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); b.textContent = 'Selected'; setTimeout(function () { b.textContent = 'Copy'; }, 1800); };
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done, fallback); else fallback();
    });
    $('#pvTab').addEventListener('click', function () { togglePanel(true); });
    $('#pvClose').addEventListener('click', function () { togglePanel(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !$('#pvPanel').hidden) togglePanel(false); });
    bindTilt($('#tiltCard'), 22); bindTilt($('#tiltBtn'), 26);

    window.addEventListener('hashchange', function () { route(false); });
    route(true);
    window.addEventListener('load', function () { hsMeasure(); onScroll(); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
