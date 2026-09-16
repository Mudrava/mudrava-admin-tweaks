#!/usr/bin/env bash
#
# Build a WordPress.org-ready release zip from the working tree.
# Excludes all dev scratch (.branding-src, .playwright-mcp, tools, dist,
# .git, node_modules) so the zip contains runtime files only.
#
set -euo pipefail
cd "$(dirname "$0")/.."

VER=$(awk -F': +' '/^ \* Version:/ {print $2; exit}' mudrava-admin-tweaks.php)
OUT="dist/mudrava-admin-tweaks-${VER}.zip"

rm -rf dist
mkdir -p "dist/mudrava-admin-tweaks"

rsync -a \
	mudrava-admin-tweaks.php \
	uninstall.php \
	readme.txt \
	LICENSE \
	"dist/mudrava-admin-tweaks/"

rsync -a --exclude '.DS_Store' src assets languages "dist/mudrava-admin-tweaks/"

find dist -name '.DS_Store' -delete

( cd dist && zip -qr "mudrava-admin-tweaks-${VER}.zip" mudrava-admin-tweaks && rm -rf mudrava-admin-tweaks )

echo "built ${OUT} ($(du -h "${OUT}" | cut -f1))"
