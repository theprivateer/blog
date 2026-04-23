---
title: 'The risks of adopting AI-led development (my humble opinion)'
intro: null
published_at: 2026-04-21T00:00:00+10:00
category_id: null
created_at: 2026-04-21T13:07:12+10:00
updated_at: 2026-04-21T13:10:13+10:00
metadata:
    title: null
    description: 'A practical look at AI-led development risks, from weaker code review and junior skill atrophy to overengineered patterns and fragile delivery.'
---

There is a lot of pressure right now to adopt AI-led development practices, and most of it is coming from outside engineering. Boards want productivity gains. Investors want AI in the pitch deck. CEOs want to be seen leading the transition rather than catching up to it. The question that tends to get skipped in all of that is the one engineering leaders actually have to answer: if we change how our teams work, what can go wrong, and what do we do about it?

I am genuinely enthusiastic about these tools. I use Claude Code and OpenAI Codex daily, and I think the productivity gains for experienced engineers are real. But I also think the disposition this transition needs (especially from the people leading it) is *well-founded* enthusiasm rather than *uncritical* enthusiasm. There are three risks I keep coming back to, in roughly this order of importance.

## Erosion of review discipline

The biggest risk is not AI writing bad code. AI writes pretty good code, most of the time. The risk is humans stopping reading it carefully. Output that looks plausible gets waved through. What used to be a meaningful review becomes a rubber stamp. Six months in, you have a codebase where nobody is entirely sure who understood what before it shipped.

Part of what makes this risk so pernicious is that it is partly structural, not just a matter of individual laziness. AI coding agents dramatically increase the throughput of code being created. They do not, on their own, increase the speed at which code gets reviewed. Review is still a human activity, constrained by how much context a person can hold in their head and how carefully they can reason about a change. So the bottleneck shifts. Writing code stops being the slow part. Reviewing it *properly* becomes the new constraint on delivery.

That is not, in itself, a problem. It is arguably a healthy rebalancing. The problem is what happens next. Teams that were previously judged on how much code they shipped now find their throughput pinned at the review stage, and the pressure to loosen up starts to build. Pull requests sit open for days. A backlog of AI-generated changes accumulates. Someone, somewhere, decides that reviews need to move faster, and the obvious way to make that happen is to read less carefully. The discipline does not collapse because anyone consciously chose to abandon it; it erodes because the system is now producing more output than the old review process was designed to absorb.

The mitigation, I think, is to be very explicit with the team that AI changes who writes the first draft, but not who is accountable for what ships. Standards for testing, observability, and CI become more important, not less. Review criteria should tighten rather than relax. If a reviewer cannot explain what a change does and why it is correct, it is not ready to merge, regardless of who or what drafted it. And if that genuinely slows delivery, the honest answer is that the team's actual delivery rate was always constrained by how much code it could responsibly ship, not by how much it could type. AI tools have just made that constraint visible.

Most of the security and IP-leakage incidents I have seen discussed are downstream symptoms of this same discipline gap. Treat the discipline as the primary control point and a lot of the secondary concerns become easier to manage.

## Skill atrophy, particularly for less experienced engineers

Mid and senior engineers have the mental models to spot when AI-generated code is wrong in subtle ways. Juniors often do not yet, and handing them a tool that produces confident, articulate output can short-circuit the deliberate practice they need to build those models in the first place.

This one worries me more the longer I think about it. The path from junior to senior runs through a lot of staring at broken code, reading documentation, and figuring out why the obvious solution is wrong. If that path is replaced by prompting until something works, we risk producing a generation of engineers who are extremely productive in familiar territory and completely stuck the moment they leave it.

The mitigation is pairing, structured learning expectations, and being deliberate about which work gets done with AI assistance and which gets done as foundational skill-building. Juniors will benefit enormously from these tools - but only if we protect the conditions that let them become seniors.

## Cargo-culting complex patterns where simpler ones would do

It is tempting, particularly when your board is asking about AI strategy, to reach for autonomous agents or elaborate multi-step workflows when a constrained chain or a well-scoped function call would deliver the same outcome more reliably, more cheaply, and more predictably.

Knowing when _not_ to use agents is, I think, a genuine signal of engineering maturity. A boring, reliable LLM feature that customers can depend on is almost always more valuable than an impressive demo that works in the pitch and fails in production.

---

These three risks are connected, and the connection matters. AI accelerates whatever foundation already exists - good practice or bad. Teams with solid review culture, clear testing standards, and engineers who know when to reach for simple tools will ship better software, faster. Teams without those things will ship worse software, faster.

That means the work of leading an AI-led development transition is at least as much about strengthening fundamentals as it is about rolling out tooling. I would even argue the tooling is the easier part. Tools can be installed in an afternoon. Changing how a team thinks about review, about junior development, and about architectural restraint takes considerably longer - and without those changes, the tools amplify exactly the problems you were hoping they would solve.