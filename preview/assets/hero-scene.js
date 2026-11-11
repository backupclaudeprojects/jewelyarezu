/* Arezoo hero scene — brings one flat picture to life without altering it.
   The picture is drawn on a WebGL canvas; soft elliptical "motion regions" sway, ripple or breathe, small
   sparkle points twinkle and sprite "floaters" drift across. With every strength at zero, or with motion
   switched off, the result is pixel-identical to the picture. No dependencies.

   Usage: AYHero.mount(element, config) -> { update(partialConfig), destroy() }
   config = {
     img, w, h,                     picture URL and its natural size
     focus: [x, y],                 0..1, which part stays in view when the box crops the picture
     speed, amp, mouse, mouseAmt,   overall speed / strength multipliers, pointer reaction on/off and amount
     regions: [{ g, x, y, rx, ry, f, type, amp, speed, phase, px, py, depth }],
                                    g = group name, x/y centre, rx/ry radii, f feather (0..1), all in 0..1 of the picture;
                                    type 'sway' (turns around the pivot px/py), 'wave' (ripple), 'breathe' (scale)
     sparks: [{ g, x, y, s, dur, dl }],
     floats: [{ g, src, x, y, w, dx, dy, rot, dur, dl, o }],   positions and travel in % of the picture
     groups: { name: { on, k, s } }  per-group switch, strength and speed multipliers
     outline: bool                   draw the region outlines (editor aid)
   } */
(function () {
  'use strict';
  var MAXR = 16;
  var VS = 'attribute vec2 p;varying vec2 v;void main(){v=p*.5+.5;v.y=1.-v.y;gl_Position=vec4(p,0.,1.);}';
  var FS = [
    '#ifdef GL_FRAGMENT_PRECISION_HIGH', 'precision highp float;', '#else', 'precision mediump float;', '#endif',
    'varying vec2 v;uniform sampler2D tex;uniform float asp;uniform vec2 ms;uniform int n;',
    'uniform vec4 A[' + MAXR + '];uniform vec4 B[' + MAXR + '];uniform vec4 C[' + MAXR + '];',
    'void main(){vec2 d=ms*.35;vec2 k=vec2(asp,1.);',
    'for(int i=0;i<' + MAXR + ';i++){if(i>=n)break;',
    ' vec2 c=A[i].xy;float q=length((v-c)/A[i].zw);float m=1.-smoothstep(1.-C[i].x,1.,q);if(m<=0.)continue;',
    ' float ph=B[i].w;float amp=B[i].z;',
    ' if(C[i].y<.5){vec2 o=(v-B[i].xy)*k;float a=amp*sin(ph)*m;float s=sin(a);float co=cos(a);d+=(vec2(o.x*co-o.y*s,o.x*s+o.y*co)-o)/k;}',
    ' else if(C[i].y<1.5){d+=amp*m*vec2(sin(ph+v.y*18.),cos(ph*.8+v.x*14.))/k;}',
    ' else{d+=(v-c)*amp*sin(ph)*m;}',
    ' d+=ms*C[i].w*m;}',
    'gl_FragColor=texture2D(tex,clamp(v-d,0.,1.));}'
  ].join('\n');
  var TYPES = { sway: 0, wave: 1, breathe: 2 };
  var reduced = function () { return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches; };
  function merge(a, b) { var o = {}, k; for (k in a) o[k] = a[k]; for (k in b) o[k] = b[k]; return o; }

  function mount(el, cfg) {
    cfg = merge({ focus: [.5, .5], speed: 1, amp: 1, mouse: true, mouseAmt: .006, regions: [], sparks: [], floats: [], groups: {} }, cfg || {});
    el.classList.add('ay-hs');
    var stage = document.createElement('div'); stage.className = 'ay-hs-stage';
    var img = document.createElement('img'); img.className = 'ay-hs-img'; img.alt = cfg.alt || ''; img.decoding = 'async'; img.src = cfg.img;
    var over = document.createElement('div'); over.className = 'ay-hs-over';
    stage.appendChild(img); el.appendChild(stage);
    var canvas = null, gl = null, prog = null, U = {}, tex = null, ready = false;
    var phases = [], last = 0, raf = 0, visible = true, mx = 0, my = 0, tx = 0, ty = 0, dead = false;

    function grp(g) { var x = cfg.groups[g]; return x ? { on: x.on !== false && x.on !== 0, k: x.k == null ? 1 : x.k, s: x.s == null ? 1 : x.s } : { on: true, k: 1, s: 1 }; }
    function still() { return reduced() || cfg.speed <= 0; }

    /* --- size the stage so the picture covers the box, keeping the focus point in view --- */
    function fit() {
      var W = el.clientWidth, H = el.clientHeight; if (!W || !H) return;
      var sc = Math.max(W / cfg.w, H / cfg.h), sw = cfg.w * sc, sh = cfg.h * sc;
      stage.style.width = sw.toFixed(1) + 'px'; stage.style.height = sh.toFixed(1) + 'px';
      stage.style.left = ((W - sw) * cfg.focus[0]).toFixed(1) + 'px'; stage.style.top = ((H - sh) * cfg.focus[1]).toFixed(1) + 'px';
      stage.style.setProperty('--ay-sw', sw.toFixed(1) + 'px');
      if (canvas) {
        var dpr = Math.min(window.devicePixelRatio || 1, 2), cw = Math.min(Math.round(sw * dpr), 2560), ch = Math.round(cw * cfg.h / cfg.w);
        if (canvas.width !== cw || canvas.height !== ch) { canvas.width = cw; canvas.height = ch; gl.viewport(0, 0, cw, ch); }
        draw();
      }
    }

    /* --- WebGL --- */
    function initGL() {
      if (ready || !img.naturalWidth || (still() && !cfg.forceCanvas)) return;   /* never before the picture has loaded */
      canvas = document.createElement('canvas'); canvas.className = 'ay-hs-canvas'; canvas.setAttribute('aria-hidden', 'true');
      try { gl = canvas.getContext('webgl', { premultipliedAlpha: false, antialias: false }) || canvas.getContext('experimental-webgl'); } catch (e) { gl = null; }
      if (!gl) { canvas = null; return; }
      var sh = function (type, src) { var s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s); return gl.getShaderParameter(s, gl.COMPILE_STATUS) ? s : null; };
      var vs = sh(gl.VERTEX_SHADER, VS), fs = sh(gl.FRAGMENT_SHADER, FS);
      if (!vs || !fs) { gl = null; canvas = null; return; }
      prog = gl.createProgram(); gl.attachShader(prog, vs); gl.attachShader(prog, fs); gl.linkProgram(prog);
      if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { gl = null; canvas = null; return; }
      gl.useProgram(prog);
      var buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf); gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
      var loc = gl.getAttribLocation(prog, 'p'); gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
      ['asp', 'ms', 'n', 'A', 'B', 'C', 'tex'].forEach(function (k) { U[k] = gl.getUniformLocation(prog, k); });
      tex = gl.createTexture(); gl.bindTexture(gl.TEXTURE_2D, tex);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
      try { gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img); } catch (e) { gl = null; canvas = null; return; }   /* cross-origin picture: stay with the still image */
      if (gl.getError()) { gl = null; canvas = null; return; }
      gl.uniform1i(U.tex, 0); gl.uniform1f(U.asp, cfg.w / cfg.h);
      stage.insertBefore(canvas, img.nextSibling);
      ready = true; el.classList.add('is-live');
      fit(); loop();
    }
    function draw() {
      if (!ready) return;
      var A = new Float32Array(MAXR * 4), B = new Float32Array(MAXR * 4), C = new Float32Array(MAXR * 4), n = 0;
      cfg.regions.forEach(function (r, i) {
        var g = grp(r.g); if (!g.on || n >= MAXR) return;
        var o = n * 4;
        A[o] = r.x; A[o + 1] = r.y; A[o + 2] = Math.max(r.rx, .001); A[o + 3] = Math.max(r.ry, .001);
        B[o] = r.px == null ? r.x : r.px; B[o + 1] = r.py == null ? r.y : r.py; B[o + 2] = (r.amp || 0) * cfg.amp * g.k; B[o + 3] = (phases[i] || 0) + (r.phase || 0);
        C[o] = Math.min(Math.max(r.f == null ? .6 : r.f, .01), 1); C[o + 1] = TYPES[r.type] || 0; C[o + 2] = 0; C[o + 3] = r.depth || 0;
        n++;
      });
      gl.uniform4fv(U.A, A); gl.uniform4fv(U.B, B); gl.uniform4fv(U.C, C); gl.uniform1i(U.n, n);
      gl.uniform2f(U.ms, mx * cfg.mouseAmt, my * cfg.mouseAmt);
      gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
    }
    function loop() {
      if (raf || dead || !ready) return;
      last = performance.now();
      var step = function (now) {
        raf = 0; if (dead || !visible || document.hidden || still()) return;
        var dt = Math.min(now - last, 80) / 1000; last = now;
        cfg.regions.forEach(function (r, i) { phases[i] = (phases[i] || 0) + dt * (r.speed || 1) * cfg.speed * grp(r.g).s; });
        mx += (tx - mx) * .05; my += (ty - my) * .05;
        draw(); raf = requestAnimationFrame(step);
      };
      raf = requestAnimationFrame(step);
    }

    /* --- sparkle points, drifting sprites and (editor) outlines, all placed in % of the picture --- */
    function overlays() {
      var h = '';
      cfg.sparks.forEach(function (s) {
        var g = grp(s.g); if (!g.on) return;
        h += '<i class="ay-hs-spark" style="left:' + s.x * 100 + '%;top:' + s.y * 100 + '%;--sz:' + (s.s || 1) * 1.5 * g.k + ';--dur:' + ((s.dur || 4) / Math.max(g.s * cfg.speed, .05)).toFixed(2) + 's;--dl:-' + (s.dl || 0) + 's"></i>';
      });
      cfg.floats.forEach(function (f) {
        var g = grp(f.g); if (!g.on) return;
        h += '<img class="ay-hs-float" alt="" src="' + f.src + '" style="left:' + f.x + '%;top:' + f.y + '%;width:' + f.w + '%;--dx:' + (f.dx || 0) / 100 + ';--dy:' + (f.dy || 0) / 100 + ';--rot:' + (f.rot || 0) + 'deg;--o:' + (f.o == null ? .9 : f.o) + ';--dur:' + ((f.dur || 30) / Math.max(g.s * cfg.speed, .05)).toFixed(2) + 's;--dl:-' + (f.dl || 0) + 's">';
      });
      if (cfg.outline) cfg.regions.forEach(function (r, i) {
        h += '<span class="ay-hs-out" style="left:' + (r.x - r.rx) * 100 + '%;top:' + (r.y - r.ry) * 100 + '%;width:' + r.rx * 200 + '%;height:' + r.ry * 200 + '%"><b>' + (i + 1) + '</b></span>';
        if (r.type === 'sway' || !r.type) h += '<span class="ay-hs-piv" style="left:' + (r.px == null ? r.x : r.px) * 100 + '%;top:' + (r.py == null ? r.y : r.py) * 100 + '%"></span>';
      });
      over.innerHTML = h;
      el.classList.toggle('is-still', still());
    }

    function onMove(e) { if (!cfg.mouse || e.pointerType === 'touch' || still()) return; var r = el.getBoundingClientRect(); tx = ((e.clientX - r.left) / r.width - .5) * 2; ty = ((e.clientY - r.top) / r.height - .5) * 2; loop(); }
    function onLeave() { tx = 0; ty = 0; }
    var io = null, ro = null;
    function start() {
      if (dead) return;
      cfg.w = cfg.w || img.naturalWidth; cfg.h = cfg.h || img.naturalHeight;
      stage.appendChild(over); overlays(); fit(); initGL();
      el.addEventListener('pointermove', onMove); el.addEventListener('pointerleave', onLeave);
      if ('ResizeObserver' in window) { ro = new ResizeObserver(fit); ro.observe(el); } else window.addEventListener('resize', fit);
      if ('IntersectionObserver' in window) { io = new IntersectionObserver(function (en) { visible = en[0].isIntersecting; el.classList.toggle('is-off', !visible); if (visible) loop(); }); io.observe(el); }
      document.addEventListener('visibilitychange', loop);
    }
    if (img.complete && img.naturalWidth) start(); else { img.addEventListener('load', start); }
    if (cfg.w && cfg.h) fit();

    return {
      update: function (part) {
        cfg = merge(cfg, part || {});
        overlays();
        if (!ready && !still()) initGL();
        if (ready) { el.classList.toggle('is-live', !still()); if (still()) { phases = []; mx = my = tx = ty = 0; } else loop(); }   /* at rest the untouched <img> shows */
        fit();
      },
      destroy: function () { dead = true; if (raf) cancelAnimationFrame(raf); if (io) io.disconnect(); if (ro) ro.disconnect(); el.removeEventListener('pointermove', onMove); el.removeEventListener('pointerleave', onLeave); document.removeEventListener('visibilitychange', loop); if (stage.parentNode) stage.parentNode.removeChild(stage); },
      config: function () { return cfg; }
    };
  }
  window.AYHero = { mount: mount };
})();
