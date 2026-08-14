<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }

        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>

<body>
<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
<script>
    window.onload = function () {
        // Build a system
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: "{!! $urlToDocs !!}",
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
            docExpansion: "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
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

<!-- "Copy for AI" button: copies the full API docs as clean Markdown -->
<script>window.__SWAGGER_DOCS_URL__ = "{!! $urlToDocs !!}";</script>
@verbatim
<script>
(function () {
    const DOCS_URL = window.__SWAGGER_DOCS_URL__;
    const HTTP_METHODS = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];

    const clean = (t) => (t == null ? '' : String(t)).replace(/\s+/g, ' ').trim();
    const refName = (ref) => ref.split('/').pop();

    function resolveRef(spec, ref) {
        const parts = ref.replace(/^#\//, '').split('/');
        let cur = spec;
        for (const p of parts) {
            if (cur == null) return null;
            cur = cur[p];
        }
        return cur;
    }

    function schemaType(spec, schema) {
        if (!schema) return '';
        if (schema.$ref) return refName(schema.$ref);
        if (schema.type === 'array') return schemaType(spec, schema.items) + '[]';
        if (schema.enum) return (schema.type || 'enum') + ' (' + schema.enum.join(' | ') + ')';
        return schema.type || (schema.properties ? 'object' : '');
    }

    function renderSchema(spec, schema, seen) {
        seen = seen || new Set();
        if (schema && schema.$ref) {
            const n = refName(schema.$ref);
            if (seen.has(n)) return '`' + n + '` (recursive)\n';
            seen.add(n);
            schema = resolveRef(spec, schema.$ref);
        }
        if (!schema) return '';
        if (schema.type === 'array' && schema.items) {
            return 'Array of:\n\n' + renderSchema(spec, schema.items, seen);
        }
        if (!schema.properties) return '`' + schemaType(spec, schema) + '`\n';
        const required = new Set(schema.required || []);
        let md = '| Field | Type | Required | Description |\n|---|---|---|---|\n';
        for (const [name, prop] of Object.entries(schema.properties)) {
            md += `| ${name} | ${schemaType(spec, prop)} | ${required.has(name) ? 'yes' : 'no'} | ${clean(prop.description)} |\n`;
        }
        return md;
    }

    function toMarkdown(spec) {
        const info = spec.info || {};
        let md = `# ${info.title || 'API'}${info.version ? ` (v${info.version})` : ''}\n\n`;
        if (info.description) md += clean(info.description) + '\n\n';

        if (Array.isArray(spec.servers) && spec.servers.length) {
            md += '**Servers:**\n' + spec.servers
                .map((s) => `- ${s.url}${s.description ? ` — ${s.description}` : ''}`)
                .join('\n') + '\n\n';
        } else if (spec.host) {
            const scheme = (spec.schemes && spec.schemes[0]) || 'https';
            md += `**Base URL:** ${scheme}://${spec.host}${spec.basePath || ''}\n\n`;
        }
        md += '---\n\n';

        const byTag = {};
        for (const [path, item] of Object.entries(spec.paths || {})) {
            for (const method of HTTP_METHODS) {
                const op = item[method];
                if (!op) continue;
                const tag = (op.tags && op.tags[0]) || 'default';
                (byTag[tag] = byTag[tag] || []).push({ path, method: method.toUpperCase(), op });
            }
        }

        for (const [tag, ops] of Object.entries(byTag)) {
            md += `## ${tag}\n\n`;
            for (const { path, method, op } of ops) {
                md += `### ${method} ${path}\n\n`;
                if (op.summary) md += clean(op.summary) + '\n\n';
                if (op.description) md += clean(op.description) + '\n\n';

                const params = op.parameters || [];
                if (params.length) {
                    md += '**Parameters:**\n\n| Name | In | Type | Required | Description |\n|---|---|---|---|---|\n';
                    for (const p of params) {
                        const type = p.schema ? schemaType(spec, p.schema) : (p.type || '');
                        md += `| ${p.name} | ${p.in} | ${type} | ${p.required ? 'yes' : 'no'} | ${clean(p.description)} |\n`;
                    }
                    md += '\n';
                }

                if (op.requestBody) {
                    const content = op.requestBody.content || {};
                    const json = content['application/json'];
                    md += '**Request Body:**\n\n';
                    md += json && json.schema
                        ? renderSchema(spec, json.schema) + '\n'
                        : Object.keys(content).join(', ') + '\n\n';
                }

                if (op.responses) {
                    md += '**Responses:**\n\n| Code | Description |\n|---|---|\n';
                    for (const [code, res] of Object.entries(op.responses)) {
                        md += `| ${code} | ${clean(res.description)} |\n`;
                    }
                    md += '\n';
                }
                md += '\n';
            }
        }
        return md.trim() + '\n';
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
            try {
                document.execCommand('copy');
                resolve();
            } catch (e) {
                reject(e);
            } finally {
                document.body.removeChild(ta);
            }
        });
    }

    function addButton() {
        const btn = document.createElement('button');
        btn.id = 'copy-for-ai';
        btn.type = 'button';
        btn.textContent = '📋 Copy for AI';
        btn.title = 'Copy the full API documentation as Markdown for Claude, Codex, Cursor, …';
        Object.assign(btn.style, {
            position: 'fixed', top: '12px', right: '16px', zIndex: '9999',
            padding: '8px 14px', fontSize: '14px', fontWeight: '600', fontFamily: 'sans-serif',
            color: '#fff', background: '#4990e2', border: 'none', borderRadius: '6px',
            cursor: 'pointer', boxShadow: '0 1px 4px rgba(0,0,0,.25)'
        });

        btn.addEventListener('click', async function () {
            const label = '📋 Copy for AI';
            btn.disabled = true;
            btn.textContent = 'Preparing…';
            try {
                const res = await fetch(DOCS_URL, { headers: { Accept: 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const spec = await res.json();
                await copyText(toMarkdown(spec));
                btn.textContent = '✓ Copied!';
                btn.style.background = '#3aa675';
            } catch (e) {
                console.error('Copy for AI failed:', e);
                btn.textContent = '✗ Failed';
                btn.style.background = '#d9534f';
            } finally {
                setTimeout(() => {
                    btn.textContent = label;
                    btn.style.background = '#4990e2';
                    btn.disabled = false;
                }, 2000);
            }
        });

        document.body.appendChild(btn);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addButton);
    } else {
        addButton();
    }
})();
</script>
@endverbatim
</body>
</html>
