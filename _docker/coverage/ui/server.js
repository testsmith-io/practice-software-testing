/**
 * Static server for the instrumented Angular build + coverage collector.
 *
 * - Serves distDir with an SPA fallback to index.html (the UI normally runs on
 *   ng serve; in coverage mode we serve a prebuilt, instrumented bundle).
 * - POST /__coverage__?id=<session> stores the posted window.__coverage__
 *   snapshot as raw/coverage-<session>.json (latest snapshot wins; counters
 *   only grow within a page load, so this is lossless).
 * - Auto-refresh: when new coverage arrives, the UI report is regenerated
 *   (report.js) every COVERAGE_REPORT_INTERVAL seconds (default 30, 0 = off).
 * - Serves the generated reports at /__reports/ui/ and /__reports/php/ with a
 *   small injected script that reloads the page when its report changes.
 *
 * Usage: node server.js [distDir] [coverageOutDir] [port]
 */
'use strict';

const http = require('http');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const distDir = process.argv[2] || '/app/dist/toolshop';
const outDir = process.argv[3] || '/coverage-out';
const port = Number(process.argv[4] || 4200);
const rawDir = path.join(outDir, 'raw');

const reportRoots = {
  ui: path.join(outDir, 'report'),
  php: process.env.PHP_REPORT_DIR || '/coverage-out-php/report',
};
const reportInterval = Number(process.env.COVERAGE_REPORT_INTERVAL ?? 30);

fs.mkdirSync(rawDir, { recursive: true });

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.mjs': 'text/javascript; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.json': 'application/json',
  '.map': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.gif': 'image/gif',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.ttf': 'font/ttf',
  '.txt': 'text/plain; charset=utf-8',
  '.webmanifest': 'application/manifest+json',
};

function handleCoveragePost(req, res, url) {
  const chunks = [];
  let size = 0;
  req.on('data', (chunk) => {
    size += chunk.length;
    if (size > 200 * 1024 * 1024) {
      req.destroy();
      return;
    }
    chunks.push(chunk);
  });
  req.on('end', () => {
    try {
      const body = Buffer.concat(chunks).toString('utf8');
      JSON.parse(body); // reject garbage early
      const id = (url.searchParams.get('id') || String(Date.now())).replace(/[^a-zA-Z0-9_-]/g, '');
      fs.writeFileSync(path.join(rawDir, `coverage-${id || 'unknown'}.json`), body);
      coverageDirty = true;
      res.writeHead(204).end();
    } catch {
      res.writeHead(400).end('invalid coverage payload');
    }
  });
}

function serveFile(res, filePath) {
  const ext = path.extname(filePath).toLowerCase();
  res.writeHead(200, {
    'Content-Type': MIME[ext] || 'application/octet-stream',
    'Cache-Control': 'no-store',
  });
  fs.createReadStream(filePath).pipe(res);
}

// ---- auto-refresh: regenerate the UI report when new coverage arrived ------
let coverageDirty = false;
let reportRunning = false;
if (reportInterval > 0) {
  setInterval(() => {
    if (!coverageDirty || reportRunning) return;
    coverageDirty = false;
    reportRunning = true;
    const child = spawn(process.execPath, [path.join(__dirname, 'report.js'), outDir], {
      stdio: 'ignore',
    });
    child.on('exit', (code) => {
      reportRunning = false;
      if (code === 0) console.log(`[coverage-server] UI report regenerated (${new Date().toISOString()})`);
      else coverageDirty = true; // retry next tick
    });
    child.on('error', () => {
      reportRunning = false;
    });
  }, reportInterval * 1000);
  console.log(`[coverage-server] auto-refresh: regenerating the UI report every ${reportInterval}s when new coverage arrives`);
}

// Injected into report pages: reload (keeping scroll position) when the
// report this page belongs to has been regenerated.
const AUTO_RELOAD_SNIPPET = `<script>(function () {
  var section = location.pathname.split('/')[2];
  var last = null;
  setInterval(function () {
    fetch('/__reports/__version').then(function (r) { return r.json(); }).then(function (v) {
      var cur = v[section];
      if (cur == null) return;
      if (last === null) { last = cur; return; }
      if (cur !== last) {
        sessionStorage.setItem('covScroll:' + location.pathname, String(window.scrollY));
        location.reload();
      }
    }).catch(function () {});
  }, 5000);
  window.addEventListener('load', function () {
    var s = sessionStorage.getItem('covScroll:' + location.pathname);
    if (s !== null) {
      sessionStorage.removeItem('covScroll:' + location.pathname);
      window.scrollTo(0, parseInt(s, 10) || 0);
    }
  });
})();</script>`;

const REPORTS_LANDING = `<!DOCTYPE html><html><head><title>Coverage reports</title>
<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;margin:3rem;color:#222}
a{display:block;font-size:1.2rem;margin:.6rem 0}</style></head><body>
<h1>Coverage reports</h1>
<a href="/__reports/php/">PHP (Laravel API) — lines, branches, paths</a>
<a href="/__reports/ui/">TypeScript (Angular UI) — statements, branches, functions, lines</a>
<p>Pages reload automatically when a report is regenerated. A report shows 404
until the first data has been collected and processed.</p>
</body></html>`;

function serveReport(res, section, subPath) {
  const root = reportRoots[section];
  let target = path.normalize(path.join(root, subPath));
  if (!target.startsWith(root)) {
    return res.writeHead(403).end();
  }
  if (fs.existsSync(target) && fs.statSync(target).isDirectory()) {
    target = path.join(target, 'index.html');
  }
  if (!fs.existsSync(target) || !fs.statSync(target).isFile()) {
    return res.writeHead(404).end('report not generated yet');
  }
  if (target.endsWith('.html')) {
    let html = fs.readFileSync(target, 'utf8');
    html = html.includes('</body>')
      ? html.replace('</body>', AUTO_RELOAD_SNIPPET + '</body>')
      : html + AUTO_RELOAD_SNIPPET;
    res.writeHead(200, { 'Content-Type': MIME['.html'], 'Cache-Control': 'no-store' });
    return res.end(html);
  }
  return serveFile(res, target);
}

const server = http.createServer((req, res) => {
  const url = new URL(req.url, `http://${req.headers.host || 'localhost'}`);

  if (url.pathname === '/__coverage__') {
    if (req.method === 'POST') return handleCoveragePost(req, res, url);
    return res.writeHead(405).end();
  }

  if (url.pathname === '/__reports' || url.pathname === '/__reports/') {
    res.writeHead(200, { 'Content-Type': MIME['.html'], 'Cache-Control': 'no-store' });
    return res.end(REPORTS_LANDING);
  }

  if (url.pathname === '/__reports/__version') {
    const versions = {};
    for (const [section, dir] of Object.entries(reportRoots)) {
      try {
        versions[section] = fs.statSync(path.join(dir, 'index.html')).mtimeMs;
      } catch {
        versions[section] = null;
      }
    }
    res.writeHead(200, { 'Content-Type': MIME['.json'], 'Cache-Control': 'no-store' });
    return res.end(JSON.stringify(versions));
  }

  const reportMatch = url.pathname.match(/^\/__reports\/(php|ui)(\/.*)?$/);
  if (reportMatch) {
    return serveReport(res, reportMatch[1], decodeURIComponent(reportMatch[2] || '/'));
  }

  if (req.method !== 'GET' && req.method !== 'HEAD') {
    return res.writeHead(405).end();
  }

  const requested = path.normalize(path.join(distDir, decodeURIComponent(url.pathname)));
  if (!requested.startsWith(distDir)) {
    return res.writeHead(403).end();
  }

  if (fs.existsSync(requested) && fs.statSync(requested).isFile()) {
    return serveFile(res, requested);
  }

  // SPA fallback: anything without a file extension gets index.html.
  if (!path.extname(url.pathname)) {
    return serveFile(res, path.join(distDir, 'index.html'));
  }

  res.writeHead(404).end('not found');
});

server.listen(port, '0.0.0.0', () => {
  console.log(`[coverage-server] serving ${distDir} on http://0.0.0.0:${port}`);
  console.log(`[coverage-server] collecting coverage into ${rawDir}`);
});
