<?php

/**
 * Merges the per-request Xdebug dumps written by collect.php into a detailed
 * php-code-coverage HTML report (annotated source, line + branch + path
 * coverage) plus a Clover XML file.
 *
 * Runs inside the laravel-api container (needs the app sources at /var/www
 * and the tooling from tools/vendor, installed by coverage-report.sh):
 *   php /opt/coverage/report.php [--raw=DIR] [--out=DIR]
 */

declare(strict_types=1);

ini_set('memory_limit', '1G');

$options = [
    'raw' => '/opt/coverage-out/raw',
    'out' => '/opt/coverage-out/report',
];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--(raw|out)=(.+)$/', $arg, $m)) {
        $options[$m[1]] = $m[2];
    }
}

$autoload = __DIR__ . '/tools/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Coverage tooling is not installed (missing tools/vendor).\n");
    fwrite(STDERR, "Run: composer install -d " . __DIR__ . "/tools\n");
    exit(1);
}
require $autoload;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlFacade;

/**
 * The real collection already happened inside the FPM requests; this driver
 * only exists because CodeCoverage requires one to replay the dumps into.
 */
final class XdebugDumpReplayDriver extends Driver
{
    public function canCollectBranchAndPathCoverage(): bool
    {
        return true;
    }

    public function canDetectDeadCode(): bool
    {
        return true;
    }

    public function nameAndVersion(): string
    {
        return 'Xdebug dump replay';
    }

    public function start(): void
    {
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
    }
}

$dumps = glob(rtrim($options['raw'], '/') . '/*.json') ?: [];
if (count($dumps) === 0) {
    fwrite(STDERR, "No coverage dumps found in {$options['raw']}. Perform some actions in the app first.\n");
    exit(1);
}

$phpFilesUnder = static function (string $dir): array {
    if (!is_dir($dir)) {
        return [];
    }
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->getExtension() === 'php') {
            $files[] = $fileInfo->getPathname();
        }
    }
    return $files;
};

$filter = new Filter;
$filter->includeFiles([
    ...$phpFilesUnder('/var/www/app'),
    ...$phpFilesUnder('/var/www/routes'),
]);

$driver = new XdebugDumpReplayDriver;
$driver->enableBranchAndPathCoverage();

$coverage = new CodeCoverage($driver, $filter);
$coverage->cacheStaticAnalysis(sys_get_temp_dir() . '/coverage-static-cache');

$merged = 0;
foreach ($dumps as $i => $dumpFile) {
    $dump = json_decode((string) file_get_contents($dumpFile), true);
    if (!is_array($dump) || ($dump['format'] ?? null) !== 'xdebug-path-coverage') {
        fwrite(STDERR, 'Skipping ' . basename($dumpFile) . " (unknown format — from an older coverage run?)\n");
        continue;
    }
    $coverage->append(
        RawCodeCoverageData::fromXdebugWithPathCoverage($dump['data']),
        'request-' . $i
    );
    $merged++;
}

if ($merged === 0) {
    fwrite(STDERR, "None of the dumps could be merged.\n");
    exit(1);
}

$outDir = rtrim($options['out'], '/');
(new HtmlFacade)->process($coverage, $outDir);
(new Clover)->process($coverage, $outDir . '/clover.xml');

$report = $coverage->getReport();
$pct = static fn (int $covered, int $total): string => $total > 0 ? sprintf('%.1f%%', 100 * $covered / $total) : 'n/a';

echo "PHP coverage from $merged request dump(s):\n";
echo sprintf(
    "  Lines:    %s (%d/%d)\n",
    $pct($report->numberOfExecutedLines(), $report->numberOfExecutableLines()),
    $report->numberOfExecutedLines(),
    $report->numberOfExecutableLines()
);
echo sprintf(
    "  Branches: %s (%d/%d)\n",
    $pct($report->numberOfExecutedBranches(), $report->numberOfExecutableBranches()),
    $report->numberOfExecutedBranches(),
    $report->numberOfExecutableBranches()
);
echo sprintf(
    "  Paths:    %s (%d/%d)\n",
    $pct($report->numberOfExecutedPaths(), $report->numberOfExecutablePaths()),
    $report->numberOfExecutedPaths(),
    $report->numberOfExecutablePaths()
);
echo sprintf(
    "  Methods:  %s (%d/%d)\n",
    $pct($report->numberOfTestedMethods(), $report->numberOfMethods()),
    $report->numberOfTestedMethods(),
    $report->numberOfMethods()
);
echo "Report: $outDir/index.html\n";
echo "Clover: $outDir/clover.xml\n";
