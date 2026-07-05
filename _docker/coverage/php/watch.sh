#!/bin/sh
# Auto-refresh mode: regenerates the PHP coverage report whenever new request
# dumps arrive. Runs as the coverage-php-watcher service (see
# docker-compose.coverage.yml). Interval in seconds via
# COVERAGE_REPORT_INTERVAL (0 disables auto-refresh).
INTERVAL="${COVERAGE_REPORT_INTERVAL:-30}"

if ! [ "$INTERVAL" -gt 0 ] 2>/dev/null; then
  echo "[coverage-watch] auto-refresh disabled (COVERAGE_REPORT_INTERVAL=$INTERVAL)"
  exec sleep infinity
fi

echo "[coverage-watch] regenerating the PHP report every ${INTERVAL}s when new coverage arrives"

STATE=""
while true; do
  if [ ! -f /opt/coverage/tools/vendor/autoload.php ]; then
    composer install -q --no-interaction -d /opt/coverage/tools || true
  fi

  # Only regenerate when the raw dumps changed (new file per request).
  NEW=$(ls -la /opt/coverage-out/raw 2>/dev/null | md5sum)
  if [ "$NEW" != "$STATE" ] && ls /opt/coverage-out/raw/*.json >/dev/null 2>&1; then
    if php /opt/coverage/report.php >/tmp/coverage-report.log 2>&1; then
      echo "[coverage-watch] report regenerated ($(date +%H:%M:%S))"
      STATE="$NEW"
    else
      echo "[coverage-watch] report generation failed:"
      tail -5 /tmp/coverage-report.log
    fi
  fi
  sleep "$INTERVAL"
done
