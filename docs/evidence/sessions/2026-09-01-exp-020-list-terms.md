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
| Ending commit | `9727e3b3349da43796620a688bb5e71a850b2183` |
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
| 2026-09-01T11:50:13+07:00 | Inspect PR #28 reviews, inline comments, conversation, and exact-head CI | GitHub | two actionable comments; CI success | Head `e53c6904a8183b287fdd32bb58065fbcb12acb97` passed run `33471128943`/job `99740939409`, but inline comments `3900867864` and `3900867872` identified multibyte-length and page-offset-overflow defects. Do not merge. |
| timestamp not independently captured | Correct review findings and add regression controls | repository | uncommitted | Search validation now counts characters; item query/offset is skipped for pages beyond computed total pages, so no multiplication occurs; 100-CJK-character and `PHP_INT_MAX` controls added. Rerun pending. |
| timestamp not independently captured | Run post-review AP-014 matrix and repository gates | repository/wp-env | exit 0 | Updated matrix passed two search controls and one extreme-page control plus all prior AP-014 assertions; PHPUnit 68/593; PHPCS 39 files; browser 14/14; provenance 49 entries; audit zero; Node/whitespace clean; two ZIPs matched updated SHA-256 `8054C0A80AC46031DD8FFABBCF07EE32024AE6423144C788EE4ED950ABF45325`. |
| 2026-09-01T15:11:25+07:00 | Reconcile user-reported merge with local review fixes | repository/GitHub | merged base green; corrections uncommitted | PR #28 merged reviewed head `e53c690` as `4d89dbeef5deb73b4c63e3526b5f3b424bbd58b8`; run `33485764519` succeeded. The two validated corrections were not in that merge, issue #27 remained open, and a narrow corrective PR is required. |
| 2026-09-01T15:49:00+07:00 | Verify corrective hosted head, comments, merge, and exact merge workflow | GitHub | exit 0; success | PR #29 head `fe003dd8de253c9f8a6b2479e1e54155c0688797` had no reviews/inline/conversation comments and passed run `33488848270`/job `99795253520`; merged as `d62c63435c6e1af939e7aa3afc3dffd9b71cb979`; corrected `main` passed run `33488950892`/job `99795591186`; issue #27 closed with evidence comment. |

## Observation ledger

| ID | Label | Observation | Evidence pointer | Effect on hypothesis |
|---|---|---|---|---|
| O1 | `OBSERVED` | AP-013 closeout commit is synchronized on main and its hosted workflow passed. | preflight | AP-014 dependency baseline is green. |
| O2 | `OBSERVED` | Exact-title search returned no AP-014 issue before #27 was created. | GitHub query | Issue #27 is not a duplicate. |
| O3 | `OBSERVED` | The fixed input has one required category/post_tag taxonomy plus bounded search, hide-empty, page, and per-page; output has only pagination and seven term fields. | implementation spec/catalog | One closed query/projection service covers the contract. |
| O4 | `OBSERVED` | Execution policy already rejects taxonomy names outside category/post_tag and requires authenticated read. | policy | Service should duplicate those checks for defense in depth. |
| O5 | `OBSERVED` | The final real WordPress matrix passed three roles/schema validations, three categories, two tags, two deterministic pages, search, two hide-empty controls, three unsupported/oversized denials, anonymous denial, and zero target mutation. | AP-014 integration matrix | Supports the AP-014 hypothesis in the controlled fixture. |
| O6 | `OBSERVED` | All ten prior runtime matrices and all repository/package gates passed against the AP-014 worktree. | execution log | No observed regression in the covered boundaries. |
| O7 | `OBSERVED` | Both actionable review findings reproduce as explicit controls and pass after correction: 100 CJK characters are accepted and `PHP_INT_MAX` returns an empty page without repeated items. | post-review matrix | Review defects are resolved locally; updated hosted-head verification remains required. |
| O8 | `OBSERVED` | PR #28 merged without O7's corrections even though its original head and merge workflow were green. | PR #28/main reconciliation | Green CI did not cover the newly identified edge cases; AP-014 closeout remains incomplete. |
| O9 | `OBSERVED` | Corrective PR #29 contained only the two fixes, regressions, and evidence reconciliation; its exact head and resulting merge commit both passed hosted checks. | PR #29 and runs `33488848270`, `33488950892` | AP-014 hosted closeout is complete without rewriting the PR #28 contradiction. |

## Contradictions and failures

| ID | Expected | Observed | Classification | Resolution/status |
|---|---|---|---|---|
| none | No contradiction before source inspection. | N/A | N/A | Open. |
| F1 | Category hide-empty fixture should return only the assigned child. | First matrix reported a mismatch without exposing the actual bounded result. | pending test/service classification | Include the small result envelope in the assertion, clean stale fixture state on rerun, and inspect exact WordPress behavior. |
| F2 | WordPress term items and count should apply identical hide-empty semantics. | Hierarchical item queries added an empty ancestor, while the count query reported only the nonempty child. | implementation query defect | Set `hierarchical=false` for both count and item queries; retain `parent_id` in projection; rerun full matrix. |
| F3 | First PHP standards gate should pass after runtime behavior is green. | Four query-array arrows were misaligned after adding `hierarchical`. | implementation standards defect | Align the five keys mechanically and rerun. |
| F4 | Green initial PR head should be merge-ready after comment inspection. | Review comment `3900867864` showed byte-count rejection of schema-valid multibyte search; `3900867872` showed possible page-offset integer overflow/repeated first page. | genuine implementation review defects | Count characters with mb fallback; calculate total pages before offset and skip the item query for out-of-range pages; add exact regressions and require new latest-head CI. |
| F5 | Review corrections should land before AP-014 merge closeout. | PR #28 was merged at the pre-correction reviewed head while the validated corrections remained local. | publication sequencing contradiction | Preserve corrections on the AP-014 branch, open a narrow corrective PR against merge `4d89dbe`, and require exact corrected-head plus corrected-merge CI before closing issue #27. |

## Decisions

| ID | Label | Decision | Evidence basis | Trade-off | Revisit trigger |
|---|---|---|---|---|---|
| D1 | `DECIDED` | Implement only category/post_tag reads and the exact registered term fields; no mutation or custom taxonomy. | S1/S2 | AP-017/AP-033 remain separate. | Fixed contract changes. |
| D2 | `DECIDED` | Use ascending `term_id` as the fixed deterministic order because the contract exposes no order input. | O3 | Stable pagination is reproducible but not alphabetical. | Product contract adds explicit ordering. |

## Verification matrix

| Acceptance condition | Check performed | Outcome | Evidence |
|---|---|---|---|
| Category/tag fixture and deterministic pagination | three categories/two tags with exact term-ID pages | `PASS` | exact first/second category pages and tag totals/pages. |
| Search and hide-empty | fixed search, 100-character multibyte search, plus category/tag nonempty controls | `PASS` | exact Bravo result; schema-valid multibyte input accepted; only assigned category/tag remained. |
| Extreme page safety | request `PHP_INT_MAX` with `per_page=1` | `PASS` | empty page with correct total; no overflow or repeated first item. |
| Unsupported/custom taxonomy denial | policy and direct-service custom taxonomy plus per_page=101 | `PASS` | three stable denials. |
| Three-role schema, logged-out denial, zero mutation | Administrator/Author/Subscriber outputs, anonymous permission, term/relationship snapshots | `PASS` | three schema validations; anonymous denied; zero changed fields/relationships. |

## Artifact inventory

| Artifact | Type | State | SHA-256/identifier | Notes |
|---|---|---|---|---|
| `docs/evidence/sessions/2026-09-01-exp-020-list-terms.md` | evidence | merged plus closeout append | EXP-020 | Preserves initial failures, review findings, premature merge, and corrective verification. |
| `agentpress/includes/Terms/TermReadService.php` | implementation | merged | `9727e3b`; correction `fe003dd` | Exact category/tag reader with character-safe search and overflow-safe bounded pagination. |
| `agentpress/tests/integration/ap014-list-terms.php` | executable evidence | merged | `9727e3b`; correction `fe003dd` | Synthetic role/taxonomy/search/hide-empty/schema/denial/mutation and review-regression controls. |
| `dist/agentpress.zip` | generated package | excluded | SHA-256 `8054C0A80AC46031DD8FFABBCF07EE32024AE6423144C788EE4ED950ABF45325` | Two consecutive post-review builds matched; 49 entries. |
| PR #29 | hosted corrective review | merged | head `fe003dd`; merge `d62c634` | Exact corrected head and corrected merge workflows succeeded; no comments remained. |

## Result

`SUPPORTED`

`OBSERVED`: the controlled real-WordPress evidence supports the hypothesis. Category/tag fixtures, stable and extreme-page pagination, ASCII and multibyte search, literal hide-empty behavior, fixed field projection, three-role schema validation, custom-taxonomy/oversize denial, anonymous denial, and zero mutation all passed. Prior runtime and repository gates remained green.

`OBSERVED`: PR #28 merged only the pre-correction head. Corrective PR #29 subsequently merged both fixes, and its exact head plus corrected `main` merge passed hosted CI.

## Limitations and `NOT_TESTED` boundaries

- `NOT_TESTED`: representative production taxonomy timing/query budgets, wp-admin UI, deployment, real ChatGPT Site Tools, release installation, and five-run reliability.
- Local browser-contract tests passed but are not live Chrome/WebMCP or ChatGPT evidence.

## Competition evidence statement

- work attributable to challenge period: pre-mutation baseline/timestamps recorded;
- pre-existing work distinguished by: synchronized AP-013 closeout baseline;
- third-party material/license/pin: `NOT_APPLICABLE` pending inspection;
- commit/PR evidence: implementation `9727e3b3349da43796620a688bb5e71a850b2183`; PR #28 merged pre-correction as `4d89dbeef5deb73b4c63e3526b5f3b424bbd58b8`; corrective commit `fe003dd8de253c9f8a6b2479e1e54155c0688797`; PR #29 merged as `d62c63435c6e1af939e7aa3afc3dffd9b71cb979`; issue #27 closed;
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
git status --short --branch: clean synchronized main at corrected AP-014 merge before this closeout append
tests/checks: corrected AP-014 matrix pass; AP-004–AP-013 regressions pass; PHPUnit 68/593; PHPCS 39 files; browser 14/14; provenance 49 entries; audit 0; deterministic ZIP pass
committed: original implementation `9727e3b3349da43796620a688bb5e71a850b2183`; correction `fe003dd8de253c9f8a6b2479e1e54155c0688797`
pushed: PR #29 corrected head and merge commit verified on GitHub
deployed: no
```
