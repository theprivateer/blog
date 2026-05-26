---
title: 'Inline code-comments as a defence against AI-led cognitive debt'
intro: null
published_at: 2026-05-13T00:00:00+10:00
category_id: 2
created_at: 2026-05-13T13:15:45+10:00
updated_at: 2026-05-13T13:37:05+10:00
metadata:
    title: null
    description: 'A look at cognitive debt in AI-assisted development and how inline code comments can preserve decision-making context for future reviewers and maintainers.'
---

Andrew Cairns recently published [an article on cognitive debt](https://acairns.co.uk/posts/cognitive-debt) that I'd recommend reading in full before this one. It put a name on something I'd been circling for a while without quite articulating, and it sent me back to a few of the threads in my [earlier post on the risks of AI-led development](https://philstephens.com/blog/the-risks-of-adopting-ai-led-development-my-humble-opinion).

The risk I put first in that post, and the one I still think matters most, was the erosion of review discipline. AI coding agents produce plausible-looking output much faster than humans can read it carefully, and the bottleneck in delivery quietly shifts from writing to reviewing. Teams that don't notice this end up with codebases nobody fully understands. Andrew's framing of cognitive debt sits one layer beneath that. Even when a review goes well at the time, the *reasoning* behind the code rarely survives. Six months later, the developer who returns to a file (or the new engineer reading it for the first time) sees what the code does but not why it ended up that way.

Self-documenting code names things well. It doesn't tell you which alternative was rejected, what constraint forced this shape, or why a section that looks more complicated than it should be is actually correct. That gap between code and understanding is what builds up in any growing codebase, but AI-assisted development makes it worse on both ends. More code gets shipped, and more of the decisions behind that code lived only in a chat window that nobody is going to scroll back through.

So I've written a small Claude skill to address it: [code-comments](https://github.com/theprivateer/claude-skills/tree/main/code-comments).

The idea is straightforward. When the coding agent is writing or modifying code, it adds inline comments at two kinds of places: decision points (where it picked one viable approach over another, or where a user instruction shaped the design) and complex control points (where the surface reading of the code doesn't match its actual behaviour). The comments are short, free-form prose, and they answer the question a reviewer is most likely to ask: *why this way?* And when the agent modifies code that already has an associated comment, it has to keep the comment in sync with the change. Stale comments mislead the next reader, so if a change makes a comment obsolete, the comment goes too.

You can also invoke the skill on-demand against an existing codebase, but in that mode the agent isn't allowed to invent rationale. It surveys the code, reports back on what it would annotate, and waits for confirmation. When it does add comments retroactively, it sticks to factual descriptions of behaviour. The original reasoning isn't recoverable from the source, and a confidently-wrong comment is worse than no comment at all.

None of this fixes the structural problem I wrote about last time. Review discipline still has to be a human commitment, and no amount of inline documentation makes a rubber-stamped pull request safe to merge. But if the review *is* happening properly, the comments give the reviewer something to push back on. "Why did you choose this approach?" becomes a question the code itself can start to answer, which means the review can spend its time on whether the reasoning holds up rather than reconstructing what the reasoning was.

I'll acknowledge that liberally commenting code is a personal preference, and not one everybody will share. There's a reasonable school of thought that says code should be clear enough to speak for itself, and that inline comments are a smell rather than a virtue. I've held versions of that view myself at different points. When I was writing every line by hand, the reasoning lived in my head as a by-product of writing it. When an agent is drafting large chunks of the code, that reasoning has to be deliberately captured somewhere or it doesn't exist at all. I'd rather have a codebase that occasionally over-explains itself than one where the *why* is permanently lost. As more of my code gets written by an agent, that's a trade I'm increasingly willing to make.

The skill is on GitHub at [theprivateer/claude-skills/code-comments](https://github.com/theprivateer/claude-skills/tree/main/code-comments). Drop the Markdown file into your global Claude skills directory and it'll activate on coding tasks automatically, or you can invoke it on demand when you want an existing file annotated. Feedback and pull requests welcome.