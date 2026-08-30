# EXP-011 — AP-005 database migrations and repositories

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-011` |
| Related task | `AP-005`; GitHub issue #9 |
| Status | `IN_PROGRESS` |
| Result | `PENDING` |
| Started local | `2026-08-31T05:14:36+07:00` |
| Started UTC | `2026-08-30T22:14:36Z` |
| Ended local | `PENDING` |
| Ended UTC | `PENDING` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-005-database-repositories` |
| Baseline commit | `c0db4b8fe9f5f450d5c198daf1fd5902078ff981` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 10.9.8; wp-env WordPress 6.9/PHP 8.0 planned |

## Question

Can AgentPress create the three exact v0.1 tables through an idempotent versioned `dbDelta` migration and provide typed repositories that round-trip bounded synthetic JSON with UTC dates, prepared SQL, and preserve-by-default deactivation/uninstall behavior?

## Hypothesis

WordPress's schema upgrade and database APIs can support an activation-safe migration layer plus narrow repositories without widening AP-005 into policy or Ability behavior. Explicit schema/version checks and synthetic lifecycle controls can demonstrate repeatability and preservation.

## Falsification condition

The hypothesis is falsified if activation omits or changes a required column/index, a second migration changes the resulting schema, repository CRUD loses or accepts out-of-contract data, runtime SQL interpolates untrusted values, timestamps are not stored in UTC, deactivation deletes rows, or default uninstall removes AgentPress data.

## Controls

- fixed commit/build: merged AP-004 baseline `c0db4b8fe9f5f450d5c198daf1fd5902078ff981`;
- fixed fixture/data: synthetic Change Set, change, and event identifiers and bounded JSON only;
- fixed identity/capabilities: database lifecycle behavior independent of user authorization; no real user content;
- fixed policy/configuration: exact three-table v0.1 schema, preserve by default, destructive uninstall only behind the specified explicit constant;
- fixed client/environment: wp-env WordPress 6.9/PHP 8.0 and repository PHPUnit/PHPCS/package gates;
- explicit scope exclusions: schemas/errors AP-006, Safe Mode AP-007, Ability catalog AP-008, business services, admin UI, live browser, deployment, and ChatGPT.

## Variables

- **Independent:** fresh versus repeated migration, repository operation, JSON/date boundary, activation/deactivation/uninstall configuration.
- **Dependent:** exact tables/columns/indexes, schema diff, returned records, affected rows, stored UTC values, prepared-query evidence, and row preservation/deletion.

## Preflight

```text
timestamp local: 2026-08-31T05:14:36+07:00
timestamp UTC: 2026-08-30T22:14:36Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
c0db4b8 (HEAD -> main, origin/main, origin/HEAD) Merge pull request #8 from MrBigleg/ap-004-private-rest-transport
dddf45a (origin/ap-004-private-rest-transport, ap-004-private-rest-transport) docs: close AP-004 evidence
1840903 feat: add private WebMCP transport
baseline SHA: c0db4b8fe9f5f450d5c198daf1fd5902078ff981
current branch: main; AP-005 branch planned after this record opens
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-005; issue pending; PR pending
environment: Node.js 22.23.2; npm 10.9.8; WordPress/PHP runtime pending
```

## Method

1. Create the isolated AP-005 branch and milestone-scoped issue after opening this record.
2. Inspect the exact specification schema/lifecycle contract and verify current `dbDelta`, activation, deactivation, uninstall, prepared-query, and UTC-date behavior from official WordPress documentation/source.
3. Implement a versioned migration and the three narrow repositories without adding AP-006+ business rules.
4. Add synthetic unit/runtime controls for exact schema/indexes, idempotent rerun, bounded CRUD, UTC storage, prepared dynamic values, deactivation preservation, and both default and explicitly destructive uninstall paths.
5. Update package/CI coverage; run local PHP, runtime, audit, provenance, and deterministic package checks while preserving failures in order.
6. Commit, push, open a draft PR linked to the issue, and merge only after local acceptance and green hosted checks.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work remains in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress `dbDelta()` reference](https://developer.wordpress.org/reference/functions/dbdelta/) | 2026-08-31 | `dbDelta()` compares supplied `CREATE TABLE` definitions with the database and applies create/update queries; it loads from WordPress's upgrade API. | Official reference; schema idempotence still requires runtime proof. |
| S2 | `SOURCE_VERIFIED` | [WordPress activation/deactivation hooks](https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/) | 2026-08-31 | Activation is the supported place to create custom tables; deactivation is not uninstall and should handle only temporary cleanup. | Supports migration on activation and preservation on deactivation. |
| S3 | `SOURCE_VERIFIED` | [WordPress uninstall methods](https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/) | 2026-08-31 | Root `uninstall.php` is invoked on deletion, must guard `WP_UNINSTALL_PLUGIN`, and is the lifecycle point for optional table/option removal. | AgentPress adds a stricter explicit opt-in before deletion. |
| S4 | `SOURCE_VERIFIED` | [WordPress `wpdb::prepare()` reference](https://developer.wordpress.org/reference/classes/wpdb/prepare/) | 2026-08-31 | Dynamic values require unquoted placeholders; WordPress 6.2+ supports `%i` for identifiers. | AgentPress minimum WordPress is 6.9. |
| S5 | `SOURCE_VERIFIED` | [WordPress `current_time()` reference](https://developer.wordpress.org/reference/functions/current_time/) | 2026-08-31 | `current_time( 'mysql', true )` returns a MySQL DATETIME string in GMT/UTC. | Used as the repository default clock boundary. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T05:14:36+07:00 | Verify AP-004 merge/issue closure, fast-forward clean main, and inspect experiment template/preflight | repository | exit 0 | Main is clean at merge commit `c0db4b8`; AP-004 PR #8 merged and issue #7 closed; EXP-011 is the next unused number. |
| timestamp not independently captured | Create `ap-005-database-repositories` and milestone issue #9 | repository/GitHub | exit 0 | Isolated task branch and [issue #9](https://github.com/MrBigleg/AgentPress/issues/9) created after EXP-011 opened. |
| timestamp not independently captured | Inspect storage specification, bootstrap/lifecycle source, tests/package/CI, and official WordPress database lifecycle references | repository/official WordPress docs | success | Exact three-table contract, migration hooks, prepared placeholders, uninstall guard, and UTC clock API identified before code mutation. |
| timestamp not independently captured | First AP-005 WordPress runtime harness | wp-env WordPress 6.9/PHP 8.0 | exit 0 | Three exact table/index controls, identical second migration, three repository round-trips, UTC clock, JSON bound, default preservation, explicit cleanup, and schema restoration passed. |
| timestamp not independently captured | First AP-005 PHPUnit and PHPCS | wp-env WordPress 6.9/PHP 8.0 | mixed | PHPUnit passed 10 tests/15 assertions. PHPCS failed on compressed/missing doc comments, missing `@throws`, dynamic exception text, one uninstall doc spacing error, and two expected direct-DDL warnings; no prepared-SQL error was reported. |
| timestamp not independently captured | Second and third AP-005 PHPCS runs | wp-env WordPress 6.9/PHP 8.0 | exit 1, then exit 0 | The second run isolated two unused codec parameters and a chained parser exception; after removal, all 16 production PHP files passed. |
| timestamp not independently captured | Expanded AP-005 runtime and real deactivation controls | wp-env WordPress 6.9/PHP 8.0 | exit 0 | Activation plus guarded upgrade remained schema-idempotent; raw idempotency key rejected; explicit uninstall preserved a similarly named unowned table; real `wp plugin deactivate` preserved a synthetic row before reactivation. |
| timestamp not independently captured | Browser, audit, provenance, package, and environment closeout | repository/wp-env | exit 0 | Browser 14/14; npm audit 0 vulnerabilities; provenance verified; 26-entry ZIP deterministic at `0B3BB13FC37DEA3603F7DD8D03D430917872A6706FFD280A69A8D1E815C78EFB`; wp-env stopped. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-004 merged at `c0db4b8`; AP-005's only declared dependency, AP-001, is also merged. | local main; PRs #2/#8 | AP-005 may begin without widening dependency scope. |
| O2 | `OBSERVED` | The implementation specification defines three expanded exact schemas, while the earlier PRD table sketch omits approval, idempotency, request, and failure fields. | implementation spec section 8; PRD section 44 | The implementation specification's resolved schema is binding; the PRD sketch is not sufficient for migration code. |
| O3 | `OBSERVED` | The current plugin activation stores only `agentpress_version`; uninstall preserves by default and deletes only that option after explicit opt-in. No schema or repository source exists. | `Activation.php`, `uninstall.php`, source listing | Establishes the AP-005 baseline. |
| O4 | `OBSERVED` | The first WordPress runtime harness passed the full schema, idempotence, repository, UTC, JSON-bound, and lifecycle matrix. | AP-005 runtime harness | Supports the hypothesis before standards/package gates. |
| O5 | `OBSERVED` | PHPUnit passed 10 tests/15 assertions; PHPCS found documentation/exception-output issues but no unsafe dynamic-value SQL. | first local PHP checks | Runtime and codec behavior are supported; production standards need correction. |
| O6 | `OBSERVED` | Final local gates passed: PHPCS 16/16 files, PHPUnit 10/10, browser 14/14, audit clean, provenance clean, and deterministic 26-entry package. | local command outputs/package hash | Supports repository quality and reproducible packaging. |
| O7 | `OBSERVED` | Real WordPress deactivation preserved the synthetic Change Set row; default uninstall preserved rows/version; explicit opt-in removed only the three tables and two version options while retaining an unowned sentinel table. | lifecycle harness and WP-CLI deactivate/check/reactivate | Supports the preserve-by-default and exact destructive-target claims. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | First production standards run passes | PHPCS reported compressed/missing comments, absent `@throws`, dynamic exception text, uninstall doc spacing, and expected direct-DDL warnings | documentation/standards defects | `RESOLVED`: expanded comments, bounded messages, documented throws, and narrowly annotated explicit uninstall DDL; final 16/16 files pass. |
| F2 | First PHPCS correction clears all findings | `JsonCodec` retained now-unused field labels and chained the caught parser exception, which the output-escaping sniff treats as unsafe exception output | standards defect | `RESOLVED`: removed unused labels and parser detail; final PHPCS passes. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Keep AP-005 limited to persistence mechanics and lifecycle behavior; business authorization and change-state policy remain in later tasks. | checklist dependency boundary | Repositories expose only bounded storage operations, not product workflows. | A required acceptance control cannot be expressed without AP-006+ policy. |
| D2 | `DECIDED` | Implement `Storage\\Migrator` and `Storage\\JsonCodec`, plus narrow Change Set, change, and audit-event repositories in their domain namespaces. | O2, O3; architecture layout | Adds storage primitives without inventing a generic ORM. | Later services require a transaction boundary the narrow repositories cannot express. |
| D3 | `DECIDED` | Cap any repository JSON document at 307,200 encoded bytes, matching the largest v0.1 transport input; reject encoding/decoding failures explicitly. | implementation spec 8 and transport maximum | Some audit fields will be much smaller after AP-009 sanitization, but no AP-005 record can exceed the global input ceiling. | A later exact per-field cap is stricter. |
| D4 | `DECIDED` | Deactivation is an explicit no-op; uninstall uses `%i` prepared identifiers and deletes the three exact prefixed tables plus AgentPress version options only when `AGENTPRESS_REMOVE_DATA_ON_UNINSTALL === true`. | S2–S4; implementation spec migration constants | Preserves recovery by default while making destructive intent auditable. | Multisite/network-wide product scope is explicitly added. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Activation creates exact columns and indexes | `Activation::activate()` plus ordered column/index assertions for all three tables | `OBSERVED`, pass | runtime harness |
| Repeated migration is idempotent | `SHOW CREATE TABLE` snapshots across direct and guarded reruns | `OBSERVED`, pass | identical schema strings |
| Repository CRUD round-trips bounded JSON and UTC dates | Three repository fixtures, injected UTC clock, updates/deletes, oversize control | `OBSERVED`, pass | nested English/Thai JSON and integer/string fields preserved; >300 KB rejected |
| Runtime dynamic SQL values are prepared | Quoted SQL-like title fixture, raw-key/unknown-column rejection, PHPCS | `OBSERVED`, pass | title stored literally without status mutation; no unprepared-value finding |
| Deactivation and default uninstall preserve rows | direct callback, real WP-CLI deactivate/check/reactivate, default `uninstall.php` include | `OBSERVED`, pass | synthetic rows and version option retained |
| Explicit destructive uninstall removes only AgentPress data | opt-in uninstall with three target tables plus similarly named sentinel | `OBSERVED`, pass | exact tables/options removed; sentinel retained; schema restored |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-011-database-repositories.md` | evidence | untracked | `EXP-011` | Opened before database research or mutation. |
| `agentpress/tests/integration/ap005-database-repositories.php` | executable evidence | untracked | SHA-256 `6B4A87C8258350A11172F30AEA456B81DA9FC917A5ECA50183FE6C67B4278970` | Main synthetic schema/repository/uninstall matrix. |
| `agentpress/tests/integration/ap005-deactivation-setup.php` | executable evidence | untracked | SHA-256 `24E7C902FAE495CA1134900F08A6F52596208CB6CF62FBCECACFC7FA700712A2` | Creates one row before real deactivation. |
| `agentpress/tests/integration/ap005-deactivation-check.php` | executable evidence | untracked | SHA-256 `032564C21BF74F3D3B646A8F113EB3227A2094B1A2FCFC01DC1531343E19F65E` | Verifies/removes row while plugin inactive. |
| `dist/agentpress.zip` | generated package control | ignored/uncommitted | SHA-256 `0B3BB13FC37DEA3603F7DD8D03D430917872A6706FFD280A69A8D1E815C78EFB` | Deterministic across consecutive builds; 26 entries. |

## Result

`PENDING CI/PR CLOSEOUT`

Local evidence supports AP-005. The experiment remains open until the implementation is committed, published, and passes the hosted repository gate.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: multisite/network activation, concurrent migration races, high-volume repository load, deployment, and AP-006+ behavior.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-005 work;
- pre-existing work distinguished by: merged AP-004 baseline;
- third-party material/license/pin: no new third-party material added; existing attribution/package boundary remains green;
- commit/PR evidence: pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-012 / AP-006;
- next falsifiable question: can shared closed schemas and error normalization reject every invalid class and serialize every documented error safely?;
- required prerequisites: AP-005 merged with green schema/lifecycle evidence.

## End state

```text
git status --short --branch: AP-005 implementation/evidence modified on ap-005-database-repositories
tests/checks: local schema/lifecycle runtime, PHPCS, PHPUnit, browser, audit, provenance, and deterministic package build pass
committed: no
pushed: no
deployed: no
```
