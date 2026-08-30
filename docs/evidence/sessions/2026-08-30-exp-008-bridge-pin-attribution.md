# EXP-008 — AP-002 bridge pin and attribution

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-008` |
| Related task | `AP-002`; GitHub issue #3 |
| Status | `IN_PROGRESS` |
| Result | `PENDING` |
| Started local | `2026-08-30T16:27:06+07:00` |
| Started UTC | `2026-08-30T09:27:06Z` |
| Ended local | `PENDING` |
| Ended UTC | `PENDING` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-002-bridge-attribution` |
| Baseline commit | `09cf45c5a7b51ba9d99d422e5b5dadf5a3a43fbc` |
| Ending commit | `ac5d92467728b0ec945d8f18ccd6c67cc47f75a2` (implementation); evidence/format closeout follows |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 10.9.8; local PHP and Composer unavailable; GitHub API inspection of pinned upstream commit/tree/blobs |

## Question

Can AgentPress pin and fully attribute the audited `code-atlantic/webmcp-abilities` source while packaging only the GPL-compatible notices and selected reusable source patterns needed by later tasks, with no upstream built-ins, settings surface, public discovery, cache, bootstrap, or obsolete browser client in the plugin ZIP?

## Hypothesis

The implementation specification's audited commit identifies a bounded GPL-2.0-or-later source whose useful allowlist, schema-limit, and rate-limit patterns can be represented by a machine-readable pin and complete notices now, while actual adaptation remains isolated to later transport tasks.

## Falsification condition

The hypothesis is falsified if the pinned commit cannot be retrieved and identified exactly, its licensing is incompatible or cannot be accurately packaged, attribution cannot distinguish copied code from concepts, or the production ZIP necessarily includes upstream generic tools, settings, public discovery, bootstrap, cache, or obsolete client code.

## Controls

- fixed commit/build: AgentPress baseline `09cf45c5a7b51ba9d99d422e5b5dadf5a3a43fbc`; upstream candidate `ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6`;
- fixed fixture/data: upstream tree and license declarations at the exact pinned commit;
- fixed identity/capabilities: public read-only upstream source access;
- fixed policy/configuration: PRD v0.1 non-goals and implementation-spec bridge boundary;
- fixed client/environment: repository inspection plus primary GitHub source/API evidence;
- explicit scope exclusions: no WebMCP adapter, REST route, runtime registration, WordPress Ability, settings page, built-in tool, or live browser behavior.

## Variables

- **Independent:** upstream commit contents, declared licensing locations, selected copied/adapted files or concepts, and package include policy.
- **Dependent:** exact pin validation, attribution completeness, license compatibility, machine readability, ZIP contents, and absence of excluded upstream behavior.

## Preflight

```text
timestamp local: 2026-08-30T16:27:06+07:00
timestamp UTC: 2026-08-30T09:27:06Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
09cf45c (HEAD -> main, origin/main, origin/HEAD) Merge pull request #2 from MrBigleg/ap-001-plugin-scaffold
17e938d (origin/ap-001-plugin-scaffold) docs: close AP-001 CI evidence
46c3e9d ci: add AP-001 scaffold checks
baseline SHA: 09cf45c5a7b51ba9d99d422e5b5dadf5a3a43fbc
current branch after preflight: ap-002-bridge-attribution
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-002; issue #3; PR pending
environment: Node.js 22.23.2; npm 10.9.8; PHP and Composer not available on the Windows host
```

## Method

1. Create the AP-002 issue with `difficulty: XS` and v0.1 milestone after opening this record.
2. Retrieve and inspect the exact upstream commit tree and all license declarations from primary source.
3. Inventory reusable patterns and explicitly excluded upstream files/behaviors; do not copy implementation code unless required by AP-002 acceptance.
4. Add a machine-readable pin, full required license text, and root third-party notice with exact provenance and adaptation status.
5. Add automated checks for pin shape, notice consistency, license detection, excluded ZIP paths/identifiers, and inclusion of required notices.
6. Run the project checks and two-build ZIP inspection, preserving failures in order.
7. Commit, push, open a draft PR, and merge only after a green check.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work performed and checked in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [`code-atlantic/webmcp-abilities` commit `ea5a2bc`](https://github.com/code-atlantic/webmcp-abilities/commit/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6) | 2026-08-30 | Exact upstream identity, commit, and contents | Commit resolves to tree `232869b917e491b6c15b47b0cd58fc941dda9797`. |
| S2 | `SOURCE_VERIFIED` | [upstream `composer.json`](https://github.com/code-atlantic/webmcp-abilities/blob/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6/composer.json), [plugin header](https://github.com/code-atlantic/webmcp-abilities/blob/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6/webmcp-abilities.php), and [readme](https://github.com/code-atlantic/webmcp-abilities/blob/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6/readme.txt) | 2026-08-30 | Each declares GPL-2.0-or-later; plugin version is 0.6.1. | Exact blob SHAs are recorded in `PROVENANCE.json`. |
| S3 | `SOURCE_VERIFIED` | [pinned Git tree](https://api.github.com/repos/code-atlantic/webmcp-abilities/git/trees/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6?recursive=1) | 2026-08-30 | Tree SHA is `232869b...`; no `LICENSE` blob exists despite the README link. | AgentPress supplies the complete GPL v2 text and states this distinction. |
| S4 | `SOURCE_VERIFIED` | [ability bridge](https://github.com/code-atlantic/webmcp-abilities/blob/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6/includes/class-ability-bridge.php), [rate limiter](https://github.com/code-atlantic/webmcp-abilities/blob/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6/includes/class-rate-limiter.php), and [client](https://github.com/code-atlantic/webmcp-abilities/blob/ea5a2bcbe77dc13aafc95b4c83a18c9b11d85da6/src/webmcp-abilities.ts) | 2026-08-30 | Pin contains useful allowlist/schema/rate-limit concepts and obsolete/cached client behavior that AgentPress must not ship. | AP-002 records concepts only; later adaptations require exact mappings. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-30T16:27:06+07:00 | Inspect merged main status/history, environment, evidence IDs, and AP-002 references | repository | mixed | Clean main at `09cf45c`; AP-001 PR #2 merged and issue #1 closed; Node/npm available; local PHP/Composer unavailable; EXP-008 is next unused ID. |
| 2026-08-30T16:27:06+07:00 | `git switch -c ap-002-bridge-attribution` | repository | exit 0 | Dependency-ordered task branch created from merged AP-001 main. |
| timestamp not independently captured | Create `difficulty: XS` label and milestone-scoped AP-002 issue | GitHub | success | Issue [#3](https://github.com/MrBigleg/AgentPress/issues/3) created under milestone v0.1 after the recorded preflight. |
| timestamp not independently captured | Inspect pinned commit, recursive tree, licensing declarations, bridge, rate limiter, client, REST, settings, and built-ins | upstream GitHub source/API | success | Exact commit/tree/blob identities recorded; three GPL-2.0-or-later declarations found; no license blob found; included/excluded concept boundary established. |
| timestamp not independently captured | First local syntax/provenance/package command | repository | exit 1 | Node syntax passed, but the provenance script's package build hit sandbox `EPERM` replacing ignored `dist/agentpress.zip`; the subsequent build hit the same boundary and the old AP-001 ZIP/hash remained. Retry with workspace write authorization. |
| timestamp not independently captured | Strengthened license equality check plus audit/provenance/package rerun | repository | exit 1 | npm audit found zero vulnerabilities; exact byte equality failed because the complete GPL copies differed only by terminal newline. Two explicit package builds still matched SHA-256 `7DC842E08377C804B3896D1E6160D887330A2E640A7B1A35E79485D63DDE1EB3`. Normalize line endings and trailing whitespace in the text-equivalence assertion. |
| before 2026-08-30T16:36:25+07:00 | Corrected syntax/audit/provenance/package rerun | repository | exit 0 | Both licenses reported GPL-2.0-or-later; exact pin verified; 15 ZIP entries included notices and no upstream runtime code; npm audit found zero vulnerabilities; two ZIP hashes matched `7DC842E08377C804B3896D1E6160D887330A2E640A7B1A35E79485D63DDE1EB3`. |
| 2026-08-30T16:41:19+07:00 | Remove two extra EOF blank lines reported by `git diff --cached --check`, then rerun provenance and two-build package controls | repository | exit 0 | License SHA-256 is now `8177F97513213526DF2CF6184D8FF986C675AFB514D4E68A404010521B880643`; both ZIP builds match `0CC9853EEBB645B7E74004C68DF1AF399F9321EDA77D9C2027247422590461B0`; `git diff --check` is clean. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-001 merged as `09cf45c`; AP-002's declared dependency is satisfied. | PR #2, issue #1, local main | Allows AP-002 to begin. |
| O2 | `OBSERVED` | The repository currently contains no `THIRD_PARTY_NOTICES.md` or `third-party/` source package. | root listing | Establishes the AP-002 baseline. |
| O3 | `SOURCE_VERIFIED` | The candidate resolves to exact commit `ea5a2bc...`, tree `232869b...`, and upstream version 0.6.1. | S1–S3 | Supports an immutable machine-readable pin. |
| O4 | `SOURCE_VERIFIED` | Three independent files declare GPL-2.0-or-later, but the pinned tree lacks the README-linked license file. | S2, S3 | Supports compatibility while requiring precise wording about the supplied full license text. |
| O5 | `SOURCE_VERIFIED` | The upstream runtime includes generic `wp/*` built-ins, settings/public-discovery options, permission-scoped caching, custom nonce behavior, and an obsolete `navigator.modelContext.provideContext` client. | S4 and inspected pinned REST/settings/built-in sources | Supports an explicit no-runtime-copy boundary for AP-002. |
| O6 | `OBSERVED` | AgentPress provenance records three concept-only candidates, an empty copied/adapted-material list, and eight excluded runtime paths. | `PROVENANCE.json` | Prevents attribution from implying that unshipped code was copied. |
| O7 | `OBSERVED` | The focused scan identifies both licenses as GPL-2.0-or-later; the 15-entry ZIP contains both notices/licenses and no upstream runtime source; two post-format builds match SHA-256 `0CC9853E...`. | local test and package output | Supports the AP-002 acceptance conditions locally. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | The first provenance test reaches its assertions and produces the AP-002 ZIP. | The read-only sandbox prevented deletion of the existing ignored ZIP. | environment boundary, not a source failure | Preserved the output; retry the same command with workspace write authorization. |
| C2 | Complete GPL copies compare as identical bytes. | They differ by one terminal newline while the license text is otherwise identical. | test defect / presentation variance | Compare normalized text, retaining the full packaged license unchanged. |
| C3 | The initially committed license copy passes the staged whitespace check. | Two extra terminal blank lines produced a `new blank line at EOF` warning and changed package bytes. | formatting/evidence drift | Removed the blank lines without amending history; retained the pre-format hash above and recorded the new current hash. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Treat AP-002 as provenance/package-boundary work; defer adapter implementation to AP-003/AP-004. | build checklist; implementation specification | AP-002 proves attribution and exclusion, not runtime behavior. | Acceptance requires copied runtime code now. |
| D2 | `DECIDED` | Include a full GPL v2 text copied from AgentPress's own license and explicitly state that it was not retrieved from the upstream tree. | O4 | More disclosure than a bare SPDX expression; avoids inventing upstream file provenance. | The project selects a different upstream pin with its own license blob. |
| D3 | `DECIDED` | Package only provenance documents in the third-party directory for AP-002. | O5, O6 | Later implementation must update the copied/adapted mapping before merge. | AP-003/AP-004 adapts source rather than concepts. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Exact machine-readable upstream pin | Pin/provenance consistency assertions | `PASS` | `PINNED_COMMIT`, `PROVENANCE.json`, O3 |
| Complete GPL-compatible attribution | Notice, three declaration blobs, and normalized full-license equality | `PASS` | `THIRD_PARTY_NOTICES.md`, bundled license, O4 |
| License scan identifies both projects | `npm run test:third-party` | `PASS` | AgentPress and upstream both reported `GPL-2.0-or-later` |
| ZIP contains notices but no excluded upstream behavior | ZIP central-directory assertions and 15-entry listing | `PASS` | O7; current SHA-256 `0CC9853EEBB645B7E74004C68DF1AF399F9321EDA77D9C2027247422590461B0` |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-30-exp-008-bridge-pin-attribution.md` | evidence | untracked | `EXP-008` | Opened before upstream research or product mutation. |
| `agentpress/third-party/webmcp-abilities/PROVENANCE.json` | machine-readable provenance | untracked | SHA-256 `55764A64F3495E9E7E358B223DBF1776ED89C47B30ADA235789CB9915A17A281` | Exact pin/tree/blob mappings and exclusions. |
| `agentpress/third-party/webmcp-abilities/LICENSE` | license | committed pre-format; cleanup pending | current SHA-256 `8177F97513213526DF2CF6184D8FF986C675AFB514D4E68A404010521B880643`; pre-format `6A64FD65...` | Complete GPL v2 text supplied by AgentPress. |
| `agentpress/THIRD_PARTY_NOTICES.md` | attribution | untracked | SHA-256 `1ED94CF4E76FFF4233A978999D309AFEFDE78DA08FF65D006CEFCA7803EB23A9` | Human-readable attribution and no-code boundary. |
| `dist/agentpress.zip` | generated package control | ignored | current SHA-256 `0CC9853EEBB645B7E74004C68DF1AF399F9321EDA77D9C2027247422590461B0`; pre-format `7DC842E...` | AP-002 test artifact, not release evidence. |

## Result

`PENDING`

No AP-002 acceptance claim exists yet.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: authoritative GitHub CI on this branch, adapter runtime, WordPress behavior beyond unchanged AP-001 scaffold checks, browser, ChatGPT, deployment, and AP-003+ behavior.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-002 work;
- pre-existing work distinguished by: merged AP-001 baseline and EXP-001 source decision;
- third-party material/license/pin: exact commit/tree/blob provenance and GPL-2.0-or-later declarations verified; no upstream runtime source copied by AP-002;
- commit/PR evidence: pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-009 / AP-003;
- next falsifiable question: can the current browser adapter register the fixed tool definitions and cancel cleanly without obsolete WebMCP APIs?;
- required prerequisites: AP-002 merged with green checks.

## End state

```text
git status --short --branch: EXP-008 and evidence index modified on ap-002-bridge-attribution
tests/checks: local syntax, npm audit, provenance/license scan, ZIP boundary, and two-build hash PASS; GitHub CI pending
committed: no
pushed: no
deployed: no
```
