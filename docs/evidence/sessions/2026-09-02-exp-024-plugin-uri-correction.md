# EXP-024 — Canonical plugin repository URI

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-024` |
| Related task | `AP-001` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-02T11:35:05+07:00` |
| Started UTC | `2026-09-02T04:35:05Z` |
| Ended local | `2026-09-02T11:38:33+07:00` |
| Ended UTC | `2026-09-02T04:38:33Z` |
| Agent/operator | Codex, metadata correction and evidence operator |
| Branch | `main` |
| Baseline commit | `83c7fba0e9fbddfe6da22b0e06b76f892d4d3d65` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; pnpm 11.21.0; local WordPress at `http://localhost:8888`; host PHP/Composer unavailable |

## Question

Can the wp-admin plugin details link resolve to the canonical AgentPress source repository without changing runtime behavior or presenting the concept site as a deployed plugin?

## Hypothesis

The plugin header contains one stale pre-publication repository slug. Replacing it with the repository configured as `origin` will make WordPress render the canonical project URL while leaving executable behavior unchanged.

## Falsification condition

The hypothesis is falsified if another shipped reference still supplies the stale URL, the replacement is not the configured canonical repository, PHP syntax fails, or the change affects files beyond metadata and its evidence record.

## Controls

- fixed commit/build: baseline `83c7fba0e9fbddfe6da22b0e06b76f892d4d3d65`.
- fixed fixture/data: existing local WordPress plugin installation.
- fixed identity/capabilities: unchanged.
- fixed policy/configuration: unchanged.
- fixed client/environment: current repository and localhost wp-admin observation supplied by the project owner.
- explicit scope exclusions: runtime feature changes, deployment, concept-site changes, and real ChatGPT Site Tools verification.

## Variables

- **Independent:** the `Plugin URI` header value.
- **Dependent:** repository URL found in source, PHP syntax result, and changed-file scope.

## Preflight

```text
timestamp: 2026-09-02T11:35:05+07:00 / 2026-09-02T04:35:05Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main
git log -3 --oneline --decorate: 83c7fba docs closeout; cb70053 PR #35 merge; 99dfbdf AP-019 evidence
baseline SHA: 83c7fba0e9fbddfe6da22b0e06b76f892d4d3d65
current branch: main
unrelated existing changes: none observed
AP task, issue, PR: AP-001 metadata correction; no issue or PR requested
environment: Node.js 22.23.2; pnpm 11.21.0; host PHP and Composer unavailable
```

## Method

1. Confirm the configured Git remote and inspect all shipped references to the stale and canonical repository URLs.
2. Replace only the plugin header's stale `Plugin URI` with the canonical AgentPress repository.
3. Run repository URL searches, PHP lint through the project environment if available, and the proportional repository checks.
4. Inspect the complete diff and staged manifest, commit, push, and record the resulting evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator verifies all evidence. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | local Git `origin` | 2026-09-02 | The canonical repository is `https://github.com/MrBigleg/AgentPress.git`. | Current checkout configuration. |
| S2 | `OBSERVED` | `agentpress/agentpress.php` at baseline | 2026-09-02 | The shipped `Plugin URI` points to the nonexistent `WP-Agent-Admin` slug. | Project-owner localhost observation reports HTTP 404. |
| S3 | `OBSERVED` | README and EXP-004 | 2026-09-02 | The chatgpt.site URL is labelled an interactive concept, not plugin runtime evidence. | Must not replace the canonical source URL. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-02T11:35:05+07:00 | Capture Git/environment preflight and inspect URL references | repository | exit 0 with host-tool limitations | Clean synchronized baseline; canonical `origin`; stale header is the only `WP-Agent-Admin` URL; host PHP/Composer unavailable. |
| 2026-09-02T11:36:00+07:00 | Replace the stale `Plugin URI` and search shipped source | repository | exit 0 | `agentpress/agentpress.php` now contains only `https://github.com/MrBigleg/AgentPress`; no stale shipped reference remains. |
| 2026-09-02T11:36:32+07:00 | `npm.cmd run lint:php` | repository, restricted shell | exit 1 before lint | Docker configuration and named-pipe access were denied by the restricted shell; no source result obtained. |
| 2026-09-02T11:37:09+07:00 | `npm.cmd run lint:php` | repository, approved Docker access | exit 0 | PHPCS passed 42/42 files in 31.82 seconds. |
| 2026-09-02T11:38:33+07:00 | Review complete diff and worktree | repository | exit 0 | Product change is one metadata line plus EXP-024 and its index entry; no unrelated changes observed. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | `origin` fetch/push both use `https://github.com/MrBigleg/AgentPress.git`. | preflight command | Supports canonical replacement. |
| O2 | `OBSERVED` | The stale slug occurs only in the plugin header. | repository `rg` search | Supports a one-line product correction. |
| O3 | `OBSERVED` | The public chatgpt.site destination is explicitly presented as a concept. | README/EXP-004 | Rules it out as the plugin source/details URI. |
| O4 | `OBSERVED` | The corrected header passes the repository's PHP standards check across all 42 PHP files. | `npm.cmd run lint:php` | Supports syntactic/style safety. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Host PHP and Composer versions can be captured directly. | Neither executable is available on host PATH. | Environment limitation. | Use the project container for PHP lint if available; do not claim host PHP verification. |
| C2 | The restricted shell can reach the existing Docker environment. | Docker config and named-pipe access were denied before lint began. | Sandbox/environment limitation; not a source failure. | Reran with approved Docker access; PHPCS passed 42/42. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Set `Plugin URI` to `https://github.com/MrBigleg/AgentPress`. | O1-O3 | Repository details are accurate; concept presentation remains separately labelled. | Canonical project home changes. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Canonical URL replaces stale URL | Exact repository search | `PASS` | Canonical header present; stale shipped URL absent. |
| PHP syntax/style remains valid | `npm.cmd run lint:php` | `PASS` | PHPCS 42/42. |
| Change remains narrowly scoped | Complete diff review | `PASS` | One product metadata line plus required evidence/index files. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `agentpress/agentpress.php` | source metadata | uncommitted | `UNCOMMITTED` | One-line canonical repository correction. |
| `docs/evidence/sessions/2026-09-02-exp-024-plugin-uri-correction.md` | evidence | uncommitted | `UNCOMMITTED` | Opened before product mutation and updated during execution. |

## Result

`SUPPORTED`

The hypothesis is supported within repository scope. The WordPress plugin header now points to the canonical AgentPress source repository, the stale slug is absent from shipped source, the concept URL remains separately labelled, and all 42 PHP files pass the project standards check. This does not independently verify the post-change link in wp-admin or any deployment.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: post-change wp-admin click, deployment, real ChatGPT Site Tools, and the five-run reliability workflow.

## Competition evidence statement

- work attributable to challenge period: timestamp and baseline recorded;
- pre-existing work distinguished by: baseline commit recorded;
- third-party material/license/pin: `NOT_APPLICABLE`;
- commit/PR evidence: `UNCOMMITTED` at experiment conclusion; commit and push authorized next;
- live URL evidence: owner-observed localhost 404 only;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: resume AP-021 or the next owner-selected v0.1 task;
- next falsifiable question: unchanged from the README next-experiment section;
- required prerequisites: this metadata correction committed and pushed.

## End state

```text
git status --short --branch: three intended paths modified/untracked; no unrelated changes observed
tests/checks: URL search PASS; git diff --check PASS; PHPCS 42/42 PASS after one recorded sandbox-only failure
committed: no
pushed: no
deployed: no
```
