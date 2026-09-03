# EXP-036 — Stage a classic-menu navigation change

## Metadata

| Field | Value |
|---|---|
| Experiment / task | `EXP-036` / `AP-022` |
| Status / result | `COMPLETE` / `SUPPORTED` |
| Started local / UTC | `2026-09-03T11:44:10+07:00` / `2026-09-03T04:44:10Z` |
| Ended local / UTC | `2026-09-03T12:38:14+07:00` / `2026-09-03T05:38:14Z` |
| Branch | `ap-022-stage-navigation-change` (from `main` `fe37fa4`) |
| Environment | Windows; Node.js 22.23.2; existing WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can `agentpress/stage-navigation-change` stage one add/remove/move against a classic menu as an immutable R2 proposal and return the exact before/after preview without mutating the live menu?

**Hypothesis:** the existing `ClassicMenuAdapter`, `ChangeCoordinator`, and staging primitives can enforce the operation-specific rules (same-origin custom URL, child-removal rejection, readable referenced items) with zero live navigation mutation.

**Falsified if:** staging mutates the live menu, the after preview differs from the requested order, an unsafe/cross-origin URL passes, an item with children is silently reparented, an unreadable/missing item passes, an unauthorized role stages, or idempotent replay duplicates work.

## Controls and variables

- Fixed: WordPress 6.9/PHP 8.0 wp-env; existing classic-menu adapter/coordinator; existing 15-tool catalog (AP-022 schema already present).
- Independent: operation (add/remove/move), item object provenance (page draft/custom URL), position/parent, URL origin, caller role, and idempotency key.
- Dependent: result/error, exact before/after item order, live menu identity, stored proposal status/hashes, and durable mutation counts.
- No subagents, dependency changes, public-contract changes, commit, push, deployment, or live-client claim.

## Method

1. Inspect the AP-022 spec (6.12), `ClassicMenuAdapter`, `ChangeCoordinator` staging, and the adjacent AP-020/AP-016 service patterns.
2. Implement a narrow `StageNavigationChangeService` and wire its executor dispatch and ZIP manifest entry.
3. Build a `wp eval-file` acceptance matrix over a classic primary menu (Home/About/Blog/Contact) plus a Services draft and a nested item.
4. Assert exact before/after previews for add/remove/move, same-origin versus cross-origin/https custom URLs, child-removal rejection, readable-object checks, schema denials, role denials, replay idempotency, and zero live-menu/no-applied mutation.
5. Run PHP unit, PHPCS, affected integration regressions, and deterministic ZIP build; record failures and corrections as they occur.

## Observations

- `OBSERVED`: The closed ability input schema rejects `change_set_title`/`request_summary`, so the service allowlist mirrors exactly `{location, operation, item, change_set_id, idempotency_key}`.
- `OBSERVED`: Adding the Services draft at position 3 returned the exact `[Home, About, Services, Blog, Contact]` preview, a `PENDING_APPROVAL` proposal with 64-char state hash, and a replay with the same change ID.
- `OBSERVED`: Add of a custom same-origin HTTPS link passed; a cross-origin HTTPS link was blocked `AP_POLICY_BLOCKED`; a non-HTTPS custom link was `AP_SCHEMA_INVALID`.
- `OBSERVED`: Removing a parent whose child is nested under it was rejected `AP_UNSUPPORTED_NAVIGATION`; a missing item id was `AP_NAVIGATION_NOT_FOUND`; unknown operation/missing key/bad location were denied.
- `OBSERVED`: Author and Subscriber/logged-out were denied at policy and at the service; the capability-mutated Author passed coarse permission; no staged change reached `APPLIED`.
- `OBSERVED`: Across all staging calls the live menu identity was unchanged (`live_menu_mutations=0`) and the only durable writes were `PENDING_APPROVAL` change rows (`durable_mutations=0` for live nav).

## Result

`SUPPORTED`: the staging service satisfies the AP-022 acceptance on the controlled classic-menu fixture.

## Verification and artifacts

- AP-022 runtime acceptance (exit 0): `add_preview_exact`, `remove_preview`, `move_preview`, `replay_idempotent`, `same_origin_custom` all `true`; `unsafe_url_denials=2`, `child_remove_denial=1`, `schema_denials=5`, `role_denials=3`, `live_menu_mutations=0`, `durable_mutations=0`.
- Regressions: AP-008 (15 registered abilities; admin 15 / subscriber 8 bridge tools), AP-010, AP-011, AP-016, AP-021 — all exit 0.
- PHP coding standards `46/46`; PHPUnit `68 tests / 593 assertions`; `git diff --check` exit 0.
- Release ZIP deterministic (two builds share one SHA-256; 59 entries); includes `StageNavigationChangeService.php`; zero `tests/` leakage.

## Changed files

- `agentpress/includes/Navigation/StageNavigationChangeService.php` (new).
- `agentpress/includes/Abilities/AbilityRegistrar.php`, `scripts/build-zip.mjs`.
- `agentpress/tests/integration/ap022-stage-navigation-change.php` (new).
- `docs/BUILD_CHECKLIST.md`, `docs/EVIDENCE_INDEX.md`, `docs/evidence/sessions/2026-09-03-exp-036-stage-navigation-change.md`, `README.md`.

## Limitations / next experiment

- The approval/rejection service (AP-023) is the next backend task; this record proves only staging and the preview/precondition rules, not the approval executor or the live apply path.
- Browser (AP-024/AP-025) and live-client behavior remain `NOT_TESTED`.
- Not committed, pushed, deployed, or live-verified at the time of writing.
