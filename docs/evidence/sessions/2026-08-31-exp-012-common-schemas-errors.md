# EXP-012 — AP-006 common schemas and error normalization

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-012` |
| Related task | `AP-006`; GitHub issue #11 |
| Status | `SUPPORTED` |
| Result | `SUPPORTED` |
| Started local | `2026-08-31T05:43:13+07:00` |
| Started UTC | `2026-08-30T22:43:13Z` |
| Ended local | `2026-08-31T12:04:46+07:00` |
| Ended UTC | `2026-08-31T05:04:46Z` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-006-common-schemas-errors` |
| Baseline commit | `9952e76c00790ee739612dd5a8e35e7a3b386b61` |
| Checkpoint commit | `59066ea3992cd2d6ddc67874efcb9fd5037cd777` |
| Ending commit | `UNCOMMITTED`; implementation commit follows this record |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 10.9.8; wp-env WordPress 6.9/PHP 8.0 observed |

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
| 2026-08-31 continuation | Reconfirm branch and remote state before resuming AP-006 | repository | exit 0 | Worktree clean at `ba77856`; live Git state shows `origin/ap-006-common-schemas-errors` at the same checkpoint despite the earlier session recording no push. No external blocker remains. |
| 2026-08-31 continuation | Implement error normalization, output validation, unit coverage, WordPress acceptance harness, and release manifest entries | repository | mutation complete; verification pending | Added the declared 17-code map, canonical messages/status/retryability, bounded secret-suppressing details, unknown-code fallback, output validation, parameterized tests, and runtime fixtures without registering AP-008 Abilities. |
| 2026-08-31 continuation | `npm run env:start` | repository | exit 0 | WordPress development site and MySQL started in 158 seconds. |
| 2026-08-31 continuation | Expanded `npm run test:unit` | wp-env WordPress 6.9/PHP 8.0 | exit 0 | PHPUnit passed 32 tests and 185 assertions, including all 17 declared error mappings and safe-detail controls. |
| 2026-08-31 continuation | First expanded `npm run lint:php` | wp-env WordPress 6.9/PHP 8.0 | exit 1 | PHPCS reported 19 errors and 17 warnings: compact/alignment formatting in `ErrorFactory` plus one missing newline before `SchemaValidator`'s class close. |
| 2026-08-31 continuation | First real WordPress AP-006 harness | wp-env WordPress 6.9/PHP 8.0 | exit 1 | Closed/extra-field controls ran, then the wrong-type fixture unexpectedly validated. Core emitted notices that `type` is required because the enum helper supplied only `enum`. |
| 2026-08-31 continuation | PHPUnit and PHPCS after enum correction | wp-env WordPress 6.9/PHP 8.0 | mixed | PHPUnit passed 33 tests/188 assertions; PHPCS isolated one missing `@throws` tag on the newly rejecting enum helper. |
| 2026-08-31 continuation | Second real WordPress AP-006 harness | wp-env WordPress 6.9/PHP 8.0 | exit 1 | Enum notices were gone, but core treated string `"1"` as satisfying an integer property, so the wrong-type falsification control still failed. |
| 2026-08-31 continuation | Bridge error-envelope integration checks | repository/wp-env | mixed | Browser adapter passed 14/14 with nested envelope parsing; PHPCS isolated one class-close newline after removing the legacy private route error helper. |
| 2026-08-31 continuation | First AP-004 regression matrix after envelope integration | wp-env WordPress 6.9/PHP 8.0 | exit 1 before mutation | The first missing-nonce response normalized with empty details as `stdClass`; the `Retry-After` check indexed it as an array and stopped the harness before Ability resolution/execution. |
| 2026-08-31 continuation | Final AP-004 transport matrix | wp-env WordPress 6.9/PHP 8.0 | exit 0 | One valid execution plus 14 forbidden controls passed with zero resolver/execution/target side effects; all route failures used declared envelope codes, private headers, bounded nonce refresh, and rate guidance. |
| 2026-08-31 continuation | Final AP-006 WordPress matrix | wp-env WordPress 6.9/PHP 8.0 | exit 0 | 13 invalid-input classes, four invalid outputs, all 17 error contracts, unknown-message suppression, and 4,096-byte safe-detail bound passed. |
| 2026-08-31 continuation | Final standards, unit, browser, provenance, audit, and package gates | repository/wp-env | exit 0 | PHPCS 21/21; PHPUnit 33 tests/188 assertions; browser 14/14; provenance 31 ZIP entries; npm audit 0 vulnerabilities; deterministic ZIP SHA-256 `21EA19FDBFF711E6F3E21114A4D366916DEB08972971B65626AA936EC66A6649`. |
| 2026-08-31T12:04:46+07:00 | `npm run env:stop` and close experiment | repository | exit 0 | Temporary WordPress environment stopped; hosted CI/PR verification remains a separate publication gate. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-005 merged at `9952e76`; AP-006's declared dependency AP-001 is merged. | local main; PRs #2/#10 | AP-006 may begin independently of AP-007/AP-008. |
| O2 | `OBSERVED` | The specification declares 17 `AP_*` codes and one closed error envelope; retry guidance is textual for nonce/schema/rate/internal rather than a literal boolean in every row. | implementation spec section 13 | The boolean mapping needs a conservative explicit project decision. |
| O3 | `SOURCE_VERIFIED` | WordPress core can enforce the required structural/type/range/enum/unique/format schema subset and validates Ability outputs after execution. | S1–S3 | AP-006 can wrap core validation and add only cross-field rules. |
| O4 | `OBSERVED` | `SchemaBuilder`, `SchemaValidator`, `CombinationRules`, and `ResultFactory` now encode closed-object fragments, common scalar/ID fragments, structural plus cross-field validation, the navigation operation rule, and the success envelope. | uncommitted source paths in artifact inventory | The implementation has a reviewable first slice but does not yet satisfy the full experiment. |
| O5 | `OBSERVED` | No error factory, bounded-detail normalizer, AP-006-specific tests, production Ability integration, or package-manifest update exists at this checkpoint. | repository status and tracked-file inventory | The 17-code error contract and acceptance matrix remain `NOT_TESTED`; AP-006 must stay `IN_PROGRESS`. |
| O6 | `OBSERVED` | The live remote-tracking branch exists at the documentation checkpoint, contradicting the prior end-state note that the branch had not been pushed. | `git status --short --branch`; `git log --decorate` | Use current Git state for publication claims and preserve the older note as time-local evidence. |
| O7 | `OBSERVED` | WordPress requires explicit enum types but accepts numeric strings for integer schemas; AgentPress now applies recursive exact JSON/PHP type checks before core range/format validation. | failed then passing AP-006 harness | The corrected validator satisfies the wrong-type falsification control without replacing core's supported keyword checks. |
| O8 | `OBSERVED` | The private REST boundary now converts core JSON errors and prior transport-specific failures to the declared envelope; AP-004's valid and 14 forbidden controls remained green. | final AP-004 harness | Supports normalization at the actual bridge boundary with zero unauthorized mutation. |
| O9 | `OBSERVED` | Final local gates passed on the completed source and deterministic 31-entry package. | final execution-log rows and artifact inventory | Supports the hypothesis locally; hosted CI remains publication evidence, not runtime evidence. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | PHP standards check reaches the source | wp-env `cli` service was stopped | environment | Start the declared wp-env runtime and rerun; do not classify as a source failure. |
| F2 | New source passes repository PHP standards | PHPCS reported 13 errors across `SchemaBuilder.php` and `SchemaValidator.php` | source formatting | Correct mechanically with `apply_patch`, then rerun and preserve both outcomes. |
| F3 | Completed AP-006 source passes its first standards run | PHPCS reported 36 fixable compact-array/alignment findings in `ErrorFactory` and one fixable class-brace finding in `SchemaValidator` | source formatting | `RESOLVED`: the formatter fixed all 37 findings; final PHPCS passed 21/21. |
| F4 | WordPress core applies the enum-only helper as a strict schema | Core requires an explicit built-in `type`; enum-only properties emitted notices and the wrong-type fixture reached a true result | implementation defect | `RESOLVED`: infer `string` or `boolean`, reject empty/mixed enums, and rerun the complete harness without notices. |
| F5 | Enum correction remains standards-complete | PHPCS required documentation of the new `InvalidArgumentException` path | documentation defect | `RESOLVED`: added the exact `@throws` contract; final PHPCS passed. |
| F6 | WordPress core rejects JSON type confusion without coercion | `rest_validate_value_from_schema()` accepted numeric string `"1"` for an integer schema | security/contract defect | `RESOLVED`: recursive exact JSON/PHP type checks run before core range/format validation; the full invalid-input matrix passed. |
| F7 | REST boundary refactor remains standards-clean | Removing the legacy route error helper left one extra blank line before the class close | source formatting | `RESOLVED`: removed the exact blank line; final PHPCS and runtime matrices passed. |
| F8 | Empty error details can pass through the REST header filter | The rate-header condition indexed the contract's empty `{}` (`stdClass`) as an array | integration defect | `RESOLVED`: require array details before reading `retry_after`; AP-004's full valid/forbidden matrix passed. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | AP-006 provides common schema/error primitives only; production Ability registration remains AP-008. | checklist dependency boundary | Tests use synthetic contracts before the catalog exists. | Exact catalog validation cannot be expressed without registering production Abilities. |
| D2 | `DECIDED` | Build schemas as inline arrays without `$ref`, apply recursive exact JSON/PHP type checks, wrap `rest_validate_value_from_schema()` for its range/format keywords, and run named cross-field callbacks last. | S1–S3; F4/F6; bridge `$ref` boundary | Closes core coercion without adding a second general JSON Schema engine. | Core gains a strict non-coercive validation mode or the bridge gains reliable `$ref` support. |
| D3 | `DECIDED` | Error-envelope `retryable` is true only for `AP_NONCE_INVALID` and `AP_RATE_LIMITED`; schema correction requires changed input and internal retries require a caller-specific decision, so both serialize false. | O2; one-retry client and bounded-loop policy | Conservative clients will not automatically repeat ambiguous failures. | The specification defines a distinct retry mode rather than a boolean. |
| D4 | `DECIDED` | Error normalization ignores arbitrary upstream exception messages and maps unknown codes to `AP_INTERNAL_ERROR`; details accept bounded scalar/list/object data only. | safe-message policy; falsification condition | Loses low-level detail at the client boundary; request IDs preserve server correlation. | A reviewed error code adds an explicitly safe detail schema. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Unknown fields and wrong types/ranges fail | WordPress runtime closed object, strict nested types, positive integers, string/item bounds | `OBSERVED`, pass | AP-006 harness; failures F4/F6 preserved before final pass |
| Oversized content, enums, and invalid combinations fail | max string/items, homogeneous typed enums, uniqueness, at-least-one, navigation operation matrix | `OBSERVED`, pass | 13 total invalid-input classes |
| Success envelopes and Ability outputs validate | exact success envelope plus missing/invalid/extra/nested-output controls | `OBSERVED`, pass | one valid and four invalid outputs; invalid outputs return `AP_INTERNAL_ERROR` |
| Every declared error has exact safe serialization | parameterized unit/runtime maps, exact keys/status/retryability, unknown-code/message/secret controls | `OBSERVED`, pass | 17 codes; 4,096-byte detail bound; PHPUnit 33/188 |
| Bridge emits the common envelope without weakening AP-004 controls | valid execution and 14 forbidden transport controls | `OBSERVED`, pass | zero unauthorized resolution, execution, or target mutation; private/no-store headers retained |
| Completed source and package pass repository gates | PHPCS, PHPUnit, browser, provenance, npm audit, two ZIP builds | `OBSERVED`, pass | 21/21; 33/188; 14/14; 31 entries; 0 vulnerabilities; deterministic hash below |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-012-common-schemas-errors.md` | evidence | checkpoint committed | `EXP-012`; `59066ea` | Opened before AP-006 specification extraction or mutation. |
| `agentpress/includes/Schemas/SchemaBuilder.php` | source | modified after checkpoint; implementation commit pending | `59066ea` checkpoint | Shared inline closed-schema fragments, typed enum enforcement, and success-output schema. |
| `agentpress/includes/Schemas/SchemaValidator.php` | source | modified after checkpoint; implementation commit pending | `59066ea` checkpoint | Strict type preflight, WordPress core validation, output boundary, and cross-field callbacks. |
| `agentpress/includes/Schemas/CombinationRules.php` | source | checkpoint committed | `59066ea` | At-least-one and classic navigation-operation rules. |
| `agentpress/includes/Results/ResultFactory.php` | source | modified after checkpoint; implementation commit pending | `59066ea` checkpoint | Common successful result envelope with UUIDv4 enforcement. |
| `agentpress/includes/Errors/ErrorFactory.php` | source | implementation commit pending | SHA-256 `1AB8D8CF1D5177AFDA876BD1DB03D6269F9CD26C56BB2A9E7A1EC3C901E984FF` | 17-code canonical map, safe normalization, envelope, and bounded details. |
| `agentpress/tests/integration/ap006-common-schemas-errors.php` | executable evidence | implementation commit pending | SHA-256 `924ED33E67633AA02E03066905B112282F8A6EB09ECDCF0E538FA37505664581` | Synthetic WordPress acceptance matrix; excluded from release ZIP. |
| `agentpress/tests/phpunit/unit/ErrorFactoryTest.php` and `SchemaResultTest.php` | executable evidence | implementation commit pending | PHPUnit 33 tests/188 assertions repository total | Parameterized declared-error and primitive contract coverage. |
| `dist/agentpress.zip` | generated package control | ignored/uncommitted | SHA-256 `21EA19FDBFF711E6F3E21114A4D366916DEB08972971B65626AA936EC66A6649` | 54,503 bytes; 31 deterministic entries; all five common runtime files present. |

## Result

`SUPPORTED`

`SOURCE_VERIFIED`: WordPress documents the structural/range/format subset and Ability output-validation boundary used here.

`OBSERVED`: after two deliberately preserved runtime falsifications and one bridge integration failure, the corrected implementation rejects all 13 invalid-input classes, rejects four invalid outputs, serializes all 17 declared errors safely, preserves AP-004's 14 forbidden zero-side-effect controls, and passes the complete local repository/package gates.

The hypothesis is supported locally. Hosted CI and merge state are publication evidence to be appended after the implementation commit is pushed.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: production Ability catalog registration/output schemas (AP-008), Safe Mode/discovery (AP-007), real browser/WebMCP inspector, ChatGPT Site Tools, deployment, and AP-009+ behavior.
- `OBSERVED`: browser adapter unit behavior passed, but this is not real Chrome/WebMCP or ChatGPT acceptance evidence.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-006 work;
- pre-existing work distinguished by: merged AP-005 baseline;
- third-party material/license/pin: no new third-party material; existing pinned provenance gate passed with 31 packaged entries;
- commit/PR evidence: implementation commit/PR pending after this record;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-013 / AP-007 Safe Mode and discovery policy;
- next falsifiable question: can discovery and execution remain no broader than actual WordPress capabilities across the required role/capability matrix, with every R3 surface absent even under direct route guessing?;
- required prerequisites: AP-006 merged with green contract evidence.

## End state

```text
git status --short --branch: AP-006 implementation, tests, bridge integration, evidence, checklist, and package manifest modified/untracked on ap-006-common-schemas-errors
tests/checks: AP-004 and AP-006 WordPress matrices pass; PHPCS 21/21; PHPUnit 33/188; browser 14/14; provenance/audit/package gates pass
committed: checkpoint 59066ea and checkpoint docs ba77856; final implementation commit pending
pushed: checkpoint branch observed on origin; final implementation not pushed
deployed: no
```
