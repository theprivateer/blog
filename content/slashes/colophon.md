---
title: "Colophon"
modified: 2025-06-14T08:55:15
---

This site is powered by a lightweight custom flat-file CMS.

Written using the Laravel PHP framework, it leverages the [Sheets](https://github.com/spatie/sheets) package to render Markdown files.

I use [iA Writer](https://ia.net/writer) to author / update posts and update [/slash pages](/slashes), using the built-in Micropub integration to publish directly to the site. These changes are then periodically pushed back to the [GitHub repository](https://github.com/theprivateer/blog) via a simple Laravel scheduled command.

The whole thing is hosted on a small DigitalOcean Droplet[^1] running Ubuntu 24.10 and PHP 8.3. Deploys are manually triggered by SSH-ing onto the server and running a simple `deploy.sh` script.

The site uses Tailwind v3 and the system monospace font. Code syntax highlighting is performed server-side using the [Shiki PHP](https://github.com/spatie/shiki-php) library.

Domain is registered through GoDaddy (shudder), with DNS via Cloudflare.

[^1]: I’m keen to move to a VPS-provider that uses ‘green’ energy from renewable resources.