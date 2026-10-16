# AGENTS.md

> **Konteks Monorepo (`specs/`):**
> Folder `specs/` ini adalah workspace khusus Business Analyst (BA) di dalam repository aplikasi HFG.
> Seluruh deliverable di folder ini (FR/NFR, BPMN, ERD, UI Mockup, Issue Capsule) berfungsi sebagai *Single Source of Truth* yang menjadi acuan langsung tim developer dan AI saat coding di root aplikasi (`../application/`).

**The manual for this repository is [`CLAUDE.md`](CLAUDE.md). Read it — all of
it — before doing anything else.** Then read `context/RULES.md`, which is the
session entry point: what to load, in what order, and what deliberately stays
out of the startup set.

This file is a pointer, not a copy. Runtimes disagree about what the project
manual is called — some look for `AGENTS.md`, some for `CLAUDE.md` — and the
worst possible answer is two manuals that drift apart while both keep sounding
authoritative. So there is exactly one manual here, and this file exists only to
send you to it.

(`opencode.json` loads `CLAUDE.md` directly, so under opencode you already have
it. Under any other runtime that found this file first, go read it now.)

Do not add rules to this file. Rules belong to their owner:
`context/general-rules.md` for the shared working rules, the relevant
`context/coder-only/*.md` or `context/document-writer-only/*.md` for
conventions.
