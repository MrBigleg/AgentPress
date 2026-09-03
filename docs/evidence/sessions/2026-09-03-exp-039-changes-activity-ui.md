# EXP-039 — Changes and Activity collaboration UI

## Metadata

| Field | Value |
|---|---|
| Experiment / tasks | `EXP-039` / `AP-024` + `AP-025` |
| Status / result | `COMPLETE` / `SUPPORTED` |
| Started local / UTC | `2026-09-03T18:09:44+07:00` / `2026-09-03T11:09:44Z` |
| Branch | `main` (owner-directed direct implementation) |
| Baseline / ending commit | `74ccc3a01b7b1740e8fe5821ac4da4f2aaace693` / `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; existing WordPress 6.9/PHP 8.0 wp-env |

## Question and acceptance

Can the existing native wp-admin module provide usable Changes list/detail/semantic diffs, explicit nonce-protected Approve/Reject controls, and sanitized Activity polling within 10 seconds while pausing in hidden tabs and preventing duplicate events or double-submit?

**Falsified if:** an approval occurs without a click, double-click causes multiple submissions, permission/conflict/expiry errors are hidden, unsafe fields render as HTML, polling continues while hidden, events duplicate, or the existing Overview/WebMCP behavior regresses.

## Controls and method

- Fixed 15-tool catalog, existing AP-020 read routes and AP-023 approval routes, current WordPress session/nonce manager, and native ESM/no-framework frontend.
- Add the missing `/updates` cursor route, localize admin endpoints, replace both placeholder panels, and add deterministic component/browser plus route tests.
- Run focused tests during development, then affected integration, browser, PHP unit, PHPCS, package/provenance, and deterministic ZIP gates before pushing.
- No React/toolchain migration, public-contract expansion, deployment, live-site mutation, or ChatGPT verification.

## Preflight

```text
timestamp: 2026-09-03T18:09:44+07:00 / 2026-09-03T11:09:44Z
git status: clean main synchronized with origin/main
git log -3: 74ccc3a merge PR #44; 51e7f71 AP-026/AP-033; c0350d1 merge PR #43
unrelated changes: none observed
```

## Observations

- `OBSERVED`: AP-020/AP-023 backend routes exist, but both admin panels remain static placeholders and `/updates` is absent.
- `OBSERVED`: the native Changes screen now loads Change Sets, renders bounded semantic before/after text, exposes approval actions only in pending detail views, disables duplicate submissions, and refreshes current state after a result.
- `OBSERVED`: permission loss, stale-state conflict, expiry, and missing-proposal failures map to explicit review messages; all server authorization and nonce enforcement remains in AP-023/`RequestGuard`.
- `OBSERVED`: `/updates` returns visibility-filtered events after a numeric cursor in ascending order, advances to the last returned ID, caps pages at 100, and returns no duplicate for a repeated cursor.
- `OBSERVED`: Activity renders only sanitized service fields, escapes untrusted strings, labels draft/staged/applied outcomes, filters client-side, polls every five seconds, and skips requests while the document is hidden.
- `OBSERVED`: `npm run test:browser` passed 26/26 tests, including explicit approve/reject invocation, double-click suppression, escaped output, cursor merge deduplication, and visibility-aware polling.
- `OBSERVED`: `ap019-admin-overview.php` passed for Administrator/Author/Subscriber shell access and localized collaboration endpoints.
- `OBSERVED`: `ap020-change-activity-reads.php` passed with 2/1/0 visible Change Sets, ten schema denials, zero duplicate cursor events, zero secret leaks, and zero read mutations.
- `OBSERVED`: PHPCS passed 54/54 files; PHPUnit passed 68 tests/593 assertions; provenance/package verification produced a deterministic 66-entry ZIP.
- `OBSERVED`: two consecutive ZIP builds matched SHA-256 `c9eefa24e1bf720c92a7acf5971a58bd4a160fc7b6c32802eab961f213769c17`.
- `NOT_TESTED`: no authenticated visual browser walkthrough or deployed-site verification was performed in this experiment.

## Changed files and verification

- Native UI: `agentpress/admin/src/admin-overview.mjs`, `admin-overview.css`, `webmcp-adapter.mjs`.
- Private reads: `ActivityReadService.php`, `AdminReadRoutes.php`, and localized `AdminPage.php` settings.
- Acceptance coverage: `admin-overview.test.mjs`, `ap019-admin-overview.php`, `ap020-change-activity-reads.php`.
- Documentation: this record, `docs/EVIDENCE_INDEX.md`, `docs/BUILD_CHECKLIST.md`, and `README.md`.
- Start SHA: `74ccc3a01b7b1740e8fe5821ac4da4f2aaace693`; ending SHA: `UNCOMMITTED` before the authorized direct-main commit.
- No deployment or live-site mutation occurred.

## Result

`SUPPORTED`: AP-024/AP-025 acceptance is supported at source, component, and local WordPress integration levels. Live visual/deployed behavior remains a separate verification boundary. Exact next task: AP-029, the consolidated clean-environment P0 security gate.
