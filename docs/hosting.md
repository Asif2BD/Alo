# Hosting Alo

Deploy only `alo.php` on 64-bit PHP 8.3+. No web-server-specific modules, shell access at request time, or writable app directory are required. Configure HTTPS, the token hash, an administrative access boundary, and rate limits before exposing it.

## LiteSpeed Enterprise and OpenLiteSpeed

Alo is written for standard PHP APIs and supports PHP through **LSAPI (`litespeed` SAPI)**. Set `ALO_TOKEN_HASH` in the PHP external application's environment using your hosting panel or LiteSpeed configuration, then restart/reload the PHP workers. Do not paste the secret token into a public file. If TLS terminates upstream, configure exact trusted proxy IPs and overwritten `X-Forwarded-Proto` as described below.

Alo identifies LiteSpeed from the server value or the LSAPI SAPI. If Enterprise/OpenLiteSpeed cannot be distinguished, it says “LiteSpeed / OpenLiteSpeed.” It does not access the WebAdmin API, real-time report files, LSCache internals, worker queues, or virtual-host statistics. OPcache is distinct from LSCache.

Reference: [LiteSpeed PHP external applications](https://docs.litespeedtech.com/lsws/extapp/php/).

## Apache

Works through mod_php or PHP-FPM. Configure the hash in the PHP process environment or server configuration outside the web root, and ensure Authorization reaches PHP. On CGI/FastCGI installations, check the server's Authorization forwarding configuration if correct credentials always return 401. Alo does not require `mod_status` and never fetches its endpoint.

## Nginx and Caddy

Use the existing PHP-FPM integration and set the hash in the FPM pool:

```ini
env[ALO_TOKEN_HASH] = YOUR_GENERATED_64_CHARACTER_HASH
```

Reload the PHP pool. Preserve Authorization in FastCGI parameters. Web-server family detection uses the runtime's `SERVER_SOFTWARE`; Caddy or another hidden upstream may remain unknown. Do not infer a complete proxy chain from this field.

## IIS / Windows

PHP runtime diagnostics and disk information can work through FastCGI. Linux `/proc` and cgroup metrics are unavailable. Alo does not execute PowerShell, WMI, or shell commands to fill gaps. Windows live hosting validation is still needed.

## Containers and shared hosting

Keep the hash in injected secrets/environment. `/proc` may show host resources while cgroups limit the container: Alo reports these separately. Only the visible cgroup v2 root is inspected; nested process limits and cgroup v1 are not resolved. `open_basedir` and disabled functions may prevent readings. Missing data remains unavailable, with no attempt to bypass host restrictions.

## Reverse-proxy TLS

Native PHP `HTTPS=on` or `HTTPS=1` is accepted. Otherwise configure `ALO_TRUSTED_PROXIES` with exact comma-separated proxy IPs. Only an exact peer match plus `X-Forwarded-Proto: https` is accepted. The trusted proxy must overwrite this header and prevent direct origin access. CIDRs, wildcards, mixed protocol lists, and untrusted headers are not accepted.

## Operational checks after deployment

1. An unauthenticated HTTPS request returns 401, or 503 if not configured; never a snapshot.
2. Plain HTTP cannot reveal data. Ideally your edge redirects to HTTPS before PHP receives it.
3. Correct browser credentials show the dashboard. A token in the URL does not authenticate.
4. Authenticated JSON and the MCP initialize/tools flow work through your proxy.
5. Response caching is disabled; the proxy must not override `Cache-Control: no-store`.
6. Confirm filesystem/container scopes and unavailable metrics match the environment.
7. Apply administrator access restrictions and request rate limits in the server/access proxy.

Current automated tests validate the PHP implementation, server-identification fixtures, local HTTP boundary, and function restrictions. They do not replace deployment tests on LiteSpeed, Apache, Nginx, Caddy, IIS, or an actual TLS proxy. Contributions documenting live validation are welcome.
