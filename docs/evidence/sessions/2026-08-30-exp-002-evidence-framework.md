# EXP-002 — Repository evidence framework

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-002` |
| Related task | documentation protocol before `AP-001` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T09:02:12+07:00` (recorded preflight) |
| Started UTC | `2026-08-30T02:02:12Z` |
| Ended local | `2026-08-30T09:09:59+07:00` |
| Ended UTC | `2026-08-30T02:09:59Z` |
| Agent/operator | Codex with user direction |
| Branch | `main` |
| Baseline commit | `835a8d241519544053a8c90b9047c665727b92bf` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows/PowerShell; documentation-only change |

## Question

Can AgentPress establish a lightweight scientific-method framework that forces future agents to preserve auditable competition evidence without claiming untested implementation progress?

## Hypothesis

A root agent agreement, evidence index, reusable experiment template, initial research record, and README status narrative will be sufficient to make the protocol discoverable, repeatable, and self-enforcing from the repository's start.

## Falsification condition

The hypothesis is falsified if a future agent cannot determine the required reading/order, allocate an experiment, distinguish evidence states, reproduce the initial research method, or identify untested/unclean/secret boundaries from repository files alone.

## Controls

- Product scope and technical decisions remain unchanged.
- No plugin scaffold, issue, commit, push, deployment, or live test is performed.
- Existing implementation specification and checklist content/hashes remain unchanged.
- Evidence files contain no credentials or private site content.

## Variables

- **Independent:** README structure, root agent instructions, evidence schema/template, and initial session records.
- **Dependent:** link validity, protocol completeness, evidence-state clarity, template usability, and worktree scope.

## Preflight

```text
recorded: 2026-08-30T09:02:12+07:00 / 2026-08-30T02:02:12Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
branch: main...origin/main
baseline: 835a8d241519544053a8c90b9047c665727b92bf
existing untracked work: docs/IMPLEMENTATION_SPEC.md, docs/BUILD_CHECKLIST.md
```

## Method

1. Preserve the product summary while making current unimplemented status explicit.
2. Add the six evidence labels and experiment lifecycle to README.
3. Record EXP-001 with sources, agents, contradictions, decisions, checks, hashes, and untested boundaries.
4. Add root `AGENTS.md` with mandatory preflight, experiment, artifact, privacy, and end-of-session behavior.
5. Add an experiment template and append-only evidence index.
6. Link the human contribution path to the same protocol.
7. Verify local links, unique experiment IDs, Markdown fences, trailing spaces, and worktree scope.
8. Complete this record with observed results rather than the planned result.

## Observation ledger

| ID | Label | Observation | Effect |
|---|---|---|---|
| O1 | `OBSERVED` | Existing README described product direction but not research method or evidence states. | Evidence framework was not discoverable. |
| O2 | `OBSERVED` | No root `AGENTS.md`, evidence index, template, or session directory existed. | Future agent self-documentation was not enforced. |
| O3 | `OBSERVED` | EXP-001 artifacts were uncommitted but had stable SHA-256 values. | They can be recorded accurately while remaining explicitly non-commit evidence. |
| O4 | `OBSERVED` | README, AGENTS, evidence index/template, EXP-001, and EXP-002 now form a linked protocol from project status through reusable record. | The method is discoverable from repository root. |
| O5 | `OBSERVED` | Six evidence labels appear across README, AGENTS, and the template; every checked Markdown fence is balanced and no checked file has trailing spaces. | Vocabulary and basic document structure are internally consistent. |
| O6 | `OBSERVED` | Local-link validation reported no missing targets in README, CONTRIBUTING, evidence index, or EXP-001. | The evidence path is navigable locally. |
| O7 | `OBSERVED` | Five untracked images appeared under `docs/assets/` after preflight while this work was in progress. | Classified as concurrent unrelated user work and left untouched. |

## Execution log

| Action | Result | Evidence |
|---|---|---|
| Inspect repository, README, CONTRIBUTING, ignore rules, and docs | exit 0 | Preflight and O1–O3 |
| Replace README in one combined delete/add patch | failed validation; no combined replacement applied | Patch tool disallowed two operations on one path |
| Replace README with separate delete then add patches | success | `README.md` |
| First AGENTS add patch | failed validation; no file created | Code-block lines were not valid patch additions |
| Corrected AGENTS add patch | success | `AGENTS.md` |
| Add evidence template, index, EXP-001, and EXP-002 | success | `docs/evidence/` and index |
| Update human contribution protocol | success | `CONTRIBUTING.md` |
| Validate files, labels, links, fences, spaces, IDs, and recorded EXP-001 hashes | exit 0 | O4–O6 |
| Recheck worktree and `docs/assets/` | exit 0 | O7; unrelated assets preserved |

## Verification matrix

| Acceptance condition | Check | Outcome |
|---|---|---|
| Root explains scientific method and current evidence state | README inspection | `PASS` |
| Agents receive mandatory preflight/session/end rules | AGENTS inspection | `PASS` |
| Reusable experiment format exists | template inspection | `PASS` |
| Initial research is reproducible without chat transcript | EXP-001 source/method/artifact review | `PASS` at documentation level |
| Index allocates unique experiment IDs | two entries, two unique IDs | `PASS` |
| Checked local links resolve | PowerShell local-link scan | `PASS` |
| Markdown fences/spaces are clean | seven files checked | `PASS` |
| Existing spec/checklist match recorded hashes | SHA-256 recomputation | `PASS` |
| Future independent agent follows protocol | not yet run | `NOT_TESTED` |

## Artifact inventory

| Artifact | State | SHA-256 | Purpose |
|---|---|---|---|
| `README.md` | `UNCOMMITTED` | `b77776c9fde8ed7aedc50c6604db69861f1a7dc6e99ee7d69e25f0f7383105f8` | Project status, method, EXP-001 summary, next experiment |
| `AGENTS.md` | `UNCOMMITTED` | `c2579e3b2c362e9b10074a5d1fad294ca153db9cd8ddb3892921adb0fe2e4034` | Mandatory agent evidence behavior |
| `CONTRIBUTING.md` | `UNCOMMITTED` | `585d7505a4ddb7fb2facec4f3c02898581a747495de08ad6ffcea60b8a2bf667` | Human contributor evidence requirement |
| `docs/EVIDENCE_INDEX.md` | `UNCOMMITTED` | `78b123933b1f86ea24bae1d0b79541dbf766304905df47ade04c9c040cc54ba9` | Append-only experiment registry |
| `docs/evidence/EXPERIMENT_TEMPLATE.md` | `UNCOMMITTED` | `c5f24708480926ab383138e87abbbf2c65160e2a7385914450da9b0dcc85778d` | Reusable experiment schema |
| `docs/evidence/sessions/2026-08-30-exp-001-research-to-spec.md` | `UNCOMMITTED` | `59f49ba12ca32b8a6c48268ae4a77947c495230d84a36aa017d5df3a158cf2ee` | Research-session evidence |

## Result

`SUPPORTED`

The repository now contains a root-level working agreement, explicit evidence vocabulary, reusable falsifiable experiment format, append-only registry, and two self-referential session records. Another agent can identify the current project boundary, the prior research method, the next task, and the evidence required before making runtime claims.

This supports the documentation-framework hypothesis only. It does not prove that future agents will comply or that judges will find the evidence persuasive.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: whether a future independent agent follows the protocol.
- `NOT_TESTED`: GitHub rendering after commit/push.
- `NOT_TESTED`: competition-judge usefulness.
- `NOT_TESTED`: any product runtime behavior.
- `UNCOMMITTED`: the framework is not yet dated commit-history evidence.

## Competition evidence statement

- The framework itself is challenge-period work but remains `UNCOMMITTED` until authorized and committed.
- No third-party code, live URL, Site Tools run, reliability run, release ZIP, or video is produced here.
- Concurrent `docs/assets/` files were not created, inspected, attributed, or claimed by this experiment.

## Next experiment

- Complete EXP-002 verification.
- Then begin EXP-003/AP-001 only after implementation authorization.

## End state

```text
branch: main...origin/main
changed by EXP-002: README.md, AGENTS.md, CONTRIBUTING.md, docs/EVIDENCE_INDEX.md, docs/evidence/**
preserved unrelated concurrent work: docs/assets/**
checks: structural/link/hash verification passed
committed: no
pushed: no
deployed: no
```

## Dated amendment — `2026-08-30T09:16:24+07:00`

EXP-003 subsequently inspected and curated the five owner-supplied visual assets, and added visual-evidence guidance to `README.md`, `AGENTS.md`, and `docs/EVIDENCE_INDEX.md`. The hashes above remain the valid end-of-EXP-002 snapshot; they are not claims about the files' current hashes. EXP-003 records the later classifications, moves, and current relevant hashes without rewriting this experiment's original observations.
