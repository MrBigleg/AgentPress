# EXP-033 — Change Set and Activity reads

## Metadata

| Field | Value |
|---|---|
| Experiment / task | `EXP-033` / `AP-020` |
| Status / result | `COMPLETE` / `SUPPORTED` |
| Started local / UTC | `2026-09-03T10:26:10+07:00` / `2026-09-03T03:26:10Z` |
| Ended local / UTC | `2026-09-03T11:31:16+07:00` / `2026-09-03T04:31:16Z` |
| Branch | `ap-020-change-activity-reads` |
| Baseline / ending commit | `31542849c025a02d4fd81503527627239e2b60bd` / `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; existing WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can the existing repositories expose bounded `get-change-set`, `list-change-sets`, and `get-agent-activity` reads plus private admin routes while enforcing owner-or-Administrator visibility, stable pagination, semantic summaries, and sanitized activity output?

**Hypothesis:** The existing Change Set and audit repositories plus the fixed 15-Ability policy can expose a read-only collaboration layer without broadening the public contract or mutating durable state.

**Falsified if:** guessed IDs disclose existence, ordinary users see another user's rows, pagination is unstable, raw proposal bodies or secret-like audit data enter activity, schemas fail, or reads mutate durable state.

## Controls and variables

- Fixed: WordPress 6.9/PHP 8.0 wp-env; existing 15-tool catalog; existing change-set/audit repos; existing capability policy and Safe Mode.
- Independent: identity (Administrator/Author/Subscriber), target set owner, guessed/absent ID, status filter, page/per-page bounds, and result filter.
- Dependent: visible rows, result/error, semantic summary boundedness, pagination stability, secret leakage, and durable mutation counts.
- No subagents, dependency changes, public-contract changes, commit, push, deployment, or live-client claim.

## Method

1. Inspect the exact Change Set/change/audit repositories, catalog schemas, transport policy, and adjacent read-service integration patterns.
2. Verify the read services enforce ownership internally and match the registered output schemas.
3. Seed a controlled WordPress matrix (two Change Sets with distinct owners, three changes, three audit events with secret sentinels) and run a `wp eval-file` acceptance script across Administor/Author/Subscriber.
4. Assert schema validity, ownership, pagination stability, status filtering, absence of secret/raw-body leakage, and zero durable mutation.
5. Run the affected integration regressions, PHP unit, PHP coding standards, and deterministic ZIP build; note failures and corrections as they occur.

## Observations

- `OBSERVED`: The registered executor now dispatches `get-change-set`, `list-change-sets`, and `get-agent-activity` to the read services while leaving the fixed 15-Ability catalog unchanged.
- `OBSERVED`: The read services return only allowlisted metadata columns and never `arguments_sanitized`; secret argument sentinels (cookie/password) and an oversized proposal body did not appear in activity/detail output.
- `OBSERVED`: Administrator saw both seeded Change Sets and all three events; Author saw only its own set and event and received `AP_CHANGE_NOT_FOUND` for a guessed non-owned set (no existence disclosure); Subscriber saw zero sets and only its own event.
- `OBSERVED`: Pagination was stable across repeated page-1 reads and the `READY_FOR_REVIEW` status filter returned one set; unknown keys, page 0, oversized `per_page`, unknown `result`, and non-positive/absent Change Set IDs all returned `AP_SCHEMA_INVALID`.
- `OBSERVED`: The semantic before/after summary is bounded (<= 5000 chars and nested depth capped at 8) and non-empty for an applied change.
- `OBSERVED`: All reads produced zero durable WordPress/AgentPress mutation (change-set, change, and audit row counts unchanged).

## Result

`SUPPORTED`: the read layer satisfies the AP-020 acceptance on the controlled fixture.

## Verification and artifacts

- AP-020 runtime acceptance (exit 0): `admin_visible_sets=2`, `author_visible_sets=1`, `subscriber_visible_sets=0`, `author_guessed_set_denials=3`, `schema_denials=7`, `secret_leaks=0`, `read_mutations=0`.
- Affected regressions: AP-008 (15 registered abilities; admin 15 / subscriber 8 / logged-out 0 bridge tools; `unauthorized_mutations=0`), AP-010 (coordinator unchanged), AP-011 (16 operations; 7 private sentinels absent), AP-021 (navigation unchanged) — all exit 0.
- PHP coding standards `48/48`; PHPUnit `68 tests / 593 assertions`; `git diff --check` exit 0.
- Release ZIP deterministic (two builds share SHA-256 `0B2C0531D77F16FFC2E0D7BF3159348FA787AEEDC250F4171C7BD90382C448BB`); includes `ActivityReadService.php`, `ChangeSetReadService.php`, `AdminReadRoutes.php`; zero `tests/` leakage.

## Changed files

- `agentpress/includes/Changes/ChangeSetReadService.php` (new), `agentpress/includes/Audit/ActivityReadService.php` (new), `agentpress/includes/Rest/AdminReadRoutes.php` (new).
- `agentpress/includes/Abilities/AbilityRegistrar.php`, `agentpress/includes/Plugin.php`, `agentpress/includes/Policy/ExecutionPolicy.php`, `scripts/build-zip.mjs`.
- `agentpress/tests/integration/ap020-change-activity-reads.php` (new).
- `docs/BUILD_CHECKLIST.md`, `docs/EVIDENCE_INDEX.md`, `docs/evidence/sessions/2026-09-03-exp-033-change-activity-reads.md`, `README.md`.

## Limitations / next experiment

- Browser (AP-024/AP-025) and live-client behavior remain `NOT_TESTED`; this record proves only the server read layer.
- AP-029 integration gate, AP-022/AP-023 approval backend, and AP-024/AP-025 UI depend on this read layer and remain open.
- Not committed, pushed, deployed, or live-verified.
