#!/usr/bin/env bash
# Replacement command for the angular-ui service in coverage mode
# (docker-compose.coverage.yml). Builds the app, instruments the bundles with
# Istanbul and serves them statically while collecting window.__coverage__.
set -euo pipefail

DIST_DIR=/app/dist/toolshop
COVERAGE_OUT=/coverage-out
TOOLS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "[coverage] Installing UI dependencies..."
cd /app
npm install --force

echo "[coverage] Building UI (development configuration, with source maps)..."
npx ng build --configuration development

echo "[coverage] Installing coverage tools..."
cd "$TOOLS_DIR"
npm install --no-audit --no-fund

# Archive snapshots from a previous session: they belong to an older build and
# must not be merged with coverage of this one.
mkdir -p "$COVERAGE_OUT/raw"
if [ -n "$(ls -A "$COVERAGE_OUT/raw" 2>/dev/null)" ]; then
  STAMP=$(date +%Y%m%d-%H%M%S)
  echo "[coverage] Archiving previous session to raw-archived-$STAMP"
  mv "$COVERAGE_OUT/raw" "$COVERAGE_OUT/raw-archived-$STAMP"
  mkdir -p "$COVERAGE_OUT/raw"
fi

echo "[coverage] Instrumenting bundles..."
node "$TOOLS_DIR/instrument.js" "$DIST_DIR" "$COVERAGE_OUT"

echo "[coverage] Serving instrumented UI on port 4200..."
exec node "$TOOLS_DIR/server.js" "$DIST_DIR" "$COVERAGE_OUT" 4200
