# AgentPress

**The shared human-agent workspace for WordPress.**

<p align="center">
  <a href="https://agentpress-webmcp.bigleg.chatgpt.site/">
    <img src="docs/evidence/assets/approved/agentpress-approved-gradient-wordmark.png" alt="AgentPress" width="560">
  </a>
</p>

<p align="center">
  <a href="https://agentpress-webmcp.bigleg.chatgpt.site/"><strong>View the interactive AgentPress concept →</strong></a>
</p>

AgentPress is an open-source WordPress plugin project that will let ChatGPT work inside the WordPress session a human is already using. WordPress defines the user's maximum authority. AgentPress narrows that authority into actions the agent may perform automatically, actions requiring explicit human approval, and actions that remain unavailable.

> **Project stage:** early implementation. The AP-001 plugin scaffold now activates on the supported runtime and fails closed on the two minimum-version controls. Abilities, Site Tools transport, product UI, production release, and the live challenge workflow remain unimplemented or unverified.

## Product thesis

```text
ChatGPT Site Tools
        ↓
WebMCP adapter using the signed-in browser session
        ↓
AgentPress policy, Change Sets, approvals, and audit
        ↓
WordPress Abilities API
        ↓
WordPress capabilities and application logic
```

The canonical v0.1 experiment is to inspect a site, create content drafts, assign existing taxonomy, stage a navigation change, approve it visibly in WordPress, and prove that a restricted Author account cannot exceed its permissions.

## We are building this as a reproducible experiment

The repository is both the product source and the lab notebook for the WebMCP Challenge. Every material research or implementation session should leave enough evidence for another person to answer:

1. What question were we testing?
2. What did we expect to be true?
3. What was held constant?
4. What did we change or inspect?
5. What did we directly observe?
6. Which claims came from primary sources?
7. What failed or contradicted the hypothesis?
8. What decision followed, and what remains untested?
9. Which files, commands, commits, screenshots, or recordings prove the result?
10. Can another person repeat the test?

This is intentionally closer to a science experiment than a conventional progress diary.

### Evidence labels

Every evidence record uses these labels so that design intent is never confused with a working result:

| Label | Meaning |
|---|---|
| `OBSERVED` | Directly seen in the repository, command output, browser, database, or UI during the recorded session. |
| `SOURCE_VERIFIED` | Supported by a linked primary source or inspected upstream source code. |
| `INFERRED` | A reasoned conclusion from observations; not directly demonstrated. |
| `DECIDED` | A project choice made after considering the evidence. |
| `PROPOSED` | Planned work that has not been built or accepted. |
| `NOT_TESTED` | Explicit boundary where no current verification exists. |

An agent must not upgrade `PROPOSED` or `NOT_TESTED` to `OBSERVED` without running the relevant test.

### Experiment lifecycle

```text
Question
  -> hypothesis and falsification condition
  -> baseline commit/worktree capture
  -> controls and variables
  -> primary-source and repository inspection
  -> implementation or test
  -> observation ledger
  -> result: supported / falsified / inconclusive
  -> artifacts and hashes
  -> next experiment
```

The root [AGENTS.md](AGENTS.md) makes this protocol mandatory for coding agents. The [evidence index](docs/EVIDENCE_INDEX.md) is the append-only registry, and the [experiment template](docs/evidence/EXPERIMENT_TEMPLATE.md) is the standard session format.

## Experiment 001 — research to implementation-ready specification

On 30 August 2026, we treated the AgentPress PRD as a hypothesis rather than assuming it was technically build-ready.

### Research question

Can AgentPress v0.1 be reduced to a small, permission-safe architecture that uses current WordPress Abilities and WebMCP behavior, fits the challenge constraints, and gives an engineering agent an unambiguous build order?

### Initial hypothesis

The PRD's product direction was sound, and the existing open-source WordPress/WebMCP bridge could supply most generic transport plumbing, but the Ability catalog, approval storage, and bridge integration needed source-level validation before scaffolding.

### Controls

- Baseline repository commit: `835a8d241519544053a8c90b9047c665727b92bf`.
- Product scope and non-goals remained fixed by PRD v2.
- Primary target remained WordPress 6.9+, PHP 8.0+, HTTPS, and ChatGPT desktop Site Tools.
- No plugin code, GitHub issues, commit, deployment, or live browser workflow was created during the session.
- Claims were checked against the supplied primary reference set; community sources were not treated as normative contracts.

### Method

1. Read the 2,036-line PRD, README, security policy, contribution guidance, license, worktree state, and recent history.
2. Split research into three read-only tracks:
   - local PRD/repository requirements and contradictions;
   - current WordPress Abilities and MCP Adapter contracts;
   - current WebMCP, ChatGPT Site Tools, challenge rules, and bridge source.
3. Inspect the current primary documentation and upstream implementation instead of relying on product summaries.
4. Record only contradictions that would change code, security, packaging, or the demo.
5. Resolve those contradictions in an implementation specification before producing tasks.
6. Derive an issue-ready build checklist from the resolved architecture.
7. Run structural checks on tool/task counts, names, Markdown fences, local links, and the final worktree.

### Main observations and decisions

- `OBSERVED`: PRD v2 named 17 tools while imposing a maximum of 15.
- `DECIDED`: combine the three bootstrap reads into `agentpress/get-context`, leaving 15 distinct Ability contracts.
- `SOURCE_VERIFIED`: current WebMCP uses `document.modelContext.registerTool`; the audited upstream bridge used an obsolete registration path.
- `SOURCE_VERIFIED`: WordPress REST cookie identity requires a valid `wp_rest` nonce; the audited upstream bridge's read/write nonce behavior was not suitable for AgentPress.
- `DECIDED`: ship one AgentPress-owned, same-origin adapter derived from selected GPL-compatible upstream patterns rather than install the upstream plugin unchanged.
- `OBSERVED`: the PRD's approval tables could not represent immutable proposals, stale-target checks, expiry, approvers, or idempotency.
- `DECIDED`: retain three tables but add the hashes, states, actors, timestamps, and indexes required to enforce those invariants.
- `DECIDED`: support classic menus on the challenge fixture and fail clearly for block Navigation in v0.1.
- `OBSERVED`: the resulting specification contains 15 unique Ability contracts and 15 unique WebMCP names.
- `OBSERVED`: the build checklist contains 36 bounded tasks; every task has dependencies and an acceptance test.
- `NOT_TESTED`: no WordPress runtime, ChatGPT Site Tools discovery, permission matrix, approval flow, or 5/5 demo run exists yet.

The complete method, sources, observations, falsification conditions, artifact hashes, and limitations are preserved in [Experiment 001](docs/evidence/sessions/2026-08-30-exp-001-research-to-spec.md).

## Approved visual direction and concepts

The project owner identified the following identity sheet as the approved visual direction and later added a standalone gradient wordmark to the approved collection. These are visual/design evidence only; they do not prove the depicted plugin screens or `agentpress.dev` examples exist.

![AgentPress approved identity system](docs/evidence/assets/approved/agentpress-approved-identity-system.png)

The standalone approved wordmark is linked through the asset ledger rather than repeated here. Four additional sheets are preserved as concepts and references. They are useful for future interface and submission design, but they are not final product contracts. In particular, the concept icon library contains older Ability/status ideas that must not override the implementation specification.

See the [visual asset ledger](docs/evidence/assets/README.md) for classification, original filenames, hashes, provenance limits, and permitted use. The move/classification session is recorded as [Experiment 003](docs/evidence/sessions/2026-08-30-exp-003-visual-assets.md).

## Current evidence-backed status

| Area | Status | Evidence |
|---|---|---|
| Product scope | `OBSERVED` | [PRD v2](docs/PRD.md) |
| Technical architecture | `DECIDED`, not implemented | [Implementation specification](docs/IMPLEMENTATION_SPEC.md) |
| Engineering sequence | `PROPOSED`, acceptance tests defined | [Build checklist](docs/BUILD_CHECKLIST.md) |
| WordPress plugin scaffold | `OBSERVED`, uncommitted | [Experiment 005](docs/evidence/sessions/2026-08-30-exp-005-plugin-scaffold.md) |
| ChatGPT Site Tools integration | `NOT_TESTED` | AP-028 acceptance gate |
| Canonical workflow reliability | `NOT_TESTED` | AP-031 requires five consecutive passes |
| Challenge submission | `NOT_TESTED` | AP-032 defines the submission evidence gate |

## Next experiment

The next dependency-ordered implementation experiment is `AP-002 — Pin and attribute the WebMCP bridge source`.

**Hypothesis:** the audited upstream bridge material can be pinned and attributed without shipping obsolete registration code, generic tools, public discovery, or upstream settings behavior.

**Falsification condition:** a missing or ambiguous license/pin, unrecorded adapted material, or any unrelated upstream runtime behavior in the AgentPress source or ZIP falsifies the hypothesis.

**Prerequisite evidence:** AP-001 is `SUPPORTED` locally in Experiment 005 but remains `UNCOMMITTED`; no push or deployment has been performed.

The detailed task and acceptance test are in the [build checklist](docs/BUILD_CHECKLIST.md#ap-002--pin-and-attribute-the-webmcp-bridge-source).

## Local development

Prerequisites are Node.js 20+, Docker Desktop, and Git. Host PHP and Composer are optional because `wp-env` supplies them inside the CLI container.

```powershell
npm install
npm run env:start
npm run env:activate
npm run test:unit
npm run lint:php
npm run build:zip
```

The supported environment is WordPress 6.9 on PHP 8.0. Separate `.wp-env.wp68.json` and `.wp-env.php74.json` configurations preserve the two fail-closed controls. The generated ZIP is written to `dist/agentpress.zip`; it is intentionally ignored by Git and must not be treated as release evidence until AP-030.

## Product principles

- Use the logged-in WordPress identity—no copied API keys or application passwords.
- Build capabilities on the WordPress Abilities API so core behavior remains protocol-independent.
- Allow safe, reversible work such as reading and creating drafts.
- Stage consequential changes such as publishing and navigation edits for approval in wp-admin.
- Revalidate authorization server-side for every execution.
- Keep a visible, sanitized audit trail of agent activity.
- Never expose sensitive administration, arbitrary code execution, credentials, or privilege escalation.
- Prefer a smaller workflow that passes 5/5 over broader unverified compatibility.

## Documentation

- [Product requirements](docs/PRD.md)
- [Implementation specification](docs/IMPLEMENTATION_SPEC.md)
- [Dependency-ordered build checklist](docs/BUILD_CHECKLIST.md)
- [Evidence index](docs/EVIDENCE_INDEX.md)
- [Experiment template](docs/evidence/EXPERIMENT_TEMPLATE.md)
- [Visual asset ledger](docs/evidence/assets/README.md)
- [Agent working agreement](AGENTS.md)
- [Contributing](CONTRIBUTING.md)
- [Security policy](SECURITY.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)

## Contributing

Discussion and focused contributions are welcome. Read [CONTRIBUTING.md](CONTRIBUTING.md) before proposing implementation changes. Material work must add or update an evidence record. Security issues should be reported privately as described in [SECURITY.md](SECURITY.md).

## License

AgentPress is licensed under the [GNU General Public License v2.0 or later](LICENSE).
