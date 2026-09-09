# Beyond PHP: the Alo roadmap

Alo 2 ships as a single PHP file. It also provides a language-neutral snapshot format, a capability manifest, and a read-only MCP interface. These are the foundation for broader coverage, not a claim that other runtimes are already monitored.

## Delivered in this rebuild

- PHP runtime and OPcache diagnostics.
- Linux host-visible resources and visible cgroup v2 root metrics.
- Web server family identification, including LiteSpeed/LSAPI.
- Clarity/Pulse dashboards, Launch homepage, authenticated JSON/OpenMetrics and read-only MCP.
- Bounded browser session history and local JSONL watch, with explicit evidence windows.
- Explicit metric units, scope, timestamps, unavailable values, and interpretation guidance.

## Next: improve server coverage

- Validate real deployments on LiteSpeed Enterprise/OpenLiteSpeed, Apache, Nginx, Caddy, and IIS.
- Add reliable process-specific cgroup resolution, cgroup v1 coverage, and better container scope detection.
- Explore opt-in native server collectors: LiteSpeed worker/queue statistics, Apache status, and Nginx status. Each needs an explicit read-only source, authorization policy, and bounded collection budget.
- Add inode coverage and review mount-path disclosure; per-filesystem usage already exists behind authentication.
- Add configurable thresholds with documented defaults and deterministic tests.

## Then: collectors beyond PHP

A future standalone collector could be written in Go (preferred starting point), Node.js, or Python, exposing the same snapshot contract. Keep collection, transport, and presentation separate:

| Layer | Contract |
| --- | --- |
| Collector | Bounded read-only observations with source, units, timestamp, scope, and availability |
| Snapshot schema | Versioned data shared across runtime implementations |
| Transport | Authenticated JSON and MCP with the same access boundary |
| Presentation | Explain the snapshot without depending on PHP internals |

Potential runtime modules include Node.js heap/event-loop observations, Python process/runtime data, and Go runtime metrics. Database/service health should be explicit opt-in with least-privilege credentials and server-side allowlists, never arbitrary targets supplied by a web visitor or agent.

No remote command runner, automatic remediation, public server inventory, or shared cross-tenant collector is planned as part of the PHP probe. Historical storage, multi-server views, OAuth, and fine-grained token scopes require separate designs and security reviews.

## Contribution gate for new collectors

Describe the user problem, data source, required permissions, platforms, maximum collection time/size, privacy exposure, schema additions, and failure behavior. Supply fixture tests plus evidence from the actual supported environment. Preserve single-file PHP deployment until an alternative package is separately ready and documented.
