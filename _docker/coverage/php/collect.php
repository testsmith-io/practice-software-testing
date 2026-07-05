<?php

/**
 * Per-request PHP code coverage collector (Xdebug, branch/path level).
 *
 * Loaded through auto_prepend_file (see coverage.ini) so it runs before every
 * FPM request when the coverage overlay is active. It starts Xdebug code
 * coverage (restricted to app code) and, on shutdown, dumps the raw coverage
 * of the request as one JSON file. The dumps are merged into a detailed
 * php-code-coverage HTML report by report.php.
 */

if (PHP_SAPI === 'cli'
    || !function_exists('xdebug_start_code_coverage')
    || !str_contains((string) ini_get('xdebug.mode'), 'coverage')
) {
    return;
}

// Only measure application code; without this filter Xdebug would track the
// whole framework and every request would produce a huge, slow dump.
xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, [
    '/var/www/app/',
    '/var/www/routes/',
]);

xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);

register_shutdown_function(static function (): void {
    $data = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();
    if (empty($data)) {
        return;
    }

    $dir = getenv('COVERAGE_RAW_DIR') ?: '/opt/coverage-out/raw';
    if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
        return;
    }

    $dump = ['format' => 'xdebug-path-coverage', 'data' => $data];
    $name = sprintf('%s-%d-%s.json', date('Ymd-His'), getmypid(), bin2hex(random_bytes(4)));
    @file_put_contents($dir . '/' . $name, json_encode($dump));
});
