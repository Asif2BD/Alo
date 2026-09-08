# Security model

Alo is an administrator-only, read-only snapshot tool, not a vulnerability scanner or proof that a server is secure. Keep PHP, the OS, and web server patched.

## Access boundary

Web access requires HTTPS and a generated 256-bit random token. Only its SHA-256 digest is configured in `ALO_TOKEN_HASH`. SHA-256 is appropriate for these high-entropy random tokens; do not substitute a human password. Authentication uses Basic (username `alo`) or Bearer and constant-time digest comparison. URL credentials are never accepted. An unconfigured installation returns 503 before collecting data. Local CLI access trusts OS permissions.

HTML/JSON/manifest routes accept GET only. MCP accepts bounded JSON-RPC POST messages, but its three tools only read. No shell commands, arbitrary file or network targets, email sending, database connections, stress tests, application writes, session mutations, or `phpinfo()` endpoints exist. Fixed local files are read with size limits. Errors and unavailable probes do not expose diagnostic internals to unauthenticated users.

HTML is escaped and uses nonce CSP. JSON contains only allowlisted fields. Every web response is `no-store`. No JSONP, CORS, external assets, or telemetry. MCP rejects any browser Origin, caps requests at 64 KiB, limits JSON nesting, rejects batches and arbitrary tool arguments, and provides no streaming sessions or server-initiated operations.

## Deployment responsibilities

- Place Alo behind a VPN, administrator IP allowlist, or identity-aware access proxy. Rate-limit all requests, including failed authentication, at that boundary. There is no persistent application rate limiter. Random tokens resist guessing, not traffic exhaustion. An authenticated snapshot occupies a worker for roughly 100 ms.
- Deploy only `alo.php`, never the repository or tests. Keep the token hash outside the document root in the web PHP environment. Do not make a public `.env` file. PHP-FPM `clear_env` may require a specific pool `env[ALO_TOKEN_HASH]` entry; LSAPI must receive its external-app environment.
- Preserve Authorization when forwarding to PHP and exclude it from logs. Use HTTPS for the full client connection. Startup/parse errors must be suppressed by production PHP configuration, not just script code.
- For TLS termination, set `ALO_TRUSTED_PROXIES` to exact comma-separated IPs you control. The proxy must **overwrite**, not append or preserve, `X-Forwarded-Proto`, and direct origin access must be restricted. No wildcard/CIDR or forwarded client-IP trust exists.
- `ALO_ALLOW_LOCAL_HTTP=1` works only with PHP's development server and a loopback peer. Never reverse-proxy this server: a loopback proxy would share the exception. It is not a production server.
- Basic credentials can remain cached until the browser closes. Use private browsing on shared machines. Rotate by generating a new token/hash and reloading the PHP handler. There is no application logout or session cookie.
- Set `display_errors=Off` and `log_errors=On` in production. Alo suppresses web display errors, while reporting the original setting for diagnosis.
- Exports include platform details, PHP configuration, extension names, and interface names. Treat them as private. Hostnames, IPs, environment variables, document roots, credentials, and cached script paths are not intentionally collected.
- MCP supports manually configured credentials, not an OAuth authorization server. Connect only trusted clients; all authorized clients have the same read scope. Tool annotations are descriptive, not an additional permission system.

## Limitations

Metrics may describe the host rather than the container. Cgroup v1 and nested worker limits are not resolved. Missing metrics are not passing checks. PHP lifecycle dates are bundled, not a live patch audit. Agent clients must treat returned strings as data, not instructions. Automated regressions do not establish complete security; review the deployment boundary independently.

## Reporting

Report suspected vulnerabilities privately through the maintainer's contact options at https://ar.bd/, or GitHub private vulnerability reporting if enabled. Do not publish credentials, server snapshots, or exploit details in public issues.
