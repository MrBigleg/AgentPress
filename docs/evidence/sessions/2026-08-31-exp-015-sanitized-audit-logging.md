# EXP-015 — AP-009 sanitized audit logging

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-015` |
| Related task | `AP-009`; GitHub issue #17 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-31T17:38:22+07:00` |
| Started UTC | `2026-08-31T10:38:22Z` |
| Ended local | `2026-08-31T21:36:07+07:00` |
| Ended UTC | `2026-08-31T14:36:07Z` |
| Agent/operator | Codex, implementation and verification agent |
| Branch | `ap-009-sanitized-audit-logging` |
| Baseline commit | `22649d5d3d814134c8bb86c8d9126b904b145c20` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows; PowerShell 7; Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; controlled wp-env WordPress 6.9 and PHP 8.0.30 |

## Question

Can every authenticated AgentPress execution attempt emit one bounded durable audit row with a unique request ID and useful outcome metadata while fixture cookies, nonces, credentials, raw headers, arbitrary metadata, and full 200 KB content remain absent, and while logged-out or invalid-nonce traffic emits no durable row?

## Hypothesis

The merged AP-005 audit repository/table and AP-006 error boundary can support a narrow logger and recursive argument sanitizer. Wiring the logger after transport authentication and nonce validation but around authenticated execution should preserve the specification's visibility and no-secret boundary while recording authenticated denials, successes, failures, and replays.

## Falsification condition

The hypothesis is false if any forbidden secret or raw header survives in durable storage; a 200 KB body creates an unbounded row or lacks byte count, SHA-256, and a preview of at most 200 escaped characters; any authenticated direct denial, success, failure, or replay lacks its required event; logged-out or invalid-nonce traffic creates a durable row; request IDs collide; or logging failure permits an otherwise unauthorized WordPress mutation.

## Controls

- fixed commit/build: clean synchronized main baseline `22649d5d3d814134c8bb86c8d9126b904b145c20`;
- fixed fixture/data: synthetic credentials, headers, nested arguments, and a deterministic 200 KB content body; no private production data;
- fixed identity/capabilities: Administrator, restricted authenticated user, logged-out user, and invalid REST nonce controls;
- fixed policy/configuration: merged AP-007 policy, AP-008 fixed registry, default AgentPress preservation settings;
- fixed client/environment: repository PHP/unit controls plus the controlled wp-env WordPress runtime;
- explicit scope exclusions: AP-010 Change Set orchestration, AP-011+ service mutations, admin activity UI/read API, deployment, ChatGPT Site Tools, and release evidence.

## Variables

- **Independent:** authentication/nonce validity, execution result class, argument key/value class, content size, actor, and logger-storage availability.
- **Dependent:** durable row count, request ID uniqueness, actor/user/result/error/target/timing fields, sanitized JSON size/content, secret absence, and unauthorized WordPress mutation count.

## Preflight

```text
timestamp: local 2026-08-31T17:38:22+07:00; UTC 2026-08-31T10:38:22Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main (clean; git emitted only a user-level ignore access warning during an earlier status command)
git log -3 --oneline --decorate:
22649d5 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-008 merge evidence
fe7f39e Merge pull request #16 from MrBigleg/ap-008-ability-registry
8c78750 (origin/ap-008-ability-registry, ap-008-ability-registry) docs: record AP-008 hosted gate
baseline SHA: 22649d5d3d814134c8bb86c8d9126b904b145c20
current branch: main; isolated AP-009 branch pending after this record opens
unrelated existing changes: none
AP task, issue, and PR: AP-009; no duplicate AP-009 issue observed; issue/PR pending
environment: Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; main run 33383116316 passed for the exact baseline
```

## Method

1. Open this record and index entry, then create an isolated AP-009 branch and one milestone issue.
2. Inspect the merged schema/repository, transport validation order, Ability execution boundary, and official WordPress APIs used for request IDs, JSON encoding, and current identity.
3. Define an explicit recursive no-secret policy, bounded content summary contract, event/result vocabulary, and failure behavior before implementation.
4. Implement the argument sanitizer and audit logger without implementing AP-010 or AP-011+ product mutations.
5. Wire durable events only after authenticated cookie identity and a valid REST nonce; cover authenticated denial, success, failure, and replay paths.
6. Run synthetic fixture controls including cookie, nonce, password-like keys, authorization/full headers, arbitrary metadata, and deterministic 200 KB content; assert bounded rows and exact secret absence.
7. Run logged-out/invalid-nonce and storage-failure zero-row/zero-unauthorized-mutation controls plus all affected regressions.
8. Run unit, PHP standards, browser, provenance, dependency audit, and deterministic package gates proportional to the change.
9. Commit, push, open an issue-linked PR, and merge only after the exact latest head passes hosted checks; append all failures and closeout evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary agent owns source inspection, implementation, and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `DECIDED` | `docs/IMPLEMENTATION_SPEC.md` sections 2, 7, 8.3, and 9.3 at baseline `22649d5` | 2026-08-31 | Durable audit begins only after authentication/nonce validation; authenticated denials and outcomes are recorded; the row schema and explicit no-secret/content-summary contract are fixed. | Project contract, not external runtime proof. |
| S2 | `OBSERVED` | `docs/BUILD_CHECKLIST.md` AP-009 at baseline `22649d5` | 2026-08-31 | Acceptance requires secret fixtures plus a 200 KB body, authenticated denial rows, and zero rows for logged-out/invalid-nonce traffic. | Executable evidence pending. |
| S3 | `OBSERVED` | Controlled wp-env WordPress 6.9 / PHP 8.0.30 runtime | 2026-08-31 | WordPress UUID generation produced four unique request IDs accepted by the exact audit-table constraint; current-user identity and UTC repository clock persisted through the existing AP-005 boundary. | Runtime observation, not a deployment claim. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T17:38:22+07:00 | Capture clean baseline, recent commits, environment versions, duplicate issue search, and latest main workflow | repository/GitHub | exit 0 | Clean main at full SHA `22649d5d3d814134c8bb86c8d9126b904b145c20`; no AP-009 issue; run `33383116316` success; Node/npm/Docker versions recorded above. |
| timestamp not independently captured | Re-read required project documents and AP-009 contract sections | repository | exit 0 | README, PRD, implementation specification, checklist, index, EXP-014, and experiment template inspected; AP-009 scope and falsification conditions extracted before product-code mutation. |
| timestamp not independently captured | Open EXP-015/index, create `ap-009-sanitized-audit-logging`, and create issue #17 | repository/GitHub | exit 0 | Evidence existed before AP-009 product-code inspection/mutation; isolated branch and [issue #17](https://github.com/MrBigleg/AgentPress/issues/17) created. |
| timestamp not independently captured | Inspect merged audit repository, storage codec, request guard, execution route, plugin wiring, factories, registrar, tests, package manifest, and CI workflow | repository | first workflow-path read failed; corrected inspection exit 0 | The audit table/repository already match the specified columns; `WebMCPRoutes::execute()` is reached after guard authentication/nonce validation and currently has no durable audit; actual workflow is `.github/workflows/ap001-ci.yml`. |
| timestamp not independently captured | Add sanitizer/logger, authenticated execution audit wiring, package entries, unit controls, and AP-009 WordPress matrix; run diff/syntax precheck | repository/host | diff check exit 0; host syntax commands unavailable | Product/test mutation complete for first verification; host has no `php` executable, so five `php -l` invocations failed before parsing and must be rerun in wp-env. |
| timestamp not independently captured | Start wp-env, activate plugin, inspect runtime, and parse five changed PHP files | controlled wp-env | exit 0 | WordPress 6.9; plugin active; all changed source/unit/integration PHP files reported no syntax errors. |
| timestamp not independently captured | First full PHP unit and coding-standard gates | controlled wp-env | unit exit 0; lint exit 2 | 51 tests/558 assertions passed. PHPCS scanned 32 files and reported seven errors plus 18 warnings, all auto-fixable alignment/docblock spacing in `AuditLogger.php` and `WebMCPRoutes.php`. |
| timestamp not independently captured | Scoped PHPCBF and full standards rerun | controlled wp-env | formatter exit 1 after fixes; lint exit 0 | PHPCBF fixed all 25 marked findings in the two scoped files; its conventional exit 1 indicated fixes were applied. Full PHPCS passed 32/32 files. |
| timestamp not independently captured | First AP-009 real WordPress/database matrix | controlled wp-env | exit 1 | Success/denial/failure/replay rows were created and early assertions passed, but the secret scan found raw `idempotency-secret-ap009` in durable sanitized arguments. |
| timestamp not independently captured | Correct no-secret matcher; rerun unit, standards, and AP-009 matrix | repository/wp-env | exit 0 | 51 tests/560 assertions; PHPCS 32/32; AP-009 produced four `SUCCESS|DENIED|FAILED|REPLAYED` rows, eight secret markers absent, 204,800 content bytes summarized into 368 JSON bytes, zero logged-out/invalid-nonce rows, and zero unauthorized execution. |
| timestamp not independently captured | AP-004 through AP-008 dependency regressions | controlled wp-env | exit 0 | AP-004 valid execution plus 14 forbidden controls; AP-005 three-table/idempotency/repository/lifecycle matrix; AP-006 13 invalid input classes/four invalid outputs/17 errors; AP-007 role/capability/object/R3 matrix; AP-008 exact 15 registry/REST/discovery matrix all passed. |
| timestamp not independently captured | Browser, provenance, dependency-audit, script-syntax, and diff gates | repository | browser/syntax/diff exit 0; provenance exit 1; dependency-audit output inconclusive | Browser 14/14 passed. Provenance stopped before inspection when the build helper received Windows `EPERM` deleting ignored `dist/agentpress.zip`; the concurrent audit call returned no conclusive bounded output and requires an isolated rerun. |
| timestamp not independently captured | Isolated provenance and dependency-audit reruns | repository | exit 0 | Licenses/pin verified; 42 ZIP entries; no upstream runtime code; `npm audit --omit=optional` found zero vulnerabilities. |
| timestamp not independently captured | Two sequential deterministic ZIP builds | repository | exit 0 | Both builds produced SHA-256 `3252213E825AD783B1D786F01AB568B0EAEE37CD7C0FD951B318D9014B6C300F`. |
| timestamp not independently captured | Restore tables intentionally removed by AP-005 and rerun AP-009 | controlled wp-env | exit 0 | PHP 8.0.30; migration restored the exact tables; AP-009 repeated the four rows/eight secret markers/204,800-to-368-byte/zero-row/zero-execution result. |
| timestamp not independently captured | Preserve canonical uppercase audit error codes; rerun unit, standards, and final AP-009 matrix | repository/wp-env | exit 0; one terminal output required an observed rerun | 51 tests/561 assertions; PHPCS 32/32; final observed AP-009 matrix passed with exact `AP_PERMISSION_DENIED` and `AP_INTERNAL_ERROR` row codes. The first matrix process ended but its output handle was omitted; container process inspection confirmed termination before the deterministic observed rerun. |
| timestamp not independently captured | Final provenance and two deterministic builds after error-code correction | repository | exit 0 | Provenance again verified 42 ZIP entries and no upstream runtime; both exact-final-source builds produced SHA-256 `74E3DA585264C8046B1B6EAF0FCD2D6F591E07ACDCB86B931DCF29859876DC21`. |
| timestamp not independently captured | Stop controlled wp-env and run Markdown fence/link plus diff checks | repository | stop/fence/diff exit 0; first link check invalid | wp-env stopped cleanly; all four changed Markdown files had balanced fences. The link checker treated root-level README's empty parent path as invalid and emitted false missing-link reports before any file lookup. |
| timestamp not independently captured | Correct and rerun changed-document structural verifier | repository | exit 0 | Balanced fences and all local targets passed for README (33 links), checklist (one), evidence index (25), and EXP-015 (two); `git diff --check` passed. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-005, AP-006, and AP-008 prerequisites are merged, and the exact clean main baseline passed hosted run `33383116316`. | recent log; README/index; GitHub run | AP-009 can begin on an isolated branch. |
| O2 | `OBSERVED` | No existing GitHub issue matched AP-009 before session mutation. | preflight issue query | A single milestone issue can be created without duplication. |
| O3 | `OBSERVED` | Only parsed Ability input reaches `ArgumentSanitizer`; `RequestGuard` rejects logged-out and invalid-nonce traffic before `WebMCPRoutes::execute()` creates a request ID or audit event. | source inspection; AP-009 zero-row controls | The durable boundary matches the fixed validation order. |
| O4 | `OBSERVED` | Success, authenticated denial, execution failure, and direct human replay produced four unique durable rows with exact actor/result/error/request identity. | final AP-009 matrix | Required event classes are representable and correlate with client results. |
| O5 | `OBSERVED` | Eight synthetic secrets—including cookie, nonce, password, authorization header, metadata, database password, idempotency key, and application password—were absent from all durable rows. | final AP-009 secret scan | The tested explicit no-secret list is enforced recursively. |
| O6 | `OBSERVED` | A 204,800-byte content value became an exact byte count, SHA-256, and escaped preview within a 368-byte JSON field; the full body was absent. | final AP-009 content assertions | Large semantic content remains bounded and auditable without duplication. |
| O7 | `OBSERVED` | Invalid nonce and logged-out requests produced zero rows; a denied execution with a failing audit repository returned `AP_INTERNAL_ERROR` and executed the Ability zero times. | final AP-009 negative controls | Pre-auth traffic is not durable and audit failure does not open an unauthorized execution path. |
| O8 | `OBSERVED` | AP-004 through AP-008, unit, standards, browser, provenance, dependency audit, and deterministic packaging all passed after the AP-009 changes. | execution log | The tested implementation preserves prerequisite behavior and repository gates. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | The repository workflow should be available at the guessed descriptive filename `.github/workflows/repository-checks.yml`. | The path did not exist; `rg --files .github` located `.github/workflows/ap001-ci.yml`, which contains the current repository checks. | inspection path error | Preserved before correction; inspected the actual workflow and used its declared gates. |
| F2 | Host `php -l` should provide a fast syntax precheck for the five changed PHP files. | PowerShell could not resolve a host PHP executable, so none of the files were parsed by those commands. | environment/tool availability | Preserved before correction; use the repository's controlled wp-env PHP runtime for syntax, unit, standards, and integration checks. |
| F3 | The first AP-009 implementation should pass the PHP coding standard. | PHPCS found seven errors and 18 warnings, limited to auto-fixable array/assignment alignment and parameter-docblock spacing in two changed files. | implementation formatting defect | Preserved before correction; run scoped PHPCBF on only those two files and rerun the full standards gate. |
| F4 | The recursive no-secret list should remove every fixture secret, including the raw idempotency key. | The first live matrix found `idempotency-secret-ap009` in `arguments_sanitized`; the initial matcher did not classify `idempotency_key` as forbidden. | security implementation defect | Preserved before correction; explicitly remove `idempotency_key` and `source_session`, add unit fixtures, clear the audit table, and rerun. |
| F5 | The provenance/package-boundary gate should build and inspect the ZIP. | Its child build received Windows `EPERM` while unlinking ignored `dist/agentpress.zip`, so no provenance conclusion was reached; the concurrent dependency-audit call also returned no conclusive output. | transient artifact-lock/tool observation failure | Preserved; rerun provenance and dependency audit independently, then perform deterministic ZIP builds sequentially. |
| F6 | The first final error-code matrix invocation should return a fully captured terminal result. | Its process ended, but the command wrapper omitted the returned session handle before final output; `docker top` confirmed no matrix process remained. | observation-handle defect | Preserved; reran the deterministic matrix after confirming termination and captured its full exit-0 output. |
| F7 | The first Markdown local-link verifier should resolve links in root `README.md`. | PowerShell `Split-Path -Parent README.md` returned an empty string, which `Join-Path` rejected and caused false missing reports; fence and diff checks still passed. | verifier path defect | Preserved; use `.` whenever a document's parent path is empty, then rerun all changed-document links. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Treat the implementation specification's stricter schema and sequencing as binding where the PRD's audit table is less detailed. | S1; PRD sections 43–45 | A narrower durable payload favors safety and audit usefulness over retaining raw request context. | A later explicit product-owner decision changes the v0.1 audit contract. |
| D2 | `DECIDED` | Audit only parsed Ability inputs and fixed metadata; never pass raw body or headers to the logger. | O3; S1 | Malformed raw JSON receives a useful denial row without retaining its body. | A reviewed future event requires a new explicitly sanitized field. |
| D3 | `DECIDED` | Treat `idempotency_key` and `source_session` as secret/session identifiers and remove them rather than hash them in generic audit arguments. | F4; implementation specification section 8.2 | AP-010 must store only its dedicated idempotency hash in the change domain, not in audit arguments. | The specification introduces a reviewed irreversible correlation field. |
| D4 | `DECIDED` | Preserve canonical uppercase public error codes in audit rows while lowercasing Ability/object identifiers. | shared AP-006 error contract; final matrix | Consumers can filter using the documented codes without accepting arbitrary identifiers. | The activity contract changes its error-code casing. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Secret fixture and 200 KB body produce one bounded, useful sanitized row | Real WordPress/MySQL fixture with eight secret markers and deterministic content | `PASS` | 204,800 bytes summarized into 368 JSON bytes; eight markers and full content absent |
| Authenticated denial/success/failure/replay each produce the intended durable event | Transport plus direct human replay controls | `PASS` | four unique rows with exact actor/result/error/request identity |
| Logged-out and invalid-nonce traffic produce zero durable rows | Guard controls with before/after row counts | `PASS` | zero rows for each class |
| Logger/storage failure cannot cause unauthorized mutation | Denied Ability plus throwing repository and execution counter | `PASS` | safe `AP_INTERNAL_ERROR`; zero Ability executions and zero new rows |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-015-sanitized-audit-logging.md` | evidence | uncommitted | `EXP-015` | Opened before AP-009 implementation inspection or code mutation. |
| `agentpress/includes/Audit/ArgumentSanitizer.php` | source | uncommitted | recursive no-secret/bounded sanitizer | Explicit secret/session/header/metadata exclusion plus content summary. |
| `agentpress/includes/Audit/AuditLogger.php` | source | uncommitted | fixed event writer | UUID/actor/result/identifier validation and repository boundary. |
| `agentpress/includes/Rest/WebMCPRoutes.php` | source | uncommitted | authenticated audit integration | One request ID across response/event; durable outcomes only after guard authorization. |
| `agentpress/tests/phpunit/unit/AuditSanitizerTest.php` | executable evidence | uncommitted | 51-suite contribution | Recursive removal, content bounds, global bounds, logger metadata validation. |
| `agentpress/tests/integration/ap009-sanitized-audit-logging.php` | executable evidence | uncommitted | real WordPress/MySQL matrix | Outcome, secret, size, unauthenticated, and storage-failure controls. |
| `dist/agentpress.zip` | generated release candidate | ignored/uncommitted | `74E3DA585264C8046B1B6EAF0FCD2D6F591E07ACDCB86B931DCF29859876DC21` | Two deterministic builds from final source; not a published release artifact. |

## Result

`SUPPORTED`

The local evidence supports the hypothesis. Authenticated WebMCP attempts produce bounded correlated rows for success, denial, and failure; the logger also accepts the fixed replay/human contract. The tested explicit secrets and session identifiers, raw header fixture, full 200 KB content, logged-out traffic, and invalid-nonce traffic are absent from durable storage. Audit persistence failure does not execute a denied Ability.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: deployed WordPress, real browser/WebMCP invocation of the production registry, ChatGPT, activity UI/read API, AP-010 mutation intent sequencing, human approval UI, and release behavior.
- Replay persistence is tested through the logger's fixed event contract; automatic idempotency replay detection belongs to AP-010 and is `NOT_TESTED` here.

## Competition evidence statement

- work attributable to challenge period: baseline, branch, issue, experiment, failures, and ending timestamps captured around AP-009 work;
- pre-existing work distinguished by: synchronized AP-008 closeout baseline;
- third-party material/license/pin: no new third-party runtime; existing pin/provenance gate passed with 42 ZIP entries and no upstream runtime code;
- commit/PR evidence: [issue #17](https://github.com/MrBigleg/AgentPress/issues/17); implementation `UNCOMMITTED`; PR pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-016 / AP-010 Change Set coordinator and idempotency;
- next falsifiable question: can one coordinator prevent mutation before durable intent, deduplicate identical keys, conflict on changed payloads, and reduce every child transition deterministically?;
- required prerequisites: AP-005, AP-007, and AP-009 merged with green storage/policy/audit evidence.

## End state

```text
git status --short --branch: AP-009-only sanitizer/logger, transport wiring, tests, package manifest, checklist, README, index, and EXP-015 changes on ap-009-sanitized-audit-logging
tests/checks: AP-009 WordPress matrix pass; AP-004–AP-008 regressions pass; unit 51/561; PHPCS 32/32; browser 14/14; provenance 42 entries; npm audit 0; deterministic final ZIP SHA-256 74E3DA585264C8046B1B6EAF0FCD2D6F591E07ACDCB86B931DCF29859876DC21
committed: no; implementation/evidence package pending
pushed: no; PR pending
deployed: no
```
