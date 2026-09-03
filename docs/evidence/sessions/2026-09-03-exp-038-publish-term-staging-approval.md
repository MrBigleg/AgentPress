# EXP-038 — Publish-content and create-term staging + approval

## Metadata

| Field | Value |
|---|---|
| Experiment / task | `EXP-038` / `AP-026` + `AP-033` |
| Status / result | `COMPLETE` / `SUPPORTED` |
| Started local / UTC | `2026-09-03T16:35:12+07:00` / `2026-09-03T09:35:12Z` |
| Ended local / UTC | `2026-09-03T17:09:55+07:00` / `2026-09-03T10:09:55Z` |
| Branch | `ap-026-ap033-publish-term` (from `main` `c0350d1`) |
| Environment | Windows; Node.js 22.23.2; existing WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can `publish-content` and `create-term` stage immutable R2 proposals that approve to a single publish/term creation, with no immediate mutation, while rejecting already-published targets and preserving the approval invariants?

**Hypothesis:** the existing Change Coordinator staging + `ApprovalService` apply dispatch can publish a post and create a term only through a reviewed proposal, with a readable target, the right capability, and zero direct mutation at staging.

**Falsified if:** staging publishes/creates, the wrong capability or a missing target passes, a republish/duplicate is accepted, or approval fails to produce exactly one publish/term.

## Controls and variables

- Fixed: WordPress 6.9/PHP 8.0 wp-env; existing coordinator/approval/audit.
- Independent: target status, taxonomy, caller role, and idempotency key.
- Dependent: staged status, live post/term state, approval result, and durable mutation counts.
- No subagents, dependency changes, public-contract changes, commit, push, deployment, or live-client claim.

## Method

1. Implement `PublishContentService` (edit_post + publish capability, not-already-published gate, stage an R2 proposal) and `CreateTermService` (manage_terms capability, parent existence, stage an R2 proposal) and dispatch them from the ability executor.
2. Extend `ApprovalService::apply_target`/`compute_target_hash` for publish and create-term.
3. Build a `wp eval-file` acceptance matrix: stage then approve a publish (never immediate), republish conflicts, stage then approve a category (never immediate), and logged-out staging/approval denials.
4. Run PHP unit, PHPCS, affected regressions, and deterministic ZIP build; record corrections as they occur.

## Observations

- `OBSERVED`: publish staging returns `PENDING_APPROVAL` with `proposed_status=publish` while the post stays `draft`; approval publishes it once and records the approver.
- `OBSERVED`: publishing an already-published target returns `AP_STATE_CONFLICT`; logged-out staging and approval return `AP_NOT_AUTHENTICATED`.
- `OBSERVED`: create-term staging returns `PENDING_APPROVAL` with the normalized `proposed_term` while the category does not exist; approval creates exactly one category and records its `term_id`.
- `OBSERVED`: one correction surfaced during the run — `wp_insert_term` returns a `[term_id, term_taxonomy_id]` array, not an int, so the apply path now reads `$term['term_id']`.

## Result

`SUPPORTED`: publish-content and create-term satisfy the AP-026/AP-033 staging + approval acceptance.

## Verification and artifacts

- AP-026/033 runtime acceptance (exit 0): `publish_staged`/`publish_approval`/`no_immediate_pub`/`republish_denied`/`term_staged`/`term_approval`/`no_immediate_term` all `true`; `logged_out_denials=3`.
- Regressions: AP-008, AP-011, AP-016, AP-020, AP-021, AP-022, AP-023, AP-026/033 — all exit 0.
- PHP coding standards `54/54`; PHPUnit `68 tests / 593 assertions`; `git diff --check` exit 0.
- Release ZIP deterministic (two builds share one SHA-256; 66 entries); includes both services; zero `tests/` leakage.

## Changed files

- `agentpress/includes/Content/PublishContentService.php` (new), `agentpress/includes/Terms/CreateTermService.php` (new).
- `agentpress/includes/Abilities/AbilityRegistrar.php`, `agentpress/includes/Changes/ApprovalService.php`, `scripts/build-zip.mjs`.
- `agentpress/tests/integration/ap026-ap033-publish-term.php` (new).
- `docs/BUILD_CHECKLIST.md`, `docs/EVIDENCE_INDEX.md`, `docs/evidence/sessions/2026-09-03-exp-038-publish-term-staging-approval.md`, `README.md`.

## Limitations / next experiment

- AP-024/AP-025 collaborate UI and live-client behavior remain `NOT_TESTED`. AP-029 integration gate and AP-030/031/032 release/reliability/submission are the next challenge gates.
- Not committed, pushed, deployed, or live-verified at the time of writing.
