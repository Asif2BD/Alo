<div align="center">

# alo.
### A little light on your server.

**A private, single-file server dashboard. Built for humans. Ready for AI agents.**

[Quick start](#quick-start) · [AI & MCP](docs/agents.md) · [Server support](docs/hosting.md) · [Contribute](CONTRIBUTING.md) · [Security](SECURITY.md) · [Roadmap](docs/roadmap.md)

PHP **8.3–8.5** · **No dependencies** · **Read-only** · **GPLv3**

![Alo desktop dashboard with resource cards and actionable observations](docs/screenshots/desktop.png)

*Actual Alo interface rendered with illustrative sample data. This is not a live server or benchmark.*

</div>

## Why Alo?

Understand the environment serving your application without installing a monitoring stack. Upload one PHP file, authenticate, and see the resource usage, runtime details, and configuration observations that matter. Alo never sends telemetry or loads third-party assets.

| For people | For agents | For administrators |
| --- | --- | --- |
| Clean, responsive dashboard | Authenticated JSON snapshots | HTTPS and generated access tokens |
| Light and dark themes | Read-only MCP tools | No shell execution or database credentials |
| Resource pressure explained | Explicit units, scope, and missing data | Locked until configured |
| Manual refresh and JSON export | Machine-readable capability manifest | One file; no writable app storage |

## What it shows

The dashboard opens with radial gauges, a CPU-time breakdown, a memory composition bar, and per-core
utilisation. Everything else is stacked into collapsible sections, so the page stays readable while still
carrying several hundred metrics.

- **Processor:** per-core busy percentages, where CPU time went (user, system, I/O wait, **steal**, IRQ, idle), model, clock, cache, load average and load per core, runnable and blocked processes, context switches, interrupts and boot time.
- **Memory:** the full `/proc/meminfo` composition — used, available, cache, buffers, shared, anonymous, mapped, active/inactive, dirty, writeback, slab, page tables, commit limit and huge pages — plus swap.
- **Pressure:** Pressure Stall Information for CPU, memory and I/O over 10, 60 and 300 second windows. This catches contention that a utilisation percentage hides.
- **Paging:** page faults, major faults, swap in/out, direct reclaims and kernel OOM kills.
- **Storage:** every real mounted filesystem with its own usage, then cumulative per-device reads, writes, bytes and busy time.
- **Network and sockets:** per-interface counters with link speed, MTU and state, plus TCP/UDP protocol counters, established connections, time-wait sockets and the retransmit rate.
- **Containers:** cgroup v2 memory with its soft limit and peak, `memory.events` including **OOM kills**, CPU quota with **throttled periods and time**, and process counts against `pids.max`.
- **Kernel:** distribution, kernel version, file-descriptor usage against the limit, CPU temperature, scaling governor, entropy and selected sysctls.
- **PHP:** version and branch lifecycle, Zend engine, thread safety, selected configuration, extensions with versions, PDO client drivers, and worker memory.
- **OPcache:** memory, waste, hit rate, hits and misses, cached scripts and keys, interned strings, restart counters and JIT buffer state, without cached file paths.
- **Observations:** capacity thresholds, container throttling and OOM kills, sustained pressure, hypervisor steal, descriptor exhaustion, retransmits and risky PHP settings — each with a practical next step.
- **Server family:** LiteSpeed/OpenLiteSpeed, Nginx, Apache, Caddy, and IIS where the runtime exposes identification.

<details>
<summary><strong>Dark theme and mobile screenshots</strong></summary>

![Alo dark desktop theme](docs/screenshots/desktop-dark.png)

<img src="docs/screenshots/mobile.png" width="390" alt="Alo mobile dashboard showing responsive resource cards and observations">

*Screenshots use the same illustrative fixture, not production measurements.*

</details>

## Quick start

**Requires 64-bit PHP 8.3 or newer; PHP 8.5 is recommended for new installs.** Linux provides the richest system metrics. Other platforms and restricted hosts retain runtime diagnostics and show unavailable readings honestly.

1. Download `alo.php` from a reviewed release or checkout. Generate an access token through SSH or on your local machine:

   ```sh
   php alo.php --generate-token
   ```

2. Save the generated **token** in your password manager. Configure its generated **`ALO_TOKEN_HASH`** in your hosting control panel, PHP-FPM pool, or LSAPI environment, outside the document root. For example, in a PHP-FPM pool:

   ```ini
   ; Replace this placeholder with the generated SHA-256 digest.
   env[ALO_TOKEN_HASH] = YOUR_GENERATED_64_CHARACTER_HASH
   ```

   Reload the PHP handler after changing its configuration. A shell `export` does not usually configure your web PHP process. See the [hosting guide](docs/hosting.md), including LiteSpeed/OpenLiteSpeed.

3. Upload **only `alo.php`**, then open `https://your-domain.example/alo.php`. Sign in with username **`alo`** and the generated **token** as the password. Unconfigured installations stay locked.
4. Restrict access to administrators and rate-limit requests at the web server or access proxy. Review [SECURITY.md](SECURITY.md) before deployment.

There is no public demo URL yet. The project owner will deploy and provide one later. The screenshots above are safe sample data; do not publish production snapshots as a demo.

### Local preview

Set `ALO_TOKEN_HASH` in your local shell first, then:

```sh
ALO_ALLOW_LOCAL_HTTP=1 php -S 127.0.0.1:8080
```

Open `http://127.0.0.1:8080/alo.php` and authenticate. The HTTP exception works only for loopback peers on PHP's development server. Never expose or reverse-proxy that server.

### CLI and integrations

```sh
php alo.php --json
```

CLI uses local OS permissions. Web integrations use HTTPS with `Authorization: Bearer <token>` or Basic authentication:

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `alo.php` | GET | Human dashboard |
| `alo.php?format=json` | GET | Timestamped snapshot |
| `alo.php?format=manifest` | GET | Units, semantics, capabilities, agent guidance |
| `alo.php?format=mcp` | POST | MCP initialize, discovery, and read-only tools |

MCP tools: **`alo_snapshot`**, **`alo_insights`**, and **`alo_capabilities`**. Supports stateless Streamable HTTP with JSON responses and protocol versions `2025-11-25`, `2025-06-18`, and `2025-03-26`. Clients must support a configured Authorization header; OAuth discovery is not implemented. See [agent connection examples and protocol details](docs/agents.md).

## Server compatibility

| Environment | Current scope |
| --- | --- |
| LiteSpeed Enterprise / OpenLiteSpeed + LSAPI | PHP diagnostics, host-visible metrics, family identification |
| Apache + mod_php or PHP-FPM | Same probe; no Apache status-module dependency |
| Nginx / Caddy + PHP-FPM | Same probe; family visible only if passed to PHP |
| IIS + FastCGI | PHP diagnostics and available disk metrics; Linux `/proc` metrics unavailable |
| Containers / shared hosting | Best-effort metrics, explicitly scoped; denied readings become `null` |

These are architectural compatibility targets. Automated tests cover PHP versions, fixture-based server detection, HTTP behavior, and restricted functions. They are not a claim of live validation on every hosting product. [Deployment notes and limitations →](docs/hosting.md)

## Understand the readings

Alo is a **snapshot**, not a monitoring daemon or a security certification. It stores no history and does not auto-poll. CPU sampling takes about 100 ms. Capacity observations start at 80% warning / 90% critical; they are investigation prompts, not universal operational limits.

Host-visible CPU/RAM may describe the host rather than a container. Cgroup data covers the visible v2 root, not necessarily the PHP worker's nested limits. Disk covers the filesystem containing the probe, not all disks, inodes, or quotas. Network counters are cumulative. PDO drivers do not establish database connectivity. Missing readings stay `null`; they never mean “healthy.”

PHP lifecycle dates were reviewed **2026-09-08** against [PHP's support schedule](https://www.php.net/supported-versions.php). They do not verify patch currency. Full metric semantics are in the authenticated manifest and [agent guide](docs/agents.md).

## Upgrading from Alo 1.x

This is a breaking replacement. Configure authentication and HTTPS before switching. Remove the old public probe; do not leave a renamed backup accessible in the document root.

Public `phpinfo`, JSONP/realtime routes, function tests, database connection tests, mail sending, I/O tests, and stress/speed benchmarks are removed. Migrate consumers to the authenticated JSON/MCP interfaces. Renaming the file is no longer treated as access control.

## Development and contributions

```sh
php -l alo.php
php tests/run.php
python3 tests/test_http.py
```

No Composer install is required. CI covers PHP 8.3, 8.4, and 8.5. The HTTP tests launch isolated loopback servers using test-only credentials. `PHP_BINARY` selects a non-default PHP executable.

Read [CONTRIBUTING.md](CONTRIBUTING.md) for the development workflow, screenshot reproduction, design standards, security rules, and PR checklist. [The roadmap](docs/roadmap.md) explains how future collectors can broaden coverage beyond PHP without weakening the access boundary.

## License and history

**GPL-3.0-only** — [license](gpl-3.0.txt).

- **2.0** — Modern PHP rebuild; private access; redesigned dashboard; structured insights; JSON/MCP; tests and contributor documentation.
- **1.1 · 2016-03-02** — PHP 7 compatibility.
- **1.0 · 2013-04-09** — Initial release.

The original Alo was based on an earlier Chinese-language GPL server-probe script. Version 2 replaces the legacy implementation while retaining its lightweight purpose and GPL license.

Created by **[M Asif Rahman](https://ar.bd/)**.
