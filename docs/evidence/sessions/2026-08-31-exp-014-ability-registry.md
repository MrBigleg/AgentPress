# EXP-014 — AP-008 fixed Ability registry

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-014` |
| Related task | `AP-008`; GitHub issue #15 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-31T16:19:07+07:00` |
| Started UTC | `2026-08-31T09:19:07Z` |
| Ended local | `2026-08-31T17:18:28+07:00` |
| Ended UTC | `2026-08-31T10:18:28Z` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-008-ability-registry` |
| Baseline commit | `dd82e82ddf5d0572b2fa01f46e7349d08443fafe` |
| Ending commit | `6c037ba22204351b506fad0fb979b6c2b1d51250` |
| Environment | Windows/PowerShell; Node.js 22.23.2; accessible npm 10.9.8; repository wp-env WordPress/PHP versions pending runtime capture |

## Question

Can AgentPress register exactly the 15 fixed v0.1 Abilities with the specified category, callbacks, closed schemas, annotations, and collision-free WebMCP mappings while keeping native WordPress Abilities REST listing/execution unavailable and exposing only the current user's AP-007-filtered bridge tools?

## Hypothesis

The merged AP-006 schema/error factories and AP-007 discovery/execution policies can be composed into one fixed registry at the official WordPress Abilities lifecycle hooks. A real WordPress integration matrix should observe exactly 15 contracts, zero generic native REST exposure, and bridge discovery equal to the AP-007 current-user filter.

## Falsification condition

The hypothesis is falsified if the runtime registry differs from exactly 15 `agentpress/*` Abilities; if any category, callback, schema, annotation, or WebMCP name differs from the binding specification; if native Abilities REST lists or executes an AgentPress Ability; if any unknown/R3 operation is registered or route-executable; or if bridge discovery exceeds the current user's AP-007-filtered fixed map. Security controls require zero unauthorized mutation.

## Controls

- fixed commit/build: clean synchronized main baseline `dd82e82ddf5d0572b2fa01f46e7349d08443fafe` after AP-007 merge closeout;
- fixed fixture/data: the binding 15-operation catalog, AP-006 closed schemas, and synthetic WordPress users/targets only;
- fixed identity/capabilities: Administrator, capability-limited user, and logged-out controls selected after contract extraction;
- fixed policy/configuration: AP-007 Safe Mode/discovery/execution behavior and `meta.show_in_rest=false` for every Ability;
- fixed client/environment: repository-pinned wp-env and package tooling, with runtime versions captured when started;
- explicit scope exclusions: AP-011+ service implementations, wp-admin UI, real browser/WebMCP acceptance, ChatGPT Site Tools, deployment, reliability, and submission.

## Variables

- **Independent:** lifecycle hook, Ability definition, current user/capabilities, native REST path, bridge discovery path, and unknown/R3 name.
- **Dependent:** registered category/Ability count and metadata, callback/schema identity, REST visibility/execution result, discovered bridge names, policy result, mutation count, and package/test outcome.

## Preflight

```text
timestamp local: 2026-08-31T16:19:07+07:00
timestamp UTC: 2026-08-31T09:19:07Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
dd82e82 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-007 merge evidence
6206e53 Merge pull request #14 from MrBigleg/ap-007-safe-mode-discovery-policy
c3ea8ef (origin/ap-007-safe-mode-discovery-policy, ap-007-safe-mode-discovery-policy) docs: record AP-007 hosted gate
baseline SHA: dd82e82ddf5d0572b2fa01f46e7349d08443fafe
current branch: main; AP-008 branch pending after this record opens
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-008; no duplicate milestone issue observed; issue/PR pending
environment: Node.js 22.23.2; accessible npm 10.9.8; PowerShell access to the roaming npm CLI was denied; WordPress/PHP pending
```

## Method

1. Open this record and index entry, then create an isolated AP-008 branch and one milestone issue.
2. Extract the exact 15 Ability names, category, lifecycle hooks, callbacks, input/output schemas, annotations, REST metadata, and bridge mappings from the binding specification and current primary WordPress source/documentation.
3. Implement one deny-by-default registry using the merged AP-006 and AP-007 boundaries without implementing AP-011+ service mutations.
4. Add unit and real WordPress controls for exact registration, lifecycle timing, schema/annotation identity, native REST absence, current-user bridge filtering, unknown/R3 denial, and zero unauthorized mutation.
5. Run AP-004/AP-006/AP-007 regressions plus standards, unit/runtime, browser, audit, provenance, and deterministic package gates; preserve failures in order.
6. Commit, push, open an issue-linked PR, and merge only after local and latest hosted checks pass.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work remains in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress `wp_register_ability()` reference](https://developer.wordpress.org/reference/functions/wp_register_ability/), WordPress 6.9 | 2026-08-31 | Abilities must register during `wp_abilities_api_init`; category, callbacks, and schemas are top-level arguments; metadata contains annotations and `show_in_rest`. | Category must already be registered. |
| S2 | `SOURCE_VERIFIED` | [WordPress Abilities REST endpoints](https://developer.wordpress.org/apis/abilities-api/rest-api-endpoints/), WordPress 6.9 | 2026-08-31 | Explicit `meta.show_in_rest=false` hides an Ability from native listings and returns `rest_ability_not_found` for direct REST retrieval/execution. | Must still prove against the pinned runtime. |
| S3 | `SOURCE_VERIFIED` | [WordPress `WP_Ability::check_permissions()` reference](https://developer.wordpress.org/reference/classes/wp_ability/check_permissions/), WordPress 6.9 | 2026-08-31 | Direct permission checks do not validate input; `execute()` validates input, checks permission, executes, and validates output. | The bridge must validate before its detailed permission preflight as specified by AP-006. |
| S4 | `SOURCE_VERIFIED` | Pinned wp-env WordPress 6.9 source `/var/www/html/wp-includes/abilities-api/class-wp-ability.php`, PHP 8.0.30 | 2026-08-31 | `meta.annotations` is merged with core `readonly/destructive/idempotent` defaults and preserves additional boolean keys; `show_in_rest` defaults false. | Source inspected directly in the controlled runtime. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-31T16:19:07+07:00 | Verify AP-007 merge/main CI, clean synchronized main, environment, and duplicate AP-008 issue absence | repository/GitHub | exit 0 | PR #14 merged as `6206e53`; issue #13 closed; latest PR run `33361850959`, merge run `33376661914`, and main closeout run `33376968801` passed; no AP-008 issue returned. |
| timestamp not independently captured | Open EXP-014/index, create `ap-008-ability-registry`, and create issue #15 | repository/GitHub | exit 0 | Evidence existed before AP-008 research/mutation; isolated branch and [issue #15](https://github.com/MrBigleg/AgentPress/issues/15) created. |
| timestamp not independently captured | Verify current official Abilities contracts and inspect the pinned 6.9 `WP_Ability::prepare_properties()` source | official WordPress docs/wp-env | success | S1–S4; runtime captured WordPress 6.9 and PHP 8.0.30. |
| timestamp not independently captured | Implement first fixed catalog/registrar, plugin lifecycle wiring, AP-007 discovery integration, bridge input prevalidation, and package manifest; run initial unit and PHPCS gates | repository/wp-env | unit exit 0; PHPCS exit 1 | Existing unit suite passed 42 tests/219 assertions. PHPCS reported 108 errors and 11 warnings across the new catalog/registrar and touched bridge; 69 findings were mechanically fixable. |
| timestamp not independently captured | First scoped PHPCBF invocation through `composer exec` | repository/wp-env | exit 1 | Composer consumed `--standard` as its own unsupported option; no formatting occurred. |
| timestamp not independently captured | Direct scoped PHPCBF, helper-documentation correction, and PHPCS rerun | repository/wp-env | formatter exit 1 after fixes; lint exit 0 | PHPCBF corrected 74 findings and reported 45 remaining documentation findings; the explicit schema-helper documentation policy and throw tag cleared the final gate at 30/30 files. |
| timestamp not independently captured | Add static catalog unit controls and real WordPress AP-008 registry/REST/discovery harness | repository | mutation complete; verification pending | Unit coverage asserts exact map identity/common contracts/nested closure; runtime harness covers 15 objects, callbacks, native REST absence, policy-filtered bridge discovery, forbidden absence, and zero unauthorized post mutation. |
| timestamp not independently captured | Expanded unit/PHPCS gates and first AP-008 WordPress matrix | repository/wp-env | unit/lint exit 0; matrix exit 1 | 44 tests/532 assertions and PHPCS 30/30 passed. Runtime registered 15 but stopped on strict PHP identity for the `get-context` input schema. |
| timestamp not independently captured | Inspect catalog/runtime `get-context` schema with `var_export` | wp-env WordPress 6.9 | exit 0 | Both structures were semantically identical, including empty `stdClass` properties; strict comparison failed because separate object instances are not identical in PHP. |
| timestamp not independently captured | Second AP-008 WordPress matrix after F4 correction | repository/wp-env | exit 1 | All catalog/runtime schema comparisons passed; the matrix stopped because the native REST read-route response differed from the expected 404 plus `rest_ability_not_found`. |
| timestamp not independently captured | First inline native REST diagnostic | wp-env WP-CLI | exit 1 | PowerShell expanded unescaped PHP variables before WP-CLI evaluation, producing an invalid PHP expression and parse error; no state changed. |
| timestamp not independently captured | Escaped native REST route diagnostics | wp-env WordPress 6.9 | exit 0 | `/wp-abilities/v1/agentpress/get-context/run` returned `rest_no_route`; the actual `/wp-abilities/v1/abilities/agentpress/get-context/run` route returned 404 `rest_ability_not_found`. |
| timestamp not independently captured | Third AP-008 WordPress matrix after F5 correction | repository/wp-env | exit 0 with notices | Substantive matrix passed: 15 registered, zero REST-listed, two REST run paths blocked, Administrator 15 tools, Subscriber 8, logged out 0, seven forbidden absent, and zero unauthorized mutations. Negative `wp_get_ability()` probes emitted expected missing-Ability notices. |
| timestamp not independently captured | Clean AP-008 matrix after F7 correction and first dependency regression | repository/wp-env | AP-008 exit 0; AP-004 exit 1 | AP-008 passed cleanly with the same counts and exact metadata. AP-004 collided when its historical synthetic `agentpress/get-context` fixture attempted to replace the now-production Ability. |
| timestamp not independently captured | Second AP-004 regression after removing only production `get-context` | repository/wp-env | exit 1 | Duplicate registration cleared, but discovery still returned the other 14 production catalog entries, violating AP-004's isolated one-tool fixture. |
| timestamp not independently captured | Final AP-004/AP-006/AP-007 dependency regressions | repository/wp-env | exit 0 | AP-004: one valid execution and 14 forbidden controls; AP-006: 13 invalid input classes, four invalid outputs, 17 errors, 4,096-byte detail bound; AP-007: four roles, logged out, mutation, 16 envelope operations, 15 abilities, seven forbidden guesses, four object controls. |
| timestamp not independently captured | Final unit, standards, browser, provenance, and audit gates | repository/wp-env | exit 0 | 44 tests/532 assertions; PHPCS 30/30; browser 14/14; provenance 40 ZIP entries with pinned GPL source and no upstream runtime; npm audit found zero vulnerabilities. |
| timestamp not independently captured | Two consecutive `npm run build:zip` runs and SHA-256 checks | repository | exit 0 | Both release builds produced `D91EC6104CCC57CB6C5F4F781C2DAA8A696456238A9F199F0B9CF63727CAD094`. |
| 2026-08-31T17:22:59+07:00 | Stage exact AP-008 manifest, run `git diff --cached --check`, and commit | repository | exit 0 | 12-file implementation/evidence package committed as `6c037ba22204351b506fad0fb979b6c2b1d51250`; worktree clean immediately afterward. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-006 schemas/errors and AP-007 policy/discovery are merged, and all latest hosted dependency gates passed. | main `dd82e82`; EXP-012; EXP-013 | AP-008's declared dependencies are satisfied. |
| O2 | `OBSERVED` | The preflight worktree is clean and synchronized with `origin/main`. | preflight status/log | AP-008 can start on an isolated branch without unrelated work. |
| O3 | `SOURCE_VERIFIED` | Core's Ability annotations and WebMCP annotations use different keys, but pinned WordPress 6.9 preserves additional boolean annotation keys while merging its defaults. | S4; implementation specification sections 5–6 | One metadata object can retain core semantics and fixed WebMCP hints; the bridge must emit only WebMCP keys. |
| O4 | `OBSERVED` | Existing bridge discovery calls every Ability permission callback with no input, which makes exact object/input policies unsuitable for coarse discovery. | `WebMCPRoutes::default_definitions()`; AP-007 `DiscoveryPolicy` | AP-008 must wire bridge discovery to AP-007's coarse filter and reserve exact permission callbacks for execution input. |
| O5 | `OBSERVED` | The clean real WordPress matrix retrieved exactly 15 catalog-identical runtime objects, with `show_in_rest=false`, callable policy/service boundaries, and exact metadata. | AP-008 harness exit 0 | The tested registry contract matches the fixed catalog. |
| O6 | `OBSERVED` | Native REST listed zero AgentPress Abilities and both correct read/write run routes returned 404 `rest_ability_not_found`; post count remained unchanged. | AP-008 harness exit 0 | The tested generic REST surface is not a second AgentPress execution path. |
| O7 | `OBSERVED` | Bridge discovery returned 15 tools for Administrator, eight exactly AP-007-discoverable tools for Subscriber, and zero logged-out tools; seven R3 names remained absent. | AP-008 harness exit 0 | The tested bridge surface is fixed-map and current-capability filtered. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | `npm --version` should inspect the configured roaming npm CLI without warnings. | PowerShell access to `C:\Users\craig\AppData\Roaming\npm\node_modules\npm\bin\npm-cli.js` was denied; the accessible npm reported 10.9.8. | environment/tool-path warning | Preserved before mutation; use repository-pinned tools and capture container/runtime versions independently. |
| F2 | The first AP-008 implementation should pass the PHP coding standard. | PHPCS reported 108 errors and 11 warnings: compressed associative schemas, missing helper short descriptions/throw tag, and alignment in the catalog, registrar, and bridge. | implementation formatting/documentation defect | Recorded before correction; run PHPCBF for the 69 marked findings, add explicit helper documentation, and rerun. |
| F3 | The scoped formatter command should forward PHPCBF options through Composer. | `composer exec phpcbf -- --standard=...` was normalized by wp-env/Composer so `--standard` reached Composer and failed as an unknown option. | command invocation defect | Recorded; invoke the repository binary directly inside the container instead. |
| F4 | The runtime `WP_Ability::get_input_schema()` value should be strictly identical to the catalog PHP value. | The first live matrix registered all 15 and then stopped on strict identity; inspection showed separate but semantically identical empty `stdClass` instances. | harness identity defect | Resolved by comparing canonical `wp_json_encode()` values, which preserves exact schema semantics without requiring object identity. |
| F5 | Direct native REST access to a REST-hidden AgentPress Ability should return HTTP 404 with `rest_ability_not_found`. | The second live matrix used a path without the controller's `/abilities/` collection segment and received `rest_no_route`; the correct path returned the expected 404 code. | harness route defect | Resolved by using `/wp-abilities/v1/abilities/{namespace}/{ability}/run` for both read and write controls. |
| F6 | The inline WP-CLI diagnostic should preserve PHP variables. | PowerShell expanded `$route` and `$response`, yielding a PHP parse error. | command quoting defect | Recorded; use escaped variables in a bounded read-only diagnostic. |
| F7 | A passing forbidden-absence control should remain free of WordPress incorrect-usage notices. | Calling `wp_get_ability()` for seven deliberately absent names emitted notices even though all absence assertions passed. | harness API-selection defect | Recorded before correction; use public `wp_has_ability()` for negative existence checks and rerun. |
| F8 | The AP-004 transport regression should remain isolated after AP-008 registers production Abilities. | Its synthetic `agentpress/get-context` registration collided with the production object, emitted a duplicate notice, and invalidated the one-tool fixture expectation. | historical fixture isolation defect | Recorded before correction; explicitly unregister the production object within the AP-004 process before installing its synthetic fixture, then rerun all dependency matrices. |
| F9 | Removing the one colliding production Ability should restore AP-004's one-tool discovery fixture. | The remaining 14 registered production Abilities were also discoverable for the Administrator, so the count remained broader than the synthetic fixture. | incomplete fixture isolation correction | Recorded; unregister every fixed AgentPress Ability inside the isolated AP-004 process before installing its two synthetic controls. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Keep AP-008 limited to registry/catalog wiring and synthetic callback controls; AP-011+ retain service behavior ownership. | build-checklist dependency boundary | Registry callbacks may use bounded placeholders that fail safely until services land. | A required registration acceptance test cannot be expressed without a service mutation. |
| D2 | `DECIDED` | Store valid core annotation keys and the two fixed WebMCP hint keys in `meta.annotations`; expose only `readOnlyHint` and `untrustedContentHint` through the bridge. | S4; implementation specification sections 5–6 | Metadata contains transport hints, but the bridge prevents core-only keys from leaking into WebMCP definitions. | WordPress begins rejecting unknown annotation keys. |
| D3 | `DECIDED` | Use AP-007 `DiscoveryPolicy` for coarse bridge discovery and AP-007 `ExecutionPolicy` inside each Ability permission callback with the actual input. | O4; S3; AP-007 contract | Discovery may advertise context-dependent tools that later reject a specific target, while execution remains exact. | WordPress adds a first-class coarse discovery callback. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Exactly 15 fixed Abilities register at the specified lifecycle | Real WordPress registry/category enumeration | `SUPPORTED` | 15 runtime objects in fixed-map order; category `agentpress` |
| Every category/callback/schema/annotation/mapping matches | Catalog-to-runtime canonical comparisons plus permission/fail-closed callback probes and 532 unit assertions | `SUPPORTED` | exact labels/descriptions/category/input/output/meta; fixed WebMCP names |
| Native Abilities REST neither lists nor runs AgentPress | Authenticated collection plus correct read/write `/run` routes and post-count control | `SUPPORTED` | zero listed; two 404 `rest_ability_not_found`; zero mutation |
| Bridge exposes only current-user AP-007-filtered mappings | Administrator, Subscriber, and logged-out `default_definitions()` controls | `SUPPORTED` | 15/8/0 tools; Subscriber equals AP-007 discovery exactly |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-31-exp-014-ability-registry.md` | evidence | committed | `EXP-014`; `6c037ba` | Opened before AP-008 source research or product-code mutation. |
| `agentpress/includes/Abilities/AbilityCatalog.php` | source | committed | fixed 15-entry catalog; `6c037ba` | Closed input/output schemas, labels, descriptions, core annotations, WebMCP hints, and REST-off metadata. |
| `agentpress/includes/Abilities/AbilityRegistrar.php` | source | committed | lifecycle registrar; `6c037ba` | Category/Ability hooks, exact policy callbacks, and fail-closed AP-011+ dispatcher boundary. |
| `agentpress/tests/phpunit/unit/AbilityCatalogTest.php` | executable evidence | committed | catalog unit controls; `6c037ba` | Exact 15-map, closed schemas, REST-off metadata, and annotations. |
| `agentpress/tests/integration/ap008-ability-registry.php` | executable evidence | committed | real WordPress matrix; `6c037ba` | Runtime objects, callbacks, native REST, bridge roles, R3 absence, and zero-mutation controls. |
| `dist/agentpress.zip` | generated release candidate | ignored/uncommitted | `D91EC6104CCC57CB6C5F4F781C2DAA8A696456238A9F199F0B9CF63727CAD094` | Two consecutive deterministic builds; not a published release artifact. |

## Result

`SUPPORTED`

The local evidence supports the hypothesis: exactly 15 fixed AgentPress Abilities register with catalog-identical schemas and metadata; the native WordPress Abilities REST surface neither lists nor runs them; bridge discovery follows the fixed map and AP-007 current-user policy; default AP-011+ execution fails closed; and the tested negative paths caused zero post mutation.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: hosted PR/CI execution, real browser/WebMCP discovery using the production catalog, ChatGPT, deployment, and AP-011+ service behavior.
- The browser suite is a transport regression, not live browser acceptance of the newly registered PHP catalog.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-008 work;
- pre-existing work distinguished by: merged AP-007/main baseline;
- third-party material/license/pin: pending source inspection; no new third-party runtime planned;
- commit/PR evidence: issue #15 and implementation commit `6c037ba22204351b506fad0fb979b6c2b1d51250`; push and PR pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-015 / AP-009 sanitized audit logging;
- next falsifiable question: can every AgentPress attempt emit a bounded, redacted audit record without secrets or raw private payloads?;
- required prerequisites: AP-008 merged with green registry/REST/discovery evidence.

## End state

```text
git status --short --branch: AP-008-only catalog, registrar, plugin/bridge wiring, AP-004 fixture isolation, tests, package manifest, checklist, README, index, and EXP-014 changes on ap-008-ability-registry
tests/checks: AP-008 WordPress matrix pass; AP-004/AP-006/AP-007 regressions pass; unit 44/532; PHPCS 30/30; browser 14/14; provenance 40 entries; npm audit 0; deterministic ZIP SHA-256 D91EC6104CCC57CB6C5F4F781C2DAA8A696456238A9F199F0B9CF63727CAD094
committed: yes, implementation/evidence commit 6c037ba22204351b506fad0fb979b6c2b1d51250; this append-only commit-reference update pending
pushed: no
deployed: no
```
