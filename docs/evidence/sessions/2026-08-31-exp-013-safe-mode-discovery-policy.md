# EXP-013 — AP-007 Safe Mode and discovery policy

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-013` |
| Related task | `AP-007`; GitHub issue #13 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-31T12:15:12+07:00` |
| Started UTC | `2026-08-31T05:15:12Z` |
| Ended local | `2026-08-31T12:40:34+07:00` |
| Ended UTC | `2026-08-31T05:40:34Z` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-007-safe-mode-discovery-policy` |
| Baseline commit | `87703a03ab7079d73b12ede5bcb3096d9f1637f4` |
| Ending commit | `c51071361e044dfd60326ccf37f76c5497cfa727` |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 11.7.0; wp-env WordPress 6.9/PHP 8.0 planned |

## Question

Can AgentPress classify v0.1 operations into R0–R3 and combine Safe Mode with current WordPress capability checks so every advertised and directly executable operation is no broader than the current user's actual authority, while every R3 users/plugins/themes/code/settings surface remains absent?

## Hypothesis

An explicit fixed policy registry can separate coarse discovery from object-specific execution, intersect both with live `current_user_can()` results, and deny unknown or R3 operations by default. A WordPress role/capability-mutation matrix can prove the envelope changes with actual authority rather than role-name assumptions.

## Falsification condition

The hypothesis is falsified if any Administrator, Editor, Author, Subscriber, logged-out, or capability-mutated user discovers or directly executes an operation beyond its current WordPress capabilities; if Safe Mode permits a risk class outside its fixed rule; if any unknown operation fails open; or if any users/plugins/themes/code/settings operation is registered, discoverable, or reachable by route guessing.

## Controls

- fixed commit/build: merged AP-006 main baseline `87703a03ab7079d73b12ede5bcb3096d9f1637f4`;
- fixed fixture/data: synthetic users, posts/pages, AgentPress-created draft marker, and fixed operation registry;
- fixed identities: Administrator, Editor, Author, Subscriber, logged-out, and one capability-mutated user;
- fixed policy/configuration: v0.1 R0–R3 rules and Safe Mode enabled;
- fixed client/environment: wp-env WordPress 6.9/PHP 8.0 plus repository unit/standards/package gates;
- explicit scope exclusions: production Ability registration AP-008, services AP-011+, UI, real browser/WebMCP, ChatGPT, deployment, and submission.

## Variables

- **Independent:** operation/risk class, user identity, live capability mutation, object ownership/status, Safe Mode, and direct versus discovery path.
- **Dependent:** policy classification, advertised operation, permission result/error, forbidden-surface absence, target mutation, and package/test outcome.

## Preflight

```text
timestamp local: 2026-08-31T12:15:12+07:00
timestamp UTC: 2026-08-31T05:15:12Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
87703a0 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-006 merge evidence
e76e3a4 Merge pull request #12 from MrBigleg/ap-006-common-schemas-errors
71d1461 (origin/ap-006-common-schemas-errors, ap-006-common-schemas-errors) docs: close AP-006 local evidence
baseline SHA: 87703a03ab7079d73b12ede5bcb3096d9f1637f4
current branch: main; AP-007 branch pending after this record opens
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-007; no duplicate milestone issue observed; issue/PR pending
environment: Node.js 22.23.2; npm 11.7.0; WordPress/PHP runtime pending
```

## Method

1. Create the isolated AP-007 branch and one milestone issue after opening this record.
2. Extract the exact R0–R3, Safe Mode, capability-envelope, discovery, object-specific execution, and forbidden-surface rules from the binding specification and official WordPress capability APIs.
3. Implement a deny-by-default policy registry/classifier and separate coarse discovery from exact execution authorization without registering AP-008 production Abilities.
4. Add parameterized unit and real WordPress controls for all required roles, live capability mutation, ownership/status boundaries, unknown operations, direct route guessing, and total R3 absence.
5. Run AP-004/AP-006 regressions plus standards, unit/runtime, browser, audit, provenance, and deterministic package gates; preserve failures in order.
6. Commit, push, open an issue-linked PR, and merge only after local and hosted checks pass.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work remains in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress `current_user_can()`](https://developer.wordpress.org/reference/functions/current_user_can/) | 2026-08-31 | Capabilities, not role names, decide authority; meta capabilities accept object IDs and map to primitive capabilities. | Super administrators require explicit AgentPress R3 blocking because core capabilities may otherwise return true. |
| S2 | `SOURCE_VERIFIED` | [WordPress `get_post_type_capabilities()`](https://developer.wordpress.org/reference/functions/get_post_type_capabilities/) and [`get_post_type_object()`](https://developer.wordpress.org/reference/functions/get_post_type_object/) | 2026-08-31 | Registered post types expose `create_posts`, `edit_posts`, `edit_others_posts`, `edit_published_posts`, and `publish_posts`; object meta capabilities remain context-dependent. | Resolve post/page capability names from live objects rather than hard-coding role expectations. |
| S3 | `SOURCE_VERIFIED` | [WordPress `register_taxonomy()` capability contract](https://developer.wordpress.org/reference/functions/register_taxonomy/) and [`get_taxonomy()`](https://developer.wordpress.org/reference/functions/get_taxonomy/) | 2026-08-31 | Taxonomies expose separate `manage_terms` and `assign_terms` capabilities; built-in defaults differ by action. | Resolve only fixed `category|post_tag` taxonomy objects. |
| S4 | `SOURCE_VERIFIED` | [WordPress Roles and Capabilities handbook](https://developer.wordpress.org/plugins/users/roles-and-capabilities/) | 2026-08-31 | `current_user_can()` is the current-user capability boundary; role labels are not the execution contract. | Required fixture expectations remain controls, not production role-name logic. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T12:15:12+07:00 | Verify AP-006 merge/main CI, clean synchronized main, environment, and duplicate AP-007 issue absence | repository/GitHub | exit 0 | Main clean at `87703a0`; closeout run `33359810396` successful; no AP-007 milestone issue returned. |
| timestamp not independently captured | Create `ap-007-safe-mode-discovery-policy` and milestone issue #13 | repository/GitHub | exit 0 | Isolated branch and [issue #13](https://github.com/MrBigleg/AgentPress/issues/13) created after EXP-013 opened. |
| timestamp not independently captured | Extract binding R0–R3, Safe Mode, 16-operation envelope, object authority, and discovery/execution rules; verify WordPress capability APIs | specification/official WordPress docs | success | Capability names will resolve from live post-type/taxonomy objects; object checks use meta capabilities with IDs; role names remain test labels only. |
| timestamp not independently captured | Implement AP-007 policy primitives, package manifest, unit controls, and WordPress acceptance harness | repository | mutation complete; verification pending | Added live capability resolution, contextual risk/Safe Mode, fixed-map discovery, 16-operation envelope, APPLIED-change authority lookup, exact execution checks, role/capability/object/R3 fixtures, and no AP-008 Ability registration. |
| 2026-08-31T12:33:01+07:00 | `npm run test:unit` | repository/wp-env CLI | exit 1 | 42 tests reached; 41 passed and `PolicyTest::test_execution_rechecks_object_and_policy_without_discovery` errored because `ExecutionPolicy` constructed a database lookup despite the injected test callback. |
| 2026-08-31T12:33:01+07:00 | `npm run lint:php` | repository/wp-env CLI | exit 1 | New policy files produced 47 documentation errors and one alignment warning; existing files were clean. |
| timestamp not independently captured | `npm run test:unit` after F1 correction | repository/wp-env CLI | exit 0 | 42 tests, 219 assertions. |
| timestamp not independently captured | `npm run lint:php` after F2 correction | repository/wp-env CLI | exit 1 | Documentation defects cleared; two auto-fixable nested-array alignment warnings remained in `CapabilityEnvelope.php`. |
| timestamp not independently captured | Final `npm run lint:php` correction and first AP-007 WordPress matrix | repository/wp-env CLI | lint exit 0; matrix exit 1 | PHPCS passed 28/28. The matrix stopped at the capability-mutated Subscriber because discovery did not observe the just-persisted explicit capabilities through the already-current user instance. |
| timestamp not independently captured | AP-007 WordPress matrix after F4 fixture correction | repository/wp-env CLI | exit 0 | Four default roles, logged out, live capability mutation, 16 capability operations, 15 fixed abilities, seven forbidden route guesses, and four object-specific controls passed. |
| timestamp not independently captured | AP-004 and AP-006 live regressions | repository/wp-env CLI | exit 0 | AP-004: one valid execution plus 14 forbidden zero-side-effect controls; AP-006: 13 invalid input classes, four invalid outputs, 17 declared errors, and 4,096-byte unsafe-detail bound. |
| timestamp not independently captured | Unit, standards, browser, provenance, and audit release gates | repository | exit 0 | 42 tests/219 assertions; PHPCS 28/28; browser 14/14; provenance 38 ZIP entries with pinned GPL source and no upstream runtime; npm audit found zero vulnerabilities. |
| timestamp not independently captured | Two consecutive `npm run build:zip` runs and SHA-256 checks | repository | exit 0 | Both release builds produced `2CF45FB1492F7458F9272B2BB17CF5820B07E41EC0A7262CE8344E3354AC0770`. |
| 2026-08-31T12:44:03+07:00 | Stage exact AP-007 manifest, run `git diff --cached --check`, and commit | repository | exit 0 | 14-file implementation/evidence package committed as `c51071361e044dfd60326ccf37f76c5497cfa727`; worktree clean immediately afterward. |
| 2026-08-31T12:47:14+07:00 | Push branch, open PR #14, and watch hosted repository gate | GitHub | exit 0 | [PR #14](https://github.com/MrBigleg/AgentPress/pull/14) links issue #13; [run 33361719090, job 99394212994](https://github.com/MrBigleg/AgentPress/actions/runs/33361719090/job/99394212994) passed in 28 seconds. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-006 and its common schema/error boundary are merged; AP-007's declared dependency is satisfied. | merge `e76e3a4`; main `87703a0` | AP-007 may begin without AP-008 Ability registration. |
| O2 | `SOURCE_VERIFIED` | WordPress distinguishes primitive post-type/taxonomy capabilities from object-specific meta capabilities, and discourages role-name authorization. | S1–S4 | AP-007 needs one live resolver shared by discovery/envelope/execution rather than a static role matrix. |
| O3 | `OBSERVED` | The fixed transport map contains exactly the 15 v0.1 operations and none of the R3 users/plugins/themes/code/settings surfaces. | `AbilityMap.php`; implementation specification section 4 | Discovery can filter the fixed map, while execution must still deny unknown route guesses. |
| O4 | `OBSERVED` | The corrected real WordPress matrix passed for Administrator, Editor, Author, Subscriber, logged-out, and a capability-mutated Subscriber, including object ownership/status controls. | AP-007 integration harness exit 0 | The tested effective envelope follows live capabilities and exact target authority. |
| O5 | `OBSERVED` | Administrator discovery remained exactly the fixed 15-map while seven representative R3/unknown route guesses returned `AP_POLICY_BLOCKED`. | AP-007 integration harness exit 0 | The tested forbidden surfaces are absent from the fixed map and fail closed at direct execution policy. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | Injected execution-policy collaborators should isolate unit tests from global WordPress database state. | `ExecutionPolicy` eagerly constructed `AgentCreatedDraftLookup`, whose constructor read absent `$GLOBALS['wpdb']`; PHPUnit errored after 41 passing tests. | implementation defect | Recorded before correction; construct the durable lookup only when no callback is supplied, then rerun. |
| F2 | New PHP policy files should pass the repository coding standard. | PHPCS reported 47 missing/compact documentation errors and one array-alignment warning. | implementation formatting defect | Recorded before correction; expand docblocks, align the array, and rerun. |
| F3 | The first F2 correction should clear the PHP coding-standard gate. | PHPCS cleared every error but requested opposite spacing for the outer `capabilities` and `blocked_areas` keys based on nested-array alignment. | implementation formatting defect | Recorded before correction; apply PHPCS's exact requested spacing and rerun. |
| F4 | Persisting `edit_posts` and `publish_posts` on the currently active Subscriber fixture should immediately expand discovery. | The first real WordPress matrix stopped because `create-draft` remained undiscoverable through the existing current-user instance. | harness isolation defect; stale current-user cache is `INFERRED` | Recorded before correction; explicitly log out, clear that synthetic user's cache, reload it as current user, and rerun the complete matrix. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Keep AP-007 limited to reusable policy/discovery/execution primitives and synthetic fixtures; AP-008 owns production Ability registration. | checklist dependency boundary | Runtime tests call policy primitives directly before the catalog exists. | A required policy control cannot be expressed without production registration. |
| D2 | `DECIDED` | Centralize current-user checks in a resolver that reads live post-type/taxonomy capability objects; never branch on role names. | S1–S4; falsification condition | More runtime calls than a precomputed role table, but custom capability changes apply immediately. | WordPress provides an equivalent stable effective-operation API. |
| D3 | `DECIDED` | Discovery filters only the fixed 15-operation map using coarse potential authority; execution ignores discovery state and independently performs exact object/action checks plus Safe Mode. | binding specification section 4.1 | A hidden tool may still be route-guessed, so the execution path remains the security boundary. | AP-008 changes transport discovery semantics. |
| D4 | `DECIDED` | Determine AgentPress-created draft authority only from an `APPLIED` `agentpress/create-draft` change row for the current object; accept no post-meta shortcut. | binding specification section 4.2; AP-005 schema | Adds one indexed database lookup for contextual R1/R2 classification. | The durable change schema or authority model changes. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Required role/capability matrix follows actual capabilities | Real WordPress Administrator, Editor, Author, Subscriber, logged-out, and explicit-capability mutation controls | `SUPPORTED` | harness success: four roles, logged out, mutation; 16-operation envelope |
| Discovery is coarse and no broader than execution authority | Fixed-map discovery plus independently invoked exact execution policy | `SUPPORTED` | 15 fixed abilities; direct policy evaluations and 42-test unit suite |
| Object ownership/status rules fail closed | AgentPress-created versus ordinary/published/other-owned draft controls | `SUPPORTED` | four object-specific controls; durable APPLIED-change lookup only |
| R3 and unknown surfaces are absent/unreachable | Fixed-map absence plus seven Administrator route guesses | `SUPPORTED` | seven guesses returned `AP_POLICY_BLOCKED`; blocked areas remain fixed |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-013-safe-mode-discovery-policy.md` | evidence | committed | `EXP-013`; `c510713` | Opened before AP-007 source research or product-code mutation. |
| `agentpress/includes/Policy/` | source | committed | 7 policy classes; `c510713` | Resolver, risk, Safe Mode, discovery, envelope, execution, and durable draft-authority lookup. |
| `agentpress/tests/phpunit/unit/PolicyTest.php` | executable evidence | committed | unit policy matrix; `c510713` | Fixed risk, contextual R1/R2, live capability mutation, discovery, envelope, and direct execution controls. |
| `agentpress/tests/integration/ap007-safe-mode-discovery-policy.php` | executable evidence | committed | WordPress matrix; `c510713` | Four default roles, logged out, capability mutation, object-specific ownership/state, and seven R3 route guesses. |
| `dist/agentpress.zip` | generated release candidate | ignored/uncommitted | `2CF45FB1492F7458F9272B2BB17CF5820B07E41EC0A7262CE8344E3354AC0770` | Two consecutive deterministic builds; not a published release artifact. |

## Result

`SUPPORTED`

The local repository evidence supports the hypothesis: AP-007's fixed classifier, Safe Mode, live capability envelope, coarse discovery filter, and independent object-specific execution policy passed the required synthetic unit and real WordPress controls. Unknown/R3 route guesses failed closed, and capability mutation changed the effective surface after an explicit fixture cache reload.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: production AP-008 Ability registration, real browser/WebMCP discovery of AP-007-filtered tools, ChatGPT, deployment, and AP-008+ service behavior.
- The browser suite was run only as an AP-003/AP-004 regression; it does not prove a production AP-007 browser integration because AP-008 has not registered the Ability catalog.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-007 work;
- pre-existing work distinguished by: merged AP-006 baseline;
- third-party material/license/pin: no new third-party material planned;
- commit/PR evidence: issue #13, implementation commit `c51071361e044dfd60326ccf37f76c5497cfa727`, [PR #14](https://github.com/MrBigleg/AgentPress/pull/14), and hosted run `33361719090` passed;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-014 / AP-008 Ability registry;
- next falsifiable question: can exactly 15 fixed Abilities register with closed input/output contracts and no generic REST execution path?;
- required prerequisites: AP-007 merged with green capability/discovery evidence.

## End state

```text
git status --short --branch: AP-007-only source, tests, package manifest, checklist, README, index, and EXP-013 changes on ap-007-safe-mode-discovery-policy
tests/checks: AP-007 WordPress matrix pass; AP-004/AP-006 regressions pass; unit 42/219; PHPCS 28/28; browser 14/14; provenance 38 entries; npm audit 0; deterministic ZIP SHA-256 2CF45FB1492F7458F9272B2BB17CF5820B07E41EC0A7262CE8344E3354AC0770
committed: yes, implementation/evidence commit c51071361e044dfd60326ccf37f76c5497cfa727; this append-only commit-reference update pending
pushed: yes, origin/ap-007-safe-mode-discovery-policy; PR #14 hosted gate passed
deployed: no
```
