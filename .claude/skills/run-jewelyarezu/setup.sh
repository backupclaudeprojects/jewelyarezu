#!/usr/bin/env bash
# One-shot local WordPress for the Arezoo theme + Arezoo Core plugin.
# WordPress core + SQLite drop-in (no MySQL) + Elementor, theme/plugin SYMLINKED from this repo,
# admin user created, plugin + theme activated, site built with ay_build_site().
# Idempotent: re-running skips what already exists.
#   WP_DIR (default /tmp/arezoo-wp)   PORT (default 8890)
set -euo pipefail
here="$(cd "$(dirname "$0")" && pwd)"
repo="$(cd "$here/../../.." && pwd)"
WP_DIR="${WP_DIR:-/tmp/arezoo-wp}"
PORT="${PORT:-8890}"
dl="$WP_DIR/.dl"
mkdir -p "$dl"

fetch() { [ -s "$dl/$2" ] || curl -fsSL --retry 3 -o "$dl/$2" "$1"; }
fetch https://wordpress.org/latest.zip wordpress.zip
fetch https://downloads.wordpress.org/plugin/elementor.latest-stable.zip elementor.zip
fetch https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip sqlite.zip

if [ ! -f "$WP_DIR/wp-load.php" ]; then
  tmp="$(mktemp -d)"; unzip -q "$dl/wordpress.zip" -d "$tmp"; cp -a "$tmp/wordpress/." "$WP_DIR/"; rm -rf "$tmp"
fi
plugins="$WP_DIR/wp-content/plugins"; themes="$WP_DIR/wp-content/themes"
[ -d "$plugins/elementor" ] || unzip -q "$dl/elementor.zip" -d "$plugins"
[ -d "$plugins/sqlite-database-integration" ] || unzip -q "$dl/sqlite.zip" -d "$plugins"

# SQLite drop-in: db.copy with its two placeholders filled in
if [ ! -f "$WP_DIR/wp-content/db.php" ]; then
  sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$plugins/sqlite-database-integration#" \
      -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
      "$plugins/sqlite-database-integration/db.copy" > "$WP_DIR/wp-content/db.php"
fi
mkdir -p "$WP_DIR/wp-content/database"

cat > "$WP_DIR/wp-config.php" <<PHP
<?php
define( 'DB_NAME', 'wp' ); define( 'DB_USER', '' ); define( 'DB_PASSWORD', '' ); define( 'DB_HOST', '' ); define( 'DB_CHARSET', 'utf8mb4' ); define( 'DB_COLLATE', '' );
define( 'DB_DIR', '$WP_DIR/wp-content/database/' ); define( 'DB_FILE', '.ht.sqlite' );
foreach ( array( 'AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT' ) as \$k ) { define( \$k, 'local-' . \$k ); }
\$table_prefix = 'wp_';
define( 'WP_DEBUG', true ); define( 'WP_DEBUG_LOG', '$WP_DIR/wp-content/debug.log' ); define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_HOME', 'http://127.0.0.1:$PORT' ); define( 'WP_SITEURL', 'http://127.0.0.1:$PORT' );
define( 'AUTOMATIC_UPDATER_DISABLED', true ); define( 'DISABLE_WP_CRON', true );
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
require_once ABSPATH . 'wp-settings.php';
PHP

cp "$here/router.php" "$WP_DIR/router.php"
ln -sfn "$repo/wordpress/arezoo-core" "$plugins/arezoo-core"
ln -sfn "$repo/wordpress/arezoo" "$themes/arezoo"

# install, activate, build (each step prints what it did)
WP_DIR="$WP_DIR" PORT="$PORT" php "$here/wp.php" -r '
  require_once ABSPATH . "wp-admin/includes/upgrade.php";
  if ( ! is_blog_installed() ) { wp_install( "Arezoo local", "admin", "admin@example.test", false, "", "admin-pass-123", "en_US" ); echo "installed\n"; }
' --install
WP_DIR="$WP_DIR" PORT="$PORT" php "$here/wp.php" -r '
  foreach ( array( "elementor/elementor.php", "arezoo-core/arezoo-core.php" ) as $p ) { $r = activate_plugin( $p ); echo $p, ": ", is_wp_error( $r ) ? $r->get_error_message() : "active", "\n"; }
  switch_theme( "arezoo" ); update_option( "permalink_structure", "/%postname%/" ); flush_rewrite_rules();
  echo "theme ", get_template(), " | elementor ", ELEMENTOR_VERSION, " | arezoo-core ", AY_VERSION, "\n";
'
WP_DIR="$WP_DIR" PORT="$PORT" php "$here/wp.php" -r '
  if ( get_option( "arezoo_built" ) ) { echo "site already built\n"; return; }
  echo implode( "\n", (array) ay_build_site() ), "\n";
  echo "front page ", get_option( "page_on_front" ), " | works ", wp_count_posts( "ay_work" )->publish, "\n";
'
echo "OK  $WP_DIR  ->  start with: $here/serve.sh start"
