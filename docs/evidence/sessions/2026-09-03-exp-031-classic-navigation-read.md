# EXP-031 — Classic navigation read adapter

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-031` |
| Related task | `AP-021` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-03T08:38:07+07:00` |
| Started UTC | `2026-09-03T01:38:07Z` |
| Ended local | `2026-09-03T09:02:57+07:00` |
| Ended UTC | `2026-09-03T02:02:57Z` |
| Agent/operator | Codex, implementation and evidence operator |
| Branch | `ap-021-classic-navigation-read` |
| Baseline commit | `e25cfe61b3b5d4332ed0994e18010272a2c9ef67` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 |

## Question

Can one bounded classic-menu adapter return the exact menu assigned to a registered WordPress location with a deterministic semantic state hash while rejecting missing, unsupported, oversized, and unauthorized navigation with zero read-time mutation?

## Hypothesis

The existing fixed `agentpress/get-navigation` contract, `edit_theme_options` policy, and canonical `StateHasher` are sufficient to add a narrow read service over WordPress classic-menu APIs without changing any public Ability or WebMCP schema.

## Falsification condition

The hypothesis is falsified if the returned Home/About/Blog/Contact hierarchy differs from WordPress, a relabel/move/add/remove/location reassignment fails to change the hash, a missing/block/oversized menu is accepted, unreadable or malformed destination data leaks, the output schema fails, an unauthorized caller succeeds, or any read changes WordPress or AgentPress target state.

## Controls

- fixed commit/build: baseline `e25cfe61b3b5d4332ed0994e18010272a2c9ef67`; no dependency update.
- fixed fixture/data: synthetic registered `primary` location with Home/About/Blog/Contact classic-menu items and bounded negative fixtures.
- fixed identity/capabilities: Administrator positive control; Author, Subscriber, and logged-out negative controls through current WordPress capabilities.
- fixed policy/configuration: existing 15-Ability catalog, current Safe Mode, `edit_theme_options`, classic menus only, 200-item hard bound.
- fixed client/environment: existing WordPress 6.9/PHP 8.0 wp-env; direct Ability integration test; no live browser/client claim.
- explicit scope exclusions: navigation staging/application, approvals, block Navigation support, fixture productization, UI, deployment, and release publication.

## Variables

- **Independent:** menu assignment, item label/order/parent/destination, item count, navigation architecture, and caller capability.
- **Dependent:** normalized output, state hash, stable error code, schema validity, and target/database mutation count.

## Preflight

```text
timestamp: 2026-09-03T08:38:07+07:00 / 2026-09-03T01:38:07Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main before branch creation
git log -3 --oneline --decorate: e25cfe6 README enhancement; 5ec6d12 submission links; 79b0587 README revision
baseline SHA: e25cfe61b3b5d4332ed0994e18010272a2c9ef67
current branch: ap-021-classic-navigation-read
unrelated existing changes: none observed
AP task, issue, PR: AP-021; issue/PR not authorized
environment: Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress/PHP runtime check pending
```

## Method

1. Inspect the exact navigation output schema, policy, registrar, hashing primitive, classic-menu APIs, and adjacent integration-test patterns.
2. Add an internal classic-menu read adapter and service with registered-location, assignment, type, readability, URL, text, and 200-item boundaries.
3. Hash the complete accepted semantic state including location, menu identity/name, and normalized ordered items; return the fixed success envelope.
4. Wire only `agentpress/get-navigation` into the existing Ability dispatcher without changing the public catalog or transport name.
5. Build a repeatable WordPress integration matrix for exact hierarchy, five material hash changes, deterministic replay, schema, errors, roles, and zero read-time mutation.
6. Run the AP-021 test, affected AP-007/AP-008/AP-012/AP-019 regressions, PHP unit tests, browser tests, PHPCS, and proportional package/provenance checks when locally available.
7. Record every failure/correction in order, close the evidence record, append the evidence index, and update project status only if acceptance is supported.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator owns implementation and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | `docs/IMPLEMENTATION_SPEC.md` at baseline | 2026-09-03 | Exact read contract, classic-only boundary, normalized fields, hash behavior, and 200-item output cap. | Project authority. |
| S2 | `SOURCE_VERIFIED` | `docs/BUILD_CHECKLIST.md` at baseline | 2026-09-03 | AP-021 deliverable, dependency, and acceptance matrix. | Project authority. |
| S3 | `OBSERVED` | WordPress 6.9/PHP 8.0.30 APIs exercised by the repository wp-env | 2026-09-03 | Runtime classic-menu registration, assignment, item retrieval, and mutation APIs. | Direct integration evidence. |
| S4 | `OBSERVED` | `AbilityRegistrar`, `ExecutionPolicy`, `AbilityCatalog`, and `StateHasher` at baseline | 2026-09-03 | Contract/policy/hash primitives exist; get-navigation execution is not dispatched. | Repository source. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-03T08:38:07+07:00 | Capture preflight and read authoritative task/template/source | repository | exit 0 | Clean baseline, exact next task, and unused EXP-031 confirmed. |
| 2026-09-03T08:41:00+07:00 | Create local branch `ap-021-classic-navigation-read` | repository | exit 0 | Branch isolated; no issue/PR/commit/push. |
| 2026-09-03T08:55:00+07:00 | Run `ap021-get-navigation.php` | WordPress 6.9/PHP 8.0 wp-env | exit 1 | First positive snapshot failed the fixed output-schema assertion; test stopped before later controls. |
| 2026-09-03T08:58:00+07:00 | Inspect stale fixture with inline `wp eval` | WordPress 6.9/PHP 8.0 wp-env | exit 1 | PowerShell/WP-CLI quote loss converted PHP strings to undefined constants; no product diagnosis obtained. |
| 2026-09-03T09:02:00+07:00 | Rerun AP-021 with bounded result diagnostic and inspect installed themes | WordPress 6.9/PHP 8.0 wp-env | test exit 1; theme query exit 0 | Result was `AP_UNSUPPORTED_NAVIGATION`; active theme is block theme Twenty Twenty-Five, while the fixture had a real registered/assigned classic location. |
| 2026-09-03T09:06:00+07:00 | Rerun `ap021-get-navigation.php` after capability-based location detection | WordPress 6.9/PHP 8.0 wp-env | exit 0 | Four-item snapshot/schema, deterministic hash, five material hash changes, three missing/unsupported controls, two private/malformed controls, three role denials, and zero read mutations passed. |
| 2026-09-03T09:09:00+07:00 | Run full `npm.cmd run lint:php` | repository/wp-env | exit 2 | New files had documentation-comment errors and one alignment warning; 44 files were scanned. |
| 2026-09-03T09:13:00+07:00 | Rerun full PHP lint after docblock corrections | repository/wp-env | exit 2 | Documentation errors cleared; two auto-fixable assignment-alignment warnings remained in `ClassicMenuAdapter`. |
| 2026-09-03T09:16:00+07:00 | Rerun full PHP lint | repository/wp-env | exit 0 | All 44 PHP files passed PHPCS. |
| 2026-09-03T09:18:00+07:00 | Run affected integration sequence AP-007/AP-008/AP-012/AP-019/AP-021 | WordPress 6.9/PHP 8.0 wp-env | exit 1 at AP-012 | AP-007 and AP-008 passed; AP-012 found `primary` assigned because the deliberately failed first AP-021 run had left a stale synthetic theme-mod reference. |
| 2026-09-03T09:23:00+07:00 | Rerun AP-021 then affected regressions | WordPress 6.9/PHP 8.0 wp-env | exit 1 at AP-012 | AP-021/AP-007/AP-008 passed; AP-012's earlier aborted run left more than its 200-page cap, so the newly created hierarchy was outside the returned sample. |
| 2026-09-03T09:27:00+07:00 | Run corrected AP-012/AP-019/AP-021 sequence | WordPress 6.9/PHP 8.0 wp-env | exit 0 | All three integrations passed; AP-021 retained five hash changes and zero read mutations. |
| 2026-09-03T09:29:00+07:00 | Run PHP unit and browser suites | repository/wp-env | exit 0 | PHP 68 tests/593 assertions; browser 18/18. |
| 2026-09-03T09:31:00+07:00 | Run final PHP lint | repository/wp-env | exit 0 | All 44 PHP files passed PHPCS. |
| 2026-09-03T09:33:00+07:00 | Run provenance/package boundary and inspect ZIP entries | repository | gate exit 0; archive inspection exit 0 | Licenses/pin passed with 55 entries, but neither new Navigation runtime class was present because the builder uses an explicit allowlist. |
| timestamp not independently captured | Update checklist/index/README after supported gates | repository | first patch targeted wrong identical checkbox | AP-016 was momentarily marked complete instead of AP-021; inspected exact task headings and corrected both before closeout. |
| timestamp correction appended at closeout | Correct the time-evidence boundary for preceding rows | evidence record | correction | Only the preflight timestamp was independently captured at action time. Later clock labels were estimates and must be treated as ordering labels, not verified timestamps; they are preserved above rather than rewritten. |
| timestamp not independently captured | Rerun provenance/package and deterministic build after allowlist correction | repository | exit 0 | 57 entries; both Navigation classes present; two builds matched SHA-256 `f523681e387edc8342d8328d383cd72bc619bcd40739f7767a59d4557897d82c`. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | The fixed Ability/schema/policy/tool map already includes get-navigation, but the production dispatcher has no navigation service and falls through to `AP_INTERNAL_ERROR`. | source inspection | Supports a bounded adapter/service change with no public-contract edit. |
| O2 | `OBSERVED` | The final snapshot matched Home/About/Blog/Contact, exact schema/fields/order/hierarchy, and an unchanged second read returned the same hash. | `ap021-get-navigation.php` | Supports exact bounded deterministic reads. |
| O3 | `OBSERVED` | Relabel, move, add, remove, and location reassignment each changed the state hash. | AP-021 integration output | Supports later stale-state protection. |
| O4 | `OBSERVED` | Missing, oversized, malformed, unreadable, Author, Subscriber, and logged-out controls failed safely; the measured read changed zero WordPress/AgentPress target state. | AP-021 integration output | Supports permission/privacy/fail-closed behavior. |
| O5 | `OBSERVED` | PHP unit, browser, PHPCS, affected WordPress regressions, provenance, archive membership, and reproducible ZIP checks passed after the recorded corrections. | command log | Supports repository integration at `UNCOMMITTED` state. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | The first normalized fixture snapshot matches the registered output schema. | The adapter rejected the whole block theme before examining the fixture's registered and assigned classic location. | Over-broad architecture detection. | Detect support from the requested registered/assigned classic location; a block theme without such a location still returns `AP_UNSUPPORTED_NAVIGATION`. Rerun pending. |
| C2 | Inline WP-CLI evaluation preserves quoted PHP string literals. | Nested PowerShell/WP-CLI parsing stripped the quotes and caused an undefined-constant fatal before product execution. | Diagnostic command quoting failure, not product behavior. | Use a temporary bounded diagnostic in the integration script, then remove it. |
| C3 | New PHP follows the repository coding standard on its first full scan. | PHPCS found documentation-comment errors and one alignment warning in the two new source files. | Source formatting/documentation failure. | Corrections and successful rerun are appended in the resolution addendum. |
| C4 | A failed AP-021 test run cannot affect a later regression. | The initial abort occurred before cleanup; a later successful rerun captured and restored that stale synthetic assignment, causing AP-012's unassigned control to fail. | Test-fixture recovery defect, not product behavior. | Before baseline capture, discard only invalid or `AP021` synthetic menu assignments; rerun AP-021 first, then affected regressions. |
| C5 | AP-012 is repeatable after an interrupted prior run. | Its fixture creates 202 public pages but had no stale-fixture cleanup; the next run's capped sample omitted its new parent/child pages. | Pre-existing regression-harness repeatability defect exposed by C4. | Correction and successful rerun are appended in the resolution addendum. |
| C6 | A successful package-boundary command proves new runtime files enter the ZIP. | The provenance assertions passed, but archive inspection showed the explicit build allowlist omitted both Navigation classes. | Genuine packaging integration defect. | Add only the two AP-021 runtime files to the explicit allowlist; rebuild and inspect exact entries. |
| C7 | A context-light checkbox patch marks AP-021 complete. | The first identical checkbox match was AP-016. | Documentation patch targeting error. | Restore AP-016 to open and mark AP-021 complete using task-heading context; verify exact headings. |

### Resolution addendum

- `OBSERVED`: C1's final AP-021 reruns passed after switching to concrete classic-location detection.
- `OBSERVED`: C2's failed inline command was not reused; the bounded diagnostic was removed after identifying C1.
- `OBSERVED`: C3 closed with 44/44 PHPCS files passing.
- `OBSERVED`: C4 and C5 closed with repeat-safe synthetic cleanup and passing AP-012/AP-021 reruns.
- `OBSERVED`: C6 closed with 57 ZIP entries, both Navigation classes present, and identical two-build SHA-256.
- `OBSERVED`: C7 closed with AP-016 open and AP-021 checked under their exact headings.

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Reject classic menus above 200 items rather than return an unverifiable partial snapshot. | S1/S2 | Smaller compatibility surface in exchange for complete hash/output integrity. | v0.1 contract adds an explicit truncation representation. |
| D2 | `DECIDED` | Hash location, menu identity/name, and all normalized items returned by the accepted snapshot. | S1/S4 | Any material state change invalidates later staging. | AP-022 identifies a missing semantic field. |
| D3 | `DECIDED` | Treat a concretely registered and assigned classic-menu location as supported even when the surrounding theme is block-based. | C1 | Capability detection is narrower and avoids a false negative; native block Navigation still has no supported location. | A block theme routes the same location through a non-classic backend. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Exact bounded classic-menu snapshot | Four-item fixture plus output-schema validation and deterministic repeat | `PASS` | Home/About/Blog/Contact; exact item fields/order/hierarchy. |
| Material mutations change hash | Relabel/move/add/remove/location reassignment | `PASS` | Five distinct material hash changes. |
| Missing/unsupported/oversized fail closed | Unassigned, unknown/non-classic location, 201-item filter | `PASS` | Stable `AP_NAVIGATION_NOT_FOUND`/`AP_UNSUPPORTED_NAVIGATION`. |
| Permission and zero-mutation controls | Malformed/unreadable destinations; Author/Subscriber/logged-out; before/after target snapshot | `PASS` | No private sentinel; three role denials; zero read mutations. |
| Affected regressions | AP-007/AP-008/AP-012/AP-019; PHP unit/browser/PHPCS | `PASS` | Policy/registry/structure/Overview pass; 68/593, 18/18, 44/44. |
| Package boundary | Provenance, exact archive members, two-build SHA-256 comparison | `PASS` | 57 entries; new runtime classes present; identical checksum. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `agentpress/includes/Navigation/ClassicMenuAdapter.php` | source | uncommitted | `1afb2b8fd337be61f38f23f24ed88c401503e9e3f86841743fece7e52fdc5b4d` | Classic-location normalization and hashing. |
| `agentpress/includes/Navigation/NavigationReadService.php` | source | uncommitted | `c8368692b978450c7ad196d3498d61ce66253823c71f4a62b238f9ce25859acf` | Current-user read boundary and result envelope. |
| `agentpress/tests/integration/ap021-get-navigation.php` | test | uncommitted | `bc0079a9fa9d56d412f2283edbebfd889540a2790400ae666d46482c30638a4f` | Acceptance/security/zero-mutation matrix. |
| `agentpress/tests/integration/ap012-site-structure.php` | test | uncommitted | `6acf4858164a7065ed416c6f0f602022d29fe8c7f2d8281af8de1cb77c1b085d` | Narrow stale-fixture recovery correction. |
| `agentpress/includes/Abilities/AbilityRegistrar.php` | source | uncommitted | `bb318d5499f9511dd2de72ad107c0bba37076c2d65dfc9d97e3c07ef76b57c72` | Dispatcher integration. |
| `scripts/build-zip.mjs` | build | uncommitted | `cdfc18d0c430f618fe6d2320afbcff84a22e8681f4bade0db6f590bab90560ec` | Explicit ZIP allowlist integration. |
| `docs/evidence/sessions/2026-09-03-exp-031-classic-navigation-read.md`; checklist/index/README | evidence/docs | uncommitted | N/A | Evidence-first record and truthful status. |
| `dist/agentpress.zip` | generated package check | ignored | `f523681e387edc8342d8328d383cd72bc619bcd40739f7767a59d4557897d82c` | 57 deterministic entries; not AP-030 release evidence. |

## Result

`SUPPORTED`

The hypothesis is supported for the repository and local WordPress 6.9/PHP 8.0.30 scope. `agentpress/get-navigation` now executes through a bounded classic-location reader, returns the fixed semantic schema and deterministic state hash, rejects unsupported/private/unauthorized states, and caused zero measured read mutation. This is not navigation staging, approval, or live ChatGPT evidence.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: a real client call to `agentpress_get_navigation`, deployed runtime, navigation staging/application, block Navigation, AP-023 approval, and five-run reliability.
- The generated ZIP passed reproducibility/membership checks but is a development artifact, not AP-030 clean-install release evidence.

## Competition evidence statement

- work attributable to challenge period: timestamps and baseline recorded before product mutation;
- pre-existing work distinguished by: exact baseline SHA and existing AP-001–AP-019 evidence;
- third-party material/license/pin: no new third-party material; existing GPL pin/provenance gate passed;
- commit/PR evidence: `UNCOMMITTED`; issue/PR not authorized;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_APPLICABLE` to AP-021.

## Next experiment

- proposed experiment ID/task: EXP-032 / AP-016;
- next falsifiable question: can update-content apply only to AgentPress-created drafts while staging every other editable target without direct mutation?;
- required prerequisites: AP-010/AP-015 already supported; AP-021 closeout independent.

## End state

```text
git status --short --branch: ap-021-classic-navigation-read; six modified and three untracked task files; no unrelated changes observed
tests/checks: AP-021 and affected integrations PASS; PHP 68/593; browser 18/18; PHPCS 44/44; provenance/package 57 entries; reproducible ZIP PASS
committed: no
pushed: no
deployed: no
```
