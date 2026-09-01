# EXP-020 — AP-014 bounded term reads

## Metadata

| Field | Value |
|---|---|
| Experiment | `EXP-020` |
| Related task | `AP-014`; GitHub issue #27 |
| Status | `COMPLETE` |
| Result | `SUPPORTED` |
| Started local | `2026-09-01T11:26:52+07:00` |
| Started UTC | `2026-09-01T04:26:52Z` |
| Ended local | `2026-09-01T11:40:47+07:00` |
| Ended UTC | `2026-09-01T04:40:47Z` |
| Agent/operator | Codex, implementation and verification agent |
| Branch | `ap-014-list-terms` |
| Baseline commit | `a2a65430277d1355d6f50fb655dbd9e6586ba57b` |
| Ending commit | `UNCOMMITTED` |
| Environment | Windows; PowerShell 7; Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; WordPress 6.9; PHP 8.0.30 |

## Question

Can `agentpress/list-terms` return deterministic bounded category/tag search and pagination results to authenticated readers while rejecting unsupported taxonomies and changing zero term or object state?

## Hypothesis

The fixed taxonomy allowlist and closed pagination schema make one narrow read service sufficient. WordPress term queries with stable ordering, exact category/tag projection, and defensive authority/type checks should satisfy all three fixture roles without mutation.

## Falsification condition

The hypothesis is false if category/tag fixtures differ from output; search, hide-empty, or pagination changes for a fixed fixture; a custom/unsupported taxonomy succeeds; any result violates its registered schema or bounds; a restricted reader receives an extra field; logged-out execution succeeds; or term/object state changes.

## Controls

- fixed build: synchronized green baseline `a2a65430277d1355d6f50fb655dbd9e6586ba57b`;
- fixture: synthetic categories/tags with fixed names, slugs, descriptions, hierarchy, counts, and a custom-taxonomy negative control;
- identities: Administrator, Author, Subscriber, logged out;
- policy: merged AP-007/AP-008 fixed catalog and live read check;
- environment: controlled WordPress 6.9/PHP 8.0.30 wp-env plus repository unit controls;
- exclusions: term creation/assignment, content mutation, UI, deployment, ChatGPT, release, and reliability.

## Variables

- **Independent:** role/user, taxonomy, search, hide_empty, page, per_page, and invalid/unsupported input.
- **Dependent:** returned IDs/order/pagination/fields, schema result, denial/error code, and term/object mutation count.

## Preflight

```text
timestamp: local 2026-09-01T11:26:52+07:00; UTC 2026-09-01T04:26:52Z
working directory: C:\Users\craig\01_Projects\WP-Agent-Admin
git status --short --branch: ## main...origin/main (clean)
git log -3 --oneline --decorate:
a2a6543 (HEAD -> main, origin/main, origin/HEAD) docs: record AP-013 merge evidence
a419fb2 Merge pull request #26 from MrBigleg/ap-013-content-reads
bf0ed55 docs: record AP-013 implementation evidence
baseline SHA: a2a65430277d1355d6f50fb655dbd9e6586ba57b
current branch: main; isolated ap-014-list-terms created after preflight
unrelated changes: none
AP task/issue/PR: AP-014; exact-title duplicate search empty; issue #27; PR pending
environment: Node.js 22.23.2; npm 11.7.0; Docker 29.6.1; baseline run 33469806837/job 99737083878 success
```

## Method

1. Open EXP-020/index and issue #27 on an isolated branch before product-code inspection/mutation.
2. Inspect the exact AP-008 schema, policy, dispatcher, WordPress term-query behavior, and fixture conventions.
3. Implement one protocol-independent service with fixed taxonomy allowlist, stable ordering, closed projection, and bounded pagination.
4. Wire only `list-terms`; keep AP-015+ unimplemented operations fail-closed.
5. Run a real three-role taxonomy/search/hide-empty/pagination/schema/unsupported/logged-out/zero-mutation matrix.
6. Run AP-004 through AP-013 regressions and all repository/package gates.
7. Commit, publish PR evidence, verify exact latest green head, merge, and append merge/main evidence.

## Parallel or delegated work

| Worker | Bounded task | Inputs | Returned evidence | How checked/synthesized |
|---|---|---|---|---|
| none | No subagents used. | N/A | N/A | Primary agent owns implementation and verification. |

## Source ledger

| ID | Evidence class | Source/version | Accessed | Claim supported | Notes |
|---|---|---|---|---|---|
| S1 | `OBSERVED` | Build checklist AP-014 at baseline `a2a6543` | 2026-09-01 | Acceptance requires exact category/tag fixtures, search/pagination, custom-taxonomy rejection, and Subscriber-safe data. | Runtime pending. |
| S2 | `DECIDED` | Implementation specification section 6.8 at baseline `a2a6543` | 2026-09-01 | Fixed input/output, allowlist, pagination, and annotations remain binding. | Exact source inspection follows record creation. |
| S3 | `OBSERVED` | EXP-014 and EXP-019 merged evidence | 2026-09-01 | Closed schema/policy registration and deterministic read-service patterns are green. | AP-014 behavior pending. |

## Execution log

| Time | Command/action | Working directory/target | Exit/result | Evidence |
|---|---|---|---|---|
| 2026-09-01T11:26:52+07:00 | Capture synchronized baseline, versions, exact-title issue search, and final AP-013 closeout CI | repository/GitHub | exit 0 | Clean full SHA; no AP-014 title; run/job above green. |
| timestamp not independently captured | Create issue #27, branch, EXP-020, and index entry | repository/GitHub | exit 0 | Material session isolated and recorded before product-code inspection/mutation. |
| timestamp not independently captured | Inspect exact list-terms schema, execution policy, dispatcher, and AP-013 read conventions | repository | exit 0 | Required taxonomy plus bounded search/hide-empty/page/per-page and seven-field result confirmed; policy rejects non-category/tag names before execution. |
| timestamp not independently captured | Implement TermReadService, narrow dispatch, package manifest, and AP-014 integration matrix | repository | uncommitted | Fixed taxonomy visibility check, term-ID ascending order, count/page queries, closed bounded projection, and three-role fixture controls added. Runtime pending. |
| timestamp not independently captured | Start/activate WordPress, syntax-check AP-014 files, and run first matrix | repository/wp-env | syntax exit 0; matrix exit 1 | Three-role schema/field/order controls passed through the first hide-empty assertion, which did not match the expected one category. Add bounded diagnostic output and rerun before classifying service versus fixture behavior. |
| timestamp not independently captured | Run bounded hide-empty diagnostic | repository/wp-env | exit 1 | WordPress returned the empty category ancestor plus its assigned child while `fields=count` returned one; this made `items` length inconsistent with `total`. Explicit non-hierarchical querying is required for literal hide-empty pagination while parent IDs remain projected. |
| timestamp not independently captured | Run corrected AP-014 matrix, PHPUnit, and first PHP standards gate | repository/wp-env | matrix/unit exit 0; lint exit 2 | Matrix passed all role/taxonomy/search/hide-empty/pagination/schema/denial/zero-mutation controls. PHPUnit passed 68/593. PHPCS found four mechanical alignment warnings in the corrected query array; align and rerun. |
| timestamp not independently captured | Rerun PHP standards | repository/wp-env | exit 0 | All 39 PHP files passed after the mechanical query-array alignment. |
| timestamp not independently captured | Run AP-004 through AP-013 real WordPress regressions | repository/wp-env | exit 0 | All ten prior matrices passed, including transport forbidden controls, zero unauthorized mutations, Change Set invariants, context/structure privacy, and content-read authority. |
| timestamp not independently captured | Run browser, provenance, audit, Node syntax, whitespace, and deterministic-package gates | repository | exit 0 | Browser 14/14; provenance 49 entries/no upstream runtime; audit zero vulnerabilities; Node scripts parse; `git diff --check` clean; two ZIPs matched SHA-256 `4FCF7F349A483D996307214E97B77650A1096B4D4224A64527D1BE3B924F6A21`. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-013 closeout commit is synchronized on main and its hosted workflow passed. | preflight | AP-014 dependency baseline is green. |
| O2 | `OBSERVED` | Exact-title search returned no AP-014 issue before #27 was created. | GitHub query | Issue #27 is not a duplicate. |
| O3 | `OBSERVED` | The fixed input has one required category/post_tag taxonomy plus bounded search, hide-empty, page, and per-page; output has only pagination and seven term fields. | implementation spec/catalog | One closed query/projection service covers the contract. |
| O4 | `OBSERVED` | Execution policy already rejects taxonomy names outside category/post_tag and requires authenticated read. | policy | Service should duplicate those checks for defense in depth. |
| O5 | `OBSERVED` | The final real WordPress matrix passed three roles/schema validations, three categories, two tags, two deterministic pages, search, two hide-empty controls, three unsupported/oversized denials, anonymous denial, and zero target mutation. | AP-014 integration matrix | Supports the AP-014 hypothesis in the controlled fixture. |
| O6 | `OBSERVED` | All ten prior runtime matrices and all repository/package gates passed against the AP-014 worktree. | execution log | No observed regression in the covered boundaries. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| none | No contradiction before source inspection. | N/A | N/A | Open. |
| F1 | Category hide-empty fixture should return only the assigned child. | First matrix reported a mismatch without exposing the actual bounded result. | pending test/service classification | Include the small result envelope in the assertion, clean stale fixture state on rerun, and inspect exact WordPress behavior. |
| F2 | WordPress term items and count should apply identical hide-empty semantics. | Hierarchical item queries added an empty ancestor, while the count query reported only the nonempty child. | implementation query defect | Set `hierarchical=false` for both count and item queries; retain `parent_id` in projection; rerun full matrix. |
| F3 | First PHP standards gate should pass after runtime behavior is green. | Four query-array arrows were misaligned after adding `hierarchical`. | implementation standards defect | Align the five keys mechanically and rerun. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Implement only category/post_tag reads and the exact registered term fields; no mutation or custom taxonomy. | S1/S2 | AP-017/AP-033 remain separate. | Fixed contract changes. |
| D2 | `DECIDED` | Use ascending `term_id` as the fixed deterministic order because the contract exposes no order input. | O3 | Stable pagination is reproducible but not alphabetical. | Product contract adds explicit ordering. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Category/tag fixture and deterministic pagination | three categories/two tags with exact term-ID pages | `PASS` | exact first/second category pages and tag totals/pages. |
| Search and hide-empty | fixed search plus category/tag nonempty controls | `PASS` | exact Bravo result; only assigned category/tag remained. |
| Unsupported/custom taxonomy denial | policy and direct-service custom taxonomy plus per_page=101 | `PASS` | three stable denials. |
| Three-role schema, logged-out denial, zero mutation | Administrator/Author/Subscriber outputs, anonymous permission, term/relationship snapshots | `PASS` | three schema validations; anonymous denied; zero changed fields/relationships. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-020-list-terms.md` | evidence | uncommitted | EXP-020 | Opened before product-code inspection/mutation. |
| `agentpress/includes/Terms/TermReadService.php` | implementation | uncommitted | AP-014 | Exact category/tag reader with stable bounded pagination. |
| `agentpress/tests/integration/ap014-list-terms.php` | executable evidence | uncommitted | AP-014 matrix | Synthetic role/taxonomy/search/hide-empty/schema/denial/mutation controls. |
| `dist/agentpress.zip` | generated package | excluded | SHA-256 `4FCF7F349A483D996307214E97B77650A1096B4D4224A64527D1BE3B924F6A21` | Two consecutive builds matched; 49 entries. |

## Result

`SUPPORTED`

`OBSERVED`: the controlled real-WordPress evidence supports the hypothesis. Category/tag fixtures, stable pagination, search, literal hide-empty behavior, fixed field projection, three-role schema validation, custom-taxonomy/oversize denial, anonymous denial, and zero mutation all passed. Prior runtime and repository gates remained green.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: representative production taxonomy timing/query budgets, wp-admin UI, deployment, real ChatGPT Site Tools, release installation, and five-run reliability.
- Local browser-contract tests passed but are not live Chrome/WebMCP or ChatGPT evidence.

## Competition evidence statement

- work attributable to challenge period: pre-mutation baseline/timestamps recorded;
- pre-existing work distinguished by: synchronized AP-013 closeout baseline;
- third-party material/license/pin: `NOT_APPLICABLE` pending inspection;
- commit/PR evidence: `UNCOMMITTED`; issue #27; PR pending at experiment conclusion;
- live URL evidence: `NOT_TESTED`;
- real ChatGPT Site Tools evidence: `NOT_TESTED`;
- five-run reliability evidence: `NOT_TESTED`;
- submission/video evidence: `NOT_TESTED`.

## Next experiment

- proposed experiment ID/task: `AP-015 — Implement create-draft`;
- next falsifiable question: can R1 draft creation force draft status, enforce type/role/parent/KSES boundaries, and replay one idempotent mutation through the Change Set coordinator?;
- required prerequisites: merged AP-010, AP-013, and AP-014 exact-head/merge-head hosted gates.

## End state

```text
git status --short --branch: AP-014 implementation, integration evidence, EXP-020, index, dispatcher, and build manifest uncommitted on ap-014-list-terms
tests/checks: AP-014 matrix pass; AP-004–AP-013 regressions pass; PHPUnit 68/593; PHPCS 39 files; browser 14/14; provenance 49 entries; audit 0; deterministic ZIP pass
committed: no
pushed: no
deployed: no
```
