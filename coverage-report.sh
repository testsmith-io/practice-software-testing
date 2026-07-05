#!/usr/bin/env bash
# Generate coverage reports from a coverage-mode session
# (started with ./start-app-coverage.sh). Output lands in ./coverage-output/.
set -e
cd "$(dirname "$0")"

echo "=== PHP (Laravel API) ==============================================="
# The report tooling (php-code-coverage) is isolated from the app's vendor
# dir and installed on first use; the host-mounted vendor dir caches it.
docker compose exec -T laravel-api sh -c \
  '[ -f /opt/coverage/tools/vendor/autoload.php ] || composer install -q --no-interaction -d /opt/coverage/tools' || true
docker compose exec -T laravel-api php /opt/coverage/report.php || true

echo
echo "=== TypeScript (Angular UI) ========================================="
docker compose exec -T angular-ui node /coverage-tools/report.js || true

echo
echo "Reports (on the host):"
echo "  PHP: coverage-output/php/report/index.html"
echo "  UI:  coverage-output/ui/report/index.html"
