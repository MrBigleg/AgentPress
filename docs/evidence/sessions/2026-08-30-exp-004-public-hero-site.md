# EXP-004 — Public AgentPress hero site documentation

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-004` |
| Related task | `AP-032` presentation artifact precursor |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T10:22:49+07:00` |
| Started UTC | `2026-08-30T03:22:49Z` |
| Ended local | `2026-08-30T10:29:10+07:00` |
| Ended UTC | `2026-08-30T03:29:10Z` |
| Agent/operator | Codex with project-owner supplied URL |
| Branch | `main` |
| Baseline commit | `41c5e2617604ed97ae73f44b70079d9329977b31` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows 10.0.26200; PowerShell 7.6.3; Git 2.55.0.windows.3; public HTTPS page returned HTTP 200 |

## Question

Can the new public ChatGPT Site be featured prominently in repository documentation while accurately presenting it as the AgentPress project hero page rather than proof of the unverified WordPress Site Tools workflow?

## Hypothesis

The supplied HTTPS page can serve as the README's primary project-overview link because it gives reviewers a faster visual introduction to the product thesis, provided the adjacent wording distinguishes the public presentation site from AP-028 live integration acceptance, AP-031 reliability evidence, and AP-032 submission completion.

## Falsification condition

The hypothesis is falsified if the URL is unreachable, does not represent AgentPress, requires private credentials for its hero content, or the documentation wording could reasonably be read as proof that the WordPress plugin, authenticated Site Tools execution, five-run reliability gate, or challenge submission is complete.

## Controls

- fixed baseline commit: `41c5e2617604ed97ae73f44b70079d9329977b31`;
- fixed product thesis, v0.1 scope, and non-goals from PRD v2;
- fixed AP-028, AP-031, and AP-032 acceptance gates;
- no plugin, architecture, implementation-specification, or deployment mutation;
- no claim that a public ChatGPT Site is the WordPress judge/demo URL;
- no commit, push, deployment, or publication without separate authorization.

## Variables

- **Independent:** README placement and description of `https://agentpress-webmcp.bigleg.chatgpt.site/`.
- **Dependent:** link reachability, product identity match, prominence, claim accuracy, local Markdown integrity, and worktree scope.

## Preflight

```text
timestamp local: 2026-08-30T10:22:49+07:00
timestamp UTC: 2026-08-30T03:22:49Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main [ahead 1]
git log -3 --oneline --decorate:
  41c5e26 (HEAD -> main) docs: add AgentPress research and evidence framework
  835a8d2 (origin/main, origin/HEAD) docs: publish AgentPress v0.1 product requirements
baseline SHA: 41c5e2617604ed97ae73f44b70079d9329977b31
current branch: main
unrelated existing changes: none shown; branch is one commit ahead of origin/main
AP task, issue, and PR reference: AP-032 presentation artifact precursor; no issue or PR supplied
environment: Windows 10.0.26200; PowerShell 7.6.3; Git 2.55.0.windows.3
```

Git emitted a sandbox permission warning for the user-level global ignore file during status inspection; repository status output was still returned. This warning is retained rather than treated as a source change.

## Method

1. Read the repository documents in the order required by `AGENTS.md` and continue from EXP-003's visual-presentation boundary.
2. Capture the repository baseline and preserve the clean worktree and unpushed commit.
3. Inspect the supplied HTTPS URL for reachability, AgentPress identity, visible purpose, and any claim that would conflict with repository evidence.
4. Add a concise, high-prominence README hero link that describes the page narrowly and preserves explicit verification boundaries.
5. Update this record during the work, append it to the evidence index, and run local Markdown/link/status checks.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used | n/a | n/a | Work performed and checked directly |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | Repository README, PRD v2, implementation specification, build checklist, evidence index, and EXP-003 at baseline | 2026-08-30 | Product positioning and verification boundaries | Current local repository |
| S2 | `OBSERVED` | `https://agentpress-webmcp.bigleg.chatgpt.site/` | 2026-08-30 | Public page identity, reachability, and concept labeling | Direct HTTPS response and embedded page source; HTTP 200 |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| `2026-08-30T10:22:49+07:00` | Required document reading and experiment selection | repository docs | `PASS` | AP-032 presentation precursor selected; AP-028/AP-031 remain separate gates |
| `2026-08-30T10:22:49+07:00` | Preflight commands | repository root | exit `0` | Clean status output; `main` ahead 1; baseline recorded |
| `2026-08-30T10:24:23+07:00` | Direct HTTPS inspection | public concept URL and embedded page | HTTP `200` | AgentPress title, product description, interactive concept, and concept footer observed |
| `2026-08-30T10:29:00+07:00` | Targeted link verification | public concept URL | HTTP `200` | README target remained reachable after edit |
| `2026-08-30T10:29:10+07:00` | Documentation checks | repository root | `PASS` | Hero image exists; four README fences balanced; `git diff --check` clean |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | The README currently opens with the product name and thesis but has no live project-overview call to action. | S1 / `README.md` | Supports a prominent hero link. |
| O2 | `OBSERVED` | The repository explicitly marks ChatGPT Site Tools integration, five-run reliability, and submission as `NOT_TESTED`. | S1 / README status table and AP-028/AP-031/AP-032 | Requires narrow presentation wording. |
| O3 | `DECIDED` | Treat the supplied URL as a public hero/project-overview page unless direct inspection establishes a different, narrower role. | O1, O2 | Prevents evidence-category drift. |
| O4 | `OBSERVED` | The supplied URL returned HTTP 200 and identifies itself as an AgentPress interactive concept/landing page for the WebMCP Challenge. | S2 | Supports the hero link. |
| O5 | `OBSERVED` | The concept page's illustrative snippet uses `navigator.modelContext.registerTool` and a slash-form name, while the implementation specification requires `document.modelContext.registerTool` and fixed underscore WebMCP names. | S1, S2 | Confirms that the page must remain presentation evidence, not runtime-contract evidence. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Repository status without warnings | Git could not access the sandboxed user-level global ignore file but returned repository status | environment warning | Retained in preflight; no repository impact observed |
| C2 | Concept snippet matches the resolved runtime contract | Page shows an older registration object and naming form | concept drift | Link labeled as an interactive concept; runtime truth remains in the implementation specification |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Place the URL near the README title as the public project overview. | O1, O2, O3 | High visibility with explicit evidence boundary | Direct page inspection contradicts its identity or purpose |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| URL is reachable and represents AgentPress | Direct HTTPS response and embedded content inspection | `PASS` | S2; HTTP 200 |
| README link is prominent and accurately labeled | Reviewed the focused README diff | `PASS` | Clickable approved wordmark plus “View the interactive AgentPress concept” above the fold |
| AP-028/AP-031/AP-032 claims remain unchanged | Focused diff review | `PASS` | Only README hero markup changed outside evidence files |
| Local Markdown targets and fences remain valid | Image existence, fence count, and `git diff --check` | `PASS` | Image exists; four fences balanced; no diff errors |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `README.md` | source documentation | tracked, modified | SHA-256 `665e85a73ae1b17abf85f4a0919d0b4634b0c83ae974d386c1a2af0187c211b4` | Clickable approved wordmark and concept link |
| `docs/evidence/sessions/2026-08-30-exp-004-public-hero-site.md` | evidence record | untracked | `UNCOMMITTED` | This session |
| `https://agentpress-webmcp.bigleg.chatgpt.site/` | external presentation URL | external | URL | Not yet inspected; not a release ZIP or WordPress demo URL |

## Result

`SUPPORTED`

The public URL is reachable, represents AgentPress, and is now available above the fold through a clickable approved wordmark and concise concept link. The wording does not promote the page to live WordPress, Site Tools, reliability, or submission evidence.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: real ChatGPT Site Tools discovery or tool execution.
- `NOT_TESTED`: WordPress plugin implementation or deployment.
- `NOT_TESTED`: AP-031 five-run reliability gate.
- `NOT_TESTED`: AP-032 challenge submission acceptance or judge workflow.
- `NOT_TESTED`: visual rendering of the revised README on GitHub; the markup and local image target were checked.
- `OBSERVED`: the concept page contains an older illustrative WebMCP registration/name form and should not be used as the implementation contract.

## Competition evidence statement

- work attributable to challenge period: current dated documentation experiment;
- pre-existing work distinguished by: baseline commit and existing EXP-001 through EXP-003 records;
- third-party material/license/pin: `NOT_APPLICABLE` to the URL link itself;
- commit/PR evidence: `UNCOMMITTED`; no issue or PR supplied;
- live URL evidence: inspection pending;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: EXP-005 / AP-001 implementation scaffold unless project sequencing changes;
- next falsifiable question: Can the minimal plugin activate on WordPress 6.9/PHP 8.0 and fail closed on unsupported versions?;
- required prerequisites: implementation authorization and recorded runtime environment.

## End state

```text
git status --short --branch: main ahead 1; README modified; EXP-004 record and evidence-index change uncommitted
tests/checks: public URL HTTP 200; hero image exists; README fences balanced; git diff --check passed
committed: no
pushed: no
deployed: no
```
