# EXP-027 — ChatGPT Site Tools discovery failure

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-027` |
| Related task | `AP-028` |
| Status | `COMPLETE` |
| Result | `PENDING` |
| Started local | `2026-09-02T13:03:27+07:00` |
| Started UTC | `2026-09-02T06:03:27Z` |
| Ended local | `2026-09-02T13:05:24+07:00` |
| Ended UTC | `2026-09-02T06:05:24Z` |
| Agent/operator | Craig, live browser operator; Codex, diagnosis/evidence operator |
| Branch | `main` |
| Baseline commit | `326c60ccdbff272cb2fc79423c7a596fb15bfc08` |
| Ending commit | `UNCOMMITTED` |
| Environment | ChatGPT desktop built-in browser; live HTTPS WordPress page; Administrator intended; selected model/app/workspace/settings details require operator confirmation |

## Question

Why did ChatGPT's built-in browser report both AgentPress tools unavailable when the same deployed page had just executed them through the Chrome-extension/Gemini client?

## Hypothesis

Because both tool calls failed before producing request IDs, ChatGPT did not discover or attach the page's Website Tools to the conversation. The highest-probability causes are current-tab/chat binding, the Website Tools permission, unsupported model/workspace availability, or desktop-app rollout/version—not the AgentPress REST executors.

## Falsification condition

The hypothesis is falsified if the address-bar Website Tools panel lists `agentpress_get_context` for the current page and the current Sol/Terra conversation is bound to that browser tab, yet an exact direct call still fails without a request ID.

## Controls

- fixed commit/build: installed build identifier remains `NOT_TESTED`; repository baseline `326c60ccdbff272cb2fc79423c7a596fb15bfc08`.
- fixed fixture/data: same live HTTPS WordPress page used for EXP-026; customer details omitted.
- fixed identity/capabilities: Administrator intended but unverified because context never ran.
- fixed policy/configuration: same read-only two-tool prompt; no mutation or browser fallback.
- fixed client/environment: ChatGPT desktop built-in browser, distinct from EXP-026 Chrome/Gemini client.
- explicit scope exclusions: mutation, Author testing, code changes, public recording, and any claim of ChatGPT execution.

## Variables

- **Independent:** ChatGPT built-in-browser client, selected model, Website Tools permission, current page/tab binding, and account/workspace availability.
- **Dependent:** address-bar tool listing, tool-call initiation, request ID generation, and structured result.

## Preflight

```text
timestamp: 2026-09-02T13:03:27+07:00 / 2026-09-02T06:03:27Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main
git log -3 --oneline --decorate: 326c60c EXP-026 closeout; b65f082 Gemini smoke; 943374b EXP-025 closeout
baseline SHA: 326c60ccdbff272cb2fc79423c7a596fb15bfc08
current branch: main
unrelated existing changes: none observed
AP task, issue, PR: AP-028; no issue/PR created
environment: ChatGPT built-in browser; exact app/model/workspace/settings evidence pending
```

## Method

1. Record the supplied result without customer identifiers: both requested tools unavailable, no request IDs, role/counts unverified, and no mutation observed.
2. Check current official OpenAI Website Tools prerequisites and limitations.
3. Build a short, ordered discovery checklist that distinguishes absent address-bar registration from conversation/model invocation failure.
4. Update the demo runbook and project status without changing plugin code.
5. Run documentation checks before any authorized commit/push.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator checks official documentation and repository evidence. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | project-owner supplied ChatGPT result | 2026-09-02 | Both requested AgentPress tools were unavailable, no request IDs existed, role/counts remained unverified, and no mutation was observed. | Customer identity omitted. |
| S2 | `SOURCE_VERIFIED` | [OpenAI ChatGPT Learn: Website Tools](https://learn.chatgpt.com/es-419/docs/webmcp) | 2026-09-02 | Current Website Tools require GPT-5.6 Sol or Terra; Luna is disabled; latest desktop app, non-Enterprise/Edu availability, enabled Browser permission, current top-level page, and rollout availability matter. | Official page was available in translated form; technical requirements are explicit. |
| S3 | `OBSERVED` | EXP-026 | 2026-09-02 | The same two deployed tool paths returned `ok: true` through a Chrome-extension/Gemini client. | Narrows diagnosis away from a universal server/executor failure. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| before 2026-09-02T13:03:27+07:00 | Run the EXP-025 read-only prompt in ChatGPT built-in browser | live site | both tools unavailable | No request IDs; no context/structure results; no mutation observed. |
| 2026-09-02T13:03:27+07:00 | Capture repository preflight | repository | exit 0 | Clean synchronized baseline. |
| timestamp not independently captured | Search and open current official OpenAI Website Tools documentation using the OpenAI Docs skill | official documentation | success | Identified model, app, workspace, permission, current-page, top-level registration, and rollout prerequisites. |
| 2026-09-02T13:05:24+07:00 | Update README/runbook and verify documentation | repository | exit 0 | Added ordered discovery checklist and exact one-tool retry; local links/fences and `git diff --check` pass. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | Neither requested ChatGPT call reached AgentPress far enough to create a request ID. | S1 | Supports pre-execution discovery/attachment failure. |
| O2 | `OBSERVED` | Role and counts are unknown in this client because `get-context` and `get-structure` never executed. | S1 | Prevents reusing the Gemini result as ChatGPT evidence. |
| O3 | `OBSERVED` | No mutation was observed, as expected when no tool executed. | S1 | Safe failure; neutral on executor correctness. |
| O4 | `SOURCE_VERIFIED` | Current official guidance limits Website Tools to Sol/Terra, excludes Luna and Enterprise/Edu, and requires current app/permission/page availability. | S2 | Supplies the ordered recovery checks. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | A green page-level WebMCP diagnostic guarantees ChatGPT can invoke the registered tools. | ChatGPT reported both tools unavailable and generated no request IDs. | Client discovery/attachment failure; exact cause unresolved. | Inspect address-bar tool listing first, then model/permission/app/workspace/page binding in order. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Do not change AgentPress server code until the address-bar tool panel and official client prerequisites are checked. | O1, S3, C1 | May delay a code fix; avoids misdiagnosing a client configuration failure. | Tools are listed in the panel but direct Sol/Terra invocation still fails. |
| D2 | `DECIDED` | Do not start the hero mutation or final recording while ChatGPT discovery is unresolved. | O1-O2 | Delays filming; preserves the one-shot recording and customer safety. | Read-only ChatGPT call returns `ok: true` with a request ID. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| ChatGPT executes `agentpress_get_context` | Direct prompt | `FAIL` | Tool unavailable; no request ID. |
| ChatGPT executes `agentpress_get_structure` | Direct prompt | `FAIL` | Tool unavailable; no request ID. |
| No mutation on failed run | Operator observation | `PASS` within reported scope | No mutation observed. |
| Exact discovery failure cause identified | Official prerequisite review plus live UI checks | `NOT_TESTED` | Operator must inspect address-bar listing/settings/model/app/workspace. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-02-exp-027-chatgpt-site-tools-discovery-failure.md` | evidence | uncommitted | `UNCOMMITTED` | Sanitized failure and troubleshooting plan. |
| raw ChatGPT session | external | not stored | no request IDs | Customer/session data intentionally omitted. |

## Result

`INCONCLUSIVE`

The two ChatGPT Site Tools calls failed safely before execution. Evidence supports a discovery/attachment problem but does not yet identify whether the cause is page binding, permission, selected model, app/workspace availability, rollout state, or a client-specific registration incompatibility.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: address-bar tool list, Website Tools permission, exact selected model, desktop version, workspace type, rollout eligibility, direct invocation after a fresh chat/page reload, and all ChatGPT mutations.

## Competition evidence statement

- work attributable to challenge period: timestamp and exact failure class recorded;
- pre-existing work distinguished by: repository baseline recorded;
- third-party material/license/pin: no new third-party code;
- commit/PR evidence: `UNCOMMITTED`;
- live URL evidence: redacted failure on live HTTPS page;
- real ChatGPT Site Tools evidence: discovery/execution `FAIL`, no request IDs;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: do not record final hero take until resolved.

## Next experiment

- proposed experiment ID/task: continue AP-028 with the ordered Website Tools discovery checklist;
- next falsifiable question: does the address-bar Website Tools panel list `agentpress_get_context`, and can a fresh Sol/Terra chat bound to that page invoke it?
- required prerequisites: latest desktop app, eligible non-Enterprise/Edu workspace, Website Tools enabled, Sol/Terra selected, AgentPress page top-level/current, and sanitized evidence capture.

## End state

```text
git status --short --branch: four intended documentation/evidence paths modified or untracked; no unrelated changes observed
tests/checks: official OpenAI requirement check PASS; local links PASS; balanced fences PASS; git diff --check PASS
committed: no
pushed: no
deployed: no repository deployment; live plugin unchanged
```
