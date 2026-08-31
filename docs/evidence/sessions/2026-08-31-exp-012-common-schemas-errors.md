# EXP-012 — AP-006 common schemas and error normalization

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-012` |
| Related task | `AP-006`; GitHub issue #11 |
| Status | `IN_PROGRESS` |
| Result | `PENDING` |
| Started local | `2026-08-31T05:43:13+07:00` |
| Started UTC | `2026-08-30T22:43:13Z` |
| Ended local | `PENDING` |
| Ended UTC | `PENDING` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-006-common-schemas-errors` |
| Baseline commit | `9952e76c00790ee739612dd5a8e35e7a3b386b61` |
| Checkpoint commit | `59066ea3992cd2d6ddc67874efcb9fd5037cd777` |
| Ending commit | `PENDING`; experiment remains open |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 10.9.8; wp-env WordPress 6.9/PHP 8.0 planned |

## Question

Can AgentPress provide shared closed-schema builders, a validated success envelope, and safe `AP_*` error normalization such that every specified invalid-input class fails as `AP_SCHEMA_INVALID` and every declared error serializes to its documented bounded shape without leaking internal detail?

## Hypothesis

The implementation specification's common schema fragments and error table can be encoded once behind transport-independent validators/factories. Parameterized tests can prove closed-object, type/range, enum, size, and operation-combination failures before AP-008 registers production Abilities.

## Falsification condition

The hypothesis is falsified if any unknown field, wrong type/range, oversized content, unsupported enum, or invalid operation combination validates; if an invalid success output escapes validation; or if any declared error has the wrong HTTP/retryable mapping, unstable shape, or unsafe internal message/detail.

## Controls

- fixed commit/build: merged AP-005 baseline `9952e76c00790ee739612dd5a8e35e7a3b386b61`;
- fixed fixture/data: synthetic closed objects, documented boundary values, and safe error fixtures only;
- fixed identity/capabilities: schema/error behavior independent of user authority;
- fixed policy/configuration: exact v0.1 common fragments, success envelope, and documented error table;
- fixed client/environment: PHPUnit plus wp-env WordPress 6.9/PHP 8.0 where core schema behavior is required;
- explicit scope exclusions: Safe Mode/discovery AP-007, Ability registration AP-008, services, UI, browser acceptance, deployment, and ChatGPT.

## Variables

- **Independent:** field presence/type/range/size/enum/combination, declared error code, safe message/detail, and output envelope.
- **Dependent:** validation result/path, stable error code/HTTP/retryable/data shape, and absence of internal detail.

## Preflight

```text
timestamp local: 2026-08-31T05:43:13+07:00
timestamp UTC: 2026-08-30T22:43:13Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
9952e76 (HEAD -> main, origin/main, origin/HEAD) Merge pull request #10 from MrBigleg/ap-005-database-repositories
fa7cdc7 (origin/ap-005-database-repositories, ap-005-database-repositories) docs: close AP-005 evidence
2b1c5f3 feat: add versioned persistence layer
baseline SHA: 9952e76c00790ee739612dd5a8e35e7a3b386b61
current branch: main; AP-006 branch planned after this record opens
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-006; issue pending; PR pending
environment: Node.js 22.23.2; npm 10.9.8; WordPress/PHP runtime pending
```

## Method

1. Create the isolated AP-006 branch and milestone issue after opening this record.
2. Extract exact common schema fragments, result envelope, validation rules, and full error map from the binding specification; verify relevant WordPress Ability validation/error behavior from official source.
3. Implement transport-independent schema builders, validator, result envelope, and safe error factory without registering AP-008 Abilities.
4. Add parameterized unit/runtime controls for every acceptance invalid class, output validation, all declared errors, and unsafe-detail suppression.
5. Run local standards, unit/runtime, audit, provenance, and deterministic package gates; preserve failures in order.
6. Commit, push, open a draft PR linked to the issue, and merge only after local and hosted checks pass.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work remains in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress `rest_validate_value_from_schema()`](https://developer.wordpress.org/reference/functions/rest_validate_value_from_schema/) | 2026-08-31 | Core supports required fields, closed/additional properties, min/max length/items/properties, unique items, enum, pattern, format, and `oneOf`/`anyOf` in its JSON Schema subset. | Combination rules that depend on sibling operation values remain explicit AgentPress code. |
| S2 | `SOURCE_VERIFIED` | [WordPress Abilities PHP reference](https://developer.wordpress.org/apis/abilities-api/php-reference/) and [`WP_Ability`](https://developer.wordpress.org/reference/classes/wp_ability/) | 2026-08-31 | Ability input is validated before permissions; execution validates output against the declared output schema. | Supports reusable input/output schemas without duplicating Ability execution. |
| S3 | `SOURCE_VERIFIED` | [WordPress REST schema handbook](https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/) | 2026-08-31 | Values should be validated before schema sanitization; unique values can converge after sanitization and must be checked again. | AP-006 validates only; field sanitization remains with AP-010+ services. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T05:43:13+07:00 | Verify AP-005 merge/issue closure and fast-forward clean main | repository/GitHub | exit 0 | PR #10 merged at `9952e76`; issue #9 closed; EXP-012 is the next unused number. |
| timestamp not independently captured | Create `ap-006-common-schemas-errors` and milestone issue #11 | repository/GitHub | exit 0 | Isolated branch and [issue #11](https://github.com/MrBigleg/AgentPress/issues/11) created after EXP-012 opened. |
| timestamp not independently captured | Extract common conventions, combination rules, full 17-code error map, and official WordPress validation behavior | specification/official WordPress docs | success | Closed-schema subset and core Ability validation boundaries identified before code mutation. |
| 2026-08-31T10:38:42+07:00 | Prepare user-requested AP-006 checkpoint | repository | in progress | Four schema/result primitives are present; error normalization and AP-006 tests remain absent. This is a partial checkpoint, not AP-006 completion evidence. |
| 2026-08-31 checkpoint | `npm run lint:php` | repository | exit 1 | wp-env reported `service "cli" is not running`; environment failure occurred before source linting. |
| 2026-08-31 checkpoint | `npm run env:start` | repository | exit 0 | WordPress development site and MySQL started in 92 seconds. |
| 2026-08-31 checkpoint | `npm run lint:php` rerun 1 | repository | exit 1 | PHPCS reached source and reported 13 errors in the new schema files: docblock descriptions/alignment and compact associative arrays. |
| 2026-08-31 checkpoint | Correct the 13 PHPCS findings with `apply_patch` | two schema files | success | Only docblocks and array formatting changed. |
| 2026-08-31 checkpoint | `npm run lint:php` rerun 2 | repository | exit 0 | PHPCS passed 20/20 scanned PHP files in 19.69 seconds. |
| 2026-08-31 checkpoint | `npm run test:unit` | repository | exit 0 | Existing suite passed 10 tests and 15 assertions; it contains no AP-006-specific coverage yet. |
| 2026-08-31 checkpoint | `npm run env:stop` | repository | exit 0 | Temporary WordPress environment stopped after verification. |
| 2026-08-31 checkpoint | Commit verified six-file checkpoint | repository | exit 0 | `59066ea3992cd2d6ddc67874efcb9fd5037cd777`; 449 insertions; branch one commit ahead of `origin/main`; not pushed. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-005 merged at `9952e76`; AP-006's declared dependency AP-001 is merged. | local main; PRs #2/#10 | AP-006 may begin independently of AP-007/AP-008. |
| O2 | `OBSERVED` | The specification declares 17 `AP_*` codes and one closed error envelope; retry guidance is textual for nonce/schema/rate/internal rather than a literal boolean in every row. | implementation spec section 13 | The boolean mapping needs a conservative explicit project decision. |
| O3 | `SOURCE_VERIFIED` | WordPress core can enforce the required structural/type/range/enum/unique/format schema subset and validates Ability outputs after execution. | S1–S3 | AP-006 can wrap core validation and add only cross-field rules. |
| O4 | `OBSERVED` | `SchemaBuilder`, `SchemaValidator`, `CombinationRules`, and `ResultFactory` now encode closed-object fragments, common scalar/ID fragments, structural plus cross-field validation, the navigation operation rule, and the success envelope. | uncommitted source paths in artifact inventory | The implementation has a reviewable first slice but does not yet satisfy the full experiment. |
| O5 | `OBSERVED` | No error factory, bounded-detail normalizer, AP-006-specific tests, production Ability integration, or package-manifest update exists at this checkpoint. | repository status and tracked-file inventory | The 17-code error contract and acceptance matrix remain `NOT_TESTED`; AP-006 must stay `IN_PROGRESS`. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | PHP standards check reaches the source | wp-env `cli` service was stopped | environment | Start the declared wp-env runtime and rerun; do not classify as a source failure. |
| F2 | New source passes repository PHP standards | PHPCS reported 13 errors across `SchemaBuilder.php` and `SchemaValidator.php` | source formatting | Correct mechanically with `apply_patch`, then rerun and preserve both outcomes. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | AP-006 provides common schema/error primitives only; production Ability registration remains AP-008. | checklist dependency boundary | Tests use synthetic contracts before the catalog exists. | Exact catalog validation cannot be expressed without registering production Abilities. |
| D2 | `DECIDED` | Build schemas as inline arrays without `$ref`, wrap `rest_validate_value_from_schema()`, and run named cross-field callbacks after structural validation. | S1–S3; bridge `$ref` boundary | Avoids a second JSON Schema engine while keeping operation combinations explicit. | Core drops a required keyword or the bridge gains reliable `$ref` support. |
| D3 | `DECIDED` | Error-envelope `retryable` is true only for `AP_NONCE_INVALID` and `AP_RATE_LIMITED`; schema correction requires changed input and internal retries require a caller-specific decision, so both serialize false. | O2; one-retry client and bounded-loop policy | Conservative clients will not automatically repeat ambiguous failures. | The specification defines a distinct retry mode rather than a boolean. |
| D4 | `DECIDED` | Error normalization ignores arbitrary upstream exception messages and maps unknown codes to `AP_INTERNAL_ERROR`; details accept bounded scalar/list/object data only. | safe-message policy; falsification condition | Loses low-level detail at the client boundary; request IDs preserve server correlation. | A reviewed error code adds an explicitly safe detail schema. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Unknown fields and wrong types/ranges fail | Pending | `NOT_TESTED` | Pending |
| Oversized content, enums, and invalid combinations fail | Pending | `NOT_TESTED` | Pending |
| Success envelopes and Ability outputs validate | Pending | `NOT_TESTED` | Pending |
| Every declared error has exact safe serialization | Pending | `NOT_TESTED` | Pending |
| Checkpoint source meets repository PHP standards | `npm run lint:php` after one environment failure and one source-format failure | pass | 20/20 PHP files scanned; final exit 0 |
| Existing unit behavior remains green | `npm run test:unit` | pass | 10 tests, 15 assertions; final exit 0 |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-012-common-schemas-errors.md` | evidence | checkpoint committed | `EXP-012`; `59066ea` | Opened before AP-006 specification extraction or mutation. |
| `agentpress/includes/Schemas/SchemaBuilder.php` | source | checkpoint committed | `59066ea` | Shared inline closed-schema fragments and success-output schema. |
| `agentpress/includes/Schemas/SchemaValidator.php` | source | checkpoint committed | `59066ea` | WordPress core validation wrapper plus cross-field callbacks. |
| `agentpress/includes/Schemas/CombinationRules.php` | source | checkpoint committed | `59066ea` | At-least-one and classic navigation-operation rules. |
| `agentpress/includes/Results/ResultFactory.php` | source | checkpoint committed | `59066ea` | Common successful result envelope. |
| `agentpress/includes/Errors/` | source | absent | not applicable | Error factory and bounded safe details remain to be implemented. |

## Result

`PENDING`

`OBSERVED`: a partial schema/result implementation exists. No AP-006 completion, full schema-correctness, error-normalization, package, runtime, or hosted-CI claim exists yet.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: AP-006 acceptance inputs, output rejection, all 17 errors, bounded-detail safety, runtime/package integration, Ability catalog, browser, deployment, and AP-007+ behavior.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-006 work;
- pre-existing work distinguished by: merged AP-005 baseline;
- third-party material/license/pin: no new third-party material planned;
- commit/PR evidence: pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-013 / next dependency-ordered task;
- next falsifiable question: selected after AP-006 based on the dependency graph;
- required prerequisites: AP-006 merged with green contract evidence.

## End state

```text
git status --short --branch: expected clean after the documentation closeout on ap-006-common-schemas-errors
tests/checks: PHP standards 20/20 pass; existing PHPUnit 10 tests/15 assertions pass; AP-006 acceptance matrix remains NOT_TESTED
committed: source checkpoint 59066ea; documentation closeout is the commit containing this record
pushed: no
deployed: no
```
