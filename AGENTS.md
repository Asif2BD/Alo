# Working on Alo

Read `CONTRIBUTING.md`, `SECURITY.md`, and `docs/agents.md` before making changes.
The default branch is `master`; make focused changes through a pull request.

- `alo.php` is the complete distributable. Keep diagnostics read-only, authenticate before collection, and retain the single-file deployment.
- Clarity and Pulse share the renderer and insight rules. Theme is independent of view. Store only presentation preferences; never persist credentials or private telemetry in browser storage.
- Treat collector strings as untrusted data. Preserve nulls, units, scope and time windows. Lifetime counters do not prove current incidents. Use deterministic fixture data in screenshots.
- `site/build.sh` generates `public/`; never serve the checkout. Preserve deployment access configuration. Public demos must never execute a live collector.
- Update the README, agent contract, manifest and public discovery documents together when behavior changes.
- Run `php tests/run.php`, `python3 tests/test_http.py`, `node tests/dashboard.mjs`, `python3 tests/test_install.py`, and `bash site/build.sh`. Set `PHP_BINARY` if needed. CI supports PHP 8.3/8.4/8.5.
- Keep tokens and setup JSON out of commits and logs. Do not weaken authentication, add remote command execution, or claim a security certification.

Runtime expansion belongs behind the language-neutral snapshot contract. See
`docs/roadmap.md` for planned standalone collectors and the review requirements.
