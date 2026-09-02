# EXP-025 — Live Site Tools checkpoint and recording plan

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-025` |
| Related task | `AP-028`, `AP-032` |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-02T11:57:16+07:00` |
| Started UTC | `2026-09-02T04:57:16Z` |
| Ended local | `2026-09-02T12:01:13+07:00` |
| Ended UTC | `2026-09-02T05:01:13Z` |
| Agent/operator | Craig, live browser operator; Codex, repository and recording-plan operator |
| Branch | `main` |
| Baseline commit | `fce8a8d7a6b251d5124ec0891881d4f3afb98f0c` |
| Ending commit | `c14383eb85a0ffc15d2ac239a78c676a778b9f86` |
| Environment | Customer-hosted HTTPS WordPress site; supported ChatGPT built-in browser and a comparison browser without WebMCP; exact domain, username, credentials, and customer content intentionally omitted |

## Question

Can the first live Site Tools checkpoint be preserved safely and converted into a truthful, low-risk, sub-three-minute recording plan that demonstrates only currently implemented AgentPress behavior?

## Hypothesis

The observed red-to-green WebMCP diagnostic contrast establishes browser-dependent discovery readiness. A preflighted Administrator hero path using context, site reads, one synthetic post draft, and existing-term assignment can demonstrate working WebMCP without publishing or modifying existing customer content; a separate Author session can preserve the permission-boundary proof.

## Falsification condition

The hypothesis is falsified if ChatGPT cannot call the implemented tools, a requested step selects an unimplemented tool, any operation publishes or alters existing customer content, the Author effective envelope fails to narrow, sensitive customer information must be committed, or the video cannot show real tool use inside three minutes.

## Controls

- fixed commit/build: repository baseline `fce8a8d7a6b251d5124ec0891881d4f3afb98f0c`; installed ZIP/build identifier not yet captured.
- fixed fixture/data: one uniquely titled synthetic post draft and one existing non-sensitive category; exact values to be chosen before the take.
- fixed identity/capabilities: Administrator for the positive hero path; Author for a separate capability/denial check.
- fixed policy/configuration: Safe Mode unchanged; no publication, existing-content edit, navigation change, term creation, plugin/theme/user/settings action, or third-party integration.
- fixed client/environment: supported ChatGPT built-in browser for Site Tools; unsupported browser only for a short diagnostic contrast if used.
- explicit scope exclusions: customer credentials/content, unimplemented AgentPress abilities, plugin upload/setup footage, deployment claims, and unverified 5/5 reliability claims.

## Variables

- **Independent:** browser WebMCP support and signed-in WordPress role.
- **Dependent:** bridge diagnostic, discovered/effective tools, context role, read results, draft/category mutation, denial behavior, and recording duration.

## Preflight

```text
timestamp: 2026-09-02T11:57:16+07:00 / 2026-09-02T04:57:16Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: clean main synchronized with origin/main
git log -3 --oneline --decorate: fce8a8d EXP-024 closeout; b806f6b plugin URI fix; 83c7fba AP-019 closeout
baseline SHA: fce8a8d7a6b251d5124ec0891881d4f3afb98f0c
current branch: main
unrelated existing changes: none observed
AP task, issue, PR: preliminary AP-028 live checkpoint and AP-032 recording planning; issue/PR not created
environment: exact live-site versions/build/checksum pending; sensitive site identity intentionally omitted
```

## Method

1. Classify the supplied live screenshot and operator observations without committing customer identity or credentials.
2. Reconcile the intended recording flow against the currently dispatched Ability implementations and project scope.
3. Verify current competition/video requirements against primary sources where available.
4. Write a concise recording runbook with exact paste-ready prompts, safety preflight, shot order, narration points, abort conditions, and truthful evidence boundaries.
5. Review links, Markdown, evidence language, changed-file scope, and repository status before any authorized commit/push.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary operator checks repository and sources directly. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | project-owner supplied screenshot and session report | 2026-09-02 | Plugin installed; unsupported browser showed red WebMCP bridge diagnostic; ChatGPT built-in browser showed green HTTPS, WordPress, Abilities API, and WebMCP diagnostics. | Customer domain/username omitted; this is not yet a recorded tool-execution result. |
| S2 | `OBSERVED` | `AbilityRegistrar.php` at baseline | 2026-09-02 | Seven abilities currently dispatch to concrete services: context, structure, content list/get, draft creation, term list, and term assignment. | Other registered catalog entries return the generic internal error until their tasks land. |
| S3 | `OBSERVED` | Devpost email supplied by project owner | 2026-09-02 | Video should be public on YouTube, have audio, remain under three minutes, show working behavior in the first 10–15 seconds, and center real tool use. | Official linked rules still require direct review. |
| S4 | `SOURCE_VERIFIED` | [WebMCP Challenge official rules](https://webmcp.devpost.com/rules) | 2026-09-02 | Submission requires a working judge-accessible URL, public licensed source repository, and public YouTube demo under three minutes with audio and functioning behavior. | Project must function as depicted/described; private apps may provide credentials in the submission form. |
| S5 | `SOURCE_VERIFIED` | [OpenAI: Using site tools in the ChatGPT desktop app](https://help.openai.com/en/articles/20001423-using-site-tools-in-the-chatgpt-desktop-app) | 2026-09-02 | Site Tools use the current built-in-browser page and signed-in session; tools are page/account-dependent and recent calls can be inspected from the address-bar indicator. | Passwords are entered on the site, never in chat. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-02T11:57:16+07:00 | Capture Git preflight and inspect checklist/dispatcher/tool-name map | repository | exit 0 | Clean synchronized baseline; AP-028/032 gates and seven concrete live dispatch paths identified. |
| 2026-09-02T11:58:00+07:00 | Review current Devpost rules and OpenAI Site Tools documentation | official web sources | success | Confirmed video, live URL, repository/licence, current-page/session, discovery, permission, and recent-tool behavior. |
| 2026-09-02T12:01:13+07:00 | Write and cross-check `docs/DEMO_RECORDING_RUNBOOK.md` | repository | exit 0 | Allowed prompt names exactly match the seven concrete adapter/dispatcher paths; no unimplemented tool name appears in a paste-ready prompt. |
| timestamp not independently captured | First local Markdown-link checker | repository | script error | Root-level `README.md` produced an empty parent-path argument; reported links were checker noise, not missing-file evidence. |
| timestamp not independently captured | Corrected local Markdown-link checker and `git diff --check` | repository | exit 0 | All local links resolve, all four files have balanced fences, and the diff has no whitespace errors. |
| timestamp not independently captured | Stage four intended files and run `git diff --cached --check` | repository | whitespace warning; commit still created | The runbook status line contained a Markdown hard-break pair of trailing spaces. The semicolon-chained shell command did not stop before commit `c14383e`; no unrelated file entered the commit. |
| timestamp not independently captured | Remove the trailing spaces and rerun fail-fast `git diff --check` | repository | exit 0 | Correction is included in the evidence-closeout commit before push; no whitespace errors remain. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | The supplied ChatGPT-browser screenshot shows all four connection diagnostics green and 15 tools exposed for the Administrator. | non-committed session screenshot | Supports live browser discovery readiness, not execution. |
| O2 | `OBSERVED` | The same installed plugin reportedly shows a red WebMCP bridge diagnostic in a browser without WebMCP. | project-owner report | Supports honest progressive-degradation behavior. |
| O3 | `OBSERVED` | The repository currently has concrete executors for only seven of the 15 registered catalog entries. | `AbilityRegistrar.php` | Requires prompts to constrain the recording to implemented calls and prevents a complete-v0.1 claim. |
| O4 | `OBSERVED` | The runbook provides a read-only rehearsal, one bounded idempotent draft/category/read-back workflow, and a separate Author page-denial prompt using only concrete service paths. | `docs/DEMO_RECORDING_RUNBOOK.md` and adapter map search | Supports a repeatable low-risk recording sequence. |
| O5 | `SOURCE_VERIFIED` | Official rules and OpenAI documentation support the runbook's under-three-minute, current-page/session, tool-activity, public-video, and judge-access requirements. | S4-S5 | Supports requirement alignment. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | A green 15-tool count implies all 15 operations are ready for a live demo. | Eight catalog entries currently fall through to `AP_INTERNAL_ERROR`. | Implementation-stage contradiction and demo risk. | Use only the seven concrete abilities; do not claim all 15 execute successfully. |
| C2 | The first lightweight link checker handles root-level files. | `Split-Path -Parent README.md` returned an empty string and caused false missing-link output. | Verification-script defect; repository links were not implicated. | Defaulted an empty parent to `.`; corrected run passed all local links. |
| C3 | The staged whitespace check prevents the commit when it reports a problem. | PowerShell continued to `git commit` because the commands were semicolon-chained; commit `c14383e` contains one trailing-space warning. | Commit-gate orchestration defect; content behavior unaffected. | Remove the whitespace in a closeout commit, verify with a fail-fast check, then push both commits together. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Use Administrator for the short positive hero take, then use Author for a separate permission proof/test clip. | S1, O3, checklist AP-028 | Strongest working path first while preserving role-boundary evidence. | Administrator path fails or customer authorizes only Author testing. |
| D2 | `DECIDED` | Limit all live mutations to one uniquely named synthetic post draft and assignment of one existing category; never publish. | project safety boundary and implemented services | Smaller feature breadth, substantially lower customer risk. | A staging clone becomes available. |
| D3 | `DECIDED` | Do not commit the supplied screenshot because it contains customer/site identity. | repository privacy rules | Loses a direct image artifact; preserves customer privacy. | Owner supplies an approved redacted capture. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Live checkpoint recorded without customer identifiers | Evidence/runbook review | `PASS` | Domain, username, credentials, and customer content omitted; screenshot intentionally not stored. |
| Prompts use only concrete abilities | Runbook names cross-checked against adapter map and dispatcher | `PASS` | Seven allowed tool names exactly match concrete paths; unimplemented names absent from paste-ready prompts. |
| Video plan matches current official requirements | Primary-source review | `PASS` | S4-S5; target 2:30, public YouTube/audio/functioning-project requirements stated. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-02-exp-025-live-site-tools-recording-plan.md` | evidence | committed | `c14383eb85a0ffc15d2ac239a78c676a778b9f86` | Contains only redacted live observations and planning evidence. |
| project-owner screenshot | screenshot | not stored | `NOT_APPLICABLE` | Contains customer identity; intentionally excluded from repository. |
| `docs/DEMO_RECORDING_RUNBOOK.md` | documentation | committed | `c14383eb85a0ffc15d2ac239a78c676a778b9f86` | Exact safety gate, prompts, shot order, narration, claims, and evidence capture; whitespace corrected before push. |

## Result

`SUPPORTED`

The planning hypothesis is supported. The live green/red browser diagnostic checkpoint is preserved without customer identifiers; the recording sequence is constrained to the seven concrete execution paths; and the shot order, narration, prompts, abort conditions, privacy controls, and submission requirements are now reproducible from the repository. This does not close AP-028: live ChatGPT tool execution and Author switching remain `NOT_TESTED`.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: live tool execution, Author role behavior in ChatGPT, published video, live judge access, release ZIP identity, and five consecutive reliability runs.

## Competition evidence statement

- work attributable to challenge period: current timestamp and repository baseline recorded;
- pre-existing work distinguished by: baseline commit recorded;
- third-party material/license/pin: no new third-party code;
- commit/PR evidence: runbook/evidence commit `c14383eb85a0ffc15d2ac239a78c676a778b9f86`; closeout correction pending;
- live URL evidence: redacted owner observation only;
- real ChatGPT Site Tools evidence: green discovery diagnostic observed; execution `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: planning only; video `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: AP-028 live Administrator and Author execution checkpoint;
- next falsifiable question: can the supported ChatGPT built-in browser call context and create exactly one safe synthetic draft while the Author envelope remains narrower?
- required prerequisites: choose synthetic title/category, capture installed build identifier if possible, and confirm no customer content will be shown or changed.

## End state

```text
git status --short --branch: four intended documentation/evidence paths modified or untracked; no unrelated changes observed
tests/checks: tool-name cross-check PASS; primary-source requirement check PASS; local links PASS; balanced fences PASS; git diff --check PASS
committed: c14383eb85a0ffc15d2ac239a78c676a778b9f86
pushed: no
deployed: no repository deployment; customer plugin installation is owner-reported
```
