# EXP-032 — Bounded content updates

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-032` |
| Related task | `AP-016` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local / UTC | `2026-09-03T10:11:48+07:00` / `2026-09-03T03:11:48Z` |
| Ended local / UTC | `2026-09-03T10:23:49+07:00` / `2026-09-03T03:23:49Z` |
| Branch | `ap-016-update-content` |
| Baseline / ending commit | `c98660a4450a1386e8135f0d49342e225e71ee3e` / `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; existing WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can `agentpress/update-content` apply a sanitized bounded patch only to an AgentPress-created draft while staging an immutable R2 proposal for every other editable post/page with zero direct target mutation?

**Hypothesis:** Existing policy, provenance, state-hash, idempotency, and Change Set primitives can enforce that boundary without changing the public contract.

**Falsified if:** status/taxonomy/unknown fields bypass the schema; ownership or parent rules fail; non-AgentPress or published content mutates directly; staging mutates the target; retries duplicate work; or forbidden calls change WordPress/AgentPress state.

## Controls and method

- Fixed baseline and existing 15-tool catalog; synthetic posts/pages; existing WordPress capability policy and Safe Mode.
- Vary provenance, status, owner, type, parent, patch, caller, and idempotency key; observe result/error, target state, proposal/audit state, and mutation counts.
- Inspect adjacent services, implement the narrow executor and dispatcher wiring, run AP-016 acceptance, then defer broad regressions to the next backend integration checkpoint.
- No subagents, dependency changes, public-contract changes, commit, push, deployment, or live-client claim.

## Preflight

```text
git status: clean main synchronized with origin/main before branch creation
git log -3: c98660a merge AP-021; 6db19c AP-021 implementation; e25cfe6 README
unrelated changes: none observed
issue/PR: not authorized
```

## Observations and result

- `OBSERVED`: The registered executor now applies title/content/excerpt/slug/parent patches to AgentPress-created drafts through the R1 coordinator.
- `OBSERVED`: An ordinary draft and published post each produced immutable 64-character state/proposal hashes and remained unchanged while pending approval.
- `OBSERVED`: Empty/status/invalid-parent inputs and Author/Subscriber/logged-out object checks failed without target mutation; Author KSES behavior and identical replay passed.
- `SUPPORTED`: The focused runtime matrix passed with 2 R1 applications, 2 R2 proposals, zero staged mutations, four schema/parent denials, three role denials, and one replay.

## Changed files and verification

- Changed files: `ContentUpdateService.php`, `AbilityRegistrar.php`, `build-zip.mjs`, `ap016-update-content.php`, checklist/index/README, and this record.
- Verification: final AP-016 runtime acceptance exit 0; full PHP lint 45/45; `git diff --check` exit 0. The first focused PHPCS command used invalid Composer argument forwarding; the direct project binary then identified and guided formatting/docblock corrections.
- Not committed, pushed, deployed, or live-verified.
