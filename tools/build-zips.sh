#!/usr/bin/env bash
# Builds the two installable packages into dist/ :
#   dist/arezoo-core-<version>.zip   (plugin)   and   dist/arezoo-theme-<version>.zip   (theme)
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"
ver="$(grep -m1 "Version:" "$root/wordpress/arezoo-core/arezoo-core.php" | sed 's/.*Version:[[:space:]]*//')"
mkdir -p "$root/dist"
rm -f "$root/dist/arezoo-core-$ver.zip" "$root/dist/arezoo-theme-$ver.zip"
(cd "$root/wordpress" && zip -q -r -X "$root/dist/arezoo-core-$ver.zip" arezoo-core -x '*.DS_Store')
(cd "$root/wordpress" && zip -q -r -X "$root/dist/arezoo-theme-$ver.zip" arezoo -x '*.DS_Store')
ls -la "$root/dist"
