# EXP-009 — AP-003 current WebMCP browser adapter

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-009` |
| Related task | `AP-003`; GitHub issue #5 |
| Status | `IN_PROGRESS` |
| Result | `PENDING` |
| Started local | `2026-08-30T16:46:12+07:00` |
| Started UTC | `2026-08-30T09:46:12Z` |
| Ended local | `PENDING` |
| Ended UTC | `PENDING` |
| Agent/operator | Codex, implementation agent |
| Branch | `ap-003-webmcp-adapter` |
| Baseline commit | `d9d0a09c2fc04fe73dc0ba294eb17526fb6e9973` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows/PowerShell; Node.js 22.23.2; npm 10.9.8; WebMCP specification commit `41d12f0`; current Chrome imperative API documentation |

## Question

Can an AgentPress-owned, feature-detected browser adapter register every supplied definition through current `document.modelContext.registerTool`, enforce the fixed collision-free name map, preserve annotations and direct structured results, and abort an in-flight per-tool fetch without referencing or calling obsolete upstream WebMCP APIs?

## Hypothesis

The current imperative API is small enough to isolate behind a dependency-injected ES module. A fixed map and one AbortController per invocation should make registration and cancellation deterministic without importing the upstream client, caching definitions, or implementing AP-004 transport policy early.

## Falsification condition

The hypothesis is falsified if any supplied known Ability is missing or registered with a slash-containing/derived name, an unknown Ability is registered, annotations or structured results are transformed incorrectly, cancellation does not abort the associated fetch signal, unsupported browsers throw, or production/built code contains `navigator.modelContext` or `provideContext`.

## Controls

- fixed commit/build: AgentPress baseline `d9d0a09c2fc04fe73dc0ba294eb17526fb6e9973`;
- fixed fixture/data: the 15 Ability-to-tool mappings and synthetic tool definitions/results;
- fixed identity/capabilities: no WordPress user; pure browser-contract unit fixture;
- fixed policy/configuration: implementation-spec mapping and AP-003 scope; no cache or dynamic name derivation;
- fixed client/environment: Node test runner with stubbed `document.modelContext`, `fetch`, and abort signals; current official WebMCP docs;
- explicit scope exclusions: no WordPress enqueue, REST nonce/route, discovery policy, Ability registration, wp-admin UI, live Chrome, or ChatGPT acceptance.

## Variables

- **Independent:** API availability, definition set/order, annotations, execute result, and cancellation timing.
- **Dependent:** registered names/options, fetch signal state, returned object identity/shape, cleanup behavior, and unsupported-client result.

## Preflight

```text
timestamp local: 2026-08-30T16:46:12+07:00
timestamp UTC: 2026-08-30T09:46:12Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main
git log -3 --oneline --decorate:
d9d0a09 (HEAD -> main, origin/main, origin/HEAD) Merge pull request #4 from MrBigleg/ap-002-bridge-attribution
bbbdf10 (origin/ap-002-bridge-attribution) docs: close AP-002 evidence
82de2df docs: normalize AP-002 license evidence
baseline SHA: d9d0a09c2fc04fe73dc0ba294eb17526fb6e9973
current branch after preflight: ap-003-webmcp-adapter
existing unrelated changes: none observed; Git emitted a read-only global-ignore permission warning
AP task, issue, and PR: AP-003; issue #5; PR pending
environment: Node.js 22.23.2; npm 10.9.8
```

## Method

1. Create the AP-003 issue with `difficulty: M` and v0.1 milestone after opening this record.
2. Recheck the current official imperative registration, execution, annotations, and cancellation contract.
3. Define the fixed 15-entry Ability-to-WebMCP map as immutable production data.
4. Implement a feature-detected adapter with injected execution transport and no persistent/shared cache.
5. Add browser-contract unit tests for complete registration, names, annotations, structured results, per-call abort, cleanup, unsupported API, and prohibited obsolete identifiers.
6. Add the adapter/tests to package and CI boundaries without implementing AP-004 authentication or REST routes.
7. Run focused and full repository checks; inspect the deterministic ZIP and preserve failures in order.
8. Commit, push, open a draft PR, and merge only after green checks.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | Not applicable | Not applicable | Work performed and checked in the primary session |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | [WebMCP specification commit `41d12f0`](https://github.com/webmachinelearning/webmcp/commit/41d12f057167ccf5954dbcf49d99502cb6c84491), `index.bs` blob `241bb2c...` | 2026-08-30 | `registerTool(tool, {signal})` unregisters on abort; execute receives required `{signal}`; results are `Promise<any>`; annotations contain read-only and untrusted-content hints. | Commit is verified and dated 2026-08-26. |
| S2 | `SOURCE_VERIFIED` | [Chrome WebMCP imperative API](https://developer.chrome.com/docs/ai/webmcp/imperative-api) | 2026-08-30 | Current examples use `document.modelContext.registerTool`; registration abort and execution cancellation are distinct signals; execution signal should be passed to fetch. | Current product-facing documentation; accessed live. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-30T16:46:12+07:00 | Inspect merged main status/history, environment, mapping, AP-003 checklist, and package tooling | repository | mixed | Clean main at `d9d0a09`; AP-002 PR #4 merged and issue #3 closed; 15 fixed names confirmed in specification; Node/npm available. |
| 2026-08-30T16:46:12+07:00 | `git switch -c ap-003-webmcp-adapter` | repository | exit 0 | Dependency-ordered AP-003 branch created from merged AP-002 main. |
| timestamp not independently captured | Create `difficulty: M` label and milestone-scoped AP-003 issue | GitHub | success | Issue [#5](https://github.com/MrBigleg/AgentPress/issues/5) created under milestone v0.1 after the recorded preflight. |
| timestamp not independently captured | Inspect current WebMCP specification IDL/algorithms and live Chrome imperative documentation | official spec/Chrome docs | success | Confirmed separate registration and execution AbortSignals, direct arbitrary structured result, two annotations, and current `document.modelContext.registerTool` API. |
| 2026-08-30T16:53:14+07:00 | Run Node syntax, 7 browser-contract tests, npm audit, provenance scan, two-build ZIP control, 16-entry listing, built-identifier scan, and whitespace check | repository | exit 0 | All 7 adapter tests passed; zero npm vulnerabilities; provenance remained green; both ZIP builds matched SHA-256 `DB3EBB1AFA48251A3CD9883857A5BB55B77232109149C4AE2151E719E039522A`; built adapter contains neither prohibited identifier. |
| 2026-08-30T16:55:08+07:00 | Add failed-registration batch cleanup and null request-init hardening; rerun all local controls | repository | exit 0 | All 8 tests passed, including abort of every attempted registration after a browser rejection; both current ZIP builds matched `8098D47E37971D5145B4FB1D2A28B74C392E45B0F5BE4AC70DFF35BA0B542EBE`; audit/provenance/built scans remained green. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-002 merged as `d9d0a09`; AP-003's declared dependency is satisfied. | PR #4, issue #3, local main | Allows AP-003 to begin. |
| O2 | `OBSERVED` | The implementation specification contains exactly 15 explicit mappings, including deliberate stage-oriented names that cannot be safely derived by replacing `/`. | implementation specification | Supports an immutable explicit map. |
| O3 | `SOURCE_VERIFIED` | Registration takes an optional signal that unregisters the tool; every execute callback receives a required signal that communicates execution cancellation. | S1, S2 | Requires one registration controller per tool and direct propagation of the execution signal into fetch. |
| O4 | `SOURCE_VERIFIED` | The execute callback resolves arbitrary structured data and current annotations expose `readOnlyHint` and `untrustedContentHint`. | S1 | Supports returning parsed JSON directly without MCP text wrapping. |
| O5 | `OBSERVED` | The adapter map contains exactly 15 unique slash-free names, including the three deliberate non-derived stage/structure/activity names. | focused test 1; production source | Supports the fixed mapping contract. |
| O6 | `OBSERVED` | Fifteen supplied definitions registered with unique registration signals; aborting one registration affected only that tool, then disposal aborted the remainder. | focused test 3 | Supports the per-tool registration lifecycle. |
| O7 | `OBSERVED` | The browser execution signal reached the exact fetch options signal and produced an `AbortError`; a successful response returned the parsed object by identity. | focused tests 4–5 | Supports cancellation propagation and direct structured results. |
| O8 | `OBSERVED` | Unsupported API returns a stable no-op handle; duplicate/unknown definitions fail before any registration; production and packaged code contain no obsolete API identifiers. | focused tests 2, 6, 8; ZIP scan | Supports fail-closed feature detection and excluded-client boundary. |
| O9 | `OBSERVED` | If the browser rejects a registration mid-batch, the adapter aborts every signal attempted in that batch before rethrowing. | focused test 7 | Prevents a partially registered private tool set after initialization failure. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| none | No contradiction recorded yet | Not applicable | not applicable | Pending source verification and implementation. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Keep AP-003 transport-agnostic through an injected executor; AP-004 owns real REST endpoints/nonces. | checklist dependency boundary | Tests use a synthetic executor rather than WordPress. | Current WebMCP requires adapter-owned network semantics. |
| D2 | `DECIDED` | Include a narrow JSON fetch executor that owns signal propagation, direct JSON parsing, and fixed POST mechanics; inject headers/retry initialization for AP-004. | O3, O4; checklist cancellation requirement | AP-003 can prove fetch cancellation without prematurely implementing nonce refresh or server authorization. | AP-004 transport cannot compose through request initialization. |
| D3 | `DECIDED` | Package the native ESM source byte-for-byte as `admin/build/webmcp-adapter.js`; no transpiler is needed for the target API. | current browser target; O8 | Avoids a new build dependency; older browsers fail feature detection and never execute registration. | Production compatibility requires transformation. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| All supplied known definitions register with fixed slash-free names | 15-definition registration test | `PASS` | O5, O6 |
| Abort cancels exactly one registration and execution signal reaches fetch | registration and fetch cancellation tests | `PASS` | O6, O7 |
| Structured result and annotations pass through | direct-identity and definition assertions | `PASS` | O4, O7 |
| Unsupported API is graceful | no-op handle test | `PASS` | O8 |
| Built production code has no obsolete API identifiers | source assertion and extracted ZIP scan | `PASS` | O8 |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-30-exp-009-current-webmcp-adapter.md` | evidence | untracked | `EXP-009` | Opened before API research or product mutation. |
| `agentpress/admin/src/webmcp-adapter.mjs` | production source | untracked | SHA-256 `58BA6424A2B77C8506A49D685022CF4DDC93FC9140E8D454BEF9706282FA24B2` | AgentPress-original current-WebMCP adapter; no upstream runtime copy. |
| `agentpress/tests/js/webmcp-adapter.test.mjs` | unit test | untracked | SHA-256 `0D91C2AAF28FA8A85DF896F25AB3F656A60269B33B19781E81E22695F8E53945` | Synthetic browser/fetch fixtures only. |
| `dist/agentpress.zip` | generated package control | ignored | current SHA-256 `8098D47E37971D5145B4FB1D2A28B74C392E45B0F5BE4AC70DFF35BA0B542EBE`; prior 7-test source `DB3EBB1A...` | 16-entry AP-003 test artifact, not release evidence. |

## Result

`PENDING`

No AP-003 acceptance claim exists yet.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: authoritative GitHub CI, WordPress enqueue/REST behavior, live browser implementation, ChatGPT, deployment, and AP-004+ behavior.

## Competition evidence statement

- work attributable to challenge period: baseline and timestamps recorded before material AP-003 work;
- pre-existing work distinguished by: merged AP-002 baseline and its concept-only provenance;
- third-party material/license/pin: no upstream runtime code planned; provenance remains under EXP-008;
- commit/PR evidence: issue #5; implementation commit and PR pending;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-010 / AP-004;
- next falsifiable question: can the private same-origin transport enforce cookie identity, `wp_rest` nonces, allowlist, limits, and no-store behavior with zero unauthorized execution?;
- required prerequisites: AP-003 merged with green checks.

## End state

```text
git status --short --branch: EXP-009 and evidence index modified on ap-003-webmcp-adapter
tests/checks: local syntax, 8 browser tests, npm audit, provenance, deterministic ZIP, and built identifier scan PASS; GitHub CI pending
committed: no
pushed: no
deployed: no
```
