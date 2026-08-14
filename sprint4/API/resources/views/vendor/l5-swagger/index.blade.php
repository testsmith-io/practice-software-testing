<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
    html
    {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }
    *,
    *:before,
    *:after
    {
        box-sizing: inherit;
    }

    body {
      margin:0;
      background: #fafafa;
    }
    </style>
</head>

<body>
<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
<script>
    window.onload = function() {
        // Build a system
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: "{!! str_contains($urlToDocs, 'localhost') ? str_replace('https', 'http', $urlToDocs) : $urlToDocs !!}",
            operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
            configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
            validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
            oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: "StandaloneLayout",
            docExpansion : "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
            deepLinking: true,
            filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
            persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",

        })

        window.ui = ui

        @if(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
        ui.initOAuth({
            usePkceWithAuthorizationCodeGrant: "{!! (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
        })
        @endif
    }
</script>


<!-- "Copy for AI" buttons: one per operation, copies that endpoint as clean Markdown -->
<script>window.__SWAGGER_DOCS_URL__ = "{!! $urlToDocs !!}";</script>
@verbatim
<script>
(function () {
    const DOCS_URL = window.__SWAGGER_DOCS_URL__;

    const clean = (t) => (t == null ? '' : String(t)).replace(/\s+/g, ' ').trim();
    const refName = (ref) => String(ref).split('/').pop();
    const pretty = (v) => JSON.stringify(v, null, 2);

    // Fetch the OpenAPI spec once and reuse it for every button.
    let specPromise = null;
    function loadSpec() {
        if (!specPromise) {
            specPromise = fetch(DOCS_URL, { headers: { Accept: 'application/json' } })
                .then((r) => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
        }
        return specPromise;
    }

    function resolveRef(spec, ref) {
        const parts = String(ref).replace(/^#\//, '').split('/');
        let cur = spec;
        for (const p of parts) {
            if (cur == null) return null;
            cur = cur[p];
        }
        return cur;
    }

    function baseUrl(spec) {
        if (Array.isArray(spec.servers) && spec.servers.length) return spec.servers[0].url;
        if (spec.host) return ((spec.schemes && spec.schemes[0]) || 'https') + '://' + spec.host + (spec.basePath || '');
        return '';
    }

    // Collect every $ref value (deduped, first-seen order) reachable from `obj`.
    function collectRefs(obj, acc) {
        if (!obj || typeof obj !== 'object') return;
        if (Array.isArray(obj)) { obj.forEach((x) => collectRefs(x, acc)); return; }
        for (const [key, value] of Object.entries(obj)) {
            if (key === '$ref' && typeof value === 'string') {
                if (!acc.includes(value)) acc.push(value);
            } else {
                collectRefs(value, acc);
            }
        }
    }

    // Markdown for a single endpoint.
    function operationToMarkdown(spec, path, method, item) {
        const op = item[method.toLowerCase()];
        const fullUrl = baseUrl(spec) + path;

        let md = `# ${method} ${fullUrl}\n\n`;
        md += `- Method: \`${method}\`\n`;
        md += `- OpenAPI path: \`${path}\`\n`;
        md += `- Full URL: \`${fullUrl}\`\n`;
        if (op.summary) md += `- Summary: ${clean(op.summary)}\n`;
        md += '\n';

        if (op.description) {
            md += `## Description\n\n${clean(op.description)}\n\n`;
        }

        // Path-level params are shared by every method on the path.
        const params = (item.parameters || []).concat(op.parameters || []);
        if (params.length) {
            md += '## Parameters\n\n';
            for (const p of params) {
                const schema = p.schema || (p.type ? { type: p.type } : {});
                md += `- \`${p.name}\` in \`${p.in}\` ${p.required ? 'required' : 'optional'}\n`;
                if (p.description) md += `  - ${clean(p.description)}\n`;
                md += `  - Schema: \`${JSON.stringify(schema)}\`\n`;
            }
            md += '\n';
        }

        if (op.requestBody) {
            md += '## Request Body\n\n```json\n' + pretty(op.requestBody) + '\n```\n\n';
        }

        if (op.responses) {
            md += '## Responses\n\n';
            for (const [code, res] of Object.entries(op.responses)) {
                md += `### ${code}\n\n`;
                if (res.description) md += `${clean(res.description)}\n\n`;
                if (res.content) md += '```json\n' + pretty(res.content) + '\n```\n\n';
            }
        }

        // Dump every component schema referenced by this operation (transitively).
        const refs = [];
        collectRefs(item.parameters, refs);
        collectRefs(op.parameters, refs);
        collectRefs(op.requestBody, refs);
        collectRefs(op.responses, refs);
        for (let i = 0; i < refs.length; i++) collectRefs(resolveRef(spec, refs[i]), refs);
        if (refs.length) {
            md += '## Referenced Schemas\n\n';
            for (const ref of refs) {
                md += `### ${refName(ref)}\n\n`;
                md += '```json\n' + pretty(resolveRef(spec, ref)) + '\n```\n\n';
            }
        }

        return md.trimEnd() + '\n';
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise((resolve, reject) => {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { document.execCommand('copy'); resolve(); }
            catch (e) { reject(e); }
            finally { document.body.removeChild(ta); }
        });
    }

    function rowToMarkdown(summary) {
        const method = clean(summary.querySelector('.opblock-summary-method')?.textContent).toUpperCase();
        const pathEl = summary.querySelector('.opblock-summary-path, .opblock-summary-path__deprecated');
        const path = (pathEl?.getAttribute('data-path') || clean(pathEl?.textContent) || '').trim();
        return loadSpec().then((spec) => {
            const item = (spec.paths || {})[path];
            if (!item || !item[method.toLowerCase()]) {
                throw new Error('Operation not found: ' + method + ' ' + path);
            }
            return operationToMarkdown(spec, path, method, item);
        });
    }

    function makeButton(summary) {
        const label = '📋 Copy for AI';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'copy-for-ai-btn';
        btn.textContent = label;
        btn.title = 'Copy this endpoint as Markdown for Claude, Codex, Cursor, …';
        btn.addEventListener('click', function (e) {
            // Don't let the click toggle the operation open/closed.
            e.preventDefault();
            e.stopPropagation();
            btn.disabled = true;
            btn.textContent = '…';
            rowToMarkdown(summary)
                .then((md) => copyText(md))
                .then(() => { btn.textContent = '✓ Copied'; })
                .catch((err) => { console.error('Copy for AI failed:', err); btn.textContent = '✗ Failed'; })
                .then(() => {
                    setTimeout(() => { btn.textContent = label; btn.disabled = false; }, 1500);
                });
        });
        return btn;
    }

    function injectButtons() {
        document.querySelectorAll('.opblock-summary').forEach((summary) => {
            if (summary.querySelector('.copy-for-ai-btn')) return;
            summary.appendChild(makeButton(summary));
        });
    }

    const style = document.createElement('style');
    style.textContent =
        '.copy-for-ai-btn{flex:none;align-self:center;margin:0 8px;padding:4px 10px;' +
        'font-size:12px;font-weight:600;font-family:sans-serif;color:#fff;background:#4990e2;' +
        'border:none;border-radius:4px;cursor:pointer;white-space:nowrap}' +
        '.copy-for-ai-btn:hover{background:#357abd}' +
        '.copy-for-ai-btn:disabled{opacity:.8;cursor:default}';
    document.head.appendChild(style);

    // Swagger UI renders asynchronously and re-renders on filter/tag toggle,
    // so re-inject (debounced) whenever the DOM changes.
    let scheduled = false;
    function schedule() {
        if (scheduled) return;
        scheduled = true;
        setTimeout(() => { scheduled = false; injectButtons(); }, 150);
    }
    const target = document.getElementById('swagger-ui') || document.body;
    new MutationObserver(schedule).observe(target, { childList: true, subtree: true });
    schedule();
})();
</script>
@endverbatim

</body>
</html>
