---
title: Colophon
template: null
draft: false
created_at: 2026-01-05T14:39:05+10:00
updated_at: 2026-02-04T13:44:11+10:00
metadata:
    title: null
    description: null
---

This site is powered by a lightweight custom hybrid database/flat-file CMS.

Written using the Laravel PHP framework, it leverages [Filament](https://filamentphp.com) to provide a lightweight admin panel.

Posts, pages and notes are all stored in the database for efficient retrieval / pagination (and eventually search), whilst being stored as Markdown files that can be version-controlled. Overkill? Perhaps. Likely to change? Probably.

Uploaded assets are stored in a Cloudflare R2 bucket.

The whole thing is managed by [Laravel Forge](https://forge.laravel.com) and hosted on Laravel VPS, because life's too short for managing servers.

The site uses the [Kelp](https://kelpui.com) HTML-first UI library for styling, and is set in [Inclusive Sans](https://fonts.google.com/specimen/Inclusive+Sans) for legibility. Code syntax highlighting is performed server-side using the [Shiki PHP](https://github.com/spatie/shiki-php) library.

For now the domain is registered through GoDaddy, with DNS via Cloudflare.

This site has [no tracking](https://themarkup.org/blacklight?location=eu&device=desktop&force=false&url=philstephens.com) and uses just [0.01g](https://www.websitecarbon.com/website/philstephens-com/) of CO<sub>2</sub> per visit.

