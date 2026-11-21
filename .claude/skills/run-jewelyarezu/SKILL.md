---
name: run-jewelyarezu
description: Build, run, test and screenshot the Arezoo WordPress site (theme "arezoo" + plugin "arezoo-core" with Elementor widgets). Use when asked to start/run the site locally, check the hero motion on phone/iPad/desktop sizes, take responsive screenshots, open the Elementor editor or wp-admin pages, run a PHP snippet inside WordPress, lint, or build the release zips.
---

Local WordPress (SQLite, no MySQL) + Elementor with this repo's theme and plugin **symlinked** in,
served by `php -S`, driven by a Playwright script. Everything lives in
`.claude/skills/run-jewelyarezu/`; paths below are relative to the repo root.

| file | role |
|---|---|
| `setup.sh` | download WP + Elementor + SQLite drop-in, write config, install, activate, build the site |
| `serve.sh start\|stop\|status` | PHP built-in server on `127.0.0.1:8890` (log `/tmp/arezoo-wp.log`) |
| `wp.php` | wp-cli stand-in: run PHP inside WordPress as admin |
| `driver.cjs` | headless Chromium checks + screenshots (to `/tmp/arezoo-shots/`) |

## Prerequisites
PHP 8.x CLI with `pdo_sqlite`, `gd`, `zip`; `curl`, `unzip`; Node + Playwright with Chromium
(preinstalled in Claude cloud containers: `require('playwright')` works from any directory).
Network access to `wordpress.org` / `downloads.wordpress.org` for the first setup only.

## Setup + build the site (once, ~10 s after downloads)
```bash
.claude/skills/run-jewelyarezu/setup.sh
```
Ends with the plugin's Persian build log ("سایت آماده است.") and `front page 26 | works 10`.
Site in `/tmp/arezoo-wp` (override `WP_DIR=`, `PORT=`). Login: `admin` / `admin-pass-123`.
Re-running is safe; it skips everything that exists. Theme/plugin are symlinks, so code edits
show up on the next page load: no reinstall needed.

## Run (agent path)
```bash
.claude/skills/run-jewelyarezu/serve.sh start
node .claude/skills/run-jewelyarezu/driver.cjs hero
```
`hero` checks the home hero on 9 screen shapes and exits 1 if any line is `BAD`:
```
OK  iPad_Pro_13_P 1032x1376 {"img":"hero2-portrait.webp","live":true,"floats":5,"floatsMoving":true,"regionsMoving":true,"stages":1,"errors":0}
```
(`img` = picture variant chosen for that aspect, `live` = WebGL running, `stages` > 1 = hero mounted twice.)

| command | what it does |
|---|---|
| `driver.cjs hero` | hero variant, WebGL, floats + regions moving, single mount, no errors, on 9 sizes |
| `driver.cjs sizes /about-me/` | screenshot at 10 sizes (phones, iPad mini/Air/Pro 13 P+L, 1366, 1584x738, 1920) + horizontal-overflow + JS-error check |
| `driver.cjs shot /collections/ --device "iPhone 13" --full` | one screenshot (`--size 390x844` also works) |
| `driver.cjs errors /` | console errors + any request leaving 127.0.0.1 (the site must make none) |
| `driver.cjs admin "admin.php?page=arezoo-settings"` | logs in, screenshots a wp-admin page |
| `driver.cjs editor 26` | opens the Elementor editor for post 26 (home), lists `ay-*` widgets in the preview, screenshots |

Pages: `/`, `/collections/`, `/about-me/`, `/contact/`, works at `/work/<slug>/`.
Plugin admin pages: `arezoo`, `arezoo-themes`, `arezoo-demos`, `arezoo-terms`, `arezoo-settings`, `arezoo-curtain`.

### Direct invocation (most PRs touch PHP internals)
```bash
php .claude/skills/run-jewelyarezu/wp.php -r 'echo AY_VERSION, " ", get_template(), "\n";'
php .claude/skills/run-jewelyarezu/wp.php -r 'print_r( wp_list_pluck( ay_social_rows(), "net" ) );'
```
Change one widget's settings on a page (pattern used for every responsive test). This example turns
phone motion off; afterwards `driver.cjs hero` reports `BAD … "live":false` on phones. Set `'yes'` to undo:
```php
// save as /tmp/x.php, run: php .claude/skills/run-jewelyarezu/wp.php /tmp/x.php
$id = (int) get_option( 'page_on_front' ); $l = ay_get_layout( $id );
ay_walk_layout( $l, function ( &$el ) { if ( ( $el['widgetType'] ?? '' ) !== 'ay-hero' ) return false; $el['settings']['motion_mobile'] = ''; return true; } );
update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $l ) ) ); ay_flush_layout_cache( $id );
```

## Test
No unit-test suite exists. The checks used before every release:
```bash
find wordpress -name '*.php' -print0 | xargs -0 -n1 php -l | grep -v '^No syntax errors'
for f in wordpress/arezoo-core/assets/js/*.js; do node --check "$f" || echo "BAD $f"; done
node .claude/skills/run-jewelyarezu/driver.cjs hero && node .claude/skills/run-jewelyarezu/driver.cjs errors /
```
Release zips (version read from `wordpress/arezoo-core/arezoo-core.php`):
```bash
tools/build-zips.sh
```
It overwrites `dist/*-<version>.zip`. If you didn't bump the version, `git checkout -- dist/` afterwards.

## Stop
```bash
.claude/skills/run-jewelyarezu/serve.sh stop
```

## Gotchas
- **Don't name globals `$wp`, `$args`, `$post` in any script run via `wp.php`.** It `require`s
  `wp-load.php` in global scope, so `$wp = '/path'` replaces WordPress's `WP` object →
  `Call to a member function add_query_var() on string` in `class-wp-taxonomy.php`. wp.php uses `$__*`.
- **The site builds itself only on a "fresh" site** (`admin_init` + blog front page + ≤2 pages).
  `setup.sh` calls `ay_build_site()` directly instead, and `arezoo_built` then blocks any rebuild.
  Re-running it (`php .claude/skills/run-jewelyarezu/wp.php -r 'delete_option("arezoo_built"); echo implode("\n", ay_build_site());'`)
  only fills in missing pages/parts and re-applies kit settings. It does NOT reset edited layouts
  (verified: 5 pages / 10 works before and after). For a truly clean site: `serve.sh stop`, delete `/tmp/arezoo-wp/wp-content/database/.ht.sqlite`, re-run `setup.sh`.
- **After editing `_elementor_data` by hand, call `ay_flush_layout_cache($id)`.** Otherwise
  Elementor serves the old cached CSS/element cache and your change looks ignored.
- **Elementor editor errors are not ours:** `Cannot read properties of null (reading 'nodeName')`
  comes from WP core `imagesloaded.min.js`, and `ERR_TUNNEL_CONNECTION_FAILED` comes from
  gravatar/jsdelivr (ace editor) being blocked by the sandbox proxy. The front end must have 0 errors.
- **WebGL motion can't be read from the canvas** (no `preserveDrawingBuffer`). `hero` hides the DOM
  layers and compares two clipped screenshots 900 ms apart instead.
- **Picture variant depends on aspect ratio, not width:** 744x1133 (iPad mini) gets `hero2-tall`,
  820x1180 gets `hero2-portrait`. That is expected, not a bug.
- **Elementor Pro control ids clash** (e.g. `sticky` hid our switch). Check that a new control is really
  visible with `driver.cjs editor <id>` plus a panel screenshot, not only on the front end.
- **`serve.sh` must background `php` itself.** `(cd dir && nohup php … &)` backgrounds the `&&`
  list, so `$!` is a subshell and killing it leaves `php -S` alive (the next start says "already
  serving" with old code paths). `stop` also `pkill`s by the exact `php -S 127.0.0.1:$PORT router.php`.
- **Uploaded-image URLs are absolute with the port of the first build.** Changing `PORT=` later
  makes the pages load `127.0.0.1:<old port>/wp-content/uploads/…`, and `driver.cjs errors` flags
  them as external. Pick the port once, per `WP_DIR`.
- **No `sendmail`:** `wp_install` prints `sh: 1: /usr/sbin/sendmail: not found`. This is harmless.

## Troubleshooting
- **`There has been a critical error on this website.` HTML from setup/wp.php**: read
  `/tmp/arezoo-wp/wp-content/debug.log` (WP_DEBUG_LOG is on); the fatal and stack are at the end.
- **`driver.cjs` hangs / `net::ERR_CONNECTION_REFUSED`**: server not running. Run `serve.sh status`, then `serve.sh start`.
- **Port 8890 busy**: use a second site, e.g. `WP_DIR=/tmp/arezoo-wp2 PORT=8891 .claude/skills/run-jewelyarezu/setup.sh`, and pass the same `WP_DIR=`/`PORT=` to `serve.sh` and `PORT=` to `driver.cjs`. Re-using the same `WP_DIR` with a new port leaves old-port image URLs (see Gotchas).
- **`Error: Cannot find module 'playwright'`**: Playwright is global here, found through
  `NODE_PATH=/usr/local/lib/node_modules_global`. A stripped env (`env -i`) loses it, so keep the
  normal shell env or `export NODE_PATH=/usr/local/lib/node_modules_global`.
- **`curl: (6) Could not resolve host: wordpress.org` in setup.sh**: the shell lost the sandbox
  proxy variables (same `env -i` cause) or has no network. Downloads are cached in
  `$WP_DIR/.dl/`, so once they exist setup needs no network.
