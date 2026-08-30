# AgentPress v0.1 Build Checklist

- **Derived from:** [Implementation Specification](./IMPLEMENTATION_SPEC.md)
- **Milestone:** `v0.1`
- **Deadline:** 3 September 2026, 1:00 p.m. PDT / 4 September 2026, 3:00 a.m. Asia/Bangkok
- **Rule:** one issue owns one bounded deliverable; merge only with its acceptance test green.

## 1. How to execute this checklist

- Create one GitHub issue for each `AP-###` item and add it to milestone `v0.1`.
- Preserve the task ID and dependency list in the issue body.
- Add one additive difficulty label: `difficulty: XS`, `difficulty: S`, `difficulty: M`, or `difficulty: L`.
- Work in dependency order. An issue may start only when all `Depends on` items are merged.
- Mark an item complete only after its acceptance test is reproducible from the repository.
- `Demo critical: yes` means the three-minute challenge path cannot succeed without it.
- Do not add non-goal issues to `v0.1`; record post-challenge ideas outside the active milestone.

## 2. P0 — platform, security, and safe content path

### AP-001 — Create the WordPress plugin scaffold

- [x] **Difficulty:** S
- **Depends on:** none
- **Demo critical:** yes
- **Deliverable:** plugin entrypoint, PSR-4 autoloading, minimum-version guard, npm/Composer scripts, PHPCS, PHPUnit, `wp-env`, build output policy, and an installable ZIP script.
- **Acceptance test:** on a clean checkout, documented commands install dependencies, boot WordPress 6.9, activate AgentPress, and show no PHP notice/fatal; WordPress 6.8 and PHP 7.4 fail closed with one admin notice.

### AP-002 — Pin and attribute the WebMCP bridge source

- [x] **Difficulty:** XS
- **Depends on:** AP-001
- **Demo critical:** yes
- **Deliverable:** pin audited upstream commit, list copied/adapted files or concepts, include GPL-2.0-or-later attribution in `THIRD_PARTY_NOTICES.md`, and exclude upstream built-ins/settings/public discovery.
- **Acceptance test:** repository contains a machine-readable pin and attribution; license scan identifies AgentPress and third-party GPL-compatible licensing; built ZIP contains notices but no upstream generic tools.

### AP-003 — Implement the current browser registration adapter

- [x] **Difficulty:** M
- **Depends on:** AP-002
- **Demo critical:** yes
- **Deliverable:** feature-detected `document.modelContext.registerTool`, fixed Ability-to-WebMCP name map, per-tool AbortController, direct structured results, annotations, and cancellation passed to fetch.
- **Acceptance test:** a browser unit test stubs `document.modelContext`, observes all supplied definitions registered with no slash-containing names, aborts one registration cleanly, and confirms no call to `navigator.modelContext` or `provideContext` exists in built code.

### AP-004 — Implement private same-origin WebMCP REST transport

- [x] **Difficulty:** M
- **Depends on:** AP-001, AP-003
- **Demo critical:** yes
- **Deliverable:** private tool-list and execute routes, `wp_rest` nonce on every request, fixed AgentPress allowlist, same-origin credentials, no shared/localStorage tool cache, size caps, and one nonce-refresh retry.
- **Acceptance test:** authenticated requests with a valid `wp_rest` nonce work; missing/wrong nonce, logged-out cookie, non-AgentPress ability name, oversized body, and cross-origin request fail without ability execution; tool responses use `private, no-store`.

### AP-005 — Add database migrations and repositories

- [x] **Difficulty:** M
- **Depends on:** AP-001
- **Demo critical:** yes
- **Deliverable:** the three specified tables, versioned idempotent `dbDelta` migration, typed repositories, UTC dates, prepared SQL, preserve-on-deactivation/uninstall default.
- **Acceptance test:** activation creates exact columns/indexes; rerunning migration changes nothing; repository CRUD round-trips bounded JSON; deactivation preserves rows; default uninstall policy preserves rows.

### AP-006 — Implement common schemas and error normalization

- [ ] **Difficulty:** M
- **Depends on:** AP-001
- **Demo critical:** yes
- **Deliverable:** closed schema builders, successful result envelope, `AP_*` error factory/HTTP map, safe message policy, and output validation for every registered Ability.
- **Acceptance test:** parameterized tests prove unknown fields, wrong types, out-of-range values, oversized content, unsupported enums, and invalid operation combinations fail with `AP_SCHEMA_INVALID`; every declared error serializes to the documented shape.

### AP-007 — Implement Safe Mode and discovery policy

- [ ] **Difficulty:** M
- **Depends on:** AP-006
- **Demo critical:** yes
- **Deliverable:** R0–R3 classifier, actual-capability envelope, coarse per-tool discovery rules, object-specific execution rules, and a hard absence of users/plugins/themes/code/settings abilities.
- **Acceptance test:** a capability matrix for Administrator, Editor, Author, Subscriber, logged-out, and a custom capability-mutated user matches actual WordPress capabilities; no R3 tool is registered or executable by route guessing.

### AP-008 — Register the 15 Abilities and fixed tool-name map

- [ ] **Difficulty:** S
- **Depends on:** AP-006, AP-007
- **Demo critical:** yes
- **Deliverable:** category at `wp_abilities_api_categories_init`, Abilities at `wp_abilities_api_init`, exact descriptions/schemas/meta, `meta.show_in_rest=false`, and explicit collision-free WebMCP names.
- **Acceptance test:** integration test retrieves exactly 15 `agentpress/*` Abilities after init, validates every category/callback/schema/annotation, confirms native Abilities REST does not list/run them, and confirms the bridge exposes only mapped current-user tools.

### AP-009 — Implement sanitized audit logging

- [ ] **Difficulty:** M
- **Depends on:** AP-005, AP-006
- **Demo critical:** yes
- **Deliverable:** request IDs, argument sanitizer, authenticated denial/success/failure/replay events, bounded content previews/hashes, and explicit no-secret list.
- **Acceptance test:** fixture requests containing cookie, nonce, password-like fields, headers, and a 200 KB body produce a bounded row containing none of those secrets; authenticated direct denials are recorded; logged-out/invalid-nonce traffic creates no durable row.

### AP-010 — Implement Change Set coordinator and idempotency

- [ ] **Difficulty:** L
- **Depends on:** AP-005, AP-007, AP-009
- **Demo critical:** yes
- **Deliverable:** default/reused Change Sets, R1 intent-before-mutation, R2 immutable proposals, state reducer, idempotency scope, target/proposal hashes, expiry, and failure recovery.
- **Acceptance test:** repeated identical key returns the first result with one mutation; same key/different payload conflicts; storage failure prevents mutation; child transitions yield every documented Change Set state.

### AP-011 — Implement `get-context`

- [ ] **Difficulty:** S
- **Depends on:** AP-008
- **Demo critical:** yes
- **Deliverable:** safe site/user/bootstrap output and effective operation envelope.
- **Acceptance test:** each fixture user receives the correct operation states; output omits email, raw capabilities, cookies, nonces, paths, plugin config, and environment details; logged out is denied.

### AP-012 — Implement `get-site-structure`

- [ ] **Difficulty:** S
- **Depends on:** AP-008
- **Demo critical:** yes
- **Deliverable:** bounded page hierarchy, counts, category/tag definitions, classic menu locations, truncation indicator, and object visibility filtering.
- **Acceptance test:** private/unreadable objects are absent for restricted users, hierarchy and counts match fixtures, output stops at its cap, and content is marked untrusted.

### AP-013 — Implement `list-content` and `get-content`

- [ ] **Difficulty:** M
- **Depends on:** AP-008
- **Demo critical:** yes
- **Deliverable:** bounded/paginated post/page discovery plus object-specific retrieval and term assignments.
- **Acceptance test:** filters and pagination are deterministic; every returned object passes `read_post`; Author cannot fetch another user's unreadable draft by direct ID; unsupported post types and oversized results fail or truncate as specified.

### AP-014 — Implement `list-terms`

- [ ] **Difficulty:** S
- **Depends on:** AP-008
- **Demo critical:** yes
- **Deliverable:** paginated category/tag reads with fixed taxonomy allowlist.
- **Acceptance test:** categories/tags match fixtures, search and pagination work, unsupported/custom taxonomies are rejected, and Subscriber receives only allowed read data.

### AP-015 — Implement `create-draft`

- [ ] **Difficulty:** M
- **Depends on:** AP-010, AP-013
- **Demo critical:** yes
- **Deliverable:** post/page draft creation, forced status, parent validation, KSES handling, Change Set linkage, and idempotent result.
- **Acceptance test:** Administrator/Editor create post/page drafts; Author creates post but direct page creation fails; Subscriber/logged out fail; manipulated `status`, unknown fields, or alternate post type cannot publish/create; repeated key creates one post.

### AP-016 — Implement `update-content`

- [ ] **Difficulty:** M
- **Depends on:** AP-010, AP-015
- **Demo critical:** no
- **Deliverable:** bounded field patch, no taxonomy/status field, automatic AgentPress-draft update, and immutable staging for all other targets.
- **Acceptance test:** AgentPress draft applies; pre-existing draft and published post remain unchanged and return pending approval; ownership/parent checks hold; an empty patch fails schema.

### AP-017 — Implement `assign-terms`

- [ ] **Difficulty:** M
- **Depends on:** AP-010, AP-014, AP-015
- **Demo critical:** yes
- **Deliverable:** append/replace existing categories/tags on AgentPress-created post drafts, with R2 staging elsewhere.
- **Acceptance test:** canonical draft receives existing category once; invalid/mixed taxonomy IDs cause no partial assignment; Author can assign permitted existing category to own draft but not another user's; published content is unchanged pending approval.

### AP-018 — Build P0 authorization regression suite

- [ ] **Difficulty:** L
- **Depends on:** AP-004, AP-007, AP-008, AP-011–AP-017
- **Demo critical:** yes
- **Deliverable:** parameterized discovery plus direct-execution tests for Administrator, Editor, Author, Subscriber, logged-out, invalid nonce, expired session, and capability mutation.
- **Acceptance test:** all mandatory PRD security cases pass; each forbidden attempt asserts both the error and zero WordPress/AgentPress target mutation; native generic Abilities REST cannot bypass the bridge.

## 3. P1 — human approval, navigation, collaboration, and challenge release

### AP-019 — Build the AgentPress admin shell and Overview

- [ ] **Difficulty:** M
- **Depends on:** AP-004, AP-007, AP-011
- **Demo critical:** yes
- **Deliverable:** top-level page, tabs, loading/error/degraded/active states, bridge diagnostic, actual tool counts, capability matrix, and separate blocked areas.
- **Acceptance test:** visual/component tests cover all states for Administrator, Author, and Subscriber; blocked areas are not counted as exposed tools; no chatbot appears.

### AP-020 — Implement Change Set and Activity read services

- [ ] **Difficulty:** M
- **Depends on:** AP-009, AP-010
- **Demo critical:** yes
- **Deliverable:** `get-change-set`, `list-change-sets`, `get-agent-activity`, admin read routes, ownership filtering, pagination, and bounded semantic summaries.
- **Acceptance test:** users see own rows, Administrator sees all, unauthorized guessed IDs do not disclose existence, pagination is stable, and no raw proposal body/secret appears in activity.

### AP-021 — Implement `get-navigation` classic adapter

- [ ] **Difficulty:** M
- **Depends on:** AP-008
- **Demo critical:** yes
- **Deliverable:** classic `primary` menu detection, normalized bounded snapshot, item hierarchy, and deterministic state hash.
- **Acceptance test:** fixture snapshot exactly matches Home/About/Blog/Contact; relabel/move/add/remove/location reassignment changes the hash; block/unassigned navigation returns the specified error.

### AP-022 — Implement `stage-navigation-change`

- [ ] **Difficulty:** L
- **Depends on:** AP-010, AP-021
- **Demo critical:** yes
- **Deliverable:** add/remove/move validator, same-origin custom URL rule, before/after semantic preview, immutable R2 proposal, and no direct menu mutation.
- **Acceptance test:** staging Services between About and Blog returns the exact preview while the live menu remains unchanged; invalid IDs, child removal, unsafe URL, and unauthorized role fail without a proposal/mutation.

### AP-023 — Implement approval and rejection service/routes

- [ ] **Difficulty:** L
- **Depends on:** AP-010, AP-016, AP-022
- **Demo critical:** yes
- **Deliverable:** explicit human endpoints, conditional claim, proposal/expiry/state revalidation, current capability recheck, narrow executors, audit, and replay/concurrency handling.
- **Acceptance test:** valid navigation approval changes the menu once; rejection changes nothing; expired, tampered, stale, permission-lost, duplicate, and concurrent approvals execute zero or at most one mutation as appropriate.

### AP-024 — Build Changes list/detail and approval UI

- [ ] **Difficulty:** L
- **Depends on:** AP-019, AP-020, AP-023
- **Demo critical:** yes
- **Deliverable:** list/detail states, semantic diffs, explicit Approve/Reject, disabled in-flight actions, expired/conflict/failure messages, and current-state refresh.
- **Acceptance test:** component/E2E tests cover loading, empty, error, pending, approve, reject, permission loss, conflict, expired, and double-click; no approval can be submitted without a user click and valid nonce.

### AP-025 — Build Activity UI and visible collaboration polling

- [ ] **Difficulty:** M
- **Depends on:** AP-019, AP-020
- **Demo critical:** yes
- **Deliverable:** Activity table/filters, `/updates` cursor polling, tab pending count, draft/staged/applied notices, visibility pause, and escaped details.
- **Acceptance test:** a tool call appears without full-page reload within 10 seconds; background tabs stop polling; duplicate polls add no duplicate event; activity fields are escaped and secrets remain absent.

### AP-026 — Implement `publish-content`

- [ ] **Difficulty:** M
- **Depends on:** AP-015, AP-023
- **Demo critical:** no
- **Deliverable:** always-staged publish proposal and narrow approval executor.
- **Acceptance test:** tool call never publishes; authorized approval publishes once; Author cannot stage/approve another user's post; current publish capability is rechecked; already-published/stale state conflicts.

### AP-027 — Create deterministic challenge fixture and reset

- [ ] **Difficulty:** M
- **Depends on:** AP-015, AP-017, AP-021
- **Demo critical:** yes
- **Deliverable:** credible small-business site, required pages/posts/categories, classic `primary` menu, Administrator/Author accounts, and documented reset command/script with no real credentials committed.
- **Acceptance test:** reset produces the same IDs/structure or a machine-readable fixture map, canonical preconditions pass, and credentials are supplied only through secure judging/setup instructions.

### AP-028 — Verify ChatGPT Site Tools discovery and execution

- [ ] **Difficulty:** M
- **Depends on:** AP-003, AP-004, AP-018, AP-019, AP-027
- **Demo critical:** yes
- **Deliverable:** live HTTPS test, supported ChatGPT desktop account/model confirmation, automatic discovery, current-user switch, structured call evidence, and issue log for client-specific behavior.
- **Acceptance test:** address-bar tool indicator appears on the AgentPress page; ChatGPT calls `agentpress_get_context` and `agentpress_create_draft`; switching to Author changes the available/effective envelope; inspector-only success does not close the issue.

### AP-033 — Implement `create-term`

- [ ] **Difficulty:** M
- **Depends on:** AP-014, AP-023
- **Demo critical:** no
- **Deliverable:** staged category/tag creation with parent validation and narrow approval executor.
- **Acceptance test:** tool call creates no term; authorized approval creates one; duplicate slug/name, wrong taxonomy, invalid parent, stale proposal, and unauthorized role cause no term creation.

### AP-029 — Run the full security and approval integration gate

- [ ] **Difficulty:** L
- **Depends on:** AP-018, AP-022, AP-023, AP-026, AP-033
- **Demo critical:** yes
- **Deliverable:** all specified role/direct-call/nonce/rate/schema/idempotency/stale/concurrency/audit cases in CI.
- **Acceptance test:** the complete suite is green on a clean WordPress 6.9 environment and each security assertion checks zero unauthorized mutation, not just status code.

### AP-030 — Package one installable release ZIP

- [ ] **Difficulty:** S
- **Depends on:** AP-002, AP-029
- **Demo critical:** yes
- **Deliverable:** production assets, no development/vendor-test junk, root license/third-party notices/readme, reproducible checksum, install/upgrade instructions.
- **Acceptance test:** upload ZIP to a clean site, activate with no separate plugin/account/configuration, discover tools, execute the smoke workflow, and compare two builds for a documented reproducibility result.

### AP-031 — Run and record the 5/5 reliability gate

- [ ] **Difficulty:** L
- **Depends on:** AP-024, AP-025, AP-027, AP-028, AP-030
- **Demo critical:** yes
- **Deliverable:** five consecutive canonical runs with fixture reset, request IDs, timings, role denial, target verification, and failure ledger.
- **Acceptance test:** all five runs pass without code/config changes; any failure resets the count after its fix; basic read/write server timings meet the PRD target or have a documented hosting-only exception.

### AP-032 — Publish challenge-ready repository, live URL, and submission evidence

- [ ] **Difficulty:** M
- **Depends on:** AP-030, AP-031
- **Demo critical:** yes
- **Deliverable:** public repo with detectable GPL license and dated WebMCP commits, working judge URL/credentials, English description, setup instructions, and public sub-three-minute YouTube video with audio.
- **Acceptance test:** an unaffiliated reviewer can use the submitted URL in the supported browser, install from source/ZIP, distinguish hackathon-period work, view the license at repo top, and complete the depicted workflow before the deadline.

## 4. P2 — polish

P2 starts only when AP-031 is green. P2 work must not destabilize the recorded demo path.

### AP-034 — Add non-demo visual polish and accessibility

- [ ] **Difficulty:** S
- **Depends on:** AP-024, AP-025
- **Demo critical:** no
- **Deliverable:** focus management, screen-reader labels, keyboard flow, responsive tables/diffs, reduced motion, and WordPress-native visual cleanup.
- **Acceptance test:** automated accessibility scan has no critical violations; Approve/Reject and tabs work by keyboard at common wp-admin widths.

### AP-035 — Add performance budgets and query diagnostics

- [ ] **Difficulty:** S
- **Depends on:** AP-029
- **Demo critical:** no
- **Deliverable:** server timing assertions for representative reads/writes, query-count budgets, indexes verified by representative queries, and bounded polling diagnostics.
- **Acceptance test:** fixture reads target <500 ms and writes <1 second server processing on the test environment; regressions fail with actionable timing/query output.

### AP-036 — Final documentation and release notes

- [ ] **Difficulty:** S
- **Depends on:** AP-030, AP-033 if included
- **Demo critical:** no
- **Deliverable:** user install/use guide, developer architecture link, supported navigation statement, security disclosure path, known limitations, changelog, and exact v0.1 tool table.
- **Acceptance test:** docs match the built ZIP/tool registry, explicitly state classic-menu-only support and Site Tools account gating, and contain no claim that inspector testing equals ChatGPT acceptance.

## 5. Challenge-critical dependency path

This is the minimum path from empty repository to the reliable three-minute demo:

```text
AP-001 scaffold
  -> AP-002 bridge pin/attribution
  -> AP-003 current document.modelContext adapter
  -> AP-004 private WordPress-session transport
  -> AP-006 schemas/errors
  -> AP-007 policy/discovery
  -> AP-008 15-Ability registry

AP-001 -> AP-005 storage -> AP-009 audit -> AP-010 Change Sets/idempotency

AP-008 -> AP-011 context
       -> AP-012 structure
       -> AP-013 content reads
       -> AP-014 terms

AP-010 + AP-013 -> AP-015 draft creation
AP-010 + AP-014 + AP-015 -> AP-017 term assignment
AP-008 -> AP-021 navigation read
AP-010 + AP-021 -> AP-022 navigation staging
AP-010 + AP-022 -> AP-023 approval execution

AP-019 admin shell
  + AP-020 collaboration reads
  + AP-023 -> AP-024 approval UI
  + AP-020 -> AP-025 visible activity

AP-015 + AP-017 + AP-021 -> AP-027 fixture
AP-018 authorization suite + AP-019 + AP-027 -> AP-028 real ChatGPT gate
AP-023 + AP-029 security gate -> AP-030 ZIP
AP-024 + AP-025 + AP-027 + AP-028 + AP-030 -> AP-031 5/5
AP-031 -> AP-032 submission
```

If the deadline forces a challenge-demo cut, cut AP-016, AP-026, AP-033, and all P2 tasks before cutting authorization, Change Sets, classic navigation staging, approval, audit, Administrator-versus-Author proof, real ChatGPT verification, packaging, or the 5/5 gate. A cut ability must be removed from registration and the documented count; never expose a stub. The recorded demo does not require editing existing content, publishing, or creating a new term, but the full 15-tool v0.1 release does.

## 6. Five-run reliability checklist

Use a fresh checklist for each run. Do not reuse a dirty fixture.

### Before each run

- [ ] Reset fixture and verify Home/About/Blog/Contact menu.
- [ ] Verify Services and the two demo drafts do not exist.
- [ ] Verify expected categories exist.
- [ ] Verify no pending Change Set from a prior run.
- [ ] Verify HTTPS, AgentPress health, current `document.modelContext`, ChatGPT Site Tools access, and valid Administrator session.
- [ ] Start a run record with run number, build checksum, browser/app version, start time, and request-ID slots.

### Administrator path

- [ ] ChatGPT discovers the AgentPress Site Tools on the AgentPress wp-admin page.
- [ ] `get-context` identifies the Administrator and correct envelope.
- [ ] `get-site-structure` reports the fixture accurately.
- [ ] One Services page draft and two post drafts are created in one Change Set.
- [ ] Existing categories are assigned; no new term is created.
- [ ] No content is published.
- [ ] The live menu is unchanged after staging Services between About and Blog.
- [ ] Changes UI displays the exact semantic before/after menu order.
- [ ] One explicit human Approve click changes the live menu exactly once.
- [ ] Change Set and Activity show all safe, staged, and approved work with no secrets.
- [ ] UI updates within 10 seconds without a full reload.

### Author denial path

- [ ] Sign out and sign in as Author inside the ChatGPT built-in browser.
- [ ] Context reports post-draft ability but page/navigation unavailable.
- [ ] Natural-language request for a page/navigation is refused or safely limited.
- [ ] Direct `create-draft(post_type=page)` attempt returns permission denial.
- [ ] Direct navigation read/stage route attempt returns permission denial or undiscoverable-tool failure.
- [ ] No page, menu item, pending change, or unauthorized audit payload is created.
- [ ] The authenticated denial appears as a sanitized activity event where applicable.

### Run closeout

- [ ] Record end time, server timings, all request IDs, and outcome.
- [ ] Verify post/page/menu/term counts against expected deltas.
- [ ] Verify no PHP/JS errors and no failed AgentPress rows.
- [ ] Mark PASS only if every item above passed without intervention or code/config change.
- [ ] On any failure, log reproduction and evidence, fix it, reset the consecutive-pass counter to zero, and restart.

## 7. Explicitly excluded from milestone `v0.1`

Do not create active milestone issues for WooCommerce, Elementor, Divi, ACF, Yoast, Rank Math, forms, media generation/uploads, multisite, user/plugin/theme administration, theme/code editing, arbitrary settings, shell/SQL/PHP execution, scheduled agents, background queues, classic/remote MCP, ChatGPT skills/plugins, vector databases, proprietary AI, billing, hosting, policy-profile builders, block Navigation compatibility, or third-party plugin adapters.
