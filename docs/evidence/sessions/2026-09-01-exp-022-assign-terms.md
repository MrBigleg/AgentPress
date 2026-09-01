# EXP-022 — Atomic existing-term assignment

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-022` |
| Related task | `AP-017` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-01T16:41:32+07:00` |
| Started UTC | `2026-09-01T09:41:32Z` |
| Ended local | `2026-09-01T17:19:11+07:00` |
| Ended UTC | `2026-09-01T10:19:11Z` |
| Agent/operator | Codex, implementation and evidence operator |
| Branch | `ap-017-assign-terms` |
| Baseline commit | `e0e4dae991decc8bd2546f55925f62c4b03b68f0` |
| Ending commit | `d70771b` implementation; evidence closeout uncommitted |
| Environment | Windows; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress/PHP pending wp-env capture |

## Question

Can `agentpress/assign-terms` atomically append or replace existing categories/tags on an authorized AgentPress-created post draft while staging every other editable target with zero mutation?

## Hypothesis

The merged term reader, durable create-draft authority, execution policy, and R1/R2 coordinator provide the required boundaries: validate every term first, derive one final/proposed set, then apply only when policy reports automatic and otherwise persist an immutable proposal.

## Falsification condition

The hypothesis is falsified if an invalid/mixed taxonomy set partially mutates, a page accepts post terms, an unauthorized user changes any assignment, an Author changes another user's post, a non-AgentPress or published post mutates during the tool call, append duplicates IDs, replace retains unintended IDs, identical replay mutates twice, or changed-key reuse does not conflict.

## Controls

- fixed commit/build: baseline `e0e4dae991decc8bd2546f55925f62c4b03b68f0` and one AP-017 worktree.
- fixed fixture/data: synthetic users, AgentPress-created and ordinary drafts, published post, category/tag fixtures, exact before/after term snapshots.
- fixed identity/capabilities: real Administrator/Author/Subscriber identities and live object/taxonomy capabilities.
- fixed policy/configuration: Safe Mode enabled; category/post_tag only; R1 only for durable AgentPress-created drafts and R2 elsewhere.
- fixed client/environment: repository wp-env CLI and locked repository gates.
- explicit scope exclusions: term creation, content editing/publishing, UI, deployment, real ChatGPT, and reliability gate.

## Variables

- **Independent:** caller identity, target ownership/state/AgentPress authority, taxonomy, term IDs, append/replace mode, idempotency key, and replay payload.
- **Dependent:** result/error, target term IDs, Change/Change Set state, replay flag, mutation count, and proposal expiry.

## Preflight

```text
timestamp: 2026-09-01T16:41:32+07:00 / 2026-09-01T09:41:32Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main before branch creation
git log -3 --oneline --decorate: e0e4dae AP-015 closeout; a169776 PR #31 merge; ba51a41 AP-015 evidence
baseline SHA: e0e4dae991decc8bd2546f55925f62c4b03b68f0
unrelated existing changes: none observed
AP task, issue, PR: AP-017; issue #32; PR pending
environment: Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; runtime versions pending
```

## Method

1. Inspect the exact assign-terms Ability schema, policy/risk mode, draft-authority lookup, coordinator, WordPress term APIs, and adjacent integration patterns.
2. Implement one service that duplicates the fixed semantic/capability boundary, resolves every term before coordination, and derives deterministic unique final/proposed IDs.
3. Use R1 apply only for an AgentPress-created draft; otherwise R2 stage with exact before/after state and zero target mutation.
4. Bind only `agentpress/assign-terms`, include the service in the deterministic package, and add the real-WordPress acceptance matrix.
5. Run AP-004–AP-015 regressions and all repository/package gates; preserve every failure/correction.
6. Commit implementation and evidence separately, publish one PR, inspect all comments, and require exact-head plus merge-head green before closeout.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator verifies all evidence. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | `docs/IMPLEMENTATION_SPEC.md` at baseline | 2026-09-01 | Exact assign-terms schema, authority, R1/R2, and output contract. | Project contract. |
| S2 | `SOURCE_VERIFIED` | Ability catalog, execution policy, draft lookup, coordinator at baseline | 2026-09-01 | Fixed schemas and durable authority/coordination interfaces exist. | Source presence is not AP-017 runtime proof. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-01T16:41:32+07:00 | Capture preflight and inspect AP-017 contracts | repository | exit 0 | Clean synchronized baseline and exact specification boundaries recorded. |
| timestamp not independently captured | Open issue #32 and branch `ap-017-assign-terms` | GitHub/repository | success | Submission-critical isolated task created before product mutation. |
| timestamp not independently captured | Run first AP-017 real-WordPress matrix | repository/wp-env | exit 1 | Replace and append applied, but identical append did not replay because the R1 command identity included the now-mutated current term snapshot. |
| timestamp not independently captured | Rerun after R1 identity correction | repository/wp-env | exit 1 | Prior failed trial left fixed synthetic term slugs, so fixture insertion stopped before product assertions. |
| timestamp not independently captured | Rerun with isolated fixtures | repository/wp-env | exit 1 | Identical append replay passed, but changed requested IDs produced the same normalized final set and were incorrectly treated as an identical replay. |
| timestamp not independently captured | Run corrected AP-017 matrix | repository/wp-env | exit 0 | Three R1 applications, two R2 proposals, append/replace, replay/conflict, atomic invalid denial, four role/target denials, and zero staged/unauthorized mutations passed. |
| timestamp not independently captured | Run initial repository gates | repository/wp-env | mixed | PHPUnit 68/593, browser 14/14, provenance 51 entries, and audit zero passed; PHPCS reported 11 mechanical findings and two abbreviated property docblocks. |
| timestamp not independently captured | Rerun PHP standards after narrow correction | repository/wp-env | exit 0 | All 41 PHP files passed. |
| timestamp not independently captured | Run AP-004 through AP-015 named standalone regressions | repository/wp-env | exit 0 | All twelve prior matrices passed, including zero unauthorized mutations and AP-015 role/idempotency controls. |
| timestamp not independently captured | Build twice and run final syntax/whitespace checks | repository | exit 0 | Both 51-entry ZIPs matched SHA-256 `A083B2A5F8A190636861AA7B04775A17B179654E1C45E930A7BC1D683BDA4EC3`; Node syntax and `git diff --check` passed; wp-env stopped. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-010, AP-014, and AP-015 prerequisites are merged and green; durable create-draft rows are the authority for automatic assignment. | repository/GitHub evidence | Supports starting AP-017. |
| O2 | `OBSERVED` | Corrected AP-017 matrix passed three R1 applications, two R2 proposals, append/replace, replay/conflict, atomic mixed-term denial, four role/target denials, and zero staged/unauthorized mutations. | AP-017 matrix | Supports the hypothesis in the controlled fixture. |
| O3 | `OBSERVED` | AP-004–AP-015 and all repository/package gates passed after preserved corrections. | execution log | No observed regression in covered boundaries. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | Identical append input should replay the first durable result. | After first mutation, recomputed `before.term_ids` included the appended term and changed the coordinator hash. | genuine implementation idempotency defect | Use a stable empty R1 identity baseline and return the real before snapshot from the trusted mutator; retain real before state for R2 target hashing. |
| F2 | A failed trial should not prevent a clean rerun. | Fixed category/tag slugs from the first failed trial remained because cleanup was not reached. | test fixture isolation defect | Add a per-run suffix to term names/slugs so preserved failed trials cannot collide. |
| F3 | Same idempotency key with different requested IDs should conflict even when the resulting union is unchanged. | Identity hashed only final normalized IDs, so append-A and append-B both resolved to the same existing `{A,B}` set. | genuine implementation idempotency defect | Persist/hash mode and requested IDs alongside final IDs; keep final IDs as the mutation/approval target. |
| F4 | Initial PHP standards gate should pass. | New service/dispatcher had 11 mechanical alignment/array findings and two abbreviated property docblocks. | implementation standards defect | Run PHPCBF only on the two AP-017 files, expand property docblocks, and rerun PHPCS. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Skip non-demo AP-016 and implement AP-017 next to shorten time to the canonical manual-test workflow. | user direction/O1 | Existing content editing remains unavailable. | Submission-critical path completes or owner restores full scope. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Canonical draft append/replace and replay | real AgentPress draft plus exact ID snapshots | `PASS` | two assignment modes, identical replay, changed-input conflict. |
| Invalid/mixed terms fail atomically | category request containing tag ID | `PASS` | `AP_TERM_NOT_FOUND`; unchanged target snapshot. |
| Author own/other and Subscriber boundaries | real roles and object permissions | `PASS` | Author own applied; Author other, Subscriber, logged out, and page denied. |
| Ordinary/published targets stage without mutation | ordinary draft and published post | `PASS` | two expiring R2 proposals; both targets unchanged. |
| Prior regressions and repository gates | AP-004–AP-015 plus full repository/package gates | `PASS` | all named checks green; deterministic hash below. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-022-assign-terms.md` | evidence | uncommitted | EXP-022 | Opened before product mutation. |
| issue #32 | task | external | https://github.com/MrBigleg/AgentPress/issues/32 | Close only after hosted merge verification. |
| `agentpress/includes/Terms/TermAssignmentService.php` | implementation | committed | `d70771b` | Atomic validation, R1/R2 coordination, append/replace, fixed output. |
| `agentpress/tests/integration/ap017-assign-terms.php` | executable evidence | committed | `d70771b` | Synthetic authority, staging, idempotency, and zero-mutation matrix. |
| `dist/agentpress.zip` | generated package | excluded | SHA-256 `A083B2A5F8A190636861AA7B04775A17B179654E1C45E930A7BC1D683BDA4EC3` | Two consecutive builds matched; 51 entries. |

## Result

`SUPPORTED`

`OBSERVED`: the controlled real-WordPress evidence supports the hypothesis. Existing terms applied atomically only to authorized AgentPress-created post drafts; ordinary/published editable posts produced immutable proposals without mutation; invalid mixed terms and unauthorized targets caused zero partial change; replay and changed-input conflict followed the durable coordinator contract.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: wp-admin UI, deployment, real ChatGPT, release installation, and five-run reliability.

## Competition evidence statement

- work attributable to challenge period: exact timestamps/baseline recorded;
- pre-existing work distinguished by: verified AP-015 closeout baseline;
- third-party material/license/pin: `NOT_APPLICABLE`; AP-017 adds none and provenance passed;
- commit/PR evidence: issue #32; implementation `d70771b`; evidence closeout uncommitted; PR pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: earliest wp-admin/manual-test surface slice, then AP-018 authorization consolidation;
- next falsifiable question: can the AgentPress wp-admin shell expose live bridge/context status and a usable manual-test checkpoint without weakening the transport boundary?;
- required prerequisites: AP-017 exact-head and merge-head hosted gates.

## End state

```text
git status --short --branch: EXP-022/index/checklist/README uncommitted on ap-017-assign-terms
tests/checks: AP-017 matrix pass; AP-004–AP-015 regressions pass; PHPUnit 68/593; PHPCS 41 files; browser 14/14; provenance 51 entries; audit 0; deterministic ZIP pass
committed: implementation `d70771b`
pushed: no AP-017 branch
deployed: no
```
