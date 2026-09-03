# EXP-040 — P0 security and approval integration gate

## Metadata

| Field | Value |
|---|---|
| Experiment / task | `EXP-040` / `AP-029` |
| Status / result | `COMPLETED` / `SUPPORTED` |
| Started local / UTC | `2026-09-03T18:37:38+07:00` / `2026-09-03T11:37:38Z` |
| Branch | `main` (owner-directed continuation) |
| Baseline / ending commit | `97d13926ad2a73a666092ead2d72fc021577ec3f` / `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can the complete P0 security and approval matrix run from one clean WordPress 6.9 fixture in CI without test-order dependencies, while every rejected path verifies zero unauthorized mutation?

**Falsified if:** the clean runner requires hidden state, an owned test fails when repeated/reordered, CI omits a specified security class, or a denial asserts only an error without checking durable state.

## Controls and method

- Fixed v0.1 scope, 15-Ability catalog, Safe Mode, canonical AP-027 fixture, and current production services.
- Main-agent ownership: consolidated runner, CI wiring, clean-environment orchestration, gate coverage audit, documentation, and final verification.
- Parallel-agent ownership (if delivered): only AP-022/AP-023/AP-026+033 integration-test self-containment; no overlapping source/CI/docs edits.
- Inventory existing matrices against role, direct-call, nonce, rate, schema, idempotency, stale/concurrency, audit, and zero-mutation requirements.
- Add the smallest deterministic runner/CI job, repair only order/fixture defects outside the parallel ownership boundary, then run the complete gate twice from reset.
- No product expansion, deployment, live-client claims, release publication, or remote branch cleanup.

## Preflight

```text
timestamp: 2026-09-03T18:37:38+07:00 / 2026-09-03T11:37:38Z
git status: clean main synchronized with origin/main
git log -3: 97d1392 AP-024/AP-025; 74ccc3a merge PR #44; 51e7f71 AP-026/AP-033
unrelated changes: none observed
```

## Observations

- `OBSERVED`: AP-018 already covers five-role discovery/direct execution, nonce/origin/core-REST boundaries, dynamic capability changes, audit sanitization, and zero unauthorized mutation, but CI does not run the WordPress integration scripts.
- `OBSERVED`: the first consolidated local pass completed 15/15 matrices in 415,941 ms and a separate AP-027 check confirmed the final canonical fixture was restored.
- `OBSERVED`: the first pass exposed a runner-only defect: the environment probe omitted `<?php`, so wp-cli echoed rather than executed it. The individual matrices and fixture reset still executed; the probe was corrected before acceptance.
- `OBSERVED`: a direct PHPCS check of the new atomic-claim fixture initially failed on compact test docblocks/arrays and deliberate direct fixture cleanup; no runtime assertion failed. The test was reformatted and scoped test-harness suppressions were added before rerun.
- `OBSERVED`: the first PHPCS rerun removed all errors but retained two auto-fixable array-alignment warnings; those final two alignments were corrected before acceptance.
- `OBSERVED`: the added atomic-claim matrix passed independently with two repository instances, one durable winner, and zero target mutations.
- `OBSERVED`: the browser suite passed 27/27, PHPUnit passed 68 tests with 593 assertions, and the standard source PHPCS gate passed 54/54 before the parallel hardening integration.
- `OBSERVED`: EXP-041 independently passed each of AP-022, AP-023, and AP-026/AP-033 alone, twice consecutively, and in all six execution permutations followed by the AP-027 fixture gate; its three-file PHPCS run reported zero errors and warnings.
- `DECIDED`: the security runner now registers 16 matrices and is wired into GitHub Actions after `wp-env` startup.
- `NOT_TESTED`: at owner direction, the final combined 16-matrix command and post-integration general regressions were not rerun. Acceptance relies on the earlier 15/15 consolidated pass, the independent atomic-claim pass, and EXP-041's permutation matrix.
- `NOT_TESTED`: the new CI job has not run remotely until this change is pushed.

## Result

`SUPPORTED`: the P0 security classes are represented in one deterministic CI runner, the original consolidated set passed 15/15, the new concurrency claim passed independently, and the formerly order-dependent suites passed every hardened permutation. The final combined invocation was intentionally not rerun and remains an explicit verification boundary.
