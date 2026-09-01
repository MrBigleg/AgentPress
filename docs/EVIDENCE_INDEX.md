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
| [EXP-011](evidence/sessions/2026-08-31-exp-011-database-repositories.md) | 2026-08-31 | Can exact versioned tables and typed repositories migrate idempotently and preserve bounded data through the v0.1 lifecycle? | AP-005 | `SUPPORTED` | `c0db4b8` | schema/lifecycle matrix and [successful run 33339723834](https://github.com/MrBigleg/AgentPress/actions/runs/33339723834) |
| [EXP-012](evidence/sessions/2026-08-31-exp-012-common-schemas-errors.md) | 2026-08-31 | Can common closed schemas and safe error normalization reject every invalid class and serialize the documented contracts? | AP-006 | `SUPPORTED` | `9952e76` | 13 invalid inputs, 4 invalid outputs, 17 errors, AP-004 regression, [successful run 33359584092](https://github.com/MrBigleg/AgentPress/actions/runs/33359584092) |
| [EXP-013](evidence/sessions/2026-08-31-exp-013-safe-mode-discovery-policy.md) | 2026-08-31 | Can Safe Mode and live WordPress capabilities keep discovery/execution no broader than actual authority with every R3 surface absent? | AP-007 | `SUPPORTED` | `c510713`; merge `6206e53` | capability/object/R3 matrices and latest [successful run 33361850959](https://github.com/MrBigleg/AgentPress/actions/runs/33361850959) |
| [EXP-014](evidence/sessions/2026-08-31-exp-014-ability-registry.md) | 2026-08-31 | Can exactly 15 fixed Abilities register with closed contracts while native REST stays unavailable and bridge discovery remains policy-filtered? | AP-008 | `SUPPORTED` | `6c037ba`; merge `fe7f39e` | exact registry/REST/discovery matrices and latest [successful run 33382573988](https://github.com/MrBigleg/AgentPress/actions/runs/33382573988) |
| [EXP-015](evidence/sessions/2026-08-31-exp-015-sanitized-audit-logging.md) | 2026-08-31 | Can authenticated attempts emit bounded useful audit rows while secrets and unauthenticated traffic remain absent? | AP-009 | `SUPPORTED` | `b416060`; merge `ce3d30a` | four-outcome/secret/200 KB/unauthenticated/storage-failure matrices and latest [successful run 33405208910](https://github.com/MrBigleg/AgentPress/actions/runs/33405208910) |
| [EXP-016](evidence/sessions/2026-08-31-exp-016-change-set-coordinator.md) | 2026-08-31 | Can one coordinator enforce intent-before-mutation, idempotent replay/conflict, immutable R2 staging, expiry, and exact parent states? | AP-010 | `SUPPORTED` | `b3422fd`; merge `a1b3d4d` | one R1 mutation; zero R2/storage/claim mutations; 11 reducer cases; latest [successful run 33411536951](https://github.com/MrBigleg/AgentPress/actions/runs/33411536951) |
| [EXP-017](evidence/sessions/2026-08-31-exp-017-get-context.md) | 2026-08-31 | Can get-context return the exact capability-sensitive safe bootstrap envelope while omitting private/session/configuration data? | AP-011 | `SUPPORTED` | `f66788a`; merge `1c0761e` | four roles; 16 operations; seven private sentinels absent; latest [successful run 33438182716](https://github.com/MrBigleg/AgentPress/actions/runs/33438182716) |
| [EXP-018](evidence/sessions/2026-09-01-exp-018-site-structure.md) | 2026-09-01 | Can get-site-structure return a bounded visible hierarchy/count/taxonomy/location snapshot without content or destination leakage? | AP-012 | `SUPPORTED` | `dca847f`; merge `146dc42` | three roles; 200-page cap; four sentinels absent; [PR-head run 33466836478](https://github.com/MrBigleg/AgentPress/actions/runs/33466836478); [merge run 33466988437](https://github.com/MrBigleg/AgentPress/actions/runs/33466988437) |
| [EXP-019](evidence/sessions/2026-09-01-exp-019-content-reads.md) | 2026-09-01 | Can list-content and get-content provide bounded deterministic post/page reads with per-object authority and zero unreadable leakage? | AP-013 | `SUPPORTED` | `926d08b`; uncommitted | three roles; deterministic pages/filters; direct-ID denials; exact 50,000-character cap; zero mutation; local gates green |

## Planned experiment queue

| Proposed experiment | Task | Falsifiable question | Prerequisites |
|---|---|---|---|
| Later | AP-028 | Does the real ChatGPT desktop built-in browser discover and execute the correct tools for the signed-in account? | bridge, abilities, HTTPS fixture |
| Later | AP-031 | Can the canonical Administrator/Author workflow pass five consecutive reset runs without intervention? | complete demo-critical path |
