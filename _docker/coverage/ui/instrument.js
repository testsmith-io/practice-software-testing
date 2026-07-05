/**
 * Instruments the Angular build output (dist) with Istanbul so that running
 * the app in a browser fills window.__coverage__.
 *
 * - Only bundles whose source map references the app's src/ files are
 *   instrumented (polyfills / third-party-only bundles are left alone).
 * - The input source map is embedded in the coverage metadata, so reports
 *   map back to the original TypeScript sources.
 * - A zero-hit "baseline" coverage of every instrumented bundle is written to
 *   the output dir, so lazy chunks that are never visited still show up as 0%.
 * - Injects the coverage-reporter snippet into index.html; the snippet POSTs
 *   window.__coverage__ to the collector in server.js.
 *
 * Usage: node instrument.js [distDir] [coverageOutDir]
 */
'use strict';

const fs = require('fs');
const path = require('path');
const { createInstrumenter } = require('istanbul-lib-instrument');
const convertSourceMap = require('convert-source-map');

const distDir = process.argv[2] || '/app/dist/toolshop';
const outDir = process.argv[3] || '/coverage-out';

function jsFiles(dir) {
  const result = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      result.push(...jsFiles(full));
    } else if (entry.name.endsWith('.js')) {
      result.push(full);
    }
  }
  return result;
}

function loadSourceMap(file, code) {
  const external = file + '.map';
  if (fs.existsSync(external)) {
    try {
      return JSON.parse(fs.readFileSync(external, 'utf8'));
    } catch {
      return null;
    }
  }
  try {
    const inline = convertSourceMap.fromSource(code);
    return inline ? inline.toObject() : null;
  } catch {
    return null;
  }
}

function coversAppSources(map) {
  return (
    map &&
    Array.isArray(map.sources) &&
    map.sources.some((s) => {
      const n = String(s).replace(/\\/g, '/');
      return !n.includes('node_modules') && n.includes('src/');
    })
  );
}

const instrumenter = createInstrumenter({ esModules: true, compact: true, produceSourceMap: false });
const baseline = {};
let instrumented = 0;

for (const file of jsFiles(distDir)) {
  let code = fs.readFileSync(file, 'utf8');
  const map = loadSourceMap(file, code);
  if (!coversAppSources(map)) {
    console.log(`[instrument] skip (no app sources): ${path.relative(distDir, file)}`);
    continue;
  }
  // The original source map no longer matches once instrumented; drop the reference.
  code = code.replace(/\/\/# sourceMappingURL=.*$/m, '');
  try {
    const output = instrumenter.instrumentSync(code, file, map);
    fs.writeFileSync(file, output);
    baseline[file] = instrumenter.lastFileCoverage();
    instrumented++;
    console.log(`[instrument] ok: ${path.relative(distDir, file)}`);
  } catch (err) {
    console.warn(`[instrument] FAILED (left uninstrumented): ${file}: ${err.message}`);
  }
}

if (instrumented === 0) {
  console.error('[instrument] No bundle was instrumented — coverage will be empty.');
  process.exit(1);
}

fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(path.join(outDir, 'baseline.json'), JSON.stringify(baseline));

// Inject the reporter snippet into index.html.
const indexPath = path.join(distDir, 'index.html');
let html = fs.readFileSync(indexPath, 'utf8');
if (!html.includes('__coverage-reporter.js')) {
  html = html.replace('</body>', '<script src="__coverage-reporter.js"></script></body>');
  fs.writeFileSync(indexPath, html);
}
fs.copyFileSync(path.join(__dirname, 'reporter-snippet.js'), path.join(distDir, '__coverage-reporter.js'));

console.log(`[instrument] ${instrumented} bundle(s) instrumented, baseline written to ${outDir}/baseline.json`);
