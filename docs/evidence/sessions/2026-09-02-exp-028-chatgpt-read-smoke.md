# EXP-028 — ChatGPT built-in-browser read smoke

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-028` |
| Related task | `AP-028` |
| Status | `COMPLETE` |
| Result | `PENDING` |
| Started local | `2026-09-02T13:13:15+07:00` |
| Started UTC | `2026-09-02T06:13:15Z` |
| Ended local | `2026-09-02T13:16:20+07:00` |
| Ended UTC | `2026-09-02T06:16:20Z` |
| Agent/operator | Craig, live browser operator; Codex, evidence operator |
| Branch | `main` |
| Baseline commit | `6081e3c2d6883b55f77f60fe785b7bb31aee2ad9` |
| Ending commit | `UNCOMMITTED` |
| Environment | ChatGPT desktop built-in browser; GPT-5.6 Sol Light observed in supplied screenshot; live HTTPS WordPress 7.1; Administrator; customer identity omitted |

## Question

Can ChatGPT's built-in browser discover and execute AgentPress's context and structure tools on the live HTTPS WordPress page after the initial no-tool failure, without observed mutation?

## Hypothesis

Retrying from the active AgentPress page after Website Tools become attached will execute both read-only tools, return distinct request IDs, report the current Administrator identity and the same bounded counts seen through the alternate client, and make no observed change.

## Falsification condition

The hypothesis is falsified if either tool remains unavailable, no request ID is returned, results disagree with the current page/session, or the operator observes any mutation.

## Controls

- fixed commit/build: installed build identifier/checksum remains `NOT_TESTED`; repository baseline `6081e3c2d6883b55f77f60fe785b7bb31aee2ad9`.
- fixed fixture/data: same live customer site and visible content state as EXP-026/027; no reset.
- fixed identity/capabilities: Administrator reported by tool; customer identifiers omitted.
- fixed policy/configuration: exact read-only two-tool prompt; no browser fallback.
- fixed client/environment: ChatGPT desktop built-in browser, GPT-5.6 Sol Light, top-level AgentPress page.
- explicit scope exclusions: write tools, Author session, public-video privacy approval, installed checksum, and 5/5 reliability.

## Variables

- **Independent:** retry after ChatGPT Website Tools attachment/discovery recovered.
- **Dependent:** tool availability, request IDs, role/count results, and observed mutation state.

## Preflight

```text
timestamp: 2026-09-02T13:13:15+07:00 / 2026-09-02T06:13:15Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main
git log -3 --oneline --decorate: 6081e3c EXP-027 closeout; 93445c1 ChatGPT discovery failure; 326c60c EXP-026 closeout
baseline SHA: 6081e3c2d6883b55f77f60fe785b7bb31aee2ad9
current branch: main
unrelated existing changes: none observed
AP task, issue, PR: AP-028 partial live gate; no issue/PR created
environment: ChatGPT built-in browser; GPT-5.6 Sol Light; live HTTPS WordPress 7.1; Administrator
```

The external browser execution occurred immediately before this repository record could be opened. Its operator-supplied output and screenshot are treated as post-run evidence; the screenshot is not stored because it contains customer identity.

## Method

1. Record the sanitized client, tool outcomes, request IDs, role, counts, and mutation observation supplied by the operator.
2. Compare the two results with the prior Gemini/Chrome smoke and AgentPress tool contracts.
3. Update project/runbook status while keeping AP-028 open until `agentpress_create_draft` and Author switching are verified.
4. Exclude the raw screenshot and customer identifiers from Git.
5. Run local-link, fence, and whitespace checks before any authorized commit/push.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator checks supplied outcomes against repository contracts. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | project-owner supplied ChatGPT result and screenshot | 2026-09-02 | Built-in-browser calls to context and structure both passed with distinct request IDs; role Administrator; 0 posts/15 pages; no mutation observed. | Raw screenshot contains customer identity and is intentionally not stored. |
| S2 | `SOURCE_VERIFIED` | `AbilityRegistrar.php`, `AbilityCatalog.php`, and adapter map at baseline | 2026-09-02 | Both named tools map to concrete read-only services. | Source contract does not replace live evidence. |
| S3 | `SOURCE_VERIFIED` | [OpenAI ChatGPT Learn: Website Tools](https://learn.chatgpt.com/es-419/docs/webmcp) | 2026-09-02 | Sol/Terra in the built-in browser can discover top-level imperative WebMCP tools when available. | Current official guidance. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| before 2026-09-02T13:13:15+07:00 | Ask ChatGPT to check the AgentPress tools again | live built-in browser | success | Context request `80e64080-a6b6-4f57-ab2d-83ab3b7e5424`; structure request `7462ee11-a085-4315-bae2-3fb45dd2a4f7`. |
| 2026-09-02T13:13:15+07:00 | Capture repository preflight and inspect current evidence/status references | repository | exit 0 | Clean synchronized baseline. |
| 2026-09-02T13:16:20+07:00 | Update README/runbook and verify documentation | repository | exit 0 | Local links resolve, fences are balanced, and `git diff --check` passes across four intended paths. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | `agentpress_get_context` passed in ChatGPT's built-in browser with request ID `80e64080-a6b6-4f57-ab2d-83ab3b7e5424`. | S1 | Supports discovery and execution. |
| O2 | `OBSERVED` | `agentpress_get_structure` passed with request ID `7462ee11-a085-4315-bae2-3fb45dd2a4f7`. | S1 | Supports ordered second read execution. |
| O3 | `OBSERVED` | The result reported Administrator, zero posts, and 15 pages, matching the earlier alternate-client smoke. | S1 and EXP-026 | Supports cross-client consistency. |
| O4 | `OBSERVED` | The operator observed no mutation. | S1 | Supports the read-only smoke within manual observation scope. |
| O5 | `OBSERVED` | The screenshot shows the selected GPT-5.6 Sol Light model and all four AgentPress diagnostics green. | non-stored S1 screenshot | Supports current-client/model/page readiness. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | The initial EXP-027 no-tool failure would reproduce until a specific setting changed. | Asking ChatGPT to check again succeeded; the exact state transition was not captured. | Intermittent client discovery/attachment behavior; cause unresolved. | Preserve both attempts in order; require fresh-page/retry readiness in the demo preflight. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Mark the ChatGPT read-discovery portion of AP-028 supported, but keep AP-028 open. | O1-O5 | Honest partial milestone rather than premature completion. | Draft execution and Author envelope/denial both pass. |
| D2 | `DECIDED` | Keep the raw screenshot out of Git and require crop/blur or a demo site for public video use. | S1 privacy content | Loses direct committed visual artifact; protects the customer. | Owner supplies an approved redacted capture. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| ChatGPT discovers and calls context | Live direct call | `PASS` | Request `80e64080-a6b6-4f57-ab2d-83ab3b7e5424`. |
| ChatGPT discovers and calls structure | Live direct call | `PASS` | Request `7462ee11-a085-4315-bae2-3fb45dd2a4f7`. |
| Current role/counts are consistent | Compare result to alternate-client smoke | `PASS` | Administrator; 0 posts/15 pages. |
| No mutation | Operator observation only | `PASS` within manual scope | No mutation observed; database-level equality `NOT_TESTED`. |
| ChatGPT creates a safe draft | Not run | `NOT_TESTED` | Required by AP-028. |
| Author changes the effective envelope and page creation is denied | Not run | `NOT_TESTED` | Required by AP-028. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-02-exp-028-chatgpt-read-smoke.md` | evidence | uncommitted | `UNCOMMITTED` | Sanitized partial AP-028 evidence. |
| supplied screenshot | external evidence | intentionally not stored | `NOT_APPLICABLE` | Contains customer domain and Administrator username. |

## Result

`INCONCLUSIVE`

The ChatGPT built-in-browser read smoke is supported: both AgentPress tools executed successfully with request IDs, returned consistent role/count results, and produced no observed mutation. The broader AP-028 question remains inconclusive until safe draft execution and Author role switching are tested, and the intermittent initial discovery failure remains unexplained.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: database-level zero mutation, draft creation, category assignment, Author role/denial, installed build checksum, public-video redaction, exact discovery recovery cause, and 5/5 reliability.

## Competition evidence statement

- work attributable to challenge period: current timestamp and two live request IDs recorded;
- pre-existing work distinguished by: repository baseline recorded;
- third-party material/license/pin: no new third-party code;
- commit/PR evidence: `UNCOMMITTED`;
- live URL evidence: redacted live HTTPS execution only;
- real ChatGPT Site Tools evidence: two read calls `PASS`; write and role switch `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: screenshot not public-ready because customer identifiers are visible.

## Next experiment

- proposed experiment ID/task: continue AP-028 with one safe existing-category lookup, one idempotent synthetic post draft, read-back, and separate Author denial;
- next falsifiable question: can the supported ChatGPT client create exactly one unpublished synthetic post draft and preserve the narrower Author boundary?
- required prerequisites: explicit authorization for a live-site draft or a safe staging/demo site, known existing category, installed build ID/checksum, and redacted recording setup.

## End state

```text
git status --short --branch: four intended documentation/evidence paths modified or untracked; no unrelated changes observed
tests/checks: supplied-result comparison PASS; request IDs captured; local links PASS; balanced fences PASS; git diff --check PASS
committed: no
pushed: no
deployed: no repository deployment; live plugin unchanged
```
