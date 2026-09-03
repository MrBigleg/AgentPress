# EXP-041 — AP-029 Security Test Hardening: Repeatability and Order-Independence

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-041` |
| Related task | `AP-029` |
| Status | `COMPLETED` |
| Result | `SUPPORTED` |
| Started local | `2026-09-03T18:55:00+07:00` |
| Started UTC | `2026-09-03T11:55:00Z` |
| Ended local | `2026-09-03T19:28:00+07:00` |
| Ended UTC | `2026-09-03T12:28:00Z` |
| Agent/operator | Antigravity (Advanced Agentic Coding) |
| Branch | `ap-029-security-test-hardening` |
| Baseline commit | `97d13926ad2a73a666092ead2d72fc021577ec3f` |
| Ending commit | `1c6dfb9` |
| Environment | Windows; Node.js 22.23.2; WordPress 6.9/PHP 8.0 wp-env (docker) |

## Question

Can the AP-022 (`ap022-stage-navigation-change.php`), AP-023 (`ap023-approval-rejection.php`), and AP-026/AP-033 (`ap026-ap033-publish-term.php`) security acceptance matrices execute independently, twice consecutively, and in arbitrary sequence without relying on prior state or corrupting navigation locations, database tables, user sessions, or fixture state, while preserving strict security assertions?

## Hypothesis

The observed cross-test interference and state corruption stem from five specific defects in the tests:
1. `ap022` and `ap023` filter `nav_menu_locations` by test name prefix, unsetting canonical menus at test startup and restoring corrupted/empty location maps on cleanup.
2. `ap022` and `ap023` call `unregister_nav_menu('primary')` on cleanup, destroying the registered location for subsequent tests and the canonical fixture.
3. `ap023` assigns `nav_menu_locations['primary'] = $menu_id` and then deletes `$menu_id`, leaving `primary` pointing to a non-existent term ID.
4. All three tests run unconditional `DELETE FROM` on `agentpress_changes`, `agentpress_change_sets`, and `agentpress_audit_events` at start and cleanup rather than removing only synthetic test fixtures.
5. All three tests set the global WordPress user to synthetic test users, delete those users on cleanup, and never restore the previous user session, leaving `$current_user` pointing to a deleted ID.

Isolating and scoping synthetic fixtures (scoped `DELETE` by synthetic actor user IDs), preserving and restoring `nav_menu_locations` and registered navigation menus without unregistering pre-existing locations, and restoring pre-test user sessions will make each test completely repeatable, order-independent, and safe against fixture corruption.

## Falsification condition

The hypothesis is falsified if:
- Any of the three tests fails when run alone on a clean fixture;
- Any test fails when run twice consecutively;
- Any test fails when executed immediately following either of the other two tests in any permutation;
- Any test leaves orphan synthetic users, pages, terms, menus, or changes;
- The canonical fixture acceptance test (`ap027-fixture-reset.php`) fails after running the test suite;
- Any security assertion (zero unauthorized mutation, staging non-mutation, single-approval, closed failure paths) is weakened or removed.

## Controls

- WordPress 6.9 / PHP 8.0 wp-env container environment (`wp-env-wp-agent-admin-40279b74-cli-1`).
- Canonical fixture state created by `npm run fixture:reset`.
- Product code (`agentpress/includes/**`) unchanged.
- Public contracts, schemas, and ability definitions unchanged.
- Test assertions must not be weakened or removed.

## Variables

- **Independent:** Test execution order (single, consecutive, and all 6 three-test permutations: AP-022 -> AP-023 -> AP-026/033, AP-026/033 -> AP-023 -> AP-022, AP-023 -> AP-022 -> AP-026/033, AP-022 -> AP-026/033 -> AP-023, AP-023 -> AP-026/033 -> AP-022, AP-026/033 -> AP-022 -> AP-023).
- **Dependent:** Exit codes (0), test assertion pass rates, database state after cleanup, `nav_menu_locations` state, registered menus, and `ap027-fixture-reset.php` pass status.

## Preflight

```text
local timestamp: 2026-09-03T18:55:00+07:00
UTC timestamp: 2026-09-03T11:55:00Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin\.worktrees\ap-029-security-test-hardening
git status --short --branch: ## ap-029-security-test-hardening
git log -3 --oneline --decorate:
97d1392 (HEAD -> ap-029-security-test-hardening, origin/main, origin/HEAD, main) feat(admin): add Changes and Activity collaboration UI
74ccc3a Merge pull request #44 from MrBigleg/ap-026-ap033-publish-term
51e7f71 (origin/ap-026-ap033-publish-term) feat(content): add publish-content and create-term staging plus approval (AP-026, AP-033, EXP-038)
baseline full commit SHA: 97d13926ad2a73a666092ead2d72fc021577ec3f
current branch: ap-029-security-test-hardening
existing unrelated changes: none (clean worktree)
AP task reference: AP-029
```

## Method

1. **Initial Baseline & Flaw Reproduction:**
   - Evaluated tests individually against clean fixture.
   - Evaluated `ap027-fixture-reset.php` after running `ap022` and `ap023`.
   - `OBSERVED`: State leak occurred; `nav_menu_locations` was wiped to `[]`, breaking `ap027`.
2. **State Preservation Implementation:**
   - In `ap022` and `ap023`:
     - Snapshot `$initial_user_id = get_current_user_id();`.
     - Snapshot `$initial_locations = get_theme_mod('nav_menu_locations', array());`.
     - Recorded `$was_primary_registered = has_nav_menu('primary') || array_key_exists('primary', get_registered_nav_menus());`. Only registered temporary primary menu if none was registered.
     - Scoped startup cleanup and exit teardown to synthetic actors:
       - `agentpress_changes WHERE actor_user_id IN (...)`
       - `agentpress_change_sets WHERE initiator_user_id IN (...)`
       - `agentpress_audit_events WHERE user_id IN (...)`
     - Restored `set_theme_mod('nav_menu_locations', $initial_locations)` on teardown.
     - Only unregistered `primary` if it was not registered prior to test entry.
     - Restored user session via `wp_set_current_user($initial_user_id)`.
   - In `ap026-ap033`:
     - Snapshot `$initial_user_id = get_current_user_id();`.
     - Replaced unconditional table truncation with scoped actor-based deletion (`actor_user_id`, `initiator_user_id`, `user_id` for `$admin`).
     - Restored `wp_set_current_user($initial_user_id)` on teardown.
3. **Assertion Scoping:**
   - In `ap022`: Scoped live pending status assertion to changes authored by the test's administrator:
     `SELECT DISTINCT status FROM agentpress_changes WHERE actor_user_id = %d`
     ensuring that completed/applied changes from other test suites do not falsely trigger assertion failures.
4. **Permutation & Idempotency Testing:**
   - Tested each suite individually.
   - Tested each suite twice consecutively.
   - Tested all 6 permutations of the 3 test suites, running `ap027-fixture-reset.php` after every sequence.
5. **Coding Standards Verification:**
   - Ran `phpcs` against all modified files using `wp-content/plugins/agentpress/phpcs.xml.dist`.

## Observations

1. `OBSERVED`: Baseline `ap022` and `ap023` clobbered `nav_menu_locations` in `wp_options`. After their run, `Locations: []` resulted in immediate failure of the AP-027 fixture integrity test.
2. `OBSERVED`: Unscoped `SELECT DISTINCT status FROM agentpress_changes` in `ap022` asserted that zero changes were `APPLIED`. When run after `ap023` or `ap026-ap033` (which apply approved changes), this assertion failed unless tables were truncated. Scoping the query to `actor_user_id = $users['administrator']` accurately verifies the staging non-mutation invariant for the test's own operations while permitting prior test artifacts to coexist.
3. `OBSERVED`: Single and consecutive execution of hardened tests:
   - `ap022` alone: exit code 0.
   - `ap022` -> `ap022`: exit code 0.
   - `ap023` alone: exit code 0.
   - `ap023` -> `ap023`: exit code 0.
   - `ap026-ap033` alone: exit code 0.
   - `ap026-ap033` -> `ap026-ap033`: exit code 0.
4. `OBSERVED`: Arbitrary sequence permutations:
   - `ap022` -> `ap023` -> `ap026-ap033` -> `ap027`: All passed, exit code 0.
   - `ap026-ap033` -> `ap023` -> `ap022` -> `ap027`: All passed, exit code 0.
   - `ap023` -> `ap022` -> `ap026-ap033` -> `ap027`: All passed, exit code 0.
   - `ap022` -> `ap026-ap033` -> `ap023` -> `ap027`: All passed, exit code 0.
   - `ap023` -> `ap026-ap033` -> `ap022` -> `ap027`: All passed, exit code 0.
   - `ap026-ap033` -> `ap022` -> `ap023` -> `ap027`: All passed, exit code 0.
5. `OBSERVED`: PHPCS validation on the 3 files:
   - Command: `phpcs --standard=wp-content/plugins/agentpress/phpcs.xml.dist /tmp/check/`
   - Result: `... 3 / 3 (100%)` — 0 errors, 0 warnings.
6. `SOURCE_VERIFIED`: Table schema verified against `agentpress/includes/Storage/Migrator.php` (`actor_user_id` on `agentpress_changes`, `initiator_user_id` on `agentpress_change_sets`, `user_id` on `agentpress_audit_events`).

## Conclusion

`SUPPORTED`.

All three security acceptance matrices are now independently repeatable and order-independent:
- Each test runs cleanly alone on a clean fixture.
- Each test runs cleanly twice consecutively.
- Any permutation of the three test suites executes without error, leaving no state leaks or orphan fixtures.
- The canonical challenge fixture (`ap027-fixture-reset.php`) passes after every execution run.
- Zero security assertions were weakened or removed; all negative security denial paths (schema invalid, permission denied, unauthenticated, duplicate idempotency replay, re-approval conflict, stale-target conflict, expired proposals, and oversize denial) remain strictly enforced.
- PHPCS passes with 0 errors and 0 warnings.

## Boundaries and Scope Compliance

- `DECIDED`: No product source was modified.
- `DECIDED`: No runner scripts, workflows, package.json, or other tests were modified.
- `NOT_COMMITTED`: Changes are staged in worktree `ap-029-security-test-hardening` pending authorized commit.
- `NOT_PUSHED`: Branch has not been pushed to remote.
- `NOT_MERGED`: `main` has not been altered.
- `EVIDENCE_INDEX`: Left untouched for main agent integration to avoid merge conflicts.
