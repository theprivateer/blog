---
title: "Colophon"
modified: 2024-12-21T16:46:01
---

This site is powered by a lightweight custom flat-file CMS.

Written using the Laravel PHP framework, it leverages the [Sheets](https://github.com/spatie/sheets) package to render Markdown files.

I use [iA Writer](https://ia.net/writer) to author posts and update [/slash pages](/slashes), using the built-in Micropub integration to publish directly to the site. These updates are then periodically pushed back to the [GitHub repository](https://github.com/theprivateer/blog).

The whole thing is hosted on a small DigitalOcean Droplet running Ubuntu 24.10 and PHP 8.3. Deploys are manually triggered by SSH-ing onto the server and running a simple `deply.sh` script.

The site uses Tailwind v3 and the system monospace font. The only Javascript used is [Shiki](https://github.com/shikijs/shiki) for syntax highlighting of code samples.

Domain is registered through GoDaddy (shudder), with DNS via Cloudflare.