# Alo 2.2 deployment verification

Verified 2026-09-09 after PR #13 merged and xCloud push-deployed `master`.

- PHP 8.3, 8.4 and 8.5 CI passed: PHP logic, real HTTP/MCP authorization,
  bounded CLI watch, restricted-host fallback, installer integrity and session-rate tests.
- Live Launch and the static sample demo rendered. Clarity/Pulse switching,
  independent themes, preference persistence, expand-all and session clear worked.
  The sample disables collection and labels all illustrative data explicitly.
- The live installer downloaded the exact reviewed payload, completed setup in a
  disposable local directory, and wrote the digest with mode 0600.
- Live `/alo.php` returned 401 without credentials, with private/no-store headers.
  No production token was retrieved or supplied; live authenticated metrics were not tested.
- xCloud reports deployed/100%/no failed steps and push-deploy from `master`.
  Its redeploy-log list is empty, so deployment identity is checked through public files.
- Current screenshots in this directory's sibling `screenshots/` were captured
  from the deployed public pages. Desktop captures are 1348×926. The current mobile
  JPEG was captured in a 390-pixel public iframe at `/mobile.html`. Clarity and
  Pulse both had equal client and scroll widths (no horizontal overflow). This
  verifies the responsive Chromium layout, not every mobile browser or device.

## AIScan

[Report: 95/100, Level 5 — Agent-Native](https://aiscan.site/r/95qy55a3)

The raw public report is in `aiscan-2.2.json`, measured
`2026-09-09T23:09:50.377Z`, rubric `2026.08.2`.

Remaining partial checks:

- M4: the scanner interprets the free SoftwareApplication offer as commerce and
  asks for paid plans. Alo has no paid plans; do not fabricate pricing metadata.
- E4: `www.alo.asif.dev` serves a duplicate origin. A canonical host redirect
  requires a hosting/CDN configuration change.

The Markdown check passes via `/index.md`. That does **not** establish negotiated
Markdown at `/`; the existing Nginx exact-root block still chooses HTML. A concrete
replacement snippet is documented in `site/README.md`. The connected xCloud tools
expose reads, not this configuration write, so it was not applied.

## xSpeed

The public scan page and REST scan request returned `502 Bad Gateway` with
`[Errno 111] Connection refused` from this environment. No completed performance
score is available. Do not interpret the failure as a failing Alo performance test.
