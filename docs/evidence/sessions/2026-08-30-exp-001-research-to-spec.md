# EXP-001 — PRD research to implementation-ready specification

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-001` |
| Related task | planning precursor to `AP-001`–`AP-036` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T08:32:48+07:00` |
| Started UTC | `2026-08-30T01:32:48Z` |
| Ended local | `2026-08-30T08:57:00+07:00` |
| Ended UTC | `2026-08-30T01:57:00Z` |
| Duration | 24 minutes 12 seconds |
| Agent/operator | Codex primary synthesis plus three read-only research agents |
| Branch | `main` |
| Baseline commit | `835a8d241519544053a8c90b9047c665727b92bf` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows/PowerShell repository inspection; primary web sources accessed 2026-08-30; no WordPress/browser runtime |

## Question

Can AgentPress v0.1 be reduced to a small, permission-safe architecture that matches current WordPress Abilities and WebMCP behavior, fits the challenge constraints, and gives an engineering agent an unambiguous dependency-ordered build plan?

## Hypothesis

PRD v2's product direction is sound. Existing WordPress/WebMCP open-source work can contribute generic patterns, but the Ability count, approval storage, navigation target, browser API, authentication behavior, and test gates need source-level validation before the plugin is scaffolded.

## Falsification condition

The hypothesis would be falsified if current primary sources contradicted the browser-session/Abilities architecture, if no safe 15-or-fewer tool catalog could cover the canonical workflow, or if unavoidable unresolved decisions still blocked a scaffold after the specification.

A subordinate assumption—that `webmcp-abilities` could be installed or vendored largely unchanged—would be falsified by a current browser API, authentication, wp-admin, security, or license-packaging incompatibility.

## Controls

- Baseline commit and PRD v2 scope remained fixed.
- WordPress 6.9+, PHP 8.0+, HTTPS, and ChatGPT desktop Site Tools remained the target.
- Explicit non-goals remained excluded.
- Research agents were read-only and owned separate source/repository angles.
- Primary/official sources controlled normative claims; community lists were discovery aids only.
- No plugin code, issue, commit, deployment, or live demo mutation was authorized.

## Variables

- **Independent:** source set, upstream source version, proposed Ability grouping, bridge integration choice, navigation adapter scope, and safety-policy interpretation.
- **Dependent:** genuine blockers found, number/clarity of final contracts, completeness of storage/approval model, task dependency graph, and structural validation results.

## Preflight

```text
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
branch: main...origin/main
baseline: 835a8d241519544053a8c90b9047c665727b92bf
tracked worktree changes: none
repository contents: governance/docs only; no plugin scaffold
source PRD: docs/PRD.md, 2,036 lines
```

## Method

1. Read the goal objective, repository status/history, PRD, README, security policy, contribution guide, and license.
2. Enumerate PRD sections and inspect requirements, tool contracts, state model, storage, acceptance criteria, demo, and build order.
3. Assign three bounded read-only research tracks.
4. Inspect current WordPress, OpenAI, Chrome/WebMCP, Devpost, and upstream bridge sources.
5. Compare the PRD with source behavior and retain only code/security/packaging/demo blockers.
6. Resolve the blockers into one implementation architecture and 15 exact Ability contracts.
7. Derive bounded development tasks with dependencies and acceptance tests.
8. Validate unique counts, Markdown structure, local links, hashes, and final worktree.

## Parallel research ledger

| Worker | Bounded task | Returned evidence | Synthesis |
|---|---|---|---|
| PRD/repository explorer | Inspect local PRD and repository for requirements, contradictions, and scaffold state. | Exact PRD line references, nine genuine gaps, clean baseline, empty scaffold. | Used to define resolution table and scope the two documents. |
| WordPress researcher | Inspect current Abilities/MCP contracts, validation, permissions, REST exposure, and authentication. | Registration/execute order, metadata drift, generic REST risk, callback behavior, current source links. | Kept AgentPress Abilities off native REST and added explicit preflight/execute checks. |
| WebMCP/challenge researcher | Audit WebMCP, ChatGPT Site Tools, challenge rules, and `webmcp-abilities`. | Current `document.modelContext` contract, tool-name limits, page/session behavior, upstream commit/API/auth/wp-admin issues, challenge gates. | Replaced drop-in bridge assumption with a narrow AgentPress adapter and release evidence gates. |

## Source ledger

All sources were accessed on 2026-08-30.

| ID | Evidence class | Source | Claim supported |
|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WordPress Abilities API](https://developer.wordpress.org/apis/abilities-api/) | WordPress 6.9+ registry of typed, permission-aware functionality. |
| S2 | `SOURCE_VERIFIED` | [Abilities PHP reference](https://developer.wordpress.org/apis/abilities-api/php-reference/) and inspected current source | Registration fields/hooks, schema/callback behavior, validation, and annotations. |
| S3 | `SOURCE_VERIFIED` | [WordPress MCP Adapter article](https://developer.wordpress.org/news/2026/02/from-abilities-to-ai-agents-introducing-the-wordpress-mcp-adapter/) | Abilities are intended as protocol-independent agent functionality. |
| S4 | `SOURCE_VERIFIED` | [WordPress REST authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/) | Cookie-authenticated REST requests require the `wp_rest` nonce in `X-WP-Nonce`. |
| S5 | `SOURCE_VERIFIED` | [`webmcp-abilities` audited commit](https://github.com/code-atlantic/webmcp-abilities/commit/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6) | Reusable concepts plus obsolete browser/auth/wp-admin behavior requiring adaptation. |
| S6 | `SOURCE_VERIFIED` | [OpenAI Site Tools help](https://help.openai.com/en/articles/20001423-using-site-tools-in-the-chatgpt-desktop-app) | Tools use the current page and signed-in built-in-browser session; availability is client/account dependent. |
| S7 | `SOURCE_VERIFIED` | [Chrome WebMCP imperative API](https://developer.chrome.com/docs/ai/webmcp/imperative-api) | Current registration is `document.modelContext.registerTool` with input schema, annotations, execution, and cancellation. |
| S8 | `SOURCE_VERIFIED` | [Chrome tool security](https://developer.chrome.com/docs/ai/webmcp/secure-tools) and [best practices](https://developer.chrome.com/docs/ai/webmcp/best-practices) | Same-origin exposure, untrusted/read-only hints, clear tools, strict server validation, UI updates, and evals. |
| S9 | `SOURCE_VERIFIED` | [OpenAI challenge page](https://openai.com/webmcp-challenge/) and [Devpost rules](https://webmcp.devpost.com/rules) | Deadline and public repository/live URL/license/video/period evidence requirements. |
| S10 | secondary discovery only | [Awesome WebMCP](https://github.com/webfuse-com/awesome-webmcp) | Located ecosystem resources; not used as a normative contract. |

## Observation and contradiction ledger

| ID | Label | Observation | Effect |
|---|---|---|---|
| O1 | `OBSERVED` | PRD v2 named 17 tools but capped v0.1 at 15. | Required catalog consolidation. |
| O2 | `OBSERVED` | Safe Mode limited automatic editing to AgentPress-created drafts while `update-content` allowed any authorized draft. | Required one authoritative risk rule. |
| O3 | `OBSERVED` | PRD approval tables lacked proposal/state hashes, expiry, approver/rejector, and idempotency. | Existing schema could not enforce stated approval invariants. |
| O4 | `SOURCE_VERIFIED` | Current WebMCP uses `document.modelContext.registerTool`; audited upstream used obsolete `navigator.modelContext.provideContext`. | Drop-in client bundle assumption falsified. |
| O5 | `SOURCE_VERIFIED` | Audited upstream nonce behavior did not match WordPress REST cookie-auth requirements. | Required AgentPress-owned transport auth. |
| O6 | `SOURCE_VERIFIED` | Audited upstream did not enqueue its tools in wp-admin and persisted permission-scoped definitions across users. | Required page-scoped private registration without persistent cache. |
| O7 | `OBSERVED` | Demo navigation architecture was unspecified. | Classic-menu fixture selected for reliable scope. |
| O8 | `OBSERVED` | Error/security integration work appeared as polish despite mandatory security criteria. | Moved authorization/schema/state tests into gates. |
| O9 | `OBSERVED` | Final spec has 15 unique Ability contracts and 15 unique WebMCP names. | Tool-count acceptance condition satisfied at specification level. |
| O10 | `OBSERVED` | Final checklist has 36 unique tasks, 36 dependency declarations, and 36 acceptance tests. | Task-breakdown acceptance condition satisfied. |
| O11 | `NOT_TESTED` | No WordPress, browser, deployment, or live ChatGPT execution occurred. | All runtime/product claims remain unverified. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off |
|---|---|---|---|---|
| D1 | `DECIDED` | Combine site/user/capability bootstrap into `agentpress/get-context`. | O1 plus non-overlap guidance | Preserves coverage with 15 tools. |
| D2 | `DECIDED` | Only AgentPress-created drafts receive automatic updates/term assignment; other content is staged. | O2 and Safe Mode invariant | Safer but narrower automation. |
| D3 | `DECIDED` | Keep three tables and add immutable proposal, state, actor, expiry, and idempotency fields. | O3 | More schema than PRD sketch, no workflow engine. |
| D4 | `DECIDED` | Build an AgentPress-owned current WebMCP adapter from selected attributed upstream patterns. | O4–O6, S4–S8 | More adapter work, one reliable plugin and no second configuration surface. |
| D5 | `DECIDED` | Expose tools only on the AgentPress wp-admin page with private no-store discovery. | O6 and Site Tools page scope | Smaller demo surface; user keeps AgentPress page open. |
| D6 | `DECIDED` | Support classic `primary` menu only in challenge v0.1. | O7 | Reliable demo; block Navigation explicitly unsupported. |
| D7 | `DECIDED` | Make security/integration tests dependencies of packaging and reliability. | O8 | Less feature breadth before core safety is green. |

## Execution evidence

| Action/check | Result | Evidence |
|---|---|---|
| Repository preflight | exit 0; `main...origin/main`, no initial tracked changes | baseline commit above |
| PRD section/line inventory | exit 0; 2,036 lines and 67 major numbered sections | `docs/PRD.md` |
| Current source audit | completed; raw GitHub fetch cache misses were recovered through GitHub rendered source and bounded research agents | S1–S10 |
| Ability/tool uniqueness check | exit 0; 15/15 unique | `docs/IMPLEMENTATION_SPEC.md` |
| Checklist structure check | exit 0; 36 tasks, 36 dependency fields, 36 acceptance tests | `docs/BUILD_CHECKLIST.md` |
| Markdown check | exit 0; balanced fences and zero trailing-space lines | both artifacts |
| Local-link check | exit 0; referenced local specification/PRD paths existed | both artifacts |

## Artifact inventory

| Artifact | State | SHA-256 | What it proves |
|---|---|---|---|
| [`docs/IMPLEMENTATION_SPEC.md`](../../IMPLEMENTATION_SPEC.md) | `UNCOMMITTED` at session end | `8aa5daed0db772ecd96be7fa1d736706cfe659f5548bcf725dea7754383de492` | Resolved architecture and 15 exact contracts existed at end of research. |
| [`docs/BUILD_CHECKLIST.md`](../../BUILD_CHECKLIST.md) | `UNCOMMITTED` at session end | `5d13543170e9fbbd77114ddee35ce0c646dead0e8db9c35bb0ab17d995956a46` | Dependency-ordered 36-task plan and 5/5 procedure existed at end of research. |

## Result

`SUPPORTED`

The overall question was answered: a source-validated, implementation-ready v0.1 architecture and ordered plan were produced without an unresolved scaffold blocker. The product direction and Abilities foundation were supported.

The subordinate assumption that the upstream bridge could be used largely unchanged was `FALSIFIED`. Its selected server-side patterns remain useful, but the current browser registration, REST authentication, wp-admin loading, caching, and output behavior require an AgentPress-owned adapter.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: plugin installation/activation.
- `NOT_TESTED`: actual WordPress Ability registration or schema validation.
- `NOT_TESTED`: any role/capability execution matrix.
- `NOT_TESTED`: current `document.modelContext` behavior in a live build.
- `NOT_TESTED`: ChatGPT Site Tools availability for the intended account/model.
- `NOT_TESTED`: navigation staging/approval/audit/UI.
- `NOT_TESTED`: deployment, judge URL, ZIP, video, or 5/5 reliability.
- `UNCOMMITTED`: artifacts did not yet provide dated commit-history evidence.

## Competition evidence statement

- `OBSERVED`: baseline PRD commit was dated 30 August 2026, within the challenge period.
- `UNCOMMITTED`: research artifacts existed in the worktree but had no resulting commit/PR at session end.
- `SOURCE_VERIFIED`: third-party bridge pin and declared license were recorded in the implementation spec; no code was copied during this experiment.
- `NOT_TESTED`: live URL, real Site Tools, five-run reliability, release ZIP, and video.

## Next experiment

- Task: `AP-001`.
- Question: Can the minimum plugin activate on WordPress 6.9/PHP 8.0 and fail closed on unsupported versions?
- Prerequisite: authorize implementation scaffold and create a new experiment record before mutation.

## End state

```text
branch: main...origin/main
untracked: docs/IMPLEMENTATION_SPEC.md, docs/BUILD_CHECKLIST.md
committed: no
pushed: no
deployed: no
```
