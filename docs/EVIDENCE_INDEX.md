# AgentPress Evidence Index

This append-only registry links AgentPress research, implementation, verification, and competition artifacts. Read [AGENTS.md](../AGENTS.md) before adding an entry and use the [experiment template](evidence/EXPERIMENT_TEMPLATE.md).

## Rules

- Allocate experiment numbers sequentially and never reuse them.
- Do not rewrite historical dates or outcomes to match later results; add a dated correction or a new experiment.
- A record may move from `IN_PROGRESS` to a terminal result, but failed observations remain in its execution log.
- `UNCOMMITTED` work is useful process evidence but is not dated commit-history evidence until committed.
- Live/deployed/ChatGPT/5-of-5 claims require their own direct artifacts.

## Experiments

| Experiment | Date | Question | Related task | Result | Baseline | Primary artifacts |
|---|---|---|---|---|---|---|
| [EXP-001](evidence/sessions/2026-08-30-exp-001-research-to-spec.md) | 2026-08-30 | Can PRD v2 become a source-validated, implementation-ready v0.1 plan? | planning precursor | `SUPPORTED` | `835a8d2` | implementation spec; build checklist |
| [EXP-002](evidence/sessions/2026-08-30-exp-002-evidence-framework.md) | 2026-08-30 | Can the repository enforce a reproducible, competition-ready evidence protocol from the start? | documentation protocol | `SUPPORTED` | `835a8d2` | README; AGENTS; evidence template/index |
| [EXP-003](evidence/sessions/2026-08-30-exp-003-visual-assets.md) | 2026-08-30 | Can project visuals be curated without conflating approved direction, concepts, and implementation evidence? | visual evidence curation | `SUPPORTED` | `835a8d2` | two approved identity assets; four concept sheets; asset ledger |
| [EXP-004](evidence/sessions/2026-08-30-exp-004-public-hero-site.md) | 2026-08-30 | Can the public AgentPress concept site be featured without confusing it with runtime evidence? | AP-032 presentation precursor | `SUPPORTED` | `41c5e26` | clickable README hero; public concept URL |
| [EXP-005](evidence/sessions/2026-08-30-exp-005-plugin-scaffold.md) | 2026-08-30 | Can the minimal plugin activate on WordPress 6.9/PHP 8.0 and fail closed on unsupported versions? | AP-001 | `SUPPORTED` | `89b4def` | scaffold; tests; compatibility screenshots; reproducible ZIP |
| [EXP-006](evidence/sessions/2026-08-30-exp-006-judge-aligned-extension-research.md) | 2026-08-30 | Can an Astro/Cloudflare export and bonus concepts be ranked against verified challenge incentives without expanding v0.1? | AP-032 precursor | `SUPPORTED` | `89b4def` | judge/supporter map; ranked extension strategy |
| [EXP-007](evidence/sessions/2026-08-30-exp-007-ap001-ci-gate.md) | 2026-08-30 | Can AP-001 repository checks run green in GitHub Actions without overstating runtime coverage? | AP-001 | `SUPPORTED` | `448e893` | CI workflow and [successful run 33303870871](https://github.com/MrBigleg/AgentPress/actions/runs/33303870871) |
| [EXP-008](evidence/sessions/2026-08-30-exp-008-bridge-pin-attribution.md) | 2026-08-30 | Can audited bridge material be pinned and attributed without shipping obsolete or unrelated upstream behavior? | AP-002 | `SUPPORTED` | `09cf45c` | provenance/package boundary and [successful run 33304713519](https://github.com/MrBigleg/AgentPress/actions/runs/33304713519) |
| [EXP-009](evidence/sessions/2026-08-30-exp-009-current-webmcp-adapter.md) | 2026-08-30 | Can the browser adapter register fixed current-WebMCP definitions and cancel execution without obsolete APIs? | AP-003 | `SUPPORTED` | `d9d0a09` | adapter tests/package and [successful run 33305257104](https://github.com/MrBigleg/AgentPress/actions/runs/33305257104) |
| [EXP-010](evidence/sessions/2026-08-30-exp-010-private-rest-transport.md) | 2026-08-30 | Can private WebMCP routes enforce cookie identity, REST nonce, origin, allowlist, and size boundaries with zero unauthorized execution? | AP-004 | `SUPPORTED` | `65bf03f` | runtime security matrix and [successful run 33338390478](https://github.com/MrBigleg/AgentPress/actions/runs/33338390478) |

## Planned experiment queue

| Proposed experiment | Task | Falsifiable question | Prerequisites |
|---|---|---|---|
| Later | AP-028 | Does the real ChatGPT desktop built-in browser discover and execute the correct tools for the signed-in account? | bridge, abilities, HTTPS fixture |
| Later | AP-031 | Can the canonical Administrator/Author workflow pass five consecutive reset runs without intervention? | complete demo-critical path |
