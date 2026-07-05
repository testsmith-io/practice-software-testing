#!/usr/bin/env bash
# Start the app with runtime code coverage enabled (PHP via PCOV, TypeScript
# via Istanbul). See _docker/coverage/README.md.
set -e
cd "$(dirname "$0")"
mkdir -p coverage-output/php/raw coverage-output/ui/raw
docker compose \
  -f docker-compose.yml \
  -f docker-compose.override.yml \
  -f docker-compose.coverage.yml \
  up -d --build
# nginx caches the upstream IP; recreating laravel-api (mode switch) would
# otherwise leave the API returning 502 until nginx is restarted.
docker compose restart web >/dev/null 2>&1 || true
echo
echo "Coverage mode is starting. The UI needs a minute or two (npm install + build + instrument):"
echo "  docker compose logs -f angular-ui"
echo "Test manually at http://localhost:4200"
echo "Live reports (auto-refreshing): http://localhost:4200/__reports/"
echo "On-demand generation also works: ./coverage-report.sh"
