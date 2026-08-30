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

## Planned experiment queue

| Proposed experiment | Task | Falsifiable question | Prerequisites |
|---|---|---|---|
| Later | AP-028 | Does the real ChatGPT desktop built-in browser discover and execute the correct tools for the signed-in account? | bridge, abilities, HTTPS fixture |
| Later | AP-031 | Can the canonical Administrator/Author workflow pass five consecutive reset runs without intervention? | complete demo-critical path |
