/**
 * Generates HTML + lcov reports from the coverage snapshots collected by
 * server.js (plus the zero-hit baseline from instrument.js), remapped to the
 * original TypeScript sources via the embedded input source maps.
 *
 * Usage: node report.js [coverageOutDir] [reportDir]
 */
'use strict';

const fs = require('fs');
const path = require('path');
const libCoverage = require('istanbul-lib-coverage');
const libSourceMaps = require('istanbul-lib-source-maps');
const libReport = require('istanbul-lib-report');
const reports = require('istanbul-reports');

async function main() {
  const outDir = process.argv[2] || '/coverage-out';
  const reportDir = process.argv[3] || path.join(outDir, 'report');
  const rawDir = path.join(outDir, 'raw');

  const map = libCoverage.createCoverageMap({});

  const baselinePath = path.join(outDir, 'baseline.json');
  if (fs.existsSync(baselinePath)) {
    map.merge(JSON.parse(fs.readFileSync(baselinePath, 'utf8')));
  }

  let snapshots = 0;
  if (fs.existsSync(rawDir)) {
    for (const file of fs.readdirSync(rawDir)) {
      if (!file.endsWith('.json')) continue;
      try {
        map.merge(JSON.parse(fs.readFileSync(path.join(rawDir, file), 'utf8')));
        snapshots++;
      } catch (err) {
        console.warn(`[report] skipping unreadable snapshot ${file}: ${err.message}`);
      }
    }
  }

  if (map.files().length === 0) {
    console.error(`[report] No coverage data in ${outDir}. Use the app in the browser first.`);
    process.exit(1);
  }

  // Remap bundle coverage onto the original TS sources, then keep only src/**.
  const store = libSourceMaps.createSourceMapStore();
  const remapped = await store.transformCoverage(map);
  const filtered = libCoverage.createCoverageMap({});
  for (const file of remapped.files()) {
    const normalized = file.replace(/\\/g, '/');
    if (normalized.includes('/src/') && !normalized.includes('node_modules')) {
      filtered.addFileCoverage(remapped.fileCoverageFor(file));
    }
  }

  if (filtered.files().length === 0) {
    console.error('[report] Coverage did not remap to any src/ file — check that source maps were embedded.');
    process.exit(1);
  }

  fs.mkdirSync(reportDir, { recursive: true });
  const context = libReport.createContext({
    dir: reportDir,
    coverageMap: filtered,
    defaultSummarizer: 'nested',
    sourceFinder: store.sourceFinder.bind(store),
  });
  reports.create('html').execute(context);
  reports.create('lcovonly', { file: 'lcov.info' }).execute(context);
  reports.create('text-summary').execute(context);

  console.log(`\n[report] merged ${snapshots} snapshot(s) + baseline`);
  console.log(`[report] HTML: ${reportDir}/index.html`);
  console.log(`[report] LCOV: ${reportDir}/lcov.info`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
