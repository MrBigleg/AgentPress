# EXP-035 — P0 authorization regression suite

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-035` |
| Related task | `AP-018` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-03T10:57:00+07:00` |
| Started UTC | `2026-09-03T03:57:00Z` |
| Ended local | `2026-09-03T11:10:00+07:00` |
| Ended UTC | `2026-09-03T04:10:00Z` |
| Agent/operator | Worker C (Verification Agent) |
| Branch | `ap-018-authorization-suite` |
| Baseline commit | `4102e9894e6bf7ca63eaeead34bb04c1f6dbe62c` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows 11; Node.js 22.x; WordPress 6.9/PHP 8.0.30 wp-env |

## Question

Can a parameterized authorization regression suite verify discovery and direct execution across Administrator, Editor, Author, Subscriber, logged-out, invalid nonce, expired session, and capability mutation sessions, proving both exact error codes and zero unauthorized mutations in WordPress and AgentPress tables?

## Hypothesis

Parameterized execution across the five fixture roles and negative security controls against all registered abilities will demonstrate that:
1. Discovery exposes only the exact capabilities possessed by the current user.
2. Direct execution of unauthorized abilities is rejected with strict error codes (`AP_NOT_AUTHENTICATED`, `AP_NONCE_INVALID`, `AP_PERMISSION_DENIED`, `AP_POLICY_BLOCKED`).
3. Every rejected attempt causes zero mutations in `wp_posts`, `wp_terms`, `wp_term_relationships`, `wp_options`, `agentpress_changes`, and `agentpress_change_sets`.
4. Native generic core Abilities REST endpoints fail to discover or execute `agentpress/*` abilities (`meta.show_in_rest = false`).
5. Authenticated denials are recorded with sanitized arguments (no secrets); unauthenticated or invalid-nonce attempts produce zero durable audit records.

## Falsification condition

The hypothesis is falsified if:
- An unauthorized caller successfully discovers or executes any forbidden ability;
- An unreadable draft, private page, or foreign proposal is returned to an unauthorized user;
- Any forbidden or failed attempt produces a mutation in WordPress posts, terms, options, menus, or AgentPress change tables;
- Native WordPress Abilities REST exposes or executes any AgentPress ability;
- Secrets (nonces, cookies, passwords) enter the audit log, or unauthenticated traffic creates audit rows;
- Capability mutation (e.g. revoking `publish_posts` or granting `edit_posts`) fails to dynamically update discovery and execution authorization.

## Controls

- fixed commit/build: `4102e9894e6bf7ca63eaeead34bb04c1f6dbe62c` on branch `ap-018-authorization-suite`.
- fixed fixture/data: Canonical fixture reset via `npm run fixture:reset` (Twenty Twenty-One, Home/About/Blog/Contact, dual accounts, standard categories).
- fixed policy: Safe Mode active, fixed 15-ability catalog, R0–R3 risk classifier.
- explicit scope exclusions: Staged approval execution (AP-023), block Navigation blocks, external plugins.

## Variables

- **Independent:** Authenticated identity (Administrator, Editor, Author, Subscriber, anonymous, mutated user), REST nonce validity, execution target (all 15 abilities, direct core Abilities REST).
- **Dependent:** Discovery tool lists, HTTP status code, `AP_*` error codes, WordPress target state (post, term, menu mutation count), AgentPress tables row counts, audit logging records.

## Preflight

```text
timestamp: 2026-09-03T10:57:00+07:00 / 2026-09-03T03:57:00Z
working directory: C:/Users/craig/01_Projects/WP-Agent-Admin/.worktrees/ap-027-deterministic-fixture
git status --short --branch: ## ap-018-authorization-suite
git log -3 --oneline --decorate: 4102e98 (HEAD -> ap-018-authorization-suite, ap-027-deterministic-fixture) feat(fixture): create deterministic challenge fixture and reset (AP-027, EXP-034)
baseline SHA: 4102e9894e6bf7ca63eaeead34bb04c1f6dbe62c
unrelated existing changes: none
AP task: AP-018
```

## Method

1. Inspect existing authorization tests (`ap004`, `ap007`, `ap008`, `ap011`, `ap013`, `ap015`, `ap016`, `ap017`, `ap021`).
2. Implement comprehensive test runner `agentpress/tests/integration/ap018-authorization-suite.php`.
3. Test Discovery Matrix across 5 roles: Administrator, Editor, Author, Subscriber, anonymous.
4. Test Direct Execution Matrix:
   - Anonymous attempts -> `AP_NOT_AUTHENTICATED` / 401, zero mutation, zero audit rows.
   - Invalid / missing nonce -> `AP_NONCE_INVALID` / 403, zero mutation, zero audit rows.
   - Cross-origin execution -> `AP_POLICY_BLOCKED` / 403, zero mutation.
   - Subscriber write attempts -> `AP_PERMISSION_DENIED`, zero mutation, sanitized audit row.
   - Author page write attempts -> `AP_PERMISSION_DENIED`, zero mutation, sanitized audit row.
   - Author attempts to edit foreign admin-owned draft -> `AP_PERMISSION_DENIED` or `AP_CONTENT_NOT_FOUND`.
   - Editor attempts classic navigation stage -> `AP_PERMISSION_DENIED`, zero menu mutation.
   - Author R1 apply vs R2 staged proposal -> AP draft applies directly; ordinary draft stages proposal without mutating `wp_posts`.
   - Core WordPress Abilities REST endpoint isolation -> confirms `meta.show_in_rest = false` rejects access.
   - Capability mutation: dynamically grant/revoke capability, assert instant discovery & execution update.
   - Audit log sanitization: verify no passwords, nonces, cookies, or authorization tokens leaked.
5. Record all observations, failures, decisions, and execution outcomes.

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | `docs/IMPLEMENTATION_SPEC.md` §7, §14.1, §14.2 | 2026-09-03 | Authorization model, Safe Mode, and integration testing requirements | Normative specification |
| S2 | `SOURCE_VERIFIED` | `docs/BUILD_CHECKLIST.md` AP-018 | 2026-09-03 | AP-018 deliverables and acceptance criteria | Task contract |
| S3 | `OBSERVED` | `agentpress/tests/integration/ap004-rest-transport.php` | 2026-09-03 | REST transport security checks | Test pattern |
| S4 | `OBSERVED` | `agentpress/tests/integration/ap007-safe-mode-discovery-policy.php` | 2026-09-03 | Discovery and execution policy matrix | Test pattern |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-03T10:57:00+07:00 | Create branch ap-018-authorization-suite and EXP-035 | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Branch created; preflight recorded |
| 2026-09-03T10:58:19+07:00 | Run first trial of `ap018-authorization-suite.php` | `.worktrees/ap-027-deterministic-fixture` | exit 1 | C1: Route path mismatch (`/agentpress/v1/execute` vs `/agentpress/v1/webmcp/execute`) |
| 2026-09-03T10:59:18+07:00 | Run second trial of `ap018-authorization-suite.php` | `.worktrees/ap-027-deterministic-fixture` | exit 1 | C2: Idempotency key `sub-k1` failed regex schema validation before permission check |
| 2026-09-03T11:00:31+07:00 | Run third trial of `ap018-authorization-suite.php` | `.worktrees/ap-027-deterministic-fixture` | exit 1 | C3: Dynamic capability mutation was masked by `$current_user` in-memory caching |
| 2026-09-03T11:01:11+07:00 | Run fourth trial of `ap018-authorization-suite.php` | `.worktrees/ap-027-deterministic-fixture` | exit 1 | C4: Column name in `agentpress_audit_events` is `arguments_sanitized` |
| 2026-09-03T11:02:29+07:00 | Run fifth trial with Author R1 vs R2 checks | `.worktrees/ap-027-deterministic-fixture` | exit 1 | C5: Post meta `_agentpress_created` was ignored because `AgentCreatedDraftLookup` checks durable `agentpress_changes` table |
| 2026-09-03T11:03:45+07:00 | Run full `ap018-authorization-suite.php` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | All security and zero-mutation checks passed green in 9.4s |
| 2026-09-03T11:04:06+07:00 | Run fixture reset verification | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Exact state hash `19ca97b4...` reproduced; tables clean |
| 2026-09-03T11:04:39+07:00 | Run `composer lint` (PHPCS) | wp-env cli | exit 0 | 48/48 PHP files passed with 0 errors |
| 2026-09-03T11:05:40+07:00 | Run `php -l` on test file | wp-env cli | exit 0 | Syntax check clean |
| 2026-09-03T11:05:57+07:00 | Run `composer test:unit` | wp-env cli | exit 0 | 68 tests, 593 assertions passed |
| 2026-09-03T11:06:10+07:00 | Run `npm run test:browser` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | 18/18 browser tests passed |
| 2026-09-03T11:06:17+07:00 | Run `npm run test:third-party` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Pin and licenses verified |
| 2026-09-03T11:06:27+07:00 | Run `npm run build:zip` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Distribution ZIP built cleanly |
| 2026-09-03T11:07:04+07:00 | Run AP-027 and AP-018 sequentially | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Both acceptance tests passed in 7.4s and 7.5s |
| 2026-09-03T11:07:25+07:00 | Final clean reset | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Exact state hash verified; 0 rows in all coordinator tables |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | Discovery Policy strictly bounds visible abilities per role: Administrator (15), Editor (13, no classic nav), Author (12, no page draft or classic nav), Subscriber (reads only, 0 write tools), Anonymous (0 tools). | `ap018-authorization-suite.php` Matrix 1 | Supports capability boundary hypothesis |
| O2 | `OBSERVED` | Direct execution by Anonymous returns HTTP 401 with `AP_NOT_AUTHENTICATED`, zero target mutations in WordPress posts/terms/menus, and zero audit rows created. | Matrix 2 Test A | Supports unauthenticated boundary hypothesis |
| O3 | `OBSERVED` | Direct execution with missing or invalid REST nonces returns HTTP 403 with `AP_NONCE_INVALID`, zero target mutations, and zero audit rows created (preventing unauthenticated write amplification). | Matrix 2 Tests B & C | Supports nonce protection hypothesis |
| O4 | `OBSERVED` | Cross-origin execution request returns HTTP 403 with `AP_POLICY_BLOCKED` and zero mutations. | Matrix 2 Test D | Supports CSRF / cross-site protection hypothesis |
| O5 | `OBSERVED` | Subscriber direct calls across all write abilities return HTTP 403 with `AP_PERMISSION_DENIED` and cause zero mutations in `wp_posts`, `wp_terms`, `wp_term_relationships`, `wp_options`, `agentpress_changes`, and `agentpress_change_sets`. | Matrix 3 Test E | Supports zero unauthorized mutation hypothesis |
| O6 | `OBSERVED` | Author page creation returns HTTP 403 with `AP_PERMISSION_DENIED` and produces zero page mutations in the database. | Matrix 3 Test F | Supports post-type capability boundary hypothesis |
| O7 | `OBSERVED` | Author attempt to edit foreign admin-owned draft returns HTTP 403/404 and produces zero target mutations. | Matrix 3 Test G | Supports object ownership boundary hypothesis |
| O8 | `OBSERVED` | Editor attempt to stage classic navigation returns HTTP 403 with `AP_PERMISSION_DENIED` and produces zero live menu mutations. | Matrix 3 Test H | Supports navigation capability boundary hypothesis |
| O9 | `OBSERVED` | R1 automatic vs R2 staged mutation: Author updating own AgentPress draft applies directly (R1); Author updating ordinary draft stages proposal with `PENDING_APPROVAL`, leaving live post unmodified (R2 zero staged mutation). | Matrix 3 Test I | Supports staged write safety hypothesis |
| O10 | `OBSERVED` | Native WordPress Abilities REST `/wp-abilities/v1/abilities` exposes zero `agentpress/*` abilities, and direct execution returns 404 `rest_ability_not_found`. | Matrix 4 | Supports core REST isolation hypothesis |
| O11 | `OBSERVED` | Dynamic capability mutation: granting `edit_posts`/`publish_posts` to subscriber immediately expands discovery; revoking immediately drops discovery and restores denial. | Matrix 5 | Supports dynamic capability reflection hypothesis |
| O12 | `OBSERVED` | Audit log sanitization: recorded audit arguments contain zero leaked secrets (passwords, nonces, cookies, auth tokens). | Matrix 6 | Supports audit sanitization hypothesis |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | REST execute endpoint path should match route. | Called `/agentpress/v1/execute` which returned 404. | Route specification | Used exact route `/agentpress/v1/webmcp/execute` as registered by `WebMCPRoutes`. |
| C2 | Permission check should reject subscriber requests. | Returned 400 `AP_SCHEMA_INVALID` because idempotency key was too short (< 8 chars). | Schema vs permission check ordering | Used 8+ character valid idempotency keys (`ap018-sub-key-01`) so schema validation passes and permission check is tested. |
| C3 | Dynamic capability mutation should take immediate effect. | In-memory `$current_user` cached old capabilities. | WordPress core user cache behavior | Added `wp_set_current_user( 0 )` before clearing cache and reloading user. |
| C4 | Audit query for arguments. | Query failed with unknown column `arguments_json`. | Table schema difference | Schema uses column name `arguments_sanitized`. Updated query. |
| C5 | Post meta `_agentpress_created` should grant R1 authority to author draft. | Returned R2 `PENDING_APPROVAL`. | Security architecture design | `AgentCreatedDraftLookup` deliberately queries durable `agentpress_changes` table for `APPLIED` records rather than trust mutable post meta. Created draft via `DraftCreationService`. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Parameterize tests across all 5 roles and all 15 catalog abilities. | O1, S1 | Test suite takes ~9s to run | None |
| D2 | `DECIDED` | Assert zero target mutation on every forbidden attempt via comprehensive database snapshot helper. | O2–O9 | Captures full posts, terms, options, changes table states | None |
| D3 | `DECIDED` | Assert that unauthenticated / invalid-nonce attempts produce zero durable audit entries. | O2, O3, S1 | Prevents unauthenticated denial-of-service write amplification | If audit requirements change |

## Artifacts and outcomes

- `agentpress/tests/integration/ap018-authorization-suite.php`: Parameterized P0 authorization regression test suite.
- `docs/evidence/sessions/2026-09-03-exp-035-p0-authorization-suite.md`: This experiment record.
- Verification: Acceptance tests `ap018-authorization-suite.php` and `ap027-fixture-reset.php` both passed (exit 0); `composer lint` 48/48 exit 0; unit tests 68/68 exit 0; browser tests 18/18 exit 0; `npm run test:third-party` exit 0; `npm run build:zip` exit 0.
