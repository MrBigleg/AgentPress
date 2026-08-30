# EXP-006 — Judge-aligned extension research

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-006` |
| Related task | `AP-032` presentation precursor |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-08-30T10:47:40+07:00` |
| Started UTC | `2026-08-30T03:47:40Z` |
| Ended local | `2026-08-30T10:51:45+07:00` |
| Ended UTC | `2026-08-30T03:51:45Z` |
| Agent/operator | Codex research agent |
| Branch | `main` |
| Baseline commit | `89b4def3a2b4ae4d1b463885a787e2a3e45e1dda` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows PowerShell; repository and public-source research only; no WordPress, PHP, browser workflow, or deployment test |

## Question

Can a one-click, agent-directed WordPress-to-Astro/Cloudflare export and a bounded set of bonus concepts be ranked against source-verified WebMCP Challenge judging incentives without changing AgentPress v0.1 scope?

## Hypothesis

The export concept can create a compelling reversible-deployment demonstration, but judge-aligned bonus concepts will score better when they strengthen AgentPress's existing permission, approval, audit, and cross-application story rather than becoming an unrelated hosting product.

## Falsification condition

The hypothesis is falsified if the official judging criteria or organizer materials do not reward usefulness, WebMCP-native execution, technical quality, or demonstrable real-world impact, or if the repository already includes export/hosting in v0.1 scope.

## Controls

- fixed commit/build: baseline commit above; research-only session
- fixed fixture/data: current PRD v2, implementation specification, checklist, official current event data
- fixed identity/capabilities: no authenticated WordPress mutation
- fixed policy/configuration: v0.1 non-goals remain binding
- fixed client/environment: current Codex session and official Devpost/OpenAI/public primary sources
- explicit scope exclusions: no implementation, issue creation, commit, push, deployment, registration, or submission

## Variables

- **Independent:** proposed extension and the verified interests/criteria used to evaluate it.
- **Dependent:** relevance, differentiation, demo value, implementation risk, and fit with the challenge criteria.

## Preflight

```text
timestamp: 2026-08-30T10:47:40+07:00 / 2026-08-30T03:47:40Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: main tracking origin/main; docs/EVIDENCE_INDEX.md modified; EXP-005 untracked
git log -3 --oneline --decorate: 89b4def; 7f4dc00; 41c5e26
baseline SHA: 89b4def3a2b4ae4d1b463885a787e2a3e45e1dda
unrelated existing changes: modified docs/EVIDENCE_INDEX.md and untracked EXP-005 plugin scaffold record; preserve both
```

## Method

1. Read the required repository documents and current experiment queue.
2. Fetch the current WebMCP Challenge overview, judging criteria, prizes, submission requirements, and announcements from the Devpost Hackathons source of truth.
3. Use primary public sources for named people and organizations when individual interests are not present in Devpost event data.
4. Separate direct observations, source-verified facts, and inferred interest alignment.
5. Rank ten proposed additions by judge alignment, product coherence, demo clarity, and deadline risk.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | no subagents used | | | |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | repository documents at baseline | 2026-08-30 | v0.1 scope and explicit non-goals | current worktree inspected |
| S2 | `SOURCE_VERIFIED` | [OpenAI WebMCP Challenge](https://openai.com/webmcp-challenge/) | 2026-08-30 | named judges, prize supporters, dates, required public materials, and organizer judging themes | official organizer page; Devpost remains authoritative for formal rules |
| S3 | `SOURCE_VERIFIED` | [Cloudflare: Building an open Agentic Internet](https://blog.cloudflare.com/the-agentic-internet/) | 2026-08-30 | Andrew Galloni co-authored the readable/discoverable/callable/payable framing | current official company publication |
| S4 | `SOURCE_VERIFIED` | [Cloudflare Astro guide](https://developers.cloudflare.com/workers/framework-guides/web-apps/astro/) and [Deploy Hooks](https://developers.cloudflare.com/workers/ci-cd/builds/deploy-hooks/) | 2026-08-30 | Astro static output can deploy to Workers; hooks can trigger CMS builds and deduplicate queued triggers | deploy hook URL is a credential and must remain secret |
| S5 | `SOURCE_VERIFIED` | [Shopify: Universal Commerce Protocol](https://shopify.engineering/ucp) | 2026-08-30 | Ilya Grigorik emphasizes discovery, negotiation, layered extensibility, and graceful human handoff | official authored publication |
| S6 | `SOURCE_VERIFIED` | [MCP-B organization](https://github.com/WebMCP-org) and [Alex Nahas profile](https://github.com/MiguelsPizza) | 2026-08-30 | browser-native interoperability, origin security, local-first systems, sync engines, and WebAI | public first-party project/profile evidence |
| S7 | `SOURCE_VERIFIED` | [Netlify: MCP goes stateless and extensible](https://www.netlify.com/blog/mcp-goes-stateless-and-extensible/) | 2026-08-30 | Sean Roberts emphasizes agent discovery, reliable calls, recovery, and stateless operational simplicity | official authored publication |
| S8 | `SOURCE_VERIFIED` | [Vercel: AGENTS.md evals](https://vercel.com/blog/agents-md-outperforms-skills-in-our-agent-evals) | 2026-08-30 | Jude Gao emphasizes measured agent correctness and compact, current framework guidance | official authored publication |
| S9 | `SOURCE_VERIFIED` | [Chrome WebMCP guide](https://developer.chrome.com/docs/ai/webmcp) and [agent security considerations](https://developer.chrome.com/docs/agents/security) | 2026-08-30 | WebMCP is progressive enhancement; visible tools, same-origin boundaries, and prompt-injection defenses matter | official platform documentation |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-08-30T10:47:40+07:00 | repository preflight and required-document review | repository root | success | baseline and unrelated work captured |
| 2026-08-30T10:50:00+07:00 | Devpost overview/criteria/prizes/requirements/announcements fetch | Devpost Hackathons MCP | failed: internal error | no Devpost-owned event facts treated as fetched or verified; no retry per plugin runtime rule |
| 2026-08-30T10:53:00+07:00 | official organizer and primary-source research | OpenAI, judge/company publications, platform documentation | success | sources S2-S9 |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | Hosting, scheduled/background agents, and broad integrations are explicitly excluded from v0.1. | `docs/BUILD_CHECKLIST.md`; `docs/IMPLEMENTATION_SPEC.md` | supports separate bonus-track treatment |
| O2 | `SOURCE_VERIFIED` | OpenAI names Sarah Drasner, Andrew Galloni, Jude Gao, Ilya Grigorik, Alex Nahas, Sean Roberts, and Justin Rushing as judges. | S2 | enables a bounded interest map |
| O3 | `SOURCE_VERIFIED` | OpenAI lists additional prizes from Shopify, Google Chrome, Netlify, Cloudflare, Vercel, and Render; OpenAI supplies the main cash and product prizes. | S2 | identifies the sponsor/supporter set |
| O4 | `SOURCE_VERIFIED` | The organizer says submissions are evaluated on usefulness, originality, execution, thoughtful WebMCP use, and human-agent experience. | S2 | supports ranking by product coherence and demonstrated workflow, not logo targeting |
| O5 | `SOURCE_VERIFIED` | Astro static output and Cloudflare Workers deployment are technically supported, and a CMS-triggered deployment can use a Deploy Hook. | S4 | supports feasibility of a bounded export path |
| O6 | `INFERRED` | The cross-judge overlap is trustworthy agent actuation: discoverable tools, explicit capability boundaries, graceful human handoff, recovery, security, portability, and measured reliability. | S3, S5-S9 plus judge roles in S2 | supports a deployment Change Set rather than an opaque one-click mutation |
| O7 | `INFERRED` | A WordPress-to-Astro export cannot honestly promise pixel-perfect migration for arbitrary themes/plugins; a supported-content manifest plus explicit incompatibility report is a more defensible contract. | repository non-goals and S4 | narrows the bonus concept to a falsifiable demo |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | Live Devpost event data would establish formal criteria and submission details. | All requested Devpost MCP reads returned an internal error. | source availability failure | formal Devpost rules/weights remain `NOT_TESTED`; official OpenAI organizer facts are used only for the strategic map |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Treat all export/hosting ideas as post-v0.1 proposals, not active milestone scope. | O1 | preserves demo reliability | after AP-031 is green |
| D2 | `DECIDED` | Frame the export as a `Static Launch Change Set`: inventory, preview, semantic/visual diff, explicit approval, deploy receipt, and rollback. | O4-O7 | more work than a blind export, but substantially stronger trust and demo value | if the challenge deadline prevents a complete end-to-end proof |
| D3 | `DECIDED` | Judge-interest statements must remain `INFERRED`; they are not private preferences or promised scoring behavior. | O2, O6 | less certainty, more honest strategy | direct public statement from a judge about this entry |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Organizer judging themes captured | official OpenAI challenge page inspected | `PASS` | S2, O4 |
| Formal Devpost rules/weights captured | Devpost MCP fetch attempted | `NOT_TESTED` | C1 |
| Named judges/supporters separated from inferred interests | source and observation ledgers reviewed | `PASS` | O2, O3, O6, D3 |
| Export feasibility bounded | current Cloudflare Astro and Deploy Hook docs inspected | `PASS` | O5, O7 |
| Ten concepts ranked with scope risk | strategic synthesis completed | `PASS` | D1-D2; user-facing ranked list |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-08-30-exp-006-judge-aligned-extension-research.md` | research record | untracked | `EXP-006` | no secrets; final file hash intentionally omitted because the record contains its own inventory |

## Result

`SUPPORTED`

The hypothesis is supported at the strategy level. Official organizer and primary-source evidence favors useful, original, reliable, human-visible WebMCP workflows, while the repository explicitly keeps hosting outside v0.1. A staged and reversible Static Launch Change Set is therefore a coherent post-core extension. This result does not establish formal Devpost scoring weights, implementation feasibility across arbitrary WordPress sites, or actual judge preference for any proposed feature.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: all proposed product behavior, formal Devpost rules/weights, Cloudflare/Astro deployment, arbitrary theme/plugin conversion, real ChatGPT execution, and judge reactions.

## Competition evidence statement

- work attributable to challenge period: this dated research record
- pre-existing work distinguished by: baseline commit and repository history
- third-party material/license/pin: external research is linked in S2-S9; no third-party code or assets added
- commit/PR evidence: `UNCOMMITTED`
- live URL evidence: `NOT_TESTED`
- real ChatGPT Site Tools evidence: `NOT_TESTED`
- five-run reliability evidence: `NOT_TESTED`
- submission/video evidence: `NOT_TESTED`

## Next experiment

- proposed experiment ID/task: post-AP-031 extension spike
- next falsifiable question: can the highest-ranked extension be demoed without weakening the canonical workflow?
- required prerequisites: AP-031 green and explicit owner approval to expand scope

## End state

```text
git status --short --branch: main; concurrent AP-001 scaffold files and index changes preserved; EXP-006 untracked
tests/checks: research-only
committed: no
pushed: no
deployed: no
```
