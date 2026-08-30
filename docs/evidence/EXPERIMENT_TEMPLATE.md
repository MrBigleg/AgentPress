# EXP-NNN — Short experiment title

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-NNN` |
| Related task | `AP-###` or `none` |
| Status | `IN_PROGRESS` |
| Result | `PENDING` |
| Started local | `YYYY-MM-DDTHH:MM:SS+HH:MM` |
| Started UTC | `YYYY-MM-DDTHH:MM:SSZ` |
| Ended local | `PENDING` |
| Ended UTC | `PENDING` |
| Agent/operator | name and role |
| Branch | branch name |
| Baseline commit | full SHA |
| Ending commit | full SHA or `UNCOMMITTED` |
| Environment | relevant WordPress, PHP, browser/client, OS, and deployment versions |

## Question

State one falsifiable question.

## Hypothesis

State the expected result and why it is plausible.

## Falsification condition

State what observation proves the hypothesis wrong. Security tests must include zero unauthorized mutation.

## Controls

- fixed commit/build:
- fixed fixture/data:
- fixed identity/capabilities:
- fixed policy/configuration:
- fixed client/environment:
- explicit scope exclusions:

## Variables

- **Independent:** what changes between trials.
- **Dependent:** what is measured or observed.

## Preflight

```text
timestamp:
working directory:
git status --short --branch:
git log -3 --oneline --decorate:
baseline SHA:
unrelated existing changes:
```

## Method

1. Add reproducible step.
2. Include exact commands/tool/route names where safe.
3. Include reset steps and negative controls.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | | | | |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `SOURCE_VERIFIED` | primary URL or pinned source | ISO date | concise claim | uncertainty/conflict |

Community/secondary sources must be marked and cannot establish a normative contract alone.

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| ISO timestamp | safe exact command or action | path/URL | exit code/status | bounded output summary or artifact link |

Do not remove failed attempts. Append the correction and rerun.

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | | command/source/artifact | supports/falsifies/neutral |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| C1 | | | genuine blocker/doc drift/test defect/etc. | |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | | O#/S#/C# | | |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| | | `PASS/FAIL/NOT_TESTED` | |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| repository path or URL | source/test/screenshot/video/ZIP/deployment | tracked/untracked/external | hash or ID | redaction/limits |

## Result

Choose exactly one: `SUPPORTED`, `FALSIFIED`, `INCONCLUSIVE`, or `BLOCKED`.

Explain what the result establishes and what it does not establish.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`:

## Competition evidence statement

- work attributable to challenge period:
- pre-existing work distinguished by:
- third-party material/license/pin:
- commit/PR evidence:
- live URL evidence:
- real ChatGPT Site Tools evidence:
- five-run reliability evidence:
- submission/video evidence:

Use `NOT_TESTED`, `UNCOMMITTED`, or `NOT_APPLICABLE` rather than leaving ambiguous blanks.

## Next experiment

- proposed experiment ID/task:
- next falsifiable question:
- required prerequisites:

## End state

```text
git status --short --branch:
tests/checks:
committed:
pushed:
deployed:
```
