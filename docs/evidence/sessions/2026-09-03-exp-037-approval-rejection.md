# EXP-037 — Human approval and rejection

## Metadata

| Field | Value |
|---|---|
| Experiment / task | `EXP-037` / `AP-023` |
| Status / result | `COMPLETE` / `SUPPORTED` |
| Started local / UTC | `2026-09-03T11:05:22+07:00` / `2026-09-03T04:05:22Z` |
| Ended local / UTC | `2026-09-03T16:26:56+07:00` / `2026-09-03T09:26:56Z` |
| Branch | `ap-023-approval-rejection` (from `main` `fbc2c47`) |
| Environment | Windows; Node.js 22.23.2; existing WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can a human wp-admin approver apply or reject one pending R2 proposal only when the live target still matches the stored target-state hash, with no stale apply, no privilege bypass, and a durable audit/Change Set record?

**Hypothesis:** the existing Change Coordinator immutability hashes plus the Change Set child statuses can drive a trusted `ApprovalService` + private routes that re-validate target state and capability before mutating.

**Falsified if:** an approval applies over a changed target, an expired proposal is applied, an unauthorized or missing approver is accepted, rejection mutates WordPress, no reusable Change Set/audit record is written, or a re-approval double-applies.

## Controls and variables

- Fixed: WordPress 6.9/PHP 8.0 wp-env; existing classic-menu fixture; existing Change Coordinator/repos/audit.
- Independent: caller role (administrator/author/logged-out), target freshness, proposal expiry, apply-after-reject state, and staged operation (navigation add/remove/move).
- Dependent: approve/reject result, live menu identity, change-row status/approver/rejector fields, parent Change Set status, and durable mutation counts.
- No subagents, dependency changes, public-contract changes, commit, push, deployment, or live-client claim.

## Method

1. Inspect the AP-023 spec (7.4, 9.2), Change Coordinator stage hashing, ChangeRepository transitions, parent reducer, and AuditLogger.
2. Implement `ApprovalService` (claim→verify proposal→check expiry→re-hash live target→apply→APPLIED/audit/parent-sync; and reject), `NavigationApplyService` (apply add/remove/move against the live menu without resetting post_type items), and `ApprovalRoutes`; wire the plugin and ZIP manifest.
3. Build a `wp eval-file` acceptance matrix: approve applies an add at the exact position, records the approver, moves the set toward COMPLETED; re-approve conflicts; reject records the rejector with zero mutation; expired and stale-target approvals fail without mutation; unauthorized/logged-out approvers are denied; and human audit records are written.
4. Run PHP unit, PHPCS, affected regressions, and deterministic ZIP build; record failures and corrections as they occur.

## Observations

- `OBSERVED`: `approve` applies a pending navigation add, records `approved_by`/`applied_at`, returns `APPLIED`/`SUCCESS`, and the live menu reflects the exact target order.
- `OBSERVED`: re-approving an already-applied change returns `AP_STATE_CONFLICT` (no double apply); a live menu changed after staging returns `AP_STATE_CONFLICT` with no mutation and the change row becomes `CONFLICT`.
- `OBSERVED`: reject records `rejected_by`/`rejected_at`/reason, leaves the menu byte-for-byte unchanged, and rejects an expired proposal with `AP_CHANGE_EXPIRED`.
- `OBSERVED`: Author reveals `AP_PERMISSION_DENIED` and logged-out reveals `AP_NOT_AUTHENTICATED`; human `actor_type` audit events are written for approve and reject.
- `OBSERVED`: Two corrections surfaced during the run: partial `wp_update_nav_menu_item` updates reset existing `post_type` items to custom links (fixed with `wp_update_post`/meta writes), and menu-order writes bypassed the nav cache (fixed via `wp_update_post`).

## Result

`SUPPORTED`: the approval and rejection machinery satisfies the AP-023 acceptance on the controlled classic-menu fixture.

## Verification and artifacts

- AP-023 runtime acceptance (exit 0): `approve_applied`, `approver_recorded`, `reapply_conflict`, `reject_no_mutation`, `rejector_recorded`, `expired_denied`, `stale_conflict`, `stale_no_mutation` all `true`; `role_denials=2`; repeatable across runs.
- Regressions: AP-008 (15 registered abilities), AP-010, AP-020, AP-021, AP-022, AP-023 — all exit 0.
- PHP coding standards `52/52`; PHPUnit `68 tests / 593 assertions`; `git diff --check` exit 0.
- Release ZIP deterministic (two builds share one SHA-256; 64 entries); includes `ApprovalService.php`, `NavigationApplyService.php`, `ApprovalRoutes.php`; zero `tests/` leakage.

## Changed files

- `agentpress/includes/Changes/ApprovalService.php` (new), `agentpress/includes/Navigation/NavigationApplyService.php` (new), `agentpress/includes/Rest/ApprovalRoutes.php` (new).
- `agentpress/includes/Navigation/StageNavigationChangeService.php` (store location in `object_type`), `agentpress/includes/Plugin.php`, `scripts/build-zip.mjs`.
- `agentpress/tests/integration/ap023-approval-rejection.php` (new).
- `docs/BUILD_CHECKLIST.md`, `docs/EVIDENCE_INDEX.md`, `docs/evidence/sessions/2026-09-03-exp-037-approval-rejection.md`, `README.md`.

## Limitations / next experiment

- The approval of a changed target fails closed to CONFLICT, but the UI to inspect and re-stage (AP-024) and the publish/term approval paths (AP-026/AP-033) are the next pieces. This record proves the navigation + read-staging approval path and the invariants.
- Browser (AP-024/AP-025) and live-client behavior remain `NOT_TESTED`.
- Not committed, pushed, deployed, or live-verified at the time of writing.
