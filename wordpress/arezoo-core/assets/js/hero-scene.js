/* Arezoo hero scene — the engine behind the «صحنهٔ هیرو آرزو» widget. No dependencies.

   regions   one flat picture is drawn on a WebGL canvas; soft elliptical regions sway, ripple or breathe,
             sparkle points twinkle and sprite "floaters" drift across. At rest (speed 0, reduced motion,
             no WebGL) the untouched <img> is shown, so the picture is never altered.
   layers    separate transparent pictures, each with its own motion and pointer depth.
   video     the owner's own muted video with a poster picture.
   slideshow pictures cross-fading with an optional slow zoom.

   AYHero.mount(element, config) -> { update(partialConfig), destroy() } */
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
  var mq = function (q) { return window.matchMedia && window.matchMedia(q).matches; };
  var reduced = function () { return mq('(prefers-reduced-motion: reduce)'); };
  function merge(a, b) { var o = {}, k; for (k in a) o[k] = a[k]; for (k in b) o[k] = b[k]; return o; }
  function el(tag, cls) { var e = document.createElement(tag); if (cls) e.className = cls; return e; }

  function mount(root, cfg) {
    cfg = merge({ mode: 'regions', fx: [.5, -1, -1], fy: [.5, -1, -1], speed: 1, amp: 1, mouse: true, mouseAmt: .006, mobile: true, pauseOff: true, regions: [], sparks: [], floats: [], layers: [], slides: [] }, cfg || {});
    root.classList.add('ay-hs');
    var dead = false, visible = true, raf = 0, io = null, ro = null, timers = [];
    var stage = el('div', 'ay-hs-stage'), over = el('div', 'ay-hs-over');
    var img = root.querySelector('img.ay-hs-img');
    var canvas = null, gl = null, U = {}, ready = false, phases = [], last = 0, mx = 0, my = 0, tx = 0, ty = 0;

    function phone() { return mq('(max-width: 767px)'); }
    function still() { return reduced() || cfg.speed <= 0 || (!cfg.mobile && phone()); }
    function focus() {
      var i = phone() ? 2 : (mq('(max-width: 1024px)') ? 1 : 0), pick = function (a) { var v = a[i]; if (v == null || v < 0) v = a[1] >= 0 && i === 2 ? a[1] : a[0]; return v; };
      return [pick(cfg.fx), pick(cfg.fy)];
    }

    /* size the stage so the picture (or the layer scene) covers the box, keeping the focus point in view */
    function fit() {
      var W = root.clientWidth, H = root.clientHeight, f = focus();
      root.style.setProperty('--fx', (f[0] * 100) + '%'); root.style.setProperty('--fy', (f[1] * 100) + '%');
      if (!W || !H || !cfg.w || !cfg.h) return;
      var sc = Math.max(W / cfg.w, H / cfg.h), sw = cfg.w * sc, sh = cfg.h * sc;
      stage.style.width = sw.toFixed(1) + 'px'; stage.style.height = sh.toFixed(1) + 'px';
      stage.style.left = ((W - sw) * f[0]).toFixed(1) + 'px'; stage.style.top = ((H - sh) * f[1]).toFixed(1) + 'px';
      stage.style.setProperty('--ay-sw', sw.toFixed(1) + 'px');
      if (canvas) {
        var dpr = Math.min(window.devicePixelRatio || 1, 2), cw = Math.min(Math.round(sw * dpr), 2560), ch = Math.round(cw * cfg.h / cfg.w);
        if (canvas.width !== cw || canvas.height !== ch) { canvas.width = cw; canvas.height = ch; gl.viewport(0, 0, cw, ch); }
        draw();
      }
    }

    /* ------------------------------------------------------------ regions */
    function initGL() {
      if (ready || !img || !img.naturalWidth || still()) return;   /* never before the picture has loaded */
      canvas = el('canvas', 'ay-hs-canvas'); canvas.setAttribute('aria-hidden', 'true');
      try { gl = canvas.getContext('webgl', { premultipliedAlpha: false, antialias: false }) || canvas.getContext('experimental-webgl'); } catch (e) { gl = null; }
      if (!gl) { canvas = null; return; }
      var sh = function (type, src) { var s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s); return gl.getShaderParameter(s, gl.COMPILE_STATUS) ? s : null; };
      var vs = sh(gl.VERTEX_SHADER, VS), fs = sh(gl.FRAGMENT_SHADER, FS);
      if (!vs || !fs) { gl = null; canvas = null; return; }
      var prog = gl.createProgram(); gl.attachShader(prog, vs); gl.attachShader(prog, fs); gl.linkProgram(prog);
      if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { gl = null; canvas = null; return; }
      gl.useProgram(prog);
      var buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf); gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
      var loc = gl.getAttribLocation(prog, 'p'); gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
      ['asp', 'ms', 'n', 'A', 'B', 'C', 'tex'].forEach(function (k) { U[k] = gl.getUniformLocation(prog, k); });
      var tex = gl.createTexture(); gl.bindTexture(gl.TEXTURE_2D, tex);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
      gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR); gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
      try { gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img); } catch (e) { gl = null; canvas = null; return; }   /* cross-origin picture: stay with the still image */
      if (gl.getError()) { gl = null; canvas = null; return; }
      gl.uniform1i(U.tex, 0); gl.uniform1f(U.asp, cfg.w / cfg.h);
      stage.insertBefore(canvas, img.nextSibling);
      ready = true; root.classList.add('is-live');
      fit(); loop();
    }
    function draw() {
      if (!ready) return;
      var A = new Float32Array(MAXR * 4), B = new Float32Array(MAXR * 4), C = new Float32Array(MAXR * 4), n = 0;
      cfg.regions.forEach(function (r, i) {
        if (n >= MAXR) return;
        var o = n * 4;
        A[o] = r.x; A[o + 1] = r.y; A[o + 2] = Math.max(r.rx, .001); A[o + 3] = Math.max(r.ry, .001);
        B[o] = r.px == null ? r.x : r.px; B[o + 1] = r.py == null ? r.y : r.py; B[o + 2] = (r.amp || 0) * cfg.amp; B[o + 3] = (phases[i] || 0) + (r.phase || 0);
        C[o] = Math.min(Math.max(r.f == null ? .6 : r.f, .01), 1); C[o + 1] = TYPES[r.type] || 0; C[o + 3] = r.depth || 0;
        n++;
      });
      gl.uniform4fv(U.A, A); gl.uniform4fv(U.B, B); gl.uniform4fv(U.C, C); gl.uniform1i(U.n, n);
      gl.uniform2f(U.ms, mx * cfg.mouseAmt, my * cfg.mouseAmt);
      gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
    }
    function loop() {
      if (raf || dead) return;
      last = performance.now();
      var step = function (now) {
        raf = 0; if (dead || (!visible && cfg.pauseOff) || document.hidden || still()) return;
        var dt = Math.min(now - last, 80) / 1000; last = now;
        mx += (tx - mx) * .05; my += (ty - my) * .05;
        if (cfg.mode === 'regions') {
          if (!ready) return;
          cfg.regions.forEach(function (r, i) { phases[i] = (phases[i] || 0) + dt * (r.speed || 1) * cfg.speed; });
          draw();
        } else if (cfg.mode === 'layers') {
          stage.style.setProperty('--mx', mx.toFixed(4)); stage.style.setProperty('--my', my.toFixed(4));
          if (Math.abs(tx - mx) < .002 && Math.abs(ty - my) < .002) return;   /* CSS animations carry on by themselves */
        } else return;
        raf = requestAnimationFrame(step);
      };
      raf = requestAnimationFrame(step);
    }
    function overlays() {
      var h = '', sp = Math.max(cfg.speed, .05);
      if (cfg.mode === 'regions') {
        cfg.sparks.forEach(function (s) { h += '<i class="ay-hs-spark" style="left:' + s.x * 100 + '%;top:' + s.y * 100 + '%;--sz:' + (s.s || 1) * 1.5 + ';--dur:' + ((s.dur || 4) / sp).toFixed(2) + 's;--dl:-' + (s.dl || 0) + 's"></i>'; });
        cfg.floats.forEach(function (f) { h += '<img class="ay-hs-float" alt="" src="' + f.src + '" style="left:' + f.x + '%;top:' + f.y + '%;width:' + f.w + '%;--dx:' + (f.dx || 0) / 100 + ';--dy:' + (f.dy || 0) / 100 + ';--rot:' + (f.rot || 0) + 'deg;--o:' + (f.o == null ? .9 : f.o) + ';--dur:' + ((f.dur || 30) / sp).toFixed(2) + 's;--dl:-' + (f.dl || 0) + 's">'; });
        if (cfg.outline) cfg.regions.forEach(function (r, i) {
          h += '<span class="ay-hs-out" style="left:' + (r.x - r.rx) * 100 + '%;top:' + (r.y - r.ry) * 100 + '%;width:' + r.rx * 200 + '%;height:' + r.ry * 200 + '%"><b>' + (i + 1) + '</b></span>';
          if (r.type === 'sway' || !r.type) h += '<span class="ay-hs-piv" style="left:' + (r.px == null ? r.x : r.px) * 100 + '%;top:' + (r.py == null ? r.y : r.py) * 100 + '%"></span>';
        });
      } else if (cfg.mode === 'layers') {
        cfg.layers.slice().sort(function (a, b) { return (a.z || 0) - (b.z || 0); }).forEach(function (l) {
          if (l.hm && phone()) return;
          h += '<div class="ay-hs-lw" style="--d:' + (cfg.mouse ? (l.depth || 0) : 0) + '"><img class="ay-hs-layer' + (l.motion ? ' ay-lm-' + l.motion : '') + '" alt="" src="' + l.src + '" style="left:' + l.x + '%;top:' + l.y + '%;width:' + l.w + '%;opacity:' + (l.o == null ? 1 : l.o) + ';--o:' + (l.o == null ? 1 : l.o) + ';--ox:' + (l.ox == null ? 50 : l.ox) + '%;--oy:' + (l.oy == null ? 50 : l.oy) + '%;--amt:' + (l.amt || 0) * cfg.amp + ';--dur:' + ((l.dur || 8) / sp).toFixed(2) + 's;--dl:-' + (l.dl || 0) + 's"></div>';
        });
      }
      over.innerHTML = h;
      root.classList.toggle('is-still', still());
      root.classList.toggle('ay-no-mobile-motion', !cfg.mobile);
    }

    /* -------------------------------------------------------------- video */
    var video = null;
    function initVideo() {
      var v = cfg.video || {}, src = (phone() && v.mobile) ? v.mobile : v.src;
      if (!src || still()) return;
      video = el('video', 'ay-hs-video');
      video.muted = true; video.defaultMuted = true; video.playsInline = true; video.setAttribute('playsinline', ''); video.setAttribute('muted', ''); video.setAttribute('aria-hidden', 'true');
      video.loop = !!v.loop && !(v.end > 0); video.preload = 'metadata';
      video.style.setProperty('--fit', v.fit || 'cover');
      if (cfg.img) video.poster = cfg.img;
      video.addEventListener('loadedmetadata', function () { if (v.start > 0) { try { video.currentTime = v.start; } catch (e) { /* not seekable yet */ } } video.playbackRate = v.rate || 1; });
      video.addEventListener('playing', function () { video.classList.add('is-on'); });
      video.addEventListener('timeupdate', function () { if (v.end > 0 && video.currentTime >= v.end) { if (v.loop) { video.currentTime = v.start || 0; } else { video.pause(); } } });
      video.src = src; root.insertBefore(video, root.firstChild ? root.firstChild.nextSibling : null);
      play();
    }
    function play() { if (!video) return; if ((visible || !cfg.pauseOff) && !document.hidden && !still()) { var p = video.play(); if (p && p.catch) p.catch(function () { /* autoplay refused: the poster stays */ }); } else video.pause(); }

    /* ---------------------------------------------------------- slideshow */
    var slides = [], cur = -1;
    function initSlides() {
      var list = cfg.slides.slice();
      if (cfg.rand) list.sort(function () { return Math.random() - .5; });
      list.forEach(function (src, i) { var im = el('img', 'ay-hs-slide'); im.alt = i ? '' : (cfg.alt || ''); im.decoding = 'async'; if (i) im.loading = 'lazy'; im.src = src; im.style.setProperty('--fade', (cfg.fade || 1.6) + 's'); im.style.setProperty('--ken', cfg.ken || 0); im.style.setProperty('--kt', ((cfg.time || 5) + (cfg.fade || 1.6) * 2) + 's'); root.insertBefore(im, root.firstChild); slides.push(im); });
      next();
    }
    function next() {
      if (dead || !slides.length) return;
      cur = (cur + 1) % slides.length;
      slides.forEach(function (s, i) { var on = i === cur; s.classList.toggle('is-on', on); if (on && cfg.ken > 0 && !still()) { s.classList.remove('is-ken'); void s.offsetWidth; s.classList.add('is-ken'); } });
      if (slides.length > 1 && !still()) timers.push(setTimeout(function () { if (visible || !cfg.pauseOff) next(); else timers.push(setTimeout(next, 1200)); }, (cfg.time || 5) * 1000));
    }

    function onMove(e) { if (!cfg.mouse || e.pointerType === 'touch' || still()) return; var r = root.getBoundingClientRect(); tx = ((e.clientX - r.left) / r.width - .5) * 2; ty = ((e.clientY - r.top) / r.height - .5) * 2; loop(); }
    function onLeave() { tx = 0; ty = 0; loop(); }
    function onVis() { if (cfg.mode === 'video') play(); else loop(); }

    function start() {
      if (dead) return;
      if (cfg.mode === 'regions') {
        cfg.w = cfg.w || img.naturalWidth; cfg.h = cfg.h || img.naturalHeight;
        stage.appendChild(img); stage.appendChild(over); root.insertBefore(stage, root.firstChild);
        overlays(); fit(); initGL();
      } else if (cfg.mode === 'layers') {
        cfg.h = 1000; cfg.w = 1000 * (cfg.ratio || 1.78);
        stage.appendChild(over); root.insertBefore(stage, root.firstChild); overlays(); fit();
      } else if (cfg.mode === 'video') { fit(); initVideo(); } else { fit(); initSlides(); }
      root.addEventListener('pointermove', onMove); root.addEventListener('pointerleave', onLeave);
      if ('ResizeObserver' in window) { ro = new ResizeObserver(function () { fit(); if (cfg.mode === 'layers') overlays(); }); ro.observe(root); } else window.addEventListener('resize', fit);
      if ('IntersectionObserver' in window) { io = new IntersectionObserver(function (en) { visible = en[0].isIntersecting; root.classList.toggle('is-off', !visible && cfg.pauseOff); onVis(); }); io.observe(root); }
      document.addEventListener('visibilitychange', onVis);
    }
    if (cfg.mode === 'regions') {
      if (!img) { img = el('img', 'ay-hs-img'); img.alt = cfg.alt || ''; img.decoding = 'async'; img.src = cfg.img; root.insertBefore(img, root.firstChild); }
      if (img.complete && img.naturalWidth) start(); else img.addEventListener('load', start);
    } else start();

    return {
      update: function (part) {
        cfg = merge(cfg, part || {});
        overlays();
        if (cfg.mode === 'regions') { if (!ready && !still()) initGL(); if (ready) { root.classList.toggle('is-live', !still()); if (still()) { phases = []; mx = my = tx = ty = 0; } } }
        fit(); loop();
      },
      destroy: function () {
        dead = true; if (raf) cancelAnimationFrame(raf); timers.forEach(clearTimeout); if (io) io.disconnect(); if (ro) ro.disconnect();
        root.removeEventListener('pointermove', onMove); root.removeEventListener('pointerleave', onLeave); document.removeEventListener('visibilitychange', onVis);
        if (video) { video.pause(); video.removeAttribute('src'); }
      },
      config: function () { return cfg; }
    };
  }
  window.AYHero = { mount: mount };
})();
