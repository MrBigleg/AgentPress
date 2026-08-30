# AgentPress Agent Working Agreement

This file applies to the entire repository. Its purpose is to make agent work reproducible and suitable as WebMCP Challenge evidence, not merely to produce code quickly.

## Required reading order

Before material research, planning, implementation, testing, or release work, read:

1. `README.md`
2. `docs/PRD.md`
3. `docs/IMPLEMENTATION_SPEC.md`
4. `docs/BUILD_CHECKLIST.md`
5. `docs/EVIDENCE_INDEX.md`
6. the experiment record for the task being continued

Do not silently broaden the v0.1 scope. The PRD's non-goals and the implementation specification's resolved decisions remain binding unless the user explicitly changes them and the decision is recorded.

## Material work requires an experiment record

Material work includes source research, architecture decisions, code behavior, security boundaries, database changes, UI behavior, browser verification, performance/reliability tests, packaging, deployment, and challenge submission work.

For each material session:

1. Choose the relevant `AP-###` task from `docs/BUILD_CHECKLIST.md`.
2. Copy `docs/evidence/EXPERIMENT_TEMPLATE.md` to `docs/evidence/sessions/YYYY-MM-DD-exp-NNN-short-slug.md`.
3. Allocate the next unused experiment number from `docs/EVIDENCE_INDEX.md`.
4. Fill the question, hypothesis, falsification condition, baseline, controls, variables, and planned method before changing code.
5. Update the record during the session rather than reconstructing a success narrative afterward.
6. Finish with `SUPPORTED`, `FALSIFIED`, `INCONCLUSIVE`, or `BLOCKED`.
7. Append the record to `docs/EVIDENCE_INDEX.md`.

A trivial typo or formatting-only change may append to the current experiment instead of opening a new one, but it must not be used to smuggle in a product or security decision.

## Evidence language is mandatory

Use these exact labels for material claims:

- `OBSERVED`: directly seen in current repository, command, browser, database, API, or UI output;
- `SOURCE_VERIFIED`: supported by a linked primary source or inspected upstream source;
- `INFERRED`: reasoned from evidence but not directly demonstrated;
- `DECIDED`: a project choice made from the evidence;
- `PROPOSED`: planned but not implemented or accepted;
- `NOT_TESTED`: explicit verification boundary.

Never describe a specification, mock, passing unit test, repository commit, deployment, or live browser result as interchangeable evidence. In particular:

- source code present is not proof that the deployed site runs it;
- Chrome/WebMCP inspector success is not ChatGPT Site Tools acceptance;
- a model saying a user approved something is not wp-admin approval;
- one successful demo is not the required 5/5 reliability result;
- planned tests are not passing tests;
- an uncommitted file is not dated commit-history evidence.

## Session preflight

Record these before mutation:

```text
local timestamp and UTC timestamp
git status --short --branch
git log -3 --oneline --decorate
baseline full commit SHA
current branch
existing unrelated changes
AP task, issue, and PR reference
environment versions relevant to the experiment
```

Preserve unrelated work. Recheck status immediately before any commit. Do not commit, push, open issues or PRs, deploy, or publish evidence unless the user has authorized that action.

## Scientific method for engineering work

Each experiment must make the following explicit.

### Question

One falsifiable question. Avoid “build feature X” as the question; prefer “Can X satisfy Y under Z constraints?”

### Hypothesis

The expected result and why current evidence makes it plausible.

### Falsification condition

The observation that proves the hypothesis wrong. Permission/security experiments must include zero unauthorized mutation, not only an HTTP error.

### Controls

What stays fixed: commit/build, WordPress/PHP/browser versions, fixture, user role/capabilities, Safe Mode, network/deployment target, and input dataset.

### Variables

- independent: the identity, capability, input, target state, client, or code change under test;
- dependent: discovered tools, result/error, WordPress mutation, AgentPress state/audit, UI update, timing, or reliability outcome.

### Method

Numbered, repeatable steps. Include exact commands and route/tool names where safe. If subagents are used, record each bounded research task and how its findings were checked or synthesized.

### Observations

Record failures and contradictions as they occur. Do not delete an inconvenient observation; append a correction or explanation.

### Conclusion

State whether the hypothesis was supported, falsified, inconclusive, or blocked. Separate what is now verified from what remains untested.

## Source and command evidence

- Prefer WordPress core/source, official WordPress documentation, the current WebMCP draft/Chrome documentation, official OpenAI documentation, and official challenge rules.
- Community lists and summaries may locate sources but are not normative contracts.
- For unstable technical claims, record access date and pinned commit/version where possible.
- Record commands, working directory, exit code, and a concise output summary. Do not paste enormous logs into Markdown; store a bounded artifact or hash and link it.
- If a test initially fails, keep the failure and rerun evidence in order.
- Mark assumptions explicitly as `INFERRED` until verified.

## Artifact rules

Each experiment record must list:

- changed files;
- tests/checks and outcomes;
- artifact paths or URLs;
- SHA-256 for release ZIPs, important generated evidence, and externally hosted recordings when obtainable;
- starting and ending commit SHA, or `UNCOMMITTED` when no commit was authorized;
- deployment URL and deployment identifier only when actually verified;
- screenshots/video timestamps for visual claims.

Small, non-sensitive experiment screenshots may go under `docs/evidence/assets/EXP-NNN/`. Long-lived approved or concept visuals go under `docs/evidence/assets/approved/` or `docs/evidence/assets/concepts/` and must be registered in `docs/evidence/assets/README.md`. Never promote a concept to approved without explicit project-owner direction. Large recordings should be externally hosted and linked with date/hash metadata. Do not commit generated dependency trees, raw database dumps, complete logs, or private browser profiles.

## Secret and privacy rules

Never record or commit:

- passwords, authentication cookies, REST nonces, application passwords, API keys, authorization headers, or session tokens;
- real private site content or personal data;
- database credentials, salts, full environment files, or private challenge credentials;
- unredacted request/response headers.

Use synthetic fixtures and redact before hashing or storing evidence. A hash does not make secret source material safe to commit if the underlying file is also present.

## Competition evidence requirements

Evidence must make it easy to distinguish work performed during the challenge period from pre-existing work:

- use ISO 8601 timestamps with timezone plus UTC;
- record the baseline commit and resulting commit/PR when authorized;
- link the exact source files added or changed;
- distinguish original AgentPress work from adapted third-party code and record license/pin;
- preserve dated tests for the Administrator-versus-Author permission proof;
- preserve the real ChatGPT Site Tools run separately from inspector/E2E tests;
- preserve all five consecutive reliability runs, including failed attempts and counter resets;
- record the release ZIP checksum, live judge URL verification, public repository state, and sub-three-minute video URL before submission.

Do not alter a historical experiment to make later results appear earlier. Append a new experiment or a clearly dated correction.

## End-of-session checklist

Before reporting completion:

- update the experiment observations, result, limitations, and next experiment;
- update `docs/EVIDENCE_INDEX.md`;
- update the README status table only if project-level evidence changed;
- ensure every claim uses the appropriate evidence label;
- verify local Markdown links and balanced fences;
- run tests/checks proportional to the change;
- record current `git status --short --branch`;
- state explicitly what was not tested, not committed, not pushed, and not deployed.

The goal is a durable evidence trail that a judge, contributor, or future agent can audit without access to the original chat transcript.
