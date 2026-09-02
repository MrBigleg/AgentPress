# EXP-026 — Gemini Chrome read-only WebMCP smoke test

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-026` |
| Related task | `AP-028` precursor |
| Status | `COMPLETE` |
| Result | `INCONCLUSIVE` |
| Started local | `2026-09-02T12:34:59+07:00` |
| Started UTC | `2026-09-02T05:34:59Z` |
| Ended local | `2026-09-02T12:37:18+07:00` |
| Ended UTC | `2026-09-02T05:37:18Z` |
| Agent/operator | Craig, live browser operator; Codex, evidence operator |
| Branch | `main` |
| Baseline commit | `943374b505d6403d41f5e1fd9b0e775adbb7624a` |
| Ending commit | `b65f082c042327199ed85f23d8a4ec9b2d35b3c3` |
| Environment | Live HTTPS WordPress 7.1 site; Chrome extension with Gemini plugin/client; exact browser/extension versions and customer identity not recorded |

## Question

Can the Chrome-extension/Gemini client discover and execute AgentPress's two implemented read tools without site mutation, and what does that establish or leave unverified for the challenge?

## Hypothesis

If the WebMCP adapter is client-compatible, Gemini will call `agentpress_get_context` followed by `agentpress_get_structure`, receive successful structured envelopes with distinct request IDs, and produce the requested summary without changing WordPress. This will support a client-compatibility smoke result but will not satisfy the ChatGPT Site Tools acceptance gate.

## Falsification condition

The hypothesis is falsified if either tool fails, the calls occur in the wrong identity/session, a write or browser fallback occurs, the summary contradicts the structured results, or any WordPress object changes.

## Controls

- fixed commit/build: installed build identifier/checksum remains `NOT_TESTED`; repository baseline `943374b505d6403d41f5e1fd9b0e775adbb7624a`.
- fixed fixture/data: existing live customer WordPress site; no fixture reset.
- fixed identity/capabilities: Administrator session; identifiers redacted from repository evidence.
- fixed policy/configuration: read-only prompt; no browser-click fallback; only AgentPress tools.
- fixed client/environment: Chrome extension with Gemini plugin/client, not ChatGPT's built-in browser.
- explicit scope exclusions: mutations, Author account, ChatGPT Site Tools acceptance, public-video suitability, and 5/5 reliability.

## Variables

- **Independent:** Gemini/Chrome WebMCP client and the ordered read-only tool request.
- **Dependent:** call order, structured success, request IDs, role/HTTPS/count/capability summary, and absence of intended mutation.

## Preflight

```text
timestamp: 2026-09-02T12:34:59+07:00 / 2026-09-02T05:34:59Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main
git log -3 --oneline --decorate: 943374b EXP-025 closeout; c14383e recording runbook; fce8a8d EXP-024 closeout
baseline SHA: 943374b505d6403d41f5e1fd9b0e775adbb7624a
current branch: main
unrelated existing changes: none observed
AP task, issue, PR: AP-028 precursor; no issue/PR created
environment: live WordPress 7.1 over HTTPS; Chrome extension/Gemini client; exact installed build and browser/client versions not recorded
```

## Method

1. Preserve the user-supplied ordered prompt, tool names, request IDs, and bounded result facts without copying the customer domain, username, page titles, or raw envelopes into the repository.
2. Compare the final Gemini summary against the structured tool results.
3. Classify this as alternate-client evidence and explicitly keep ChatGPT Site Tools/AP-028 open.
4. Add recording guidance for raw tool-output privacy and a safe next read-only category check.
5. Run Markdown, local-link, and diff checks before any authorized commit/push.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator reviewed the supplied transcript against repository contracts. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | project-owner supplied Gemini/Chrome transcript | 2026-09-02 | Gemini called both requested AgentPress tools with empty inputs and received `ok: true` results with distinct request IDs. | Raw transcript contains customer identifiers/content and is intentionally not stored. |
| S2 | `SOURCE_VERIFIED` | `AbilityRegistrar.php`, `AbilityCatalog.php`, and adapter map at baseline | 2026-09-02 | Both tool names map to concrete read services; neither is intended to mutate WordPress. | Repository contract, not standalone proof of zero mutation. |
| S3 | `SOURCE_VERIFIED` | [OpenAI Site Tools documentation](https://help.openai.com/en/articles/20001423-using-site-tools-in-the-chatgpt-desktop-app) | 2026-09-02 | ChatGPT Site Tools acceptance depends on the ChatGPT built-in-browser page/account flow. | Gemini/Chrome success does not substitute for that client-specific gate. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| before 2026-09-02T12:34:59+07:00 | Execute ordered context/structure prompt | live Chrome extension/Gemini client | two tool successes | `agentpress_get_context` request `49029206-eadd-4e22-b233-5d7b946189ef`; `agentpress_get_structure` request `9b735e81-c877-42cb-a391-724983cd772d`. |
| 2026-09-02T12:34:59+07:00 | Capture repository preflight and classify client/evidence boundary | repository | exit 0 | Clean synchronized baseline; prior built-in-browser inference requires correction. |
| 2026-09-02T12:37:18+07:00 | Update README/runbook, append EXP-025 attribution correction, and verify Markdown | repository | exit 0 | Local links resolve, fences are balanced, and `git diff --check` passes across five intended paths. |
| timestamp not independently captured | First evidence-closeout commit command | repository | exit 1 | An accidental trailing `Ring` argument was parsed as a pathspec; no commit was created and the verified staged files remained intact. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | Gemini invoked `agentpress_get_context` and `agentpress_get_structure` in the requested order with `{}` and both returned `ok: true`. | S1 and two request IDs | Supports tool discovery/execution compatibility for this client. |
| O2 | `OBSERVED` | Context reported an Administrator on HTTPS with the expected automatic, approval-required, and blocked-area envelope. | redacted S1 summary | Supports current-session identity/capability transport. |
| O3 | `OBSERVED` | Structure reported zero visible posts, 15 visible pages, the two public post taxonomies, one unassigned menu location, and `truncated: false`. | redacted S1 summary | Supports bounded structure execution on the live site. |
| O4 | `OBSERVED` | The final Gemini answer matched the role, HTTPS state, content counts, and capability classifications in the tool results. | S1 | Supports accurate summarization. |
| O5 | `OBSERVED` | Raw tool activity displayed customer URL, user display name, and page titles, including a draft title, despite the prompt asking not to reveal such data. | S1 | Falsifies public-recording safety on this customer session without redaction or a demo site. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Prior EXP-025 wording identified the green client as ChatGPT's built-in browser. | The operator clarified that the successful execution used a Chrome extension with a Gemini plugin/client. | Evidence-attribution correction. | Append a dated correction to EXP-025 and update README/runbook; AP-028 remains open. |
| C2 | Prompt-level instructions can keep sensitive fields out of visible tool traces. | The tool trace necessarily showed structured fields returned by the contracts before the model summarized them. | Recording/privacy limitation, not a tool-call failure. | Do not publicly record this raw customer session; use a synthetic demo/staging site or crop/redact approved footage. |
| C3 | A read-only success proves zero mutation. | No post-call database/object-count verification was supplied. | Verification boundary. | Treat zero mutation as intended and `NOT_TESTED`; add explicit before/after verification in the next run. |
| C4 | The first evidence-closeout commit command contains only the intended message. | An accidental `Ring` pathspec caused Git to reject the command before committing. | Operator command typo; no repository content changed. | Remove the stray argument, recheck the staged manifest/whitespace, and retry. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Record this as successful Gemini/Chrome alternate-client read execution, not ChatGPT Site Tools acceptance. | O1-O4, C1, S3 | Preserves useful interoperability evidence without overstating the challenge gate. | Same prompt succeeds in the supported ChatGPT built-in browser. |
| D2 | `DECIDED` | Do not store the raw transcript or supplied screenshot in the repository. | O5, privacy rules | Direct visual evidence remains external/uncommitted; customer privacy is preserved. | Owner supplies an explicitly approved redacted artifact. |
| D3 | `DECIDED` | Do not run a mutation or public recording on this customer session until customer authorization and redaction/demo-site controls are confirmed. | O5, C2-C3 | Slows the hero take; avoids exposing or changing customer data. | Safe staging/demo environment or explicit permission is confirmed. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Ordered alternate-client tool execution | Supplied transcript review | `PASS` | Two `ok: true` results and distinct request IDs. |
| Accurate bounded final summary | Result/answer comparison | `PASS` | Role, HTTPS, 0/15 counts, and capability groups agree. |
| No WordPress mutation | No after-state/database verification supplied | `NOT_TESTED` | Tools are read-only by contract, but live zero-mutation was not independently checked. |
| ChatGPT Site Tools acceptance | Wrong client for gate | `NOT_TESTED` | AP-028 remains open. |
| Public recording privacy | Raw trace inspection | `FAIL` | Customer identity/content visible in tool output. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-02-exp-026-gemini-chrome-read-smoke.md` | evidence | committed | `b65f082c042327199ed85f23d8a4ec9b2d35b3c3` | Sanitized facts and request IDs only. |
| raw Gemini/Chrome transcript | external session evidence | intentionally not stored | two request IDs above | Contains customer identifiers and page titles. |

## Result

`INCONCLUSIVE`

The Gemini/Chrome discovery, ordered calls, structured success, and final-summary accuracy are supported. The overall hypothesis remains `INCONCLUSIVE` because no live after-state/database check proved zero mutation, and the raw tool trace failed the public-recording privacy condition. This experiment does not close AP-028.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: database/object before-after equality, ChatGPT built-in-browser execution, Author role, write path, installed build checksum, exact browser/extension versions, public judge access, and 5/5 reliability.

## Competition evidence statement

- work attributable to challenge period: current timestamp and request IDs recorded;
- pre-existing work distinguished by: repository baseline recorded;
- third-party material/license/pin: Gemini/Chrome client is external and no third-party code was added;
- commit/PR evidence: evidence and attribution commit `b65f082c042327199ed85f23d8a4ec9b2d35b3c3`;
- live URL evidence: redacted live HTTPS execution only;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: raw transcript is unsuitable for public use due customer identifiers.

## Next experiment

- proposed experiment ID/task: AP-028 ChatGPT built-in-browser read/write smoke on a safe demo/staging site;
- next falsifiable question: can ChatGPT execute the same two reads and one idempotent synthetic post-draft workflow while preserving before/after counts and role boundaries?
- required prerequisites: safe environment/authorization, redacted account, installed build ID/checksum, exact client version, and a known existing category.

## End state

```text
git status --short --branch: five intended documentation/evidence paths modified or untracked; no unrelated changes observed
tests/checks: transcript/result comparison PASS; client attribution corrected; local links PASS; balanced fences PASS; git diff --check PASS
committed: b65f082c042327199ed85f23d8a4ec9b2d35b3c3
pushed: no
deployed: no repository deployment; live plugin installation is operator-reported
```
