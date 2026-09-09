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

Everything else the build writes is documentation *about* Alo:

| Path | Purpose |
| --- | --- |
| `index.html` | The landing page. Semantic HTML, one `<h1>`, inline CSS, no external assets or fonts. |
| `index.md` | The same content as Markdown, served on `Accept: text/markdown`. |
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
