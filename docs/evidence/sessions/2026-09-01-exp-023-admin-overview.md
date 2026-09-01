# EXP-023 — Live wp-admin Overview shell

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-023` |
| Related task | `AP-019` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-01T17:51:21+07:00` |
| Started UTC | `2026-09-01T10:51:21Z` |
| Ended local | `2026-09-01T21:43:47+07:00` |
| Ended UTC | `2026-09-01T14:43:47Z` |
| Agent/operator | Codex, UI implementation and evidence operator |
| Branch | `ap-019-admin-overview` |
| Baseline commit | `91cf1606c8f447c87c7b00c6afad9133cd96c070` |
| Ending commit | `12a75ca35961151eff00510bd61f7f885b67d1e3` |
| Environment | Windows; Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress 6.9; PHP 8.0.30; headless Chrome 152 |

## Question

Can a dedicated AgentPress wp-admin screen expose live current-user bridge/context diagnostics and an accurate capability matrix across loading, active, degraded, error, and no-tool states without counting blocked policy areas as tools?

## Hypothesis

The existing private discovery/execute routes and safe `get-context` envelope already provide the necessary current-user data. A page-scoped module can register the returned definitions, render actual counts, and keep blocked R3 areas visually separate without adding a chatbot or a second authorization path.

## Falsification condition

The hypothesis is falsified if assets load outside the AgentPress screen, a logged-out/unauthorized user can access it, displayed identity/capabilities differ from the server responses, blocked areas increase tool counts, unsupported WebMCP is shown as active, an error leaves no retry path, a Subscriber-like user sees fictional write tools, or keyboard/mobile/reduced-motion checks fail.

## Controls

- fixed commit/build: baseline `91cf1606c8f447c87c7b00c6afad9133cd96c070` and one AP-019 worktree.
- fixed fixture/data: Administrator, Author, and Subscriber users with live discovery/context responses.
- fixed identity/capabilities: real WordPress session/REST nonce and current-user policy; no client-authored capability claims.
- fixed policy/configuration: assets enqueue only on the top-level AgentPress page; blocked R3 areas remain absent from Ability/tool maps.
- fixed client/environment: wp-env WordPress 6.9/PHP 8.0 plus browser-contract tests; live ChatGPT remains separate.
- explicit scope exclusions: Change/Activity data services, approval controls, deployment, real ChatGPT verification, and reliability gate.

## Variables

- **Independent:** user identity, WebMCP support, HTTPS/runtime diagnostic, discovery/context success or failure, and viewport/reduced-motion setting.
- **Dependent:** rendered state, registered/exposed count, automatic/approval counts, capability rows, blocked-area separation, retry behavior, accessibility, and asset scope.

## Preflight

```text
timestamp: 2026-09-01T17:51:21+07:00 / 2026-09-01T10:51:21Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main before branch creation
git log -3 --oneline --decorate: 91cf160 AP-017 closeout; 3b6daa5 PR #33 merge; bdb0a92 AP-017 evidence
baseline SHA: 91cf1606c8f447c87c7b00c6afad9133cd96c070
unrelated existing changes: none observed
AP task, issue, PR: AP-019; issue #34; PR pending
environment: Node.js 22.23.2; npm 10.9.8; Docker 29.6.1; WordPress/PHP pending
```

## Method

1. Inspect approved identity assets, exact Overview specification, plugin hooks, private transport, browser adapter, and safe context envelope.
2. Register a top-level read-authorized AgentPress page and enqueue UI/WebMCP assets only on its hook.
3. Fetch private definitions/context with the existing nonce manager, register current tools, and render deterministic loading/active/degraded/error/no-tool models.
4. Build the approved Ink Navy/Cyan/Lilac control-room visual direction with responsive, keyboard, and reduced-motion behavior; no chatbot.
5. Add pure component-model tests plus real WordPress menu/enqueue/role/runtime evidence for Administrator, Author, and Subscriber.
6. Run prior regressions and repository/package gates, then publish one issue-linked PR and require exact-head/merge-head success.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator verifies all evidence. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | PRD/implementation specification at baseline | 2026-09-01 | Required tabs/states/count/capability/blocked-area boundaries. | Project contract. |
| S2 | `SOURCE_VERIFIED` | approved identity-system asset/ledger | 2026-09-01 | Ink Navy, Slate, Cyan, Lilac, Green and 12–16px radius are approved direction. | Asset approval is not UI implementation evidence. |
| S3 | `SOURCE_VERIFIED` | private transport/context/browser adapter source at baseline | 2026-09-01 | Current-user data and imperative WebMCP registration interfaces exist. | Runtime behavior requires current tests. |
| S4 | `SOURCE_VERIFIED` | [WordPress Advanced Administration Handbook: automatic background updates](https://developer.wordpress.org/advanced-administration/upgrade/upgrading/) | 2026-09-01 | A pinned development environment can disable automatic background updates with `AUTOMATIC_UPDATER_DISABLED`. | Applied only to the disposable wp-env fixture. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-01T17:51:21+07:00 | Capture preflight, read frontend-design skill, inspect specs/assets/runtime source | repository | exit 0 | Clean baseline and approved visual/functional boundaries recorded. |
| timestamp not independently captured | Open issue #34 and branch `ap-019-admin-overview` | GitHub/repository | success | Isolated task created before product mutation. |
| 2026-09-01T18:03:07+07:00 | Activate plugin and run `ap019-admin-overview.php` in wp-env | WordPress 6.9/PHP 8.0 | exit 1 | Role checks reached the module assertion, then the harness called a nonexistent `WP_Script_Modules::get_enqueued_script_modules()` method. |
| 2026-09-01T18:03:31+07:00 | Inspect `get_class_methods( wp_script_modules() )` | WordPress 6.9/PHP 8.0 | exit 0 | Public queue inspection method is `get_queue()`. |
| 2026-09-01T18:04:00+07:00 | Rerun `ap019-admin-overview.php` with `get_queue()` | WordPress 6.9/PHP 8.0 | exit 0 | Administrator/Author/Subscriber exposed 15/12/8 definitions; six blocked areas; zero asset, email, and chatbot leaks; logged-out denied. |
| 2026-09-01T18:07:00+07:00 | Authenticate and render AP-019 with Playwright CLI | `http://localhost:8888/wp-admin/admin.php?page=agentpress` | page reached error/retry state | Both core `/wp-json/wp/v2/users/me` and AgentPress discovery returned Apache HTML 404; product accurately surfaced the parse error. |
| 2026-09-01T18:08:49+07:00 | Run `wp rewrite flush --hard` | disposable wp-env | exit 1 | WordPress core update collision (`register_block_pattern()` redeclaration) showed the pinned environment had been mutated by a background core update; maintenance mode reappeared. |
| timestamp not independently captured | Add `AUTOMATIC_UPDATER_DISABLED`, destroy only the disposable environment, rebuild, activate, and verify REST index | repository/wp-env | exit 0 | Constant value `1`; `/wp-json/` returned 200 with `agentpress/v1`; role matrix remained 15/12/8. |
| timestamp not independently captured | Render, interact, resize, inspect console, and capture screenshots with Playwright CLI | local wp-admin | exit 0 | Degraded Overview rendered; Changes/Activity and keyboard Enter worked; 1280px and 768px captures reviewed; zero product console errors. |
| timestamp not independently captured | `npm.cmd run test:browser` and AP-019 WordPress integration | repository/wp-env | exit 0 | 18/18 browser tests; Administrator/Author/Subscriber 15/12/8; six blocked areas and zero leaks. |
| timestamp not independently captured | PHP unit, PHPCS, provenance, package, and critical integration chain | repository/wp-env | exit 0 after recorded corrections | 68 tests/593 assertions; PHPCS 42/42; 55 ZIP entries; ten integration scripts green. |
| 2026-09-01T21:43:47+07:00 | Commit verified implementation | repository | exit 0 | `12a75ca35961151eff00510bd61f7f885b67d1e3`. |
| 2026-09-01T21:50:37+07:00 | Verify exact PR head | GitHub Actions | success | PR #35 head `99dfbdf`; run `33521943562`; job `99903135199`; no reviews, inline comments, or conversation comments. |
| 2026-09-01T21:52:57+07:00 | Merge PR #35 and verify issue closure | GitHub | success | Merge `cb70053fd30191feae891ab0a7a158375d51ba32`; issue #34 closed at `2026-09-01T14:52:58Z`. |
| 2026-09-01T21:53:27+07:00 | Verify exact merge head | GitHub Actions | success | Run `33522249708`; job `99904157486`; all repository steps passed on `cb70053`. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | The repository has no admin shell yet; it has a tested browser adapter, private definitions/execute routes, and safe context service. | source inspection | Supports a narrow page-scoped implementation. |
| O2 | `OBSERVED` | The real WordPress matrix exposed 15/12/8 permitted definitions to Administrator/Author/Subscriber, kept all six R3 areas separate, loaded two assets only on AgentPress, leaked no email/chatbot text, and denied logged-out access. | `ap019-admin-overview.php` | Supports role accuracy and page isolation. |
| O3 | `OBSERVED` | Headless Chrome without `document.modelContext` rendered a degraded state with 15 permitted definitions, zero exposed tools, HTTPS/WebMCP attention indicators, and zero console errors. | desktop/compact screenshots and Playwright snapshots | Supports honest environmental diagnostics rather than a false active claim. |
| O4 | `OBSERVED` | Changes and Activity have explicit non-action placeholders; mouse and keyboard Tab/Enter navigation selected them; 1280px and 768px layouts remained legible. | Playwright interaction and EXP-023 captures | Supports the AP-019 shell boundary and responsive manual-test checkpoint. |
| O5 | `OBSERVED` | Browser, PHP unit, PHPCS, provenance/package, and ten critical integration scripts passed after the recorded corrections. | command log | Supports regression compatibility at implementation commit `12a75ca`. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| F1 | The integration harness can inspect the enqueued module through a guessed getter. | WordPress 6.9 has no `get_enqueued_script_modules()` method; the run terminated at that assertion after earlier role/style assertions passed. | Test-harness API mismatch; product runtime was not implicated. | Replaced it with observed public `get_queue()`; all later role runs passed. |
| F2 | A newly started pinned wp-env remains on WordPress 6.9 for browser verification. | A background core update reactivated maintenance mode and left colliding core declarations; Apache also lacked the rewrite file required by the saved pretty-permalink setting. | Disposable environment drift, directly observed; not attributed to AP-019. | Pinned with `AUTOMATIC_UPDATER_DISABLED`; clean rebuild verified WordPress 6.9, constant `1`, REST 200, and no recurring maintenance. |
| F3 | The HTTP wp-env Overview can call the `get-context` Ability and then show HTTPS as a degraded diagnostic. | Discovery returned 200, but `get-context` returned audited `AP_INTERNAL_ERROR`: its intentionally HTTPS-only output schema rejects the local HTTP `home_url`. | Product integration mismatch between an HTTP diagnostic state and an HTTPS-only Site Tools contract. | Safe server bootstrap implemented; corrected HTTP page rendered degraded while registered tools retained private execution transport. |
| F4 | The context-presence assertion should match HTML entity-encoded JSON keys. | `JSON_HEX_QUOT` does not transform structural JSON quotes, so the otherwise-correct integration run failed only at the guessed `&quot;context&quot;` substring. | Test-harness serialization mismatch. | Corrected to observed structural JSON; later runs passed. |
| F5 | Provenance and ZIP build gates can run concurrently on Windows. | Both processes replaced `dist/agentpress.zip`; provenance hit `EPERM` while the explicit build completed. | Test orchestration collision, not a source failure. | Sequential provenance rerun passed with 55 entries and no upstream runtime code. |
| F6 | Manual alignment added around the new `Plugin` assignments matches PHPCS. | PHPCS reported two fixable alignment warnings and exit 2. | Formatting failure. | Restored single spaces; PHPCS passed 42/42. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Use WordPress system typography and the approved palette; signature is a control-circuit capability matrix, not generic stat cards. | S1/S2 | Native admin cohesion over custom webfonts. | Owner changes approved direction. |
| D2 | `DECIDED` | Changes/Activity tabs appear as clearly labelled upcoming surfaces in AP-019; their data/actions remain AP-020/AP-024/AP-025. | scope/S1 | Manual navigation exists before those services. | Dependent tasks land. |
| D3 | `DECIDED` | Bootstrap the same safe `ContextService` result into the authenticated page settings instead of creating an audited Ability execution merely by viewing wp-admin. Discovery and all registered-tool execution still use the private nonce-protected transport. | F3 and AP-019's live-context requirement | The Overview can report HTTP/WebMCP degradation without weakening the HTTPS-only `get-context` Ability output contract or manufacturing an activity event. | A dedicated non-audited admin read route becomes necessary. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Page/menu/access and page-only assets | Direct WordPress enqueue/render matrix, logged-out control, browser route | `PASS` | two page assets; zero off-screen leaks; logged-out denied. |
| Loading/active/degraded/error/no-tool models | Four model tests; server skeleton; observed error/retry during F2; real degraded browser | `PASS` for component contracts and local degraded/error; live active browser `NOT_TESTED` | 18 browser tests and Playwright snapshots. |
| Role-specific counts/capability/blocked separation | Administrator/Author/Subscriber model tests plus real discovery/context matrix | `PASS` | 15/12/8 definitions; six blocked areas; blocked list excluded from counts. |
| Responsive/keyboard/reduced-motion/no-chatbot | 1280px/768px visual inspection; Tab+Enter; reduced-motion CSS; HTML/runtime sentinel | `PASS` at tested widths; assistive technology `NOT_TESTED` | two screenshots; zero chatbot matches. |
| Prior regressions and repository/package gates | PHP unit, PHPCS, provenance, AP-004/007/008/011/012/013/014/015/017/019 | `PASS` | 68 tests/593 assertions; 42 PHPCS files; ten integration scripts; 55 ZIP entries. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-023-admin-overview.md` | evidence | committed | `99dfbdf` | Opened before product mutation and closed after verification. |
| `agentpress/includes/Admin/AdminPage.php` and `agentpress/admin/src/admin-overview.*` | implementation | committed | `12a75ca` | Page-scoped server shell, safe context bootstrap, browser model/UI. |
| `agentpress/tests/integration/ap019-admin-overview.php` and `agentpress/tests/js/admin-overview.test.mjs` | executable evidence | committed | `12a75ca` | Three-role WordPress and component state/count contracts. |
| `dist/agentpress.zip` | generated package check | ignored, not a release | SHA-256 `9dc467ed2f9ad523b03979bbd71d3c7850f3b205eccacc90f5d328b2d141ab95` | 102,945 bytes; 55 entries; AP-030 release verification remains separate. |
| `docs/evidence/assets/EXP-023/ap019-overview-desktop.png` | screenshot | committed | SHA-256 `7218086cf1b2939147bc582265d046c65b598a9efaa8b11069f789a2b0299312` | Synthetic Administrator, 1280px, HTTP degraded state; evidence commit `99dfbdf`. |
| `docs/evidence/assets/EXP-023/ap019-overview-compact.png` | screenshot | committed | SHA-256 `1da72f48469db63c0957ac145e08cef17e2dd006e2e7fe2ca19d070efb51a456` | Synthetic Administrator, 768px, HTTP degraded state; evidence commit `99dfbdf`. |
| issue #34 | task | closed | https://github.com/MrBigleg/AgentPress/issues/34 | Closed by PR #35 after hosted merge verification. |
| PR #35 | merge evidence | merged | `99dfbdf`; merge `cb70053` | Exact-head run `33521943562`; merge-head run `33522249708`; both successful. |

## Result

`SUPPORTED`

The hypothesis is supported for AP-019's repository and local wp-env scope. A page-scoped wp-admin shell now reports the current safe identity/capability envelope, current permitted definition count, actual WebMCP exposure count, automatic/approval outcomes, and separate blocked areas. It handles loading, error/retry, degraded, active-model, and no-tool-model states without a chatbot or second authorization path. The real HTTP test correctly remained degraded because HTTPS and `document.modelContext` were absent.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: a real HTTPS browser with `document.modelContext`, the real ChatGPT Site Tools client, deployed runtime, assistive-technology audit, AP-024 approval UI, AP-025 live activity, and the AP-031 five-run reliability gate.
- The 768px capture is a compact desktop viewport, not a physical mobile-device claim.
- The generated ZIP is a package regression artifact, not AP-030 install/reproducibility/release evidence.

## Competition evidence statement

- work attributable to challenge period: exact timestamps/baseline recorded;
- pre-existing work distinguished by: verified AP-017 closeout baseline;
- third-party material/license/pin: no new third-party runtime; existing pin/license verifier passed;
- commit/PR evidence: implementation `12a75ca`; evidence `99dfbdf`; PR #35 merged as `cb70053`; issue #34 closed; exact-head and merge-head hosted gates passed;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: AP-021 after the AP-019 manual-test checkpoint; AP-020 remains the other UI prerequisite;
- next falsifiable question: can a bounded classic `primary` menu snapshot and deterministic state hash reflect every semantic menu change while rejecting unsupported/unassigned navigation without mutation?;
- required prerequisites: AP-019 exact-head and merge-head hosted gates.

## End state

```text
git status --short --branch: clean main synchronized at cb70053 before this merge-evidence correction
tests/checks: browser 18/18; PHP unit 68/593; PHPCS 42/42; provenance 55 entries; ten critical integrations PASS
committed: implementation 12a75ca35961151eff00510bd61f7f885b67d1e3
pushed: AP-019 branch and PR #35; merged to origin/main
deployed: no
```
