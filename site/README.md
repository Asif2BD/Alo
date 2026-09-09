# The documentation site

`build.sh` generates the public documentation site for Alo — the pages served at
<https://alo.asif.dev> — into a `public/` directory at the repository root.

```sh
bash site/build.sh
```

`public/` is generated output and is git-ignored. Build it on deploy, not in the
repository.

## What it deploys, and what it deliberately does not

The document root is `public/`, never the repository root. `SECURITY.md` is
explicit that only `alo.php` should ever be reachable over the web, so the
build copies exactly that one executable file into `public/` and leaves the
tests, the git metadata, and the licence text outside the document root.

The builder requires PHP 8.3+ to render deterministic sample HTML and compute payload checksums. It preserves existing files, including the access digest, across rebuilds; obsolete public artifacts must be removed explicitly. Everything else it writes is documentation or a distributable:

| Path | Purpose |
| --- | --- |
| `index.html` | The landing page. Semantic HTML, one `<h1>`, inline CSS, no external assets or fonts. |
| `index.md` | The same content as Markdown, available directly; root negotiation requires the Nginx configuration below. |
| `docs/*.md` | Copies of `readme.md`, `SECURITY.md` and `docs/*.md`. |
| `llms.txt` | Copied from the repository root. |
| `robots.txt` | Crawl rules, a Content Signals policy, and the sitemap pointer. |
| `sitemap.xml` | Every public URL. |
| `feed.json`, `feed.xml` | JSON Feed 1.1 and Atom release feeds. |
| `openapi.json` | OpenAPI 3.1 description of the authenticated `alo.php` routes. |
| `.well-known/api-catalog` | RFC 9727 / RFC 9264 linkset pointing at the above. |
| `.well-known/mcp/server-card.json` | Describes the read-only MCP endpoint and its three tools. |
| `.well-known/agent-skills/index.json`, `alo-skill.json` | An agent skill for reading a snapshot correctly. |
| `.well-known/security.txt` | RFC 9116 contact details. |
| `404.html` | Served with a real `404` status. |

The probe at `/alo.php` stays token-protected and keeps sending
`X-Robots-Tag: noindex`. `robots.txt` disallows it as well, so only the
documentation is indexable.

## Deploying somewhere else

The generated documents carry a canonical base URL. Override it for another host:

```sh
ALO_SITE_URL=https://alo.example.com bash site/build.sh
```

## Keeping the content honest

The claims on the landing page — supported PHP versions, the three MCP tools,
the protocol versions, the security boundary — are drawn from `readme.md`,
`docs/agents.md` and `SECURITY.md`. When those change, update `build.sh` in the
same commit so the site cannot drift away from the documentation it summarises.

## Public files added in 2.2

`demo.html` is static sample HTML, never a live probe. `install.sh` pins a checksummed, content-addressed payload under `downloads/alo-<sha256>.txt`; the text extension is intentional so PHP does not execute the download. Prior payloads remain available for pinned installers. `docs/contributing.md` is the public contributor guide.

## Root Markdown negotiation on Nginx

The build alone cannot change server routing. Replace the existing exact-root block with this snippet in the site's custom Nginx configuration, validate it, then reload through the hosting panel:

```nginx
location = / {
    add_header Vary Accept always;
    add_header Cache-Control "public, max-age=600" always;
    if ($http_accept ~* "text/markdown") { rewrite ^ /index.md last; }
    try_files /index.html =404;
}
location = /index.md {
    default_type text/markdown;
    add_header Vary Accept always;
    add_header Cache-Control "public, max-age=600" always;
}
```

Preserve the site's HSTS/security headers. Confirm both representations and `Vary: Accept` through the actual CDN/proxy; never apply public caching rules to `alo.php`.
