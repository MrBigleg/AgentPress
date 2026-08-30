# EXP-010 — AP-004 private same-origin WebMCP REST transport

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-010` |
| Related task | `AP-004`; GitHub issue #7 |
| Status | `COMPLETED` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T16:59:50+07:00` |
| Started UTC | `2026-08-30T09:59:50Z` |
| Ended local | `2026-08-31T05:10:28+07:00` |
| Ended UTC | `2026-08-30T22:10:28Z` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-004-private-rest-transport` |
| Baseline commit | `65bf03f09b942eb15e0a5d3848fc882fe763376f` |
| Ending commit | `1840903fe03e9994ed4ba8cb37b34a7946e6a5df` (implementation); evidence closeout follows |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 10.9.8; wp-env WordPress 6.9/PHP 8.0; official WordPress 6.9.7 core source |

## Question

Can AgentPress expose private tool discovery and execution plus one nonce-refresh path such that only a same-origin, cookie-authenticated WordPress user presenting a valid `wp_rest` nonce can reach the fixed AgentPress allowlist, while missing/wrong nonce, logged-out, cross-origin, oversized, malformed, unknown, or third-party requests produce zero Ability execution and private no-store responses?

## Hypothesis

WordPress REST cookie authentication plus explicit nonce validation, a hard-coded 15-Ability allowlist, pre-dispatch origin/size/rate controls, and an injectable Ability resolver/executor can enforce the boundary. The AP-003 client can compose a single nonce refresh/retry without persistent tool caching.

## Falsification condition

The hypothesis is falsified if any forbidden request increments the synthetic execution counter or mutates the target fixture, if discovery works without cookie identity plus a valid `wp_rest` nonce, if an arbitrary registered Ability can execute, if cross-origin or oversized input reaches resolution/execution, if responses omit `private, no-store`/`Vary: Cookie`, if client definitions persist in shared storage, or if nonce refresh causes more than one retry.

## Controls

- fixed commit/build: AgentPress baseline `65bf03f09b942eb15e0a5d3848fc882fe763376f`;
- fixed fixture/data: synthetic allowlisted and third-party Ability fixtures, execution counter, Administrator/logged-out identities, bounded JSON requests;
- fixed identity/capabilities: cookie-authenticated Administrator versus user ID 0;
- fixed policy/configuration: exact 15-entry registrar allowlist, 100 KB discovery/default body cap, 300 KB bounded content execute cap only where specified, private no-store responses, one nonce retry;
- fixed client/environment: WordPress 6.9/PHP 8.0 integration environment plus Node client tests;
- explicit scope exclusions: real AP-008 Ability implementations, AP-006 final schemas/errors, change storage, approval/audit, wp-admin presentation, live Chrome, and ChatGPT acceptance.

## Variables

- **Independent:** identity, nonce, Origin header, body size, Ability name, request shape, rate state, and first-response nonce failure.
- **Dependent:** route result/status/headers, resolver and execute counters, target fixture state, client retry count, and storage access.

## Preflight

```text
timestamp local: 2026-08-30T16:59:50+07:00
timestamp UTC: 2026-08-30T09:59:50Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
65bf03f (HEAD -> main, origin/main, origin/HEAD) Merge pull request #6 from MrBigleg/ap-003-webmcp-adapter
704bc1a (origin/ap-003-webmcp-adapter) docs: close AP-003 evidence
9897f37 feat: add current WebMCP browser adapter
baseline SHA: 65bf03f09b942eb15e0a5d3848fc882fe763376f
current branch after preflight: ap-004-private-rest-transport
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-004; issue #7; PR pending
environment: Node.js 22.23.2; npm 10.9.8; WordPress/PHP runtime pending
```

## Method

1. Create the AP-004 issue with `difficulty: M` and v0.1 milestone after opening this record.
2. Verify current WordPress REST cookie-authentication, nonce, route permission, request, response-header, and AJAX behavior from official documentation/core source.
3. Implement fixed allowlist, pre-execution request guard, rate-limit interface, private REST routes, and signed-in nonce refresh without registering AP-008 placeholder tools.
4. Extend the AP-003 fetch executor with one nonce-specific refresh/retry and no shared/local storage.
5. Add unit and WordPress integration controls for valid discovery/execution and every forbidden request, asserting both the error and zero resolver/execution/target mutation.
6. Add package/CI coverage, run local checks, and preserve failures in order.
7. Commit, push, open a draft PR, and merge only after green checks and runtime acceptance evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work performed and checked in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress REST cookie authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/) | 2026-08-30 | Manual same-site requests use `wp_rest` nonces in `X-WP-Nonce`; absent nonce sets current user to 0; cookie auth also requires appropriate capability. | Official handbook. |
| S2 | `SOURCE_VERIFIED` | [WordPress 6.9.7 `rest_cookie_check_errors()`](https://github.com/WordPress/wordpress-develop/blob/6.9.7/src/wp-includes/rest-api.php) | 2026-08-30 | Core verifies `wp_rest`, returns 403 for invalid nonce, resets identity when missing, and emits a refreshed nonce header after success. | Official tag `6.9.7`, commit `c95518d...`. |
| S3 | `SOURCE_VERIFIED` | [WordPress 6.9.7 REST server](https://github.com/WordPress/wordpress-develop/blob/6.9.7/src/wp-includes/rest-api/class-wp-rest-server.php) and [`register_rest_route()` reference](https://developer.wordpress.org/reference/functions/register_rest_route/) | 2026-08-30 | Routes register on `rest_api_init`, permission callbacks must explicitly deny with false/null/`WP_Error`, and `rest_post_dispatch` receives ensured responses before headers send. | Supports centralized private headers including error responses. |
| S4 | `SOURCE_VERIFIED` | [WordPress 6.9.7 Abilities API](https://github.com/WordPress/wordpress-develop/blob/6.9.7/src/wp-includes/abilities-api.php) and [`WP_Ability`](https://github.com/WordPress/wordpress-develop/blob/6.9.7/src/wp-includes/abilities-api/class-wp-ability.php) | 2026-08-30 | Exact lookup is `wp_get_ability`; preflight `check_permissions(input)` is side-effect free; `execute(input)` validates and checks permission again before callback. | Synthetic fixture only; no production Ability registered by AP-004. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-30T16:59:50+07:00 | Inspect merged main status/history, AP-004 specification/checklist, runtime shell, and test bootstrap | repository | mixed | Clean main at `65bf03f`; AP-003 PR #6 merged and issue #5 closed; transport and validation-order contracts identified; no route implementation exists. |
| 2026-08-30T16:59:50+07:00 | `git switch -c ap-004-private-rest-transport` | repository | exit 0 | Dependency-ordered AP-004 branch created from merged AP-003 main. |
| timestamp not independently captured | Create milestone-scoped AP-004 issue | GitHub | success | Issue [#7](https://github.com/MrBigleg/AgentPress/issues/7) created with existing `difficulty: M` label. |
| timestamp not independently captured | Query plain upstream tag `6.9` | WordPress GitHub API | HTTP 404 | No plain tag exists; preserved failure and enumerated matching refs. Selected latest patch tag `6.9.7` rather than assuming a tag name. |
| timestamp not independently captured | Inspect official REST authentication docs and WordPress 6.9.7 REST/Ability source | official docs/GitHub | success | Cookie/nonce reset/error behavior, permission dispatch, response filter, request body/JSON/header accessors, and Ability lookup/permission/execute methods verified. |
| 2026-08-30T17:19:34+07:00 | First AP-004 WordPress runtime harness | wp-env WordPress 6.9/PHP 8.0 | exit 1 | Seven forbidden controls passed with zero resolver/execution/target side effects. Discovery emitted missing-Ability notices and malformed JSON was rejected by core as `rest_invalid_json` before the route callback, while the harness expected `AP_SCHEMA_INVALID`. |
| 2026-08-30T17:21:00+07:00 | Second AP-004 WordPress runtime harness | wp-env WordPress 6.9/PHP 8.0 | exit 1 | Ten forbidden controls passed with zero side effects and missing-Ability notices were eliminated. The absolute-size fixture was invalid JSON, so core rejected it as `rest_invalid_json` before the harness could observe the size code. |
| timestamp not independently captured | Third AP-004 WordPress runtime harness | wp-env WordPress 6.9/PHP 8.0 | exit 0 | Valid discovery/execution succeeded once; all 11 forbidden controls preserved zero sensitive side effects; private headers and signed-in nonce refresh passed. |
| timestamp not independently captured | `npm run test:unit` | wp-env WordPress 6.9/PHP 8.0 | exit 0 | PHPUnit: 7 tests, 12 assertions. |
| timestamp not independently captured | First `npm run lint:php` | wp-env WordPress 6.9/PHP 8.0 | exit 2 | PHPCS found one alignment warning, unslashed/unsanitized server inputs, and a dynamic translation-string violation. |
| timestamp not independently captured | Fourth AP-004 WordPress runtime harness | wp-env WordPress 6.9/PHP 8.0 | exit 0 | All runtime/security controls remained green after server-input sanitization. |
| timestamp not independently captured | Second `npm run lint:php` | wp-env WordPress 6.9/PHP 8.0 | exit 2 | Security errors were cleared; one auto-fixable assignment alignment warning remained because the manual spacing was one column short. |
| timestamp not independently captured | Third `npm run lint:php` | wp-env WordPress 6.9/PHP 8.0 | exit 0 | All 10 production PHP files passed PHPCS. |
| timestamp not independently captured | `npm run test:browser`; Node syntax; `npm audit --audit-level=high` | repository | exit 0 | Browser suite passed 14/14; adapter syntax passed; npm reported 0 vulnerabilities. |
| timestamp not independently captured | First `npm run test:third-party` | repository, sandboxed | exit 1 | Read-only sandbox blocked replacement of `dist/agentpress.zip` with `EPERM`; no source/provenance assertion failed. Escalated rerun pending. |
| timestamp not independently captured | Expanded rate-limit runtime harness | wp-env WordPress 6.9/PHP 8.0 | exit 0 | Twelve controls passed, including the 60-per-user discovery ceiling and `Retry-After: 60`; exact total/per-Ability controls were added for the next rerun. |
| timestamp not independently captured | PHPCS after rate-limit implementation | wp-env WordPress 6.9/PHP 8.0 | exit 1 | One missing `@param` tag in `RequestRateLimiter::allow()`; production behavior was unaffected. |
| 2026-08-31 local / 2026-08-30 UTC | Final local verification batch | repository and wp-env | exit 0 | PHPCS 10/10 files; PHPUnit 7 tests/12 assertions; browser 14/14; Node syntax; npm audit 0 vulnerabilities; provenance/package scan 20 entries with no upstream runtime code. |
| 2026-08-31 local / 2026-08-30 UTC | Final AP-004 WordPress runtime matrix | wp-env WordPress 6.9/PHP 8.0 | exit 0 | One valid execution and 14 fail-closed controls passed, including discovery, total, and per-Ability ceilings with `Retry-After: 60`; zero forbidden resolver/execution/target mutation. |
| 2026-08-31 local / 2026-08-30 UTC | Two pre-review release builds and SHA-256 | `dist/agentpress.zip` | exit 0 | Both builds produced `E954A96A27F94E16D11387651BC85BBAF14217C84FA5AF52EA58CEB31C573E9C`. |
| 2026-08-31 local / 2026-08-30 UTC | `npm run env:stop` | repository | exit 0 | Local WordPress environment stopped after verification. |
| 2026-08-31 local / 2026-08-30 UTC | Post-review validation-order runtime, PHPCS, provenance, and two builds | repository and wp-env | exit 0 | The default 100 KB check was moved before per-Ability rate accounting; all 14 controls and 10/10 PHPCS files passed; the environment was stopped; both final 20-entry builds produced `31C718F1445FFCF1D4E897DCC868EE6E9AD4AC85591FBEEB808AC1EDEB0485BD`. |
| 2026-08-31T05:10:28+07:00 | GitHub Actions implementation gate | PR #8 at `1840903` | exit 0 | Run [33338390478](https://github.com/MrBigleg/AgentPress/actions/runs/33338390478), job `99329349910`, passed unit, standards, provenance, audit, and package checks in 21 seconds. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-003 merged as `65bf03f`; AP-004's declared dependencies are satisfied. | PRs #2/#6, local main | Allows AP-004 to begin. |
| O2 | `OBSERVED` | At baseline, the plugin had no REST routes, Ability resolver, rate limiter, nonce refresh action, or WordPress integration-test bootstrap. | baseline source/test listing | Establishes the security baseline. |
| O3 | `SOURCE_VERIFIED` | WordPress cookie authentication treats a missing REST nonce as user ID 0 and rejects a wrong nonce at 403; a valid nonce preserves identity and emits a refreshed nonce header. | S1, S2 | Supports requiring explicit identity plus header nonce on both AgentPress routes. |
| O4 | `SOURCE_VERIFIED` | `rest_post_dispatch` sees an ensured response after route callbacks and before headers are sent. | S3 | Supports adding `private, no-store` and `Vary: Cookie` to both success and route-error responses. |
| O5 | `SOURCE_VERIFIED` | WordPress Ability execution revalidates input and permission; preflight permission can prevent calling `execute`. | S4 | Supports explicit preflight followed by core execution without duplicating business logic. |
| O6 | `OBSERVED` | WordPress runtime accepted valid private discovery/execution once and rejected 14 forbidden controls before sensitive side effects; route errors carried private no-store headers and rate errors carried `Retry-After: 60`. | final runtime matrix | Supports the AP-004 server-side boundary under the synthetic fixture. |
| O7 | `OBSERVED` | The browser suite refreshed/retried nonce errors exactly once, never retried permission errors, used same-origin/no-store requests, and kept the nonce in page memory only. | 14/14 Node tests | Supports the client-side AP-004 boundary. |
| O8 | `OBSERVED` | Two consecutive package builds produced the same 20-entry ZIP SHA-256 and contained no upstream runtime source. | final package scan/builds | Supports deterministic packaging and the AP-002 attribution boundary after AP-004 additions. |
| O9 | `OBSERVED` | The published implementation commit passed the hosted unit, standards, provenance, audit, package, and built-adapter storage scans. | GitHub run `33338390478` | Supports reproducibility outside the local workstation. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | Malformed JSON reaches the route callback and returns `AP_SCHEMA_INVALID`; absent fixed-map Abilities resolve quietly | WordPress rejected malformed JSON earlier as `rest_invalid_json`; `wp_get_ability()` emitted notices for each absent fixed-map Ability | test expectation mismatch and implementation defect | `RESOLVED`: preserved core's earlier fail-closed error and added a registry existence check; subsequent runs were quiet and green. |
| F2 | The absolute-size control returns `AP_REQUEST_TOO_LARGE` | The fixture was a 307,201-byte string rather than valid JSON, so WordPress returned `rest_invalid_json` first | test fixture defect | `RESOLVED`: encoded valid JSON above 300 KB; subsequent runs returned `AP_REQUEST_TOO_LARGE` before resolution/execution. |
| F3 | Production PHP passes repository coding/security standards | PHPCS found one alignment warning, unslashed/unsanitized `REMOTE_ADDR`, `HTTP_ORIGIN`, and `HTTP_SEC_FETCH_SITE`, plus a dynamic translation literal | implementation defects | `RESOLVED`: aligned assignments, sanitized inputs, bounded the internal error text, and passed 10/10 production files. |
| F4 | The manual assignment alignment correction satisfies PHPCS | The assignment remained one space short | formatting defect | `RESOLVED`: added the reported space; subsequent PHPCS runs passed. |
| F5 | Provenance/package verification can rebuild the ZIP in the default sandbox | The read-only sandbox rejected unlinking the existing workspace ZIP with `EPERM` | environment limitation | `RESOLVED`: approved workspace-scoped rerun passed; final deterministic package hash is recorded above. |
| F6 | The rate-limit implementation passes PHPCS after behavior succeeds | `RequestRateLimiter::allow()` lacked the new `$default_limit` parameter documentation | documentation defect | `RESOLVED`: added the required tag; subsequent PHPCS runs passed. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | AP-004 uses synthetic registered Ability fixtures only in integration tests; it does not create production placeholder Abilities before AP-008. | checklist dependency boundary | Valid route mechanics can be tested without claiming the catalog exists. | WordPress integration cannot register a synthetic fixture independently. |
| D2 | `DECIDED` | Explicitly validate the `X-WP-Nonce` header as `wp_rest` inside the route guard even though external cookie-auth REST requests are also checked by core. | O3; cookie-only product boundary | Defense in depth and deterministic internal dispatch; intentionally excludes application-password access to these routes. | WordPress supplies a route-scoped cookie-auth assertion that remains active in internal dispatch. |
| D3 | `DECIDED` | Reject a present foreign `Origin` or `Sec-Fetch-Site: cross-site`; allow absent Origin because same-origin GET does not reliably send it and the unguessable nonce remains the CSRF control. | S1–S3; browser request behavior | Does not pretend Origin alone authenticates; nonce remains mandatory. | Target browser consistently supplies trustworthy origin metadata for every request. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Valid authenticated nonce discovery and execution work | WordPress synthetic-Ability runtime harness | `OBSERVED`, pass | one valid resolver/execution/target transition |
| Missing/wrong nonce and logged-out requests execute nothing | WordPress runtime controls | `OBSERVED`, pass | zero forbidden resolver/execution/target mutation |
| Unknown/third-party, cross-origin, malformed, and oversized requests execute nothing | WordPress runtime controls | `OBSERVED`, pass | all expected bounded errors; zero sensitive side effects |
| Responses are private no-store and vary by cookie | Success/error header assertions | `OBSERVED`, pass | `Cache-Control: private, no-store`; `Vary: Cookie` |
| Discovery, total, and per-Ability limits fail with retry guidance | Runtime limits reduced to one through the documented filter | `OBSERVED`, pass | `AP_RATE_LIMITED`; `Retry-After: 60`; zero sensitive side effects |
| Client refreshes nonce/retries once only and uses no shared storage | Node browser suite, production-source scan, and hosted built-ZIP scan | `OBSERVED`, pass | 14/14 tests; no local/session storage identifiers in source or built adapter |
| Published implementation passes repository gates | GitHub Actions on `1840903` | `OBSERVED`, pass | run `33338390478`, job `99329349910` |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-30-exp-010-private-rest-transport.md` | evidence | committed at `1840903` | `EXP-010` | Opened before WordPress research or product mutation. |
| `agentpress/tests/integration/ap004-rest-transport.php` | executable evidence | committed at `1840903` | AP-004 runtime matrix | Synthetic fixture; not included in release ZIP. |
| `dist/agentpress.zip` | generated release check | ignored/uncommitted | `31C718F1445FFCF1D4E897DCC868EE6E9AD4AC85591FBEEB808AC1EDEB0485BD` | Final post-review artifact; deterministic across two consecutive builds; 20 entries. |
| GitHub Actions run | hosted command evidence | completed | `33338390478` / job `99329349910` | Published implementation gate; conclusion `success`. |

## Result

`SUPPORTED`

`SOURCE_VERIFIED`: WordPress cookie authentication, REST dispatch, private response filtering, and Ability permission/execution behavior support the chosen transport boundary.

`OBSERVED`: the published AP-004 implementation allows one valid synthetic discovery/execution path and rejects 14 forbidden controls without unauthorized Ability resolution, execution, or target mutation. Local and GitHub-hosted gates passed.

This result establishes the private transport and retry contract only. It is not production Ability-catalog, real-browser, deployed-site, or ChatGPT acceptance evidence.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: real browser cookie transport, live Chrome, ChatGPT Site Tools, deployment, and AP-005+ behavior.
- The rate limiter uses WordPress transients and is not an atomic distributed counter; v0.1 evidence covers deterministic single-site enforcement, not high-concurrency load behavior.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-004 work;
- pre-existing work distinguished by: merged AP-003 adapter baseline;
- third-party material/license/pin: no new third-party material added; existing pinned attribution remained green;
- commit/PR evidence: implementation commit `1840903`, issue #7, draft PR #8, and successful run `33338390478`;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-011 / AP-005;
- next falsifiable question: can the three specified tables and typed repositories migrate idempotently and preserve bounded data under the v0.1 lifecycle rules?;
- required prerequisites: AP-004 merged with green security/runtime checks.

## End state

```text
git status --short --branch: clean at 1840903 before this evidence closeout
tests/checks: local runtime/security matrix, PHPCS, PHPUnit, browser, syntax, audit, provenance, and deterministic package build pass
committed: implementation 1840903; closeout pending
pushed: implementation pushed; closeout pending
deployed: no
```
