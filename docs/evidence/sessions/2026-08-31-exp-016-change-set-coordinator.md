# EXP-016 — AP-010 Change Set coordinator and idempotency

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-016` |
| Related task | `AP-010`; GitHub issue #19 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-31T22:00:05+07:00` |
| Started UTC | `2026-08-31T15:00:05Z` |
| Ended local | `2026-08-31T22:47:24+07:00` |
| Ended UTC | `2026-08-31T15:47:24Z` |
| Agent/operator | Codex, implementation and verification agent |
| Branch | `ap-010-change-set-coordinator` |
| Baseline commit | `6b6631958ebf8197fb92de1685788fb51276c326` |
| Ending commit | `b3422fd693d0633ecad29ed3b0df43992a271f0d` |
| Environment | Windows; PowerShell 7; Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 |

## Question

Can one narrow Change Set coordinator durably record and atomically claim every automatic R1 intent before mutation, stage immutable expiring R2 proposals without mutation, deduplicate identical per-user idempotency keys, conflict on changed payloads, and derive every documented parent state from child transitions?

## Hypothesis

The merged AP-005 repositories, AP-007 policy boundary, and AP-009 audit writer provide the necessary persistence, authority, and outcome primitives. Adding canonical hashing, a fixed state reducer, and conditional repository transitions should support deterministic R1/R2 coordination without a workflow engine or background queue.

## Falsification condition

The hypothesis is false if an R1 mutation occurs before a durable `RECORDED` row and successful atomic `APPLYING` claim; storage/claim failure permits mutation; an identical user/Ability/key/payload repeats mutation; the same scope with a changed payload does not return `AP_STATE_CONFLICT`; an R2 stage mutates its target; proposal or target hashes fail to detect change; expiry is not exactly bounded by the fixed 24-hour contract; immutable proposal fields can be changed after staging; or any documented child-state combination derives an incorrect Change Set state.

## Controls

- fixed commit/build: clean synchronized main baseline `6b6631958ebf8197fb92de1685788fb51276c326`;
- fixed fixture/data: synthetic users, deterministic inputs/snapshots/keys, isolated Change Set rows, and mutation counters rather than private site data;
- fixed identity/capabilities: explicit actor user IDs after the merged AP-007 permission boundary; no coordinator authority expansion;
- fixed policy/configuration: R1 and R2 supplied only after policy classification; 24-hour proposal expiry; UTC clocks; existing AP-005 table schema;
- fixed client/environment: unit controls plus real WordPress 6.9/MySQL wp-env integration;
- explicit scope exclusions: AP-011+ WordPress content/taxonomy/navigation services, AP-023 human approval route, admin UI, background queues, deployment, ChatGPT, and release evidence.

## Variables

- **Independent:** risk class, actor/Ability/idempotency key, canonical payload, before/after state, repository/claim/mutator failure, time, and child status combinations.
- **Dependent:** Change Set reuse/ID/status, change row state/hash/expiry/object/error fields, mutation count, replay/conflict result, durable audit outcome, and unauthorized mutation count.

## Preflight

```text
timestamp: local 2026-08-31T22:00:05+07:00; UTC 2026-08-31T15:00:05Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main (clean)
git log -3 --oneline --decorate:
6b66319 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-009 merge evidence
ce3d30a Merge pull request #18 from MrBigleg/ap-009-sanitized-audit-logging
5fa36ca (origin/ap-009-sanitized-audit-logging, ap-009-sanitized-audit-logging) docs: record AP-009 hosted gate
baseline SHA: 6b6631958ebf8197fb92de1685788fb51276c326
current branch: main; isolated AP-010 branch pending after this record opens
unrelated existing changes: none
AP task, issue, and PR: AP-010; no duplicate AP-010 issue observed; issue/PR pending
environment: Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; exact baseline run 33405723377 passed
```

## Method

1. Open this record and index entry, then create one isolated AP-010 branch and milestone issue.
2. Inspect the exact AP-005 repository operations/schema and AP-009 audit boundary; identify missing conditional writes before implementation.
3. Define canonical JSON/hash inputs, idempotency scope/payload semantics, fixed child-to-parent reducer precedence, and immutable R2 fields.
4. Implement the smallest coordinator plus state hasher/reducer and narrow conditional repository operations; do not implement AP-011+ services or human approval.
5. Unit-test canonical order invariance, hash sensitivity, every parent-state combination, expiry, and invalid transition rejection.
6. Run a real WordPress/MySQL matrix covering default/reused sets, R1 intent-before-mutation, claim/storage/mutator failure, identical replay, changed-payload conflict, R2 zero mutation, immutable proposals, and audit outcomes.
7. Run AP-004 through AP-009 regressions and all repository-wide unit/standards/browser/provenance/audit/package gates.
8. Commit, push, open an issue-linked PR, merge only the exact latest green head, and append merge/main evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary agent owns inspection, implementation, and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `DECIDED` | `docs/IMPLEMENTATION_SPEC.md` sections 7 and 8.1–8.2 at baseline `6b66319` | 2026-08-31 | Exact child states, parent reducer outcomes, R1 sequence, R2 hash/expiry contract, table columns, and hashed idempotency/session values are fixed. | Project contract, not runtime proof. |
| S2 | `OBSERVED` | `docs/BUILD_CHECKLIST.md` AP-010 at baseline `6b66319` | 2026-08-31 | Acceptance requires one mutation on identical replay, conflict on changed payload, zero mutation on storage failure, and all documented parent states. | Executable evidence pending. |
| S3 | `OBSERVED` | EXP-011, EXP-013, and EXP-015 merged evidence | 2026-08-31 | Required schema/repositories, authority boundary, and sanitized audit writer exist with green hosted evidence. | Exact coordinator compatibility pending inspection. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T22:00:05+07:00 | Re-read current project/AP-010 contract and capture baseline, recent commits, environment, duplicate issue search, and main CI | repository/GitHub | exit 0 | Clean main at full SHA `6b6631958ebf8197fb92de1685788fb51276c326`; no AP-010 issue; exact run `33405723377` success; versions recorded above. |
| timestamp not independently captured | Open EXP-016/index, create `ap-010-change-set-coordinator`, and create issue #19 | repository/GitHub | exit 0 | Evidence existed before AP-010 implementation inspection/mutation; isolated branch and [issue #19](https://github.com/MrBigleg/AgentPress/issues/19) created. |
| timestamp not independently captured | Inspect AP-005 repositories/store/codec/schema, AP-009 logger, existing tests, and all hash/idempotency/state references | repository | exit 0 | Exact columns/bounds exist, but repositories expose only unconditional CRUD: no idempotency lookup, child-status list, or atomic status compare-and-set. The reducer contract has no literal outcome for a mixed terminal applied/rejected set with no pending child. |
| timestamp not independently captured | Start wp-env, activate AgentPress, inspect runtime, and syntax-check all AP-010 PHP files | repository/wp-env | initial sandbox failure, then exit 0 | Initial activation could not access Docker from the restricted command context; authorized rerun confirmed AgentPress active on WordPress 6.9/PHP 8.0.30. All eight AP-010 files passed `php -l`. |
| timestamp not independently captured | Run focused PHPUnit and first PHP standards gate | repository/wp-env | unit exit 0; lint exit 2 | PHPUnit: 65 tests, 579 assertions. PHPCS found 57 errors and 27 warnings, confined to new/modified AP-010 formatting and docblocks. PHPCBF fixed 38; remaining comments/tags were repaired manually. |
| timestamp not independently captured | Rerun PHP standards | repository/wp-env | exit 0 | 35 PHP files passed PHPCS. |
| timestamp not independently captured | Run first real MySQL AP-010 matrix | repository/wp-env | exit 1 | Failed at strict supplied Change Set reuse assertion because a database row ID flowed into the semantic result as a numeric string. Earlier R1/replay/conflict assertions passed before this failure. Normalize resolved set IDs to integers at the coordinator boundary and rerun from clean synthetic fixtures. |
| timestamp not independently captured | Rerun AP-010 MySQL matrix and focused PHPUnit after ID normalization | repository/wp-env | exit 0 | Matrix: four sets, three durable changes, one R1 mutation, zero R2/storage/claim mutations, two single transport audit rows, 11 reducer cases. PHPUnit: 65 tests, 579 assertions. |
| timestamp not independently captured | Run AP-004 through AP-009 real WordPress/MySQL regressions | repository/wp-env | exit 0 | AP-004: 14 forbidden controls; AP-005: three repository round trips; AP-006: 13 invalid inputs/17 declared errors; AP-007: 15 Abilities/seven forbidden guesses; AP-008: 15 admin/eight subscriber tools and zero unauthorized mutations; AP-009: four audit outcomes, eight secrets absent, zero unauthorized mutations. |
| timestamp not independently captured | Run browser and first provenance gate | repository | browser exit 0; provenance exit 1 | Browser passed 14/14. Provenance stopped before inspection because its build child received Windows `EPERM` unlinking ignored `dist/agentpress.zip`; this is the same bounded artifact-access condition observed in EXP-015 and requires an authorized isolated rerun. |
| timestamp not independently captured | Rerun provenance with workspace artifact access; run dependency, script-syntax, and diff gates | repository | exit 0 | Provenance verified GPL licenses/pin, 45 ZIP entries, and no upstream runtime code. `npm audit --omit=optional` found zero vulnerabilities; both package scripts passed syntax; `git diff --check` passed. |
| timestamp not independently captured | Build final release ZIP twice from identical source and hash each result | repository | exit 0 | Both builds produced SHA-256 `32E9366F63F1ADCBF98D9C1F67F01E8D664CD75EBD54BCE1343E9937BB84C980`. |
| timestamp not independently captured | Commit/push AP-010, open PR #20, and verify its exact hosted head | repository/GitHub | exit 0 | Implementation `b3422fd693d0633ecad29ed3b0df43992a271f0d`; evidence SHA commit `c27e912c711e02854994fb2c212bf56bb70c555d`; [PR #20](https://github.com/MrBigleg/AgentPress/pull/20) links issue #19 and exact head passed [run 33411177779, job 99550827486](https://github.com/MrBigleg/AgentPress/actions/runs/33411177779/job/99550827486). |
| 2026-08-31T22:59:07+07:00 | Verify latest evidence head, merge PR #20, confirm issue closure, fast-forward main, and verify merge-head CI | repository/GitHub | exit 0 | Final PR head `7ad2e0ba862df2e3e44a79d17cce3281959f5faa` passed [run 33411438312, job 99551700972](https://github.com/MrBigleg/AgentPress/actions/runs/33411438312/job/99551700972); merge `a1b3d4de22fd56a660ba476b4a145c589f8925cc`; issue #19 closed; exact merge head passed [run 33411536951, job 99552035408](https://github.com/MrBigleg/AgentPress/actions/runs/33411536951/job/99552035408). |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-005, AP-007, and AP-009 are merged, and exact clean baseline run `33405723377` passed. | recent log; README/index; GitHub run | AP-010 can begin on an isolated branch. |
| O2 | `OBSERVED` | No existing GitHub issue matched AP-010 before session mutation. | preflight query | One milestone issue can be created without duplication. |
| O3 | `OBSERVED` | The existing `RecordStore::update()` filters only by row ID; AP-010 requires status-qualified compare-and-set to claim `RECORDED` or `PENDING_APPROVAL` atomically. | source inspection | Add one generic allowlisted conditional update primitive and expose only fixed status transitions through `ChangeRepository`. |
| O4 | `OBSERVED` | The fixed parent enum cannot represent mixed terminal applied/rejected children under every literal reducer sentence: `PARTIALLY_APPROVED` requires a pending child, while `COMPLETED` requires all applied. | implementation specification section 7.1 | A deterministic project decision is required; no new state may be added in AP-010. |
| O5 | `OBSERVED` | The real MySQL matrix observed `APPLYING` before its trusted mutator, exactly one mutation after identical replay, conflict on changed payload, and zero mutation after synthetic storage or claim failure. | AP-010 integration output | The R1 durability and idempotency hypothesis is supported under controlled WordPress/MySQL conditions. |
| O6 | `OBSERVED` | R2 staging caused zero mutation, stored canonical target/proposal hashes, expired exactly 24 hours after the fixed clock, rejected immutable-field changes, and reduced the parent to `READY_FOR_REVIEW`. | AP-010 integration output | The immutable staging hypothesis is supported under controlled conditions. |
| O7 | `OBSERVED` | Unit tests covered canonical order/sensitivity and 11 parent-state classes; AP-004 through AP-009 regressions, PHP standards, browser, provenance, audit, and deterministic package gates all passed. | command outputs and final ZIP hash | The implementation preserves prerequisite behavior and repository gates locally. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Every reachable child-status combination should map literally to one documented parent-state sentence. | Mixed terminal `APPLIED` plus `REJECTED|EXPIRED` has no literal match in the fixed seven-state rules. | specification gap | Preserve the fixed enum; classify mixed applied/rejected terminal sets as `PARTIALLY_APPROVED`, the only state that truthfully signals partial approval. Record and test this extension explicitly. |
| F1 | The first real MySQL matrix should accept explicit Change Set reuse. | The correct set was reused in storage, but the returned `change_set_id` retained wpdb's numeric-string representation and failed the strict integer contract. | implementation defect | Cast resolved database IDs once at the coordinator boundary; preserve the failing run and rerun the complete matrix. |
| F2 | The provenance gate should build and inspect the release boundary in its initial restricted command context. | The child build received Windows `EPERM` while unlinking ignored `dist/agentpress.zip`, so no provenance conclusion was reached. | environment/artifact-access failure | Preserve this result and rerun the same bounded gate with authorized workspace write access. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Keep AP-010 protocol-independent: coordinator accepts reviewed narrow callbacks/data and never arbitrary callable names from request input. | S1; v0.1 non-goals | AP-011+ service adapters remain responsible for WordPress semantics. | A concrete service cannot satisfy the fixed coordinator boundary. |
| D2 | `DECIDED` | Add status-qualified compare-and-set to `RecordStore` and expose it as fixed `ChangeRepository::transition()` rather than performing read-then-write claims. | O3; R1/R2 claim contract | Slightly expands AP-005's low-level primitive while keeping SQL/table/column inputs allowlisted. | WordPress supplies an equivalent transactional repository abstraction. |
| D3 | `DECIDED` | Derive mixed terminal applied/rejected/expired sets as `PARTIALLY_APPROVED`; do not add a new state or call them fully completed. | C1; fixed enum | The state may remain partial with no pending action, but does not misrepresent rejected work as completed. | Product owner changes the fixed state vocabulary or reducer sentence. |
| D4 | `DECIDED` | Keep one WebMCP audit row at the AP-009 transport boundary; infer `SUCCESS|PENDING|REPLAYED` and change IDs from validated service output instead of writing a duplicate coordinator row. | AP-009 transport ownership; AP-010 semantic results | Coordinator stays protocol-independent and direct non-WebMCP callers must own their audit boundary. | A future protocol cannot classify outcomes from the fixed result contract. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Identical key replays first result with exactly one mutation | Real MySQL R1 apply plus identical replay and counter | `SUPPORTED` | `r1_mutations:1`; same change/result replayed |
| Same scope and changed payload conflicts | Reuse raw key with changed `after` state | `SUPPORTED` | `AP_STATE_CONFLICT`; mutation counter unchanged |
| Storage/claim failure prevents mutation and records safe failure | Synthetic throwing repository and false compare-and-set | `SUPPORTED` | `storage_mutations:0`; `claim_mutations:0`; durable failed mutator row also verified |
| R2 proposal is immutable, expires at 24 hours, and causes zero mutation | Real proposal row, fixed clock/hash recomputation, forbidden update | `SUPPORTED` | `r2_mutations:0`; exact `2026-09-01 15:00:00`; immutable exception |
| Every documented child transition derives the correct parent state | Unit reducer data provider plus real COMPLETED/READY/FAILED parents | `SUPPORTED` | 11 reducer classes and unknown-state rejection |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-016-change-set-coordinator.md` | evidence | committed | `EXP-016`; `b3422fd` | Opened before AP-010 implementation inspection or product-code mutation. |
| `agentpress/includes/Changes/ChangeCoordinator.php` | source | committed | `b3422fd` | Protocol-independent R1/R2 coordination and semantic results. |
| `agentpress/includes/Changes/ChangeSetStateReducer.php` | source | committed | `b3422fd` | Fixed deterministic parent reducer including recorded C1 decision. |
| `agentpress/includes/Changes/StateHasher.php` | source | committed | `b3422fd` | Recursive canonical JSON and SHA-256 contracts. |
| `agentpress/tests/integration/ap010-change-set-coordinator.php` | executable evidence | committed | `b3422fd` | Real WordPress/MySQL mutation, replay, failure, proposal, privacy, and audit matrix. |
| `dist/agentpress.zip` | ignored release candidate | generated | `32E9366F63F1ADCBF98D9C1F67F01E8D664CD75EBD54BCE1343E9937BB84C980` | Two deterministic local builds; not release/deployment evidence. |

## Result

`SUPPORTED`

Under the controlled WordPress 6.9/PHP 8.0.30/MySQL fixture, the AP-010 coordinator durably recorded and atomically claimed R1 work before one mutation, replayed identical requests without a second mutation, rejected changed-payload key reuse, prevented mutation on storage/claim failure, staged immutable 24-hour R2 proposals without mutation, and deterministically reduced all tested child-state classes. The final PR head and exact merge head also passed the hosted repository gate; AP-010 is merged and issue #19 is closed.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: concurrent multi-process duplicate insertion beyond the database uniqueness/compare-and-set controls; AP-011+ real WordPress service mutation; human approval; deployment; real ChatGPT; release installation; and five-run reliability.
- Browser unit behavior passed, but no live browser/WebMCP/ChatGPT claim is made by AP-010.

## Competition evidence statement

- work attributable to challenge period: baseline/timestamps captured before AP-010 product-code mutation;
- pre-existing work distinguished by: synchronized AP-009 closeout baseline;
- third-party material/license/pin: no new third-party runtime; existing GPL pin/provenance gate passed with 45 ZIP entries and no upstream runtime code;
- commit/PR evidence: implementation `b3422fd693d0633ecad29ed3b0df43992a271f0d`; final PR head `7ad2e0ba862df2e3e44a79d17cce3281959f5faa`; merged PR #20 at `a1b3d4de22fd56a660ba476b4a145c589f8925cc`; issue #19 closed; exact merge-head run `33411536951` passed;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: `EXP-017` / AP-011 `get-context`;
- next falsifiable question: can one bounded read service expose the correct effective operation envelope for each fixture user while omitting all private identity, session, capability, path, configuration, and environment fields?
- required prerequisites: merged AP-007 policy and AP-008 Ability catalog; AP-010 is not a dependency.

## End state

```text
git status --short --branch: clean synchronized main at a1b3d4de22fd56a660ba476b4a145c589f8925cc before this merge-evidence append; no unrelated changes observed
tests/checks: AP-010 MySQL matrix pass; AP-004–AP-009 regressions pass; unit 65/579; PHPCS 35/35; browser 14/14; provenance 45 entries; npm audit 0; deterministic ZIP SHA-256 32E9366F63F1ADCBF98D9C1F67F01E8D664CD75EBD54BCE1343E9937BB84C980
committed: yes, implementation/evidence `b3422fd693d0633ecad29ed3b0df43992a271f0d`
pushed: yes; PR #20 merged; final PR head and exact merge head passed hosted gates
deployed: no
```
