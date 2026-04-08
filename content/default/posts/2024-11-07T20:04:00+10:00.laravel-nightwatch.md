---
title: 'Laravel Nightwatch'
intro: null
published_at: 2024-11-07T20:04:00+10:00
category_id: null
created_at: 2024-11-08T06:04:00+10:00
updated_at: 2024-11-08T06:04:00+10:00
metadata:
    title: null
    description: 'A first look at Laravel Nightwatch from Laracon AU, and why its all-in-one monitoring and observability could replace several daily tools.'
---

This year Laracon AU is in Brisbane, and today I was lucky enough to attend the first day in person. The schedule was packed with a whole bunch of fantastic presentations, but the main buzz is around [Laravel Nightwatch](https://nightwatch.laravel.com), the new first-party monitoring and observability platform that was announced today.

Based on the demo alone (and there is still additional functionality that is intentionally blurred-out) I can see this replacing at least four different tools that I use virtually every day at [Rex](https://rexsoftware.com.au):

- NewRelic (application performance)
- BugSnag (exception capture)
- Google Log Explorer
- Google Cloud Monitoring (resources and uptime)

Basic monitoring is fine, but investigating an incident involves jumping across multiple services to get the full picture, whereas Nightwatch seems to have it all in one place. It's as if the Laravel team have seen my struggle, and built an observability platform just for me.

Best of all, since it is built specifically for Laravel applications it is able to surface framework-specific information such as routes and authenticated users - something that is largely only possible with additional custom code (that needs to be maintained) when using our current stack. Everything is connected and contextual.

Like pretty much everyone else in the room, I signed up to the waitlist immediately, and I’m excited for Q1 2025 when they’ll start letting people in.
