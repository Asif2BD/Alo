# Contributing to Alo

Thanks for helping make server diagnostics clearer, safer, and more useful. Bug reports, documentation, accessibility improvements, hosting validation, and code contributions are welcome.

## Before you start

Search existing issues and pull requests. For a new collector, dependency, authentication change, or JSON schema change, open an issue describing the problem and proposed scope before building it. Report security vulnerabilities privately using [SECURITY.md](SECURITY.md).

A helpful bug report includes PHP version/SAPI, OS, web server family, reproduction steps, expected behavior, actual behavior, and a redacted error excerpt. Never attach a live token, Authorization header, unredacted environment, production snapshot, or private IP/path.

## Development workflow

1. Fork and clone the repository. Create a focused branch from `master`.
2. Use 64-bit PHP 8.3+ and Python 3. No Composer dependencies are needed.
3. Generate a local token with `php alo.php --generate-token`, configure its hash locally, and run `ALO_ALLOW_LOCAL_HTTP=1 php -S 127.0.0.1:8080`.
4. Make the smallest coherent change. Keep the distributable self-contained in `alo.php`.
5. Run:

   ```sh
   php -l alo.php
   php tests/run.php
   python3 tests/test_http.py
   php -d disable_functions=file_get_contents,sys_getloadavg,disk_total_space,disk_free_space,opcache_get_status alo.php --json
   ```

   Set `PHP_BINARY` for the HTTP tests if PHP is not on PATH. CI tests PHP 8.3, 8.4, and 8.5.
6. Open a PR explaining the problem, behavior change, validation, and limitations. Include sanitized before/after screenshots for UI changes.

## Code and security standards

- Use strict types, four-space indentation, explicit return types, and small named functions under the `Alo` namespace. Preserve the GPL header.
- Authentication must run before collectors. Never add public diagnostics, URL tokens, arbitrary shell commands, user-selected paths/hosts, or secrets to reports.
- External probes require a separately reviewed allowlist, time/size budgets, and a threat model. Do not silently add network requests or application writes.
- Treat OS and runtime strings as untrusted. Escape HTML; serialize JSON through `json_encode`; never insert data into executable JavaScript.
- Use `null` for missing metrics, retain units and collection scope, and distinguish host metrics from container limits. A collector failure must degrade gracefully.
- Preserve `schema_version: 1` semantics. Additive optional fields are acceptable; breaking meanings require a new version and migration notes.
- Add meaningful tests for access changes, parser edge cases, threshold calculations, and MCP behavior. Do not replace real HTTP checks with mocks of the implementation.

## Design standards

The dashboard should be calm, clear, and useful at a glance. Reuse the existing color tokens, typography, spacing, and card hierarchy. Avoid decorative charts, fabricated health scores, and unexplained acronyms. Show missing data and metric scope near the reading. Keep keyboard focus visible, controls labeled, light/dark contrast readable, and the layout usable at 390 px. Do not add external fonts, icon CDNs, analytics, or frameworks to the single-file probe.

## Reproduce screenshots

Screenshots must use deterministic sample data, never live production data:

```sh
php tests/render_fixture.php > /tmp/alo-preview.html
python3 -m http.server 8081 --bind 127.0.0.1 --directory /tmp
```

Open `http://127.0.0.1:8081/alo-preview.html` with a 1440×1100 desktop viewport, then capture a full-page screenshot. Repeat in dark mode, and at 390×844 for mobile. The fixture marks the page as illustrative sample data. Save screenshots to `docs/screenshots/desktop.png`, `desktop-dark.png`, and `mobile.png`. Verify no horizontal overflow, cropped controls, or private data. No preview fixture endpoint exists in the production file.

## Pull request checklist

- [ ] Explain why the change is needed and what users will see.
- [ ] Pass syntax, parser/security, and HTTP/MCP tests.
- [ ] Handle restricted hosting and unavailable data.
- [ ] Update docs, manifest, and schema notes if behavior changes.
- [ ] Attach sample-data screenshots for UI changes.
- [ ] Preserve the single-file deployment and no-telemetry promise.
- [ ] Disclose environments tested; do not label fixture tests as live hosting validation.

## Broader runtime coverage

Alo's language-neutral JSON contract is the extension boundary. See [the roadmap](docs/roadmap.md) before adding a collector for Node.js, Python, Go, databases, or server-native metrics. New runtime collectors must reuse the units, scope, timestamp, and access principles rather than embedding remote administration in the PHP probe.

Contributions are distributed under the project's GPL-3.0-only license. Be respectful, specific, and constructive in reviews.
