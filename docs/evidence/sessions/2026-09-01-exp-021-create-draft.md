# EXP-021 — Safe idempotent draft creation

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-021` |
| Related task | `AP-015` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-01T15:52:16+07:00` |
| Started UTC | `2026-09-01T08:52:16Z` |
| Ended local | `2026-09-01T16:08:02+07:00` |
| Ended UTC | `2026-09-01T09:08:02Z` |
| Agent/operator | Codex, implementation and evidence operator |
| Branch | `ap-015-create-draft` |
| Baseline commit | `0a049993d080d37e34a51b9a0511b4e78d863428` |
| Ending commit | `9e74b82` implementation; evidence closeout uncommitted |
| Environment | Windows; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 in wp-env CLI |

## Question

Can `agentpress/create-draft` create exactly one forced-draft post or page through the Change Set coordinator while enforcing role, type, parent, schema, and current-user KSES boundaries with zero unauthorized mutation?

## Hypothesis

The merged Ability schema, execution policy, content authority rules, and R1 coordinator make this plausible: a narrow service can validate the target, pass a deterministic command to `ChangeCoordinator::execute_r1()`, and perform one `wp_insert_post()` only after durable intent exists.

## Falsification condition

The hypothesis is falsified if any caller publishes content, selects an unsupported type, bypasses the closed schema, creates a page as Author, creates any content as Subscriber or logged-out, uses an invalid/unreadable/non-page parent, crosses the current user's KSES boundary, mutates after intent storage/claim failure, creates more than one post for an identical key, or returns replay success for the same key with different material input.

## Controls

- fixed commit/build: baseline `0a049993d080d37e34a51b9a0511b4e78d863428`; one AP-015 branch/build per run.
- fixed fixture/data: synthetic Administrator, Editor, Author, Subscriber users; synthetic readable/unreadable page parents; unique AP-015 titles/keys; before/after post and Change Set snapshots.
- fixed identity/capabilities: use real WordPress roles and current-user capability checks; no capability mutation unless an explicit negative control requires it.
- fixed policy/configuration: Safe Mode enabled; exact registered create-draft input/output schemas; R1 coordinator path.
- fixed client/environment: WordPress CLI integration runner in repository wp-env; Node/PHP gates from locked repository dependencies.
- explicit scope exclusions: update, taxonomy assignment, publish, wp-admin UI, deployment, real ChatGPT Site Tools, and five-run reliability.

## Variables

- **Independent:** caller role/login state, post type, parent, title/content/excerpt, markup, idempotency key, replay payload, and injected coordinator/storage outcome.
- **Dependent:** permission/result/error, created post count/fields/status/author/parent/content, Change Set/change rows and state, replay identity, and unauthorized target mutation count.

## Preflight

```text
timestamp: 2026-09-01T15:52:16+07:00 / 2026-09-01T08:52:16Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main before branch creation
git log -3 --oneline --decorate: 0a04999 docs: close AP-014 corrective merge evidence; d62c634 Merge pull request #29; fe003dd AP-014 corrective review commit
baseline SHA: 0a049993d080d37e34a51b9a0511b4e78d863428
unrelated existing changes: none observed
AP task, issue, PR: AP-015; issue #30; PR pending
environment: Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress 6.9; PHP 8.0.30
```

## Method

1. Inspect the exact Ability schema, execution policy, R1 coordinator contract, result/error factories, bootstrap/manifest, and adjacent integration patterns.
2. Implement one create-draft service and bind only `agentpress/create-draft`; force `post_status=draft`, current actor, and post/page allowlist independent of input.
3. Validate page parent type and current-user authority before coordination; normalize only registered fields and apply WordPress insertion/KSES under the current identity.
4. Execute through the R1 coordinator with a deterministic material payload and mutation callback; return the fixed registered result projection.
5. Run a real-WordPress AP-015 matrix covering Administrator/Editor/Author/Subscriber/logged-out, schema/type/status/parent/KSES, intent/replay/conflict/failure, and exact before/after mutation assertions.
6. Rerun AP-004 through AP-014 integration regressions, unit tests, PHP standards, browser contracts, provenance, audit, syntax/whitespace, and two deterministic ZIP builds.
7. Recheck staged manifest, commit only AP-015 artifacts, publish one branch/draft PR when authorized, inspect all review channels, and require exact-head plus merge-head hosted success before issue closeout.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator inspects and verifies all evidence. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | repository `docs/IMPLEMENTATION_SPEC.md` at baseline | 2026-09-01 | Create-draft is R1, forced draft, closed post/page contract with parent and idempotency boundaries. | Project contract; upstream runtime behavior still requires direct inspection/testing. |
| S2 | `SOURCE_VERIFIED` | repository Ability catalog and Change coordinator at baseline | 2026-09-01 | Exact registered schema and intent-before-mutation/idempotency interfaces already exist. | Inspected source; not proof of AP-015 behavior. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-01T15:52:16+07:00 | Capture status/log/SHA/timestamps and environment versions | repository | exit 0 with npm profile warning | Clean synchronized baseline; exact versions recorded above. |
| 2026-09-01T15:54:00+07:00 | Create issue #30 and branch `ap-015-create-draft` | GitHub/repository | success | Isolated issue/branch created from verified baseline before product-code mutation. |
| timestamp not independently captured | Run first AP-015 real-WordPress matrix | repository/wp-env | exit 1 | Fixture stopped while creating the second Subscriber-derived user because the helper reused one synthetic email address. No AP-015 behavior conclusion. |
| timestamp not independently captured | Rerun AP-015 after unique-user fix | repository/wp-env | exit 1 | First Administrator draft executed, then the test fatally called protected `WP_Ability::validate_output()`; `execute()` already validates output. No product failure classified. |
| timestamp not independently captured | Rerun AP-015 after public-API test correction | repository/wp-env | exit 1 | Editor draft succeeded but the test expected script stripping; the single-site Editor has `unfiltered_html`, so WordPress correctly followed the current user's broader KSES policy. |
| timestamp not independently captured | Run corrected AP-015 matrix | repository/wp-env | exit 0 | Five permitted role/type applications, three role/login denials, six schema/type/parent denials, three KSES capability controls, five replays, one conflict, six applied changes, and zero denied mutations passed. |
| timestamp not independently captured | Run initial repository gates | repository/wp-env | mixed | PHPUnit 68/593, browser 14/14, provenance 50 entries, and audit zero passed; PHPCS failed on 21 new-service docblock/alignment findings, 13 auto-fixable. |
| timestamp not independently captured | Rerun PHP standards after narrow formatting/docblock correction | repository/wp-env | exit 0 | All 40 PHP files passed. |
| timestamp not independently captured | Start AP-004 through AP-014 regression loop | repository/wp-env | exit 1 after two passes | AP-004 and primary AP-005 passed; generic file loop then called AP-005 deactivation check without its required setup/deactivate sequence, so its sentinel was absent. |
| timestamp not independently captured | Run explicit AP-006 through AP-014 standalone regressions | repository/wp-env | exit 0 | All nine named matrices passed; together with the earlier AP-004 and primary AP-005 passes, all applicable prior runtime regressions are green. |
| timestamp not independently captured | Build twice and run final syntax/whitespace checks | repository | exit 0 | Both 50-entry ZIPs matched SHA-256 `ACA6C5D90F2FA07F90532F9B702EB4CEF64C26A629D8A57D369747C5A4013E9C`; Node syntax and `git diff --check` passed. |
| timestamp not independently captured | Capture WordPress/PHP versions with inline PHP expression | repository/wp-env | exit 1 | PowerShell stripped string quotes, so WP-CLI evaluated undefined constants; command-evidence quoting failure only. |
| timestamp not independently captured | Capture versions with separate quote-safe commands | repository/wp-env | exit 0 | WordPress 6.9; PHP 8.0.30. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-010, AP-013, and AP-014 prerequisites are merged and their latest main workflows are green. | README/Evidence Index/GitHub runs | Supports starting AP-015. |
| O2 | `OBSERVED` | The final real-WordPress AP-015 matrix passed five authorized role/type applications, three role/login denials, six direct schema/type/parent denials, three capability-sensitive KSES controls, five identical replays, one changed-payload conflict, six durable applied rows, and zero denied mutations. | AP-015 matrix | Supports the hypothesis in the controlled fixture. |
| O3 | `OBSERVED` | AP-004 through AP-014 standalone regressions and every repository/package gate passed after documented test/standards corrections. | execution log | No observed regression in covered boundaries. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | Synthetic role fixtures should all have unique identities. | Second Subscriber-derived user reused `ap015-subscriber@example.test` and creation failed. | test fixture defect | Generate one suffix per user and include it in both login and email; clean stale AP-015 fixtures and rerun. |
| F2 | The matrix can call the Ability's output validator directly. | WordPress 6.9 declares `WP_Ability::validate_output()` protected; the test fatally stopped after its first successful execution. | test API misuse | Rely on public `WP_Ability::execute()`, which performs output validation, and retain exact result-shape assertions. |
| F3 | Every non-Administrator should have script markup stripped. | The default single-site Editor has `unfiltered_html`, and WordPress preserved the script. | test capability assumption | Assert stripping for Author and preservation only when the current identity actually has `unfiltered_html`; this tests the specified current-user policy rather than role labels. |
| F4 | Initial PHP standards gate should pass. | New service had 13 mechanical alignment findings and eight abbreviated/missing docblock descriptions. | implementation standards defect | Run PHPCBF on only the new service, expand its docblocks, and rerun PHPCS. |
| F5 | Every `ap004`–`ap014` PHP file is a standalone regression matrix. | AP-005 includes lifecycle helper scripts that require an ordered setup/deactivate/check flow; the generic loop invoked the check alone. | test orchestration defect | Keep the passed primary AP-005 matrix and run the named standalone AP-006–AP-014 matrices explicitly. |
| F6 | One inline PHP expression can capture both runtime versions through PowerShell/wp-env unchanged. | Nested quotes were stripped and PHP treated JSON keys as undefined constants. | command quoting defect | Use `wp core version` and `php -r 'echo PHP_VERSION;'` separately; both passed. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Keep AP-015 limited to create-draft; updates, terms, publishing, UI, and deployment remain separate tasks. | S1/O1 | Narrower integration surface. | Explicit project-owner scope change. |
| D2 | `DECIDED` | Let WordPress save filters enforce the current identity's KSES policy rather than imposing one role-agnostic allowlist. | F3/O2 | Editor behavior differs from Author when `unfiltered_html` is granted. | WordPress insertion/filter contract or project policy changes. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Forced draft, role/type/parent/KSES boundaries | real roles plus capability-mutated page creator and synthetic parents/markup | `PASS` | five forced drafts; six semantic denials; three KSES capability controls. |
| Intent before mutation, replay/conflict/storage failure | real coordinator rows/replays/conflict plus AP-010 injected storage/claim regressions | `PASS` | six applied rows; five replays; one conflict; AP-010 storage/claim mutation counters remained zero. |
| Zero unauthorized mutation and exact output schema | Ability execution and direct-service before/after controls | `PASS` | three role/login denials; six schema/type/parent denials; zero denied mutation. |
| Prior regressions and repository/package gates | AP-004–AP-014, PHPUnit, PHPCS, browser, provenance, audit, deterministic builds | `PASS` | all named gates green; ZIP hash below. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-021-create-draft.md` | evidence | uncommitted | EXP-021 | Opened before product-code mutation. |
| issue #30 | task | external | https://github.com/MrBigleg/AgentPress/issues/30 | Close only after corrected merge/main verification. |
| `agentpress/includes/Content/DraftCreationService.php` | implementation | committed | `9e74b82` | Closed validation, forced draft, parent/authority checks, R1 coordination, fixed result. |
| `agentpress/tests/integration/ap015-create-draft.php` | executable evidence | committed | `9e74b82` | Synthetic role/capability/KSES/idempotency/denial/durable-state matrix. |
| `dist/agentpress.zip` | generated package | excluded | SHA-256 `ACA6C5D90F2FA07F90532F9B702EB4CEF64C26A629D8A57D369747C5A4013E9C` | Two consecutive builds matched; 50 entries. |

## Result

`SUPPORTED`

`OBSERVED`: the controlled real-WordPress evidence supports the hypothesis. Authorized users created only forced post/page drafts, the Author/page and Subscriber/logged-out paths failed, page parents were type/read checked, WordPress followed each caller's KSES capability, identical keys replayed one durable result, changed input conflicted, and denied trials caused zero target mutation.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: wp-admin UI, representative production timing/concurrency beyond the coordinator controls, deployment, real ChatGPT Site Tools, release installation, and five-run reliability.

## Competition evidence statement

- work attributable to challenge period: exact pre-mutation timestamps/baseline recorded;
- pre-existing work distinguished by: verified AP-014 closeout baseline;
- third-party material/license/pin: `NOT_APPLICABLE`; AP-015 adds no third-party material and the provenance/package gate passed;
- commit/PR evidence: issue #30; implementation `9e74b82`; evidence closeout uncommitted; PR pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: `AP-016 — Implement update-content` after AP-015 hosted closeout;
- next falsifiable question: can bounded draft updates apply only to AgentPress-owned drafts while every other target becomes an immutable zero-mutation proposal?;
- required prerequisites: AP-015 exact-head and merge-head hosted gates.

## End state

```text
git status --short --branch: EXP-021, index, checklist, and README uncommitted on ap-015-create-draft
tests/checks: AP-015 matrix pass; AP-004–AP-014 regressions pass; PHPUnit 68/593; PHPCS 40 files; browser 14/14; provenance 50 entries; audit 0; deterministic ZIP pass
committed: implementation `9e74b82`
pushed: no AP-015 branch
deployed: no
```
