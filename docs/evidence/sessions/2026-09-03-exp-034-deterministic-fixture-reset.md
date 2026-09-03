# EXP-034 — Deterministic challenge fixture and reset

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-034` |
| Related task | `AP-027` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-03T10:41:00+07:00` |
| Started UTC | `2026-09-03T03:41:00Z` |
| Ended local | `2026-09-03T10:55:00+07:00` |
| Ended UTC | `2026-09-03T03:55:00Z` |
| Agent/operator | Worker C (Verification Agent) |
| Branch | `ap-027-deterministic-fixture` |
| Baseline commit | `31542848c0a87677d24268eec2e646279f67a2fa` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows 11; Node.js 22.x; WordPress 6.9/PHP 8.0.30 wp-env |

## Question

Can a repeatable reset command establish a credible small-business WordPress fixture with fixed pages, posts, categories, classic `primary` navigation, and Administrator/Author accounts such that multiple resets produce identical structure/IDs, satisfy all canonical v0.1 preconditions, and purge stale AgentPress state without committing real credentials?

## Hypothesis

Using WordPress core APIs executed via WP-CLI / `wp eval-file`, a deterministic reset script can purge prior test artifacts, configure site metadata, ensure the `primary` navigation location is registered with Home, About, Blog, and Contact items, seed categories and posts, create canonical Administrator and Author accounts, and purge AgentPress coordinator tables, producing an identical machine-readable fixture manifest across consecutive runs.

## Falsification condition

The hypothesis is falsified if consecutive reset executions yield conflicting structures or state hashes; `ClassicMenuAdapter` returns `AP_UNSUPPORTED_NAVIGATION` or missing location; required pages (`Home`, `About`, `Blog`, `Contact`), posts, categories, or accounts are absent or have incorrect capabilities; stale AgentPress records survive reset; or real credentials are hardcoded into committed files.

## Controls

- fixed commit/build: `31542848c0a87677d24268eec2e646279f67a2fa` on branch `ap-027-deterministic-fixture`.
- fixed fixture/data: Small business site ("Acme Web & Digital Studio"), 4 standard pages (`Home`, `About`, `Blog`, `Contact`), 2 posts, 3 standard categories (`News`, `Announcements`, `Case Studies`).
- fixed navigation: classic menu assigned to registered `primary` theme location with exact order `Home`, `About`, `Blog`, `Contact`.
- fixed identities: `agentpress_admin` (Administrator) and `agentpress_author` (Author).
- fixed policy: clean AgentPress tables (`agentpress_changes`, `agentpress_change_sets`, `agentpress_audit_events`).
- explicit scope exclusions: Navigation staging/approval execution (AP-022/AP-023), block Navigation blocks, external plugins.

## Variables

- **Independent:** Reset script execution count, prior dirty database state.
- **Dependent:** Post/page/term/user IDs, menu item structure, `ClassicMenuAdapter` state hash, table row counts, machine-readable fixture map.

## Preflight

```text
timestamp: 2026-09-03T10:41:00+07:00 / 2026-09-03T03:41:00Z
working directory: C:/Users/craig/01_Projects/WP-Agent-Admin/.worktrees/ap-027-deterministic-fixture
git status --short --branch: ## ap-027-deterministic-fixture...origin/main
git log -3 --oneline --decorate: 3154284 Merge pull request #37 from MrBigleg/ap-016-update-content
baseline SHA: 31542848c0a87677d24268eec2e646279f67a2fa
unrelated existing changes: none (isolated worktree)
AP task: AP-027
```

## Method

1. Inspect required canonical fixture state from `docs/IMPLEMENTATION_SPEC.md` §10 & §14.4 and `docs/BUILD_CHECKLIST.md` AP-027.
2. Implement `scripts/reset-fixture.php` to deterministically configure options, users, terms, posts, pages, classic menu, and clean AgentPress tables.
3. Add `npm run fixture:reset` to `package.json` with cross-platform runner `scripts/reset-fixture.mjs`.
4. Create an integration test `agentpress/tests/integration/ap027-fixture-reset.php` to verify determinism and preconditions.
5. Execute multiple reset trials to test idempotency and repeatability.
6. Verify output schema and `ClassicMenuAdapter` compatibility.
7. Record all observations, failures, and resolutions.

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | `docs/IMPLEMENTATION_SPEC.md` §10, §14.4 | 2026-09-03 | Canonical fixture requirements: classic theme/menu `primary` location, Home/About/Blog/Contact, Administrator/Author, 5-run reliability baseline | Normative specification |
| S2 | `SOURCE_VERIFIED` | `docs/BUILD_CHECKLIST.md` AP-027 | 2026-09-03 | AP-027 deliverable and acceptance test criteria | Task contract |
| S3 | `OBSERVED` | `agentpress/tests/integration/ap021-get-navigation.php` | 2026-09-03 | Classic menu setup and `ClassicMenuAdapter` snapshot verification pattern | Existing code |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-03T10:41:00+07:00 | Create worktree and initialize EXP-034 | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Worktree isolated; preflight recorded |
| 2026-09-03T10:42:11+07:00 | Verify active theme and install Twenty Twenty-One | wp-env cli | exit 0 | `twentytwentyone` active; registers `primary` location natively |
| 2026-09-03T10:44:50+07:00 | Verify PHP stdin piping to `wp eval-file -` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Confirmed reliable cross-platform execution via stdin |
| 2026-09-03T10:47:56+07:00 | Run first trial of `npm run fixture:reset` | `.worktrees/ap-027-deterministic-fixture` | exit 1 | C1: `ClassicMenuAdapter` snapshot returned `AP_PERMISSION_DENIED` (no user set in CLI) |
| 2026-09-03T10:49:05+07:00 | Run corrected trial 1 of `npm run fixture:reset` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Reset succeeded; manifest written |
| 2026-09-03T10:49:25+07:00 | Run idempotency trial 2 of `npm run fixture:reset` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | C2: Hash changed because menu was deleted and recreated with new auto-increment IDs |
| 2026-09-03T10:50:05+07:00 | Run stable reconciliation trial 1 of `npm run fixture:reset` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Menu items stably mapped to page IDs; hash `19ca97b4...` |
| 2026-09-03T10:50:22+07:00 | Run stable reconciliation trial 2 of `npm run fixture:reset` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | 100% exact match: Menu 38, items 1216/1217/1218/1219, hash `19ca97b4...` |
| 2026-09-03T10:51:34+07:00 | Run `ap027-fixture-reset.php` acceptance test | `.worktrees/ap-027-deterministic-fixture` | exit 0 | All preconditions, user capabilities, menu structure, and clean tables pass |
| 2026-09-03T10:52:18+07:00 | Run `composer lint` (PHPCS) | wp-env cli | exit 0 | 48/48 PHP files passed with 0 errors |
| 2026-09-03T10:53:01+07:00 | Run `composer test:unit` | wp-env cli | exit 0 | 68 tests, 593 assertions passed |
| 2026-09-03T10:53:09+07:00 | Run `npm run test:browser` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | 18/18 browser tests passed |
| 2026-09-03T10:53:18+07:00 | Run `npm run test:third-party` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | Licenses/pins verified |
| 2026-09-03T10:53:31+07:00 | Run `npm run build:zip` | `.worktrees/ap-027-deterministic-fixture` | exit 0 | ZIP generation clean |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | Twenty Twenty-One is active and registers the native classic menu location `primary`. | `wp theme list`, `wp eval "var_dump(get_registered_nav_menus());"` | Supports canonical classic fixture requirement |
| O2 | `OBSERVED` | `npm run fixture:reset` sets site title "Acme Web & Digital Studio", description, front page to Home, and posts page to Blog. | `canonical-fixture.json` | Supports credible small-business fixture |
| O3 | `OBSERVED` | Canonical accounts `agentpress_admin` (Administrator) and `agentpress_author` (Author) are created with verified capabilities. Author cannot edit pages or manage options. | `ap027-fixture-reset.php` assertions | Supports dual-account role requirements |
| O4 | `OBSERVED` | Canonical categories `News`, `Announcements`, `Case Studies` and canonical pages `Home`, `About`, `Blog`, `Contact` are created in published status. `Services` page is absent as required for canonical run. | `ap027-fixture-reset.php` assertions | Supports canonical workflow preconditions |
| O5 | `OBSERVED` | Reconciling menu items preserves existing menu and item IDs across consecutive resets, maintaining an identical state hash (`19ca97b4d3106b6799cb5563cd137c7aef07cea2abaf0065c7a04d43332a75c7`). | Trials 1 & 2 output | Supports deterministic repeatability requirement |
| O6 | `OBSERVED` | AgentPress coordinator database tables (`agentpress_changes`, `agentpress_change_sets`, `agentpress_audit_events`) are purged to 0 rows. | Database query check | Supports clean slate requirement |
| O7 | `OBSERVED` | Machine-readable manifest `docs/evidence/fixtures/canonical-fixture.json` is generated with schema `agentpress-canonical-fixture-v1` and complete ID mappings. | File content | Supports machine-readable fixture deliverable |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | `ClassicMenuAdapter::snapshot()` should succeed during reset verification. | Returned `AP_PERMISSION_DENIED`. | Test execution environment context | WP-CLI runs with current user 0; `ClassicMenuAdapter` checks `current_user_can('read_post')`. Resolved by setting current user to `agentpress_admin` before calling snapshot. |
| C2 | Consecutive reset runs should produce identical state hash. | Initial trial deleted and recreated menu, changing auto-increment post IDs and altering hash. | State stability defect | Reconcile existing menu items by matching target page IDs, preserving stable IDs and deterministic hash across consecutive resets. |
| C3 | Integration test assertion on `blogname` should match string. | Failed because WordPress stores/returns `&` as HTML entity `&amp;`. | WordPress core behavior | Used `html_entity_decode()` in test assertion. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Standardize on Twenty Twenty-One classic theme for the challenge fixture. | O1, S1 | Requires theme to be active; supported by core WordPress | If block theme classic menu support is added in future |
| D2 | `DECIDED` | Reconcile existing menu items rather than recreate from scratch. | O5, C2 | Preserves stable item IDs and state hash across multiple resets | If menu structure needs radical re-ordering |
| D3 | `DECIDED` | Use Node runner `scripts/reset-fixture.mjs` piping PHP into `wp eval-file -`. | O2, O7 | Allows cross-platform `npm run fixture:reset` without shell-specific piping issues | None |

## Artifacts and outcomes

- `scripts/reset-fixture.php`: Core PHP reset script configuring site, accounts, pages, posts, categories, classic menu, and purging AgentPress tables.
- `scripts/reset-fixture.mjs`: Cross-platform Node runner executing reset and generating manifest.
- `package.json`: Added `fixture:reset` script.
- `docs/evidence/fixtures/canonical-fixture.json`: Machine-readable fixture manifest.
- `agentpress/tests/integration/ap027-fixture-reset.php`: Integration acceptance test verifying all preconditions and determinism.
- `docs/evidence/sessions/2026-09-03-exp-034-deterministic-fixture-reset.md`: This experiment record.
- Verification: Acceptance test `ap027-fixture-reset.php` exit 0; `composer lint` 48/48 exit 0; unit tests 68/68 exit 0; browser tests 18/18 exit 0; `test:third-party` exit 0; `build:zip` exit 0.
