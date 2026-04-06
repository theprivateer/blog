---
title: 'Rebuilding my micro-blog with AI agents'
intro: null
published_at: 2026-03-13T00:00:00+10:00
category_id: 4
created_at: 2026-03-13T13:31:47+10:00
updated_at: 2026-03-13T13:36:04+10:00
metadata:
    title: null
    description: 'A practical look at rebuilding a micro-blog with AI agents, using Claude Code in Laravel and SwiftUI to test where agent-driven workflows help.'
---

Over the past few months I've started using AI agents more regularly in my development workflow. At first it was mostly the usual things: asking questions, generating small snippets of code, or acting as a slightly more capable autocomplete. In many ways it simply replaced a lot of trips to search engines and documentation.

But as AI has become the dominant talking point in software development, I realised I hadn't really tested what an agent-driven workflow actually feels like in practice.

With the prospect of moving back into the workforce, I wanted to explore this properly. Not just out of curiosity, but as a way to think more concretely about how AI might realistically fit into a professional development workflow.

So I decided to build something.

## Choosing a small, controlled project

The project I chose was to rebuild the micro-blog section of my personal website. It's already live over at https://moments.philstephens.com.

It's small, well defined, and not mission critical. That makes it a good candidate for experimentation because there's very little risk if things go wrong.

Instead of working primarily inside an IDE, I wanted to push the idea of agentic development as far as I reasonably could. The goal wasn't to blindly accept whatever the AI produced, but to treat the agent as a collaborator while I focused on shaping the architecture and reviewing the results.

To keep things grounded, I deliberately chose a stack I know extremely well. The rebuild uses the <abbr title="Tailwind, Alpine, Laravel and Livewire">TALL</abbr> stack. Familiar tools meant I could easily spot mistakes, refine prompts, and gradually tighten the guardrails I was giving the agent as the project progressed.

The repository for the project lives here: https://github.com/theprivateer/moments

## Why Claude Code?

For this project I decided to use **Claude Code** as the primary agent.

The choice wasn't especially philosophical. At the time I started the project, Anthropic's Opus and Sonnet models were outperforming the comparable ChatGPT models in my own testing. Claude Code also offered a workflow that made it relatively easy to work outside a traditional IDE, which suited the experiment I wanted to run.

The interesting part wasn't which model I chose. It was figuring out how to structure the interaction so the agent could work effectively without drifting too far from the design.

That meant writing clearer prompts, defining rules for the codebase, and gradually refining the instructions the agent followed as the project evolved.

## Early results

It's still early days, but the base functionality is already there.

At the moment the project exists as a full Laravel application. My longer-term plan is to convert it into a reusable package that can be installed into any Laravel project, in a similar way to how Statamic works.

If that works out, it should make it easy to drop a micro-blog timeline into other projects without rebuilding the same functionality each time. There's still plenty of work to do before it reaches that point, but the foundations are in place.

## Productivity versus reality

One of the questions I had going into this experiment was whether working with an AI agent would genuinely make me faster.

The honest answer is yes, although the benefits are uneven. When the problem is clearly defined and the boundaries are well understood, an agent can move surprisingly quickly. Boilerplate disappears, repetitive tasks shrink, and it becomes easy to explore small ideas without much friction.

However, the moment something becomes architectural, ambiguous, or subtle, the human still needs to take the lead. Reviewing the output carefully is essential because small mistakes can compound quickly if they slip through.

So while AI has definitely improved my productivity on this project, I'm not quite ready to uninstall my IDE just yet.

## A side quest: building a small iOS app

Alongside the web project, I also took the opportunity to try something slightly outside my usual comfort zone. I built a small iOS app that lets me interact with the micro-blog timeline from my phone.

The app is intentionally simple. It allows me to post updates and browse the timeline, which is really all I need. The real value was in experimenting with a SwiftUI workflow while working with an AI agent.

The code for that project is here: https://github.com/theprivateer/moments-ios

Working with Claude Code in a SwiftUI project turned out to be more productive than I expected, and it gave me the confidence to attempt something more ambitious.

## The next project

That experiment has already led directly to the next project.

I'm currently building what I would consider a "real" app that I intend to publish on the App Store. To keep things manageable I've chosen to rebuild an older project with a very clear specification, although it's a step up in scope and complexity compared to the micro-blog app.

If it works the way I hope, it should be a good test of whether an AI-assisted workflow holds up across a larger codebase.

But that's a story for another post.