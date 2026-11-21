#!/usr/bin/env node
/* Drive the local Arezoo site (made by setup.sh, served by serve.sh) with headless Chromium.
 *
 *   node driver.cjs shot   <path> [--size 390x844 | --device "iPhone 13"] [--full] [--out name.png]
 *   node driver.cjs sizes  <path>          screenshots at the phone/iPad/laptop/desktop matrix + horizontal-overflow check
 *   node driver.cjs hero                   home hero on 8 screen shapes: picture variant, WebGL live, floats + regions moving
 *   node driver.cjs admin  <wp-admin path> logged-in screenshot, e.g. "admin.php?page=arezoo-settings"
 *   node driver.cjs editor <post id>       open the Elementor editor, wait for panel + preview, list Arezoo widgets
 *   node driver.cjs errors <path>          console errors + external (non-local) requests on a page
 *
 * Env: PORT (8890), SHOTS (/tmp/arezoo-shots). Exit code 1 when a check fails.
 */
const { chromium, devices } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE = `http://127.0.0.1:${process.env.PORT || 8890}`;
const OUT = process.env.SHOTS || '/tmp/arezoo-shots';
fs.mkdirSync(OUT, { recursive: true });
const [cmd, arg, ...rest] = process.argv.slice(2);
const opt = (k) => { const i = rest.indexOf('--' + k); return i < 0 ? undefined : (rest[i + 1] && !rest[i + 1].startsWith('--') ? rest[i + 1] : true); };
const url = (p = '/') => (/^https?:/.test(p) ? p : BASE + (p.startsWith('/') ? p : '/' + p));
const file = (n) => path.join(OUT, n.replace(/[^\w.-]+/g, '_'));

const MATRIX = [
  ['iPhone_14', 390, 844], ['Pixel_7', 412, 915], ['Phone_730', 730, 1197],
  ['iPad_mini_P', 744, 1133], ['iPad_Air_P', 820, 1180], ['iPad_Pro_13_P', 1032, 1376], ['iPad_Pro_13_L', 1376, 1032],
  ['Laptop_1366', 1366, 768], ['Desktop_1584', 1584, 738], ['FullHD', 1920, 1080],
];
const ctxFor = (w, h) => ({ viewport: { width: w, height: h }, isMobile: Math.min(w, h) < 800, hasTouch: w < 1400 });

async function page(browser, ctxOpts) {
  const ctx = await browser.newContext(ctxOpts);
  const p = await ctx.newPage();
  p._errors = [];
  p.on('pageerror', (e) => p._errors.push('pageerror: ' + e.message));
  p.on('console', (m) => m.type() === 'error' && p._errors.push('console: ' + m.text()));
  return p;
}

async function login(p) {
  await p.goto(url('/wp-login.php'));
  await p.fill('#user_login', 'admin');
  await p.fill('#user_pass', 'admin-pass-123');
  await Promise.all([p.waitForNavigation(), p.click('#wp-submit')]);
}

(async () => {
  const b = await chromium.launch();
  let fail = false;
  try {
    if (cmd === 'shot') {
      const dev = opt('device');
      const [w, h] = String(opt('size') || '1440x900').split('x').map(Number);
      const p = await page(b, dev ? devices[dev] : { ...ctxFor(w, h), deviceScaleFactor: 1 });
      await p.goto(url(arg), { waitUntil: 'load' });
      await p.waitForTimeout(1500);
      const out = file(opt('out') || `shot_${(dev || w + 'x' + h)}_${arg || 'home'}.png`);
      await p.screenshot({ path: out, fullPage: !!opt('full') });
      console.log(out, p._errors.length ? '\n' + p._errors.join('\n') : '');
    } else if (cmd === 'sizes') {
      for (const [n, w, h] of MATRIX) {
        const p = await page(b, ctxFor(w, h));
        await p.goto(url(arg), { waitUntil: 'load' });
        await p.waitForTimeout(1200);
        const over = await p.evaluate(() => document.documentElement.scrollWidth - innerWidth);
        const out = file(`size_${n}_${arg || 'home'}.png`);
        await p.screenshot({ path: out });
        const ok = over <= 1 && !p._errors.length;
        fail ||= !ok;
        console.log((ok ? 'OK  ' : 'BAD ') + n.padEnd(14) + `${w}x${h}`.padEnd(10) + `overflowX=${over} errors=${p._errors.length} ${out}`);
        await p.context().close();
      }
    } else if (cmd === 'hero') {
      for (const [n, w, h] of MATRIX.slice(0, 9)) {
        const p = await page(b, ctxFor(w, h));
        await p.goto(url('/'), { waitUntil: 'load' });
        await p.waitForTimeout(1500);
        const pos = () => p.evaluate(() => [...document.querySelectorAll('.ay-hero .ay-hs-float')].map((x) => { const r = x.getBoundingClientRect(); return Math.round(r.x) + ',' + Math.round(r.y); }).join(' '));
        const a = await p.evaluate(() => { const h = document.querySelector('.ay-hero'); return h && { img: (h.querySelector('img.ay-hs-img') || {}).src?.split('/').pop(), live: h.classList.contains('is-live'), floats: h.querySelectorAll('.ay-hs-float').length, stages: h.querySelectorAll('.ay-hs-stage').length }; });
        if (!a) { console.log('BAD ' + n + ' no .ay-hero on home'); fail = true; continue; }
        const p1 = await pos(); await p.waitForTimeout(1200); const p2 = await pos();
        /* WebGL regions: hide DOM layers, compare two frames */
        await p.addStyleTag({ content: '.ay-hs-spark,.ay-hs-float,.e-con.ay-float-top,.ay-dock{visibility:hidden!important}' });
        const box = await p.evaluate(() => { const r = document.querySelector('.ay-hero').getBoundingClientRect(); return { x: 0, y: Math.max(0, r.y), width: Math.min(innerWidth, r.width), height: Math.min(innerHeight, r.height) }; });
        const s1 = (await p.screenshot({ clip: box })).toString('base64'); await p.waitForTimeout(900); const s2 = (await p.screenshot({ clip: box })).toString('base64');
        await p.addStyleTag({ content: '.ay-hs-spark,.ay-hs-float{visibility:visible!important}' });
        const out = file(`hero_${n}.png`); await p.screenshot({ path: out });
        const ok = a.live && a.floats > 0 && p1 !== p2 && s1 !== s2 && a.stages <= 1 && !p._errors.length;
        fail ||= !ok;
        console.log((ok ? 'OK  ' : 'BAD ') + n.padEnd(14) + `${w}x${h}`.padEnd(10) + JSON.stringify({ img: a.img, live: a.live, floats: a.floats, floatsMoving: p1 !== p2, regionsMoving: s1 !== s2, stages: a.stages, errors: p._errors.length }));
        await p.context().close();
      }
    } else if (cmd === 'admin') {
      const p = await page(b, { viewport: { width: 1440, height: 1000 } });
      await login(p);
      await p.goto(url('/wp-admin/' + (arg || '')), { waitUntil: 'load' });
      const out = file(opt('out') || `admin_${arg || 'dashboard'}.png`);
      await p.screenshot({ path: out, fullPage: !!opt('full') });
      console.log(out, await p.title());
    } else if (cmd === 'editor') {
      const p = await page(b, { viewport: { width: 1600, height: 1000 } });
      await login(p);
      await p.goto(url(`/wp-admin/post.php?post=${arg}&action=elementor`), { waitUntil: 'load' });
      await p.waitForSelector('#elementor-panel', { timeout: 60000 });
      const fr = p.frameLocator('#elementor-preview-iframe');
      await fr.locator('.elementor-element').first().waitFor({ timeout: 60000 });
      await p.waitForTimeout(2000);
      const widgets = await p.frame({ name: 'elementor-preview-iframe' })?.evaluate(() => [...new Set([...document.querySelectorAll('[data-widget_type^="ay-"]')].map((e) => e.dataset.widget_type))]);
      const out = file(`editor_${arg}.png`);
      await p.screenshot({ path: out });
      console.log(out, '\narezoo widgets in preview:', (widgets || []).join(' '), p._errors.length ? '\n' + p._errors.join('\n') : '');
    } else if (cmd === 'errors') {
      const p = await page(b, ctxFor(1440, 900));
      const ext = [];
      p.on('request', (r) => { const u = r.url(); if (!u.startsWith(BASE) && !u.startsWith('data:') && !u.startsWith('blob:')) ext.push(u); });
      await p.goto(url(arg), { waitUntil: 'load' });
      await p.waitForTimeout(2000);
      fail = p._errors.length > 0 || ext.length > 0;
      console.log(`errors: ${p._errors.length}\n${p._errors.join('\n')}\nexternal requests: ${ext.length}\n${ext.join('\n')}`);
    } else {
      console.log(fs.readFileSync(__filename, 'utf8').split('\n').slice(1, 13).join('\n'));
    }
  } finally {
    await b.close();
  }
  process.exit(fail ? 1 : 0);
})();
